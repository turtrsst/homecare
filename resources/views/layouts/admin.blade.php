@extends('layouts.admin')

@section('title', 'Dashboard Operasional')

@section('content')
    <div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-soeradji-600">Soeradji Care</p>
            <h1 class="mt-2 text-2xl font-black text-stone-900 sm:text-3xl">Dashboard Operasional</h1>
        </div>
        <p class="text-sm text-stone-500">{{ now()->translatedFormat('l, j F Y') }} · Pantau antrean, jadwal, dan pelayanan rumah sakit.</p>
    </div>

    <div class="mb-8 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        <a href="{{ route('operasional.pengajuan.index', ['status' => 'submitted']) }}" class="stat-card block">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Verifikasi</span>
                <span class="rounded-xl bg-amber-50 p-2 text-amber-600"><x-icon name="clipboard" class="h-4 w-4" /></span>
            </div>
            <p class="mt-4 text-3xl font-black text-stone-900">{{ $counts['menunggu_verifikasi'] }}</p>
            <p class="mt-1 text-sm text-stone-500">menunggu</p>
        </a>

        <a href="{{ route('operasional.pengajuan.index', ['status' => 'approved']) }}" class="stat-card block">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Tindak lanjut</span>
                <span class="rounded-xl bg-rose-50 p-2 text-rose-600"><x-icon name="alert" class="h-4 w-4" /></span>
            </div>
            <p class="mt-4 text-3xl font-black text-stone-900">{{ $counts['perlu_tindak_lanjut'] }}</p>
            <p class="mt-1 text-sm text-stone-500">perlu perhatian</p>
        </a>

        <a href="{{ route('operasional.jadwal.index') }}" class="stat-card block">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Jadwal</span>
                <span class="rounded-xl bg-soeradji-50 p-2 text-soeradji-600"><x-icon name="calendar" class="h-4 w-4" /></span>
            </div>
            <p class="mt-4 text-3xl font-black text-stone-900">{{ $counts['terjadwal_hari_ini'] }}</p>
            <p class="mt-1 text-sm text-stone-500">hari ini</p>
        </a>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Berjalan</span>
                <span class="rounded-xl bg-medical-50 p-2 text-medical-600"><x-icon name="activity" class="h-4 w-4" /></span>
            </div>
            <p class="mt-4 text-3xl font-black text-stone-900">{{ $counts['sedang_berlangsung'] }}</p>
            <p class="mt-1 text-sm text-stone-500">sedang dilayani</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Selesai</span>
                <span class="rounded-xl bg-emerald-50 p-2 text-emerald-600"><x-icon name="check" class="h-4 w-4" /></span>
            </div>
            <p class="mt-4 text-3xl font-black text-stone-900">{{ $counts['selesai_hari_ini'] }}</p>
            <p class="mt-1 text-sm text-stone-500">hari ini</p>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <section aria-labelledby="queue-heading">
            <div class="mb-3 flex items-center justify-between">
                <h2 id="queue-heading" class="text-sm font-bold uppercase tracking-[0.18em] text-stone-400">Antrean kerja</h2>
                <a href="{{ route('operasional.pengajuan.index') }}" class="text-xs font-bold text-soeradji-700 hover:text-soeradji-800">Semua →</a>
            </div>

            @if ($queue->isEmpty())
                <x-card :padding="false">
                    <x-empty-state icon="check" title="Antrean kosong 🎉">
                        Semua pengajuan sudah ditindaklanjuti.
                    </x-empty-state>
                </x-card>
            @else
                <div class="space-y-3">
                    @foreach ($queue as $request)
                        <a href="{{ route('operasional.pengajuan.show', $request->code) }}"
                           class="block rounded-[24px] border border-stone-200 bg-white p-4 shadow-soft transition hover:border-soeradji-200 hover:shadow-pop">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-base font-black text-stone-900">{{ $request->patient->name }}</p>
                                    <p class="mt-1 truncate text-xs text-stone-500">
                                        {{ $request->code }} · {{ $request->items->pluck('service_name')->unique()->implode(', ') }}
                                    </p>
                                </div>
                                <x-status-badge :status="$request->status" />
                            </div>
                            <div class="mt-3 flex items-center justify-between text-xs text-stone-500">
                                <span>{{ $request->submitted_at?->diffForHumans() }}</span>
                                <span class="font-semibold text-stone-600">oleh {{ $request->user->name }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        <section aria-labelledby="today-heading">
            <div class="mb-3 flex items-center justify-between">
                <h2 id="today-heading" class="text-sm font-bold uppercase tracking-[0.18em] text-stone-400">Kunjungan hari ini</h2>
                <a href="{{ route('operasional.jadwal.index') }}" class="text-xs font-bold text-soeradji-700 hover:text-soeradji-800">Jadwal →</a>
            </div>

            @if ($todayAppointments->isEmpty())
                <x-card :padding="false">
                    <x-empty-state icon="calendar" title="Tidak ada kunjungan hari ini">
                        Terbitkan jadwal dari pengajuan yang sudah disetujui.
                    </x-empty-state>
                </x-card>
            @else
                <div class="space-y-3">
                    @foreach ($todayAppointments as $appointment)
                        <a href="{{ route('operasional.jadwal.show', $appointment) }}"
                           class="flex items-center gap-4 rounded-[24px] border border-stone-200 bg-white p-4 shadow-soft transition hover:border-soeradji-200 hover:shadow-pop">
                            <div class="w-14 shrink-0 rounded-2xl bg-soeradji-50 p-2 text-center ring-1 ring-soeradji-100">
                                <p class="text-sm font-black text-stone-900">{{ $appointment->scheduled_at->format('H.i') }}</p>
                                <p class="text-[10px] font-bold uppercase text-stone-400">WIB</p>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-base font-black text-stone-900">{{ $appointment->request->patient->name }}</p>
                                <p class="mt-1 truncate text-xs text-stone-500">
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
