<?php

namespace App\Services;

use App\Enums\Freelancer\DeliveryStatus;
use App\Enums\Freelancer\QuoteStatus;
use App\Enums\Freelancer\VerdictStatus;
use App\Models\AdminNotification;
use App\Models\FreelancerContract;
use App\Models\FreelancerQuoteRequest;
use App\Models\FreelancerService;
use App\Models\FreelancerServicePackage;
use App\Notifications\Freelancer\HireConfirmedNotification;
use App\Notifications\Freelancer\HiredNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Named OrderService per the freelancer-service lifecycle spec — kept in the
 * flat App\Services namespace with a Freelancer prefix (matching this app's
 * existing FreelancerContractService/FreelancerServiceService/... convention)
 * rather than App\Services\OrderService, which already exists for physical
 * store orders and is an unrelated domain.
 *
 * Two ways into an order — purchaseDirect() (fixed-price, no quote) and
 * purchaseFromQuote() (an accepted quote sets the price) — branch only on
 * validation and on how the amount/milestones are built. Everything after
 * that (contract creation, wallet crediting, audit log, notifications) is
 * shared in createOrder() so delivery/verdict/notification/audit logic is
 * never duplicated between the two paths.
 */
class FreelancerOrderService
{
    public function __construct(
        private readonly FreelancerContractService $contractService,
        private readonly FreelancerQuoteService $quoteService,
        private readonly CommissionService $commissionService,
        private readonly FreelancerAuditLogService $auditLog,
    ) {
    }

    /**
     * Guard used before building the payment-gateway redirect — kept in the
     * service layer so the "is this service eligible" rule lives in one
     * place rather than being re-implemented at the controller.
     */
    public function assertPurchasableDirect(FreelancerService $service, ?string $packageTier = null): void
    {
        if ($packageTier) {
            $this->getDirectHirePackage($service, $packageTier);
            return;
        }

        if ($service->price === null) {
            throw new RuntimeException(translate('this_service_requires_a_quote_before_it_can_be_purchased'));
        }
    }

    public function getDirectHirePackage(FreelancerService $service, ?string $packageTier): ?FreelancerServicePackage
    {
        if (!$packageTier) {
            return null;
        }

        $package = $service->packages()
            ->where('tier', $packageTier)
            ->where('is_enabled', true)
            ->first();

        if (!$package || $package->price === null) {
            throw new RuntimeException(translate('selected_package_is_not_available'));
        }

        return $package;
    }

    public function getDirectHireAmount(FreelancerService $service, ?string $packageTier = null): float
    {
        $package = $this->getDirectHirePackage($service, $packageTier);

        return $package ? (float) $package->price : (float) $service->price;
    }

    /**
     * Resolves + validates the quote a checkout is being built from, so the
     * controller can read the negotiated price without duplicating the
     * ownership/status/pricing-type checks that purchaseFromQuote() also runs
     * at webhook time.
     */
    public function getQuoteForCheckout(int $quoteRequestId, int $customerId): FreelancerQuoteRequest
    {
        $quote = FreelancerQuoteRequest::with('service')
            ->where('id', $quoteRequestId)
            ->where('customer_id', $customerId)
            ->firstOrFail();

        if (!$quote->service) {
            throw new RuntimeException(translate('this_service_does_not_use_the_quote_first_flow'));
        }

        if ($quote->status !== QuoteStatus::Quoted) {
            throw new RuntimeException(translate('this_quote_is_not_ready_to_be_accepted'));
        }

        return $quote;
    }

    public function purchaseDirect(
        FreelancerService $service,
        int $customerId,
        string $scope,
        ?string $paymentRequestId = null,
        ?string $packageTier = null,
    ): FreelancerContract {
        $this->assertPurchasableDirect($service, $packageTier);
        $package = $this->getDirectHirePackage($service, $packageTier);
        $amount = $package ? (float) $package->price : (float) $service->price;
        $milestoneTitle = $package
            ? (translate($package->tier) . ' - ' . translate('full_delivery'))
            : translate('full_delivery');

        return $this->createOrder(
            service: $service,
            customerId: $customerId,
            scope: $scope,
            amount: $amount,
            milestones: [['title' => $milestoneTitle, 'amount' => $amount]],
            paymentRequestId: $paymentRequestId,
            quoteRequestId: null,
        );
    }

