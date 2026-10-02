@extends('layouts.app')

@section('title', 'Ubah Data '.$patient->name)

@section('content')
    <nav class="text-sm mb-4">
        <a href="{{ route('akun.pasien.index') }}" class="font-semibold text-stone-400 hover:text-brand-700">← Pasien Saya</a>
    </nav>

    <div class="max-w-2xl">
        <h1 class="text-2xl font-extrabold text-stone-900">Ubah Data Pasien</h1>
        <p class="mt-1 text-sm text-stone-500">{{ $patient->name }}</p>

        <x-card class="mt-5">
            <form method="POST" action="{{ route('akun.pasien.update', $patient) }}" class="space-y-5">
                @csrf
                @method('PUT')

                @include('patient.patients._form')

                <div class="flex gap-3 pt-2">
                    <x-button :href="route('akun.pasien.index')" variant="ghost" full class="justify-center">Batal</x-button>
                    <x-button type="submit" full icon="check" class="sm:w-auto sm:flex-none">Simpan Perubahan</x-button>
                </div>
            </form>
        </x-card>

        {{-- Hapus --}}
        <div class="mt-6">
            <button type="button" x-data @click="$dispatch('open-modal', 'modal-delete')"
                    class="inline-flex items-center gap-2 text-sm font-bold text-rose-500 hover:text-rose-600 transition min-h-11">
                <x-icon name="trash" class="w-4 h-4" /> Hapus data pasien ini
            </button>
        </div>
    </div>

    <x-modal name="modal-delete" title="Hapus Data Pasien?" maxWidth="max-w-md">
        <form method="POST" action="{{ route('akun.pasien.destroy', $patient) }}">
            @csrf
            @method('DELETE')
            <p class="text-sm text-stone-500">
                Data <span class="font-bold text-stone-800">{{ $patient->name }}</span> akan dihapus permanen.
                Pasien yang punya riwayat pengajuan tidak dapat dihapus.
            </p>
            <div class="mt-5 flex gap-3">
                <x-button type="button" variant="secondary" full x-on:click="$dispatch('close-modal', 'modal-delete')">Batal</x-button>
                <x-button type="submit" variant="danger" full icon="trash">Hapus</x-button>
            </div>
        </form>
    </x-modal>
@endsection
