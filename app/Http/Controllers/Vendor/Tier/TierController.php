<?php

namespace App\Http\Controllers\Vendor\Tier;

use App\Http\Controllers\Controller;
use App\Library\Payer;
use App\Library\Payment as PaymentInfo;
use App\Library\Receiver;
use App\Models\ProductTier;
use App\Models\Seller;
use App\Models\VendorTier;
use App\Models\VendorTierPayment;
use App\Traits\Payment;
use App\Traits\PaymentGatewayTrait;
use function App\Utils\payment_gateways;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\SellerWallet;

class TierController extends Controller
{
    use Payment, PaymentGatewayTrait;

    public function index()
    {
        $seller = auth('seller')->user();
        if ($seller && (bool) $seller->first_login_after_approval) {
            $seller->update(['first_login_after_approval' => false]);
        }

        $tiers = ProductTier::where('is_active', true)
            ->where('monthly_fee_usd', '>', 0)
            ->get();
        $upgradeFrom = (float) request('upgrade_from', 0);
        if ($upgradeFrom > 0) {
            $tiers = $tiers->filter(fn($t) => (float)$t->monthly_fee_usd > $upgradeFrom)->values();
        }
        $sellerType = $seller->seller_type ?? 'individual';
        $activeTier = VendorTier::with('tier')
            ->where('seller_id', $seller->id)
            ->whereIn('status', ['active', 'trial'])
            ->where(function ($query) {
                $today = now()->toDateString();
                $query->where(fn ($active) => $active->where('status', 'active')->whereDate('end_date', '>=', $today))
                    ->orWhere(fn ($trial) => $trial->where('status', 'trial')->whereDate('trial_end_date', '>=', $today));
            })
            ->latest()
            ->first();
        $tierExpired = $sellerType !== 'freelancer'
            && !$activeTier
            && VendorTier::where('seller_id', $seller->id)
                ->whereIn('status', ['cancelled', 'ended', 'endende', 'expired'])
                ->latest()
                ->exists();
        $trialEligible = !$this->hasClaimedPaidTierTrial($seller->id);
        $productCategories = [
            (object) ['id' => 1, 'name' => 'One-time sales (low-value)'],
            (object) ['id' => 2, 'name' => 'One-time sales (mid-value)'],
            (object) ['id' => 3, 'name' => 'One-time sales (high-value)'],
            (object) ['id' => 4, 'name' => 'Recurring/monthly rentals'],
            (object) ['id' => 5, 'name' => 'High-value recurring rentals'],
            (object) ['id' => 6, 'name' => 'One-time niche products'],
        ];

        return view('vendor-views.tier.index', compact(
            'tiers',
            'productCategories',
            'sellerType',
            'activeTier',
            'tierExpired',
            'trialEligible'
        ));
    }

    /**
     * Selection Logic:
     * 1. Tier 6 or $0 Tiers -> ALWAYS FREE, 1 YEAR VALIDITY.
     * 2. Vendor who has never claimed a paid-tier trial -> FREE TRIAL.
     * 3. Expired/Standard User -> CHECKOUT (Subscription Tiers).
     */
    public function select(Request $request)
    {
        if ($redirect = $this->denyIfNotApproved()) return $redirect;

        $request->validate(['tier_id' => 'required|exists:admin_product_tiers,id']);

        $tier = ProductTier::find($request->tier_id);
        if (!$tier) {
            ToastMagic::error(translate('Selected_tier_not_found.'));
            return back();
        }

        $seller = auth('seller')->user();
        if (!$seller) {
            ToastMagic::error(translate('Please_login_to_continue.'));
            return redirect()->route('vendor.auth.login');
        }

        if (empty($seller->account_no) || empty($seller->bank_name)) {
            session([
                'pending_tier_id' => $request->tier_id,
                'pending_payment_method' => $request->payment_method,
            ]);
            ToastMagic::info(translate('Please_add_your_bank_details_before_subscribing_to_a_plan.'));
            return redirect()->route('vendor.profile.update-bank-info', [$seller->id]);
        }
        session()->forget(['pending_tier_id', 'pending_payment_method']);

        // --- RULE 1: TIER 6 OR FREE TIERS -> 1 YEAR VALIDITY / SKIP PAYMENT ---
        if ($tier->id == 6 || $tier->monthly_fee_usd == 0) {
            return $this->processZeroDollarTierActivation($tier);
        }

        // --- RULE 2: CHECK TRIAL ELIGIBILITY ---
        if (!$this->hasClaimedPaidTierTrial($seller->id)) {
            return $this->activateFreeTrialSubscription($tier);
        }

        // --- RULE 3: STANDARD PAYMENT ---
        return $this->redirectToCheckout($request->tier_id);
    }

