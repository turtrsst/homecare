@props(['lines' => 3])

<div {{ $attributes->merge(['class' => 'space-y-3']) }} aria-hidden="true">
    <div class="skeleton h-5 w-2/5"></div>
    @for ($i = 0; $i < $lines; $i++)
        <div class="skeleton h-4 w-full"></div>
    @endfor
</div>
