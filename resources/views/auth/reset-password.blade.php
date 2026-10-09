@extends('layouts.guest')

@section('title', 'Atur ulang kata sandi')

@section('content')
    <div class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.22em] text-soeradji-600">Langkah terakhir</p>
        <h1 class="mt-2 text-3xl font-extrabold text-clinic-900">Kata sandi baru</h1>
        <p class="mt-2 text-sm leading-relaxed text-clinic-600">Gunakan minimal 8 karakter. Tautan ini hanya berlaku sebentar.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="mb-2 block text-sm font-bold text-clinic-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="username"
                   class="w-full rounded-2xl border border-clinic-200 bg-clinic-50 px-3.5 py-3 text-sm text-clinic-900 focus:border-soeradji-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-soeradji-100">
            @error('email')
                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="mb-2 block text-sm font-bold text-clinic-700">Kata sandi baru</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                   class="w-full rounded-2xl border border-clinic-200 bg-clinic-50 px-3.5 py-3 text-sm text-clinic-900 focus:border-soeradji-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-soeradji-100">
            @error('password')
                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="mb-2 block text-sm font-bold text-clinic-700">Ulangi kata sandi</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   class="w-full rounded-2xl border border-clinic-200 bg-clinic-50 px-3.5 py-3 text-sm text-clinic-900 focus:border-soeradji-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-soeradji-100">
        </div>

        <button type="submit" class="btn-primary w-full py-3.5">Simpan kata sandi baru</button>
    </form>
@endsection
