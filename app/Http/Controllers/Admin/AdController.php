<?php
// app/Http/Controllers/Admin/AdController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\AdGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdController extends Controller
{
    public function index()
    {
        $ads = Ad::with('adGroup')->latest()->paginate(10);
        // MODIFIED PATH
        return view('admin-views.ads.manage.index', compact('ads'));
    }

    public function create()
    {
        $adGroups = AdGroup::where('is_active', true)->pluck('name', 'id');
        // MODIFIED PATH
        return view('admin-views.ads.manage.create', compact('adGroups'));
    }

    public function store(Request $request)
    {
        // ... (Store logic unchanged)
        $data = $this->validateAd($request);
        $data['is_active'] = $request->has('is_active');
        $data['ad_group_id'] = $request->ad_group_id ?: null;
        if ($request->hasFile('image_path')) {
            $data['image_path'] = $request->file('image_path')->store('ads', 'public');
        }
        Ad::create($data);
        return redirect()->route('admin.ads.index')
                         ->with('success', 'Ad created successfully.');
    }

    public function edit(Ad $ad)
    {
        $adGroups = AdGroup::where('is_active', true)->pluck('name', 'id');
        // MODIFIED PATH
        return view('admin-views.ads.manage.edit', compact('ad', 'adGroups'));
    }

    public function update(Request $request, Ad $ad)
    {
        // ... (Update logic unchanged)
        $data = $this->validateAd($request, $ad);
        $data['is_active'] = $request->has('is_active');
        $data['ad_group_id'] = $request->ad_group_id ?: null;
        if ($request->hasFile('image_path')) {
            if ($ad->image_path) {
                Storage::disk('public')->delete($ad->image_path);
            }
            $data['image_path'] = $request->file('image_path')->store('ads', 'public');
        }
        $ad->update($data);
        return redirect()->route('admin.ads.index')
                         ->with('success', 'Ad updated successfully.');
    }

    public function destroy(Ad $ad)
    {
        // ... (Destroy logic unchanged)
        if ($ad->image_path) {
            Storage::disk('public')->delete($ad->image_path);
        }
        $ad->delete();
        return redirect()->route('admin.ads.index')
                         ->with('success', 'Ad deleted successfully.');
    }

    private function validateAd(Request $request, Ad $ad = null): array
    {
        // ... (Validation logic unchanged)
        $imageRule = 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048';
        if ($request->input('type') === 'image') {
            $imageRule = $ad ? $imageRule : 'required|' . $imageRule;
        }
        return $request->validate([
            'name' => 'required|string|max:255',
            'ad_group_id' => 'nullable|exists:ad_groups,id',
            'type' => 'required|in:image,code,text',
            'content' => Rule::requiredIf($request->type === 'code' || $request->type === 'text'),
            'image_path' => $imageRule,
            'destination_url' => Rule::requiredIf($request->type === 'image' || $request->type === 'text') . '|nullable|url',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);
    }
}
