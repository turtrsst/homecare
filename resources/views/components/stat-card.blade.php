@props(['label', 'value', 'icon' => 'chart', 'color' => 'teal', 'href' => null])

@php
    $colors = [
        'teal' => 'bg-brand-50 text-brand-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'blue' => 'bg-blue-50 text-blue-600',
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'rose' => 'bg-rose-50 text-rose-600',
        'indigo' => 'bg-indigo-50 text-indigo-600',
    ];
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'bg-white rounded-2xl ring-1 ring-stone-200/70 shadow-card p-5 flex items-center gap-4 '.($href ? 'transition hover:shadow-pop hover:ring-brand-300' : '')]) }}>
    <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 {{ $colors[$color] ?? $colors['teal'] }}">
        <x-icon :name="$icon" class="w-6 h-6" />
    </div>
    <div class="min-w-0">
        <p class="text-2xl font-extrabold text-stone-900 leading-tight">{{ $value }}</p>
        <p class="text-sm text-stone-500 truncate">{{ $label }}</p>
    </div>
</{{ $tag }}>
