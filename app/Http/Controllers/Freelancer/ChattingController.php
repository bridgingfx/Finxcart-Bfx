<?php

namespace App\Http\Controllers\Freelancer;

use App\Contracts\Repositories\ChattingRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\ShopRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Enums\ViewPaths\Freelancer\Chatting;
use App\Events\ChattingEvent;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Vendor\ChattingRequest;
use App\Models\Chatting as ChattingModel;
use App\Services\ChattingService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ChattingController extends BaseController
{
    public function __construct(
        private readonly ChattingRepositoryInterface $chattingRepo,
        private readonly ShopRepositoryInterface      $shopRepo,
        private readonly ChattingService              $chattingService,
        private readonly VendorRepositoryInterface    $vendorRepo,
        private readonly CustomerRepositoryInterface  $customerRepo,
    )
    {
    }

    public function index(?Request $request, string|array $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->getListView(type: $type);
    }

    public function getListView(string|array $type = null): View
    {
        $shop = $this->shopRepo->getFirstWhere(params: ['seller_id' => auth('freelancer')->id()]);
        $freelancerId = auth('freelancer')->id();

        $allChattingUsers = $this->chattingRepo->getListWhereNotNull(
            orderBy: ['id' => 'DESC'],
            filters: ['seller_id' => $freelancerId],
            whereNotNull: ['user_id', 'seller_id'],
            relations: ['customer'],
            dataLimit: 'all'
        )->unique('user_id');

        if (count($allChattingUsers) > 0) {
            $lastChatUser = $allChattingUsers[0]->customer;
            $this->chattingRepo->updateAllWhere(
                params: ['seller_id' => $freelancerId, 'user_id' => $lastChatUser['id']],
                data: ['seen_by_seller' => 1]
            );

            $countUnreadMessages = $this->chattingRepo->countUnreadMessages(data: [
                'seller_id' => $freelancerId,
                'usersColumn' => 'user_id',
                'filteredByColumn' => 'seen_by_seller',
                'notificationReceiver' => 'seller',
            ]);

            $chattingMessages = $this->chattingRepo->getListWhereNotNull(
                orderBy: ['id' => 'DESC'],
                filters: ['seller_id' => $freelancerId, 'user_id' => $lastChatUser->id],
                whereNotNull: ['user_id', 'seller_id'],
                relations: ['customer'],
                dataLimit: 'all'
            );

            return view(Chatting::INDEX[VIEW], [
                'userType' => 'customer',
                'allChattingUsers' => $allChattingUsers,
                'lastChatUser' => $lastChatUser,
                'chattingMessages' => $chattingMessages,
                'countUnreadMessages' => $countUnreadMessages,
            ]);
        }

        return view(Chatting::INDEX[VIEW], compact('shop'));
    }

    public function getMessageByUser(Request $request): JsonResponse
    {
        $freelancerId = auth('freelancer')->id();
        $data = [];

        if ($request->has(key: 'user_id')) {
            $getUser = $this->customerRepo->getFirstWhere(params: ['id' => $request['user_id']]);
            $this->chattingRepo->updateAllWhere(
                params: ['seller_id' => $freelancerId, 'user_id' => $request['user_id']],
                data: ['seen_by_seller' => 1]
            );
            $chattingMessages = $this->chattingRepo->getListWhereNotNull(
                orderBy: ['id' => 'DESC'],
                filters: ['seller_id' => $freelancerId, 'user_id' => $request['user_id']],
                whereNotNull: ['user_id', 'seller_id'],
                dataLimit: 'all'
            );
            $data = self::getRenderMessagesView(user: $getUser, message: $chattingMessages);
        }

        return response()->json($data);
    }

    public function addFreelancerMessage(ChattingRequest $request): JsonResponse
    {
        $data = [];
        $freelancer = $this->vendorRepo->getFirstWhere(params: ['id' => auth('freelancer')->id()]);
        $shop = $this->shopRepo->getFirstWhere(params: ['seller_id' => auth('freelancer')->id()]);

        if ($request->has(key: 'user_id')) {
            $this->chattingRepo->add(
                data: $this->chattingService->getCustomerChattingData(
                    request: $request,
                    shopId: $shop['id'],
                    vendorId: $freelancer['id']
                )
            );
            $customer = $this->customerRepo->getFirstWhere(params: ['id' => $request['user_id']]);
            event(new ChattingEvent(key: 'message_from_seller', type: 'customer', userData: $customer, messageForm: $freelancer, messageText: $request['message'] ?? null));

            $chattingMessages = $this->chattingRepo->getListWhereNotNull(
                orderBy: ['id' => 'DESC'],
                filters: ['seller_id' => $freelancer['id'], 'user_id' => $request['user_id']],
                whereNotNull: ['user_id', 'seller_id'],
                dataLimit: 'all'
            );
            $data = self::getRenderMessagesView(user: $customer, message: $chattingMessages);
        }

        return response()->json($data);
    }

    public function markTyping(Request $request): JsonResponse
    {
        $freelancerId = auth('freelancer')->id();
        if ($request->has(key: 'user_id')) {
            $this->chattingService->markTyping('seller', $freelancerId, 'customer', $request['user_id']);
        }

        return response()->json(['status' => true]);
    }

    public function typingStatus(Request $request): JsonResponse
    {
        $freelancerId = auth('freelancer')->id();
        $typing = $request->has(key: 'user_id')
            && $this->chattingService->isTyping('customer', $request['user_id'], 'seller', $freelancerId);

        return response()->json(['typing' => (bool) $typing]);
    }

    protected function getRenderMessagesView(object $user, object $message): array
    {
        $userData = [
            'name' => $user['f_name'] . ' ' . $user['l_name'],
            'phone' => $user['country_code'] . $user['phone'],
            'lastActive' => translate('last_login') . ' ' . ($user->updated_at?->diffForHumans() ?? translate('not_available')),
            'detailsRoute' => '#',
            'image' => getStorageImages(path: $user->image_full_url, type: 'backend-profile'),
        ];

        return [
            'userData' => $userData,
            'chattingMessages' => view('freelancer-views.chatting.messages', [
                'lastChatUser' => $user,
                'userType' => 'customer',
                'chattingMessages' => $message,
            ])->render(),
        ];
    }

    public function getAdminMessagesView(): View
    {
        $freelancerId = auth('freelancer')->id();

        ChattingModel::where('seller_id', $freelancerId)
            ->where('admin_id', 0)
            ->whereNull('user_id')
            ->whereNull('delivery_man_id')
            ->update(['seen_by_seller' => 1]);

        $chattingMessages = ChattingModel::where('seller_id', $freelancerId)
            ->where('admin_id', 0)
            ->whereNull('user_id')
            ->whereNull('delivery_man_id')
            ->orderBy('id')
            ->get();

        return view('freelancer-views.chatting.admin', compact('chattingMessages'));
    }

    public function pollAdminMessages(): JsonResponse
    {
        $freelancerId = auth('freelancer')->id();

        ChattingModel::where('seller_id', $freelancerId)
            ->where('admin_id', 0)
            ->whereNull('user_id')
            ->whereNull('delivery_man_id')
            ->update(['seen_by_seller' => 1]);

        $chattingMessages = ChattingModel::where('seller_id', $freelancerId)
            ->where('admin_id', 0)
            ->whereNull('user_id')
            ->whereNull('delivery_man_id')
            ->orderBy('id')
            ->get();

        return response()->json([
            'html' => $chattingMessages->isEmpty()
                ? null
                : view('freelancer-views.chatting.messages', ['lastChatUser' => null, 'chattingMessages' => $chattingMessages])->render(),
        ]);
    }

    public function markTypingToAdmin(): JsonResponse
    {
        $this->chattingService->markTyping('seller', auth('freelancer')->id(), 'admin', 0);

        return response()->json(['status' => true]);
    }

    public function adminTypingStatus(): JsonResponse
    {
        $typing = $this->chattingService->isTyping('admin', 0, 'seller', auth('freelancer')->id());

        return response()->json(['typing' => $typing]);
    }

    public function sendAdminMessage(ChattingRequest $request): JsonResponse
    {
        $freelancerId = auth('freelancer')->id();
        $shop = $this->shopRepo->getFirstWhere(params: ['seller_id' => $freelancerId]);

        ChattingModel::create([
            'seller_id' => $freelancerId,
            'admin_id' => 0,
            'shop_id' => $shop['id'] ?? null,
            'message' => $request['message'],
            'attachment' => json_encode($this->chattingService->getAttachment($request)),
            'sent_by_seller' => 1,
            'seen_by_seller' => 1,
            'seen_by_admin' => 0,
            'notification_receiver' => 'admin',
            'created_at' => now(),
        ]);

        $chattingMessages = ChattingModel::where('seller_id', $freelancerId)
            ->where('admin_id', 0)
            ->whereNull('user_id')
            ->whereNull('delivery_man_id')
            ->orderBy('id')
            ->get();

        return response()->json([
            'html' => view('freelancer-views.chatting.messages', ['lastChatUser' => null, 'chattingMessages' => $chattingMessages])->render(),
        ]);
    }

    public function getNewNotification(): JsonResponse
    {
        $freelancerId = auth('freelancer')->id();
        $chatting = $this->chattingRepo->getListWhereNotNull(
            filters: ['seller_id' => $freelancerId, 'seen_by_seller' => 0, 'notification_receiver' => 'seller', 'seen_notification' => 0],
            whereNotNull: ['seller_id'],
        )->count();

        $this->chattingRepo->updateListWhereNotNull(
            filters: ['seller_id' => $freelancerId, 'seen_by_seller' => 0, 'notification_receiver' => 'seller', 'seen_notification' => 0],
            whereNotNull: ['seller_id'],
            data: ['seen_notification' => 1]
        );

        return response()->json([
            'newMessagesExist' => $chatting,
            'message' => $chatting > 1 ? $chatting . ' ' . translate('New_Message') : translate('New_Message'),
        ]);
    }
}
