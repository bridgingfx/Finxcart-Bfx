<?php

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\RestockProductRepositoryInterface;
use App\Contracts\Repositories\RestockProductCustomerRepositoryInterface;
use App\Contracts\Repositories\RobotsMetaContentRepositoryInterface;
use App\Enums\WebConfigKey;
use App\Events\RefundEvent;
use App\Http\Requests\Web\CustomerProfileUpdateRequest;
use App\Models\SupportTicketConv;
use App\Traits\PdfGenerator;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\DeliveryMan;
use App\Models\DeliveryZipCode;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductCompare;
use App\Models\RefundRequest;
use App\Models\Review;
use App\Models\Seller;
use App\Models\ShippingAddress;
use App\Models\SupportTicket;
use App\Models\BusinessSetting;
use App\Models\Wishlist;
use App\Traits\CommonTrait;
use App\Models\User;
use App\Utils\CustomerManager;
use App\Utils\Helpers;
use App\Utils\ImageManager;
use App\Utils\OrderManager;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserProfileController extends Controller
{
    use CommonTrait, PdfGenerator;

    public function __construct(
        private Order                                              $order,
        private Seller                                             $seller,
        private Product                                            $product,
        private Review                                             $review,
        private DeliveryMan                                        $deliver_man,
        private ProductCompare                                     $compare,
        private Wishlist                                           $wishlist,
        private readonly BusinessSettingRepositoryInterface        $businessSettingRepo,
        private readonly RobotsMetaContentRepositoryInterface      $robotsMetaContentRepo,
        private readonly RestockProductRepositoryInterface         $restockProductRepo,
        private readonly RestockProductCustomerRepositoryInterface $restockProductCustomerRepo,
    )
    {

    }

    public function user_profile(Request $request)
    {
        // 'user_profile' has no dedicated view in the currently active theme (only
        // theme_aster ships one) — fall back to the account dashboard instead of
        // a fatal "View [] not found" error.
        if (empty(VIEW_FILE_NAMES['user_profile'])) {
            return redirect()->route('user-account');
        }

        $wishlists = $this->wishlist->whereHas('wishlistProduct', function ($q) {
            return $q;
        })->where('customer_id', auth('customer')->id())->count();
        $total_order = $this->order->where('customer_id', auth('customer')->id())->count();
        $total_loyalty_point = auth('customer')->user()->loyalty_point;
        $totalWalletBalance = auth('customer')->user()->wallet_balance;
        $addresses = ShippingAddress::where('customer_id', auth('customer')->id())->latest()->get();
        $customer_detail = User::where('id', auth('customer')->id())->first();

        return view(VIEW_FILE_NAMES['user_profile'], compact('customer_detail', 'addresses', 'wishlists', 'total_order', 'total_loyalty_point', 'totalWalletBalance'));
    }

    public function user_account(Request $request)
    {
        $country_restrict_status = getWebConfig(name: 'delivery_country_restriction');
        $customerDetail = User::where('id', auth('customer')->id())->first();
        return view(VIEW_FILE_NAMES['user_account'], compact('customerDetail'));

    }

    public function getUserProfileUpdate(CustomerProfileUpdateRequest $request): RedirectResponse
    {
        $imageName = $request->file('image') ? ImageManager::update('profile/', auth('customer')->user()->image, 'webp', $request->file('image')) : auth('customer')->user()->image;
        $user = auth('customer')->user();
        User::find($user['id'])->update([
            'f_name' => $request['f_name'],
            'l_name' => $request['l_name'],
            'phone' => $user['is_phone_verified'] ? $user['phone'] : $request['phone'],
            'email' => $request['email'],
            'is_phone_verified' => $request['phone'] == $user['phone'] ? $user['is_phone_verified'] : 0,
            'is_email_verified' => $request['email'] == $user['email'] ? $user['is_email_verified'] : 0,
            'image' => $imageName,
            'password' => !empty($request['password']) && strlen($request['password']) > 5 ? bcrypt($request['password']) : auth('customer')->user()->password,
        ]);

        Toastr::info(translate('updated_successfully'));
        return redirect()->back();
    }

    public function account_address_add()
    {
        // 'account_address_add' has no dedicated view in the currently active theme
        // (only theme_aster ships one) — the default theme adds addresses via the
        // modal on the account-address page instead. Redirect there rather than a
        // fatal "View [] not found" error, since this route carries no 'customer'
        // middleware and is reachable by anyone.
        if (empty(VIEW_FILE_NAMES['account_address_add'])) {
            return redirect()->route('account-address');
        }

        $country_restrict_status = getWebConfig(name: 'delivery_country_restriction');
        $zip_restrict_status = getWebConfig(name: 'delivery_zip_code_area_restriction');
        $default_location = getWebConfig(name: 'default_location');

        $countries = $country_restrict_status ? $this->get_delivery_country_array() : COUNTRIES;

        $zip_codes = $zip_restrict_status ? Cache::remember(CACHE_FOR_DELIVERY_ZIP_CODES, CACHE_FOR_3_HOURS, fn() => DeliveryZipCode::all()) : 0;

        return view(VIEW_FILE_NAMES['account_address_add'], compact('countries', 'zip_restrict_status', 'zip_codes', 'default_location'));
    }

    public function account_delete($id)
    {
        if (auth('customer')->id() == $id) {
            $user = User::find($id);

            $ongoing = ['out_for_delivery', 'processing', 'confirmed', 'pending'];
            $order = Order::where('customer_id', $user->id)->whereIn('order_status', $ongoing)->count();
            if ($order > 0) {
                Toastr::warning(translate('you_can_not_delete_account_due_ongoing_order'));
                return redirect()->back();
            }
            auth()->guard('customer')->logout();

            ImageManager::delete('/profile/' . $user['image']);
            session()->forget('wish_list');

            $user->delete();
            Toastr::info(translate('Your_account_deleted_successfully!!'));
            return redirect()->route('home');
        }

        Toastr::warning(translate('access_denied') . '!!');
        return back();
    }

    public function account_address(): View|RedirectResponse
    {
        $country_restrict_status = getWebConfig(name: 'delivery_country_restriction');
        $zip_restrict_status = getWebConfig(name: 'delivery_zip_code_area_restriction');

        $countries = $country_restrict_status ? $this->get_delivery_country_array() : COUNTRIES;
        $zip_codes = $zip_restrict_status ? Cache::remember(CACHE_FOR_DELIVERY_ZIP_CODES, CACHE_FOR_3_HOURS, fn() => DeliveryZipCode::all()) : 0;

        $countriesName = [];
        $countriesCode = [];
        foreach ($countries as $country) {
            $countriesName[] = $country['name'];
            $countriesCode[] = $country['code'];
        }

        if (auth('customer')->check()) {
            $shippingAddresses = ShippingAddress::where('customer_id', auth('customer')->id())->latest()->get();
            return view('web-views.users-profile.account-address', compact('shippingAddresses', 'country_restrict_status', 'zip_restrict_status', 'countries', 'zip_codes', 'countriesName', 'countriesCode'));
        } else {
            return redirect()->route('home');
        }
    }

    public function address_store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required|max:20',
            'city' => 'required',
            'zip' => 'required',
            'country' => 'required',
            'address' => 'required',
        ]);

        $numericPhoneValue = preg_replace('/[^0-9]/', '', $request['phone']);
        $numericLength = strlen($numericPhoneValue);
        if ($numericLength < 4 || $numericLength > 20) {
            $request->validate([
                'phone' => 'min:5|max:20',
            ], [
                'phone.min' => translate('The_phone_number_must_be_at_least_4_characters'),
                'phone.max' => translate('The_phone_number_may_not_be_greater_than_20_characters'),
            ]);
        }

        $country_restrict_status = getWebConfig(name: 'delivery_country_restriction');
        $zip_restrict_status = getWebConfig(name: 'delivery_zip_code_area_restriction');

        $country_exist = self::delivery_country_exist_check($request->country);
        $zipcode_exist = self::delivery_zipcode_exist_check($request->zip);

        if ($country_restrict_status && !$country_exist) {
            Toastr::error(translate('Delivery_unavailable_in_this_country!'));
            return back();
        }

        if ($zip_restrict_status && !$zipcode_exist) {
            Toastr::error(translate('Delivery_unavailable_in_this_zip_code_area!'));
            return back();
        }

        $address = [
            'customer_id' => auth('customer')->check() ? auth('customer')->id() : null,
            'contact_person_name' => $request['name'],
            'address_type' => $request['addressAs'],
            'address' => $request['address'],
            'city' => $request['city'],
            'zip' => $request['zip'],
            'country' => $request['country'],
            'phone' => $request['phone'],
            'is_billing' => $request['is_billing'],
            'latitude' => $request['latitude'],
            'longitude' => $request['longitude'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('shipping_addresses')->insert($address);

        Toastr::success(translate('address_added_successfully!'));

        if (theme_root_path() == 'default') {
            return back();
        } else {
            return redirect()->route('user-profile');
        }
    }

    public function address_edit(Request $request, $id)
    {
        $shippingAddress = ShippingAddress::where('customer_id', auth('customer')->id())->find($id);
        $country_restrict_status = getWebConfig(name: 'delivery_country_restriction');
        $zip_restrict_status = getWebConfig(name: 'delivery_zip_code_area_restriction');

        $delivery_countries = $country_restrict_status ? self::get_delivery_country_array() : COUNTRIES;
        $delivery_zipcodes = $zip_restrict_status ? Cache::remember(CACHE_FOR_DELIVERY_ZIP_CODES, CACHE_FOR_3_HOURS, fn() => DeliveryZipCode::all()) : 0;

        $countriesName = [];
        $countriesCode = [];
        foreach ($delivery_countries as $country) {
            $countriesName[] = $country['name'];
            $countriesCode[] = $country['code'];
        }

        if (isset($shippingAddress)) {
            return view(VIEW_FILE_NAMES['account_address_edit'], compact('shippingAddress', 'country_restrict_status', 'zip_restrict_status', 'delivery_countries', 'delivery_zipcodes', 'countriesName', 'countriesCode'));
        } else {
            Toastr::warning(translate('access_denied'));
            return back();
        }
    }

    public function address_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required|max:20',
            'city' => 'required',
            'zip' => 'required',
            'country' => 'required',
            'address' => 'required',
        ]);

        $numericPhoneValue = preg_replace('/[^0-9]/', '', $request['phone']);
        $numericLength = strlen($numericPhoneValue);
        if ($numericLength < 4 || $numericLength > 20) {
            $request->validate([
                'phone' => 'min:5|max:20',
            ], [
                'phone.min' => translate('The_phone_number_must_be_at_least_4_characters'),
                'phone.max' => translate('The_phone_number_may_not_be_greater_than_20_characters'),
            ]);
        }

        $country_restrict_status = getWebConfig(name: 'delivery_country_restriction');
        $zip_restrict_status = getWebConfig(name: 'delivery_zip_code_area_restriction');

        $country_exist = self::delivery_country_exist_check($request->country);
        $zipcode_exist = self::delivery_zipcode_exist_check($request->zip);

        if ($country_restrict_status && !$country_exist) {
            Toastr::error(translate('Delivery_unavailable_in_this_country!'));
            return back();
        }

        if ($zip_restrict_status && !$zipcode_exist) {
            Toastr::error(translate('Delivery_unavailable_in_this_zip_code_area!'));
            return back();
        }

        $updateAddress = [
            'contact_person_name' => $request->name,
            'address_type' => $request->addressAs,
            'address' => $request->address,
            'city' => $request->city,
            'zip' => $request->zip,
            'country' => $request->country,
            'phone' => $request->phone,
            'is_billing' => $request->is_billing,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if (auth('customer')->check()) {
            ShippingAddress::where('id', $request->id)->update($updateAddress);
            Toastr::success(translate('address_updated_successfully!'));
        } else {
            Toastr::error(translate('Insufficient_permission!'));
        }
        return theme_root_path() == 'default' ? redirect()->route('account-address') : redirect()->route('user-profile');
    }

    public function address_delete(Request $request)
    {
        if (auth('customer')->check()) {
            ShippingAddress::destroy($request->id);
            Toastr::success(translate('address_Delete_Successfully'));
            return redirect()->back();
        } else {
            return redirect()->back();
        }
    }

    public function account_payment()
    {
        if (auth('customer')->check()) {
            return view('web-views.users-profile.account-payment');

        } else {
            return redirect()->route('home');
        }

    }

    public function account_order(Request $request)
    {
        $order_by = $request->order_by ?? 'desc';
        if (theme_root_path() == 'theme_fashion') {
            $show_order = $request->show_order ?? 'ongoing';

            $array = ['pending', 'confirmed', 'out_for_delivery', 'processing'];
            $orders = $this->order->withSum('orderDetails', 'qty')
                ->with(['children' => fn($q) => $q->withSum('orderDetails', 'qty')])
                ->where(['customer_id' => auth('customer')->id(), 'is_guest' => '0'])
                ->whereNull('parent_order_id')
                ->when($show_order == 'ongoing', function ($query) use ($array) {
                    $query->whereIn('order_status', $array);
                })
                ->when($show_order == 'previous', function ($query) use ($array) {
                    $query->whereNotIn('order_status', $array);
                })
                ->when($request['search'], function ($query) use ($request) {
                    $query->where('id', 'like', "%{$request['search']}%");
                })
                ->orderBy('id', $order_by)->paginate(10)->appends(['show_order' => $show_order, 'search' => $request->search]);
        } else {
            $show_order = $request->show_order ?? 'ongoing';
            $statusMap = [
                'ongoing'   => ['pending', 'confirmed', 'processing', 'out_for_delivery'],
                'delivered' => ['delivered'],
                'canceled'  => ['canceled', 'failed', 'returned'],
            ];
            $orders = $this->order->withSum('orderDetails', 'qty')
                ->with([
                    'children' => fn($q) => $q->withSum('orderDetails', 'qty'),
                    'details:order_id,product_type',
                    'seller.shop',
                ])
                ->where(['customer_id' => auth('customer')->id(), 'is_guest' => '0'])
                ->whereNull('parent_order_id')
                ->when(array_key_exists($show_order, $statusMap), fn($q) => $q->whereIn('order_status', $statusMap[$show_order]))
                ->orderBy('id', $order_by)
                ->paginate(10)
                ->appends(['show_order' => $show_order]);
        }

        return view(VIEW_FILE_NAMES['account_orders'], compact('orders', 'order_by', 'show_order'));
    }

    public function account_order_details(Request $request): View|RedirectResponse
    {
        $order = $this->order->with(['deliveryManReview', 'customer', 'offlinePayments', 'details.productAllStatus', 'details.product', 'children.details.productAllStatus', 'children.details.product', 'parent', 'seller.shop', 'children.seller.shop', 'sourceShipment.items.orderDetail', 'children.sourceShipment.items.orderDetail'])
            ->where(['id' => $request['id'], 'customer_id' => auth('customer')->id(), 'is_guest' => '0'])
            ->first();

        if ($order) {
            $displayDetails = $order->children->count() > 0
                ? $order->children->flatMap(function ($c) { return $c->details; })
                : $order->details;

            $reviewsByProduct = Review::where('customer_id', auth('customer')->id())
                ->whereIn('product_id', $order->details->pluck('product_id'))
                ->whereNull('delivery_man_id')
                ->get()
                ->groupBy('product_id')
                ->map(fn($group) => $group->values());

            $order?->details?->map(function ($detail) use ($order, $reviewsByProduct) {
                $order['total_qty'] += $detail->qty;

                $reviews = $reviewsByProduct->get($detail['product_id'], collect());
                $reviewData = null;
                foreach ($reviews as $review) {
                    if ($review->order_id == $detail->order_id) {
                        $reviewData = $review;
                    }
                }

                if (isset($reviews[0]) && is_null($reviewData)) {
                    $reviewData = ($reviews[0]['order_id'] == null ? $reviews[0] : null);
                }
                $detail['reviewData'] = $reviewData;
                return $order;
            });
            return view(VIEW_FILE_NAMES['account_order_details'], [
                'order' => $order,
                'displayDetails' => $displayDetails,
                'refund_day_limit' => getWebConfig(name: 'refund_day_limit'),
                'current_date' => Carbon::now(),
            ]);
        }

        Toastr::warning(translate('invalid_order'));
        return redirect()->route('account-oder');
    }

    public function account_order_details_seller_info(Request $request)
    {
        $order = $this->order->with(['seller.shop', 'children.seller.shop'])->find($request->id);
        if (!$order) {
            Toastr::warning(translate('invalid_order'));
            return redirect()->route('account-oder');
        }

        $sellerOrders = $order->children->count() > 0 ? $order->children : collect([$order]);

        $vendors = $sellerOrders->map(function ($sellerOrder) {
            $productIds = $this->product->active()->where(['added_by' => $sellerOrder->seller_is])->where('user_id', $sellerOrder->seller_id)->pluck('id')->toArray();
            $product_count = count($productIds);

            $ratingSummary = $this->review->active()
                ->whereIn('product_id', $productIds)
                ->selectRaw('COUNT(*) as rating_count, AVG(rating) as avg_rating, SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive_count')
                ->first();

            $rating_count = (int) ($ratingSummary->rating_count ?? 0);
            $avg_rating = $ratingSummary->avg_rating;
            $vendorRattingStatusPositive = (int) ($ratingSummary->positive_count ?? 0);

            $rating_percentage = $rating_count != 0 ? ($vendorRattingStatusPositive * 100) / $rating_count : 0;

            return [
                'order' => $sellerOrder,
                'avg_rating' => $avg_rating,
                'rating_count' => $rating_count,
                'product_count' => $product_count,
                'rating_percentage' => $rating_percentage,
            ];
        });

        return view(VIEW_FILE_NAMES['seller_info'], compact('vendors', 'order'));

    }

    public function account_order_details_delivery_man_info(Request $request)
    {

        $order = $this->order->with(['verificationImages', 'details.product', 'deliveryMan.rating', 'deliveryManReview', 'deliveryMan' => function ($query) {
            return $query->withCount('review');
        }])->find($request->id);

        if (!$order) {
            Toastr::warning(translate('invalid_order'));
            return redirect()->route('account-oder');
        }

        if (theme_root_path() == 'theme_fashion' || theme_root_path() == 'default') {
            foreach ($order->details as $details) {
                if ($details->product) {
                    if ($details->product->product_type == 'physical') {
                        $order['product_type_check'] = $details->product->product_type;
                        break;
                    } else {
                        $order['product_type_check'] = $details->product->product_type;
                    }
                }
            }
        }

        $delivered_count = $this->order->where(['order_status' => 'delivered', 'delivery_man_id' => $order->delivery_man_id, 'delivery_type' => 'self_delivery'])->count();

        return view(VIEW_FILE_NAMES['delivery_man_info'], compact('delivered_count', 'order'));
    }

    public function getAccountOrderDetailsReviewsView(Request $request): View|RedirectResponse
    {
        $order = $this->order->with(['deliveryManReview', 'customer', 'offlinePayments', 'details'])
            ->where(['id' => $request['id'], 'customer_id' => auth('customer')->id(), 'is_guest' => '0'])
            ->first();
        if ($order) {
            $reviewsByProduct = Review::with('reply')
                ->where('customer_id', auth('customer')->id())
                ->whereIn('product_id', $order->details->pluck('product_id'))
                ->whereNull('delivery_man_id')
                ->get()
                ->groupBy('product_id')
                ->map(fn($group) => $group->values());

            $order?->details?->map(function ($detail) use ($order, $reviewsByProduct) {
                $order['total_qty'] += $detail->qty;
                $reviews = $reviewsByProduct->get($detail['product_id'], collect());
                $reviewData = null;
                foreach ($reviews as $review) {
                    if ($review->order_id == $detail->order_id) {
                        $reviewData = $review;
                    }
                }
                if (isset($reviews[0]) && !$reviewData) {
                    $reviewData = ($reviews[0]['order_id'] != null ? $reviews[0] : null);
                }
                $detail['reviewData'] = $reviewData;
                return $order;
            });

            return view(VIEW_FILE_NAMES['order_details_review'], compact('order'));
        }
        Toastr::warning(translate('invalid_order'));
        return redirect()->route('account-oder');
    }


    public function account_wishlist()
    {
        if (auth('customer')->check()) {
            $wishlists = Wishlist::with([
                'productFullInfo.brand',
                'productFullInfo.digitalVariation',
                'productFullInfo.clearanceSale' => function ($query) {
                    $query->active();
                },
            ])->where('customer_id', auth('customer')->id())->paginate(15);
            $brand_setting = getWebConfig(name: 'product_brand');
            return view(VIEW_FILE_NAMES['account_wishlist'], compact('wishlists', 'brand_setting'));
        } else {
            return redirect()->route('home');
        }
    }

    public function account_tickets()
    {
        if (auth('customer')->check()) {
            $supportTickets = SupportTicket::where('customer_id', auth('customer')->id())->latest()->paginate(10);
            
            // --- Added lines to fetch orders ---
            $customerOrders = $this->order->with('orderDetails.product.translations')
                ->where(['customer_id' => auth('customer')->id(), 'is_guest' => '0'])
                ->latest()
                ->get(['id', 'order_status', 'created_at']); // Select only necessary fields

            return view(VIEW_FILE_NAMES['account_tickets'], compact('supportTickets', 'customerOrders'));
        } else {
            return redirect()->route('home');
        }
    }
    public function submitSupportTicket(Request $request): RedirectResponse
    {
        $request->validate([
            'ticket_subject' => 'required',
            'ticket_type' => 'required',
            'ticket_priority' => 'required',
            'ticket_description' => 'required_without_all:image.*',
            'image.*' => 'required_without_all:ticket_description|image|mimes:jpeg,png,jpg,gif|max:6000',
        ], [
            'ticket_subject.required' => translate('The_ticket_subject_is_required'),
            'ticket_type.required' => translate('The_ticket_type_is_required'),
            'ticket_priority.required' => translate('The_ticket_priority_is_required'),
            'ticket_description.required_without_all' => translate('Either_a_ticket_description_or_an_image_is_required'),
            'image.*.required_without_all' => translate('Either_a_ticket_description_or_an_image_is_required'),
            'image.*.image' => translate('The_file_must_be_an_image'),
            'image.*.mimes' => translate('The_file_must_be_of_type:_jpeg,_png,_jpg,_gif'),
            'image.*.max' => translate('The_image_must_not_exceed_6_MB'),
        ]);

        $images = [];
        if ($request->file('image')) {
            foreach ($request['image'] as $key => $value) {
                $image_name = ImageManager::upload('support-ticket/', 'webp', $value);
                $images[] = [
                    'file_name' => $image_name,
                    'storage' => getWebConfig(name: 'storage_connection_type') ?? 'public',
                ];
            }
        }

        $ticket = [
            'subject' => $request['ticket_subject'],
            'type' => $request['ticket_type'],
            'customer_id' => auth('customer')->check() ? auth('customer')->id() : null,
            'priority' => $request['ticket_priority'],
            'description' => $request['ticket_description'],
            'attachment' => json_encode($images),
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('support_tickets')->insert($ticket);
        return back();
    }

    public function single_ticket(Request $request)
    {
        $ticket = SupportTicket::with(['conversations' => function ($query) {
            $query->with('adminInfo')->when(theme_root_path() == 'default', function ($sub_query) {
                $sub_query->orderBy('id', 'desc');
            });
        }])->where('id', $request->id)->first();
        return view(VIEW_FILE_NAMES['ticket_view'], compact('ticket'));
    }

    public function comment_submit(Request $request, $id)
    {
        if ($request->file('image') == null && empty($request['comment'])) {
            Toastr::error(translate('type_something') . '!');
            return back();
        }

        DB::table('support_tickets')->where(['id' => $id])->update([
            'status' => 'open',
            'updated_at' => now(),
        ]);

        $image = [];
        if ($request->file('image')) {
            $validator = $request->validate([
                'image.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:6000'
            ]);
            foreach ($request->image as $key => $value) {
                $image_name = ImageManager::upload('support-ticket/', 'webp', $value);
                $image[] = [
                    'file_name' => $image_name,
                    'storage' => getWebConfig(name: 'storage_connection_type') ?? 'public',
                ];
            }
        }
        $data = [
            'customer_message' => $request->comment,
            'attachment' => $image,
            'support_ticket_id' => $id,
            'position' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        SupportTicketConv::create($data);
        Toastr::success(translate('message_send_successfully') . '!');
        return back();
    }

    public function support_ticket_close($id)
    {
        DB::table('support_tickets')->where(['id' => $id])->update([
            'status' => 'close',
            'updated_at' => now(),
        ]);
        Toastr::success(translate('ticket_closed') . '!');
        return redirect('/account-tickets');
    }


    public function support_ticket_delete($id): RedirectResponse
    {
        if (!auth('customer')->check()) {
            return redirect()->back();
        }

        $support = SupportTicket::with('conversations')
            ->where(['id' => $id, 'customer_id' => auth('customer')->id()])
            ->first();

        if (!$support) {
            Toastr::warning(translate('not_found') . '!');
            return redirect()->route('account-tickets');
        }

        $this->deleteSupportTicketAttachments($support->attachment);

        foreach ($support->conversations as $conversation) {
            $this->deleteSupportTicketAttachments($conversation->attachment);
        }

        $support->conversations()->delete();
        $support->delete();

        Toastr::success(translate('Removed_successfully') . '!');
        return redirect()->route('account-tickets');
    }

    public function track_order(): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'track-order']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        return view(VIEW_FILE_NAMES['tracking-page'], [
            'robotsMetaContentData' => $robotsMetaContentData
        ]);
    }

    public function track_order_wise_result(Request $request)
    {
        if (auth('customer')->check()) {
            $customerId = auth('customer')->id();
            $orderDetails = Order::with(['orderDetails', 'children.details', 'children.seller.shop', 'children.deliveryMan'])->where('id', $request['order_id'])
                ->where(function ($q) use ($customerId) {
                    $q->where('customer_id', $customerId)
                      ->orWhereHas('details', fn($q2) => $q2->where('customer_id', $customerId));
                })->first();

            if (!$orderDetails) {
                Toastr::warning(translate('invalid_order'));
                return redirect()->route('account-oder');
            }

            $isOrderOnlyDigital = self::getCheckIsOrderOnlyDigital($orderDetails);
            return view(VIEW_FILE_NAMES['track_order_wise_result'], compact('orderDetails', 'isOrderOnlyDigital'));
        }
        return back();
    }

    public function getCheckIsOrderOnlyDigital($order): bool
    {
        $isOrderOnlyDigital = true;
        if ($order->orderDetails) {
            foreach ($order->orderDetails as $detail) {
                $product = json_decode($detail->product_details, true);
                if (isset($product['product_type']) && $product['product_type'] == 'physical') {
                    $isOrderOnlyDigital = false;
                }
            }
        }
        return $isOrderOnlyDigital;
    }
