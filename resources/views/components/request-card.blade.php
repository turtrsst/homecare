@props(['request', 'showTimelineHint' => false, 'href' => null])

<a href="{{ $href ?? route('akun.pengajuan.show', $request->code) }}" class="block bg-white rounded-2xl ring-1 ring-stone-200/70 shadow-card p-5 transition hover:shadow-pop hover:ring-brand-300 group">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <h3 class="font-bold text-stone-900 group-hover:text-brand-700 transition">
                    {{ $request->items->pluck('service_name')->unique()->take(2)->implode(', ') ?: 'Homecare' }}
                </h3>
                @if ($request->items->count() > 2)
                    <span class="text-xs text-stone-400">+{{ $request->items->count() - 2 }} layanan</span>
                @endif
            </div>
            <p class="mt-1 text-sm text-stone-500">
                Untuk <span class="font-semibold text-stone-700">{{ $request->patient->name }}</span>
                @if ($request->appointment)
                    · {{ $request->appointment->scheduled_at->translatedFormat('j M Y, H.i') }}
                @elseif ($request->preferred_date)
                    · permintaan {{ $request->preferred_date->translatedFormat('j M Y') }}
                @endif
            </p>
            <p class="mt-0.5 text-xs text-stone-400">{{ $request->code }}</p>
        </div>
        <div class="flex flex-col items-end gap-2 shrink-0">
            <x-status-badge :status="$request->status" />
            <x-icon name="chevron-right" class="w-5 h-5 text-stone-300 group-hover:text-brand-500 transition" />
        </div>
    </div>
</a>
