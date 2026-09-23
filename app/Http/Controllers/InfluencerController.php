<?php

namespace App\Http\Controllers;

use App\Models\Influencer;
use App\Models\InfluencerInquiry;
use App\Models\InfluencerRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InfluencerController extends Controller
{
    /**
     * Display the public list of influencers.
     */
    public function index(Request $request)
    {
        $query = Influencer::query();

        // Sorting Logic
        if ($request->has('sort')) {
            if ($request->sort == 'rating_desc') {
                $query->orderBy('avg_rating', 'desc');
            } elseif ($request->sort == 'followers_desc') {
                $query->orderBy('total_reach', 'desc');
            }
        } else {
            // Default sort: Newest first
            $query->orderBy('created_at', 'desc');
        }

        $influencers = $query->paginate(12);

        // Path: resources/themes/default/web-views/influencers/index.blade.php
        return view('web-views.influencers.index', compact('influencers'));
    }

    /**
     * Display the individual influencer profile.
     */
    public function show($slug)
    {
        // Eager load ratings and the user who gave the rating
        $influencer = Influencer::with(['ratings.user'])->where('slug', $slug)->firstOrFail();
        
        // Path: resources/themes/default/web-views/influencers/show.blade.php
        return view('web-views.influencers.show', compact('influencer'));
    }

    /**
     * Store the "Contact" modal inquiry form.
     */
    public function storeInquiry(Request $request)
    {
        $request->validate([
            'influencer_id' => 'required|exists:influencers,id',
            'customer_name' => 'required|string',
            'customer_email' => 'required|email',
            'message' => 'required|string',
            'phone' => 'nullable|string'
        ]);

        InfluencerInquiry::create($request->all());

        return back()->with('success', 'Your inquiry has been sent successfully! We will contact you soon.');
    }

    /**
     * Store a user rating (Star rating + Comment).
     */
public function storeRating(Request $request, $id)
{
    if (!Auth::guard('customer')->user()) {
        return redirect()->route('customer.auth.login')->with('error', 'Please login to rate.');
    }

    $request->validate([
        'rating' => 'required|integer|min:1|max:5|regex:/^\d+$/',
        'comment' => 'nullable|string'
    ]);

    // 2. Find Influencer
    $influencer = Influencer::findOrFail($id);

    // 3. Save Rating
    // We use the Model Relationship to save safely
    InfluencerRating::updateOrCreate(
        [
            'user_id' => Auth::guard('customer')->id(),
            'influencer_id' => $id
        ],
        [
            'rating' => $request->rating,
            'comment' => $request->comment
        ]
    );

    // 4. Update Stats
    $influencer->updateRating();

    // 5. FIXED: Redirect explicitly to the Profile Page using the Slug
    return redirect()->route('influencers.show', $influencer->slug)
                     ->with('success', 'Review submitted successfully.');
}
}