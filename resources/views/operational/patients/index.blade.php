@extends('layouts.admin')

@section('title', 'Data Pasien')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-stone-900">Data Pasien</h1>
        <p class="mt-1 text-sm text-stone-500">Seluruh pasien yang terdaftar di layanan homecare.</p>
    </div>

    <form method="GET" class="mb-6 flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <x-icon name="search" class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 pointer-events-none" />
            <input type="search" name="q" value="{{ $search }}" placeholder="Cari nama atau NIK..."
                   class="w-full rounded-xl ring-1 ring-stone-300 bg-white pl-11 pr-4 py-2.5 text-sm text-stone-800 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-brand-500 min-h-11" />
        </div>
        <x-button type="submit" variant="secondary" icon="search" class="sm:w-auto">Cari</x-button>
    </form>

    @if ($patients->isEmpty())
        <x-card :padding="false">
            <x-empty-state icon="users" title="Pasien tidak ditemukan">
                {{ $search ? 'Coba kata kunci lain.' : 'Belum ada pasien terdaftar.' }}
            </x-empty-state>
        </x-card>
    @else
        <div class="bg-white rounded-2xl ring-1 ring-stone-200/70 shadow-card overflow-hidden">
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-bold uppercase tracking-wider text-stone-400 bg-stone-50 border-b border-stone-200">
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3">Usia / JK</th>
                            <th class="px-5 py-3">Pendaftar</th>
                            <th class="px-5 py-3">Alamat</th>
                            <th class="px-5 py-3 text-center">Pengajuan</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($patients as $patient)
                            <tr class="hover:bg-stone-50/70 transition">
                                <td class="px-5 py-3.5">
                                    <p class="font-semibold text-stone-900">{{ $patient->name }}</p>
                                    @if ($patient->nik) <p class="font-mono text-xs text-stone-400">{{ $patient->nik }}</p> @endif
                                </td>
                                <td class="px-5 py-3.5 text-stone-500 whitespace-nowrap">
                                    {{ $patient->age() ? $patient->age().' th' : '—' }} · {{ $patient->gender?->label() ?? '—' }}
                                </td>
                                <td class="px-5 py-3.5 text-stone-500">{{ $patient->user->name }}</td>
                                <td class="px-5 py-3.5 text-stone-500 max-w-64">
                                    <p class="truncate">{{ $patient->addresses->firstWhere('is_primary', true)?->oneLine() ?? $patient->addresses->first()?->oneLine() ?? '—' }}</p>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <x-badge color="{{ $patient->homecare_requests_count > 0 ? 'teal' : 'stone' }}">{{ $patient->homecare_requests_count }}</x-badge>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('operasional.pasien.show', $patient) }}"
                                       class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-bold text-brand-700 hover:bg-brand-50 transition">
                                        Buka <x-icon name="chevron-right" class="w-3.5 h-3.5" />
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="md:hidden divide-y divide-stone-100">
                @foreach ($patients as $patient)
                    <a href="{{ route('operasional.pasien.show', $patient) }}" class="flex items-center gap-3.5 p-4 hover:bg-stone-50 transition">
                        <span class="w-10 h-10 rounded-full bg-stone-100 text-stone-500 flex items-center justify-center font-bold shrink-0">
                            {{ mb_substr($patient->name, 0, 1) }}
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block font-bold text-stone-900 text-sm truncate">{{ $patient->name }}</span>
                            <span class="block text-xs text-stone-400">{{ $patient->age() ? $patient->age().' th · ' : '' }}{{ $patient->homecare_requests_count }} pengajuan</span>
                        </span>
                        <x-icon name="chevron-right" class="w-5 h-5 text-stone-300 shrink-0" />
                    </a>
                @endforeach
            </div>
        </div>

        <div class="mt-6">{{ $patients->links() }}</div>
    @endif
@endsection
