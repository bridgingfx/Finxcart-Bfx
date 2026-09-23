<?php

namespace App\Http\Controllers\Freelancer;

use App\Contracts\Repositories\NotificationRepositoryInterface;
use App\Contracts\Repositories\ShopRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\NotificationModalViewRequest;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use App\Models\VendorNotification;
use App\Models\VendorTier;
use App\Models\Product;
use App\Models\NotificationSeen;

class NotificationController extends Controller
{
    public function __construct(
        private readonly ShopRepositoryInterface $shopRepo,
        private readonly NotificationRepositoryInterface $notificationRepo,
    )
    {
    }

    public function poll(): JsonResponse
    {
        $seller = auth('freelancer')->user();
        $sellerId = $seller->id;

        // This poll runs every few seconds — cap the rows actually rendered in the
        // dropdown (mirroring $systemNotifications' take(20) below) and get the
        // badge count via a separate lightweight COUNT instead of loading every
        // unread row just to display a number.
        $unreadNotificationCount = VendorNotification::where('seller_id', $sellerId)
            ->whereNull('read_at')
            ->count();

        $unreadNotifications = VendorNotification::where('seller_id', $sellerId)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        $systemNotifications = Notification::whereBetween('created_at', [$seller->created_at, now()])
            ->where('sent_to', 'seller')
            ->with('notificationSeenBy')
            ->latest()
            ->take(20)
            ->get();

        $systemNotificationCount = Notification::whereBetween('created_at', [$seller->created_at, now()])
            ->where('sent_to', 'seller')
            ->whereDoesntHave('notificationSeenBy')
            ->count();

        // Same aggregate the sidebar view composer (AppServiceProvider) uses for the
        // separate "Messages"/"Messages from Admin" badges — those aren't part of the
        // notification bell, so they were never refreshed by this poll and stayed
        // stale until a reload.
        $chattingCounts = \App\Models\Chatting::where('seller_id', $sellerId)->selectRaw(
            'SUM(CASE WHEN seen_by_seller = 0 AND user_id IS NOT NULL THEN 1 ELSE 0 END) as unseen_customer,
             SUM(CASE WHEN seen_by_seller = 0 AND admin_id IS NOT NULL AND user_id IS NULL THEN 1 ELSE 0 END) as unseen_admin'
        )->first();

        return response()->json([
            'count' => $systemNotificationCount + $unreadNotificationCount,
            'html' => view('layouts.freelancer.partials._notification-items', compact('systemNotifications', 'unreadNotifications'))->render(),
            'chat_unseen_customer' => (int) ($chattingCounts->unseen_customer ?? 0),
            'chat_unseen_admin' => (int) ($chattingCounts->unseen_admin ?? 0),
        ]);
    }

    public function getNotificationModalView(NotificationModalViewRequest $request): JsonResponse
    {
        $shop = $this->shopRepo->getFirstWhere(params:['seller_id'=> auth('freelancer')->id()]);
        $companyName = getWebConfig('company_name') ?? '';
        $notificationType = $request->input('notification_type', 'system');

        $customNotification = null;
        if ($notificationType === 'custom') {
            $customNotification = VendorNotification::where('id', $request['id'])
                ->where('seller_id', auth('freelancer')->id())
                ->first();
        }

        if ($customNotification) {
            $data = $customNotification;

            if ($data->read_at == null) {
                $data->read_at = now();
                $data->save();
            }

            if ($data->reference_id) {
                $vendorTier = VendorTier::with('tier')->find($data->reference_id);

                if ($vendorTier) {
                    $products = Product::where('vendor_tier_id', $vendorTier->id)
                        ->select('id', 'name', 'thumbnail', 'unit_price', 'status')
                        ->get();

                    $viewHTML = view('vendor-views.notification.plan_expiry_details_modal', [
                        'data' => $data,
                        'vendorTier' => $vendorTier,
                        'products' => $products,
                        'companyName' => $companyName,
                        'shop' => $shop
                    ])->render();

                    $count = $this->getCombinedNotificationCount();

                    return response()->json([
                        'notification_count' => $count,
                        'view' => $viewHTML,
                    ]);
                }
            }

            $viewHTML = view('vendor-views.partials.notification-modal', compact('shop', 'companyName', 'data'))->render();

            $count = $this->getCombinedNotificationCount();

            return response()->json([
                'notification_count' => $count,
                'view' => $viewHTML,
            ]);
        }

        NotificationSeen::updateOrInsert(
            [
                'seller_id' => auth('freelancer')->id(),
                'notification_id' => $request['id']
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $data = $this->notificationRepo->getFirstWhere(params:['id' => $request['id']]);

        if (!$data) {
            return response()->json([
                'notification_count' => $this->getCombinedNotificationCount(),
                'view' => '',
            ], 404);
        }

        $count = $this->getCombinedNotificationCount();

        return response()->json([
            'notification_count' => $count,
            'view' => view('vendor-views.partials.notification-modal', compact('shop', 'companyName', 'data'))->render(),
        ]);
    }

    public function markAsRead(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer|regex:/^\d+$/'
        ]);

        $notification = VendorNotification::where('id', $request->id)
            ->where('seller_id', auth('freelancer')->id())
            ->first();

        if ($notification) {
            if (is_null($notification->read_at)) {
                $notification->read_at = now();
                $notification->save();
            }

            return response()->json([
                'success' => true,
                'notification_count' => $this->getCombinedNotificationCount(),
            ]);
        }

        return response()->json(['success' => false], 404);
    }

    public function markKycSeen(): JsonResponse
    {
        $seller = auth('freelancer')->user();
        if ($seller) {
            $seller->update(['kyc_notification_seen' => 1]);
        }
        return response()->json(['success' => true]);
    }

    private function getCombinedNotificationCount(): int
    {
        $systemCount = $this->notificationRepo->countWhereBetween(
            params: [auth('freelancer')->user()->created_at, now()],
            filters: ['sent_to' => 'seller'],
            relations: 'notificationSeenBy'
        );

        $customCount = VendorNotification::where('seller_id', auth('freelancer')->id())
            ->whereNull('read_at')
            ->count();

        return $systemCount + $customCount;
    }
}
