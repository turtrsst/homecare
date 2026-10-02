@extends('layouts.admin')

@section('title', 'Pengajuan Homecare')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-stone-900">Pengajuan Homecare</h1>
        <p class="mt-1 text-sm text-stone-500">Verifikasi, skrining, persetujuan, dan penjadwalan.</p>
    </div>

    {{-- Filter --}}
    <form method="GET" class="mb-6 space-y-3">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <x-icon name="search" class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 pointer-events-none" />
                <input type="search" name="q" value="{{ $search }}" placeholder="Cari kode, nama pasien, atau pemesan..."
                       class="w-full rounded-xl ring-1 ring-stone-300 bg-white pl-11 pr-4 py-2.5 text-sm text-stone-800 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-brand-500 min-h-11" />
            </div>
            @if ($currentStatus)
                <input type="hidden" name="status" value="{{ $currentStatus }}">
            @endif
            <x-button type="submit" variant="secondary" icon="search" class="sm:w-auto">Cari</x-button>
        </div>

        <div class="flex gap-2 overflow-x-auto pb-1 -mx-1 px-1" role="tablist" aria-label="Filter status">
            <a href="{{ route('operasional.pengajuan.index', $search ? ['q' => $search] : []) }}"
               class="shrink-0 rounded-xl px-3.5 py-2 text-xs font-bold transition min-h-9 inline-flex items-center
                  {{ $currentStatus ? 'bg-white ring-1 ring-stone-200 text-stone-500 hover:ring-stone-300' : 'bg-stone-900 text-white' }}">
                Semua
            </a>
            @foreach ($statuses as $status)
                <a href="{{ route('operasional.pengajuan.index', array_filter(['q' => $search ?: null, 'status' => $status->value])) }}"
                   class="shrink-0 rounded-xl px-3.5 py-2 text-xs font-bold transition min-h-9 inline-flex items-center gap-1.5
                      {{ $currentStatus === $status->value ? 'bg-stone-900 text-white' : 'bg-white ring-1 ring-stone-200 text-stone-500 hover:ring-stone-300' }}">
                    {{ $status->label() }}
                </a>
            @endforeach
        </div>
    </form>

    {{-- Daftar --}}
    @if ($requests->isEmpty())
        <x-card :padding="false">
            <x-empty-state icon="clipboard" title="Tidak ada pengajuan{{ $currentStatus ? ' dengan status ini' : '' }}">
                {{ $search ? 'Coba kata kunci lain.' : 'Pengajuan dari pasien akan muncul di sini.' }}
            </x-empty-state>
        </x-card>
    @else
        <div class="bg-white rounded-2xl ring-1 ring-stone-200/70 shadow-card overflow-hidden">
            {{-- Tabel desktop --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-bold uppercase tracking-wider text-stone-400 bg-stone-50 border-b border-stone-200">
                            <th class="px-5 py-3">Kode</th>
                            <th class="px-5 py-3">Pasien</th>
                            <th class="px-5 py-3">Layanan</th>
                            <th class="px-5 py-3">Diajukan</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Total</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($requests as $request)
                            <tr class="hover:bg-stone-50/70 transition">
                                <td class="px-5 py-3.5 font-mono text-xs font-bold text-stone-700 whitespace-nowrap">{{ $request->code }}</td>
                                <td class="px-5 py-3.5">
                                    <p class="font-semibold text-stone-900 whitespace-nowrap">{{ $request->patient->name }}</p>
                                    <p class="text-xs text-stone-400">oleh {{ $request->user->name }}</p>
                                </td>
                                <td class="px-5 py-3.5 text-stone-500 max-w-56">
                                    <p class="truncate">{{ $request->items->pluck('service_name')->unique()->implode(', ') }}</p>
                                </td>
                                <td class="px-5 py-3.5 text-stone-500 whitespace-nowrap">
                                    {{ $request->submitted_at?->translatedFormat('j M Y') }}
                                    <span class="block text-xs text-stone-400">{{ $request->submitted_at?->diffForHumans() }}</span>
                                </td>
                                <td class="px-5 py-3.5"><x-status-badge :status="$request->status" /></td>
                                <td class="px-5 py-3.5 text-right font-bold text-stone-800 whitespace-nowrap">{{ $request->formattedTotal() }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('operasional.pengajuan.show', $request->code) }}"
                                       class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-bold text-brand-700 hover:bg-brand-50 transition">
                                        Buka <x-icon name="chevron-right" class="w-3.5 h-3.5" />
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Kartu mobile --}}
            <div class="md:hidden divide-y divide-stone-100">
                @foreach ($requests as $request)
                    <a href="{{ route('operasional.pengajuan.show', $request->code) }}" class="block p-4 hover:bg-stone-50 transition">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-bold text-stone-900 text-sm">{{ $request->patient->name }}</p>
                                <p class="font-mono text-xs text-stone-400 mt-0.5">{{ $request->code }}</p>
                            </div>
                            <x-status-badge :status="$request->status" />
                        </div>
                        <p class="mt-2 text-xs text-stone-500 truncate">{{ $request->items->pluck('service_name')->unique()->implode(', ') }}</p>
                        <div class="mt-2 flex items-center justify-between text-xs">
                            <span class="text-stone-400">{{ $request->submitted_at?->diffForHumans() }}</span>
                            <span class="font-bold text-stone-800">{{ $request->formattedTotal() }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="mt-6">{{ $requests->links() }}</div>
    @endif
@endsection
