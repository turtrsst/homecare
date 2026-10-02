@extends('layouts.admin')

@section('title', 'Jadwal Kunjungan')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-stone-900">Jadwal Kunjungan</h1>
        <p class="mt-1 text-sm text-stone-500">Monitor pelaksanaan kunjungan homecare per tanggal.</p>
    </div>

    {{-- Filter tanggal & status --}}
    <form method="GET" class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-end gap-3">
            <x-input name="tanggal" type="date" label="Tanggal" :value="$date->toDateString()" class="sm:w-52" />
            <div class="sm:w-56">
                <x-select name="status" label="Status" placeholder="Semua status"
                          :options="$statuses->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all()"
                          :value="$currentStatus" />
            </div>
            <div class="flex gap-2">
                <x-button type="submit" variant="secondary" icon="search">Terapkan</x-button>
                <a href="{{ route('operasional.jadwal.index') }}"
                   class="inline-flex items-center rounded-xl px-3.5 py-2.5 text-sm font-bold text-stone-500 hover:bg-stone-100 transition min-h-11">
                    Reset
                </a>
            </div>
        </div>
        {{-- Navigasi hari --}}
        <div class="mt-3 flex items-center gap-2 text-sm">
            <a href="{{ route('operasional.jadwal.index', ['tanggal' => $date->copy()->subDay()->toDateString(), 'status' => $currentStatus]) }}"
               class="rounded-xl ring-1 ring-stone-200 bg-white p-2.5 text-stone-500 hover:ring-brand-300 hover:text-brand-700 transition" aria-label="Hari sebelumnya">
                <x-icon name="chevron-left" class="w-4.5 h-4.5" />
            </a>
            <a href="{{ route('operasional.jadwal.index', ['status' => $currentStatus]) }}"
               class="rounded-xl px-3.5 py-2 font-bold {{ $date->isToday() ? 'bg-stone-900 text-white' : 'ring-1 ring-stone-200 bg-white text-stone-600 hover:ring-brand-300' }} transition">
                Hari ini
            </a>
            <a href="{{ route('operasional.jadwal.index', ['tanggal' => $date->copy()->addDay()->toDateString(), 'status' => $currentStatus]) }}"
               class="rounded-xl ring-1 ring-stone-200 bg-white p-2.5 text-stone-500 hover:ring-brand-300 hover:text-brand-700 transition" aria-label="Hari berikutnya">
                <x-icon name="chevron-right" class="w-4.5 h-4.5" />
            </a>
            <span class="ml-2 font-bold text-stone-700">{{ $date->translatedFormat('l, j F Y') }}</span>
        </div>
    </form>

    @if ($appointments->isEmpty())
        <x-card :padding="false">
            <x-empty-state icon="calendar" title="Tidak ada kunjungan pada tanggal ini"
                           actionHref="{{ route('operasional.pengajuan.index', ['status' => 'approved']) }}" actionLabel="Lihat pengajuan siap dijadwalkan">
                Coba geser tanggal atau terbitkan jadwal dari pengajuan yang sudah disetujui.
            </x-empty-state>
        </x-card>
    @else
        <div class="bg-white rounded-2xl ring-1 ring-stone-200/70 shadow-card overflow-hidden">
            {{-- Desktop --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-bold uppercase tracking-wider text-stone-400 bg-stone-50 border-b border-stone-200">
                            <th class="px-5 py-3">Jam</th>
                            <th class="px-5 py-3">Pasien / Kode</th>
                            <th class="px-5 py-3">Layanan</th>
                            <th class="px-5 py-3">Petugas</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($appointments as $appointment)
                            <tr class="hover:bg-stone-50/70 transition">
                                <td class="px-5 py-3.5 font-extrabold text-stone-900 whitespace-nowrap">{{ $appointment->scheduled_at->format('H.i') }}</td>
                                <td class="px-5 py-3.5">
                                    <p class="font-semibold text-stone-900 whitespace-nowrap">{{ $appointment->request->patient->name }}</p>
                                    <p class="font-mono text-xs text-stone-400">{{ $appointment->request->code }}</p>
                                </td>
                                <td class="px-5 py-3.5 text-stone-500 max-w-48">
                                    <p class="truncate">{{ $appointment->request->items->pluck('service_name')->unique()->implode(', ') }}</p>
                                </td>
                                <td class="px-5 py-3.5 text-stone-500 max-w-48">
                                    <p class="truncate">{{ $appointment->assignments->whereNotIn('status', ['cancelled'])->pluck('staff.name')->implode(', ') ?: '—' }}</p>
                                </td>
                                <td class="px-5 py-3.5"><x-status-badge :status="$appointment->status" /></td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('operasional.jadwal.show', $appointment) }}"
                                       class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-bold text-brand-700 hover:bg-brand-50 transition">
                                        Monitor <x-icon name="chevron-right" class="w-3.5 h-3.5" />
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile --}}
            <div class="md:hidden divide-y divide-stone-100">
                @foreach ($appointments as $appointment)
                    <a href="{{ route('operasional.jadwal.show', $appointment) }}" class="flex items-center gap-4 p-4 hover:bg-stone-50 transition">
                        <div class="w-14 text-center shrink-0">
                            <p class="text-sm font-extrabold text-stone-900">{{ $appointment->scheduled_at->format('H.i') }}</p>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-stone-900 text-sm truncate">{{ $appointment->request->patient->name }}</p>
                            <p class="text-xs text-stone-400 mt-0.5 truncate">
                                {{ $appointment->assignments->whereNotIn('status', ['cancelled'])->pluck('staff.name')->implode(', ') ?: 'Tanpa petugas' }}
                            </p>
                        </div>
                        <x-status-badge :status="$appointment->status" />
                    </a>
                @endforeach
            </div>
        </div>

        <div class="mt-6">{{ $appointments->links() }}</div>
    @endif
@endsection
