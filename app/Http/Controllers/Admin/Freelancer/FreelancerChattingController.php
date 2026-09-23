<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Contracts\Repositories\ChattingRepositoryInterface;
use App\Enums\GlobalConstant;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\ShopRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\Chatting;
use App\Models\Seller;
use App\Services\ChattingService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FreelancerChattingController extends Controller
{
    public function __construct(
        private readonly ChattingRepositoryInterface $chattingRepo,
        private readonly ShopRepositoryInterface      $shopRepo,
        private readonly CustomerRepositoryInterface  $customerRepo,
        private readonly ChattingService              $chattingService,
    )
    {
    }

    public function index(Request $request): View
    {
        $freelancerIds = Chatting::query()
            ->whereNotNull('seller_id')
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('seller_id');

        $freelancers = Seller::where('seller_type', 'freelancer')
            ->whereIn('id', $freelancerIds)
            ->orderBy('f_name')
            ->with('storage')
            ->get(['id', 'f_name', 'l_name', 'image']);

        $sellerId = $request->get('seller_id');
        $selectedFreelancer = $sellerId ? Seller::where('seller_type', 'freelancer')->find($sellerId) : null;

        $allChattingUsers = collect();
        $lastChatUser = null;
        $chattingMessages = collect();

        if ($selectedFreelancer) {
            $allChattingUsers = $this->chattingRepo->getListWhereNotNull(
                orderBy: ['id' => 'DESC'],
                filters: ['seller_id' => $selectedFreelancer->id],
                whereNotNull: ['user_id', 'seller_id'],
                relations: ['customer'],
                dataLimit: 'all'
            )->unique('user_id');

            $requestedUserId = $request->get('user_id');
            $lastChatUser = $requestedUserId
                ? $this->customerRepo->getFirstWhere(params: ['id' => $requestedUserId])
                : ($allChattingUsers->first()->customer ?? null);

            if ($lastChatUser) {
                $chattingMessages = $this->chattingRepo->getListWhereNotNull(
                    orderBy: ['id' => 'DESC'],
                    filters: ['seller_id' => $selectedFreelancer->id, 'user_id' => $lastChatUser->id],
                    whereNotNull: ['user_id', 'seller_id'],
                    dataLimit: 'all'
                );

                $this->chattingRepo->updateAllWhere(
                    params: ['seller_id' => $selectedFreelancer->id, 'user_id' => $lastChatUser->id],
                    data: ['seen_by_admin' => 1]
                );
            }
        }

        return view('admin-views.freelancer.chats.index', compact(
            'freelancers', 'selectedFreelancer', 'allChattingUsers', 'lastChatUser', 'chattingMessages'
        ));
    }

    public function sendMessage(Request $request): RedirectResponse
    {
        $request->validate([
            'seller_id' => 'required|integer|regex:/^\d+$/|exists:sellers,id',
            'user_id' => 'required|integer|regex:/^\d+$/|exists:users,id',
            'message' => 'required_without_all:file,media|nullable|string|max:2000',
            'media.*' => 'file|max:2048|mimes:' . str_replace('.', '', implode(',', GlobalConstant::MEDIA_EXTENSION)),
            'file.*' => 'file|max:2048|mimes:' . str_replace('.', '', implode(',', GlobalConstant::DOCUMENT_EXTENSION)),
        ]);

        $freelancer = Seller::where('seller_type', 'freelancer')->findOrFail($request['seller_id']);
        $shop = $this->shopRepo->getFirstWhere(params: ['seller_id' => $freelancer->id]);

        $this->chattingRepo->add(data: [
            'user_id' => $request['user_id'],
            'seller_id' => $freelancer->id,
            'shop_id' => $shop['id'] ?? null,
            'admin_id' => 0,
            'message' => $request['message'],
            'attachment' => json_encode($this->chattingService->getAttachment($request)),
            'sent_by_admin' => 1,
            'seen_by_admin' => 1,
            'seen_by_customer' => 0,
            'seen_by_seller' => 0,
            'created_at' => now(),
        ]);

        ToastMagic::success(translate('message_sent_successfully'));

        return redirect()->route('admin.freelancer.chats.index', [
            'seller_id' => $freelancer->id,
            'user_id' => $request['user_id'],
        ]);
    }
}
