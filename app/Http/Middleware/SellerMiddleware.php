<?php
namespace App\Http\Middleware;
use Closure;
class SellerMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!auth('seller')->check()) {
            return redirect()->route('vendor.auth.login');
        }

        $seller = auth('seller')->user();

        if ($seller->account_status === 'inactive') {
            auth()->guard('seller')->logout();
            return redirect()->route('vendor.auth.login')
                ->with('error', translate('Your_account_has_been_suspended_Please_contact_support.') . ' (support@finxcart.com)');
        }

        if ($seller->seller_type === 'freelancer') {
            auth()->guard('seller')->logout();
            return redirect()->route('freelancer.auth.login');
        }

        return $next($request);
    }
}