    /**
     * RENEW Method (Called from Notification or My Tier Page)
     */
    public function renew(Request $request)
    {
        if ($redirect = $this->denyIfNotApproved()) return $redirect;

        $seller = auth('seller')->user();
        $tier = ProductTier::find($request->tier_id);
        $existingVendorTier = VendorTier::find($request->vendor_tier_record_id);

        if (!$tier || !$existingVendorTier || $existingVendorTier->seller_id !== $seller->id) {
            ToastMagic::error(translate('Plan_not_found'));
            return back();
        }

        $currentDate = now();
        
        // --- DATE CALCULATION LOGIC ---
        // 1. Start with the existing end date
        $effectiveDate = Carbon::parse($existingVendorTier->end_date);

        // 2. Check trial priority
        if ($existingVendorTier->status === 'trial' && $existingVendorTier->trial_end_date) {
            $trialEnd = Carbon::parse($existingVendorTier->trial_end_date);
            if ($trialEnd->gt($effectiveDate)) {
                $effectiveDate = $trialEnd;
            }
        }

        // 3. If expired, restart from TODAY
        if ($effectiveDate->isPast()) {
            $effectiveDate = $currentDate;
        }

        // 4. ADD 30 DAYS
        $newEndDate = $effectiveDate->copy()->addDays(30); 

        // 5. Handle Trial Data preservation
        $trial_end_date_str = null;
        $trial_commission_rate = null;
        
        if ($existingVendorTier->status === 'trial' && $existingVendorTier->trial_end_date) {
             if (Carbon::parse($existingVendorTier->trial_end_date)->isFuture()) {
                 $trial_end_date_str = $existingVendorTier->trial_end_date;
                 $trial_commission_rate = 15.00;
             }
        }

        // --- STORE IN SEPARATE SESSION KEY TO AVOID OVERWRITE ---
        // We use 'tier_renewal_data' to persist this logic until payment request
        session([
            'tier_renewal_data' => [
                'tier_id' => $tier->id,
                'billing_end_date' => $newEndDate->toDateTimeString(),
                'trial_end_date' => $trial_end_date_str,
                'trial_commission_rate' => $trial_commission_rate,
                'is_renewal' => true,
                'previous_record_id' => $existingVendorTier->id,
            ]
        ]);

        session(['selected_tier_id' => $tier->id]);
        
        return redirect()->route('vendor.tier.vendor-checkout-payment');
    }

    protected function redirectToCheckout($tier_id)
    {
        session(['selected_tier_id' => $tier_id]);
        // Clear old renewal data if just standard selecting
        session()->forget('tier_renewal_data');
        
        ToastMagic::success(translate('Tier_plan_selected._Proceed_to_payment.'));
        return redirect()->route('vendor.tier.vendor-checkout-payment');
    }

    public function vendorCheckoutPayment()
    {
        if ($redirect = $this->denyIfNotApproved()) return $redirect;

        $tierId = session('selected_tier_id');
        if (!$tierId) return redirect()->route('vendor.tier.index');

        $tier = ProductTier::find($tierId);
        $digitalPayment = getWebConfig(name:'digital_payment');
        $availablePaymentMethod = $digitalPayment && count(payment_gateways()) > 0 ? ['payment_gateways'] : [];
        $amount = $tier->monthly_fee_usd ?? 0;

        $seller = auth('seller')->user();
        $wallet = SellerWallet::where('seller_id', $seller->id)->first();
        $walletBalance = $wallet ? (float) $wallet->total_earning : 0;

        return view('vendor-views.tier.vendor_payment', [
            'tier' => $tier,
            'amount' => $amount,
            'digital_payment' => getWebConfig(name: 'digital_payment'),
            'payment_gateways_list' => payment_gateways(),
            'activeMinimumMethods' => count($availablePaymentMethod) > 0,
            'walletBalance' => $walletBalance,
            'walletSufficient' => $walletBalance >= $amount,
        ]);
    }

