<?php
// app/Http/Controllers/Admin/ReportController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\AdImpression;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdsReportController extends Controller
{
 public function index(Request $request)
    {
        // ... (Query logic unchanged)
        $impressions = AdImpression::where('is_click', false)->count();
        $clicks = AdImpression::where('is_click', true)->count();
        $ctr = ($impressions > 0) ? ($clicks / $impressions) * 100 : 0;
        $topAds = Ad::withCount(['impressions', 'clicks'])
            ->orderBy('clicks_count', 'desc')
            ->orderBy('impressions_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($ad) {
                $ad->ctr = ($ad->impressions_count > 0) ? ($ad->clicks_count / $ad->impressions_count) * 100 : 0;
                return $ad;
            });
        $clicksByDay = AdImpression::where('is_click', true)
            ->where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as clicks'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->pluck('clicks', 'date');
        $impressionsByDay = AdImpression::where('is_click', false)
            ->where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as impressions'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->pluck('impressions', 'date');
        $labels = [];
        $clickData = [];
        $impressionData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = $date;
            $clickData[] = $clicksByDay[$date] ?? 0;
            $impressionData[] = $impressionsByDay[$date] ?? 0;
        }
        $chartData = [
            'labels' => $labels,
            'clicks' => $clickData,
            'impressions' => $impressionData,
        ];

        // MODIFIED PATH
        return view('admin-views.ads.report.index', compact(
            'impressions', 'clicks', 'ctr', 'topAds', 'chartData'
        ));
    }
}
