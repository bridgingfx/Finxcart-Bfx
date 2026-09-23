<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerificationComplete
{
    public function handle(Request $request, Closure $next)
    {
        $seller = auth('seller')->user();

        if (!$seller) {
            return redirect()->route('vendor.auth.login');
        }

        $verification = $seller->vendorVerification;

        if (!$verification || $verification->status !== 'approved') {
            // Allow unapproved vendors to view the product list — modal handles the UX
            if ($request->routeIs('vendor.products.list')) {
                return $next($request);
            }
            // Send "Add Product" attempts to the tier page so vendor knows what to do next
            if ($request->routeIs('vendor.products.add')) {
                return redirect()->route('vendor.tier.index')
                    ->with('warning', 'Please complete your verification and purchase a tier plan to start adding products.');
            }
            return redirect()->route('vendor.dashboard.index')
                ->with('warning', 'Your account is under review. You can add products once approved.');
        }

        return $next($request);
    }
}
