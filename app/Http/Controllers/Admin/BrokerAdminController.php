<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Broker;
use App\Models\BrokerReview;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class BrokerAdminController extends Controller
{
    // --- BROKER MANAGEMENT ---

    public function index()
    {
        $brokers = Broker::latest()->paginate(10);
        return view('admin-views.brokers.index', compact('brokers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'logo' => 'required|image',
            'affiliate_url' => 'required|url',
            // Scores (0-10)
            'score_license' => 'required|numeric|min:0|max:10|regex:/^\d+(\.\d+)?$/',
            'score_software' => 'required|numeric|min:0|max:10|regex:/^\d+(\.\d+)?$/',
            'score_stability' => 'required|numeric|min:0|max:10|regex:/^\d+(\.\d+)?$/',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $path = public_path('uploads/brokers');
            if (!File::exists($path)) File::makeDirectory($path, 0777, true, true);
            $name = time() . '.' . $request->logo->extension();
            $request->logo->move($path, $name);
            $logoPath = 'public/uploads/brokers/' . $name;
        }

        $broker = Broker::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'logo' => $logoPath,
            'affiliate_url' => $request->affiliate_url,
            'regulations' => $request->regulations,
            'min_deposit' => $request->min_deposit,
            'max_leverage' => $request->max_leverage,
            'platforms' => $request->platforms,
            'description' => $request->description,
            // WikiFX Scores
            'score_license' => $request->score_license,
            'score_software' => $request->score_software,
            'score_stability' => $request->score_stability,
        ]);

        $broker->calculateSystemScore();

        return back()->with('success', 'Broker added successfully');
    }

    public function destroy($id)
    {
        $broker = Broker::findOrFail($id);
        if(File::exists(public_path($broker->logo))) File::delete(public_path($broker->logo));
        $broker->delete();
        return back()->with('success', 'Broker deleted');
    }

    // --- REVIEW MANAGEMENT (APPROVAL) ---

    public function reviewList()
    {
        // Get reviews pending approval first
        $reviews = BrokerReview::with('broker')->orderBy('is_approved', 'asc')->latest()->paginate(20);
        return view('admin-views.brokers.reviews', compact('reviews'));
    }

    public function approveReview($id)
    {
        $review = BrokerReview::findOrFail($id);
        $review->update(['is_approved' => 1]);
        
        // Recalculate the broker's average
        $review->broker->updateUserRating();

        return back()->with('success', 'Review Approved!');
    }

    public function deleteReview($id)
    {
        $review = BrokerReview::findOrFail($id);
        $broker = $review->broker;
        $review->delete();
        
        // Recalculate
        if($broker) $broker->updateUserRating();

        return back()->with('success', 'Review Deleted');
    }

    public function edit($id)
    {
        $broker = Broker::findOrFail($id);
        // Ensure you create this file in the next step
        return view('admin-views.brokers.edit', compact('broker'));
    }

    /**
     * Update the specified broker in storage.
     */
    public function update(Request $request, $id)
    {
        $broker = Broker::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'affiliate_url' => 'required|url',
            'score_license' => 'required|numeric|min:0|max:10|regex:/^\d+(\.\d+)?$/',
            'score_software' => 'required|numeric|min:0|max:10|regex:/^\d+(\.\d+)?$/',
            'score_stability' => 'required|numeric|min:0|max:10|regex:/^\d+(\.\d+)?$/',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // Optional on update
        ]);

        $data = [
            'name' => $request->name,
            'affiliate_url' => $request->affiliate_url,
            'regulations' => $request->regulations,
            'min_deposit' => $request->min_deposit,
            'max_leverage' => $request->max_leverage,
            'platforms' => $request->platforms,
            'description' => $request->description,
            'score_license' => $request->score_license,
            'score_software' => $request->score_software,
            'score_stability' => $request->score_stability,
        ];

        // Handle Image Update
        if ($request->hasFile('logo')) {
            // Delete old logo
            if(File::exists(public_path($broker->logo))) {
                File::delete(public_path($broker->logo));
            }

            // Save new logo
            $path = public_path('uploads/brokers');
            if (!File::exists($path)) File::makeDirectory($path, 0777, true, true);
            
            $name = time() . '.' . $request->logo->extension();
            $request->logo->move($path, $name);
            $data['logo'] = 'public/uploads/brokers/' . $name;
        }

        // Update Record
        $broker->update($data);

        // Recalculate System Score (Important!)
        $broker->calculateSystemScore();

        return redirect()->route('admin.brokers.index')->with('success', 'Broker updated successfully');
    }


}