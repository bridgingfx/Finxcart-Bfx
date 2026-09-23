<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Influencer;
use App\Models\InfluencerInquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File; // Import File Facade

class InfluencerAdminController extends Controller
{
    public function index()
    {
        $influencers = Influencer::latest()->paginate(10);
        return view('admin-views.influencers.index', compact('influencers'));
    }

    public function create()
    {
        return view('admin-views.influencers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'instagram_followers' => 'nullable|integer|regex:/^\d+$/',
            'youtube_subscribers' => 'nullable|integer|regex:/^\d+$/',
            'tiktok_followers' => 'nullable|integer|regex:/^\d+$/',
            'bio' => 'required',
        ]);

        $imagePath = null;

        // FIXED: Check folder existence and create if missing
        if ($request->hasFile('profile_image')) {
            $path = public_path('uploads/influencers');
            
            if (!File::exists($path)) {
                File::makeDirectory($path, 0777, true, true);
            }

            $imageName = time() . '.' . $request->profile_image->extension();
            $request->profile_image->move($path, $imageName);
            $imagePath = 'uploads/influencers/' . $imageName;
        }

        $totalReach = ($request->instagram_followers ?? 0) + 
                      ($request->youtube_subscribers ?? 0) + 
                      ($request->tiktok_followers ?? 0);

        Influencer::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . rand(100,999),
            'bio' => $request->bio,
            'profile_image' => $imagePath,
            'instagram_followers' => $request->instagram_followers ?? 0,
            'youtube_subscribers' => $request->youtube_subscribers ?? 0,
            'tiktok_followers' => $request->tiktok_followers ?? 0,
            'total_reach' => $totalReach,
        ]);

        return redirect()->route('admin.influencers.index')
                         ->with('success', 'Influencer added successfully');
    }

    public function edit($id)
    {
        $influencer = Influencer::findOrFail($id);
        return view('admin-views.influencers.edit', compact('influencer'));
    }

    public function update(Request $request, $id)
    {
        $influencer = Influencer::findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->except(['profile_image']);

        // FIXED: Image Update Logic
        if ($request->hasFile('profile_image')) {
            $path = public_path('uploads/influencers');
            
            if (!File::exists($path)) {
                File::makeDirectory($path, 0777, true, true);
            }

            // Delete old image
            if(File::exists(public_path($influencer->profile_image))) {
                File::delete(public_path($influencer->profile_image));
            }

            $imageName = time() . '.' . $request->profile_image->extension();
            $request->profile_image->move($path, $imageName);
            $data['profile_image'] = 'uploads/influencers/' . $imageName;
        }

        $data['total_reach'] = ($request->instagram_followers ?? 0) + 
                               ($request->youtube_subscribers ?? 0) + 
                               ($request->tiktok_followers ?? 0);

        $influencer->update($data);

        return redirect()->route('admin.influencers.index')
                         ->with('success', 'Influencer updated successfully');
    }

    public function destroy($id)
    {
        $influencer = Influencer::findOrFail($id);
        
        if(File::exists(public_path($influencer->profile_image))) {
            File::delete(public_path($influencer->profile_image));
        }

        $influencer->delete();
        return back()->with('success', 'Influencer deleted');
    }

    public function inquiries()
    {
        $inquiries = InfluencerInquiry::with('influencer')->latest()->paginate(10);
        return view('admin-views.influencers.inquiries', compact('inquiries'));
    }
}