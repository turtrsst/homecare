@extends('layouts.guest')
@section('title', 'Terjadi Kesalahan')
@section('content')
    <x-card class="text-center py-12">
        <div class="mx-auto w-16 h-16 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center">
            <x-icon name="alert" class="w-8 h-8" />
        </div>
        <h1 class="mt-5 text-2xl font-extrabold text-stone-900">Maaf, terjadi gangguan</h1>
        <p class="mt-2 text-stone-500 text-sm max-w-sm mx-auto">
            Permintaan Anda belum dapat diproses. Silakan coba beberapa saat lagi — tim kami telah menerima laporan ini.
        </p>
        <div class="mt-6 flex flex-col sm:flex-row justify-center gap-3">
            <x-button :href="rtrim(route('home'), '/').'/'">Kembali</x-button>
            <x-button :href="route('contact')" variant="secondary">Hubungi Kami</x-button>
        </div>
    </x-card>
@endsection
