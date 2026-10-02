@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'icon' => null,
    'full' => false,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition-all duration-150 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.98] select-none';

    $variants = [
        'primary' => 'bg-brand-600 text-white hover:bg-brand-700 shadow-sm',
        'secondary' => 'bg-white text-stone-700 ring-1 ring-stone-300 hover:bg-stone-50',
        'soft' => 'bg-brand-50 text-brand-700 hover:bg-brand-100 ring-1 ring-brand-100',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-700 shadow-sm',
        'danger-soft' => 'bg-rose-50 text-rose-700 hover:bg-rose-100 ring-1 ring-rose-100',
        'ghost' => 'text-stone-600 hover:bg-stone-100',
        'warm' => 'bg-warm-500 text-white hover:bg-warm-600 shadow-sm',
    ];

    $sizes = [
        'sm' => 'text-sm px-3 min-h-9 py-1.5',
        'md' => 'text-sm sm:text-base px-5 min-h-11 py-2.5',
        'lg' => 'text-base sm:text-lg px-7 min-h-12 py-3',
    ];

    $classes = $base.' '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']).($full ? ' w-full' : '');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon) <x-icon :name="$icon" class="w-5 h-5 shrink-0" /> @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon) <x-icon :name="$icon" class="w-5 h-5 shrink-0" /> @endif
        {{ $slot }}
    </button>
@endif
