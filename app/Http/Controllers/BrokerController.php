<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\BrokerReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BrokerController extends Controller
{
public function index(Request $request)    {
    
    
    $query = Broker::where('is_active', true);

    // Filter Logic
    if ($request->has('sort')) {
        switch ($request->sort) {
            case 'authority_score':
                $query->orderBy('system_rating', 'desc');
                break;
            case 'user_rating':
                $query->orderBy('user_rating', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('system_rating', 'desc');
                break;
        }
    } else {
        // Default Sort: Authority Score
        $query->orderBy('system_rating', 'desc');
    }

    $brokers = $query->paginate(12);
        return view('web-views.brokers.index', compact('brokers'));
    }

    public function show($slug)
    {
        $broker = Broker::with(['reviews' => function($q){
            $q->where('is_approved', 1)->latest();
        }])->where('slug', $slug)->firstOrFail();

        return view('web-views.brokers.show', compact('broker'));
    }

    public function storeReview(Request $request, $id)
    {
        // Check Auth (Adjust guard 'customer' if needed)
        if (!Auth::guard('customer')->check()) {
            return redirect()->route('customer.auth.login')->with('error', 'Login to review');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5|regex:/^\d+$/',
            'comment' => 'required|string',
        ]);

        BrokerReview::create([
            'broker_id' => $id,
            'user_id' => Auth::guard('customer')->id(),
            'reviewer_name' => Auth::guard('customer')->user()->f_name . ' ' . Auth::guard('customer')->user()->l_name,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_approved' => false, // MUST BE APPROVED BY ADMIN
        ]);

        return back()->with('success', 'Review submitted! It will appear after admin approval.');
    }
}