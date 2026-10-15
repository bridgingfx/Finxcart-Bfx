<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\FreelancerQuoteRequest;
use App\Services\FreelancerQuoteService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class FreelancerQuoteController extends Controller
{
    public function __construct(private readonly FreelancerQuoteService $quoteService)
    {
    }

    public function index(): View
    {
        $quotes = FreelancerQuoteRequest::where('seller_id', auth('freelancer')->id())
            ->with(['customer', 'service.images'])
            ->latest()
            ->paginate(15);

        return view('freelancer-views.quotes.index', compact('quotes'));
    }

    public function show(int $id): View
    {
        $quote = $this->ownedQuote($id);

        return view('freelancer-views.quotes.view', compact('quote'));
    }

    public function reply(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'quoted_price' => 'required|numeric|min:0|regex:/^\d+(\.\d+)?$/',
            'quoted_delivery_days' => 'required|integer|min:1|max:365|regex:/^\d+$/',
            'reply_message' => 'required|string|max:2500',
        ]);

        try {
            $this->quoteService->sendQuote(
                quoteId: $id,
                sellerId: auth('freelancer')->id(),
                data: $request->only(['quoted_price', 'quoted_delivery_days', 'reply_message']),
            );
        } catch (RuntimeException $exception) {
            ToastMagic::error($exception->getMessage());
            return redirect()->route('freelancer.quotes.view', $id);
        }

        ToastMagic::success(translate('your_reply_has_been_sent'));
        return redirect()->route('freelancer.quotes.view', $id);
    }

    private function ownedQuote(int $id): FreelancerQuoteRequest
    {
        $quote = FreelancerQuoteRequest::with(['customer', 'service'])
            ->where('id', $id)
            ->where('seller_id', auth('freelancer')->id())
            ->firstOrFail();

        return $quote;
    }
}
