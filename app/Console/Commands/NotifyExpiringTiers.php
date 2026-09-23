<?php

namespace App\Console\Commands;

use App\Models\VendorTier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifyExpiringTiers extends Command
{
    protected $signature = 'tiers:notify-expiring';
    protected $description = 'Send renewal reminders for vendor tiers ending in three days';

    public function handle(): int
    {
        $targetDate = now()->addDays(3)->toDateString();

        VendorTier::with('seller')
            ->where(function ($query) use ($targetDate) {
                $query->where(fn ($trial) => $trial->where('status', 'trial')->whereDate('trial_end_date', $targetDate))
                    ->orWhere(fn ($active) => $active->where('status', 'active')->whereDate('end_date', $targetDate));
            })
            ->each(function (VendorTier $tier) {
                $seller = $tier->seller;
                if (!$seller?->email) {
                    return;
                }

                $endDate = $tier->status === 'trial' ? $tier->trial_end_date : $tier->end_date;
                $subject = $tier->status === 'trial'
                    ? 'Your Finxcart Trial Ends in 3 Days'
                    : 'Your Finxcart Subscription Ends in 3 Days';

                Mail::raw(
                    "Hello {$seller->f_name},\n\nYour Finxcart {$tier->status} ends in 3 days ({$endDate->format('d M Y')}).\n\nPlease visit your dashboard to renew and continue listing products.\n\nFinxcart Team",
                    fn ($message) => $message->to($seller->email)->subject($subject)
                );
                $this->info("Notified: {$seller->email}");
            });

        $this->info('Done.');
        return self::SUCCESS;
    }
}
