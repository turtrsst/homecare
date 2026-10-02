@props(['type' => 'info', 'title' => null])

@php
    $styles = [
        'info' => ['bg-sky-50 ring-sky-200 text-sky-800', 'info'],
        'success' => ['bg-emerald-50 ring-emerald-200 text-emerald-800', 'check-circle'],
        'warning' => ['bg-amber-50 ring-amber-200 text-amber-800', 'alert'],
        'error' => ['bg-rose-50 ring-rose-200 text-rose-800', 'x-circle'],
    ];
    [$classes, $icon] = $styles[$type] ?? $styles['info'];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl ring-1 ring-inset p-4 sm:p-5 '.$classes]) }} role="{{ $type === 'error' ? 'alert' : 'status' }}">
    <div class="flex gap-3">
        <x-icon :name="$icon" class="w-5 h-5 shrink-0 mt-0.5" />
        <div class="text-sm sm:text-base space-y-1">
            @if ($title)
                <p class="font-bold">{{ $title }}</p>
            @endif
            <div>{{ $slot }}</div>
        </div>
    </div>
</div>
