{{-- Logo + nama merek Soeradji Care. Props: size (sm|md|lg), dark (bool), tagline (bool) --}}
@props(['size' => 'md', 'dark' => false, 'tagline' => true])

@php
    $box = match ($size) {
        'sm' => 'h-9 w-9 rounded-xl',
        'lg' => 'h-14 w-14 rounded-2xl',
        default => 'h-11 w-11 rounded-2xl',
    };
    $img = match ($size) {
        'sm' => 'h-6 w-6',
        'lg' => 'h-9 w-9',
        default => 'h-7 w-7',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-3']) }}>
    <span class="{{ $box }} flex shrink-0 items-center justify-center bg-white/0 shadow-lg shadow-soeradji-600/20 ring-1 ring-black/5">
        <img src="{{ asset(config('homecare.logo_path')) }}" alt="" class="{{ $img }} object-contain" width="64" height="64">
    </span>
    <span class="leading-tight">
        <span class="block text-[1.05rem] font-extrabold tracking-tight {{ $dark ? 'text-white' : 'text-clinic-900' }}">{{ config('homecare.brand_name') }}</span>
        @if ($tagline)
            <span class="block text-[10px] font-semibold uppercase tracking-[0.16em] {{ $dark ? 'text-soeradji-200' : 'text-soeradji-700' }}">{{ config('homecare.hospital_name') }}</span>
        @endif
    </span>
</span>
