<?php
// app/Http/Controllers/Admin/AdGroupController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdGroupController extends Controller
{
    public function index()
    {
        $adGroups = AdGroup::latest()->paginate(10);
        // MODIFIED PATH
        return view('admin-views.ads.groups.index', compact('adGroups'));
    }

    public function create()
    {
        // MODIFIED PATH
        return view('admin-views.ads.groups.create');
    }

    public function store(Request $request)
    {
        // ... (Store logic unchanged)
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:ad_groups',
        ]);
        AdGroup::create([
            'name' => $request->name,
            'slug' => Str::slug($request->slug),
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);
        return redirect()->route('admin.ad-groups.index')
                         ->with('success', 'Ad Group created successfully.');
    }

    public function edit(AdGroup $adGroup)
    {
        // MODIFIED PATH
        return view('admin-views.ads.groups.edit', compact('adGroup'));
    }

    public function update(Request $request, AdGroup $adGroup)
    {
        // ... (Update logic unchanged)
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:ad_groups,slug,' . $adGroup->id,
        ]);
        $adGroup->update([
            'name' => $request->name,
            'slug' => Str::slug($request->slug),
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);
        return redirect()->route('admin.ad-groups.index')
                         ->with('success', 'Ad Group updated successfully.');
    }

    public function destroy(AdGroup $adGroup)
    {
        // ... (Destroy logic unchanged)
        $adGroup->delete();
        return redirect()->route('admin.ad-groups.index')
                         ->with('success', 'Ad Group deleted successfully.');
    }
}
