@extends('layouts.admin')

@section('title', 'Ubah Data '.$staff->name)

@section('content')
    <nav class="text-sm mb-4">
        <a href="{{ route('operasional.petugas.index') }}" class="font-semibold text-stone-400 hover:text-brand-700">← Tenaga Kesehatan</a>
    </nav>
    <div class="max-w-2xl">
        <h1 class="text-2xl font-extrabold text-stone-900">Ubah Data Petugas</h1>
        <p class="mt-1 text-sm text-stone-500">{{ $staff->name }} · {{ $staff->profession->label() }}</p>
        <x-card class="mt-5">
            @include('operational.staff._form')
        </x-card>
    </div>
@endsection
