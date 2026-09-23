<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Http\Controllers\BaseController;
use App\Models\FreelancerPortfolioItem;
use App\Services\FreelancerPortfolioService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FreelancerPortfolioManageController extends BaseController
{
    public function __construct(private readonly FreelancerPortfolioService $portfolioService)
    {
    }

    public function index(?Request $request, string $type = null): View
    {
        $searchValue = $request['searchValue'];

        $portfolioItems = FreelancerPortfolioItem::with('seller.shop')
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request['status'] === 'active');
            })
            ->when($searchValue, function ($query) use ($searchValue) {
                $query->where(function ($query) use ($searchValue) {
                    $query->where('title', 'like', "%{$searchValue}%")
                        ->orWhere('description', 'like', "%{$searchValue}%")
                        ->orWhereHas('seller', function ($sellerQuery) use ($searchValue) {
                            $sellerQuery->where('f_name', 'like', "%{$searchValue}%")
                                ->orWhere('l_name', 'like', "%{$searchValue}%")
                                ->orWhere('email', 'like', "%{$searchValue}%");
                        });
                });
            })
            ->orderByDesc('id')
            ->paginate(getWebConfig(name: 'pagination_limit'))
            ->appends($request->query());

        return view('admin-views.freelancer.portfolio.index', compact('portfolioItems'));
    }

    public function show(FreelancerPortfolioItem $portfolio): View
    {
        $portfolio->load(['seller.shop', 'galleryItems']);

        return view('admin-views.freelancer.portfolio.show', ['item' => $portfolio]);
    }

    public function edit(FreelancerPortfolioItem $portfolio): View
    {
        $portfolio->load('galleryItems');

        return view('admin-views.freelancer.portfolio.edit', ['item' => $portfolio]);
    }

    public function update(Request $request, FreelancerPortfolioItem $portfolio): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tags' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'project_url' => 'nullable|url|max:255',
            'completed_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
            'gallery' => 'nullable|array',
            'gallery.*.id' => 'nullable|integer|regex:/^\d+$/',
            'gallery.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'gallery.*.url' => 'nullable|url|max:255',
        ]);

        $portfolio->update($this->portfolioService->getUpdateData($request, $portfolio));
        $this->portfolioService->syncGalleryItems($portfolio, $request);

        ToastMagic::success(translate('portfolio_item_updated_successfully'));
        return redirect()->route('admin.freelancer.portfolio.index');
    }
}