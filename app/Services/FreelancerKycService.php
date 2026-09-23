<?php

namespace App\Services;

use App\Events\FreelancerKycStatusChangedEvent;
use App\Models\Admin;
use App\Models\VendorVerification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FreelancerKycService
{
    public function __construct(private readonly FreelancerAuditLogService $auditLog)
    {
    }

    public function approve(VendorVerification $verification, Admin $reviewer, ?string $approvalNote): VendorVerification
    {
        $reviewerName = $this->reviewerName($reviewer);

        DB::transaction(function () use ($verification, $reviewer, $reviewerName, $approvalNote) {
            $before = ['status' => $verification->status];

            $verification->update([
                'status' => 'approved',
                'rejection_reason' => null,
                'approval_note' => $approvalNote,
                'reviewed_by_name' => $reviewerName,
                'reviewed_at' => now(),
            ]);

            // kyc_notification_seen = 0 flags the "Congratulations" toast to fire
            // the freelancer's next page load (see layouts/freelancer/app.blade.php).
            $verification->seller->update([
                'status' => 'approved',
                'first_login_after_approval' => true,
                'kyc_notification_seen' => 0,
            ]);

            $this->auditLog->log(
                actorType: 'admin',
                actorId: $reviewer->id,
                subject: $verification,
                action: 'freelancer_kyc_approved',
                before: $before,
                after: ['status' => 'approved', 'reviewed_by' => $reviewerName],
            );
        });

        $this->dispatchStatusChangedEvent($verification, 'approved', $reviewerName);

        return $verification->fresh(['seller']);
    }

    public function reject(VendorVerification $verification, Admin $reviewer, string $rejectionReason): VendorVerification
    {
        $reviewerName = $this->reviewerName($reviewer);

        DB::transaction(function () use ($verification, $reviewer, $reviewerName, $rejectionReason) {
            $before = ['status' => $verification->status];

            $verification->update([
                'status' => 'rejected',
                'rejection_reason' => $rejectionReason,
                'approval_note' => null,
                'reviewed_by_name' => $reviewerName,
                'reviewed_at' => now(),
            ]);

            // kyc_notification_seen = 0 so the freelancer's next page load shows a
            // toast — previously left at 1 ("already seen"), which silently
            // suppressed any rejection notice. See layouts/freelancer/app.blade.php.
            $verification->seller->update([
                'status' => 'rejected',
                'kyc_notification_seen' => 0,
            ]);

            $this->auditLog->log(
                actorType: 'admin',
                actorId: $reviewer->id,
                subject: $verification,
                action: 'freelancer_kyc_rejected',
                before: $before,
                after: ['status' => 'rejected', 'rejection_reason' => $rejectionReason, 'reviewed_by' => $reviewerName],
            );
        });

        $this->dispatchStatusChangedEvent($verification, 'rejected', $reviewerName);

        return $verification->fresh(['seller']);
    }

    private function dispatchStatusChangedEvent(VendorVerification $verification, string $status, string $reviewerName): void
    {
        try {
            $approved = $status === 'approved';

            event(new FreelancerKycStatusChangedEvent(email: $verification->seller->email, data: [
                'seller_id' => $verification->seller->id,
                'verification_id' => $verification->id,
                'name' => $verification->seller->f_name . ' ' . $verification->seller->l_name,
                'vendorName' => $verification->seller->f_name . ' ' . $verification->seller->l_name,
                'shopName' => $verification->seller->shop?->name,
                'sellerType' => 'Freelancer',
                'seller_type' => 'freelancer',
                'reviewerName' => $reviewerName,
                'message' => $approved
                    ? 'Congratulations! You are approved as a freelancer on Finxcart. You can now login and start offering your services.'
                    : 'Admin has reviewed your credentials. Please login to see the status.',
                'status' => $status,
                'rejection_reason' => $approved ? null : $verification->rejection_reason,
                'rejectionReason' => $approved ? null : $verification->rejection_reason,
                'subject' => translate($approved ? 'Verification_Approved' : 'Verification_Rejected'),
                'title' => translate($approved ? 'Verification_Approved' : 'Verification_Rejected'),
                'userType' => 'vendor',
                'templateName' => $approved ? 'vendor-verification-approved' : 'vendor-verification-denied',
            ]));
        } catch (\Throwable $e) {
            Log::error('[FreelancerKycService] Status-changed event failed: ' . $e->getMessage());
        }
    }

    private function reviewerName(Admin $reviewer): string
    {
        return trim(($reviewer->f_name ?? '') . ' ' . ($reviewer->l_name ?? '')) ?: ($reviewer->name ?? $reviewer->email);
    }
}
