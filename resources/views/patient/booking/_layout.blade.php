{{-- Kerangka bersama wizard booking: stepper + konten langkah + navigasi --}}
@extends('layouts.app')

@section('title', 'Pesan Homecare — '.($stepTitles[$step] ?? ''))

@section('content')
    <div class="mb-5">
        <div class="flex items-center justify-between gap-3">
            <h1 class="text-xl sm:text-2xl font-extrabold text-stone-900">Pesan Homecare</h1>
            <form method="POST" action="{{ route('akun.pesan.reset') }}">
                @csrf
                @method('POST')
                <button type="submit" class="text-xs font-bold text-stone-400 hover:text-rose-500 transition underline underline-offset-2">
                    Mulai ulang
                </button>
            </form>
        </div>
        <div class="mt-4">
            <x-stepper :steps="$steps" :stepTitles="$stepTitles" :current="$stepIndex" />
        </div>
        <p class="mt-3 text-sm text-stone-500 sm:hidden">
            Langkah {{ $stepIndex }} dari {{ count($steps) }}: <span class="font-bold text-stone-700">{{ $stepTitles[$step] }}</span>
        </p>
    </div>

    @yield('step-content')
@endsection
