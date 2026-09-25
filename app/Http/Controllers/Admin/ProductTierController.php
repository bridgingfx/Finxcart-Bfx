<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductTier;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductTierController extends Controller
{
    /**
     * Display a listing of the product tiers.
     */
    public function index()
    {
        $tiers = ProductTier::all();
        return view('admin-views.tiers.index', compact('tiers'));
    }

    /**
     * Show the form for creating a new product tier.
     */
    public function create()
    {
        return view('admin-views.tiers.create');
    }

    /**
     * Store a newly created product tier.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:admin_product_tiers,name',
            'target_category' => 'required|string|max:150',
            'ideal_product_types' => 'required|string',
            'monthly_fee_usd' => 'nullable|numeric|min:0|regex:/^\d+(\.\d+)?$/',
            'sales_commission_rate' => 'nullable|numeric|min:0|max:100|regex:/^\d+(\.\d+)?$/',
            'is_commission_only' => 'boolean',
            'price_threshold_min_usd' => 'nullable|numeric|min:0|regex:/^\d+(\.\d+)?$/',
            'price_threshold_max_usd' => 'nullable|numeric|gt:price_threshold_min_usd|regex:/^\d+(\.\d+)?$/',
            'is_recurring_focus' => 'boolean',
            'is_free_first_month' => 'boolean',
            'images_videos_allowed' => 'required|string|max:50',
            'search_ranking' => 'required|string|max:50',
            'buyer_interaction' => 'required|string|max:50',
            'analytics' => 'required|string|max:50',
            'billing_tools' => 'required|string|max:50',
            'api_integrations' => 'nullable|boolean',
            'listings_per_fee' => 'required|string|max:50',
            'featured_product_quota' => 'nullable|integer|min:0|regex:/^\d+$/',
            'is_featured_vendor' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // PREVIOUSLY: This line caused the issue by dividing by 100
        // $data = $request->except(['sales_commission_rate']);
        // $data['sales_commission_rate'] = $request->input('sales_commission_rate') / 100;

        // FIX: Save all data directly (including sales_commission_rate as-is)
        $data = $request->all();
        $data['is_featured_vendor'] = $request->boolean('is_featured_vendor');

        try {
            $tier = ProductTier::create($data);
            return redirect()->route('admin.tiers.index')->with('success', translate('Product tier created successfully!'));
        } catch (\Exception $e) {
            \Log::error("Failed to create Product Tier: " . $e->getMessage());
            return back()->withInput()->with('error', translate('Failed to create product tier. Please try again.'));
        }
    }

    /**
     * Display the specified product tier.
     */
    public function show(ProductTier $productTier)
    {
        return response()->json($productTier);
    }

    /**
     * Show the form for editing the specified product tier.
     */
    public function edit($id)
    {
        $tier = ProductTier::findOrFail($id);

        $productCategories = [
            (object)['id' => 1, 'name' => 'One-time sales (low-value)'],
            (object)['id' => 2, 'name' => 'One-time sales (mid-value)'],
            (object)['id' => 3, 'name' => 'One-time sales (high-value)'],
            (object)['id' => 4, 'name' => 'Recurring/monthly rentals'],
            (object)['id' => 5, 'name' => 'High-value recurring rentals'],
            (object)['id' => 6, 'name' => 'One-time niche products'],
        ];

        $imagesOptions = ['1 image', 'Up to 5', 'Unlimited'];
        $rankingOptions = ['Standard', 'Priority (top 10)', 'Top + Homepage'];
        $interactionOptions = ['Email', 'Chat', 'Chat + Priority Support', 'Chat + Dedicated Manager'];
        $analyticsOptions = ['Views only', 'Views, conversions', 'Sources, demographics', 'Churn, lifetime value', 'Predictive insights'];
        $billingOptions = ['None', 'Auto-renewals + Invoicing'];

        return view('admin-views.tiers.edit', compact(
            'tier',
            'productCategories',
            'imagesOptions',
            'rankingOptions',
            'interactionOptions',
            'analyticsOptions',
            'billingOptions'
        ));
    }

    /**
     * Update the specified product tier in storage.
     */
    public function update(Request $request, $id)
    {
        $tier = ProductTier::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:admin_product_tiers,name,' . $tier->id,
            'target_category' => 'required|string|max:150',
            'ideal_product_types' => 'required|string',
            'monthly_fee_usd' => 'nullable|numeric|min:0|regex:/^\d+(\.\d+)?$/',
            'sales_commission_rate' => 'nullable|numeric|min:0|max:100|regex:/^\d+(\.\d+)?$/',
            'is_commission_only' => 'boolean',
            'price_threshold_min_usd' => 'nullable|numeric|min:0|regex:/^\d+(\.\d+)?$/',
            'price_threshold_max_usd' => 'nullable|numeric|gt:price_threshold_min_usd|regex:/^\d+(\.\d+)?$/',
            'is_recurring_focus' => 'boolean',
            'is_free_first_month' => 'boolean',
            'images_videos_allowed' => 'required|string|max:50',
            'search_ranking' => 'required|string|max:50',
            'buyer_interaction' => 'required|string|max:50',
            'analytics' => 'required|string|max:50',
            'billing_tools' => 'required|string|max:50',
            'api_integrations' => 'nullable|boolean',
            'listings_per_fee' => 'required|string|max:50',
            'featured_product_quota' => 'nullable|integer|min:0|regex:/^\d+$/',
            'is_featured_vendor' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // FIX: Removed 'sales_commission_rate' from the except list so it is handled normally
        // Also removed the logic that divided it by 100
        $data = $request->except(['_token', '_method']);

        // Handle boolean checkboxes
        $data['is_commission_only'] = $request->boolean('is_commission_only');
        $data['is_recurring_focus'] = $request->boolean('is_recurring_focus');
        $data['is_free_first_month'] = $request->boolean('is_free_first_month');
        $data['api_integrations'] = $request->boolean('api_integrations');
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured_vendor'] = $request->boolean('is_featured_vendor');

        try {
            $tier->update($data);
            return redirect()->route('admin.tiers.index')->with('success', translate('Product tier updated successfully!'));
        } catch (\Exception $e) {
            \Log::error("Failed to update Product Tier: " . $e->getMessage());
            return back()->withInput()->with('error', translate('Failed to update product tier. Please try again.'));
        }
    }

    public function toggleActive($id)
    {
        $tier = ProductTier::findOrFail($id);
        $tier->update(['is_active' => !$tier->is_active]);
        $status = $tier->is_active ? translate('activated') : translate('deactivated');
        ToastMagic::success(translate('Tier') . ' ' . $status . ' ' . translate('successfully'));
        return back();
    }

    public function destroy($id)
    {
        $tier = ProductTier::findOrFail($id);
        $tier->delete();
        return redirect()->route('admin.tiers.index')->with('success', 'Product tier deleted successfully.');
    }
}
