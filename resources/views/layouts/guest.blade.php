{{-- Layout masuk & daftar: kartu tunggal di atas latar gedung RSUP. --}}
@extends('layouts.base')

@section('body')
    <div class="relative flex min-h-full flex-col overflow-hidden mesh-bg">
        <div class="pointer-events-none absolute inset-0 grid-dots opacity-60"></div>

        <header class="relative z-10">
            <div class="container-app flex h-20 items-center justify-between">
                <a href="{{ route('home') }}" aria-label="Beranda {{ config('homecare.brand_name') }}"><x-brand size="sm" /></a>
                <a href="{{ route('ai-assistant') }}" class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2 text-sm font-semibold text-clinic-700 hover:bg-white/70">
                    <x-icon name="sparkles" class="h-4 w-4 text-soeradji-600" /> Tanya Sora
                </a>
            </div>
        </header>

        <main id="konten" class="relative z-10 flex flex-1 items-center justify-center px-4 pb-12 pt-4 sm:pt-8">
            <div class="w-full max-w-md animate-fade-up">
                <div class="rounded-[2rem] border border-white/70 bg-white/85 p-7 shadow-panel backdrop-blur-xl sm:p-9">
                    @hasSection('heading')
                        <div class="mb-7">
                            @yield('heading')
                        </div>
                    @endif
                    @yield('content')
                </div>
                <p class="mt-6 text-center text-xs text-clinic-500">{{ config('homecare.hospital_name') }} · Layanan kesehatan di rumah</p>
            </div>
        </main>
    </div>
@endsection
