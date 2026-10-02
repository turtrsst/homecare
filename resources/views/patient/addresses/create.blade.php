@extends('layouts.app')

@section('title', 'Tambah Alamat')

@section('content')
    <div class="max-w-2xl">
        <nav class="text-sm mb-4">
            <a href="{{ route('akun.pasien.alamat.index', $patient) }}" class="font-semibold text-stone-400 hover:text-brand-700">← Alamat {{ $patient->name }}</a>
        </nav>
        <h1 class="text-2xl font-extrabold text-stone-900">Tambah Alamat</h1>
        <p class="mt-1 text-sm text-stone-500">Pastikan alamat mudah ditemukan petugas kami.</p>

        <x-card class="mt-5">
            @include('patient.addresses._form')
        </x-card>
    </div>
@endsection
