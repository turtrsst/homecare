@props(['icon' => 'clipboard', 'title', 'action' => null, 'actionHref' => null, 'actionLabel' => null])

<div {{ $attributes->merge(['class' => 'text-center py-12 px-4']) }}>
    <div class="mx-auto w-16 h-16 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mb-4">
        <x-icon :name="$icon" class="w-8 h-8" />
    </div>
    <h3 class="text-lg font-bold text-stone-800">{{ $title }}</h3>
    @if ($slot->isNotEmpty())
        <p class="mt-1.5 text-stone-500 max-w-sm mx-auto">{{ $slot }}</p>
    @endif
    @if ($actionHref && $actionLabel)
        <div class="mt-5">
            <x-button :href="$actionHref" icon="plus">{{ $actionLabel }}</x-button>
        </div>
    @endif
</div>
