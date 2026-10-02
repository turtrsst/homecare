@extends('layouts.admin')

@section('title', 'Dashboard Operasional')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900">Dashboard Operasional</h1>
        <p class="mt-1 text-stone-500">{{ now()->translatedFormat('l, j F Y') }} · Pantau antrean & kunjungan hari ini.</p>
    </div>

    {{-- Kartu ringkasan --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4 mb-8">
        <a href="{{ route('operasional.pengajuan.index', ['status' => 'submitted']) }}">
            <x-stat-card label="Menunggu Verifikasi" :value="$counts['menunggu_verifikasi']" icon="clipboard" color="amber" />
        </a>
        <a href="{{ route('operasional.pengajuan.index', ['status' => 'approved']) }}">
            <x-stat-card label="Perlu Tindak Lanjut" :value="$counts['perlu_tindak_lanjut']" icon="alert" color="rose" />
        </a>
        <a href="{{ route('operasional.jadwal.index') }}">
            <x-stat-card label="Terjadwal Hari Ini" :value="$counts['terjadwal_hari_ini']" icon="calendar" color="blue" />
        </a>
        <x-stat-card label="Sedang Berlangsung" :value="$counts['sedang_berlangsung']" icon="activity" color="teal" />
        <x-stat-card label="Selesai Hari Ini" :value="$counts['selesai_hari_ini']" icon="check" color="emerald" />
    </div>

    <div class="grid lg:grid-cols-2 gap-5 items-start">
        {{-- Antrean kerja --}}
        <section aria-labelledby="queue-heading">
            <div class="flex items-center justify-between mb-3">
                <h2 id="queue-heading" class="text-sm font-bold uppercase tracking-wider text-stone-400">Antrean Kerja</h2>
                <a href="{{ route('operasional.pengajuan.index') }}" class="text-xs font-bold text-brand-700 hover:text-brand-800">Semua →</a>
            </div>

            @if ($queue->isEmpty())
                <x-card :padding="false">
                    <x-empty-state icon="check" title="Antrean kosong 🎉">
                        Semua pengajuan sudah ditindaklanjuti.
                    </x-empty-state>
                </x-card>
            @else
                <div class="space-y-2.5">
                    @foreach ($queue as $request)
                        <a href="{{ route('operasional.pengajuan.show', $request->code) }}"
                           class="block bg-white rounded-2xl ring-1 ring-stone-200/70 shadow-card p-4 transition hover:ring-brand-300 hover:shadow-pop">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-bold text-stone-900 text-sm truncate">{{ $request->patient->name }}</p>
                                    <p class="text-xs text-stone-400 mt-0.5 truncate">
                                        {{ $request->code }} · {{ $request->items->pluck('service_name')->unique()->implode(', ') }}
                                    </p>
                                </div>
                                <x-status-badge :status="$request->status" />
                            </div>
                            <div class="mt-2.5 flex items-center justify-between text-xs text-stone-400">
                                <span>{{ $request->submitted_at?->diffForHumans() }}</span>
                                <span class="font-semibold text-stone-500">oleh {{ $request->user->name }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Kunjungan hari ini --}}
        <section aria-labelledby="today-heading">
            <div class="flex items-center justify-between mb-3">
                <h2 id="today-heading" class="text-sm font-bold uppercase tracking-wider text-stone-400">Kunjungan Hari Ini</h2>
                <a href="{{ route('operasional.jadwal.index') }}" class="text-xs font-bold text-brand-700 hover:text-brand-800">Jadwal →</a>
            </div>

            @if ($todayAppointments->isEmpty())
                <x-card :padding="false">
                    <x-empty-state icon="calendar" title="Tidak ada kunjungan hari ini">
                        Terbitkan jadwal dari pengajuan yang sudah disetujui.
                    </x-empty-state>
                </x-card>
            @else
                <div class="space-y-2.5">
                    @foreach ($todayAppointments as $appointment)
                        <a href="{{ route('operasional.jadwal.show', $appointment) }}"
                           class="flex items-center gap-4 bg-white rounded-2xl ring-1 ring-stone-200/70 shadow-card p-4 transition hover:ring-brand-300">
                            <div class="w-14 text-center shrink-0">
                                <p class="text-sm font-extrabold text-stone-900">{{ $appointment->scheduled_at->format('H.i') }}</p>
                                <p class="text-[10px] font-semibold text-stone-400 uppercase">WIB</p>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-stone-900 text-sm truncate">{{ $appointment->request->patient->name }}</p>
                                <p class="text-xs text-stone-400 mt-0.5 truncate">
                                    {{ $appointment->request->code }}
                                    @if ($appointment->assignments->isNotEmpty())
                                        · {{ $appointment->assignments->whereNotIn('status', ['cancelled'])->pluck('staff.name')->implode(', ') }}
                                    @endif
                                </p>
                            </div>
                            <x-status-badge :status="$appointment->status" />
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
