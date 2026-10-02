@extends('layouts.admin')

@section('title', 'Master Layanan')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-stone-900">Layanan & Tarif</h1>
            <p class="mt-1 text-sm text-stone-500">Katalog layanan homecare yang bisa dipesan pasien.</p>
        </div>
        @can('services.manage')
            <x-button :href="route('operasional.layanan.create')" icon="plus">Tambah Layanan</x-button>
        @endcan
    </div>

    @if ($services->isEmpty())
        <x-card :padding="false">
            <x-empty-state icon="heart" title="Belum ada layanan"
                           actionHref="{{ route('operasional.layanan.create') }}" actionLabel="Tambah Layanan">
                Tambahkan layanan homecare agar pasien bisa mulai memesan.
            </x-empty-state>
        </x-card>
    @else
        <div class="bg-white rounded-2xl ring-1 ring-stone-200/70 shadow-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-bold uppercase tracking-wider text-stone-400 bg-stone-50 border-b border-stone-200">
                            <th class="px-5 py-3">Layanan</th>
                            <th class="px-5 py-3">Kategori</th>
                            <th class="px-5 py-3 text-right">Harga</th>
                            <th class="px-5 py-3 text-center">Durasi</th>
                            <th class="px-5 py-3 text-center">Dipesan</th>
                            <th class="px-5 py-3 text-center">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($services as $service)
                            <tr class="hover:bg-stone-50/70 transition">
                                <td class="px-5 py-3.5">
                                    <p class="font-semibold text-stone-900">
                                        {{ $service->name }}
                                        @if ($service->is_featured) <x-badge color="amber" class="ml-1">Unggulan</x-badge> @endif
                                    </p>
                                    <p class="font-mono text-xs text-stone-400">{{ $service->code }}</p>
                                </td>
                                <td class="px-5 py-3.5 text-stone-500 whitespace-nowrap">{{ $service->category ?: '—' }}</td>
                                <td class="px-5 py-3.5 text-right font-bold text-stone-800 whitespace-nowrap">{{ $service->formattedPrice() }}</td>
                                <td class="px-5 py-3.5 text-center text-stone-500 whitespace-nowrap">{{ $service->duration_minutes }} mnt</td>
                                <td class="px-5 py-3.5 text-center">
                                    <x-badge color="{{ $service->request_items_count > 0 ? 'teal' : 'slate' }}">{{ $service->request_items_count }}</x-badge>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <x-badge :color="$service->is_active ? 'emerald' : 'slate'">{{ $service->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('operasional.layanan.edit', $service) }}"
                                           class="rounded-lg p-2 text-stone-400 hover:text-brand-700 hover:bg-brand-50 transition" aria-label="Ubah {{ $service->name }}">
                                            <x-icon name="edit" class="w-4.5 h-4.5" />
                                        </a>
                                        <form method="POST" action="{{ route('operasional.layanan.destroy', $service) }}"
                                              onsubmit="return confirm('Hapus layanan {{ $service->name }}? Layanan dengan riwayat akan otomatis dinonaktifkan.');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="rounded-lg p-2 text-stone-400 hover:text-rose-600 hover:bg-rose-50 transition" aria-label="Hapus {{ $service->name }}">
                                                <x-icon name="trash" class="w-4.5 h-4.5" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">{{ $services->links() }}</div>
    @endif
@endsection