    public function payWithWallet(Request $request)
    {
        if ($redirect = $this->denyIfNotApproved()) return $redirect;

        $request->validate(['tier_id' => 'required|integer|regex:/^\d+$/|exists:admin_product_tiers,id']);

        $seller = auth('seller')->user();
        $tier = ProductTier::find($request->tier_id);
        $wallet = SellerWallet::where('seller_id', $seller->id)->first();
        $amount = $tier->monthly_fee_usd ?? 0;

        if (!$wallet || $wallet->total_earning < $amount) {
            ToastMagic::error(translate('Insufficient_wallet_balance.'));
            return back();
        }

        $currentDate = now();
        $renewalData = session('tier_renewal_data');
        $isValidRenewal = $renewalData && isset($renewalData['tier_id']) && $renewalData['tier_id'] == $tier->id;

        if ($isValidRenewal) {
            $billingEndDate = $renewalData['billing_end_date'];
            $trial_end_date_str = $renewalData['trial_end_date'];
            $trial_commission_rate = $renewalData['trial_commission_rate'];
            $is_renewal = true;
            $previous_record_id = $renewalData['previous_record_id'];
        } else {
            $billingEndDate = $currentDate->copy()->addDays(30)->subDay()->toDateTimeString();
            $trial_end_date_str = null;
            $trial_commission_rate = null;
            $is_renewal = false;
            $previous_record_id = null;
        }

        $transaction_id = 'VTX_WALLET_' . Str::uuid();

        try {
            DB::beginTransaction();

            $vendorTierId = null;

            if ($is_renewal && $previous_record_id) {
                $existingTier = VendorTier::find($previous_record_id);
                if ($existingTier) {
                    $existingTier->update([
                        'end_date' => Carbon::parse($billingEndDate),
                        'status' => 'active',
                        'last_payment_method' => 'seller_wallet',
                        'renewal_date' => Carbon::parse($billingEndDate)->addDay(),
                        'updated_at' => now(),
                    ]);
                    $vendorTierId = $existingTier->id;
                    Product::where('vendor_tier_id', $vendorTierId)
                        ->where('request_status', 3)
                        ->update(['status' => 1, 'request_status' => 1, 'updated_at' => now()]);
                    cacheRemoveByType(type: 'products');
                }
            }

            if (!$vendorTierId) {
                $vendorTier = VendorTier::create([
                    'seller_id' => $seller->id,
                    'product_tier_id' => $tier->id,
                    'monthly_fee_usd' => $amount,
                    'sales_commission_rate' => $tier->sales_commission_rate,
                    'trial_commission_rate' => $trial_commission_rate,
                    'start_date' => $currentDate,
                    'trial_end_date' => $trial_end_date_str ? Carbon::parse($trial_end_date_str) : null,
                    'end_date' => Carbon::parse($billingEndDate),
                    'status' => 'active',
                    'last_payment_method' => 'seller_wallet',
                    'is_auto_renew' => true,
                    'renewal_date' => Carbon::parse($billingEndDate)->addDay(),
                ]);
                $vendorTierId = $vendorTier->id;
            }

            VendorTierPayment::create([
                'vendor_tier_id' => $vendorTierId,
                'billing_start_date' => $currentDate,
                'billing_end_date' => Carbon::parse($billingEndDate),
                'amount_paid' => $amount,
                'currency' => 'USD',
                'payment_method' => 'seller_wallet',
                'transaction_id' => $transaction_id,
                'status' => 'completed',
                'payment_status' => 'completed',
                'paid_at' => now(),
            ]);

            $wallet->decrement('total_earning', $amount);
            $wallet->increment('withdrawn', $amount);

            DB::commit();

            session()->forget(['selected_tier_id', 'tier_renewal_data']);
            ToastMagic::success(translate('Payment_successful!_Your_plan_is_active.'));
            return redirect()->route('vendor.tier.my-tier-plan');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet Tier Payment Error: ' . $e->getMessage());
            ToastMagic::error(translate('Payment_failed._Please_try_again.'));
            return back();
        }
    }

