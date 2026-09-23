<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminChatController extends Controller
{
    const SUPPORT_ADMIN_ID = 36;

    public function loginAsSupport()
    {
        try {
            // Verify admin authentication
            if (!Auth::guard('admin')->check()) {
                abort(403, 'Unauthorized access');
            }

            // Find the Support Admin proxy user
            $supportUser = User::find(self::SUPPORT_ADMIN_ID);
            
            if (!$supportUser) {
                Log::error('Support Admin user not found', ['id' => self::SUPPORT_ADMIN_ID]);
                return back()->with('error', 'Support chat is not configured. Please contact your system administrator.');
            }

            // Verify required fields exist
            if (empty($supportUser->email)) {
                Log::error('Support Admin user has no email', ['user' => $supportUser]);
                return back()->with('error', 'Support user configuration error. Please contact your system administrator.');
            }

            // Log in as the Support Admin on the web guard
            Auth::guard('web')->login($supportUser);

            Log::info('Admin logged in as support', [
                'admin_id' => Auth::guard('admin')->id(),
                'support_user_id' => $supportUser->id
            ]);

            // Redirect to Chatify dashboard
            return redirect()->route('chatify');

        } catch (\Exception $e) {
            Log::error('Error logging in as support', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Failed to open support chat: ' . $e->getMessage());
        }
    }
}