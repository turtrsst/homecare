@extends('layouts.guest')

@section('title', 'Daftar Akun')

@section('content')
    <x-card class="p-6 sm:p-8">
        <h1 class="text-2xl font-extrabold text-stone-900">Buat akun Anda</h1>
        <p class="mt-1.5 text-sm text-stone-500">
            Satu akun bisa untuk memesan layanan bagi diri sendiri maupun anggota keluarga.
        </p>

        <form method="POST" action="{{ route('register') }}" class="mt-7 space-y-5" novalidate>
            @csrf

            <x-input name="name" label="Nama lengkap" required
                     placeholder="Contoh: Rama Aditya" autocomplete="name" icon="user" />

            <x-input name="email" type="email" label="Email" required
                     placeholder="nama@email.com" autocomplete="email" icon="mail" />

            <x-input name="phone" type="tel" label="Nomor telepon / WhatsApp" required
                     placeholder="Contoh: 081234567890" autocomplete="tel" icon="phone"
                     hint="Dipakai tim kami untuk menghubungi Anda terkait kunjungan." />

            <x-input name="password" type="password" label="Kata sandi" required
                     placeholder="Minimal 8 karakter" autocomplete="new-password"
                     hint="Gunakan kombinasi huruf dan angka agar lebih aman." />

            <x-input name="password_confirmation" type="password" label="Ulangi kata sandi" required
                     placeholder="Ketik ulang kata sandi" autocomplete="new-password" />

            <x-button type="submit" full size="lg">Daftar Sekarang</x-button>

            <p class="text-xs text-stone-400 text-center leading-relaxed">
                Dengan mendaftar, Anda menyetujui pemrosesan data untuk keperluan pelayanan
                kesehatan. Data Anda disimpan secara privat dan tidak dibagikan ke pihak ketiga.
            </p>
        </form>

        <p class="mt-6 text-center text-sm text-stone-500">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-bold text-brand-700 hover:text-brand-800 underline underline-offset-2">
                Masuk di sini
            </a>
        </p>
    </x-card>
@endsection
