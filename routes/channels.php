<?php

use App\Models\ChatThread;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

Broadcast::channel('chat-thread.{threadId}', function ($user, $threadId) {
    Log::info('=== BROADCASTING AUTH CHECK ===');
    Log::info('Thread ID: ' . $threadId);
    
    // Log request info
    Log::info('Request Session ID: ' . request()->session()->getId());
    Log::info('Has Auth User (web): ' . (Auth::guard('web')->check() ? 'YES' : 'NO'));
    Log::info('Has Auth User (customer): ' . (Auth::guard('customer')->check() ? 'YES' : 'NO'));
    Log::info('Has Auth User (admin): ' . (Auth::guard('admin')->check() ? 'YES' : 'NO'));
    
    // Check for X-Auth-Token header
    $tokenFromHeader = request()->header('X-Auth-Token');
    Log::info('X-Auth-Token header: ' . ($tokenFromHeader ? substr($tokenFromHeader, 0, 15) . '...' : 'NOT PRESENT'));
    
    $thread = ChatThread::find($threadId);
    
    if (!$thread) {
        Log::error('Auth FAILED: Thread ' . $threadId . ' not found.');
        return false;
    }

    Log::info('Thread Admin ID: ' . $thread->admin_id);
    Log::info('Thread User ID: ' . $thread->user_id);
    Log::info('Thread Session ID: ' . $thread->session_id);
    Log::info('Thread Auth Token: ' . ($thread->auth_token ? 'EXISTS' : 'NULL'));

    // Case 1: Is it a logged-in ADMIN?
    if (Auth::guard('admin')->check()) {
        Log::info('Type: Admin. ID: ' . Auth::guard('admin')->id());
        $is_allowed = Auth::guard('admin')->id() === $thread->admin_id;
        Log::info('Allowed: ' . ($is_allowed ? 'YES' : 'NO'));
        Log::info('-------------------------------');
        return $is_allowed;
    }

    // Case 2: Is it a logged-in WEB user?
    if (Auth::guard('web')->check()) {
        Log::info('Type: Web User. ID: ' . Auth::guard('web')->id());
        $is_allowed = Auth::guard('web')->id() === $thread->user_id;
        Log::info('Allowed: ' . ($is_allowed ? 'YES' : 'NO'));
        Log::info('-------------------------------');
        return $is_allowed;
    }

    // Case 3: Is it a logged-in CUSTOMER?
    if (Auth::guard('customer')->check()) {
        Log::info('Type: Customer User. ID: ' . Auth::guard('customer')->id());
        $is_allowed = Auth::guard('customer')->id() === $thread->user_id;
        Log::info('Allowed: ' . ($is_allowed ? 'YES' : 'NO'));
        Log::info('-------------------------------');
        return $is_allowed;
    }

    // Case 4: Is it a GUEST with auth token?
    if ($tokenFromHeader && $thread->auth_token) {
        Log::info('Type: Guest with Token');
        $is_allowed = ($thread->auth_token === $tokenFromHeader);
        Log::info('Token Match: ' . ($is_allowed ? 'YES' : 'NO'));
        if (!$is_allowed) {
            Log::info('Expected: ' . substr($thread->auth_token, 0, 20) . '...');
            Log::info('Got: ' . substr($tokenFromHeader, 0, 20) . '...');
        }
        Log::info('-------------------------------');
        return $is_allowed;
    }

    // Case 5: Is it a GUEST with session?
    $guestSessionId = request()->session()->getId();
    if ($thread->session_id) {
        Log::info('Type: Guest with Session');
        $is_allowed = ($thread->session_id === $guestSessionId);
        Log::info('Session Match: ' . ($is_allowed ? 'YES' : 'NO'));
        if (!$is_allowed) {
            Log::info('Expected: ' . $thread->session_id);
            Log::info('Got: ' . $guestSessionId);
        }
        Log::info('-------------------------------');
        return $is_allowed;
    }
    
    Log::warning('No valid auth method found for guest');
    Log::info('-------------------------------');
    return false;
    
}, ['guards' => ['web', 'admin', 'customer']]);
