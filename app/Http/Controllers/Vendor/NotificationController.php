<?php

namespace App\Http\Controllers\Vendor;

use App\Contracts\Repositories\NotificationRepositoryInterface;
use App\Contracts\Repositories\ShopRepositoryInterface;
use App\Enums\ViewPaths\Vendor\Notification;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Vendor\NotificationModalViewRequest;
use App\Repositories\NotificationSeenRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
// --- NEW IMPORTS START ---
use App\Models\VendorNotification;
use App\Models\VendorTier;
use App\Models\Product;
use App\Models\NotificationSeen;
// --- NEW IMPORTS END ---

class NotificationController extends BaseController
{
    /**
     * @param ShopRepositoryInterface $shopRepo
     * @param NotificationRepositoryInterface $notificationRepo
     * @param NotificationSeenRepository $notificationSeenRepo
     */
    public function __construct(
        private readonly ShopRepositoryInterface $shopRepo,
        private readonly NotificationRepositoryInterface $notificationRepo,
        private readonly NotificationSeenRepository $notificationSeenRepo,
    )
    {
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View|Collection|LengthAwarePaginator|callable|RedirectResponse|null
     */
    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return null;
    }

    /**
     * Handle the Modal View Request.
     * Checks for Custom Expiry Notifications first, then falls back to System Notifications.
     *
     * @param NotificationModalViewRequest $request
     * @return JsonResponse
     */
    public function getNotificationModalView(NotificationModalViewRequest $request): JsonResponse
    {
        $shop = $this->shopRepo->getFirstWhere(params:['seller_id'=> auth('seller')->id()]);
        $companyName = getWebConfig('company_name') ?? '';
        $notificationType = $request->input('notification_type', 'system');

        // -------------------------------------------------------------------------
        // LOGIC BRANCH 1: CHECK FOR CUSTOM PLAN EXPIRY NOTIFICATION
        // -------------------------------------------------------------------------
        $customNotification = null;
        if ($notificationType === 'custom') {
            $customNotification = VendorNotification::where('id', $request['id'])
                ->where('seller_id', auth('seller')->id())
                ->first();
        }

        if ($customNotification) {
            $data = $customNotification;
            
            // 1. Mark as Read immediately
            if ($data->read_at == null) {
                $data->read_at = now();
                $data->save();
                \Illuminate\Support\Facades\Cache::forget("vendor_layout_badges_{$data->seller_id}");
            }

            // 2. Check if this is a Tier Expiry Warning (Has reference_id)
            if ($data->reference_id) {
                $vendorTier = VendorTier::with('tier')->find($data->reference_id);

                // If the Tier Record exists, show the Special Renewal Modal
                if ($vendorTier) {
                    $products = Product::where('vendor_tier_id', $vendorTier->id)
                        ->select('id', 'name', 'thumbnail', 'unit_price', 'status')
                        ->get();

                    // Render the DETAILED view we created in Step 5
                    $viewHTML = view('vendor-views.notification.plan_expiry_details_modal', [
                        'data' => $data,
                        'vendorTier' => $vendorTier,
                        'products' => $products,
                        'companyName' => $companyName,
                        'shop' => $shop
                    ])->render();

                    // Calculate Count (System + Custom)
                    $count = $this->getCombinedNotificationCount();

                    return response()->json([
                        'notification_count' => $count,
                        'view' => $viewHTML,
                    ]);
                }
            }

            // If it's a custom notification but NOT an expiry warning (no reference_id),
            // or if the Tier record was deleted, fall back to the standard view.
            // We use the View Enum for the path, but pass our custom data.
            $viewHTML = view(Notification::INDEX['view'], compact('shop', 'companyName', 'data'))->render();
            
            $count = $this->getCombinedNotificationCount();

            return response()->json([
                'notification_count' => $count,
                'view' => $viewHTML,
            ]);
        }

        // -------------------------------------------------------------------------
        // LOGIC BRANCH 2: STANDARD SYSTEM NOTIFICATION (Legacy)
        // -------------------------------------------------------------------------
        
        // Mark as seen in the system repo
        NotificationSeen::updateOrInsert(
            [
                'seller_id' => auth('seller')->id(),
                'notification_id' => $request['id']
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Fetch data from system repo
        $data = $this->notificationRepo->getFirstWhere(params:['id' => $request['id']]);

        if (!$data) {
            return response()->json([
                'notification_count' => $this->getCombinedNotificationCount(),
                'view' => '',
            ], 404);
        }

        // Calculate Count
        $count = $this->getCombinedNotificationCount();

        return response()->json([
            'notification_count' => $count,
            'view' => view(Notification::INDEX['view'], compact('shop', 'companyName', 'data'))->render(),
        ]);
    }

    /**
     * Helper to get the total count of unread notifications (System + Custom)
     */
    private function getCombinedNotificationCount(): int
    {
        // 1. Count System Notifications
        $systemCount = $this->notificationRepo->countWhereBetween(
            params: [auth('seller')->user()->created_at, now()],
            filters: ['sent_to' => 'seller'],
            relations: 'notificationSeenBy'
        );

        // 2. Count Custom Notifications (Unread only)
        $customCount = VendorNotification::where('seller_id', auth('seller')->id())
            ->whereNull('read_at')
            ->count();

        return $systemCount + $customCount;
    }
}
