<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Chatting;
use App\Models\Contact;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminNotificationController extends Controller
{
    public function poll(): JsonResponse
    {
        $unreadContactMessages = Contact::where('seen', 0)->latest()->take(5)->get();
        $unreadContactMessageCount = Contact::where('seen', 0)->count();

        $adminChattingUnseenQuery = Chatting::where('admin_id', 0)
            ->where('seen_by_admin', 0)
            ->where('notification_receiver', 'admin');
        $adminChattingUnseenCount = (clone $adminChattingUnseenQuery)->count();
        $adminChattingNotifications = (clone $adminChattingUnseenQuery)
            ->with(['customer', 'deliveryMan', 'seller'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($chat) {
                if ($chat->user_id) {
                    $chat->notification_label = translate('customer');
                    $chat->notification_name = trim(($chat->customer->f_name ?? '') . ' ' . ($chat->customer->l_name ?? '')) ?: translate('customer');
                    $chat->notification_link = route('admin.messages.index', ['type' => 'customer']);
                } elseif ($chat->delivery_man_id) {
                    $chat->notification_label = translate('delivery_man');
                    $chat->notification_name = trim(($chat->deliveryMan->f_name ?? '') . ' ' . ($chat->deliveryMan->l_name ?? '')) ?: translate('delivery_man');
                    $chat->notification_link = route('admin.messages.index', ['type' => 'delivery-man']);
                } else {
                    $chat->notification_label = translate('freelancer');
                    $chat->notification_name = trim(($chat->seller->f_name ?? '') . ' ' . ($chat->seller->l_name ?? '')) ?: translate('freelancer');
                    $chat->notification_link = route('admin.freelancer.messages.index', ['seller_id' => $chat->seller_id]);
                }
                return $chat;
            });

        $adminSystemNotificationQuery = AdminNotification::whereNull('read_at');
        $adminSystemNotificationCount = (clone $adminSystemNotificationQuery)->count();
        $adminSystemNotifications = (clone $adminSystemNotificationQuery)
            ->latest()
            ->take(5)
            ->get();

        $totalAdminNotificationCount = $unreadContactMessageCount + $adminChattingUnseenCount + $adminSystemNotificationCount;

        return response()->json([
            'count' => $totalAdminNotificationCount,
            'html' => view('layouts.admin.partials._notification-items', compact(
                'adminSystemNotifications',
                'adminChattingNotifications',
                'unreadContactMessages'
            ))->render(),
        ]);
    }

    public function markAsRead(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer|regex:/^\d+$/',
        ]);

        $notification = AdminNotification::find($request->id);

        if ($notification) {
            if (is_null($notification->read_at)) {
                $notification->read_at = Carbon::now();
                $notification->save();
            }

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    public function markAllRead(): JsonResponse
    {
        AdminNotification::whereNull('read_at')->update(['read_at' => Carbon::now()]);
        Cache::forget('admin_header_system_notification_count');

        Contact::where('seen', 0)->update(['seen' => 1]);
        Cache::forget('admin_header_unread_contact_count');

        Chatting::where('admin_id', 0)
            ->where('seen_by_admin', 0)
            ->where('notification_receiver', 'admin')
            ->update(['seen_by_admin' => 1]);
        Cache::forget('admin_header_chatting_unseen_count');

        return response()->json(['success' => true]);
    }
}
