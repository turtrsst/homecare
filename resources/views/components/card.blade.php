@props(['padding' => true, 'hover' => false])

<div {{ $attributes->merge([
    'class' => 'bg-white rounded-2xl ring-1 ring-stone-200/70 shadow-card '.
        ($padding ? 'p-5 sm:p-6' : '').
        ($hover ? ' transition hover:shadow-pop hover:ring-brand-200' : ''),
]) }}>
    {{ $slot }}
</div>