    /**
     * Generates Payment Request.
     * Merges Renewal Data (if exists) with Standard Data.
     */
    public function vendorTierPaymentRequest(Request $request)
    {
        if ($redirect = $this->denyIfNotApproved()) return $redirect;

        $request->merge([
            'payment_method' => $request->input('online_payment')
        ]);

        $seller = auth('seller')->user();
        $tier = ProductTier::find($request->tier_id);
        
        if (!$seller || !$tier) {
            ToastMagic::error(translate('Invalid_session_or_tier.'));
            return back();
        }

        $currentDate = now();
        $amount = $tier->monthly_fee_usd ?? 0;
        $currency_code = 'USD';
        $transaction_id = 'VTX_' . Str::uuid();
        
        // --- CHECK FOR RENEWAL DATA IN SESSION ---
        $renewalData = session('tier_renewal_data');
        
        // Check if renewal data matches current tier request
        $isValidRenewal = $renewalData && isset($renewalData['tier_id']) && $renewalData['tier_id'] == $tier->id;

        if ($isValidRenewal) {
            // CASE A: RENEWAL FLOW (Use calculated dates from renew method)
            $billingEndDate = $renewalData['billing_end_date'];
            $trial_end_date_str = $renewalData['trial_end_date'];
            $trial_commission_rate = $renewalData['trial_commission_rate'];
            $is_renewal = true;
            $previous_record_id = $renewalData['previous_record_id'];
        } 
        else {
            // CASE B: STANDARD PURCHASE FLOW
            $subscription_end_date = $currentDate->copy()->addDays(30)->subDay();
            $billingEndDate = $subscription_end_date->toDateTimeString();
            
            // A paid purchase never inherits an existing trial. The 60-day
            // trial is a one-time vendor benefit, not one trial per tier.
            $trial_end_date_str = null;
            $trial_commission_rate = null;
            $is_renewal = false;
            $previous_record_id = null;
        }

        // Build Final Meta Data
        session([
            'payment_meta_data' => [
                'seller_id' => $seller->id,
                'tier_id' => $tier->id,
                'tier_name' => $tier->name,
                'monthly_fee_usd' => $amount,
                'sales_commission_rate' => $tier->sales_commission_rate,
                'trial_commission_rate' => $trial_commission_rate,
                'billing_start_date' => $currentDate->toDateTimeString(),
                'billing_end_date' => $billingEndDate,
                'trial_end_date' => $trial_end_date_str,
                'is_first_time_free_plan' => false,
                'is_renewal' => $is_renewal, // Correctly passed flag
                'previous_record_id' => $previous_record_id, // Correctly passed ID
                'payment_method' => $request['payment_method']
            ],
            'pending_transaction_id' => $transaction_id
        ]);

        $additionalData = [
            'seller_id' => $seller->id,
            'tier_id' => $tier->id,
            'tier_name' => $tier->name,
            'transaction_id' => $transaction_id,
            'payment_mode' => $request->has('payment_platform') ? $request['payment_platform'] : 'web',
            'business_name' => getWebConfig(name: 'company_name'),
            'business_logo' => dynamicAsset(path: 'public/assets/front-end/img/finxcart-payment-logo.png'),
        ];

        $payer = new Payer(
            $seller->shop->name ?? $seller->f_name,
            $seller->email,
            $seller->phone ?? '',
            ''
        );

        $paymentInfo = new PaymentInfo(
            success_hook: 'vendor_tier_payment_success',
            failure_hook: 'vendor_tier_payment_fail',
            currency_code: $currency_code,
            payment_method: $request['payment_method'],
            payment_platform: $request['payment_platform'],
            payer_id: $seller->id,
            receiver_id: 100,
            additional_data: $additionalData,
            payment_amount: $amount,
            external_redirect_link: route('vendor.tier.payment-success'),
            attribute: 'vendor_tier',
            attribute_id: $transaction_id
        );

        $receiverInfo = new Receiver('Admin', 'admin_logo.png');

        return redirect(Payment::generate_link($payer, $paymentInfo, $receiverInfo));
    }

