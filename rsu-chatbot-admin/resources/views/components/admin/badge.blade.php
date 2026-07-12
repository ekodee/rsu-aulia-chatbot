@props(['type'])

@php
    $colors = [
        'success' => 'bg-green-100 text-green-600',
        'warning' => 'bg-yellow-100 text-yellow-600',
        'error' => 'bg-red-100 text-red-600',
    ];
@endphp

<span class="px-3 py-1 text-xs rounded-full {{ $colors[$type] }}">
    {{ $slot }}
</span>
