<?php

namespace App\Http\Controllers\Vendor\Product;

use App\Contracts\Repositories\AttributeRepositoryInterface;
use App\Contracts\Repositories\AuthorRepositoryInterface;
use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\ColorRepositoryInterface;
use App\Contracts\Repositories\DealOfTheDayRepositoryInterface;
use App\Contracts\Repositories\DigitalProductAuthorRepositoryInterface;
use App\Contracts\Repositories\DigitalProductVariationRepositoryInterface;
use App\Contracts\Repositories\FlashDealProductRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\RestockProductRepositoryInterface;
use App\Contracts\Repositories\RestockProductCustomerRepositoryInterface;
use App\Contracts\Repositories\ProductSeoRepositoryInterface;
use App\Contracts\Repositories\PublishingHouseRepositoryInterface;
use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Contracts\Repositories\StockClearanceProductRepositoryInterface;
use App\Contracts\Repositories\StockClearanceSetupRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Contracts\Repositories\WishlistRepositoryInterface;
use App\Enums\ViewPaths\Vendor\Product;
use App\Enums\WebConfigKey;
use App\Exports\ProductListExport;
use App\Exports\RestockProductListExport;
use App\Http\Controllers\BaseController;
use App\Http\Requests\ProductAddRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Repositories\DigitalProductPublishingHouseRepository;
use App\Repositories\TranslationRepository;
use App\Services\ProductService;
use App\Services\VendorTierService; // <-- IMPORTED
use App\Utils\CategoryManager;
use App\Models\AdminNotification;
use App\Models\Color;
use App\Traits\FileManagerTrait;
use App\Traits\ProductTrait;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

// --- ADD THESE 'use' STATEMENTS ---
use Illuminate\Support\Facades\Auth;
use App\Models\VendorTier;
use App\Models\SellerContract;
use App\Models\Product as ProductModel; // Alias Product model

// ---------------------------------

class ProductController extends BaseController
{
    use ProductTrait;
    use FileManagerTrait {
        delete as deleteFile;
        update as updateFile;
    }

    public function __construct(
        private readonly AuthorRepositoryInterface $authorRepo,
        private readonly PublishingHouseRepositoryInterface $publishingHouseRepo,
        private readonly DigitalProductAuthorRepositoryInterface $digitalProductAuthorRepo,
        private readonly DigitalProductPublishingHouseRepository $digitalProductPublishingHouseRepo,
        private readonly CategoryRepositoryInterface $categoryRepo,
        private readonly BrandRepositoryInterface $brandRepo,
        private readonly ProductRepositoryInterface $productRepo,
        private readonly DigitalProductVariationRepositoryInterface $digitalProductVariationRepo,
        private readonly StockClearanceProductRepositoryInterface $stockClearanceProductRepo,
        private readonly StockClearanceSetupRepositoryInterface $stockClearanceSetupRepo,
        private readonly ProductSeoRepositoryInterface $productSeoRepo,
        private readonly RestockProductRepositoryInterface $restockProductRepo,
        private readonly RestockProductCustomerRepositoryInterface $restockProductCustomerRepo,
        private readonly TranslationRepository $translationRepo,
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
        private readonly ColorRepositoryInterface $colorRepo,
        private readonly AttributeRepositoryInterface $attributeRepo,
        private readonly ReviewRepositoryInterface $reviewRepo,
        private readonly CartRepositoryInterface $cartRepo,
        private readonly WishlistRepositoryInterface $wishlistRepo,
        private readonly FlashDealProductRepositoryInterface $flashDealProductRepo,
        private readonly DealOfTheDayRepositoryInterface $dealOfTheDayRepo,
        private readonly VendorRepositoryInterface $vendorRepo,
        private readonly ProductService $productService,
        private readonly VendorTierService $vendorTierService // <-- INJECTED
    )
    {
    }

    public function index(?Request $request, string|array $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->getListView(request: $request, type: $type);
    }

