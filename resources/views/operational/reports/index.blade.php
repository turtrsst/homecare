@extends('layouts.admin')

@section('title', 'Laporan')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-stone-900">Laporan Operasional</h1>
        <p class="mt-1 text-sm text-stone-500">Ringkasan pengajuan, kunjungan, dan pendapatan pada periode yang dipilih.</p>
    </div>

    {{-- Filter periode --}}
    <form method="GET" class="mb-6">
        <x-card :padding="false" class="p-4 sm:p-5">
            <div class="flex flex-col sm:flex-row sm:items-end gap-3">
                <x-input name="from" type="date" label="Dari tanggal" :value="$from->toDateString()" class="sm:w-48" />
                <x-input name="to" type="date" label="Sampai tanggal" :value="$to->toDateString()" class="sm:w-48" />
                <x-button type="submit" variant="secondary" icon="chart">Tampilkan</x-button>
                <div class="flex gap-2 sm:ml-auto">
                    @foreach ([7 => '7 hari', 30 => '30 hari', 90 => '90 hari'] as $days => $label)
                        <a href="{{ route('operasional.laporan.index', ['from' => now()->subDays($days)->toDateString(), 'to' => now()->toDateString()]) }}"
                           class="rounded-xl px-3 py-2 text-xs font-bold ring-1 transition min-h-9 inline-flex items-center
                                  {{ $from->equalTo(now()->subDays($days), 'day') && $to->isToday() ? 'bg-stone-900 text-white ring-stone-900' : 'bg-white text-stone-500 ring-stone-200 hover:ring-brand-300' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
        </x-card>
    </form>

    {{-- Angka utama --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3 sm:gap-4 mb-8">
        <x-stat-card label="Total Pengajuan" :value="$totals['requests']" icon="clipboard" color="indigo" />
        <x-stat-card label="Selesai" :value="$totals['completed']" icon="check" color="emerald" />
        <x-stat-card label="Kunjungan Selesai" :value="$totals['visits']" icon="home" color="teal" />
        <x-stat-card label="Dibatalkan" :value="$totals['cancelled']" icon="x-circle" color="amber" />
        <x-stat-card label="Ditolak" :value="$totals['rejected']" icon="alert" color="rose" />
        <x-stat-card label="Pendapatan (lunas)" icon="money" color="blue"
                     :value="'Rp '.number_format($totals['revenue'] / 1000000, 1, ',', '.').' jt'" />
    </div>

    <div class="grid lg:grid-cols-2 gap-5 items-start">
        {{-- Distribusi status --}}
        <x-card>
            <h2 class="font-extrabold text-stone-900 mb-4">Distribusi Status Pengajuan</h2>
            @php $maxStatus = max(1, $byStatus->max() ?? 1); $grandTotal = max(1, $byStatus->sum()); @endphp
            @if ($byStatus->isEmpty())
                <p class="text-sm text-stone-400">Tidak ada pengajuan pada periode ini.</p>
            @else
                <ul class="space-y-3">
                    @foreach ($statuses as $status)
                        @php $count = (int) ($byStatus[$status->value] ?? 0); @endphp
                        @if ($count > 0)
                            <li>
                                <div class="flex items-center justify-between text-sm mb-1.5">
                                    <span class="font-semibold text-stone-700">{{ $status->label() }}</span>
                                    <span class="font-bold text-stone-900">
                                        {{ $count }} <span class="text-xs font-semibold text-stone-400">({{ round($count / $grandTotal * 100) }}%)</span>
                                    </span>
                                </div>
                                <div class="h-2.5 rounded-full bg-stone-100 overflow-hidden" role="img"
                                     aria-label="{{ $status->label() }}: {{ $count }} pengajuan">
                                    <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-teal-400 transition-all"
                                         style="width: {{ round($count / $maxStatus * 100) }}%"></div>
                                </div>
                            </li>
                        @endif
                    @endforeach
                </ul>
            @endif
        </x-card>

        {{-- Layanan terlaris --}}
        <x-card>
            <h2 class="font-extrabold text-stone-900 mb-4">Layanan Paling Diminati</h2>
            @if ($byService->isEmpty())
                <p class="text-sm text-stone-400">Belum ada data layanan pada periode ini.</p>
            @else
                @php $maxService = max(1, (int) $byService->max('total')); @endphp
                <ol class="space-y-3.5">
                    @foreach ($byService as $i => $row)
                        <li>
                            <div class="flex items-center justify-between text-sm mb-1.5 gap-3">
                                <span class="font-semibold text-stone-700 truncate">
                                    <span class="text-stone-300 font-extrabold mr-1.5">{{ $i + 1 }}.</span>{{ $row->service_name }}
                                </span>
                                <span class="font-bold text-stone-900 shrink-0">{{ (int) $row->total }}×</span>
                            </div>
                            <div class="h-2.5 rounded-full bg-stone-100 overflow-hidden">
                                <div class="h-full rounded-full bg-gradient-to-r from-warm-400 to-amber-400 transition-all"
                                     style="width: {{ round((int) $row->total / $maxService * 100) }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </x-card>
    </div>

    <p class="mt-6 text-xs text-stone-400">
        Periode {{ $from->translatedFormat('j M Y') }} – {{ $to->translatedFormat('j M Y') }} ·
        pendapatan dihitung dari pengajuan berstatus pembayaran <span class="font-semibold">lunas</span> yang diajukan pada periode ini.
    </p>
@endsection
