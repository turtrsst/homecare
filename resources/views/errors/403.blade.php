@extends('layouts.guest')
@section('title', 'Akses Ditolak')
@section('content')
    <x-card class="text-center py-12">
        <div class="mx-auto w-16 h-16 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center">
            <x-icon name="shield" class="w-8 h-8" />
        </div>
        <h1 class="mt-5 text-2xl font-extrabold text-stone-900">Halaman ini bukan untuk Anda</h1>
        <p class="mt-2 text-stone-500 text-sm max-w-sm mx-auto">
            Anda tidak memiliki izin mengakses halaman tersebut. Bila ini terasa keliru, hubungi koordinator homecare.
        </p>
        <div class="mt-6 flex flex-col sm:flex-row justify-center gap-3">
            <x-button :href="auth()->check() ? route('akun.dashboard') : rtrim(route('home'), '/').'/'">Kembali</x-button>
            <x-button :href="route('contact')" variant="secondary">Hubungi Kami</x-button>
        </div>
    </x-card>
@endsection
