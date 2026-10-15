<?php
// app/Helpers/AdHelper.php

namespace App\Helpers;

use App\Models\Ad;
use App\Models\AdGroup;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class AdHelper
{
    /**
     * Parse content for ad shortcodes and replace them with ad HTML.
     */
    public function parse(string $content): string
    {
        // Log to check if the helper is being called
        Log::info('AdHelper parse() method was called.');

        // Regex to find [ad id="..."] or [ad group="..."]
        $pattern = '/\[ad\s+(id|group)="([^"]+)"\]/i';

        return preg_replace_callback($pattern, function ($matches) {
            $type = $matches[1]; // 'id' or 'group'
            $value = $matches[2]; // the actual ID or slug

            if ($type === 'id') {
                return $this->renderAd((int)$value);
            }

            if ($type === 'group') {
                return $this->renderGroup($value);
            }

            return '';
        }, $content);
    }

    /**
     * Renders a single ad by its ID.
     */
    protected function renderAd(int $id): string
    {
        // Cache the ad query for 5 minutes
        $ad = Cache::remember("ad_{$id}", now()->addMinutes(5), function () use ($id) {
            return Ad::where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('start_date')->orWhere('start_date', '<=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('end_date')->orWhere('end_date', '>=', now());
                })
                ->find($id);
        });

        if (!$ad) {
            Log::warning("Ad ID: {$id} not found or inactive/expired.");
            return "";
        }

        return $this->buildAdHtml($ad);
    }

    /**
     * Renders a random ad from a group by its slug.
     */
    protected function renderGroup(string $slug): string
    {
        // Get all active ads in this group (cached)
        $activeAds = Cache::remember("ad_group_ads_{$slug}", now()->addMinutes(5), function () use ($slug) {
            $group = AdGroup::where('slug', $slug)
                ->where('is_active', true)
                ->first();

            if (!$group) {
                return collect(); // Return an empty collection
            }

            // Get all ads that are active and within their date range
            return $group->ads()
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('start_date')->orWhere('start_date', '<=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('end_date')->orWhere('end_date', '>=', now());
                })
                ->get();
        });

        if ($activeAds->isEmpty()) {
            Log::warning("Ad Group: {$slug} not found or has no active ads.");
            return "";
        }

        // Pick one at random
        $ad = $activeAds->random();

        return $this->buildAdHtml($ad);
    }

    /**
     * Builds the final HTML for a given ad, including tracking.
     */
    protected function buildAdHtml(Ad $ad): string
    {
        // These routes MUST exist in your routes/web.php
        $clickUrl = route('ads.click', $ad);
        $impressionUrl = route('ads.impression', $ad);

        // This view MUST exist
        try {
            // This view is at /resources/views/ads/render.blade.php
            return View::make('ads.render', compact('ad', 'clickUrl', 'impressionUrl'))->render();
        } catch (\Exception $e) {
            Log::error("Ad render failed (check views/ads/render.blade.php): " . $e->getMessage());
            return "";
        }
    }
}
