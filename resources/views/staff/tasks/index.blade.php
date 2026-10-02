@extends('layouts.app')

@section('title', 'Tugas Saya')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900">Tugas Saya</h1>
        <p class="mt-1 text-stone-500">
            Halo, <span class="font-semibold text-stone-700">{{ $staff->name }}</span> — {{ $staff->profession->label() }}
            @if ($staff->specialization) · {{ $staff->specialization }} @endif
        </p>
    </div>

    {{-- Hari ini --}}
    <section class="mb-8" aria-labelledby="today-heading">
        <h2 id="today-heading" class="text-sm font-bold uppercase tracking-wider text-stone-400 mb-3 flex items-center gap-2">
            <x-icon name="calendar" class="w-4 h-4" /> Kunjungan Hari Ini
            @if ($today->isNotEmpty()) <x-badge color="teal">{{ $today->count() }}</x-badge> @endif
        </h2>

        @if ($today->isEmpty())
            <x-card :padding="false">
                <x-empty-state icon="calendar" title="Tidak ada kunjungan hari ini">
                    Nikmati waktunya — tugas berikutnya akan muncul di sini.
                </x-empty-state>
            </x-card>
        @else
            <div class="space-y-3">
                @foreach ($today as $appointment)
                    <x-appointment-card :appointment="$appointment" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- Akan datang --}}
    <section class="mb-8" aria-labelledby="upcoming-heading">
        <h2 id="upcoming-heading" class="text-sm font-bold uppercase tracking-wider text-stone-400 mb-3">Akan Datang</h2>
        @if ($upcoming->isEmpty())
            <p class="text-sm text-stone-400 bg-white rounded-2xl ring-1 ring-stone-200/70 p-4">Belum ada tugas terjadwal berikutnya.</p>
        @else
            <div class="space-y-3">
                @foreach ($upcoming->take(10) as $appointment)
                    <x-appointment-card :appointment="$appointment" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- Riwayat --}}
    @if ($history->isNotEmpty())
        <section aria-labelledby="history-heading">
            <h2 id="history-heading" class="text-sm font-bold uppercase tracking-wider text-stone-400 mb-3">Selesai Terbaru</h2>
            <div class="space-y-3">
                @foreach ($history as $appointment)
                    <a href="{{ route('tugas.show', $appointment) }}"
                       class="flex items-center gap-4 rounded-2xl bg-white ring-1 ring-stone-200/70 shadow-card p-4 transition hover:ring-brand-300">
                        <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <x-icon name="check" class="w-5 h-5" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block font-bold text-stone-800 text-sm truncate">{{ $appointment->request->patient->name }}</span>
                            <span class="block text-xs text-stone-400 mt-0.5">{{ $appointment->scheduled_at->translatedFormat('j M Y, H.i') }} · {{ $appointment->request->items->pluck('service_name')->unique()->implode(', ') }}</span>
                        </span>
                        <x-icon name="chevron-right" class="w-5 h-5 text-stone-300 shrink-0" />
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endsection
