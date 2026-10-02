@extends('layouts.guest')
@section('title', 'Halaman Tidak Ditemukan')
@section('content')
    <x-card class="text-center py-12">
        <div class="mx-auto w-16 h-16 rounded-2xl bg-stone-100 text-stone-400 flex items-center justify-center">
            <x-icon name="search" class="w-8 h-8" />
        </div>
        <h1 class="mt-5 text-2xl font-extrabold text-stone-900">Halaman tidak ditemukan</h1>
        <p class="mt-2 text-stone-500 text-sm max-w-sm mx-auto">
            Mungkin alamatnya berubah atau terjadi salah ketik. Mari kembali ke tempat yang aman.
        </p>
        <div class="mt-6 flex flex-col sm:flex-row justify-center gap-3">
            <x-button :href="rtrim(route('home'), '/').'/'">Ke Beranda</x-button>
            <x-button :href="route('services.index')" variant="secondary">Lihat Layanan</x-button>
        </div>
    </x-card>
@endsection
