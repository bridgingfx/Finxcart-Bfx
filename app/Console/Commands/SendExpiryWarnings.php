<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VendorTier;
use App\Models\VendorNotification;
use App\Mail\PlanExpiringSoonMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendExpiryWarnings extends Command
{
    protected $signature = 'app:send-expiry-warnings';
    protected $description = 'Sends warnings for plans expiring within the next 5 days, respecting trial dates.';

    public function handle()
    {
        $this->info('Starting expiry check...');
        
        // We fetch ALL active/trial tiers first
        $activeTiers = VendorTier::with(['seller', 'tier'])
            ->whereIn('status', ['active', 'trial'])
            ->get();

        $today = Carbon::today();
        $fiveDaysFromNow = Carbon::today()->addDays(5);
        
        $count = 0;

        foreach ($activeTiers as $tier) {
            $seller = $tier->seller;
            if (!$seller) continue;

            // --- DETERMINE EFFECTIVE EXPIRY DATE ---
            // If status is 'trial' and trial_end_date exists and is greater than end_date, use trial_end_date
            // Otherwise use end_date.
            $effectiveExpiryDate = $tier->end_date;

            if ($tier->status === 'trial' && $tier->trial_end_date) {
                if ($tier->trial_end_date->gt($tier->end_date)) {
                    $effectiveExpiryDate = $tier->trial_end_date;
                }
            }
            
            // Check if this effective date is valid AND is within the next 5 days
            if ($effectiveExpiryDate->gte($today) && $effectiveExpiryDate->lte($fiveDaysFromNow)) {
                
                $daysLeft = $today->diffInDays($effectiveExpiryDate, false);
                $daysLeft = (int) ceil($daysLeft); // Ensure whole number

                $isTrial = $tier->status === 'trial';
                $planName = $tier->tier->name;
                $expiryDateStr = $effectiveExpiryDate->format('F d, Y');
                
                // --- UPDATED: GENERATE DIRECT RENEWAL LINK ---
                // This link passes the specific IDs needed to setup the renewal immediately via the controller
                $paymentLink = route('vendor.tier.renew-link', [
                    'tier_id' => $tier->product_tier_id, 
                    'vendor_tier_record_id' => $tier->id
                ]);

                // Message Customization
                if ($isTrial) {
                    $title = "Free Trial Ending Soon";
                    $message = "Your Trial for $planName ends in $daysLeft days ($expiryDateStr). Upgrade to keep your data.";
                } else {
                    $title = "Plan Expiring Soon";
                    $message = "Your $planName plan expires in $daysLeft days ($expiryDateStr). Renew now.";
                }

                // 1. Send Email
                try {
                    // We update the tier object temporarily to use the effective date in the email view
                    $tier->end_date = $effectiveExpiryDate; 
                    Mail::to($seller->email)->send(new PlanExpiringSoonMail($tier, $paymentLink, $daysLeft));
                    $this->info("Email sent to {$seller->email}");
                } catch (\Exception $e) {
                    Log::error("Email error: " . $e->getMessage());
                }

                // 2. Send Notification (Once per day)
                $alreadyNotified = VendorNotification::where('seller_id', $seller->id)
                    ->where('title', $title)
                    ->whereDate('created_at', $today)
                    ->exists();

                if (!$alreadyNotified) {
                    VendorNotification::create([
                        'seller_id' => $seller->id,
                        'title' => $title,
                        'message' => $message,
                        'link' => $paymentLink,
                        'reference_id' => $tier->id, // Save the Tier ID for Modal Logic
                    ]);

                    $count++;
                }
            }
        }

        $this->info("Processed $count notifications.");
        return 0;
    }
}