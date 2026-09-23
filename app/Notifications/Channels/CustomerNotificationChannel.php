<?php

namespace App\Notifications\Channels;

use App\Models\CustomerNotification;
use App\Traits\PushNotificationTrait;
use Illuminate\Notifications\Notification;

/**
 * Routes ->notify() database-channel payloads into the app's existing
 * customer_notifications inbox table instead of Laravel's stock polymorphic
 * `notifications` table (which this app's `notifications` table schema does
 * not match — see App\Models\Notification, a bespoke broadcast table). Also
 * pushes to the customer's device when they have an FCM token registered, so
 * every Notification class using this channel gets real push delivery for free.
 */
class CustomerNotificationChannel
{
    use PushNotificationTrait;

    public function send(mixed $notifiable, Notification $notification): void
    {
        $data = $notification->toArray($notifiable);

        CustomerNotification::create([
            'customer_id' => $notifiable->getKey(),
            'title' => $data['title'],
            'message' => $data['message'],
            'link' => $data['link'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
        ]);

        $this->sendGenericPushNotification($notifiable->cm_firebase_token ?? null, $data['title'], $data['message']);
    }
}
