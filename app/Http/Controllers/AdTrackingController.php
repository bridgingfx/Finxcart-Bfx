<?php
// app/Http/Controllers/AdTrackingController.php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\AdImpression;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class AdTrackingController extends Controller
{
    public function trackClick(Request $request, Ad $ad)
    {
        if (!$ad->is_active) {
            return redirect($ad->destination_url ?: '/');
        }

        try {
            AdImpression::create([
                'ad_id' => $ad->id,
                'ad_group_id' => $ad->ad_group_id,
                'is_click' => true,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => $request->session()->getId(),
            ]);
        } catch (\Exception $e) {
            Log::error('Ad click tracking failed: ' . $e->getMessage());
        }

        return redirect()->away($ad->destination_url ?: '/');
    }

    public function trackImpression(Request $request, Ad $ad)
    {
        if ($ad->is_active) {
            try {
                AdImpression::create([
                    'ad_id' => $ad->id,
                    'ad_group_id' => $ad->ad_group_id,
                    'is_click' => false,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'session_id' => $request->session()->getId(),
                ]);
            } catch (\Exception $e) {
                Log::error('Ad impression tracking failed: ' . $e->getMessage());
            }
        }

        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        return Response::make($pixel, 200, ['Content-Type' => 'image/gif']);
    }
}
