@extends('layouts.guest')

@section('title', 'Daftar Akun')

@section('content')
    <div class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.22em] text-soeradji-600">Buat akun</p>
        <h1 class="mt-2 text-3xl font-black text-clinic-900">Daftar ke Soeradji Care</h1>
        <p class="mt-2 text-sm text-clinic-600">Satu akun bisa dipakai untuk diri sendiri maupun anggota keluarga.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4" novalidate>
        @csrf

        <div>
            <label for="name" class="mb-2 block text-sm font-bold text-clinic-700">Nama lengkap</label>
            <div class="relative">
                <x-icon name="user" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-clinic-400" />
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Contoh: Rama Aditya"
                       class="w-full rounded-2xl border border-stone-200 bg-stone-50 py-3 pl-10 pr-3 text-sm text-clinic-900 placeholder:text-clinic-400 focus:border-soeradji-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-soeradji-100">
            </div>
            @error('name')
                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>

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
            <label for="phone" class="mb-2 block text-sm font-bold text-clinic-700">Nomor telepon / WhatsApp</label>
            <div class="relative">
                <x-icon name="phone" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-clinic-400" />
                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" placeholder="0812xxxxxxxx"
                       class="w-full rounded-2xl border border-stone-200 bg-stone-50 py-3 pl-10 pr-3 text-sm text-clinic-900 placeholder:text-clinic-400 focus:border-soeradji-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-soeradji-100">
            </div>
            @error('phone')
                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="mb-2 block text-sm font-bold text-clinic-700">Kata sandi</label>
            <div class="relative">
                <x-icon name="lock" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-clinic-400" />
                <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter"
                       class="w-full rounded-2xl border border-stone-200 bg-stone-50 py-3 pl-10 pr-3 text-sm text-clinic-900 placeholder:text-clinic-400 focus:border-soeradji-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-soeradji-100">
            </div>
            @error('password')
                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="mb-2 block text-sm font-bold text-clinic-700">Ulangi kata sandi</label>
            <div class="relative">
                <x-icon name="lock" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-clinic-400" />
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ketik ulang kata sandi"
                       class="w-full rounded-2xl border border-stone-200 bg-stone-50 py-3 pl-10 pr-3 text-sm text-clinic-900 placeholder:text-clinic-400 focus:border-soeradji-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-soeradji-100">
            </div>
        </div>

        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-soeradji-600 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-soeradji-600/20 transition hover:bg-soeradji-700">
            <x-icon name="user-plus" class="h-4 w-4" /> Daftar Sekarang
        </button>

        <p class="text-center text-[11px] leading-relaxed text-clinic-500">
            Dengan mendaftar, Anda menyetujui pemrosesan data untuk kebutuhan pelayanan kesehatan. Data Anda disimpan secara privat.
        </p>
    </form>

    <div class="mt-6 text-center text-sm text-clinic-600">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-bold text-soeradji-700 underline underline-offset-2 hover:text-soeradji-800">Masuk di sini</a>
    </div>
@endsection
