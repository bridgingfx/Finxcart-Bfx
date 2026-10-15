<?php

if (!function_exists('youtubeEmbed')) {
    function youtubeEmbed($url)
    {
        if (empty($url)) {
            return null;
        }

        // Already an embed URL
        if (str_contains($url, 'youtube.com/embed/')) {
            return $url;
        }

        // Extract video ID from different YouTube formats
        $patterns = [
            '/youtu\.be\/([^\?&]+)/',
            '/youtube\.com\/.*v=([^\?&]+)/',
            '/youtube\.com\/shorts\/([^\?&]+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return "https://www.youtube.com/embed/" . $matches[1];
            }
        }

        return null;
    }
}