    public function getListView(Request $request, $type): View
    {
        $vendorId   = auth('seller')->id();
        $seller     = auth('seller')->user();
        $activeTier = $this->vendorTierService->getActiveTierForSeller($vendorId);
        $tierExpired = (
            !$activeTier
            || ($activeTier->status === 'trial' && $activeTier->trial_end_date && now()->gt($activeTier->trial_end_date))
            || ($activeTier->end_date && now()->gt($activeTier->end_date) && $activeTier->status !== 'trial')
        );
        $hasEverHadTier = \App\Models\VendorTier::where('seller_id', $vendorId)->exists();
        $activeTiersList = \App\Models\VendorTier::with('tier')
            ->where('seller_id', $vendorId)
            ->whereIn('status', ['active', 'trial'])
            ->where(function ($q) {
                $today = now()->toDateString();
                $q->where(fn($a) => $a->where('status', 'active')->whereDate('end_date', '>=', $today))
                  ->orWhere(fn($t) => $t->where('status', 'trial')->whereDate('trial_end_date', '>=', $today));
            })
            ->get();
        $usedSlots  = $this->vendorTierService->getSellerProductCount($vendorId);
        $tierProductCounts = \App\Models\Product::whereIn('vendor_tier_id', $activeTiersList->pluck('id'))
            ->selectRaw('vendor_tier_id, count(*) as total')
            ->groupBy('vendor_tier_id')
            ->pluck('total', 'vendor_tier_id');
        $totalSlots = $activeTiersList->sum(fn($vt) => (int)($vt->tier->listings_per_fee ?? 0));
        $tierName   = $activeTiersList->count() > 1
            ? translate('All_Plans')
            : (optional(optional($activeTier)->tier)->name ?? 'No Plan');

        $filters = [
            'added_by' => 'seller',
            'seller_id' => $vendorId,
            'brand_id' => $request['brand_id'],
            'category_id' => $request['category_id'],
            'sub_category_id' => $request['sub_category_id'],
            'sub_sub_category_id' => $request['sub_sub_category_id'],
            'request_status' => $type == 'new-request' ? 0 : ($type == 'approved' ? '1' : ($type == 'denied' ? '2' : 'all')),
        ];
        $searchValue = $request['searchValue'];
        $products = $this->productRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $searchValue,
            filters: $filters,
            relations: ['translations', 'seoInfo', 'vendorTier.tier', 'clearanceSale' => function ($query) { // <-- ADDED vendorTier.tier
                return $query->active();
            }],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT),
            // Columns actually used by vendor-views/product/list.blade.php + its
            // _product-row.blade.php partial, incl. thumbnail_full_url's raw columns
            // and vendor_tier_id (read directly via groupBy()/filter() on the
            // collection, not just through the vendorTier relation).
            select: ['id', 'added_by', 'user_id', 'vendor_tier_id', 'name', 'product_type', 'unit_price', 'featured', 'status', 'request_status', 'thumbnail', 'thumbnail_storage_type'],
        );
        $brands = $this->brandRepo->getListWhere(filters: ['status' => 1], dataLimit: 'all');
        $categories = $this->categoryRepo->getListWhere(filters: ['position' => 0], dataLimit: 'all');
        $subCategory = $this->categoryRepo->getFirstWhere(params: ['id' => $request['sub_category_id']]);
        $subSubCategory = $this->categoryRepo->getFirstWhere(params: ['id' => $request['sub_sub_category_id']]);

        // --- NEW: Get available tiers for the modal ---
        $availableTiers = $this->vendorTierService->getAvailableTiers();

        return view(Product::LIST[VIEW], compact('products', 'type', 'searchValue', 'brands',
            'categories', 'subCategory', 'subSubCategory', 'filters',
            'availableTiers', 'totalSlots', 'usedSlots', 'tierName', 'tierExpired', 'hasEverHadTier', 'activeTiersList', 'tierProductCounts'
        ));
    }

    public function getRequestRestockListView(Request $request): View|RedirectResponse
    {
        // ... (This method is unchanged)
        $filters = [
            'added_by' => 'seller',
            'seller_id' => auth('seller')->id(),
            'brand_id' => $request['brand_id'],
            'category_id' => $request['category_id'],
            'sub_category_id' => $request['sub_category_id'],
        ];

        $startDate = '';
        $endDate = '';
        if (isset($request['restock_date']) && !empty($request['restock_date'])) {
            $dates = explode(' - ', $request['restock_date']);
            if (count($dates) !== 2 || !checkDateFormatInMDY($dates[0]) || !checkDateFormatInMDY($dates[1])) {
                ToastMagic::error(translate('Invalid_date_range_format'));
                return back();
            }
            $startDate = Carbon::createFromFormat('m/d/Y', $dates[0])->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $dates[1])->endOfDay();
        }
        $restockProducts = $this->restockProductRepo->getListWhereBetween(
            orderBy: ['updated_at' => 'desc'],
            searchValue: $request['searchValue'],
            filters: $filters,
            relations: ['product'],
            whereBetween: 'created_at',
            whereBetweenFilters: $startDate && $endDate ? [$startDate, $endDate] : [],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT),
        );
        $brands = $this->brandRepo->getListWhere(filters: ['status' => 1], dataLimit: 'all');
        $categories = $this->categoryRepo->getListWhere(filters: ['position' => 0], dataLimit: 'all');
        $subCategory = $this->categoryRepo->getFirstWhere(params: ['id' => $request['sub_category_id']]);
        $totalRestockProducts = $this->restockProductRepo->getListWhere(filters: $filters, dataLimit: 'all')->count();
        return view(\App\Enums\ViewPaths\Vendor\Product::REQUEST_RESTOCK_LIST[VIEW], compact('restockProducts', 'brands',
            'categories', 'subCategory', 'filters', 'totalRestockProducts'));
    }

    public function deleteRestock(string|int $id): RedirectResponse
    {
        // ... (This method is unchanged)
        $this->restockProductRepo->delete(params: ['id' => $id]);
        $this->restockProductCustomerRepo->delete(params: ['restock_product_id' => $id]);
        ToastMagic::success(translate('product_restock_removed_successfully'));
        return back();
    }

    public function getAddView(Request $request): View|RedirectResponse
    {
        $vendorId   = auth('seller')->id();
        $seller     = auth('seller')->user();

        // Account status gate — pending/rejected sellers cannot add products
        if (in_array($seller->status, ['pending', 'rejected'], true)) {
            return redirect()->route('vendor.dashboard.index')
                ->with('error', $seller->status === 'rejected'
                    ? translate('Your_account_verification_was_rejected._Please_resubmit_your_documents.')
                    : translate('Your_account_is_pending_approval._You_cannot_add_products_yet.')
                );
        }

        // Tier gate — non-freelancers must have an active tier
        $activeTier = $this->vendorTierService->getActiveTierForSeller($vendorId);

        $tierExpired = (
            !$activeTier
            || ($activeTier->status === 'trial' && $activeTier->trial_end_date && now()->gt($activeTier->trial_end_date))
            || ($activeTier->end_date && now()->gt($activeTier->end_date) && $activeTier->status !== 'trial')
        );

        if ($tierExpired) {
            session()->flash('pending_product_session', true);
            $hasEverHadTier = \App\Models\VendorTier::where('seller_id', $vendorId)->exists();
            $message = $hasEverHadTier
                ? translate('Your_subscription_has_ended._Please_select_a_plan_to_continue.')
                : translate("You_don't_have_a_tier_plan_yet._Please_purchase_a_plan_to_start_listing_products.");
            return redirect()->route('vendor.tier.index')->with('error', $message);
        }

        // Resolve which tier this add-product page is scoped to
        $requestedTierId = (int) $request->query('vendor_tier_id', 0);
        $selectedTier = $requestedTierId
            ? VendorTier::with('tier')->where('id', $requestedTierId)->where('seller_id', $vendorId)->whereIn('status', ['active', 'trial'])->first()
            : null;
        $selectedTier = $selectedTier ?? $activeTier;
        $tierDetails  = $selectedTier?->tier ?? null;

        // Slot data — scoped to the selected tier only
        $totalSlots = (int) optional($tierDetails)->listings_per_fee;
        $usedSlots  = \App\Models\Product::where('vendor_tier_id', $selectedTier?->id)->count();
        $tierName   = optional($tierDetails)->name ?? 'Active Plan';

        $categories = $this->categoryRepo->getListWhere(filters: ['position' => 0], dataLimit: 'all');
        $selectedCategoryRows = [];
        // brands/colors/attributes/brandSetting intentionally not loaded here — the
        // vendor add-new.blade.php view (unlike the edit view) never references them.
        $digitalProductSetting = getWebConfig(name: 'digital_product');
        $languages = getWebConfig(name: 'pnc_language') ?? null;
        $defaultLanguage = $languages[0];
        $digitalProductFileTypes = ['audio', 'video', 'document', 'software'];
        $digitalProductAuthors = $this->authorRepo->getListWhere(dataLimit: 'all');
        $publishingHouseList = $this->publishingHouseRepo->getListWhere(dataLimit: 'all');
        return view(Product::ADD[VIEW], compact(
            'languages', 'categories', 'selectedCategoryRows',
            'digitalProductSetting',
            'defaultLanguage', 'digitalProductFileTypes', 'digitalProductAuthors',
            'publishingHouseList', 'selectedTier', 'tierDetails',
            'totalSlots', 'usedSlots', 'tierName', 'tierExpired'
        ));
    }

    public function add(ProductAddRequest $request, ProductService $service): View|JsonResponse|RedirectResponse
    {
        // The client submits this endpoint once via AJAX (so validation failures come back
        // as JSON for inline field errors instead of a raw-JSON page navigation — see
        // ProductAddRequest::failedValidation(), which always returns JSON regardless of
        // request type). This used to short-circuit here with a "validation passed" no-op,
        // then the client immediately re-submitted the same multipart form natively to
        // actually save it — silently uploading every image/video/file twice over the wire.
        // Saving directly on this same request removes that duplicate upload entirely; the
        // AJAX branches below return a redirect URL instead of a Location header so the
        // client can navigate there itself.
        $isAjax = $request->ajax();

        $seller = auth('seller')->user();

        // If vendor already has an active/trial tier, save product directly without tier re-selection
        $submittedTierId = (int) $request->input('vendor_tier_id', 0);

        if ($submittedTierId) {
            // Specific tier requested (via slot button) — validate it has space
            $activeTier = VendorTier::with('tier')
                ->where('id', $submittedTierId)
                ->where('seller_id', $seller->id)
                ->whereIn('status', ['active', 'trial'])
                ->first();

            if ($activeTier) {
                $limit = $this->vendorTierService->getTierListingLimit($activeTier->tier);
                $used  = \App\Models\Product::where('vendor_tier_id', $activeTier->id)->count();
                if ($limit !== PHP_INT_MAX && $used >= $limit) {
                    try {
                        \App\Events\TierThresholdReachedEvent::dispatch(
                            $seller->id,
                            $seller->email,
                            $used,
                            $limit,
                            $activeTier->tier->name ?? translate('current_tier'),
                            route('vendor.tier.index'),
                        );
                    } catch (\Throwable $e) {
                        \Log::error('[Vendor ProductController] Tier threshold notification failed: ' . $e->getMessage());
                    }

                    ToastMagic::error(translate('This_tier_slot_is_full._Please_choose_another_tier.'));
                    if ($isAjax) {
                        $request->flash();
                        return response()->json(['redirect_url' => url()->previous()], 200);
                    }
                    return back()->withInput();
                }
            }
        } else {
            // No tier specified (sidebar Add New Product) — find first available tier with free slots
            $activeTier = $this->vendorTierService->getAvailableTiers($seller->id)->first() ?? null;
        }

        if ($activeTier) {
            $dataArray = $service->getAddProductData(request: $request, addedBy: 'seller');
            $dataArray['vendor_tier_id'] = $activeTier->id;

            $savedProduct = $this->productRepo->add(data: $dataArray);
            $this->productRepo->addRelatedTags(request: $request, product: $savedProduct);
            $this->productRepo->addRelatedCategories(request: $request, product: $savedProduct);
            $this->translationRepo->add(request: $request, model: 'App\Models\Product', id: $savedProduct->id);
            $this->updateProductAuthorAndPublishingHouse(request: $request, product: $savedProduct);

            $digitalFileArray = $service->getAddProductDigitalVariationData(request: $request, product: $savedProduct);
            foreach ($digitalFileArray as $digitalFile) {
                $this->digitalProductVariationRepo->add(data: $digitalFile);
            }

            $this->productSeoRepo->add(data: $service->getProductSEOData(request: $request, product: $savedProduct, action: 'add'));

            $this->notifyAdminOfProductSubmission($savedProduct, $seller);

            ToastMagic::success(translate('product_added_successfully'));
            session()->flash('clear_pending_product_stage', true);
            if ($isAjax) {
                return response()->json(['redirect_url' => route('vendor.products.list', ['type' => 'new-request'])], 200);
            }
            return redirect()->route('vendor.products.list', ['type' => 'new-request']);
        }

        // No active tier — save form to session, redirect vendor to tier selection/payment
        $sellerId = auth('seller')->id();
        $tempFiles = $this->storePendingProductTempFiles($request, $sellerId);

        session()->put('pending_product', [
            'data' => $request->except(['image', 'images', 'preview_file', 'meta_image', 'digital_file_ready']),
            'temp_files' => $tempFiles,
            'seller_id' => $sellerId,
            'created_at' => now()->timestamp,
        ]);

        session()->flash('pending_product_session', true);
        if ($isAjax) {
            return response()->json(['redirect_url' => route('vendor.tier.index')], 200);
        }
        return redirect()->route('vendor.tier.index');
    }


    protected function storePendingProductTempFiles(Request $request, int $sellerId): array
    {
        $tempDirectory = "temp/{$sellerId}";
        $storedFiles = [
            'image' => null,
            'images' => [],
            'color_images' => [],
            'preview_file' => null,
            'meta_image' => null,
            'digital_file_ready' => null,
            'digital_files' => [],
        ];

        if ($request->hasFile('image')) {
            $storedFiles['image'] = $this->storeTempUploadedFile($request->file('image'), $tempDirectory);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images', []) as $image) {
                if ($image instanceof UploadedFile) {
                    $storedFiles['images'][] = $this->storeTempUploadedFile($image, $tempDirectory);
                }
            }
        }

        foreach (array_keys($request->all()) as $key) {
            if (str_starts_with($key, 'color_image_') && $request->hasFile($key)) {
                $storedFiles['color_images'][$key] = $this->storeTempUploadedFile($request->file($key), $tempDirectory);
            }
        }

        if ($request->hasFile('preview_file')) {
            $storedFiles['preview_file'] = $this->storeTempUploadedFile($request->file('preview_file'), $tempDirectory);
        }

        if ($request->hasFile('meta_image')) {
            $storedFiles['meta_image'] = $this->storeTempUploadedFile($request->file('meta_image'), $tempDirectory);
        }

        if ($request->hasFile('digital_file_ready')) {
            $storedFiles['digital_file_ready'] = $this->storeTempUploadedFile($request->file('digital_file_ready'), $tempDirectory);
        }

        if ($request->hasFile('digital_files')) {
            foreach ((array) $request->file('digital_files') as $variantKey => $file) {
                if ($file instanceof UploadedFile) {
                    $storedFiles['digital_files'][$variantKey] = $this->storeTempUploadedFile($file, $tempDirectory);
                }
            }
        }

        return $storedFiles;
    }

    protected function storeTempUploadedFile(UploadedFile $file, string $tempDirectory): string
    {
        $storageName = 'public';
        $extension = $file->getClientOriginalExtension() ?: $file->extension() ?: 'bin';
        $fileName = Carbon::now()->format('YmdHis') . '-' . Str::random(12) . '.' . $extension;
        Storage::disk($storageName)->makeDirectory($tempDirectory);
        Storage::disk($storageName)->putFileAs($tempDirectory, $file, $fileName);

        return $tempDirectory . '/' . $fileName;
    }

    public function savePendingProductFromSession(int $vendorTierId, ProductService $service): ?ProductModel
    {
        $pendingProduct = session('pending_product');
        $sellerId = auth('seller')->id();

        if (!$pendingProduct || (int) ($pendingProduct['seller_id'] ?? 0) !== (int) $sellerId) {
            return null;
        }

        $stagedRequest = $this->buildPendingProductRequest($pendingProduct['data'], $pendingProduct['temp_files']);
        $dataArray = $service->getAddProductData(request: $stagedRequest, addedBy: 'seller');
        $dataArray['vendor_tier_id'] = $vendorTierId;

        $savedProduct = $this->productRepo->add(data: $dataArray);

        $this->productRepo->addRelatedTags(request: $stagedRequest, product: $savedProduct);
        $this->productRepo->addRelatedCategories(request: $stagedRequest, product: $savedProduct);
        $this->translationRepo->add(request: $stagedRequest, model: 'App\Models\Product', id: $savedProduct->id);
        $this->updateProductAuthorAndPublishingHouse(request: $stagedRequest, product: $savedProduct);

        $digitalFileArray = $service->getAddProductDigitalVariationData(request: $stagedRequest, product: $savedProduct);
        foreach ($digitalFileArray as $digitalFile) {
            $this->digitalProductVariationRepo->add(data: $digitalFile);
        }

        $this->productSeoRepo->add(data: $service->getProductSEOData(request: $stagedRequest, product: $savedProduct, action: 'add'));

        $this->notifyAdminOfProductSubmission($savedProduct, auth('seller')->user());

        $this->cleanupPendingProductTempFiles($pendingProduct['temp_files'] ?? [], $sellerId);
        session()->forget('pending_product');

        return $savedProduct;
    }

    protected function notifyAdminOfProductSubmission(ProductModel $product, $seller): void
    {
        try {
            $shopName = $seller?->shop?->name ?? $seller?->f_name ?? 'A vendor';
            AdminNotification::create([
                'type' => 'product_submission',
                'title' => 'New Product Submitted',
                'message' => "{$shopName} submitted \"{$product->name}\" for approval.",
                'link' => route('admin.products.list', ['type' => 'new-request']),
                'reference_id' => $product->id,
            ]);
        } catch (\Throwable $e) {
            \Log::error('[Vendor ProductController] Admin notification failed: ' . $e->getMessage());
        }
    }

    protected function buildPendingProductRequest(array $data, array $tempFiles): Request
    {
        $request = Request::create('/', 'POST', $data);

        if (!empty($tempFiles['image'])) {
            $request->files->set('image', $this->restorePendingUploadedFile($tempFiles['image']));
        }

        if (!empty($tempFiles['images'])) {
            $restoredImages = [];
            foreach ($tempFiles['images'] as $path) {
                $restoredImages[] = $this->restorePendingUploadedFile($path);
            }
            $request->files->set('images', $restoredImages);
        }

        if (!empty($tempFiles['color_images'])) {
            foreach ($tempFiles['color_images'] as $fieldName => $path) {
                $request->files->set($fieldName, $this->restorePendingUploadedFile($path));
            }
        }

        if (!empty($tempFiles['preview_file'])) {
            $request->files->set('preview_file', $this->restorePendingUploadedFile($tempFiles['preview_file']));
        }

        if (!empty($tempFiles['meta_image'])) {
            $request->files->set('meta_image', $this->restorePendingUploadedFile($tempFiles['meta_image']));
        }

        if (!empty($tempFiles['digital_file_ready'])) {
            $request->files->set('digital_file_ready', $this->restorePendingUploadedFile($tempFiles['digital_file_ready']));
        }

        if (!empty($tempFiles['digital_files'])) {
            $digitalFiles = [];
            foreach ($tempFiles['digital_files'] as $variantKey => $path) {
                $digitalFiles[$variantKey] = $this->restorePendingUploadedFile($path);
            }
            $request->files->set('digital_files', $digitalFiles);
        }

        return $request;
    }

    protected function restorePendingUploadedFile(string $relativePath): UploadedFile
    {
        $absolutePath = storage_path('app/public/' . $relativePath);
        $originalName = basename($relativePath);
        $mimeType = @mime_content_type($absolutePath) ?: null;

        return new UploadedFile($absolutePath, $originalName, $mimeType, null, true);
    }

    protected function cleanupPendingProductTempFiles(array $tempFiles, int $sellerId): void
    {
        $storageName = 'public';

        foreach (['image', 'preview_file', 'meta_image', 'digital_file_ready'] as $key) {
            if (!empty($tempFiles[$key])) {
                Storage::disk($storageName)->delete($tempFiles[$key]);
            }
        }

        foreach (['images', 'digital_files'] as $key) {
            foreach (($tempFiles[$key] ?? []) as $path) {
                Storage::disk($storageName)->delete($path);
            }
        }

        foreach (($tempFiles['color_images'] ?? []) as $path) {
            Storage::disk($storageName)->delete($path);
        }

        Storage::disk($storageName)->deleteDirectory("temp/{$sellerId}");
    }

    public function getUpdateView(string|int $id): RedirectResponse|View
    {
        $product = $this->productRepo->getFirstWhereWithoutGlobalScope(params: ['id' => $id, 'user_id' => auth('seller')->id(), 'added_by' => 'seller'], relations: ['translations', 'seoInfo', 'digitalProductAuthors', 'digitalProductPublishingHouse', 'vendorTier.tier', 'categories']); // <-- Load associated tier
        if (!$product) {
            ToastMagic::error(translate('invalid_product'));
            return redirect()->route('vendor.products.list', ['type' => 'all']);
        }

        if ($product['request_status'] == 3) {
            ToastMagic::error(translate('This_product_is_cancelled/disabled_and_cannot_be_edited.'));
            return redirect()->route('vendor.products.list', ['type' => 'all']);
        }

        $productAuthorIds = $this->productService->getProductAuthorsInfo(product: $product)['ids'];
        $productPublishingHouseIds = $this->productService->getProductPublishingHouseInfo(product: $product)['ids'];

        $product['colors'] = json_decode($product['colors']) ?? [];
        $categories = $this->categoryRepo->getListWhere(filters: ['position' => 0], dataLimit: 'all');
        $selectedCategoryRows = CategoryManager::buildCategoryRows($product->categories);
        $brands = $this->brandRepo->getListWhere(filters: ['status' => 1], dataLimit: 'all', lightweight: true);
        $brandSetting = getWebConfig(name: 'product_brand');
        $digitalProductSetting = getWebConfig(name: 'digital_product');
        $colors = $this->colorRepo->getList(orderBy: ['name' => 'desc'], dataLimit: 'all');
        $attributes = $this->attributeRepo->getList(orderBy: ['name' => 'desc'], dataLimit: 'all');
        $languages = getWebConfig(name: 'pnc_language') ?? null;
        $defaultLanguage = $languages[0];
        $digitalProductFileTypes = ['audio', 'video', 'document', 'software'];
        $digitalProductAuthors = $this->authorRepo->getListWhere(dataLimit: 'all');
        $publishingHouseList = $this->publishingHouseRepo->getListWhere(dataLimit: 'all');

        // --- MODIFICATION: Get available tiers + the product's current tier ---
        $availableTiers = $this->vendorTierService->getAvailableTiers();

        // Add the product's current tier to the list if it's not already there (e.g., if it's now "full" or expired but assigned)
        $currentVendorTier = $product->vendorTier;
        if ($currentVendorTier && !$availableTiers->contains('id', $currentVendorTier->id)) {
            // Re-fetch it with tier details just in case
            $currentVendorTier->load('tier');
            $availableTiers->push($currentVendorTier);
        }

        // This is the tier currently selected
        $selectedTier = $currentVendorTier;
        $tierDetails = $selectedTier?->tier;


        return view(Product::UPDATE[VIEW], compact('product', 'categories', 'selectedCategoryRows', 'brands', 'brandSetting', 'digitalProductSetting', 'colors', 'attributes', 'languages', 'defaultLanguage', 'digitalProductFileTypes', 'digitalProductAuthors', 'publishingHouseList', 'productAuthorIds', 'productPublishingHouseIds',
            'availableTiers', // <-- Pass all available tiers for the dropdown
            'selectedTier',   // <-- Pass the currently assigned tier
            'tierDetails'     // <-- Pass the details of the assigned tier
        ));
    }

    public function update(ProductUpdateRequest $request, ProductService $service, string|int $id): JsonResponse|RedirectResponse
    {
        // --- MODIFICATION: Validate against the *selected* tier's rules ---
        $productData = $request->all();
        $productData['id'] = $id; // Pass the product ID for the update check

        $tierValidation = $this->vendorTierService->validateTierPlanForOperation('update_product', $productData);
        if (!$tierValidation['is_valid']) {
            if ($request->ajax()) {
                return response()->json([
                    'errors' => $tierValidation['errors']
                ], 422); // Use 422 for validation errors
            }

            foreach ($tierValidation['errors'] as $error) {
                ToastMagic::error($error['message']);
            }
            return back()->withInput();
        }

        // The client submits this endpoint once via AJAX — see ProductController::add()
        // for the same fix. This used to short-circuit here with a "Validation Passed"
        // no-op response and rely on the client re-submitting the form natively to
        // actually persist the change; the JS was updated to do a single submit and
        // navigate using the JSON response, which left this branch never saving the
        // product at all (edits silently no-op'd). Saving directly on this same
        // request and returning a redirect_url fixes that.
        $isAjax = $request->ajax();

        $product = $this->productRepo->getFirstWhereWithoutGlobalScope(params: ['id' => $id], relations: ['translations', 'seoInfo']);
        $dataArray = $service->getUpdateProductData(request: $request, product: $product, updateBy: 'seller');

        // --- MODIFICATION: Add the selected vendor_tier_id to the data array ---
        $dataArray['vendor_tier_id'] = $request->vendor_tier_id;

        $this->updateProductAuthorAndPublishingHouse(request: $request, product: $product);

        $this->productRepo->update(id: $id, data: $dataArray);
        $this->productRepo->addRelatedTags(request: $request, product: $product);
        $this->productRepo->addRelatedCategories(request: $request, product: $product);
        $this->translationRepo->update(request: $request, model: 'App\Models\Product', id: $id);

        self::getDigitalProductUpdateProcess($request, $product);

        $this->productSeoRepo->updateOrInsert(
            params: ['product_id' => $product['id']],
            data: $service->getProductSEOData(request: $request, product: $product, action: 'update')
        );

        $updatedProduct = $this->productRepo->getFirstWhere(params: ['id' => $product['id']]);
        $this->updateRestockRequestListAndNotify(product: $product, updatedProduct: $updatedProduct);
        $this->updateStockClearanceProduct(product: $updatedProduct);

        ToastMagic::success(translate('product_updated_successfully'));
        if ($isAjax) {
            return response()->json(['redirect_url' => route('vendor.products.list', ['type' => 'all'])], 200);
        }
        return redirect()->route('vendor.products.list', ['type' => 'all']);
    }
    public function updateStockClearanceProduct($product): void
    {
        $config = $this->stockClearanceSetupRepo->getFirstWhere(params: [
            'setup_by' => $product['added_by'] == 'admin' ? $product['added_by'] : 'vendor',
            'shop_id' => $product['added_by'] == 'admin' ? 0 : $product?->seller?->shop?->id,
        ]);
        $stockClearanceProduct = $this->stockClearanceProductRepo->getFirstWhere(params: ['product_id' => $product['id']]);

        if ($config && $config['discount_type'] == 'product_wise' && $stockClearanceProduct && $stockClearanceProduct['discount_type'] == 'flat') {
            $minimumPrice = $product['unit_price'];
            foreach ((json_decode($product['variation'], true) ?? []) as $variation) {
                if ($variation['price'] < $minimumPrice) {
                    $minimumPrice = $variation['price'];
                }
            }

            if ($minimumPrice < $stockClearanceProduct['discount_amount']) {
                $this->stockClearanceProductRepo->updateByParams(params: ['product_id' => $product['id']], data: ['is_active' => 0]);
            }
        }
    }

    // --- THIS IS THE NEW, CRITICAL METHOD FOR THE E-CONTRACT FLOW ---
    public function getProductContractView(Request $request, $product_id)
    {
        $seller = auth('seller')->user();

        $product = ProductModel::with(['vendorTier.tier'])
            ->where('id', $product_id)
            ->where('user_id', $seller->id)
            ->first();

        if (!$product) {
            ToastMagic::error(translate('Invalid_product.'));
            return redirect()->route('vendor.products.list', ['type' => 'all']);
        }

        if (!$product->vendorTier || !$product->vendorTier->tier) {
            ToastMagic::error(translate('Product_is_not_linked_to_a_valid_tier_plan.'));
            return redirect()->route('vendor.products.update', ['id' => $product->id]);
        }

        $existingContract = SellerContract::where('product_id', $product->id)
                            ->where('vendor_tier_id', $product->vendorTier->id)
                            ->first();

        if($existingContract) {
            ToastMagic::info(translate('Contract_already_signed_for_this_product.'));
            return redirect()->route('vendor.products.list', ['type' => 'all']);
        }

        $vendorTier = $product->vendorTier;
        $tier_name = $vendorTier->tier->name;
        $tierID = $vendorTier->product_tier_id; // The ID of the base tier (ProductTier)
        $productID = $product->id;
        $vendorTierID = $vendorTier->id; // The ID of the subscription (VendorTier)

        return view('vendor-views.contracts.contract', compact('tier_name', 'tierID', 'productID', 'vendorTierID'));
    }

    public function updateProductAuthorAndPublishingHouse(object|array $request, object|array $product): void
    {
        if ($request['product_type'] == 'digital') {
            if ($request->has('authors')) {
                $authorIds = [];
                foreach ($request['authors'] as $author) {
                    $authorId = $this->authorRepo->updateOrCreate(params: ['name' => $author], value: ['name' => $author]);
                    $authorIds[] = $authorId?->id;
                }

                foreach ($authorIds as $author) {
                    $productAuthorData = ['author_id' => $author, 'product_id' => $product->id];
                    $this->digitalProductAuthorRepo->updateOrCreate(params: $productAuthorData, value: $productAuthorData);
                }

                $this->digitalProductAuthorRepo->deleteWhereNotIn(filters: ['product_id' => $product->id], whereNotIn: ['author_id' => $authorIds]);
            } else {
                $this->digitalProductAuthorRepo->delete(params: ['product_id' => $product->id]);
            }

            if ($request->has('publishing_house')) {
                $publishingHouseIds = [];
                foreach ($request['publishing_house'] as $publishingHouse) {
                    $publishingHouseId = $this->publishingHouseRepo->updateOrCreate(params: ['name' => $publishingHouse], value: ['name' => $publishingHouse]);
                    $publishingHouseIds[] = $publishingHouseId?->id;
                }

                foreach ($publishingHouseIds as $publishingHouse) {
                    $publishingHouseData = ['publishing_house_id' => $publishingHouse, 'product_id' => $product->id];
                    $this->digitalProductPublishingHouseRepo->updateOrCreate(params: $publishingHouseData, value: $publishingHouseData);
                }
                $this->digitalProductPublishingHouseRepo->deleteWhereNotIn(filters: ['product_id' => $product->id], whereNotIn: ['publishing_house_id' => $publishingHouseIds]);
            } else {
                $this->digitalProductPublishingHouseRepo->delete(params: ['product_id' => $product->id]);
            }
        } else {
            $this->digitalProductAuthorRepo->delete(params: ['product_id' => $product->id]);
            $this->digitalProductPublishingHouseRepo->delete(params: ['product_id' => $product->id]);
        }
    }


    public function getDigitalProductUpdateProcess($request, $product): void
    {
        if ($request->has('digital_product_variant_key') && !$request->hasFile('digital_file_ready')) {
            $getAllVariation = $this->digitalProductVariationRepo->getListWhere(filters: ['product_id' => $product['id']]);
            $getAllVariationKey = $getAllVariation->pluck('variant_key')->toArray();
            $getRequestVariationKey = $request['digital_product_variant_key'];
            $differenceFromDB = array_diff($getAllVariationKey, $getRequestVariationKey);
            $differenceFromRequest = array_diff($getRequestVariationKey, $getAllVariationKey);
            $newCombinations = array_merge($differenceFromDB, $differenceFromRequest);

            foreach ($newCombinations as $newCombination) {
                if (in_array($newCombination, $request['digital_product_variant_key'])) {
                    $uniqueKey = strtolower(str_replace('-', '_', $newCombination));

                    $fileItem = null;
                    if ($request['digital_product_type'] == 'ready_product') {
                        $fileItem = $request->file('digital_files.' . $uniqueKey);
                    }
                    $uploadedFile = '';
                    if ($fileItem) {
                        $uploadedFile = $this->fileUpload(dir: 'product/digital-product/', format: $fileItem->getClientOriginalExtension(), file: $fileItem);
                    }
                    $this->digitalProductVariationRepo->add(data: [
                        'product_id' => $product['id'],
                        'variant_key' => $request->input('digital_product_variant_key.' . $uniqueKey),
                        'sku' => $request->input('digital_product_sku.' . $uniqueKey),
                        'price' => currencyConverter(amount: $request->input('digital_product_price.' . $uniqueKey)),
                        'file' => $uploadedFile,
                    ]);
                }
            }

            foreach ($differenceFromDB as $variation) {
                $variation = $this->digitalProductVariationRepo->getFirstWhere(params: ['product_id' => $product['id'], 'variant_key' => $variation]);
                if ($variation) {
                    // $this->deleteFile(filePath: '/product/digital-product/' . $variation['file']);
                    $this->digitalProductVariationRepo->delete(params: ['id' => $variation['id']]);
                }
            }

            foreach ($getAllVariation as $variation) {
                if (in_array($variation['variant_key'], $request['digital_product_variant_key'])) {
                    $uniqueKey = strtolower(str_replace('-', '_', $variation['variant_key']));

                    $fileItem = null;
                    if ($request['digital_product_type'] == 'ready_product') {
                        $fileItem = $request->file('digital_files.' . $uniqueKey);
                    }
                    $uploadedFile = $variation['file'] ?? '';
                    $variation = $this->digitalProductVariationRepo->getFirstWhere(params: ['product_id' => $product['id'], 'variant_key' => $variation['variant_key']]);
                    if ($fileItem) {
                        $uploadedFile = $this->fileUpload(dir: 'product/digital-product/', format: $fileItem->getClientOriginalExtension(), file: $fileItem);
                    }
                    $this->digitalProductVariationRepo->updateByParams(params: ['product_id' => $product['id'], 'variant_key' => $variation['variant_key']], data: [
                        'variant_key' => $request->input('digital_product_variant_key.' . $uniqueKey),
                        'sku' => $request->input('digital_product_sku.' . $uniqueKey),
                        'price' => currencyConverter(amount: $request->input('digital_product_price.' . $uniqueKey)),
                        'file' => $uploadedFile,
                    ]);
                }

                if ($request['product_type'] == 'physical' || $request['digital_product_type'] == 'ready_after_sell') {
                    $variation = $this->digitalProductVariationRepo->getFirstWhere(params: ['product_id' => $product['id'], 'variant_key' => $variation['variant_key']]);
                    if ($variation && $variation['file']) {
                        // $this->deleteFile(filePath: '/product/digital-product/' . $variation['file']);
                        $this->digitalProductVariationRepo->updateByParams(params: ['id' => $variation['id']], data: ['file' => '']);
                    }
                    if ($request['product_type'] == 'physical') {
                        $variation->delete();
                    }
                }
            }
        } else {
            $this->digitalProductVariationRepo->delete(params: ['product_id' => $product['id']]);
        }
    }

    public function getView(string|int $id): View|RedirectResponse
    {
        $vendorId = auth('seller')->id();
        $productActive = $this->productRepo->getFirstWhereActive(params: ['id' => $id, 'user_id' => $vendorId]);
        $relations = ['category', 'brand', 'reviews', 'rating', 'orderDetails', 'orderDelivered', 'translations', 'seoInfo', 'clearanceSale' => function ($query) {
            return $query->active();
        }];
        $product = $this->productRepo->getFirstWhereWithoutGlobalScope(params: ['id' => $id, 'user_id' => $vendorId], relations: $relations);
        if (!$product) {
            return redirect()->route('vendor.products.list', ['type' => 'all']);
        }
        $isActive = $this->productRepo->getWebFirstWhereActive(params: ['id' => $id]);
        $product['priceSum'] = $product?->orderDelivered->sum('price');
        $product['qtySum'] = $product?->orderDelivered->sum('qty');
        $product['discountSum'] = $product?->orderDelivered->sum('discount');

        $productColors = [];
        $colorCodes = json_decode($product['colors'], true) ?? [];
        $colorMap = Color::whereIn('code', $colorCodes)->get()->keyBy('code');
        foreach ($colorCodes as $code) {
            $c = $colorMap->get($code);
            if ($c) {
                $productColors[$c['name']] = $colorCodes;
            }
        }

        $reviews = $this->reviewRepo->getListWhere(orderBy: ['created_at' => 'desc'], filters: ['product_id' => ['product_id' => $id], 'whereNull' => ['column' => 'delivery_man_id']], dataLimit: getWebConfig(name: 'pagination_limit'));
        return view(Product::VIEW[VIEW], compact('product', 'reviews', 'productActive', 'productColors', 'isActive'));
    }

    public function exportList(Request $request, string $type): BinaryFileResponse
    {
        $vendorId = auth('seller')->id();
        $vendor = $this->vendorRepo->getFirstWhere(params: ['id' => $vendorId]);
        $filters = [
            'added_by' => 'seller',
            'seller_id' => $vendorId,
            'brand_id' => $request['brand_id'],
            'category_id' => $request['category_id'],
            'sub_category_id' => $request['sub_category_id'],
            'sub_sub_category_id' => $request['sub_sub_category_id'],
            'request_status' => $type == 'new-request' ? 0 : ($type == 'approved' ? 1 : ($type == 'denied' ? 2 : 'all')),
        ];
        $products = $this->productRepo->getListWhere(orderBy: ['id' => 'desc'], searchValue: $request['searchValue'], filters: $filters, relations: ['translations'], dataLimit: 'all');

        $category = (!empty($request['category_id']) && $request->has('category_id')) ? $this->categoryRepo->getFirstWhere(params: ['id' => $request['category_id']]) : 'all';
        $subCategory = (!empty($request->sub_category_id) && $request->has('sub_category_id')) ? $this->categoryRepo->getFirstWhere(params: ['id' => $request['sub_category_id']]) : 'all';
        $subSubCategory = (!empty($request->sub_sub_category_id) && $request->has('sub_sub_category_id')) ? $this->categoryRepo->getFirstWhere(params: ['id' => $request['sub_sub_category_id']]) : 'all';
        $brand = (!empty($request->brand_id) && $request->has('brand_id')) ? $this->brandRepo->getFirstWhere(params: ['id' => $request->brand_id]) : 'all';
        $seller = (!empty($request->seller_id) && $request->has('seller_id')) ? $this->vendorRepo->getFirstWhere(params: ['id' => $request->seller_id]) : '';
        $data = [
            'data-from' => 'vendor',
            'vendor' => $vendor,
            'products' => $products,
            'category' => $category,
            'sub_category' => $subCategory,
            'sub_sub_category' => $subSubCategory,
            'brand' => $brand,
            'searchValue' => $request['searchValue'],
            'type' => $request->type ?? '',
            'seller' => $seller,
            'status' => $request->status ?? '',
        ];
        return Excel::download(new ProductListExport($data), ucwords($request['type']) . '-' . 'product-list.xlsx');
    }

    public function exportRestockList(Request $request): BinaryFileResponse
    {
        $vendorId = auth('seller')->id();
        $filters = [
            'added_by' => 'seller',
            'seller_id' => $vendorId,
            'brand_id' => $request['brand_id'],
            'category_id' => $request['category_id'],
            'sub_category_id' => $request['sub_category_id'],
        ];

        $startDate = '';
        $endDate = '';
        if (isset($request['restock_date']) && !empty($request['restock_date'])) {
            $dates = explode(' - ', $request['restock_date']);
            $startDate = Carbon::createFromFormat('m/d/Y', $dates[0])->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $dates[1])->endOfDay();
        }
        $restockProducts = $this->restockProductRepo->getListWhereBetween(
            orderBy: ['updated_at' => 'desc'],
            searchValue: $request['searchValue'],
            filters: $filters,
            relations: ['product'],
            whereBetween: 'created_at',
            whereBetweenFilters: $startDate && $endDate ? [$startDate, $endDate] : [],
            dataLimit: 'all',
        );
        $brand = (!empty($request->brand_id) && $request->has('brand_id')) ? $this->brandRepo->getFirstWhere(params: ['id' => $request->brand_id]) : 'all';
        $category = (!empty($request['category_id']) && $request->has('category_id')) ? $this->categoryRepo->getFirstWhere(params: ['id' => $request['category_id']]) : 'all';
        $subCategory = (!empty($request->sub_category_id) && $request->has('sub_category_id')) ? $this->categoryRepo->getFirstWhere(params: ['id' => $request['sub_category_id']]) : 'all';

        $data = [
            'products' => $restockProducts,
            'category' => $category,
            'subCategory' => $subCategory,
            'brand' => $brand,
            'searchValue' => $request['searchValue'],
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
        return Excel::download(new RestockProductListExport($data), 'restock-product-list.xlsx');
    }

    public function getSkuCombinationView(Request $request, ProductService $service): JsonResponse
    {
        // Fired on every debounced keystroke while typing the unit price (see
        // getUpdateSKUFunctionality() in the vendor product JS). On the add-product page
        // there is no product_id yet, and generatePhysicalVariationCombination() only
        // reads $product->variation (falling back cleanly when $product is null) — so
        // skip the query entirely when there's nothing to look up, and only eager-load
        // the one column this view actually uses instead of digitalVariation/seoInfo,
        // which this endpoint never reads.
        $product = $request->filled('product_id')
            ? $this->productRepo->getFirstWhere(params: ['id' => $request['product_id']], relations: [])
            : null;
        $combinationView = $service->getSkuCombinationView(request: $request, product: $product);
        return response()->json(['view' => $combinationView]);
    }

    public function getDigitalVariationCombinationView(Request $request, ProductService $service): JsonResponse
    {
        $product = $request->filled('product_id')
            ? $this->productRepo->getFirstWhere(params: ['id' => $request['product_id']], relations: ['digitalVariation'])
            : null;
        $combinationView = $service->getDigitalVariationCombinationView(request: $request, product: $product);
        return response()->json(['view' => $combinationView]);
    }

    public function deleteDigitalVariationFile(Request $request, ProductService $service): JsonResponse
    {
        $variation = $this->digitalProductVariationRepo->getFirstWhere(params: ['product_id' => $request['product_id'], 'variant_key' => $request['variant_key']]);
        if ($variation) {
            $this->deleteFile(filePath: '/product/digital-product/' . $variation['file']);
            $this->digitalProductVariationRepo->updateByParams(params: ['id' => $variation['id']], data: ['file' => null]);
            return response()->json([
                'status' => 1,
                'message' => translate('delete_successful')
            ]);
        }
        return response()->json([
            'status' => 0,
            'message' => translate('delete_unsuccessful')
        ]);
    }

    public function getCategories(Request $request, ProductService $service): JsonResponse
    {
        $parentId = $request['parent_id'];
        $filter = ['parent_id' => $parentId];
        $categories = $this->categoryRepo->getListWhere(filters: $filter, dataLimit: 'all');
        $dropdown = $service->getCategoryDropdown(request: $request, categories: $categories);

        $childCategories = '';
        if (count($categories) == 1) {
            $subCategories = $this->categoryRepo->getListWhere(filters: ['parent_id' => $categories[0]['id']], dataLimit: 'all');
            $childCategories = $service->getCategoryDropdown(request: $request, categories: $subCategories);
        }

        return response()->json([
            'select_tag' => $dropdown,
            'sub_categories' => count($categories) == 1 ? $childCategories : '',
        ]);
    }

    /**
     * Flat id/parent_id/name list of every category, for the "add another
     * category" row repeater to build its 2nd/3rd/etc. row dropdowns from
     * in-browser instead of repeating the same AJAX round-trip per row that
     * the first (main) category row still uses. Fetched once per page load
     * in the background — the category tree is small (a few hundred rows
     * total) so this is a single cheap query either way, just consolidated
     * into one request instead of N.
     */
    public function getAllCategoriesFlat(): JsonResponse
    {
        // defaultName falls back to $this->name when no translation row exists,
        // so 'name' must stay in the select — dropping it left every row's
        // name resolving to null whenever a category had no translation.
        $categories = \App\Models\Category::select(['id', 'parent_id', 'position', 'name'])->get();

        return response()->json([
            'categories' => $categories->map(fn($category) => [
                'id' => $category->id,
                'parent_id' => $category->parent_id,
                'position' => $category->position,
                'name' => $category->defaultName,
            ])->values(),
        ]);
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $status = $request['status'];
        $productId = $request['id'];
        $product = $this->productRepo->getFirstWhere(params: ['id' => $productId, 'user_id' => auth('seller')->id()]);
        $success = 0;

        if ($status == 1 && $product['request_status'] == 1) {
            $this->productRepo->update(id: $productId, data: ['status' => $status]);
            $success = 1;
        } elseif ($status != 1) {
            $this->productRepo->update(id: $productId, data: ['status' => $status ?? 0]);
            $success = 1;
        }

        return response()->json([
            'success' => $success,
            'message' => $success ? translate("status_updated_successfully") : translate("Product_must_be_approved_by_Admin_first"),
        ], 200);
    }

    public function featuredStatus(Request $request): JsonResponse
    {
        $productId = $request['id'];
        // Scope to the authenticated vendor's own product — without this a vendor
        // could toggle another vendor's product by guessing its id.
        $product = $this->productRepo->getFirstWhere(params: ['id' => $productId, 'user_id' => auth('seller')->id()]);
        if (!$product) {
            return response()->json(['success' => 0, 'message' => translate('product_not_found')], 404);
        }

        $this->productRepo->update(id: $productId, data: [
            'featured' => is_null($product['featured']) || $product['featured'] == 0 ? 1 : 0,
        ]);

        return response()->json([
            'success' => 1,
            'message' => translate('status_updated_successfully'),
        ], 200);
    }

    public function getBarcodeView(Request $request, string|int $id): View|RedirectResponse
    {
        if ($request['limit'] > 270) {
            ToastMagic::warning(translate('you_can_not_generate_more_than_270_barcode'));
            return back();
        }
        $product = $this->productRepo->getFirstWhere(params: ['id' => $id, 'user_id' => auth('seller')->id()]);
        $rangeData = range(1, $request->limit ?? 4);
        $barcodes = array_chunk($rangeData, 24);
        return view(Product::BARCODE_VIEW[VIEW], compact('product', 'barcodes'));
    }

    public function delete(string|int $id, ProductService $service): RedirectResponse
    {
        $product = $this->productRepo->getFirstWhere(params: ['id' => $id, 'user_id' => auth('seller')->id()]);

        if ($product) {
            $this->translationRepo->delete(model: 'App\Models\Product', id: $id);
            $this->cartRepo->delete(params: ['product_id' => $id]);
            $this->wishlistRepo->delete(params: ['product_id' => $id]);
            $this->flashDealProductRepo->delete(params: ['product_id' => $id]);
            $this->dealOfTheDayRepo->delete(params: ['product_id' => $id]);
            $service->deleteImages(product: $product);
            $this->productRepo->delete(params: ['id' => $id]);
            ToastMagic::success(translate('product_removed_successfully'));
        } else {
            ToastMagic::error(translate('invalid_product'));
        }

        return redirect()->route('vendor.products.list', ['type' => 'all']);
    }

    public function getStockLimitListView(Request $request): View
    {
        $vendorId = auth('seller')->id();
        $stockLimit = getWebConfig(name: 'stock_limit');
        $sortOrderQty = $request['sortOrderQty'];
        $searchValue = $request['searchValue'];
        $withCount = ['orderDetails'];
        $status = $request['status'];
        $filters = [
            'added_by' => 'seller',
            'request_status' => 1,
            'product_type' => 'physical',
            'seller_id' => $vendorId,
        ];

        $orderBy = [];
        if ($sortOrderQty == 'quantity_asc') {
            $orderBy = ['current_stock' => 'asc'];
        } else if ($sortOrderQty == 'quantity_desc') {
            $orderBy = ['current_stock' => 'desc'];
        } elseif ($sortOrderQty == 'order_asc') {
            $orderBy = ['order_details_count' => 'asc'];
        } elseif ($sortOrderQty == 'order_desc') {
            $orderBy = ['order_details_count' => 'desc'];
        } elseif ($sortOrderQty == 'default') {
            $orderBy = ['id' => 'asc'];
        }

        $products = $this->productRepo->getStockLimitListWhere(orderBy: $orderBy, searchValue: $searchValue, filters: $filters, withCount: $withCount, relations: ['translations'], dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT));
        return view(Product::STOCK_LIMIT[VIEW], compact('products', 'searchValue', 'status', 'sortOrderQty', 'stockLimit'));
    }

    public function updateQuantity(Request $request): RedirectResponse
    {
        $variations = [];
        $stockCount = $request['current_stock'];
        if ($request->has('type')) {
            foreach ($request['type'] as $key => $str) {
                $item = [];
                $item['type'] = $str;
                $item['price'] = currencyConverter(amount: abs($request['price_' . str_replace('.', '_', $str)]));
                $item['sku'] = $request['sku_' . str_replace('.', '_', $str)];
                $item['qty'] = abs($request['qty_' . str_replace('.', '_', $str)]);
                $variations[] = $item;
            }
        }
        $dataArray = [
            'current_stock' => $stockCount,
            'variation' => json_encode($variations),
        ];

        if ($stockCount >= 0) {
            $product = $this->productRepo->getFirstWhere(params: ['id' => $request['product_id']]);
            $this->productRepo->updateByParams(params: ['id' => $request['product_id']], data: $dataArray);
            $updatedProduct = $this->productRepo->getFirstWhere(params: ['id' => $request['product_id']]);
            $this->updateRestockRequestListAndNotify(product: $product, updatedProduct: $updatedProduct);

            ToastMagic::success(translate('product_quantity_updated_successfully'));
            return back();
        }
        ToastMagic::warning(translate('product_quantity_can_not_be_less_than_0_'));
        return back();
    }

    public function deleteImage(Request $request, ProductService $service): RedirectResponse
    {
        $product = $this->productRepo->getFirstWhere(params: [
            'id' => $request['id'],
            'user_id' => auth('seller')->id(),
            'added_by' => 'seller',
        ]);

        if (!$product) {
            ToastMagic::error(translate('invalid_product'));
            return back();
        }

        $images = json_decode($product['images']) ?: [];
        if (empty($product['thumbnail']) && count($images) < 2) {
            ToastMagic::warning(translate('you_can_not_delete_all_images'));
            return back();
        }

        if (!$request->filled('name') || basename($request['name']) !== $request['name']) {
            ToastMagic::error(translate('invalid_image'));
            return back();
        }

        $attachedImageNames = collect($images)->map(function ($image) {
            if (is_object($image)) {
                return $image->image_name ?? null;
            }

            if (is_array($image)) {
                return $image['image_name'] ?? null;
            }

            return $image;
        })->filter()->values()->toArray();

        if (!in_array($request['name'], $attachedImageNames, true)) {
            ToastMagic::error(translate('invalid_image'));
            return back();
        }

        $imageProcessing = $service->deleteImage(request: $request, product: $product);
        $updateData = [
            'images' => json_encode($imageProcessing['images']),
            'color_image' => json_encode($imageProcessing['color_images']),
        ];
        $this->productRepo->update(id: $request['id'], data: $updateData);
        $this->deleteFile(filePath: '/product/' . $request['name']);

        ToastMagic::success(translate('product_image_removed_successfully'));
        return back();
    }

    public function getVariations(Request $request): JsonResponse
    {
        $product = $this->productRepo->getFirstWhere(params: ['id' => $request['id']]);
        $restockId = $request['restock_id'];
        $restockVariants = $this->restockProductRepo->getListWhereBetween(filters: ['product_id' => $request['id']])?->pluck('variant')->toArray() ?? [];

        return response()->json([
            'view' => view(Product::GET_VARIATIONS[VIEW], compact('product', 'restockId', 'restockVariants'))->render()
        ]);
    }

    public function getBulkImportView(): View
    {
        return view(Product::BULK_IMPORT[VIEW]);
    }

    public function importBulkProduct(Request $request, ProductService $service): RedirectResponse
    {
        $dataArray = $service->getImportBulkProductData(request: $request, addedBy: 'seller');
        if (!$dataArray['status']) {
            ToastMagic::error($dataArray['message']);
            return back();
        }

        $this->productRepo->addArray(data: $dataArray['products']);
        ToastMagic::success($dataArray['message']);
        return back();
    }

    public function getSearchedProductsView(Request $request): JsonResponse
    {
        $searchValue = $request['searchValue'] ?? null;
        $products = $this->productRepo->getListWhere(
            searchValue: $searchValue,
            filters: [
                'added_by' => 'seller',
                'seller_id' => auth('seller')->id(),
                'status' => 1,
                'category_id' => $request['category_id'],
                'code' => $request['name'] ?? null,
            ],
            dataLimit: getWebConfig(name: 'pagination_limit')
        );
        return response()->json([
            'count' => $products->count(),
            'result' => view(Product::SEARCH[VIEW], compact('products'))->render(),
        ]);
    }


    public function getProductGalleryView(Request $request): View
    {
        $vendorId = auth('seller')->id();
        $searchValue = $request['searchValue'];
        $filters = [
            'added_by' => 'seller',
            'searchValue' => $searchValue,
            'request_status' => 1,
            'seller_id' => $vendorId,
            'brand_id' => $request['brand_id'],
            'category_id' => $request['category_id'],
        ];
        $products = $this->productRepo->getListWhere(orderBy: ['id' => 'desc'], searchValue: $request['searchValue'], filters: $filters, relations: ['translations', 'category.translations', 'tags'], dataLimit: getWebConfig(WebConfigKey::PAGINATION_LIMIT));
        $allColorCodes = $products->flatMap(fn($p) => json_decode($p->colors ?? '[]', true))->unique()->filter()->values();
        $colorMap = Color::whereIn('code', $allColorCodes->toArray())->get()->keyBy('code');
        $products->map(function ($product) use ($colorMap) {
            if ($product->product_type == 'physical' && count(json_decode($product->choice_options)) > 0 || count(json_decode($product->colors)) > 0) {
                $colorName = [];
                foreach (json_decode($product->colors) as $code) {
                    $colorName[] = optional($colorMap->get($code))->name;
                }
                $product['colorsName'] = array_filter($colorName);
            }
        });

        $brands = $this->brandRepo->getListWhere(filters: ['status' => 1], dataLimit: 'all');

        $categories = $this->categoryRepo->getListWhere(filters: ['position' => 0], dataLimit: 'all');

        return view(Product::PRODUCT_GALLERY[VIEW], compact('products', 'brands', 'categories', 'searchValue'));

    }

    public function getStockLimitStatus(Request $request): JsonResponse
    {
        $vendorId = auth('seller')->id();
        $filters = [
            'added_by' => 'seller',
            'product_type' => 'physical',
            'request_status' => 1,
            'seller_id' => $vendorId,
        ];
        $products = $this->productRepo->getStockLimitListWhere(filters: $filters, dataLimit: 'all')
            ->where('status', 1);
        if ($products->count() == 1) {
            $product = $products->first();
            $thumbnail = getStorageImages(path: $product->thumbnail_full_url, type: 'backend-product');
            return response()->json(['status' => 'one_product', 'product_count' => 1, 'product' => $product, 'thumbnail' => $thumbnail]);
        } else {
            return response()->json(['status' => 'multiple_product', 'product_count' => $products->count()]);
        }
    }

    public function deletePreviewFile(Request $request): JsonResponse
    {
        $product = $this->productRepo->getFirstWhereWithoutGlobalScope(params: ['id' => $request['product_id']]);
        $this->productService->deletePreviewFile(product: $product);
        $this->productRepo->update(id: $request['product_id'], data: ['preview_file' => null]);
        return response()->json([
            'status' => 1,
            'message' => translate('Preview_file_deleted')
        ]);
    }
}







