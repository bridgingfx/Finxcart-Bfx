<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\FreelancerQuoteRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class FreelancerQuoteController extends Controller
{
    public function index(Request $request): View
    {
        $quotes = FreelancerQuoteRequest::query()
            ->with(['customer', 'seller.shop', 'service'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->get('status')))
            ->when($request->filled('searchValue'), function ($q) use ($request) {
                $search = $request->get('searchValue');
                $q->where(function ($query) use ($search) {
                    $query->whereHas('customer', fn ($c) => $c->where('f_name', 'like', "%{$search}%")
                        ->orWhere('l_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('seller', fn ($s) => $s->where('f_name', 'like', "%{$search}%")
                            ->orWhere('l_name', 'like', "%{$search}%"))
                        ->orWhereHas('service', fn ($s) => $s->where('title', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate((int) (getWebConfig(name: 'pagination_limit') ?? 25))
            ->appends($request->query());

        return view('admin-views.freelancer.quotes.index', compact('quotes'));
    }

    public function show(FreelancerQuoteRequest $quote): View
    {
        $quote->load(['customer', 'seller.shop', 'service', 'contract']);

        return view('admin-views.freelancer.quotes.view', compact('quote'));
    }
}
