@extends('layouts.guest')
@section('title', 'Sesi Berakhir')
@section('content')
    <x-card class="text-center py-12">
        <div class="mx-auto w-16 h-16 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center">
            <x-icon name="clock" class="w-8 h-8" />
        </div>
        <h1 class="mt-5 text-2xl font-extrabold text-stone-900">Sesi Anda telah berakhir</h1>
        <p class="mt-2 text-stone-500 text-sm max-w-sm mx-auto">
            Demi keamanan, sesi yang terlalu lama tidak aktif akan ditutup. Silakan muat ulang halaman dan coba lagi.
        </p>
        <div class="mt-6">
            <x-button href="javascript:window.location.reload()">Muat Ulang Halaman</x-button>
        </div>
    </x-card>
@endsection
