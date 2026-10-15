<?php

namespace App\Http\Controllers;

use App\Events\NewChatMessage;
use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon; // Added for date filtering

class ChatController extends Controller
{
    /**
     * CUSTOMER: Start a new chat or get existing one.
     */
    public function startChat(Request $request)
    {
        $sessionId = $request->session()->getId();
        
        // Check both customer and web guards
        $userId = Auth::guard('customer')->id() ?? Auth::guard('web')->id();

        Log::info('Starting chat', [
            'user_id' => $userId,
            'session_id' => $sessionId,
            'guard_customer' => Auth::guard('customer')->check(),
            'guard_web' => Auth::guard('web')->check(),
        ]);

        $thread = $userId
            ? ChatThread::where('user_id', $userId)->first()
            : ChatThread::where('session_id', $sessionId)->first();

        if (!$thread) {
            $admin = Admin::find(1); // Assuming admin 1 handles chats

            $thread = ChatThread::create([
                'user_id' => $userId,
                'session_id' => $userId ? null : $sessionId,
                'admin_id' => $admin->id ?? null,
            ]);
            
            Log::info('Created new thread', [
                'thread_id' => $thread->id,
            ]);
        }

        return response()->json([
            'thread_id' => $thread->id,
            'auth_token' => $thread->auth_token, // Make sure your ChatThread model generates this
            'messages' => $thread->messages()->orderBy('created_at', 'asc')->get()
        ]);
    }

    /**
     * CUSTOMER & ADMIN: Send a new message.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'thread_id' => 'required|exists:chat_threads,id',
            'message' => 'required|string',
        ]);

        // Determine sender type
        if (Auth::guard('admin')->check()) {
            $senderType = 'admin';
            $adminId = Auth::guard('admin')->id();
        } elseif (Auth::guard('customer')->check() || Auth::guard('web')->check()) {
            $senderType = 'customer';
        } else {
            // This case might be for guests
            $senderType = 'customer'; 
        }

        $thread = ChatThread::findOrFail($request->thread_id);
        $sessionId = $request->session()->getId();

        // Authorization check
        if ($senderType === 'admin' && $adminId !== $thread->admin_id) {
            Log::warning('Unauthorized admin attempt');
            return response()->json(['error' => 'Unauthorized admin'], 403);
        }
        
        if ($senderType === 'customer') {
            $userId = Auth::guard('customer')->id() ?? Auth::guard('web')->id();
            // Updated guest check
            $isAuthorized = ($userId && $userId === $thread->user_id) || 
                              (!$userId && $sessionId === $thread->session_id);
            
            if (!$isAuthorized) {
                Log::warning('Unauthorized customer/guest attempt', [
                    'thread_user_id' => $thread->user_id,
                    'thread_session_id' => $thread->session_id,
                    'current_user_id' => $userId,
                    'current_session_id' => $sessionId
                ]);
                return response()->json(['error' => 'Unauthorized customer'], 403);
            }
        }

        $message = ChatMessage::create([
            'chat_thread_id' => $request->thread_id,
            'body' => $request->message,
            'sender_type' => $senderType,
        ]);

        // Update thread timestamp to bring it to the top
        $thread->touch(); 

        Log::info('Message created', [
            'message_id' => $message->id,
            'thread_id' => $message->chat_thread_id,
            'sender_type' => $senderType,
        ]);

        broadcast(new NewChatMessage($message))->toOthers();

        return response()->json($message);
    }

    /**
     * ADMIN: Show the main chat dashboard.
     */
    public function adminDashboard(Request $request)
    {
        $filter = $request->query('filter', 'today'); // Default to 'today'
        $adminId = Auth::guard('admin')->id();

        $threadsQuery = ChatThread::where('admin_id', $adminId)
                                  ->with(['user']) // Eager-load the user
                                  ->withCount('messages')
                                  ->orderBy('updated_at', 'desc');

        if ($filter === 'today') {
            $threadsQuery->whereDate('updated_at', Carbon::today());
        }
        // No 'else' means 'all' will just not apply the date filter

        $threads = $threadsQuery->get();
        
        // Make sure to use the correct view path
        return view('admin-views.chat-dashboard', [
            'threads' => $threads,
            'filter' => $filter
        ]);
    }

    /**
     * ADMIN: Show the view for a specific chat.
     */
    public function adminChatView(ChatThread $thread)
    {
        if ($thread->admin_id !== Auth::guard('admin')->id()) {
            abort(403);
        }

        // Optional: Implement logic to mark messages as 'read'
        // ChatMessage::where('chat_thread_id', $thread->id)
        //     ->where('sender_type', 'customer')
        //     ->whereNull('read_at')
        //     ->update(['read_at' => now()]);

        return view('admin-views.chat-view', [
            'thread' => $thread,
            'messages' => $thread->messages()->orderBy('created_at', 'asc')->get(),
            'user' => $thread->user // Pass the user data to the view
        ]);
    }
}