    /**
     * Handles the successful payment callback.
     */
    public function vendorTierPaymentSuccess(Request $request)
    {
        $failure_flags = ['fail', 'failed', 'cancelled', 'cancel', 'error'];
        $status_param = $request->get('flag') ?? $request->get('status') ?? $request->get('payment_status');

        if ($status_param && in_array(strtolower($status_param), $failure_flags)) {
            return $this->vendorTierPaymentFail($request);
        }

        $seller = auth('seller')->user();
        $transaction_id = session('pending_transaction_id') ?? $request->get('transaction_id') ?? $request->get('token');
        $metaData = session('payment_meta_data');

        if (!$seller || !$transaction_id || !$metaData) {
            ToastMagic::error(translate('Payment_session_invalid._Please_contact_support_if_you_were_charged.'));
            session()->forget(['pending_transaction_id', 'payment_meta_data', 'selected_tier_id', 'tier_renewal_data']);
            return redirect()->route('vendor.tier.index');
        }

        if (isset($metaData['seller_id']) && (int)$metaData['seller_id'] !== (int)auth('seller')->id()) {
            ToastMagic::error(translate('access_denied'));
            session()->forget(['pending_transaction_id', 'payment_meta_data', 'selected_tier_id', 'tier_renewal_data']);
            return redirect()->route('vendor.tier.index');
        }

        if (VendorTierPayment::where('transaction_id', $transaction_id)->exists()) {
            ToastMagic::warning(translate('Payment_already_processed.'));
            return redirect()->route('vendor.tier.my-tier-plan');
        }

        try {
            DB::beginTransaction();
            
            $status = !empty($metaData['trial_end_date']) ? 'trial' : 'active';
            $vendorTierId = null;

            // --- UPDATE EXISTING RECORD IF RENEWAL ---
            if (isset($metaData['is_renewal']) && $metaData['is_renewal'] && isset($metaData['previous_record_id'])) {
                
                $existingTier = VendorTier::where('id', $metaData['previous_record_id'])
                    ->where('seller_id', auth('seller')->id())
                    ->first();
                
                if ($existingTier) {
                    $existingTier->update([
                        'end_date' => Carbon::parse($metaData['billing_end_date']),
                        'status' => 'active',
                        'last_payment_method' => $metaData['payment_method'] ?? 'gateway_confirmed',
                        'renewal_date' => Carbon::parse($metaData['billing_end_date'])->addDay(),
                        'trial_end_date' => $metaData['trial_end_date'] ? Carbon::parse($metaData['trial_end_date']) : $existingTier->trial_end_date,
                        'updated_at' => now(),
                    ]);
                    
                    $vendorTierId = $existingTier->id;
                    
                    // Reactivate Products
                    Product::where('vendor_tier_id', $vendorTierId)
                        ->where('request_status', 3)
                        ->update([
                            'status' => 1,
                            'request_status' => 1,
                            'updated_at' => now()
                        ]);
                    cacheRemoveByType(type: 'products');

                    Log::info("Renewed Tier #{$vendorTierId} for Seller {$seller->id}.");
                }
            }

            // --- CREATE NEW RECORD IF NOT RENEWAL ---
            if (!$vendorTierId) {
                $vendorTier = VendorTier::create([
                    'seller_id' => auth('seller')->id(),
                    'product_tier_id' => $metaData['tier_id'],
                    'monthly_fee_usd' => $metaData['monthly_fee_usd'],
                    'sales_commission_rate' => $metaData['sales_commission_rate'],
                    'trial_commission_rate' => $metaData['trial_commission_rate'] ?? null,
                    'start_date' => Carbon::parse($metaData['billing_start_date']),
                    'trial_end_date' => $metaData['trial_end_date'] ? Carbon::parse($metaData['trial_end_date']) : null,
                    'end_date' => Carbon::parse($metaData['billing_end_date']),
                    'status' => $status,
                    'last_payment_method' => $metaData['payment_method'] ?? 'gateway_confirmed',
                    'is_auto_renew' => true,
                    'renewal_date' => Carbon::parse($metaData['billing_end_date'])->addDay(),
                ]);
                $vendorTierId = $vendorTier->id;
            }

            // --- CREATE PAYMENT RECORD ---
            VendorTierPayment::create([
                'vendor_tier_id' => $vendorTierId,
                'billing_start_date' => Carbon::parse($metaData['billing_start_date']),
                'billing_end_date' => Carbon::parse($metaData['billing_end_date']),
                'amount_paid' => $metaData['monthly_fee_usd'],
                'currency' => $request->get('currency_code') ?? 'USD',
                'payment_method' => $request->get('payment_method') ?? 'gateway_confirmed',
                'transaction_id' => $transaction_id,
                'status' => 'completed',
                'payment_status' => 'completed',
                'paid_at' => now(),
                'payment_meta' => json_encode($metaData),
            ]);

            $savedProduct = null;
            if (session()->has('pending_product')) {
                $savedProduct = app(\App\Http\Controllers\Vendor\Product\ProductController::class)
                    ->savePendingProductFromSession($vendorTierId, app(\App\Services\ProductService::class));
            }

            DB::commit();

            session()->forget(['pending_transaction_id', 'payment_meta_data', 'selected_tier_id', 'tier_renewal_data']);
            if ($savedProduct) {
                session()->flash('clear_pending_product_stage', true);
            }
            ToastMagic::success(translate('Payment_successful!_Your_plan_is_active.'));
            return redirect()->route('vendor.tier.my-tier-plan');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vendor Tier Payment Error: ' . $e->getMessage());
            ToastMagic::error(translate('Payment_was_successful_but_activation_failed.'));
            return redirect()->route('vendor.tier.index');
        }
    }

