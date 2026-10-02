@props(['appointment', 'showRequest' => true])

<div class="bg-white rounded-2xl ring-1 ring-stone-200/70 shadow-card p-5">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex flex-col items-center justify-center shrink-0">
                <span class="text-[10px] font-bold uppercase leading-none">{{ $appointment->scheduled_at->translatedFormat('M') }}</span>
                <span class="text-lg font-extrabold leading-tight">{{ $appointment->scheduled_at->format('j') }}</span>
            </div>
            <div>
                <p class="font-bold text-stone-900">
                    {{ $appointment->scheduled_at->translatedFormat('H.i') }} WIB
                    @if ($appointment->scheduled_at->isToday()) <x-badge color="blue" class="ml-1">Hari ini</x-badge> @endif
                </p>
                <p class="text-sm text-stone-500">
                    @if ($showRequest && $appointment->relationLoaded('request'))
                        {{ $appointment->request->patient->name ?? '—' }} · {{ $appointment->request->code }}
                    @else
                        Perkiraan {{ $appointment->estimated_duration_minutes }} menit
                    @endif
                </p>
            </div>
        </div>
        <x-status-badge :status="$appointment->status" />
    </div>

    @if ($appointment->relationLoaded('assignments') && $appointment->assignments->isNotEmpty())
        <div class="mt-4 pt-4 border-t border-stone-100 flex flex-wrap gap-2">
            @foreach ($appointment->assignments->whereNotIn('status', ['cancelled']) as $assignment)
                <span class="inline-flex items-center gap-2 rounded-xl bg-stone-50 ring-1 ring-stone-200/70 px-3 py-2 text-sm">
                    <x-icon name="stethoscope" class="w-4 h-4 text-brand-600" />
                    <span class="font-semibold text-stone-700">{{ $assignment->staff->name }}</span>
                    <span class="text-stone-400">· {{ $assignment->staff->profession->label() }}</span>
                </span>
            @endforeach
        </div>
    @endif
</div>
