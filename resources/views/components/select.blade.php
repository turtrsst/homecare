@props([
    'label' => null,
    'name',
    'value' => null,
    'hint' => null,
    'required' => false,
    'options' => [],
    'placeholder' => null,
])

@php
    $id = $attributes->get('id', $name);
    $error = $errors->first($name);
    $selected = old($name, $value);
@endphp

<div class="space-y-1.5">
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-semibold text-stone-700">
            {{ $label }}
            @if ($required) <span class="text-rose-500" aria-hidden="true">*</span> @endif
        </label>
    @endif

    <div class="relative">
        <select
            id="{{ $id }}"
            name="{{ $name }}"
            @if ($required) required @endif
            aria-invalid="{{ $error ? 'true' : 'false' }}"
            {{ $attributes->merge([
                'class' => 'block w-full appearance-none rounded-xl border-0 bg-white px-4 py-3 pr-11 text-base text-stone-900 shadow-sm ring-1 transition focus:ring-2 '.
                    ($error ? 'ring-rose-300 focus:ring-rose-500' : 'ring-stone-300 focus:ring-brand-600'),
            ]) }}
        >
            @if ($placeholder !== null)
                <option value="">{{ $placeholder }}</option>
            @endif
            @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected((string) $selected === (string) $optionValue)>
                    {{ $optionLabel }}
                </option>
            @endforeach
            {{ $slot }}
        </select>

        <x-icon name="chevron-down" class="w-5 h-5 absolute right-3.5 top-1/2 -translate-y-1/2 text-stone-400 pointer-events-none" />
    </div>

    @if ($hint && ! $error)
        <p class="text-sm text-stone-500">{{ $hint }}</p>
    @endif

    @if ($error)
        <p class="text-sm font-medium text-rose-600 flex items-start gap-1.5" role="alert">
            <x-icon name="alert" class="w-4 h-4 mt-0.5 shrink-0" />
            <span>{{ $error }}</span>
        </p>
    @endif
</div>
