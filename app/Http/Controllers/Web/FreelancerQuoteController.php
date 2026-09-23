<?php

namespace App\Http\Controllers\Web;

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

    public function index(Request $request): View
    {
        $quotes = FreelancerQuoteRequest::where('customer_id', auth('customer')->id())
            ->with(['seller.shop', 'service'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view(VIEW_FILE_NAMES['freelancer_quotes_index'], compact('quotes'));
    }

    public function show(int $quote): View
    {
        $quote = FreelancerQuoteRequest::where('id', $quote)
            ->where('customer_id', auth('customer')->id())
            ->with(['seller.shop', 'service'])
            ->firstOrFail();

        return view(VIEW_FILE_NAMES['freelancer_quote_show'], compact('quote'));
    }

    public function store(Request $request, int $service): RedirectResponse
    {
        $request->validate([
            'description' => 'required|string|max:2500',
            'delivery_preference' => 'required|in:24_hours,3_days,7_days,custom',
            'custom_delivery_text' => 'required_if:delivery_preference,custom|nullable|string|max:100',
            'budget' => 'required|numeric|min:0|regex:/^\d+(\.\d+)?$/',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,pdf,webp,doc,docx,zip',
        ]);

        $this->quoteService->requestQuote(
            customerId: auth('customer')->id(),
            serviceId: $service,
            data: $request->only(['description', 'delivery_preference', 'custom_delivery_text', 'budget']),
            attachmentFiles: $request->file('attachments', []),
        );

        ToastMagic::success(translate('your_quote_request_has_been_sent'));
        return back();
    }

    public function decline(int $quote): RedirectResponse
    {
        try {
            $this->quoteService->declineQuote($quote, auth('customer')->id());
        } catch (RuntimeException $exception) {
            ToastMagic::error($exception->getMessage());
            return back();
        }

        ToastMagic::success(translate('quote_declined'));
        return back();
    }
}
