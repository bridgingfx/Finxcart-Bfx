<?php

namespace App\Http\Controllers\Freelancer;

use App\Contracts\Repositories\FreelancerPortfolioItemRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Freelancer\FreelancerPortfolioAddRequest;
use App\Http\Requests\Freelancer\FreelancerPortfolioUpdateRequest;
use App\Models\FreelancerPortfolioItem;
use App\Services\FreelancerPortfolioService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class FreelancerPortfolioController extends Controller
{
    public function __construct(
        private readonly FreelancerPortfolioItemRepositoryInterface $portfolioRepo,
        private readonly FreelancerPortfolioService $portfolioService,
    ) {
    }

    public function index(): View
    {
        $portfolioItems = $this->portfolioRepo->getBySellerId(auth('freelancer')->id(), ['galleryItems']);

        return view('freelancer-views.portfolio.index', compact('portfolioItems'));
    }

    public function create(): View|RedirectResponse
    {
        if (!$this->isApproved()) {
            session()->flash('freelancer_kyc_popup_message', translate('your_documents_must_be_approved_by_admin_before_adding_portfolio_items'));
            return redirect()->route('freelancer.portfolio.index');
        }

        return view('freelancer-views.portfolio.create');
    }

    public function store(FreelancerPortfolioAddRequest $request): RedirectResponse
    {
        $sellerId = auth('freelancer')->id();
        $priority = $this->portfolioRepo->getBySellerId($sellerId)->count();

        $item = $this->portfolioRepo->add($this->portfolioService->getAddData($request, $sellerId, $priority));
        $this->portfolioService->syncGalleryItems($item, $request);

        ToastMagic::success(translate('portfolio_item_added_successfully'));
        return redirect()->route('freelancer.portfolio.index');
    }

    public function show(int $id): View
    {
        $item = $this->ownedItem($id, ['galleryItems']);
        if (!$item) {
            abort(404);
        }

        return view('freelancer-views.portfolio.view', compact('item'));
    }

    public function edit(int $id): View
    {
        $item = $this->ownedItem($id, ['galleryItems']);
        if (!$item) {
            abort(404);
        }

        return view('freelancer-views.portfolio.edit', compact('item'));
    }

    public function update(int $id, FreelancerPortfolioUpdateRequest $request): RedirectResponse
    {
        $item = $this->ownedItem($id);
        if (!$item) {
            abort(404);
        }

        $this->portfolioRepo->update((string)$id, $this->portfolioService->getUpdateData($request, $item));
        $this->portfolioService->syncGalleryItems($item, $request);

        ToastMagic::success(translate('portfolio_item_updated_successfully'));
        return redirect()->route('freelancer.portfolio.index');
    }

    public function activate(int $id): RedirectResponse
    {
        $item = $this->ownedItem($id);
        if (!$item) {
            abort(404);
        }

        $item->update(['is_active' => true]);

        ToastMagic::success(translate('portfolio_item_activated_successfully'));
        return redirect()->route('freelancer.portfolio.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $item = $this->ownedItem($id, ['galleryItems']);
        if (!$item) {
            abort(404);
        }

        if ($item->is_active) {
            ToastMagic::warning(translate('please_activate_another_portfolio_item_before_deleting_this_one'));
            return redirect()->route('freelancer.portfolio.index');
        }

        $this->portfolioService->deleteImage($item);
        $this->portfolioService->deleteGalleryImages($item);
        $this->portfolioRepo->delete(['id' => $id]);

        ToastMagic::success(translate('portfolio_item_deleted_successfully'));
        return redirect()->route('freelancer.portfolio.index');
    }

    private function ownedItem(int $id, array $relations = []): ?FreelancerPortfolioItem
    {
        $item = $this->portfolioRepo->getFirstWhere(['id' => $id], $relations);
        if (!$item || $item->seller_id !== auth('freelancer')->id()) {
            return null;
        }

        return $item;
    }

    private function isApproved(): bool
    {
        return auth('freelancer')->user()?->status === 'approved';
    }
}
