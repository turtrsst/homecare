@props([
    'label' => null,
    'name',
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'required' => false,
    'icon' => null,
])

@php
    $id = $attributes->get('id', $name);
    $error = $errors->first($name);
@endphp

<div class="space-y-1.5">
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-semibold text-stone-700">
            {{ $label }}
            @if ($required) <span class="text-rose-500" aria-hidden="true">*</span> @endif
        </label>
    @endif

    <div class="relative">
        @if ($icon)
            <x-icon :name="$icon" class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 pointer-events-none" />
        @endif

        <input
            type="{{ $type }}"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            @if ($required) required @endif
            aria-invalid="{{ $error ? 'true' : 'false' }}"
            @if ($error) aria-describedby="{{ $id }}-error" @endif
            {{ $attributes->merge([
                'class' => 'block w-full rounded-xl border-0 bg-white px-4 py-3 text-base text-stone-900 shadow-sm ring-1 transition placeholder:text-stone-400 focus:ring-2 focus:ring-brand-600 disabled:bg-stone-50 disabled:text-stone-500 '.
                    ($icon ? 'pl-11 ' : '').
                    ($error ? 'ring-rose-300 focus:ring-rose-500' : 'ring-stone-300 focus:ring-brand-600'),
            ]) }}
        />
    </div>

    @if ($hint && ! $error)
        <p class="text-sm text-stone-500">{{ $hint }}</p>
    @endif

    @if ($error)
        <p id="{{ $id }}-error" class="text-sm font-medium text-rose-600 flex items-start gap-1.5" role="alert">
            <x-icon name="alert" class="w-4 h-4 mt-0.5 shrink-0" />
            <span>{{ $error }}</span>
        </p>
    @endif
</div>
