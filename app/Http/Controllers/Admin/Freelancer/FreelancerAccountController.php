<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Events\VendorRegistrationEvent;
use App\Http\Controllers\BaseController;
use App\Models\Chatting;
use App\Models\FreelancerCategory;
use App\Models\FreelancerContract;
use App\Models\FreelancerPortfolioItem;
use App\Models\FreelancerService;
use App\Models\FreelancerSpecialization;
use App\Models\NotificationSeen;
use App\Models\Seller;
use App\Models\SellerWallet;
use App\Models\Shop;
use App\Models\Storage;
use App\Models\VendorVerification;
use App\Services\FreelancerPortfolioService;
use App\Traits\FileManagerTrait;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FreelancerAccountController extends BaseController
{
    use FileManagerTrait;

    public function __construct(
        private readonly VendorRepositoryInterface $vendorRepo,
        private readonly FreelancerPortfolioService $portfolioService,
    ) {
    }

    public function index(Request|null $request, string $type = null): View
    {
        return $this->getListView($request);
    }

    public function getListView(Request $request): View
    {
        $searchValue = $request['searchValue'];

        $freelancers = Seller::where('seller_type', 'freelancer')
            ->with('vendorVerification:id,seller_id,status')
            ->withCount(['freelancerServices as services_count', 'freelancerContracts as contracts_count'])
            ->when($searchValue, function ($query) use ($searchValue) {
                $searchTerms = explode(' ', $searchValue);
                $query->where(function ($query) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $query->orWhere('f_name', 'like', "%$term%")
                            ->orWhere('l_name', 'like', "%$term%")
                            ->orWhere('phone', 'like', "%$term%")
                            ->orWhere('email', 'like', "%$term%");
                    }
                });
            })
            ->orderByDesc('id')
            ->paginate(getWebConfig(name: 'pagination_limit'))
            ->appends($request->query());

        $pendingVerificationCount = VendorVerification::where('seller_type', 'freelancer')
            ->whereIn('status', ['pending', 'resubmitted'])
            ->count();

        return view('admin-views.freelancer.accounts.index', compact('freelancers', 'pendingVerificationCount'));
    }

    public function create(): View
    {
        return view('admin-views.freelancer.accounts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'f_name' => 'required|string|max:255',
            'l_name' => 'nullable|string|max:255',
            'shop_name' => 'nullable|string|max:100',
            // Scoped to seller_type='freelancer' — Vendor accounts (seller_type NULL
            // or company/individual) may legitimately share this email.
            'email' => [
                'required', 'email', 'max:80',
                \Illuminate\Validation\Rule::unique('sellers', 'email')->where(fn ($query) => $query->where('seller_type', 'freelancer')),
            ],
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $seller = DB::transaction(function () use ($request) {
            $seller = Seller::create([
                'f_name' => $request['f_name'],
                'l_name' => $request['l_name'] ?? null,
                'phone' => $request['phone'],
                'email' => $request['email'],
                'image' => 'def.png',
                'password' => bcrypt($request['password']),
                // Admin-created freelancers skip the usual document-verification
                // review — they're active and approved from the moment of creation.
                'status' => 'approved',
                'account_status' => 'active',
                'seller_type' => 'freelancer',
                'first_login_after_approval' => false,
            ]);

            $shopName = $request['shop_name'] ?: Str::before($request['email'], '@');
            Shop::create([
                'seller_id' => $seller->id,
                'name' => $shopName,
                'slug' => Str::slug($shopName, '-') . '-' . Str::random(6),
                'address' => '',
                'contact' => $request['phone'],
                'email' => $request['email'],
                'image' => 'def.png',
                'image_storage_type' => null,
                'banner' => 'def.png',
                'banner_storage_type' => null,
                'bottom_banner' => 'def.png',
                'bottom_banner_storage_type' => null,
            ]);

            SellerWallet::create([
                'seller_id' => $seller->id,
                'total_earning' => 0,
                'withdrawn' => 0,
                'commission_given' => 0,
                'pending_withdraw' => 0,
                'delivery_charge_earned' => 0,
                'collected_cash' => 0,
                'total_tax_collected' => 0,
            ]);

            VendorVerification::create([
                'seller_id' => $seller->id,
                'seller_type' => 'individual',
                'personal_name' => trim($request['f_name'] . ' ' . ($request['l_name'] ?? '')),
                'status' => 'approved',
            ]);

            return $seller;
        });

        try {
            event(new VendorRegistrationEvent(email: $seller->email, data: [
                'name' => $seller->f_name,
                'vendorName' => $seller->f_name,
                'sellerType' => 'Freelancer',
                'seller_type' => 'freelancer',
                'message' => 'An admin has created a freelancer account for you on Finxcart. Your account is already approved — log in to complete your profile.',
                'status' => 'approved',
                'subject' => translate('Registration_Approved'),
                'title' => translate('Registration_Approved'),
                'userType' => 'vendor',
                'templateName' => 'registration-approved',
            ]));
        } catch (\Throwable $e) {
            \Log::warning('Freelancer account creation email failed: ' . $e->getMessage());
        }

        ToastMagic::success(translate('freelancer_account_created_successfully'));
        return redirect()->route('admin.freelancer.accounts.view', $seller->id);
    }

    public function show(int $id): View|RedirectResponse
    {
        $freelancer = $this->vendorRepo->getFirstWhere(
            params: ['id' => $id, 'seller_type' => 'freelancer'],
            relations: ['vendorVerification', 'shop']
        );

        if (!$freelancer) {
            ToastMagic::error(translate('freelancer_not_found'));
            return redirect()->route('admin.freelancer.accounts.index');
        }

        $services = FreelancerService::with(['category', 'specialization'])
            ->where('seller_id', $id)
            ->orderByDesc('id')
            ->get();

        $portfolioItems = FreelancerPortfolioItem::where('seller_id', $id)
            ->orderByDesc('id')
            ->get();

        $contracts = FreelancerContract::with(['customer', 'service'])
            ->where('seller_id', $id)
            ->orderByDesc('id')
            ->get();

        $chattingCustomers = Chatting::with('customer')
            ->where('seller_id', $id)
            ->whereNotNull('user_id')
            ->latest('id')
            ->get()
            ->unique('user_id');

        $servicesCount = $services->count();
        $portfolioCount = $portfolioItems->count();
        $submittedDocuments = $this->getSubmittedDocuments($freelancer);

        return view('admin-views.freelancer.accounts.view', compact(
            'freelancer', 'servicesCount', 'portfolioCount', 'services', 'portfolioItems', 'contracts', 'chattingCustomers', 'submittedDocuments'
        ));
    }

    public function edit(int $id): View|RedirectResponse
    {
        $freelancer = $this->vendorRepo->getFirstWhere(
            params: ['id' => $id, 'seller_type' => 'freelancer'],
            relations: ['shop']
        );

        if (!$freelancer) {
            ToastMagic::error(translate('freelancer_not_found'));
            return redirect()->route('admin.freelancer.accounts.index');
        }

        return view('admin-views.freelancer.accounts.edit', compact('freelancer'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $freelancer = $this->vendorRepo->getFirstWhere(params: ['id' => $id, 'seller_type' => 'freelancer']);
        if (!$freelancer) {
            ToastMagic::error(translate('freelancer_not_found'));
            return redirect()->route('admin.freelancer.accounts.index');
        }

        $request->validate([
            'f_name' => 'required|string|max:255',
            'l_name' => 'nullable|string|max:255',
            'shop_name' => 'nullable|string|max:100',
            'email' => ['required', 'email', 'max:80', Rule::unique('sellers', 'email')->ignore($freelancer->id)],
            'phone' => 'required|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $updateData = [
            'f_name' => $request['f_name'],
            'l_name' => $request['l_name'] ?? null,
            'phone' => $request['phone'],
            'email' => $request['email'],
        ];

        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($request['password']);
        }

        if ($request->hasFile('image')) {
            $updateData['image'] = $this->update(dir: 'seller/', oldImage: $freelancer->image, format: 'webp', image: $request->file('image'));
        }

        $freelancer->update($updateData);

        if ($request->filled('shop_name') && $freelancer->shop) {
            $freelancer->shop->update(['name' => $request['shop_name']]);
        }

        ToastMagic::success(translate('freelancer_account_updated_successfully'));
        return redirect()->route('admin.freelancer.accounts.view', $freelancer->id);
    }

    private function getSubmittedDocuments(Seller $freelancer): array
    {
        $verification = $freelancer->vendorVerification;
        $verificationStorage = getWebConfig(name: 'storage_connection_type') ?? 'public';

        $documents = [
            $this->makeDocument(
                label: translate('profile_photo'),
                filename: !empty($freelancer->image) && $freelancer->image !== 'def.png' ? $freelancer->image : null,
                url: $freelancer->image_full_url['path'] ?? null,
            ),
            $this->makeDocument(
                label: translate('personal_id_document'),
                filename: $verification?->personal_id_document,
                url: $this->storagePath('vendor-verifications', $verification?->personal_id_document, $verificationStorage),
            ),
            $this->makeDocument(
                label: translate('shop_logo'),
                filename: !empty($freelancer->shop?->image) && $freelancer->shop?->image !== 'def.png' ? $freelancer->shop?->image : null,
                url: $freelancer->shop?->image_full_url['path'] ?? null,
            ),
            $this->makeDocument(
                label: translate('shop_banner'),
                filename: !empty($freelancer->shop?->banner) && $freelancer->shop?->banner !== 'def.png' ? $freelancer->shop?->banner : null,
                url: $freelancer->shop?->banner_full_url['path'] ?? null,
            ),
        ];

        if (!empty($verification?->payout_kyc_document)) {
            $documents[] = $this->makeDocument(
                label: translate('payout_kyc_document'),
                filename: $verification->payout_kyc_document,
                url: $this->storagePath('vendor-verifications', $verification->payout_kyc_document, $verificationStorage),
            );
        }

        return $documents;
    }

    private function makeDocument(string $label, ?string $filename, ?string $url): array
    {
        return [
            'label' => $label,
            'filename' => $filename,
            'url' => $url,
            'is_pdf' => $filename ? str_ends_with(strtolower($filename), '.pdf') : false,
        ];
    }

    private function storagePath(string $directory, ?string $filename, string $storage): ?string
    {
        if (empty($filename)) {
            return null;
        }

        $link = storageLink($directory, $filename, $storage);

        return is_array($link) && ($link['status'] ?? null) == 200 ? $link['path'] : null;
    }

    public function updateAccountStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'id' => 'required|integer|regex:/^\d+$/|exists:sellers,id',
            'account_status' => 'required|in:active,inactive',
        ]);

        $freelancer = $this->vendorRepo->getFirstWhere(params: ['id' => $request['id'], 'seller_type' => 'freelancer']);
        if (!$freelancer) {
            ToastMagic::error(translate('freelancer_not_found'));
            return back();
        }

        $updateData = ['account_status' => $request['account_status']];
        if ($request['account_status'] === 'inactive') {
            $updateData['auth_token'] = Str::random(80);
        }

        $this->vendorRepo->update(id: $request['id'], data: $updateData);

        if ($request['account_status'] === 'inactive') {
            ToastMagic::error(translate('freelancer_account_has_been_suspended_successfully'));
        } else {
            ToastMagic::success(translate('freelancer_account_has_been_reactivated_successfully'));
        }

        try {
            event(new VendorRegistrationEvent(email: $freelancer->email, data: [
                'vendorName'   => $freelancer->f_name,
                'status'       => $request['account_status'],
                'subject'      => $request['account_status'] === 'inactive' ? translate('Account_Suspended') : translate('Account_Reactivated'),
                'title'        => $request['account_status'] === 'inactive' ? translate('Account_Suspended') : translate('Account_Reactivated'),
                'userType'     => 'vendor',
                'templateName' => $request['account_status'] === 'inactive' ? 'account-suspended' : 'account-activation',
            ]));
        } catch (\Throwable $e) {
            \Log::warning('Freelancer account status email failed: ' . $e->getMessage());
        }

        return back();
    }

    public function destroy(Request $request): JsonResponse
    {
        $freelancer = $this->vendorRepo->getFirstWhere(params: ['id' => $request['id'], 'seller_type' => 'freelancer']);

        if (!$freelancer) {
            return response()->json(['message' => translate('freelancer_not_found')], 404);
        }

        try {
            DB::transaction(function () use ($freelancer) {
                $sellerId = $freelancer->id;

                // Contracts must go before services: freelancer_contracts.freelancer_service_id
                // has no cascade/null-on-delete rule, so deleting a service that still has a
                // contract pointing at it throws a foreign key violation. Milestones,
                // deliverables, messages, attachments and reviews all cascade from the
                // contract itself, so deleting the contract rows is enough here.
                FreelancerContract::where('seller_id', $sellerId)->delete();

                foreach (FreelancerPortfolioItem::where('seller_id', $sellerId)->with('galleryItems')->get() as $item) {
                    $this->portfolioService->deleteImage($item);
                    $this->portfolioService->deleteGalleryImages($item);
                }
                FreelancerPortfolioItem::where('seller_id', $sellerId)->delete();
                FreelancerService::where('seller_id', $sellerId)->delete();
                FreelancerSpecialization::withoutGlobalScope('translate')->where('seller_id', $sellerId)->delete();
                FreelancerCategory::withoutGlobalScope('translate')->where('seller_id', $sellerId)->delete();

                SellerWallet::where('seller_id', $sellerId)->delete();
                NotificationSeen::where('seller_id', $sellerId)->delete();

                $verification = VendorVerification::where('seller_id', $sellerId)->first();
                if ($verification) {
                    foreach (['personal_id_document'] as $docField) {
                        if (!empty($verification->$docField)) {
                            $this->delete('vendor-verifications/' . $verification->$docField);
                        }
                    }
                    $verification->delete();
                }

                $shop = $freelancer->shop;
                if ($shop) {
                    if (!empty($shop->image) && $shop->image !== 'def.png') {
                        $this->delete('shop/' . $shop->image);
                    }
                    if (!empty($shop->banner) && $shop->banner !== 'def.png') {
                        $this->delete('shop/banner/' . $shop->banner);
                    }
                    $shop->delete();
                }

                Storage::where('data_type', \App\Models\Seller::class)->where('data_id', $sellerId)->delete();

                if (!empty($freelancer->image) && $freelancer->image !== 'def.png') {
                    $this->delete('seller/' . $freelancer->image);
                }

                $freelancer->delete();
            });
        } catch (\Throwable $e) {
            \Log::error('[FreelancerAccountController::destroy] Failed to delete freelancer ' . $freelancer->id . ': ' . $e->getMessage());
            return response()->json(['message' => translate('failed_to_delete_freelancer_account')], 500);
        }

        return response()->json(['message' => translate('freelancer_deleted_successfully')]);
    }
}
