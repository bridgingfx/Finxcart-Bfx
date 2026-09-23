<?php

namespace App\Listeners;

use App\Events\FreelancerKycStatusChangedEvent;
use App\Models\AdminNotification;
use App\Models\Seller;
use App\Models\VendorNotification;
use App\Traits\EmailTemplateTrait;
use App\Traits\PushNotificationTrait;
use Illuminate\Support\Facades\Log;

class FreelancerKycStatusChangedListener
{
    use EmailTemplateTrait, PushNotificationTrait;

    public function handle(FreelancerKycStatusChangedEvent $event): void
    {
        $data = $event->data;
        $status = $data['status'] ?? 'unknown';
        $sellerId = $data['seller_id'] ?? null;

        if ($sellerId) {
            $this->createNotification((int) $sellerId, $status, $data);
        }

        // Mail sends synchronously (no queue worker configured) — defer past the response
        // so admin approve/reject actions don't block on an SMTP/Brevo round-trip.
        dispatch(function () use ($event, $status) {
            try {
                $this->sendEmail($event, $status);
            } catch (\Throwable $e) {
                \Log::warning('[FreelancerKycStatusChangedListener] Deferred mail send failed: ' . $e->getMessage());
            }
        })->afterResponse();

        $this->notifyAdmin($status, $data);
    }

    private function notifyAdmin(string $status, array $data): void
    {
        if (!in_array($status, ['approved', 'rejected'], true)) {
            return;
        }

        try {
            $name = $data['vendorName'] ?? $data['name'] ?? 'A freelancer';
            $reviewer = $data['reviewerName'] ?? null;
            $byline = $reviewer ? " by {$reviewer}" : '';

            AdminNotification::create([
                'type' => 'kyc_reviewed',
                'title' => 'Freelancer KYC ' . ($status === 'approved' ? 'Approved' : 'Rejected'),
                'message' => "{$name}'s verification was {$status}{$byline}.",
                'link' => route('admin.freelancer.accounts.index'),
                'reference_id' => $data['seller_id'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('[FreelancerKycStatusChangedListener] Admin notification failed: ' . $e->getMessage());
        }
    }

    private function createNotification(int $sellerId, string $status, array $data): void
    {
        try {
            // Dismiss all previous unread KYC notifications for this seller
            // so only the latest status shows as a toast — no duplicates
            VendorNotification::where('seller_id', $sellerId)
                ->whereIn('title', ['KYC Approved', 'KYC Rejected'])
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            $approved = $status === 'approved';
            $title    = $approved ? 'KYC Approved' : 'KYC Rejected';
            $message  = $approved
                ? 'Your freelancer verification has been approved. You can now start listing products.'
                : 'Your freelancer verification was rejected. Reason: ' . ($data['rejection_reason'] ?? 'N/A');

            VendorNotification::create([
                'seller_id'    => $sellerId,
                'title'        => $title,
                'message'      => $message,
                'link'         => route('freelancer.verification.form'),
                'reference_id' => $data['verification_id'] ?? null,
                'read_at'      => null,
            ]);

            $fcmToken = Seller::find($sellerId)?->cm_firebase_token;
            dispatch(function () use ($fcmToken, $title, $message) {
                try {
                    $this->sendGenericPushNotification($fcmToken, $title, $message);
                } catch (\Throwable $e) {
                    Log::warning('[FreelancerKycStatusChangedListener] Deferred push notification failed: ' . $e->getMessage());
                }
            })->afterResponse();
        } catch (\Throwable $e) {
            Log::error('[FreelancerKycStatusChangedListener] Notification failed: ' . $e->getMessage());
        }
    }

    private function sendEmail(FreelancerKycStatusChangedEvent $event, string $status): void
    {
        try {
            $this->getEmailTemplateDataForUpdate('vendor');
            $this->sendingMail(
                sendMailTo: $event->email,
                userType: 'vendor',
                templateName: $status === 'approved' ? 'vendor-verification-approved' : 'vendor-verification-denied',
                data: $event->data,
            );
        } catch (\Throwable $e) {
            Log::error('[FreelancerKycStatusChangedListener] Email failed: ' . $e->getMessage());
        }
    }
}
