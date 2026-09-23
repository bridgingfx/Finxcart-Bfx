<?php

namespace App\Http\Controllers\Vendor;

use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Enums\ExportFileNames\Admin\Report;
use App\Exports\OrderReportExport;
use App\Models\Seller;
use App\Utils\BackEndHelper;
use App\Utils\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderReportController extends Controller
{
    public function __construct(
        private readonly VendorRepositoryInterface $vendorRepo,
    )
    {
    }

    public function order_report(Request $request)
    {
        $date_type = $request['date_type'] ?? 'this_year';
        $from = $request['from'];
        $search = $request['search'];
        $to = $request['to'];
        $query_param = ['search' => $request['search'], 'date_type' => $date_type, 'from' => $from, 'to' => $to];

        $chart_data = self::order_report_chart_filter($request);

        $orders = self::all_order_table_data_filter($request);
        $orders = $orders->latest('updated_at')->paginate(Helpers::pagination_limit())->appends($query_param);

        // The 3 status counts + 6 amount sums below all share the exact same base
        // filter (seller_is/seller_id + the selected date range) and only differ by
        // which order_status/payment_method selects a row and whether it's counted
        // or summed — so they're collapsed into one conditional-aggregation query
        // instead of 9 separate round-trips.
        $stats_query = Order::where(['seller_is' => 'seller', 'seller_id' => auth('seller')->id()]);
        $stats = self::date_wise_common_filter($stats_query, $date_type, $from, $to)
            ->selectRaw("
                SUM(CASE WHEN order_status IN ('out_for_delivery', 'processing', 'confirmed', 'pending') THEN 1 ELSE 0 END) as ongoing_order,
                SUM(CASE WHEN order_status IN ('canceled', 'failed', 'returned') THEN 1 ELSE 0 END) as canceled_order,
                SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered_order,
                SUM(CASE WHEN order_status NOT IN ('delivered', 'canceled', 'returned', 'failed') THEN order_amount ELSE 0 END) as due_amount,
                SUM(CASE WHEN order_status = 'delivered' THEN order_amount ELSE 0 END) as settled_amount,
                SUM(CASE WHEN order_status = 'delivered' AND payment_method NOT IN ('cash', 'cash_on_delivery', 'pay_by_wallet', 'offline_payment') THEN order_amount ELSE 0 END) as digital_payment,
                SUM(CASE WHEN order_status = 'delivered' AND payment_method IN ('cash', 'cash_on_delivery') THEN order_amount ELSE 0 END) as cash_payment,
                SUM(CASE WHEN order_status = 'delivered' AND payment_method = 'pay_by_wallet' THEN order_amount ELSE 0 END) as wallet_payment,
                SUM(CASE WHEN payment_method = 'offline_payment' THEN order_amount ELSE 0 END) as offline_payment
            ")->first();

        $ongoing_order = (int) $stats->ongoing_order;
        $canceled_order = (int) $stats->canceled_order;
        $delivered_order = (int) $stats->delivered_order;

        $order_count = array(
            'ongoing_order' => $ongoing_order,
            'canceled_order' => $canceled_order,
            'delivered_order' => $delivered_order,
            'total_order' => $canceled_order + $ongoing_order + $delivered_order,
        );

        $due_amount = (float) $stats->due_amount;
        $settled_amount = (float) $stats->settled_amount;

        $digital_payment = (float) $stats->digital_payment;
        $cash_payment = (float) $stats->cash_payment;
        $wallet_payment = (float) $stats->wallet_payment;
        $offline_payment = (float) $stats->offline_payment;

        $total_payment = $cash_payment + $wallet_payment + $digital_payment + $offline_payment;

        $payment_data = [
            'total_payment' => $total_payment,
            'cash_payment' => $cash_payment,
            'wallet_payment' => $wallet_payment,
            'digital_payment' => $digital_payment,
            'offline_payment' => $offline_payment,
        ];

        return view('vendor-views.report.order-report', compact('orders', 'order_count', 'payment_data', 'chart_data', 'due_amount', 'settled_amount', 'search', 'date_type', 'from', 'to'));
    }

    public function order_report_chart_filter($request)
    {
        $from = $request['from'];
        $to = $request['to'];
        $date_type = $request['date_type'] ?? 'this_year';

        if ($date_type == 'this_year') {
            $number = 12;
            $default_inc = 1;
            $current_start_year = date('Y-01-01');
            $current_end_year = date('Y-12-31');
            $from_year = Carbon::parse($from)->format('Y');
            return self::order_report_same_year($request, $current_start_year, $current_end_year, $from_year, $number, $default_inc);
        } elseif ($date_type == 'this_month') { //this month table
            $current_month_start = date('Y-m-01');
            $current_month_end = date('Y-m-t');
            $inc = 1;
            $month = date('m');
            $number = date('d', strtotime($current_month_end));
            return self::order_report_same_month($request, $current_month_start, $current_month_end, $month, $number, $inc);
        } elseif ($date_type == 'this_week') {
            return self::order_report_this_week($request);
        } elseif ($date_type == 'today') {
            return self::getOrderReportForToday($request);
        } elseif ($date_type == 'custom_date' && !empty($from) && !empty($to)) {
            $start_date = Carbon::parse($from)->format('Y-m-d 00:00:00');
            $end_date = Carbon::parse($to)->format('Y-m-d 23:59:59');
            $from_year = Carbon::parse($from)->format('Y');
            $from_month = Carbon::parse($from)->format('m');
            $from_day = Carbon::parse($from)->format('d');
            $to_year = Carbon::parse($to)->format('Y');
            $to_month = Carbon::parse($to)->format('m');
            $to_day = Carbon::parse($to)->format('d');

            if ($from_year != $to_year) {
                return self::order_report_different_year($request, $start_date, $end_date, $from_year, $to_year);
            } elseif ($from_month != $to_month) {
                return self::order_report_same_year($request, $start_date, $end_date, $from_year, $to_month, $from_month);
            } elseif ($from_month == $to_month) {
                return self::order_report_same_month($request, $start_date, $end_date, $from_month, $to_day, $from_day);
            }
        }
    }

    public function order_report_same_year($request, $start_date, $end_date, $from_year, $number, $default_inc)
    {
        $orders = self::order_report_chart_common_query($start_date, $end_date)
            ->selectRaw('sum(order_amount) as order_amount, YEAR(updated_at) year, MONTH(updated_at) month')
            ->groupBy(DB::raw("DATE_FORMAT(updated_at, '%M')"))
            ->latest('updated_at')->get();

        for ($inc = $default_inc; $inc <= $number; $inc++) {
            $month = substr(date("F", strtotime("2023-$inc-01")), 0, 3);
            $order_amount[$month] = 0;
            foreach ($orders as $match) {
                if ($match['month'] == $inc) {
                    $order_amount[$month] = $match['order_amount'];
                }
            }
        }

        return array(
            'order_amount' => $order_amount,
        );
    }

    public function order_report_same_month($request, $start_date, $end_date, $month_date, $number, $default_inc)
    {
        $year_month = date('Y-m', strtotime($start_date));
        $month = substr(date("F", strtotime("$year_month")), 0, 3);

        $orders = self::order_report_chart_common_query($start_date, $end_date)
            ->selectRaw('sum(order_amount) as order_amount, YEAR(updated_at) year, MONTH(updated_at) month, DAY(updated_at) day')
            ->groupBy(DB::raw("DATE_FORMAT(updated_at, '%D')"))
            ->latest('updated_at')->get();

        for ($inc = $default_inc; $inc <= $number; $inc++) {
            $order_amount[$inc] = 0;
            foreach ($orders as $match) {
                if ($match['day'] == $inc) {
                    $order_amount[$inc] = $match['order_amount'];
                }
            }
        }

        return array(
            'order_amount' => $order_amount,
        );
    }

    public function order_report_this_week($request)
    {
        $start_date = Carbon::now()->startOfWeek();
        $end_date = Carbon::now()->endOfWeek();

        $number = 6;
        $period = CarbonPeriod::create(Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek());
        $day_name = array();
        foreach ($period as $date) {
            array_push($day_name, $date->format('l'));
        }

        $orders = self::order_report_chart_common_query($start_date, $end_date)
            ->select(
                DB::raw('sum(order_amount) as order_amount'),
                DB::raw("(DATE_FORMAT(updated_at, '%W')) as day")
            )
            ->groupBy(DB::raw("DATE_FORMAT(updated_at, '%D')"))
            ->latest('updated_at')->get();

        for ($inc = 0; $inc <= $number; $inc++) {
            $order_amount[$day_name[$inc]] = 0;
            foreach ($orders as $match) {
                if ($match['day'] == $day_name[$inc]) {
                    $order_amount[$day_name[$inc]] = $match['order_amount'];
                }
            }
        }

        return array(
            'order_amount' => $order_amount,
        );
    }

    public function getOrderReportForToday($request): array
    {
        $number = 1;
        $dayName = [Carbon::today()->format('l')];
        $orders = self::order_report_chart_common_query(Carbon::now()->startOfDay(), Carbon::now()->endOfDay())
            ->select(
                DB::raw('sum(order_amount) as order_amount'),
                DB::raw("(DATE_FORMAT(updated_at, '%W')) as day")
            )
            ->groupBy(DB::raw("DATE_FORMAT(updated_at, '%D')"))
            ->latest('updated_at')->get();

        for ($inc = 0; $inc < $number; $inc++) {
            $orderAmount[$dayName[$inc]] = 0;
            foreach ($orders as $match) {
                if ($match['day'] == $dayName[$inc]) {
                    $orderAmount[$dayName[$inc]] = $match['order_amount'];
                }
            }
        }

        return [
            'order_amount' => $orderAmount ?? [],
        ];
    }

    public function order_report_different_year($request, $start_date, $end_date, $from_year, $to_year)
    {
        $orders = self::order_report_chart_common_query($start_date, $end_date)
            ->selectRaw('sum(order_amount) as order_amount, YEAR(updated_at) year')
            ->groupBy(DB::raw("DATE_FORMAT(updated_at, '%Y')"))
            ->latest('updated_at')->get();

        for ($inc = $from_year; $inc <= $to_year; $inc++) {
            $order_amount[$inc] = 0;
            foreach ($orders as $match) {
                if ($match['year'] == $inc) {
                    $order_amount[$inc] = $match['order_amount'];
                }
            }
        }

        return array(
            'order_amount' => $order_amount,
        );
    }

    public function all_order_table_data_filter($request)
    {
        $search = $request['search'];
        $from = $request['from'];
        $to = $request['to'];
        $date_type = $request['date_type'] ?? 'this_year';

        $orders_query = Order::withSum('details', 'tax')
            ->withSum('details', 'discount')
            ->when($search, function ($q) use ($search) {
                $q->orWhere('id', 'like', "%{$search}%");
            })
            ->where(['seller_is' => 'seller', 'seller_id' => auth('seller')->id()]);
        return self::date_wise_common_filter($orders_query, $date_type, $from, $to);
    }

    public function date_wise_common_filter($query, $date_type, $from, $to)
    {
        return $query->when(($date_type == 'this_year'), function ($query) {
            return $query->whereYear('updated_at', date('Y'));
        })
            ->when(($date_type == 'this_month'), function ($query) {
                return $query->whereMonth('updated_at', date('m'))
                    ->whereYear('updated_at', date('Y'));
            })
            ->when(($date_type == 'this_week'), function ($query) {
                return $query->whereBetween('updated_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            })
            ->when(($date_type == 'today'), function ($query) {
                return $query->whereBetween('updated_at', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()]);
            })
            ->when(($date_type == 'custom_date' && !is_null($from) && !is_null($to)), function ($query) use ($from, $to) {
                return $query->whereDate('updated_at', '>=', $from)
                    ->whereDate('updated_at', '<=', $to);
            });
    }

    public function pie_chart_and_order_count_common_query($request, $query)
    {
        $from = $request['from'];
        $to = $request['to'];
        $date_type = $request['date_type'] ?? 'this_year';

        $query_f = $query->where(['seller_is' => 'seller', 'seller_id' => auth('seller')->id()]);
        return self::date_wise_common_filter($query_f, $date_type, $from, $to);
    }

    public function order_report_export_excel(Request $request)
    {
        $orders = self::all_order_table_data_filter($request)->latest('updated_at')->get();

        $data = array();
        foreach ($orders as $order) {
            $data[] = array(
                'Order ID' => $order->id,
                'Total Amount' => setCurrencySymbol(amount: usdToDefaultCurrency(amount: $order->order_amount)),
                'Product Discount' => setCurrencySymbol(amount: usdToDefaultCurrency(amount: $order->details_sum_discount)),
                'Coupon Discount' => setCurrencySymbol(amount: usdToDefaultCurrency(amount: $order->discount_amount)),
                'Shipping Charge' => setCurrencySymbol(amount: usdToDefaultCurrency(amount: $order->shipping_cost - ($order->extra_discount_type == 'free_shipping_over_order_amount' ? $order->extra_discount : 0))),
                'VAT/TAX' => setCurrencySymbol(amount: usdToDefaultCurrency(amount: $order->details_sum_tax)),
                'Commission' => setCurrencySymbol(amount: usdToDefaultCurrency(amount: $order->admin_commission)),
                'deliveryman_incentive' => ($order->delivery_type == 'self_delivery' && $order->delivery_man_id) ? setCurrencySymbol(amount: usdToDefaultCurrency(amount: $order->deliveryman_charge ?? 0)) : setCurrencySymbol(amount: usdToDefaultCurrency(amount: 0)),
                'Status' => BackEndHelper::order_status($order->order_status)
            );
        }

        return (new FastExcel($data))->download('order_report_list.xlsx');
    }

    public function orderReportExportExcel(Request $request): BinaryFileResponse
    {
        $orders = self::all_order_table_data_filter($request)->latest('updated_at')->get();
        $vendorId = auth('seller')->id();
        $vendor = $this->vendorRepo->getFirstWhere(params: ['id' => $vendorId]);
        $data = [
            'orders' => $orders,
            'search' => $request['search'],
            'vendor' => $vendor,
            'from' => $request['from'],
            'to' => $request['to'],
            'dateType' => $request['date_type'] ?? 'this_year'
        ];
        return Excel::download(new OrderReportExport($data), Report::ORDER_REPORT_LIST);
    }

    public function order_report_chart_common_query($start_date, $end_date)
    {
        return Order::where(['seller_is' => 'seller', 'seller_id' => auth('seller')->id(), 'order_status' => 'delivered'])
            ->whereBetween('updated_at', [$start_date, $end_date]);
    }

    public function exportOrderReportInPDF(Request $request)
    {
        $dateType = $request['date_type'] ?? 'this_year';

        $orders = self::all_order_table_data_filter($request)->latest('updated_at')->get();
        $seller = auth('seller')->user()->f_name . ' ' . auth('seller')->user()->l_name;

        $totalOrderAmount = $orders->sum('order_amount') ?? 0;
        $totalProductDiscount = $orders->sum('details_sum_discount') ?? 0;
        $totalDiscountedAmount = $orders->sum('discount_amount') ?? 0;
        $totalTax = $orders->sum('details_sum_tax') ?? 0;
        $totalOrderCommission = $orders->sum('admin_commission') ?? 0;


        $totalCouponDiscount = 0;

        $orders->map(function ($order) use ($totalCouponDiscount) {
            $totalCouponDiscount = $totalCouponDiscount + ($order->shipping_cost - ($order->extra_discount_type == 'free_shipping_over_order_amount' ? $order->extra_discount : 0));
        });

        $totalDeliveryCharge = 0;
        $totalDeliverymanIncentive = 0;
        foreach ($orders as $order) {
            $totalDeliveryCharge += ($order->shipping_cost - ($order->extra_discount_type == 'free_shipping_over_order_amount' ? $order->extra_discount : 0));
            $totalDeliverymanIncentive += ($order->delivery_type == 'self_delivery' && $order->delivery_man_id) ? $order->deliveryman_charge : 0;
        }

        $data = [
            'orders' => $orders,
            'total_orders' => count($orders),
            'search' => $request['search'],
            'seller' => $seller,
            'type' => 'seller',
            'from' => $request['from'],
            'to' => $request['to'],
            'company_name' => getWebConfig(name: 'company_name'),
            'company_email' => getWebConfig(name: 'company_email'),
            'company_phone' => getWebConfig(name: 'company_phone'),
            'company_web_logo' => getWebConfig(name: 'company_web_logo'),
            'date_type' => $request['date_type'] ?? 'this_year',
            'total_order_amount' => $totalOrderAmount,
            'total_product_discount' => $totalProductDiscount,
            'total_coupon_discount' => $totalCouponDiscount,
            'total_discounted_amount' => $totalDiscountedAmount,
            'total_tax' => $totalTax,
            'total_order_commission' => $totalOrderCommission,
            'total_delivery_charge' => $totalDeliveryCharge,
            'total_deliveryman_incentive' => $totalDeliverymanIncentive,
        ];

        $mpdfView = View::make('admin-views.transaction.total_orders_report_pdf', ['data' => $data]);
        Helpers::gen_mpdf($mpdfView, 'order_transaction_summary_report_', $dateType);
    }
}
