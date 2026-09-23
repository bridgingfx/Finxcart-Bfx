<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\FreelancerHireRequest;
use App\Library\Payer;
use App\Library\Payment as PaymentInfo;
use App\Library\Receiver;
use App\Models\FreelancerContract;
use App\Models\FreelancerService;
use App\Services\FreelancerContractService;
use App\Services\FreelancerOrderService;
use App\Traits\Payment;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use RuntimeException;

class FreelancerHireController extends Controller
{
    use Payment;

    public function __construct(
        private readonly FreelancerContractService $contractService,
        private readonly FreelancerOrderService $orderService,
    ) {
    }

    public function create(FreelancerService $service): View|RedirectResponse
    {
        $service->loadMissing('seller.shop', 'packages');

        if (!$this->isHireable($service)) {
            Toastr::error(translate('this_service_is_not_available_for_hire_right_now'));
            return redirect()->route('hire-freelancer.show', $service->seller_id);
        }

        $quoteRequestId = request()->integer('quote_request_id') ?: null;
        $packageTier = request()->string('package')->toString() ?: null;

        try {
            if ($quoteRequestId) {
                $this->orderService->getQuoteForCheckout($quoteRequestId, auth('customer')->id());
            } else {
                $this->orderService->assertPurchasableDirect($service, $packageTier);
            }
        } catch (RuntimeException $exception) {
            Toastr::error($exception->getMessage());
            return redirect()->route('hire-freelancer.service-show', $service->id);
        }

        $selectedPackage = $quoteRequestId ? null : $this->orderService->getDirectHirePackage($service, $packageTier);
        $displayPrice = $quoteRequestId
            ? $this->orderService->getQuoteForCheckout($quoteRequestId, auth('customer')->id())->quoted_price
            : $this->orderService->getDirectHireAmount($service, $packageTier);

        return view(VIEW_FILE_NAMES['freelancer_hire_create'], compact('service', 'quoteRequestId', 'selectedPackage', 'packageTier', 'displayPrice'));
    }

    /**
     * The customer pays via Stripe first — the contract itself is only created
     * once payment actually clears, in freelancer_hire_payment_success(). This
     * mirrors how the store's own checkout never creates an Order until payment
     * succeeds (see PaymentController::getCustomerPaymentRequest / digital_payment_success).
     */
    public function checkout(FreelancerHireRequest $request, FreelancerService $service): RedirectResponse
    {
        if (!$this->isHireable($service)) {
            Toastr::error(translate('this_service_is_not_available_for_hire_right_now'));
            return redirect()->route('hire-freelancer.show', $service->seller_id);
        }

        $customer = auth('customer')->user();
        $quoteRequestId = $request->input('quote_request_id');
        $packageTier = $request->input('package');

        try {
            if ($quoteRequestId) {
                $chargeAmount = (float) $this->orderService->getQuoteForCheckout((int) $quoteRequestId, $customer->id)->quoted_price;
            } else {
                $this->orderService->assertPurchasableDirect($service, $packageTier);
                $chargeAmount = $this->orderService->getDirectHireAmount($service, $packageTier);
            }
        } catch (RuntimeException $exception) {
            Toastr::error($exception->getMessage());
            return redirect()->route('hire-freelancer.show', $service->seller_id);
        }

        $payer = new Payer(
            trim($customer->f_name . ' ' . $customer->l_name),
            $customer->email,
            $customer->phone,
            '',
        );

        $additionalData = [
            'freelancer_service_id' => $service->id,
            'customer_id' => $customer->id,
            'scope' => $request->input('scope'),
            'freelancer_quote_request_id' => $request->input('quote_request_id'),
            'package_tier' => $packageTier,
            'business_name' => getWebConfig(name: 'company_name'),
            'business_logo' => dynamicAsset(path: 'public/assets/front-end/img/finxcart-payment-logo.png'),
            'payment_mode' => 'web',
        ];

        $paymentInfo = new PaymentInfo(
            success_hook: 'freelancer_hire_payment_success',
            failure_hook: 'freelancer_hire_payment_fail',
            currency_code: 'USD',
            payment_method: 'stripe',
            payment_platform: 'web',
            payer_id: $customer->id,
            receiver_id: $service->seller_id,
            additional_data: $additionalData,
            payment_amount: $chargeAmount,
            external_redirect_link: route('hire.payment-result'),
            attribute: 'freelancer_hire',
            attribute_id: $service->id,
        );

        $receiverInfo = new Receiver(
            $service->seller?->shop?->name ?: trim($service->seller?->f_name . ' ' . $service->seller?->l_name),
            'example.png',
        );

        $redirectLink = $this->generate_link($payer, $paymentInfo, $receiverInfo);

        if (!$redirectLink) {
            Toastr::error(translate('payment_gateway_is_not_available_right_now'));
            return redirect()->route('hire-freelancer.show', $service->seller_id);
        }

        return redirect($redirectLink);
    }

    public function paymentResult(): View
    {
        $flag = request('flag');
        $contract = null;

        if ($flag === 'success') {
            $contractId = session()->pull('freelancer_hire_last_contract_id');
            $contract = $contractId ? FreelancerContract::find($contractId) : null;
        }

        return view(VIEW_FILE_NAMES['freelancer_hire_payment_result'], compact('flag', 'contract'));
    }

    private function isHireable(FreelancerService $service): bool
    {
        $hasPackagePrice = $service->packages->contains(fn ($package) => $package->is_enabled && $package->price !== null);

        return $service->is_active
            && $service->seller
            && $service->seller->status === 'approved'
            && $service->seller->account_status === 'active'
            && $service->seller->seller_type === 'freelancer'
            && ($service->price !== null || $hasPackagePrice);
    }
}

