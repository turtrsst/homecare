@extends('layouts.admin')

@section('title', 'Ubah Layanan')

@section('content')
    <nav class="text-sm mb-4">
        <a href="{{ route('operasional.layanan.index') }}" class="font-semibold text-stone-400 hover:text-brand-700">← Layanan & Tarif</a>
    </nav>
    <div class="max-w-3xl">
        <h1 class="text-2xl font-extrabold text-stone-900">Ubah Layanan</h1>
        <p class="mt-1 text-sm text-stone-500">{{ $service->name }} · <span class="font-mono">{{ $service->slug }}</span></p>
        <x-card class="mt-5">
            @include('operational.services._form')
        </x-card>
    </div>
@endsection
