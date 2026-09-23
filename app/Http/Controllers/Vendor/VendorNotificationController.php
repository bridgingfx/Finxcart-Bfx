<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VendorNotification;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VendorNotificationController extends Controller
{
    public function poll(): \Illuminate\Http\JsonResponse
    {
        $seller = Auth::guard('seller')->user();
        $sellerId = $seller->id;

        $unreadNotifications = VendorNotification::where('seller_id', $sellerId)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get();

        $systemNotifications = Notification::whereBetween('created_at', [$seller->created_at, Carbon::now()])
            ->where('sent_to', 'seller')
            ->with('notificationSeenBy')
            ->latest()
            ->take(20)
            ->get();

        $systemNotificationCount = Notification::whereBetween('created_at', [$seller->created_at, Carbon::now()])
            ->where('sent_to', 'seller')
            ->whereDoesntHave('notificationSeenBy')
            ->count();

        // Same aggregate the header/sidebar view composer (AppServiceProvider) uses for
        // the separate "Messages" badges — those aren't part of the notification bell,
        // so they were never refreshed by this poll and stayed stale until a reload.
        $chattingCounts = \App\Models\Chatting::where('seller_id', $sellerId)->selectRaw(
            'SUM(CASE WHEN seen_by_seller = 0 THEN 1 ELSE 0 END) as unseen_total,
             SUM(CASE WHEN seen_by_seller = 0 AND user_id IS NOT NULL THEN 1 ELSE 0 END) as unseen_customer'
        )->first();

        return response()->json([
            'count' => $systemNotificationCount + $unreadNotifications->count(),
            'html' => view('layouts.vendor.partials._notification-items', compact('systemNotifications', 'unreadNotifications'))->render(),
            'chat_unseen_total' => (int) ($chattingCounts->unseen_total ?? 0),
            'chat_unseen_customer' => (int) ($chattingCounts->unseen_customer ?? 0),
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|regex:/^\d+$/'
        ]);

        $notification = VendorNotification::where('id', $request->id)
            ->where('seller_id', Auth::guard('seller')->id())
            ->first();

        if ($notification) {
            if (is_null($notification->read_at)) {
                $notification->read_at = Carbon::now();
                $notification->save();
                \Illuminate\Support\Facades\Cache::forget("vendor_layout_badges_{$notification->seller_id}");
            }

            return response()->json([
                'success' => true,
                'notification_count' => $this->getCombinedNotificationCount(),
            ]);
        }

        return response()->json(['success' => false], 404);
    }

    public function markKycSeen(): \Illuminate\Http\JsonResponse
    {
        $seller = Auth::guard('seller')->user();
        if ($seller) {
            $seller->update(['kyc_notification_seen' => 1]);
        }
        return response()->json(['success' => true]);
    }

    private function getCombinedNotificationCount(): int
    {
        $seller = Auth::guard('seller')->user();

        if (!$seller) {
            return 0;
        }

        $systemCount = Notification::whereBetween('created_at', [$seller->created_at, Carbon::now()])
            ->where('sent_to', 'seller')
            ->whereDoesntHave('notificationSeenBy')
            ->count();

        $customCount = VendorNotification::where('seller_id', $seller->id)
            ->whereNull('read_at')
            ->count();

        return $systemCount + $customCount;
    }
}