    public function vendorTierPaymentFail(Request $request)
    {
        session()->forget(['pending_transaction_id', 'payment_meta_data', 'selected_tier_id', 'tier_renewal_data']);
        ToastMagic::error(translate('Payment_failed.'));
        return redirect()->route('vendor.tier.index');
    }

    protected function processZeroDollarTierActivation(ProductTier $tier)
    {
        $seller = auth('seller')->user();
        $currentDate = now();
        // 1 Year Validity
        $billingEndDate = $currentDate->copy()->addYear()->subDay()->toDateTimeString();
        $transaction_id = 'VTX_FREE_' . Str::uuid();

        $metaData = [
            'seller_id' => $seller->id,
            'tier_id' => $tier->id,
            'tier_name' => $tier->name,
            'monthly_fee_usd' => 0,
            'sales_commission_rate' => $tier->sales_commission_rate,
            'trial_commission_rate' => null, 
            'billing_start_date' => $currentDate->toDateTimeString(),
            'billing_end_date' => $billingEndDate,
            'trial_end_date' => null, 
            'payment_method' => 'one_time_skipped_payment',
        ];

        try {
            DB::beginTransaction();

            $vendorTier = VendorTier::create([
                'seller_id' => $metaData['seller_id'],
                'product_tier_id' => $metaData['tier_id'],
                'monthly_fee_usd' => 0,
                'sales_commission_rate' => $metaData['sales_commission_rate'],
                'trial_commission_rate' => null, 
                'start_date' => Carbon::parse($metaData['billing_start_date']),
                'trial_end_date' => null,
                'end_date' => Carbon::parse($metaData['billing_end_date']),
                'status' => 'active',
                'last_payment_method' => 'one_time_skipped_payment',
                'is_auto_renew' => false, 
                'renewal_date' => null,
            ]);

            VendorTierPayment::create([
                'vendor_tier_id' => $vendorTier->id,
                'billing_start_date' => Carbon::parse($metaData['billing_start_date']),
                'billing_end_date' => Carbon::parse($metaData['billing_end_date']),
                'amount_paid' => 0,
                'currency' => 'USD',
                'payment_method' => 'one_time_skipped_payment',
                'transaction_id' => $transaction_id,
                'status' => 'completed',
                'payment_status' => 'free',
                'paid_at' => now(),
                'payment_meta' => json_encode($metaData),
            ]);

            DB::commit();

            if (session()->has('pending_product')) {
                app(\App\Http\Controllers\Vendor\Product\ProductController::class)
                    ->savePendingProductFromSession($vendorTier->id, app(\App\Services\ProductService::class));
            }

            ToastMagic::success(translate('Tier_activated_successfully!'));
            return redirect()->route('vendor.tier.my-tier-plan');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('vendor.tier.index');
        }
    }

