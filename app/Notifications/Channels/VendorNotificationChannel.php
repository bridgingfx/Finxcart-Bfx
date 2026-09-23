<?php

namespace App\Notifications\Channels;

use App\Models\VendorNotification;
use App\Traits\PushNotificationTrait;
use Illuminate\Notifications\Notification;

/**
 * Routes ->notify() database-channel payloads into the app's existing
 * vendor_notifications inbox table (shared by vendors and freelancers,
 * distinguished by seller_type) instead of Laravel's stock polymorphic
 * `notifications` table. Also pushes to the seller's device when they have
 * an FCM token registered, so every Notification class using this channel
 * gets real push delivery for free.
 */
class VendorNotificationChannel
{
    use PushNotificationTrait;

    public function send(mixed $notifiable, Notification $notification): void
    {
        $data = $notification->toArray($notifiable);

        VendorNotification::create([
            'seller_id' => $notifiable->getKey(),
            'title' => $data['title'],
            'message' => $data['message'],
            'link' => $data['link'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
        ]);

        $this->sendGenericPushNotification($notifiable->cm_firebase_token ?? null, $data['title'], $data['message']);
    }
}
