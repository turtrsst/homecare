@props(['service', 'featured' => false, 'href' => null, 'showCategory' => true])

@php $href = $href ?? route('services.show', $service->slug); @endphp

<a href="{{ $href }}" class="group block bg-white rounded-2xl ring-1 ring-stone-200/70 shadow-card overflow-hidden transition hover:shadow-pop hover:ring-brand-300 hover:-translate-y-0.5 {{ $featured ? 'ring-2 ring-warm-400/70' : '' }}">
    @if ($service->thumbnailUrl())
        <img src="{{ $service->thumbnailUrl() }}" alt="{{ $service->name }}" loading="lazy" decoding="async"
             class="w-full aspect-[10/6.5] object-cover" />
    @else
        <div class="w-full aspect-[10/6.5] bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center">
            <x-icon name="{{ $service->displayIcon() }}" class="w-14 h-14 text-white" />
        </div>
    @endif

    <div class="p-5">
        @if (($showCategory && $service->category) || $service->is_featured)
            <div class="flex items-start gap-3">
                @if ($showCategory && $service->category)
                    <span class="text-[11px] font-bold uppercase tracking-wider text-brand-600">{{ $service->category }}</span>
                @endif
                @if ($service->is_featured)
                    <x-badge color="warm" icon="sparkles" class="ml-auto shrink-0">Unggulan</x-badge>
                @endif
            </div>
        @endif

        <h3 class="mt-2 font-bold text-stone-900 group-hover:text-brand-700 transition">{{ $service->name }}</h3>

        @if ($service->short_description)
            <p class="mt-1 text-sm text-stone-500 line-clamp-2">{{ $service->short_description }}</p>
        @endif

        <div class="mt-4 flex items-center justify-between text-sm">
            <span class="font-bold text-brand-700">{{ $service->formattedPrice() }}</span>
            <span class="text-stone-400 flex items-center gap-1">
                <x-icon name="clock" class="w-4 h-4" />
                ± {{ $service->duration_minutes }} menit
            </span>
        </div>
    </div>
</a>
