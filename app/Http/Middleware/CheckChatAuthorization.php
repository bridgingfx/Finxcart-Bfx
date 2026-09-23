<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CheckChatAuthorization
{
    public function handle(Request $request, Closure $next)
    {
        // Extract target user ID from route parameter or request
        $targetUserId = $request->route('id') ?? $request->input('id');
        
        // If no target specified, allow (dashboard view)
        if (!$targetUserId) {
            return $next($request);
        }

        // Check authorization using Gate
        if (!Gate::allows('chat-with-user', $targetUserId)) {
            abort(403, 'You are not authorized to chat with this user.');
        }

        return $next($request);
    }
}