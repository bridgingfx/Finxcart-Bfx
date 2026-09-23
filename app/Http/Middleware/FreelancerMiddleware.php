<?php
namespace App\Http\Middleware;
use Closure;
class FreelancerMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!auth('freelancer')->check()) {
            return redirect()->route('freelancer.auth.login');
        }

        $seller = auth('freelancer')->user();

        if ($seller->account_status === 'inactive') {
            auth()->guard('freelancer')->logout();
            return redirect()->route('freelancer.auth.login')
                ->with('error', translate('Your_account_has_been_suspended_Please_contact_support.') . ' (support@finxcart.com)');
        }

        if ($seller->seller_type !== null && $seller->seller_type !== 'freelancer') {
            auth()->guard('freelancer')->logout();
            return redirect()->route('freelancer.auth.login')
                ->with('error', translate('this_account_is_not_a_freelancer_account'));
        }

        return $next($request);
    }
}