    protected function activateFreeTrialSubscription(ProductTier $tier)
    {
        $seller = auth('seller')->user();
        if ($this->hasClaimedPaidTierTrial($seller->id)) {
            ToastMagic::info(translate('Your_one-time_60-day_trial_has_already_been_used._Please_complete_payment_to_activate_this_tier.'));
            return $this->redirectToCheckout($tier->id);
        }

        $currentDate = now();
        $transaction_id = 'VTX_TRIAL_' . Str::uuid();

        $trial_end_date = $currentDate->copy()->addDays(60);
        $billing_end_date = $trial_end_date->copy();

        $metaData = [
            'seller_id' => $seller->id,
            'tier_id' => $tier->id,
            'tier_name' => $tier->name,
            'monthly_fee_usd' => 0, 
            'sales_commission_rate' => $tier->sales_commission_rate,
            'trial_commission_rate' => 15.00, 
            'billing_start_date' => $currentDate->toDateTimeString(),
            'billing_end_date' => $billing_end_date->toDateTimeString(),
            'trial_end_date' => $trial_end_date->toDateTimeString(),
            'is_first_time_free_plan' => true,
            'payment_method' => 'free_trial_access',
        ];

        try {
            DB::beginTransaction();

            Seller::whereKey($seller->id)->lockForUpdate()->first();
            if ($this->hasClaimedPaidTierTrial($seller->id)) {
                DB::rollBack();
                ToastMagic::info(translate('Your_one-time_60-day_trial_has_already_been_used._Please_complete_payment_to_activate_this_tier.'));
                return $this->redirectToCheckout($tier->id);
            }

            $vendorTier = VendorTier::create([
                'seller_id' => $metaData['seller_id'],
                'product_tier_id' => $metaData['tier_id'],
                'monthly_fee_usd' => $metaData['monthly_fee_usd'],
                'sales_commission_rate' => $metaData['sales_commission_rate'],
                'trial_commission_rate' => $metaData['trial_commission_rate'],
                'start_date' => Carbon::parse($metaData['billing_start_date']),
                'trial_end_date' => Carbon::parse($metaData['trial_end_date']),
                'end_date' => Carbon::parse($metaData['billing_end_date']),
                'status' => 'trial',
                'last_payment_method' => $metaData['payment_method'],
                'is_auto_renew' => true,
                'renewal_date' => Carbon::parse($metaData['billing_end_date'])->addDay(),
            ]);

            VendorTierPayment::create([
                'vendor_tier_id' => $vendorTier->id,
                'billing_start_date' => Carbon::parse($metaData['billing_start_date']),
                'billing_end_date' => Carbon::parse($metaData['billing_end_date']),
                'amount_paid' => 0,
                'currency' => 'USD',
                'payment_method' => $metaData['payment_method'],
                'transaction_id' => $transaction_id,
                'status' => 'completed',
                'payment_status' => 'free',
                'paid_at' => now(),
                'payment_meta' => json_encode($metaData),
            ]);

            DB::commit();

            if (session()->has('pending_product')) {
                app(\App\Http\Controllers\Vendor\Product\ProductController::class)
                    ->savePendingProductFromSession($vendorTier->id, app(\App\Services\ProductService::class));
            }

            ToastMagic::success(translate('Free_trial_started!_Enjoy_60_days_of_benefits.'));

            return redirect()->route('vendor.tier.my-tier-plan');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('vendor.tier.index');
        }
    }

    private function denyIfNotApproved(): ?\Illuminate\Http\RedirectResponse
    {
        if (auth('seller')->user()?->status !== 'approved') {
            ToastMagic::warning(translate('Your_account_is_pending_admin_approval._You_cannot_purchase_a_tier_yet.'));
            return redirect()->route('vendor.tier.index');
        }
        return null;
    }

    protected function hasClaimedPaidTierTrial(int $sellerId): bool
    {
        return VendorTier::where('seller_id', $sellerId)
            ->whereNotNull('trial_end_date')
            ->whereHas('tier', fn ($query) => $query->where('monthly_fee_usd', '>', 0))
            ->exists();
    }

    public function eContract(Request $request)
    {
        $vendorTierId = $request->vendor_tier_id;
        $seller = auth('seller')->user();
        $vendorTier = VendorTier::with('tier')->find($vendorTierId);
        if (!$vendorTier || $vendorTier->seller_id !== $seller->id) {
            return redirect()->route('vendor.tier.my-tier-plan');
        }
        return view('vendor-views.contracts.contract', [
            'vendorTier' => $vendorTier,
            'tier_name' => $vendorTier->tier->name ?? 'N/A',
        ]);
    }

