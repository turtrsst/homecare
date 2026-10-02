@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
    <div class="max-w-2xl">
        <h1 class="text-2xl font-extrabold text-stone-900">Profil Saya</h1>
        <p class="mt-1 text-sm text-stone-500">Data akun & keamanan Anda.</p>

        <x-card class="mt-5">
            <form method="POST" action="{{ route('akun.profil.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Ringkasan akun --}}
                <div class="flex items-center gap-4 pb-5 border-b border-stone-100">
                    <span class="w-16 h-16 rounded-2xl bg-gradient-to-br from-brand-500 to-teal-600 text-white flex items-center justify-center text-2xl font-extrabold shrink-0">
                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0">
                        <p class="font-extrabold text-stone-900 truncate">{{ $user->name }}</p>
                        <p class="text-sm text-stone-500 truncate">{{ $user->email }}</p>
                        <x-badge color="teal" class="mt-1">{{ $user->role->label() }}</x-badge>
                    </div>
                </div>

                <x-input name="name" label="Nama lengkap" required icon="user" maxlength="120" :value="$user->name" />
                <x-input name="email" type="email" label="Email" required icon="mail" :value="$user->email"
                         hint="Dipakai untuk login & pemberitahuan penting." />
                <x-input name="phone" type="tel" label="Nomor telepon" inputmode="tel" icon="phone" :value="$user->phone"
                         placeholder="08xx xxxx xxxx" />

                <div class="pt-3 border-t border-stone-100">
                    <p class="text-sm font-bold text-stone-800 mb-1">Ubah kata sandi <span class="font-normal text-stone-400">(opsional)</span></p>
                    <p class="text-xs text-stone-400 mb-4">Kosongkan bila tidak ingin mengubah.</p>
                    <div class="space-y-5">
                        <x-input name="password" type="password" label="Kata sandi baru" autocomplete="new-password" icon="lock"
                                 hint="Minimal 8 karakter." />
                        <x-input name="password_confirmation" type="password" label="Konfirmasi kata sandi baru" autocomplete="new-password" icon="lock" />
                    </div>
                </div>

                <div class="pt-2">
                    <x-button type="submit" size="lg" icon="check" full class="sm:w-auto sm:flex-none">Simpan Perubahan</x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection
