<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Enums\GlobalConstant;
use App\Events\ChattingEvent;
use App\Http\Controllers\Controller;
use App\Models\Chatting;
use App\Models\Seller;
use App\Services\ChattingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FreelancerMessageController extends Controller
{
    public function __construct(private readonly ChattingService $chattingService)
    {
    }
    public function index(Request $request): View
    {
        $freelancers = Seller::where('seller_type', 'freelancer')
            ->orderBy('f_name')
            ->with('storage')
            ->get(['id', 'f_name', 'l_name', 'image']);

        $sellerId = $request->get('seller_id');
        $selectedFreelancer = $sellerId ? Seller::where('seller_type', 'freelancer')->find($sellerId) : null;

        $chattingMessages = collect();

        if ($selectedFreelancer) {
            Chatting::where('admin_id', 0)
                ->where('seller_id', $selectedFreelancer->id)
                ->whereNull('user_id')
                ->whereNull('delivery_man_id')
                ->update(['seen_by_admin' => 1]);

            $chattingMessages = Chatting::where('admin_id', 0)
                ->where('seller_id', $selectedFreelancer->id)
                ->whereNull('user_id')
                ->whereNull('delivery_man_id')
                ->orderBy('id')
                ->get();
        }

        $unreadCounts = Chatting::where('admin_id', 0)
            ->whereIn('seller_id', $freelancers->pluck('id'))
            ->whereNull('user_id')
            ->whereNull('delivery_man_id')
            ->where('sent_by_seller', 1)
            ->where('seen_by_admin', 0)
            ->selectRaw('seller_id, COUNT(*) as total')
            ->groupBy('seller_id')
            ->pluck('total', 'seller_id');

        return view('admin-views.freelancer.messages.index', compact(
            'freelancers', 'selectedFreelancer', 'chattingMessages', 'unreadCounts'
        ));
    }

    public function unreadCounts(): JsonResponse
    {
        $freelancerIds = Seller::where('seller_type', 'freelancer')->pluck('id');

        $unreadCounts = Chatting::where('admin_id', 0)
            ->whereIn('seller_id', $freelancerIds)
            ->whereNull('user_id')
            ->whereNull('delivery_man_id')
            ->where('sent_by_seller', 1)
            ->where('seen_by_admin', 0)
            ->selectRaw('seller_id, COUNT(*) as total')
            ->groupBy('seller_id')
            ->pluck('total', 'seller_id');

        return response()->json([
            'counts' => $unreadCounts,
        ]);
    }

    public function threadMessages(Request $request): JsonResponse
    {
        $freelancer = Seller::where('seller_type', 'freelancer')->findOrFail($request->get('seller_id'));

        Chatting::where('admin_id', 0)
            ->where('seller_id', $freelancer->id)
            ->whereNull('user_id')
            ->whereNull('delivery_man_id')
            ->update(['seen_by_admin' => 1]);

        $chattingMessages = Chatting::where('admin_id', 0)
            ->where('seller_id', $freelancer->id)
            ->whereNull('user_id')
            ->whereNull('delivery_man_id')
            ->orderBy('id')
            ->get();

        return response()->json([
            'html' => $chattingMessages->isEmpty()
                ? null
                : view('admin-views.freelancer.chats.messages', ['chattingMessages' => $chattingMessages])->render(),
        ]);
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'seller_id' => 'required|integer|regex:/^\d+$/|exists:sellers,id',
            'message' => 'required_without_all:file,media|nullable|string|max:2000',
            'media.*' => 'file|max:2048|mimes:' . str_replace('.', '', implode(',', GlobalConstant::MEDIA_EXTENSION)),
            'file.*' => 'file|max:2048|mimes:' . str_replace('.', '', implode(',', GlobalConstant::DOCUMENT_EXTENSION)),
        ]);

        $freelancer = Seller::where('seller_type', 'freelancer')->findOrFail($request['seller_id']);

        Chatting::create([
            'seller_id' => $freelancer->id,
            'admin_id' => 0,
            'message' => $request['message'],
            'attachment' => json_encode($this->chattingService->getAttachment($request)),
            'sent_by_admin' => 1,
            'seen_by_admin' => 1,
            'seen_by_seller' => 0,
            'notification_receiver' => 'seller',
            'created_at' => now(),
        ]);

        $messageForm = (object) [
            'f_name' => 'Admin',
            'l_name' => '',
            'shop' => (object) ['name' => getWebConfig(name: 'company_name')],
        ];
        event(new ChattingEvent(key: 'message_from_admin', type: 'freelancer', userData: $freelancer, messageForm: $messageForm, messageText: $request['message']));

        $chattingMessages = Chatting::where('admin_id', 0)
            ->where('seller_id', $freelancer->id)
            ->whereNull('user_id')
            ->whereNull('delivery_man_id')
            ->orderBy('id')
            ->get();

        return response()->json([
            'html' => view('admin-views.freelancer.chats.messages', ['chattingMessages' => $chattingMessages])->render(),
        ]);
    }

    public function markTyping(Request $request): JsonResponse
    {
        if ($request->has(key: 'seller_id')) {
            $this->chattingService->markTyping('admin', 0, 'seller', $request['seller_id']);
        }

        return response()->json(['status' => true]);
    }

    public function typingStatus(Request $request): JsonResponse
    {
        $typing = $request->has(key: 'seller_id')
            && $this->chattingService->isTyping('seller', $request['seller_id'], 'admin', 0);

        return response()->json(['typing' => (bool) $typing]);
    }
}