    public function purchaseFromQuote(
        int $quoteRequestId,
        int $customerId,
        string $scope,
        ?string $paymentRequestId = null,
    ): FreelancerContract {
        $quote = FreelancerQuoteRequest::with('service')
            ->where('id', $quoteRequestId)
            ->where('customer_id', $customerId)
            ->firstOrFail();

        if (!$quote->service) {
            throw new RuntimeException(translate('this_service_does_not_use_the_quote_first_flow'));
        }

        return $this->createOrder(
            service: $quote->service,
            customerId: $customerId,
            scope: $scope,
            amount: (float) $quote->quoted_price,
            milestones: [['title' => translate('full_delivery'), 'amount' => $quote->quoted_price]],
            paymentRequestId: $paymentRequestId,
            quoteRequestId: $quoteRequestId,
        );
    }

    /**
     * @param array<int, array{title: string, amount: float|string}> $milestones
     */
    private function createOrder(
        FreelancerService $service,
        int $customerId,
        string $scope,
        float $amount,
        array $milestones,
        ?string $paymentRequestId,
        ?int $quoteRequestId,
    ): FreelancerContract {
        $contract = DB::transaction(function () use ($service, $customerId, $scope, $amount, $milestones, $paymentRequestId, $quoteRequestId) {
            // Accepting the quote here (inside the same transaction as contract
            // creation) is what actually enforces the Quoted->Accepted transition —
            // acceptQuote() throws if it's in any other state.
            $quote = $quoteRequestId ? $this->quoteService->acceptQuote($quoteRequestId, $customerId) : null;

            $contract = $this->contractService->hire(
                service: $service,
                customerId: $customerId,
                scope: $scope,
                amount: $amount,
                milestones: $milestones,
                paymentRequestId: $paymentRequestId,
            );

            $contract->update([
                'freelancer_quote_request_id' => $quote?->id,
                'delivery_status' => DeliveryStatus::Pending,
                'verdict_status' => VerdictStatus::Pending,
            ]);

            return $contract;
        });

        $this->auditLog->log(
            actorType: 'customer',
            actorId: $customerId,
            subject: $contract,
            action: $quoteRequestId ? 'purchase_from_quote' : 'purchase_direct',
            before: null,
            after: [
                'status' => $contract->status,
                'delivery_status' => $contract->delivery_status->value,
                'verdict_status' => $contract->verdict_status->value,
                'total_amount' => (string) $contract->total_amount,
            ],
        );

        // Side effects below are independently guarded — a failure crediting the
        // wallet or sending a notification must never unwind a payment that has
        // already cleared and a contract that has already been created.
        try {
            $this->commissionService->recordCommissionLedger(
                sellerId: $service->seller_id,
                grossAmount: (float) $contract->total_amount,
                referenceType: 'freelancer_contract',
                referenceId: $contract->id,
                updateWallets: true,
            );
        } catch (\Throwable $e) {
            Log::error('[FreelancerOrderService] Wallet crediting failed for contract ' . $contract->id . ': ' . $e->getMessage());
        }

        try {
            $freelancerName = trim(($contract->freelancer?->f_name ?? '') . ' ' . ($contract->freelancer?->l_name ?? '')) ?: 'A freelancer';
            $customerName = trim(($contract->customer?->f_name ?? '') . ' ' . ($contract->customer?->l_name ?? '')) ?: 'A customer';
            AdminNotification::create([
                'type' => 'freelancer_service_purchased',
                'title' => 'Freelancer Service Purchased',
                'message' => "{$customerName} purchased \"{$service->title}\" from {$freelancerName}.",
                'link' => route('admin.freelancer.contracts.view', $contract->id),
                'reference_id' => $contract->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('[FreelancerOrderService] Admin notification failed for contract ' . $contract->id . ': ' . $e->getMessage());
        }

        // Mail sends synchronously (no queue configured) — defer past the response
        // so the customer isn't stuck waiting on SMTP right after their card was charged.
        dispatch(function () use ($contract) {
            try {
                $contract->freelancer->notify(new HiredNotification($contract));
            } catch (\Throwable $e) {
                Log::error('[FreelancerOrderService] Hired notification failed: ' . $e->getMessage());
            }

            try {
                $contract->customer->notify(new HireConfirmedNotification($contract));
            } catch (\Throwable $e) {
                Log::error('[FreelancerOrderService] Hire-confirmed notification failed: ' . $e->getMessage());
            }
        })->afterResponse();

        return $contract->fresh(['milestones']);
    }
}

