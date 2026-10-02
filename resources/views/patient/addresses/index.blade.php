@extends('layouts.app')

@section('title', 'Alamat — '.$patient->name)

@section('content')
    <nav class="text-sm mb-4">
        <a href="{{ route('akun.pasien.index') }}" class="font-semibold text-stone-400 hover:text-brand-700">← Pasien Saya</a>
    </nav>

    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-stone-900">Alamat Kunjungan</h1>
            <p class="mt-1 text-sm text-stone-500">Pasien: <span class="font-semibold text-stone-700">{{ $patient->name }}</span></p>
        </div>
        <x-button :href="route('akun.pasien.alamat.create', $patient)" icon="plus">Tambah Alamat</x-button>
    </div>

    @if ($patient->addresses->isEmpty())
        <x-card :padding="false">
            <x-empty-state icon="map-pin" title="Belum ada alamat"
                           actionHref="{{ route('akun.pasien.alamat.create', $patient) }}" actionLabel="Tambah Alamat">
                Tambahkan alamat agar petugas tahu harus ke mana.
            </x-empty-state>
        </x-card>
    @else
        <div class="grid sm:grid-cols-2 gap-4">
            @foreach ($patient->addresses as $address)
                <x-card>
                    <div class="flex items-start gap-3.5">
                        <span class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                            <x-icon name="map-pin" class="w-5 h-5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-bold text-stone-900">{{ $address->label ?: 'Alamat' }}</p>
                                @if ($address->is_primary) <x-badge color="teal">Utama</x-badge> @endif
                            </div>
                            <p class="mt-1 text-sm text-stone-500 leading-relaxed">{{ $address->oneLine() }}</p>
                            <p class="mt-1 text-xs text-stone-400">a.n. {{ $address->recipient_name }} · {{ $address->phone }}</p>
                            @if ($address->notes)
                                <p class="mt-2 text-xs text-stone-500 bg-stone-50 rounded-xl px-3 py-2">📝 {{ $address->notes }}</p>
                            @endif
                            <div class="mt-3 flex flex-wrap gap-2">
                                <x-button :href="route('akun.pasien.alamat.edit', [$patient, $address])" variant="soft" size="sm" icon="edit">Ubah</x-button>
                                <form method="POST" action="{{ route('akun.pasien.alamat.destroy', [$patient, $address]) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-sm font-semibold text-rose-500 hover:bg-rose-50 transition min-h-9">
                                        <x-icon name="trash" class="w-4 h-4" /> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>
    @endif
@endsection
