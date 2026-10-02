@props(['title', 'subtitle' => null, 'align' => 'left'])

<div class="{{ $align === 'center' ? 'text-center' : '' }}">
    <h2 {{ $attributes->merge(['class' => 'text-2xl sm:text-3xl font-extrabold']) }}>{{ $title }}</h2>
    @if ($subtitle)
        <p class="mt-2 text-stone-500 text-base sm:text-lg max-w-2xl {{ $align === 'center' ? 'mx-auto' : '' }}">{{ $subtitle }}</p>
    @endif
</div>
