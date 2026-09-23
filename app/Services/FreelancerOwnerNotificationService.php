<?php

namespace App\Services;

use App\Models\Seller;
use App\Models\VendorNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FreelancerOwnerNotificationService
{
    public function notify(?Seller $seller, string $subject, string $message): void
    {
        if (!$seller || empty($seller->email)) {
            return;
        }

        $this->createNotification($seller, $subject, $message);
        $this->sendEmail($seller, $subject, $message);
    }

    private function createNotification(Seller $seller, string $subject, string $message): void
    {
        try {
            VendorNotification::create([
                'seller_id' => $seller->id,
                'title' => $subject,
                'message' => $message,
                'link' => route('freelancer.services.index'),
                'reference_id' => null,
                'read_at' => null,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Freelancer owner notification failed: ' . $exception->getMessage());
        }
    }

    private function sendEmail(Seller $seller, string $subject, string $message): void
    {
        try {
            Mail::raw($message, function ($mail) use ($seller, $subject) {
                $mail->to($seller->email)->subject($subject);
            });
        } catch (\Throwable $exception) {
            Log::warning('Freelancer owner email failed: ' . $exception->getMessage());
        }
    }
}
