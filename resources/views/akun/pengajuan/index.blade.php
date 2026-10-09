@extends('layouts.app')

@section('title', 'Riwayat Pesanan')

@section('content')
    <div class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-soeradji-600">Pesanan saya</p>
        <h1 class="mt-2 text-3xl font-black text-clinic-900 sm:text-4xl">Riwayat dan status pesanan</h1>
        <p class="mt-2 text-base text-clinic-600">Pantau semua pesanan layanan homecare Anda dari sini.</p>
    </div>

    <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-2">
            @php
                $statuses = [
                    'all' => ['label' => 'Semua', 'icon' => 'list', 'color' => 'stone'],
                    'pending' => ['label' => 'Menunggu', 'icon' => 'clock', 'color' => 'amber'],
                    'approved' => ['label' => 'Disetujui', 'icon' => 'check-circle', 'color' => 'green'],
                    'ongoing' => ['label' => 'Berlangsung', 'icon' => 'activity', 'color' => 'blue'],
                    'completed' => ['label' => 'Selesai', 'icon' => 'check-circle2', 'color' => 'medical'],
                    'cancelled' => ['label' => 'Dibatalkan', 'icon' => 'x-circle', 'color' => 'rose'],
                ];
            @endphp
            @foreach ($statuses as $key => $status)
                <button type="button" x-data="{}" x-on:click="$dispatch('filter-status', { status: '{{ $key }}' })"
                        class="inline-flex items-center gap-2 rounded-full border px-3.5 py-2 text-sm font-semibold transition"
                        :class="'{{ $key }}' === $wire.selectedStatus
                            ? 'border-soeradji-300 bg-soeradji-50 text-soeradji-700'
                            : 'border-stone-200 bg-white text-stone-600 hover:border-stone-300 hover:bg-stone-50'">
                    <x-icon :name="$status['icon']" class="h-4 w-4" />
                    {{ $status['label'] }}
                </button>
            @endforeach
        </div>

        <a href="{{ route('akun.pesan.step', 'pasien') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-soeradji-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-soeradji-700">
            <x-icon name="plus" class="h-4 w-4" />
            Pesan baru
        </a>
    </div>

    @if ($requests->count() > 0)
        <div class="space-y-4">
            @foreach ($requests as $request)
                <a href="{{ route('akun.pengajuan.show', $request) }}"
                   class="group block overflow-hidden rounded-[24px] border border-stone-200 bg-white shadow-card transition hover:border-soeradji-300 hover:shadow-pop">
                    <div class="p-4 sm:p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-soeradji-50 text-soeradji-600 ring-1 ring-soeradji-100">
                                        @php
                                            $icon = match($request->status->value) {
                                                'pending' => 'clock',
                                                'approved' => 'check-circle',
                                                'ongoing' => 'activity',
                                                'completed' => 'check-circle2',
                                                'cancelled' => 'x-circle',
                                                default => 'clipboard'
                                            };
                                        @endphp
                                        <x-icon :name="$icon" class="h-6 w-6" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-clinic-500">{{ $request->code }}</p>
                                        <h3 class="mt-1 text-lg font-black text-clinic-900">
                                            {{ $request->items->pluck('service_name')->unique()->join(', ') ?: 'Layanan Homecare' }}
                                        </h3>
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-3 text-sm text-clinic-600">
                                    <span class="flex items-center gap-1.5">
                                        <x-icon name="calendar" class="h-4 w-4 text-soeradji-600" />
                                        {{ $request->visit_date?->translatedFormat('d M Y') ?? 'Belum dijadwalkan' }}
                                    </span>
                                    @if ($request->visit_time)
                                        <span class="flex items-center gap-1.5">
                                            <x-icon name="clock" class="h-4 w-4 text-soeradji-600" />
                                            {{ $request->visit_time }}
                                        </span>
                                    @endif
                                    <span class="flex items-center gap-1.5">
                                        <x-icon name="map-pin" class="h-4 w-4 text-soeradji-600" />
                                        {{ $request->address ?? 'Alamat tidak tersedia' }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex flex-col items-end gap-3">
                                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-sm font-bold"
                                      :class="{
                                          'border-amber-200 bg-amber-50 text-amber-700': '{{ $request->status->value }}' === 'pending',
                                          'border-blue-200 bg-blue-50 text-blue-700': '{{ $request->status->value }}' === 'approved',
                                          'border-indigo-200 bg-indigo-50 text-indigo-700': '{{ $request->status->value }}' === 'ongoing',
                                          'border-green-200 bg-green-50 text-green-700': '{{ $request->status->value }}' === 'completed',
                                          'border-rose-200 bg-rose-50 text-rose-700': '{{ $request->status->value }}' === 'cancelled',
                                      }">
                                    <span class="h-2 w-2 rounded-full" :class="{
                                        'bg-amber-600': '{{ $request->status->value }}' === 'pending',
                                        'bg-blue-600': '{{ $request->status->value }}' === 'approved',
                                        'bg-indigo-600': '{{ $request->status->value }}' === 'ongoing',
                                        'bg-green-600': '{{ $request->status->value }}' === 'completed',
                                        'bg-rose-600': '{{ $request->status->value }}' === 'cancelled',
                                    }"></span>
                                    {{ $request->status->label() }}
                                </span>

                                <div class="rounded-2xl bg-clinic-50 px-3.5 py-2">
                                    <p class="text-xs text-clinic-500">Total pesanan</p>
                                    <p class="mt-1 text-lg font-black text-clinic-900">Rp {{ number_format($request->items->sum('total_price') ?? 0, 0, ',', '.') }}</p>
                                </div>

                                <span class="text-xs font-semibold text-soeradji-700 group-hover:underline">Lihat detail →</span>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center justify-center rounded-[30px] border border-dashed border-stone-300 bg-stone-50 py-12 text-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-stone-200 text-stone-500">
                <x-icon name="inbox" class="h-8 w-8" />
            </div>
            <h2 class="mt-4 text-xl font-black text-clinic-900">Belum ada pesanan</h2>
            <p class="mt-2 max-w-sm text-sm text-clinic-600">Anda belum membuat pesanan layanan homecare. Mulai pesan sekarang dan dapatkan layanan terbaik dari kami.</p>
            <a href="{{ route('akun.pesan.step', 'pasien') }}" class="mt-6 inline-flex items-center gap-2 rounded-2xl bg-soeradji-600 px-5 py-3 text-sm font-bold text-white hover:bg-soeradji-700">
                <x-icon name="plus" class="h-4 w-4" />
                Buat pesanan pertama
            </a>
        </div>
    @endif
@endsection
