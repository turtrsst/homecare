@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <div class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.22em] text-soeradji-600">Selamat datang</p>
        <h1 class="mt-2 text-3xl font-black text-clinic-900">Masuk ke Soeradji Care</h1>
        <p class="mt-2 text-sm text-clinic-600">Masuk untuk memesan layanan atau memantau status kunjungan Anda.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-5" novalidate>
        @csrf

        <div>
            <label for="email" class="mb-2 block text-sm font-bold text-clinic-700">Email</label>
            <div class="relative">
                <x-icon name="mail" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-clinic-400" />
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="nama@email.com"
                       class="w-full rounded-2xl border border-stone-200 bg-stone-50 py-3 pl-10 pr-3 text-sm text-clinic-900 placeholder:text-clinic-400 focus:border-soeradji-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-soeradji-100">
            </div>
            @error('email')
                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="mb-2 flex items-center justify-between gap-2">
                <label for="password" class="text-sm font-bold text-clinic-700">Kata sandi</label>
                <a href="{{ route('password.request') }}" class="text-xs font-semibold text-soeradji-700 hover:text-soeradji-800">Lupa kata sandi?</a>
            </div>
            <div class="relative">
                <x-icon name="lock" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-clinic-400" />
                <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••"
                       class="w-full rounded-2xl border border-stone-200 bg-stone-50 py-3 pl-10 pr-3 text-sm text-clinic-900 placeholder:text-clinic-400 focus:border-soeradji-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-soeradji-100">
            </div>
            @error('password')
                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-3 text-sm text-clinic-600">
            <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-stone-300 text-soeradji-600 focus:ring-soeradji-500" @checked(old('remember'))>
            Ingat saya di perangkat ini
        </label>

        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-soeradji-600 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-soeradji-600/20 transition hover:bg-soeradji-700">
            <x-icon name="log-in" class="h-4 w-4" /> Masuk
        </button>
    </form>

    <div class="mt-6 text-center text-sm text-clinic-600">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-bold text-soeradji-700 underline underline-offset-2 hover:text-soeradji-800">Daftar sekarang</a>
    </div>

    <div class="mt-5 text-center">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-clinic-500 hover:text-clinic-700">
            <x-icon name="chevron-left" class="h-4 w-4" /> Kembali ke beranda
        </a>
    </div>
@endsection
