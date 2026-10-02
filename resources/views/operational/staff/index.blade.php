@extends('layouts.admin')

@section('title', 'Tenaga Kesehatan')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-stone-900">Tenaga Kesehatan</h1>
            <p class="mt-1 text-sm text-stone-500">Tim petugas homecare yang dapat ditugaskan ke kunjungan.</p>
        </div>
        @can('staff.manage')
            <x-button :href="route('operasional.petugas.create')" icon="plus">Tambah Petugas</x-button>
        @endcan
    </div>

    @if ($staff->isEmpty())
        <x-card :padding="false">
            <x-empty-state icon="stethoscope" title="Belum ada tenaga kesehatan"
                           actionHref="{{ route('operasional.petugas.create') }}" actionLabel="Tambah Petugas">
                Tambahkan dokter, perawat, bidan, atau fisioterapis untuk mulai menjadwalkan kunjungan.
            </x-empty-state>
        </x-card>
    @else
        <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach ($staff as $member)
                <x-card>
                    <div class="flex items-start gap-3.5">
                        <span class="w-12 h-12 rounded-2xl bg-brand-100 text-brand-700 flex items-center justify-center text-lg font-extrabold shrink-0">
                            {{ mb_substr($member->name, 0, 1) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="font-bold text-stone-900 truncate">{{ $member->name }}</p>
                                <x-badge :color="$member->is_active ? 'emerald' : 'slate'">{{ $member->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                            </div>
                            <p class="text-sm text-stone-500">
                                {{ $member->profession->label() }}
                                @if ($member->specialization) · {{ $member->specialization }} @endif
                            </p>
                            <p class="mt-1 text-xs text-stone-400">
                                {{ $member->assignments_count }} penugasan
                                @if ($member->license_number) · STR {{ $member->license_number }} @endif
                            </p>
                            @if ($member->phone)
                                <a href="tel:{{ $member->phone }}" class="mt-1 block text-xs font-semibold text-brand-700 hover:underline">{{ $member->phone }}</a>
                            @endif
                        </div>
                    </div>
                    <div class="mt-4 pt-3.5 border-t border-stone-100 flex gap-2">
                        <x-button :href="route('operasional.petugas.edit', $member)" variant="soft" size="sm" icon="edit">Ubah</x-button>
                        <form method="POST" action="{{ route('operasional.petugas.destroy', $member) }}"
                              onsubmit="return confirm('Hapus data {{ $member->name }}? Petugas dengan tugas berjalan akan otomatis dinonaktifkan.');">
                            @csrf @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-sm font-semibold text-rose-500 hover:bg-rose-50 transition min-h-9">
                                <x-icon name="trash" class="w-4 h-4" /> Hapus
                            </button>
                        </form>
                    </div>
                </x-card>
            @endforeach
        </div>

        <div class="mt-6">{{ $staff->links() }}</div>
    @endif
@endsection
