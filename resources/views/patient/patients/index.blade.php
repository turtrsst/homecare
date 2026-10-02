@extends('layouts.app')

@section('title', 'Pasien Saya')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-stone-900">Pasien Saya</h1>
            <p class="mt-1 text-sm text-stone-500">Data diri sendiri & keluarga yang bisa dipesankan homecare.</p>
        </div>
        <x-button :href="route('akun.pasien.create')" icon="plus">Tambah Pasien</x-button>
    </div>

    @if ($patients->isEmpty())
        <x-card :padding="false">
            <x-empty-state icon="users" title="Belum ada data pasien"
                           actionHref="{{ route('akun.pasien.create') }}" actionLabel="Tambah Pasien">
                Tambahkan data diri sendiri atau anggota keluarga untuk mulai memesan layanan.
            </x-empty-state>
        </x-card>
    @else
        <div class="grid sm:grid-cols-2 gap-4">
            @foreach ($patients as $patient)
                <x-patient-card :patient="$patient" :addresses="$patient->addresses">
                    <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-stone-100">
                        <x-button :href="route('akun.pasien.alamat.index', $patient)" variant="soft" size="sm" icon="map-pin">
                            Alamat ({{ $patient->addresses->count() }})
                        </x-button>
                        <x-button :href="route('akun.pasien.edit', $patient)" variant="ghost" size="sm" icon="edit">
                            Ubah Data
                        </x-button>
                    </div>
                </x-patient-card>
            @endforeach
        </div>
    @endif
@endsection
