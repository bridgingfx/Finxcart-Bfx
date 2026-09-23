<?php

namespace App\Listeners;

use App\Events\VendorRegistrationEvent;
use App\Mail\VendorRegistrationMail;
use App\Models\AdminNotification;
use App\Models\Seller;
use App\Models\VendorNotification;
use App\Traits\EmailTemplateTrait;
use App\Traits\PushNotificationTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VendorRegistrationListener
{
    use EmailTemplateTrait, PushNotificationTrait;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(VendorRegistrationEvent $event): void
    {
        $this->getEmailTemplateDataForUpdate($event->data['userType']);

        // Mail sends synchronously (no queue worker configured) — defer it past the
        // response so vendor/freelancer create/approve/reject actions don't block.
        dispatch(function () use ($event) {
            // afterResponse() only truly detaches from the connection on servers that
            // support fastcgi_finish_request() (PHP-FPM). On `php artisan serve` (no
            // such support) the connection stays open, so an uncaught exception here
            // still overwrites/corrupts the response the browser already received.
            try {
                $this->sendMail($event);
            } catch (\Throwable $e) {
                \Log::warning('[VendorRegistrationListener] Deferred mail send failed: ' . $e->getMessage());
            }
        })->afterResponse();

        $this->notifyAdmin($event);
        $this->notifySeller($event);
    }

    private function sendMail(VendorRegistrationEvent $event):void{
        try {
            $email = $event->email;
            $data = $event->data;
            $this->sendingMail(sendMailTo: $email, userType: $data['userType'], templateName: $data['templateName'], data: $data);
        } catch (\Throwable $e) {
            Log::error('[VendorRegistrationListener] Email failed: ' . $e->getMessage(), [
                'to' => $event->email,
                'templateName' => $event->data['templateName'] ?? null,
            ]);
        }
    }

    private function notifyAdmin(VendorRegistrationEvent $event): void
    {
        try {
            $data = $event->data;
            $isFreelancer = ($data['seller_type'] ?? null) === 'freelancer';
            $roleLabel = $isFreelancer ? 'Freelancer' : 'Vendor';
            $name = $data['vendorName'] ?? $data['name'] ?? $event->email;
            $verificationLink = $isFreelancer
                ? route('admin.freelancer.accounts.index')
                : route('admin.vendors.vendor-verifications.index');

            switch ($data['templateName'] ?? null) {
                case 'registration':
                    AdminNotification::create([
                        'type' => 'seller_registration',
                        'title' => "New {$roleLabel} Registered",
                        'message' => "{$name} ({$event->email}) has registered as a {$roleLabel}.",
                        'link' => $verificationLink,
                        'reference_id' => $data['seller_id'] ?? null,
                    ]);
                    break;

                case 'vendor-verification-submitted':
                    AdminNotification::create([
                        'type' => 'kyc_submitted',
                        'title' => "{$roleLabel} KYC Submitted",
                        'message' => "{$name} submitted verification documents and is awaiting review.",
                        'link' => $isFreelancer ? $verificationLink : route('admin.vendors.vendor-list'),
                        'reference_id' => $data['seller_id'] ?? null,
                    ]);
                    break;

                case 'vendor-verification-approved':
                case 'vendor-verification-denied':
                    $approved = $data['templateName'] === 'vendor-verification-approved';
                    $reviewer = $data['reviewerName'] ?? null;
                    $byline = $reviewer ? " by {$reviewer}" : '';
                    AdminNotification::create([
                        'type' => 'kyc_reviewed',
                        'title' => "{$roleLabel} KYC " . ($approved ? 'Approved' : 'Rejected'),
                        'message' => "{$name}'s verification was " . ($approved ? 'approved' : 'rejected') . "{$byline}.",
                        'link' => $verificationLink,
                        'reference_id' => $data['seller_id'] ?? null,
                    ]);
                    break;
            }
        } catch (\Throwable $e) {
            Log::error('[VendorRegistrationListener] Admin notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Vendor/company-individual counterpart of FreelancerKycStatusChangedListener::createNotification() —
     * that listener only fires for seller_type === 'freelancer'; everyone else's
     * verification approve/reject goes through this event instead, and previously
     * never wrote a seller-facing VendorNotification row at all (only an admin one).
     */
    private function notifySeller(VendorRegistrationEvent $event): void
    {
        $data = $event->data;

        if (!in_array($data['templateName'] ?? null, ['vendor-verification-approved', 'vendor-verification-denied'], true)) {
            return;
        }

        $sellerId = $data['seller_id'] ?? null;
        if (!$sellerId) {
            return;
        }

        try {
            $approved = $data['templateName'] === 'vendor-verification-approved';

            // Dismiss previous unread KYC notifications so only the latest status shows.
            VendorNotification::where('seller_id', $sellerId)
                ->whereIn('title', ['KYC Approved', 'KYC Rejected'])
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            $title = $approved ? 'KYC Approved' : 'KYC Rejected';
            $message = $approved
                ? 'Your vendor verification has been approved. You can now start listing products.'
                : 'Your vendor verification was rejected. Reason: ' . ($data['rejection_reason'] ?? 'N/A');

            VendorNotification::create([
                'seller_id' => $sellerId,
                'title' => $title,
                'message' => $message,
                'link' => route('vendor.verification.form'),
                'reference_id' => $data['verification_id'] ?? null,
                'read_at' => null,
            ]);

            $fcmToken = Seller::find($sellerId)?->cm_firebase_token;
            dispatch(function () use ($fcmToken, $title, $message) {
                try {
                    $this->sendGenericPushNotification($fcmToken, $title, $message);
                } catch (\Throwable $e) {
                    Log::warning('[VendorRegistrationListener] Deferred push notification failed: ' . $e->getMessage());
                }
            })->afterResponse();
        } catch (\Throwable $e) {
            Log::error('[VendorRegistrationListener] Seller notification failed: ' . $e->getMessage());
        }
    }
}
