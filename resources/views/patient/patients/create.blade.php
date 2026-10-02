@extends('layouts.app')

@section('title', 'Tambah Pasien')

@section('content')
    <nav class="text-sm mb-4">
        <a href="{{ $returnTo ?: route('akun.pasien.index') }}" class="font-semibold text-stone-400 hover:text-brand-700">← Kembali</a>
    </nav>

    <div class="max-w-2xl">
        <h1 class="text-2xl font-extrabold text-stone-900">Tambah Pasien</h1>
        <p class="mt-1 text-sm text-stone-500">Data ini dipakai untuk pemesanan homecare & komunikasi dengan petugas.</p>

        <x-card class="mt-5">
            <form method="POST" action="{{ route('akun.pasien.store') }}" class="space-y-5">
                @csrf
                @if ($returnTo) <input type="hidden" name="return_to" value="{{ $returnTo }}"> @endif

                @include('patient.patients._form')

                <div class="flex gap-3 pt-2">
                    <x-button :href="$returnTo ?: route('akun.pasien.index')" variant="ghost" full class="justify-center">Batal</x-button>
                    <x-button type="submit" full icon="check" class="sm:w-auto sm:flex-none">Simpan Pasien</x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection
