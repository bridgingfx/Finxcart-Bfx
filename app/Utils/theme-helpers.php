<?php

if (!function_exists('clean_html')) {
    /**
     * Sanitize vendor-supplied rich-text HTML before rendering.
     * Strips all tags not in the safe allowlist. Attributes on allowed
     * tags are also stripped to prevent event-handler injection.
     * For full XSS protection install ezyang/htmlpurifier and update
     * this function to use HTMLPurifier instead.
     */
    function clean_html(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }
        $allowed = '<p><br><b><strong><i><em><u><ul><ol><li><h1><h2><h3><h4><h5><h6><span><div><blockquote><pre><code>';
        $stripped = strip_tags($html, $allowed);
        // Remove all attributes from the remaining tags to block event-handler injection
        return preg_replace('/<(\w+)[^>]*>/', '<$1>', $stripped);
    }
}

if (!function_exists('theme_asset')) {
    function theme_asset($path = null): string
    {
        $themeName = theme_root_path();
        if($themeName == 'default'){
            return dynamicAsset(path: $path);
        }else{
            if (DOMAIN_POINTED_DIRECTORY == 'public') {
                return dynamicAsset(path: 'public/themes/'.$themeName.'/public/'.$path);
            }else{
                return dynamicAsset(path: 'resources/themes/'.$themeName.'/public/'.$path);
            }
        }
    }
}

if (!function_exists('theme_root_path')) {
    function theme_root_path(): string
    {
        return env('WEB_THEME') == null ? 'default' : env('WEB_THEME');
    }
}

if (!function_exists('getHexToRGBColorCode')) {
    function getHexToRGBColorCode($hex): ?string
    {
        $result = preg_match('/^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i', $hex, $matches);
        return $result ? hexdec($matches[1]) . ', ' . hexdec($matches[2]) . ', ' . hexdec($matches[3]) : null;
    }
}

if (!function_exists('getSystemDynamicPartials')) {
    function getSystemDynamicPartials($type = null): mixed
    {
        if ($type == 'analytics_script') {
            return view("system-partials._analytics_script");
        }
        return null;
    }
}