   public function myTier()
    {
        $seller = auth('seller')->user();
        if (!$seller) return redirect()->route('vendor.auth.login');

        $subscriptions = VendorTier::with('tier')
            ->withCount(['products' => fn($q) => $q->where('added_by', 'seller')
                ->where('user_id', $seller->id)
                ->where('request_status', '!=', 3)])
            ->where('seller_id', $seller->id)
            ->latest()
            ->get();

        $paymentHistory = VendorTierPayment::with('vendorTier.tier')
            ->whereHas('vendorTier', function($q) use ($seller) {
                $q->where('seller_id', $seller->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $maxTierFee = ProductTier::where('is_active', true)->where('monthly_fee_usd', '>', 0)->max('monthly_fee_usd') ?? 0;
        return view('vendor-views.tier.my_tier_plan', compact('subscriptions', 'paymentHistory', 'maxTierFee'));
    }

    public function renewSubscriptionLink(Request $request)
        {
            // Ensure authentication
            if (!auth('seller')->check()) {
                // Store the intended URL so they are redirected back here after login
                session()->put('url.intended', url()->full());
                ToastMagic::warning(translate('Please_login_to_renew_your_plan.'));
                return redirect()->route('vendor.auth.login');
            }
    
            $seller = auth('seller')->user();
            $tier = ProductTier::find($request->tier_id);
            $existingVendorTier = VendorTier::find($request->vendor_tier_record_id);
    
            // Validation with redirection to Index instead of 'back'
            if (!$tier || !$existingVendorTier || $existingVendorTier->seller_id != $seller->id) {
                ToastMagic::error(translate('Invalid_renewal_link_or_plan_not_found.'));
                return redirect()->route('vendor.tier.index');
            }
    
            // --- REUSE RENEWAL LOGIC ---
            // We simply call the renew logic passing the request object since it contains the same IDs
            return $this->renew($request);
        }


    public function cancel(Request $request)
    {
        $request->validate([
            'vendor_tier_id' => 'required|integer|regex:/^\d+$/|exists:vendor_tiers,id'
        ]);

        $seller = auth('seller')->user();
        
        // Find the tier ensuring it belongs to the authenticated seller
        $vendorTier = VendorTier::where('id', $request->vendor_tier_id)
            ->where('seller_id', $seller->id)
            ->first();

        if (!$vendorTier) {
            ToastMagic::error(translate('Plan_not_found_or_access_denied.'));
            return back();
        }

        try {
            DB::beginTransaction();

            // 1. Update Tier Status to 'cancelled'
            $vendorTier->update([
                'status' => 'cancelled',
                'updated_at' => now()
            ]);

            // 2. Disable products linked to this tier
            $affectedProducts = Product::where('vendor_tier_id', $vendorTier->id)->update([
                'status' => 0,
                'featured_status' => 0,
                'request_status' => 3,
                'updated_at' => now()
            ]);

            // 3. If no other active tier remains, disable all remaining active products (vendor_tier_id = null)
            $hasOtherActiveTier = VendorTier::where('seller_id', $seller->id)
                ->where('id', '!=', $vendorTier->id)
                ->whereIn('status', ['active', 'trial'])
                ->where(function ($q) {
                    $today = now()->toDateString();
                    $q->where(fn($a) => $a->where('status', 'active')->whereDate('end_date', '>=', $today))
                      ->orWhere(fn($t) => $t->where('status', 'trial')->whereDate('trial_end_date', '>=', $today));
                })
                ->exists();

            if (!$hasOtherActiveTier) {
                $affectedProducts += Product::where('user_id', $seller->id)
                    ->where('added_by', 'seller')
                    ->where('status', 1)
                    ->update(['status' => 0, 'featured_status' => 0, 'request_status' => 3, 'updated_at' => now()]);
            }

            DB::commit();

            cacheRemoveByType(type: 'products');

            Log::info("Seller {$seller->id} cancelled Tier #{$vendorTier->id}. {$affectedProducts} products deactivated.");
            ToastMagic::success(translate('Plan_cancelled_successfully._Associated_products_have_been_deactivated.'));
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Tier Cancellation Error: " . $e->getMessage());
            ToastMagic::error(translate('Failed_to_cancel_plan.'));
        }

        return redirect()->route('vendor.tier.my-tier-plan');
    }

}

