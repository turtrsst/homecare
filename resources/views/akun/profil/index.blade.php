@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
    <div class="mb-8">
        <a href="{{ route('akun.dashboard') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-soeradji-700 hover:text-soeradji-800">
            <x-icon name="chevron-left" class="h-4 w-4" />
            Kembali ke dashboard
        </a>
    </div>

    <div class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-soeradji-600">Pengaturan akun</p>
        <h1 class="mt-2 text-3xl font-black text-clinic-900 sm:text-4xl">Profil saya</h1>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_0.6fr]">
        <div class="space-y-6">
            <form method="POST" action="{{ route('akun.profil.update') }}" class="rounded-[30px] border border-stone-200 bg-white p-5 shadow-card sm:p-6" novalidate>
                @csrf
                @method('PUT')

                <h2 class="text-xl font-black text-clinic-900">Data pribadi</h2>

                <div class="mt-6 space-y-5">
                    <div>
                        <label for="name" class="mb-2 block text-sm font-bold text-clinic-700">Nama lengkap</label>
                        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-clinic-900 placeholder:text-clinic-400 focus:border-soeradji-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-soeradji-100">
                        @error('name')
                            <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="mb-2 block text-sm font-bold text-clinic-700">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required disabled
                               class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-clinic-400 placeholder:text-clinic-400 focus:outline-none">
                        <p class="mt-2 text-xs text-clinic-500">Email tidak dapat diubah untuk keamanan</p>
                    </div>

                    <div>
                        <label for="phone" class="mb-2 block text-sm font-bold text-clinic-700">Nomor telepon / WhatsApp</label>
                        <input id="phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}" required
                               class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-clinic-900 placeholder:text-clinic-400 focus:border-soeradji-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-soeradji-100">
                        @error('phone')
                            <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-soeradji-600 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-soeradji-600/20 hover:bg-soeradji-700">
                    <x-icon name="save" class="h-4 w-4" />
                    Simpan perubahan
                </button>
            </form>

            <form method="POST" action="{{ route('password.update') }}" class="rounded-[30px] border border-stone-200 bg-white p-5 shadow-card sm:p-6" novalidate>
                @csrf
                @method('PUT')

                <h2 class="text-xl font-black text-clinic-900">Ubah kata sandi</h2>

                <div class="mt-6 space-y-5">
                    <div>
                        <label for="current_password" class="mb-2 block text-sm font-bold text-clinic-700">Kata sandi saat ini</label>
                        <input id="current_password" type="password" name="current_password" required
                               class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-clinic-900 placeholder:text-clinic-400 focus:border-soeradji-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-soeradji-100">
                        @error('current_password')
                            <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-bold text-clinic-700">Kata sandi baru</label>
                        <input id="password" type="password" name="password" required
                               class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-clinic-900 placeholder:text-clinic-400 focus:border-soeradji-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-soeradji-100">
                        @error('password')
                            <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-bold text-clinic-700">Ulangi kata sandi baru</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required
                               class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-clinic-900 placeholder:text-clinic-400 focus:border-soeradji-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-soeradji-100">
                    </div>
                </div>

                <button type="submit" class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-clinic-900 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-clinic-900/20 hover:bg-clinic-800">
                    <x-icon name="lock" class="h-4 w-4" />
                    Perbarui kata sandi
                </button>
            </form>
        </div>

        <div class="space-y-6">
            <div class="rounded-[30px] border border-stone-200 bg-white p-5 shadow-card sm:p-6">
                <h2 class="text-lg font-black text-clinic-900">Profil</h2>
                <div class="mt-6 flex flex-col items-center text-center">
                    <span class="flex h-20 w-20 items-center justify-center rounded-full bg-soeradji-100 text-4xl font-black text-soeradji-700">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                    <p class="mt-4 font-black text-clinic-900">{{ $user->name }}</p>
                    <p class="mt-1 text-sm text-clinic-600">{{ $user->email }}</p>
                    <span class="mt-3 inline-flex rounded-full bg-medical-50 px-3 py-1 text-xs font-bold text-medical-700">
                        {{ $user->role->label() }}
                    </span>
                </div>
            </div>

            <div class="rounded-[30px] border border-stone-200 bg-white p-5 shadow-card sm:p-6">
                <h2 class="text-lg font-black text-clinic-900">Akun</h2>
                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between rounded-2xl bg-clinic-50 px-3 py-2.5">
                        <span class="text-clinic-600">Bergabung sejak</span>
                        <span class="font-bold text-clinic-900">{{ $user->created_at->translatedFormat('d M Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl bg-clinic-50 px-3 py-2.5">
                        <span class="text-clinic-600">Total pesanan</span>
                        <span class="font-bold text-clinic-900">{{ $user->requests()->count() ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl bg-clinic-50 px-3 py-2.5">
                        <span class="text-clinic-600">Status verifikasi</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-xs font-bold text-green-700">
                            <x-icon name="check-circle" class="h-3 w-3" />
                            Terverifikasi
                        </span>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700 hover:bg-rose-100">
                    <x-icon name="logout" class="h-4 w-4" />
                    Keluar dari akun
                </button>
            </form>
        </div>
    </div>
@endsection
