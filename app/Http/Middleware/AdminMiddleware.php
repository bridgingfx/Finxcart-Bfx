<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next): mixed
    {
        if (Auth::guard('admin')->check()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return new JsonResponse([
                'message' => translate('unauthenticated'),
                'redirect_url' => url('login/' . getWebConfig(name: 'admin_login_url')),
            ], 401);
        }

        return redirect()->to(url('login/' . getWebConfig(name: 'admin_login_url')));
    }
}
