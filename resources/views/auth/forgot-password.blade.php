@extends('layouts.guest')

@section('title', 'Lupa kata sandi')

@section('content')
    <div class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.22em] text-soeradji-600">Akun Anda</p>
        <h1 class="mt-2 text-3xl font-extrabold text-clinic-900">Lupa kata sandi?</h1>
        <p class="mt-2 text-sm leading-relaxed text-clinic-600">Masukkan email yang terdaftar. Kami akan mengirimkan tautan untuk membuat kata sandi baru.</p>
    </div>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5" novalidate>
        @csrf
        <div>
            <label for="email" class="mb-2 block text-sm font-bold text-clinic-700">Email</label>
            <div class="relative">
                <x-icon name="mail" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-clinic-400" />
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="nama@email.com"
                       class="w-full rounded-2xl border border-clinic-200 bg-clinic-50 py-3 pl-10 pr-3 text-sm text-clinic-900 placeholder:text-clinic-400 focus:border-soeradji-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-soeradji-100">
            </div>
            @error('email')
                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-primary w-full py-3.5">Kirim tautan atur ulang</button>
    </form>

    <p class="mt-6 text-center text-sm text-clinic-600">
        Ingat kata sandi? <a href="{{ route('login') }}" class="font-bold text-soeradji-700 hover:underline">Kembali masuk</a>
    </p>
@endsection
