@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <x-card class="p-6 sm:p-8">
        <h1 class="text-2xl font-extrabold text-stone-900">Selamat datang kembali 👋</h1>
        <p class="mt-1.5 text-sm text-stone-500">Masuk untuk memesan atau memantau layanan homecare Anda.</p>

        <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-5" novalidate>
            @csrf

            <x-input name="email" type="email" label="Email" icon="mail" required
                     placeholder="nama@email.com" autocomplete="email" />

            <x-input name="password" type="password" label="Kata sandi" required
                     placeholder="••••••••" autocomplete="current-password" />

            <label class="flex items-center gap-2.5 text-sm text-stone-600 cursor-pointer select-none">
                <input type="checkbox" name="remember" value="1"
                       class="rounded border-stone-300 text-brand-600 focus:ring-brand-600 w-4.5 h-4.5"
                       @checked(old('remember'))>
                Ingat saya di perangkat ini
            </label>

            <x-button type="submit" full size="lg">Masuk</x-button>
        </form>

        <p class="mt-6 text-center text-sm text-stone-500">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-bold text-brand-700 hover:text-brand-800 underline underline-offset-2">
                Daftar sekarang — gratis
            </a>
        </p>
    </x-card>

    <div class="mt-5 text-center">
        <a href="{{ rtrim(route('home'), '/') }}/" class="inline-flex items-center gap-1.5 text-sm font-semibold text-stone-400 hover:text-stone-600 transition">
            <x-icon name="chevron-left" class="w-4 h-4" /> Kembali ke beranda
        </a>
    </div>
@endsection
