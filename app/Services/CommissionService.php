<?php

namespace App\Services;

use App\Events\SplitTransactionCompleteEvent;
use App\Models\AdminWallet;
use App\Models\BusinessSetting;
use App\Models\CommissionLedger;
use App\Models\SellerWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionService
{
    public const PLATFORM_COMMISSION_RATE = 0.15;

    public function recordCommissionLedger(
        int $sellerId,
        float $grossAmount,
        float $serviceCharge = 0.0,
        ?int $orderId = null,
        string $currency = 'USD',
        string $referenceType = 'order',
        ?int $referenceId = null,
        ?float $companyShareAmount = null,
        bool $updateWallets = false,
    ): CommissionLedger {
        if ($orderId && $referenceType === 'order') {
            $existingLedger = CommissionLedger::where('order_id', $orderId)
                ->where('reference_type', $referenceType)
                ->first();

            if ($existingLedger) {
                return $existingLedger;
            }
        }

        $companyShare = round($companyShareAmount ?? ($grossAmount * self::PLATFORM_COMMISSION_RATE), 4);
        $netVendorPayout = round($grossAmount - $companyShare - $serviceCharge, 4);

        return DB::transaction(function () use (
            $sellerId,
            $grossAmount,
            $companyShare,
            $serviceCharge,
            $netVendorPayout,
            $orderId,
            $currency,
            $referenceType,
            $referenceId,
            $updateWallets,
        ) {
            if ($updateWallets) {
                $this->updateWallets($sellerId, $companyShare, $netVendorPayout);
            }

            $ledger = CommissionLedger::create([
                'seller_id' => $sellerId,
                'order_id' => $orderId,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'gross_amount' => $grossAmount,
                'company_share_amount' => $companyShare,
                'service_charge_amount' => $serviceCharge,
                'net_vendor_payout' => $netVendorPayout,
                'currency' => $currency,
                'platform_rate' => self::PLATFORM_COMMISSION_RATE * 100,
            ]);

            $this->dispatchSplitEvent($ledger, $sellerId, $grossAmount, $companyShare, $serviceCharge, $netVendorPayout, $currency, $referenceType, $referenceId);

            return $ledger;
        });
    }

    public function previewSplit(float $grossAmount, float $serviceCharge = 0.0, ?float $companyShareAmount = null): array
    {
        $companyShare = round($companyShareAmount ?? ($grossAmount * self::PLATFORM_COMMISSION_RATE), 4);

        return [
            'gross' => $grossAmount,
            'company_share' => $companyShare,
            'service_charge' => $serviceCharge,
            'net_payout' => round($grossAmount - $companyShare - $serviceCharge, 4),
            'rate_pct' => self::PLATFORM_COMMISSION_RATE * 100,
        ];
    }

    public function getConfiguredServiceCharge(): float
    {
        $setting = BusinessSetting::where('type', 'commission_service_charge_amount')->first();

        return max(0, (float) ($setting?->value ?? 0));
    }

    private function updateWallets(int $sellerId, float $companyShare, float $netVendorPayout): void
    {
        $adminWallet = AdminWallet::first();

        if ($adminWallet) {
            $adminWallet->increment('commission_earned', $companyShare);
        } else {
            AdminWallet::create([
                'admin_id' => 1,
                'commission_earned' => $companyShare,
                'inhouse_earning' => 0,
                'withdrawn' => 0,
                'delivery_charge_earned' => 0,
                'pending_amount' => 0,
                'total_tax_collected' => 0,
            ]);
        }

        $sellerWallet = SellerWallet::firstOrCreate(
            ['seller_id' => $sellerId],
            [
                'total_earning' => 0,
                'withdrawn' => 0,
                'commission_given' => 0,
                'pending_withdraw' => 0,
                'delivery_charge_earned' => 0,
                'collected_cash' => 0,
                'total_tax_collected' => 0,
            ]
        );

        $sellerWallet->increment('total_earning', $netVendorPayout);
        $sellerWallet->increment('commission_given', $companyShare);
        $sellerWallet->increment('pending_withdraw', $netVendorPayout);
    }

    private function dispatchSplitEvent(
        CommissionLedger $ledger,
        int $sellerId,
        float $grossAmount,
        float $companyShare,
        float $serviceCharge,
        float $netVendorPayout,
        string $currency,
        string $referenceType,
        ?int $referenceId,
    ): void {
        try {
            event(new SplitTransactionCompleteEvent(
                sellerId: $sellerId,
                grossAmount: $grossAmount,
                companyShareAmount: $companyShare,
                serviceChargeAmount: $serviceCharge,
                netVendorPayout: $netVendorPayout,
                currency: $currency,
                ledgerId: $ledger->id,
                referenceType: $referenceType,
                referenceId: $referenceId,
            ));
        } catch (\Throwable $e) {
            Log::error('[CommissionService] SplitTransactionCompleteEvent failed: ' . $e->getMessage());
        }
    }
}
