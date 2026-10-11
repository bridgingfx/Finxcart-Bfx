{{-- FinXCart redesign: consistent simple line icons for category tiles. $icon in chart|code|card|palette|users|briefcase|grid. --}}
@php
    $paths = [
        'chart' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2" />',
        'code' => '<path d="M8 9l-4 3 4 3M16 9l4 3-4 3M13 5l-2 14" />',
        'card' => '<rect x="2" y="5" width="20" height="14" rx="2" /><path d="M2 10h20" />',
        'palette' => '<circle cx="12" cy="12" r="9" /><circle cx="8.5" cy="10" r="1.2" fill="currentColor" /><circle cx="12" cy="8" r="1.2" fill="currentColor" /><circle cx="15.5" cy="10" r="1.2" fill="currentColor" />',
        'users' => '<circle cx="9" cy="8" r="3.5" /><path d="M2.5 20c.8-3.5 3.4-5.5 6.5-5.5s5.7 2 6.5 5.5M16 4.6a3.5 3.5 0 0 1 0 6.8M18.5 14.9c2 .7 3.2 2.5 3.7 5.1" />',
        'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2" /><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M3 13h18" />',
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1.5" /><rect x="14" y="3" width="7" height="7" rx="1.5" /><rect x="3" y="14" width="7" height="7" rx="1.5" /><rect x="14" y="14" width="7" height="7" rx="1.5" />',
    ];
    $d = $paths[$icon] ?? $paths['grid'];
@endphp
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $d !!}</svg>
