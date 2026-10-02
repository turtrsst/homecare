@props(['status'])

@php
    // Menerima enum ber-label + badgeClasses (HomecareRequestStatus, AppointmentStatus, dst.)
    $label = method_exists($status, 'label') ? $status->label() : (string) $status;
    $classes = method_exists($status, 'badgeClasses') ? $status->badgeClasses() : 'bg-slate-100 text-slate-700 ring-slate-200';
    $icon = match (true) {
        method_exists($status, 'isFinal') && $status->isFinal() && $status->value === 'completed' => 'check-circle',
        method_exists($status, 'isFinal') && $status->isFinal() && in_array($status->value, ['rejected', 'cancelled']) => 'x-circle',
        in_array($status->value ?? '', ['need_information']) => 'alert',
        default => 'clock',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset '.$classes]) }}>
    <x-icon :name="$icon" class="w-3.5 h-3.5" />
    {{ $label }}
</span>