public function track_order_result(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
            'phone_number' => 'required'
        ]);

        $user = auth('customer')->user();
        $orderId = $request['order_id'];
        // Clean the phone number (remove spaces) to improve matching
        $inputPhone = trim($request['phone_number']);

        $orderDetails = null;

        // 1. Attempt to find the order by ID first
        $order = Order::with(['shippingAddress', 'billingAddress', 'details', 'children.details', 'children.seller.shop', 'children.deliveryMan'])
            ->where('id', $orderId)
            ->first();

        if ($order) {
            // CHECK 1: Does the Shipping Address phone match?
            if ($order->shippingAddress && $this->phonesMatch($order->shippingAddress->phone, $inputPhone)) {
                $orderDetails = $order;
            }
            // CHECK 2: Does the Billing Address phone match?
            elseif ($order->billingAddress && $this->phonesMatch($order->billingAddress->phone, $inputPhone)) {
                $orderDetails = $order;
            }
            // CHECK 3: Does the customer's account phone match?
            else {
                $userInfo = User::where('id', $order->customer_id)->first();
                if ($userInfo && $this->phonesMatch($userInfo->phone, $inputPhone)) {
                    $orderDetails = $order;
                }
            }
        }

        // --- Result Handling ---
        
        if (isset($orderDetails)) {
            // POS Check
            if ($orderDetails['order_type'] == 'POS') {
                Toastr::error(translate('this_order_is_created_by_') . ($orderDetails['seller_is'] == 'seller' ? 'vendor' : 'admin') . translate('_from POS') . ',' . translate('please_contact_with_') . ($orderDetails['seller_is'] == 'seller' ? 'vendor' : 'admin') . translate('_to_know_more_details') . '.');
                return redirect()->back();
            }

            $order_verification_status = getWebConfig(name: 'order_verification');
            $isOrderOnlyDigital = self::getCheckIsOrderOnlyDigital($orderDetails);
            $user_phone = $inputPhone;

            return view(VIEW_FILE_NAMES['track_order'], compact('orderDetails', 'user_phone', 'order_verification_status', 'isOrderOnlyDigital'));
        }

        // If we reach here, no match was found
        Toastr::error(translate('invalid_Order_Id_or_phone_Number'));
        return redirect()->route('track-order.index', ['order_id' => $orderId, 'phone_number' => $inputPhone]);
    }

    private function phonesMatch($dbPhone, $inputPhone): bool
    {
        $cleanDb    = preg_replace('/[^0-9]/', '', (string) $dbPhone);
        $cleanInput = preg_replace('/[^0-9]/', '', (string) $inputPhone);

        if ($cleanDb === '' || $cleanInput === '') {
            return false;
        }

        // Exact numeric match
        if ($cleanDb === $cleanInput) {
            return true;
        }

        // Allow country-code differences: compare last 9 digits (minimum meaningful length)
        $minLen = 9;
        if (strlen($cleanDb) >= $minLen && strlen($cleanInput) >= $minLen) {
            return str_ends_with($cleanDb, substr($cleanInput, -$minLen))
                || str_ends_with($cleanInput, substr($cleanDb, -$minLen));
        }

        return false;
    }

    public function track_last_order()
    {
        $orderDetails = OrderManager::track_order(Order::where('customer_id', auth('customer')->id())->latest()->first()->id);

        if ($orderDetails != null) {
            return view('web-views.order.tracking', compact('orderDetails'));
        } else {
            return redirect()->route('track-order.index')->with('Error', translate('invalid_Order_Id_or_phone_Number'));
        }

    }

    public function order_cancel($id)
    {
        $order = Order::with(['details', 'children'])->where(['id' => $id])->first();

        if (!$order) {
            Toastr::error(translate('order_not_found'));
            return back();
        }

        // Parent orders cannot be cancelled directly — cancel each child shipment individually
        if ($order->children->count() > 0) {
            Toastr::error(translate('status_not_changable_now'));
            return back();
        }

        if ($order['payment_method'] == 'offline_payment') {
            Toastr::error(translate('The_order_status_cannot_be_updated_as_it_is_an_offline_payment'));
            return back();
        }

        if (!in_array($order['order_status'], ['pending', 'confirmed', 'processing'])) {
            Toastr::error(translate('status_not_changable_now'));
            return back();
        }

        $hasPhysical = $order->details->contains(fn($d) => ($d->product_type ?? 'physical') !== 'digital');
        if (!$hasPhysical) {
            Toastr::error(translate('status_not_changable_now'));
            return back();
        }

        OrderManager::stock_update_on_order_status_change($order, 'canceled');
        Order::where('id', $id)->update(['order_status' => 'canceled']);

        if ($order['payment_method'] == 'customer_wallet') {
            app(\App\Contracts\Repositories\WalletTransactionRepositoryInterface::class)
                ->addWalletTransaction(
                    user_id: $order['customer_id'],
                    amount: $order['order_amount'],
                    transactionType: 'order_refund',
                    reference: 'order_cancel_refund'
                );
        }

        // If this is a child shipment, check if all siblings are now terminal → cancel parent too
        if ($order->parent_order_id) {
            $parent = Order::with('children')->find($order->parent_order_id);
            if ($parent) {
                $terminal = ['canceled', 'returned', 'failed', 'delivered'];
                $allDone = $parent->children
                    ->every(fn($c) => $c->id === (int)$id || in_array($c->order_status, $terminal));
                if ($allDone) {
                    Order::where('id', $parent->id)->update(['order_status' => 'canceled']);
                }
            }
        }

        Toastr::success(translate('successfully_canceled'));
        return back();
    }

    public function refund_request(Request $request, $id): View|RedirectResponse
    {
        $orderDetails = OrderDetail::find($id);
        $user = auth('customer')->user();

        $loyaltyPointStatus = getWebConfig(name: 'loyalty_point_status');
        if ($loyaltyPointStatus == 1) {
            $loyaltyPoint = CustomerManager::count_loyalty_point_for_amount($id);
            if ($user['loyalty_point'] < $loyaltyPoint) {
                Toastr::warning(translate('you_have_not_sufficient_loyalty_point_to_refund_this_order') . '!!');
                return back();
            }
        }

        return view('web-views.users-profile.refund-request', [
            'order_details' => $orderDetails,
        ]);
    }

    public function store_refund(Request $request): RedirectResponse
    {
        $request->validate([
            'order_details_id' => 'required',
            'amount' => 'required',
            'refund_reason' => 'required'

        ]);
        $orderDetails = OrderDetail::find($request->order_details_id);
        $user = auth('customer')->user();


        $loyalty_point_status = getWebConfig(name: 'loyalty_point_status');
        if ($loyalty_point_status == 1) {
            $loyalty_point = CustomerManager::count_loyalty_point_for_amount($request->order_details_id);

            if ($user->loyalty_point < $loyalty_point) {
                Toastr::warning(translate('you_have_not_sufficient_loyalty_point_to_refund_this_order') . '!!');
                return back();
            }
        }

        $refundRequest = new RefundRequest;
        $refundRequest->order_details_id = $request->order_details_id;
        $refundRequest->customer_id = auth('customer')->id();
        $refundRequest->status = 'pending';
        $refundRequest->amount = $request->amount;
        $refundRequest->product_id = $orderDetails->product_id;
        $refundRequest->order_id = $orderDetails->order_id;
        $refundRequest->refund_reason = $request->refund_reason;

        if ($request->file('images')) {
            $images = [];
            foreach ($request->file('images') as $img) {
                $images[] = [
                    'image_name' => ImageManager::upload('refund/', 'webp', $img),
                    'storage' => getWebConfig(name: 'storage_connection_type') ?? 'public',
                ];
            }
            $refundRequest->images = $images;
        }
        $refundRequest->save();

        $orderDetails->refund_request = 1;
        $orderDetails->save();

        $order = Order::find($orderDetails->order_id);
        event(new RefundEvent(status: 'refund_request', order: $order, refund: $refundRequest, orderDetails: $orderDetails));

        Toastr::success(translate('refund_requested_successful!!'));
        return redirect()->route('account-order-details', ['id' => $orderDetails->order_id]);
    }

    public function generate_invoice($id)
    {
        $order = Order::with(['seller', 'shipping', 'customer'])->where('id', $id)->first();
        $data["email"] = $order->customer["email"];
        $data["order"] = $order;
        $invoiceSettings = getWebConfig(name: 'invoice_settings');
        $mpdf_view = \View::make(VIEW_FILE_NAMES['order_invoice'], compact('order', 'invoiceSettings'));
        $this->generatePdf(view: $mpdf_view, filePrefix: 'order_invoice_', filePostfix: $order['id'], pdfType: 'invoice', requestFrom: 'web');
    }

    public function refund_details($id)
    {
        $order_details = OrderDetail::find($id);

        if (!$order_details) {
            if (request()->ajax()) {
                return response()->json(['status' => 0, 'message' => translate('product_not_found')]);
            }
            Toastr::error(translate('product_not_found'));
            return redirect()->back();
        }

        $refund = RefundRequest::with(['product', 'order'])->where('customer_id', auth('customer')->id())
            ->where('order_details_id', $order_details->id)->first();
        $product = $this->product->find($order_details->product_id);
        $order = $this->order->find($order_details->order_id);

        if (request()->ajax()) {
            if ($product) {
                return response()->json([
                    'status' => 1,
                    'view' => view(VIEW_FILE_NAMES['refund_details'], compact('order_details', 'refund', 'product', 'order'))->render(),
                ]);
            }
            return response()->json(['status' => 0, 'message' => translate('product_not_found')]);
        }

        if ($product) {
            return view(VIEW_FILE_NAMES['refund_details'], compact('order_details', 'refund', 'product', 'order'));
        }

        Toastr::error(translate('product_not_found'));
        return redirect()->back();
    }

    public function refer_earn(Request $request): View|RedirectResponse
    {
        $refEarningStatus = getWebConfig(name: 'ref_earning_status') ?? 0;
        if (!$refEarningStatus) {
            Toastr::error(translate('you_have_no_permission'));
            return redirect('/');
        }
        $customer_detail = User::where('id', auth('customer')->id())->first();
        if (empty($customer_detail['referral_code'])) {
            User::where('id', auth('customer')->id())->update([
                'referral_code' => Helpers::generate_referer_code(),
            ]);
            $customer_detail = User::where('id', auth('customer')->id())->first();
        }

        return view(VIEW_FILE_NAMES['refer_earn'], compact('customer_detail'));
    }

    public function user_coupons(Request $request): View
    {
        $customerId = auth('customer')->id();
        $coupons = Coupon::active()->with('seller.shop')
            ->withCount(['order as used_count' => fn($q) => $q->where('customer_id', $customerId)])
            ->whereIn('customer_id', [$customerId, '0'])
            ->whereDate('start_date', '<=', date('Y-m-d'))
            ->whereDate('expire_date', '>=', date('Y-m-d'))
            ->paginate(8);

        return view(VIEW_FILE_NAMES['user_coupons'], compact('coupons'));
    }

    public function restockRequestsView(Request $request): View
    {
        $restockProducts = $this->restockProductRepo->getListWhere(
            orderBy: ['updated_at' => 'desc'],
            searchValue: $request['searchValue'],
            filters: ['customer_id' => auth('customer')->id()],
            relations: ['product.clearanceSale' => function ($query) {
                return $query->active();
            }],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT),
        );
        $productIdsArray = $restockProducts->pluck('product_id')->toArray();
        return view(VIEW_FILE_NAMES['user_restock_requests'], compact('restockProducts', 'productIdsArray'));
    }

    private function deleteSupportTicketAttachments(array|string|null $attachments): void
    {
        if (empty($attachments)) {
            return;
        }

        if (is_string($attachments)) {
            $decodedAttachments = json_decode($attachments, true);
            $attachments = json_last_error() === JSON_ERROR_NONE
                ? (is_array($decodedAttachments) ? $decodedAttachments : [$decodedAttachments])
                : [$attachments];
        }

        if (!is_array($attachments)) {
            return;
        }

        foreach ($attachments as $attachment) {
            $fileName = is_array($attachment) ? ($attachment['file_name'] ?? null) : $attachment;

            if ($fileName) {
                ImageManager::delete('support-ticket/' . $fileName);
            }
        }
    }

    public function deleteRestockRequest(Request $request): RedirectResponse
    {
        $customerId = auth('customer')->id();
        if ($request['id']) {
            $this->restockProductCustomerRepo->delete(params: ['restock_product_id' => $request['id'], 'customer_id' => $customerId]);
        } else {
            $this->restockProductCustomerRepo->delete(params: ['customer_id' => $customerId]);
        }

        $restockProducts = $this->restockProductRepo->getListWhere(relations: ['restockProductCustomers'], dataLimit: 'all');
        $restockProducts->map(function ($restockProduct) {
            if ($restockProduct->restockProductCustomers->count() === 0) {
                $this->restockProductRepo->delete(params: ['id' => $restockProduct['id']]);
            }
        });

        Toastr::success(translate('product_restock_request_removed_successfully'));
        return redirect()->route('user-restock-requests');
    }
}
