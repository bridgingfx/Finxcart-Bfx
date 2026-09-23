<?php

namespace App\Console\Commands;

use App\Models\CommissionLedger;
use App\Models\Order;
use App\Services\CommissionService;
use Illuminate\Console\Command;

class BackfillCommissionLedger extends Command
{
    protected $signature = 'commission-ledger:backfill {--seller_id=} {--dry-run}';

    protected $description = 'Backfill commission_ledger rows from delivered and disbursed seller orders without changing wallets.';

    public function handle(CommissionService $commissionService): int
    {
        $sellerId = $this->option('seller_id');
        $dryRun = (bool) $this->option('dry-run');
        $created = 0;
        $skipped = 0;

        $orders = Order::with('orderTransaction')
            ->where('seller_is', 'seller')
            ->where('order_status', 'delivered')
            ->where('admin_commission', '>', 0)
            ->whereHas('orderTransaction', function ($query) {
                $query->where('status', 'disburse');
            })
            ->when($sellerId, function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })
            ->orderBy('id')
            ->get();

        foreach ($orders as $order) {
            $exists = CommissionLedger::where('order_id', $order->id)
                ->where('reference_type', 'order')
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            if (!$dryRun) {
                $commissionService->recordCommissionLedger(
                    sellerId: (int) $order->seller_id,
                    grossAmount: (float) ($order->orderTransaction?->order_amount ?? $order->order_amount),
                    serviceCharge: 0,
                    orderId: (int) $order->id,
                    currency: 'USD',
                    referenceType: 'order',
                    referenceId: $order->orderTransaction?->id,
                    companyShareAmount: (float) $order->admin_commission,
                    updateWallets: false,
                );
            }

            $created++;
        }

        $this->info(($dryRun ? 'Would create' : 'Created') . " {$created} commission ledger row(s). Skipped {$skipped} existing row(s).");

        return self::SUCCESS;
    }
}
