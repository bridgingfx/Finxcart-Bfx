<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CustomerNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class CustomerNotificationController extends Controller
{
    public function poll(): JsonResponse
    {
        $customerNotifications = CustomerNotification::where('customer_id', auth('customer')->id())
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return response()->json([
            'count' => $customerNotifications->whereNull('read_at')->count(),
            'html' => view('web-views.partials._customer-notification-items', compact('customerNotifications'))->render(),
        ]);
    }

    public function read(int $id): RedirectResponse
    {
        $notification = CustomerNotification::where('id', $id)
            ->where('customer_id', auth('customer')->id())
            ->first();

        if ($notification && !$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return redirect($notification->link ?? url('/'));
    }

    public function markAllRead(): RedirectResponse
    {
        CustomerNotification::where('customer_id', auth('customer')->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back();
    }
}
