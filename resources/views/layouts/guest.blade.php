@extends('layouts.base')

@section('body')
    <div class="min-h-full flex flex-col bg-gradient-to-b from-soeradji-50 via-white to-stone-50">
        <header class="container-app pt-6 sm:pt-8">
            <a href="{{ rtrim(route('home'), '/') }}/" class="inline-flex items-center gap-3 group" aria-label="Kembali ke beranda">
                <span class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-2xl bg-soeradji-600 shadow-md ring-1 ring-soeradji-200 transition group-hover:scale-[1.02]">
                    <img src="{{ asset(config('homecare.logo_path')) }}" alt="Logo Soeradji Care" class="h-8 w-8 object-contain">
                </span>
                <span class="leading-tight">
                    <span class="block text-base font-black text-clinic-900">{{ config('homecare.brand_name') }}</span>
                    <span class="block text-[10px] font-bold uppercase tracking-[0.18em] text-soeradji-700">{{ config('homecare.hospital_name') }}</span>
                </span>
            </a>
        </header>

        <main class="flex-1 px-4 py-8 sm:py-12">
            <div class="mx-auto w-full max-w-5xl lg:grid lg:grid-cols-[1fr_1.05fr] lg:items-center lg:gap-8">
                <div class="hidden lg:block">
                    <div class="rounded-[32px] bg-gradient-to-br from-soeradji-600 via-soeradji-500 to-medical-500 p-8 text-white shadow-panel">
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.2em] text-soeradji-50">
                            <x-icon name="shield" class="h-4 w-4" />
                            Layanan resmi rumah sakit
                        </div>
                        <h1 class="mt-6 text-4xl font-black leading-tight">Perawatan kesehatan rumah yang lebih mudah.</h1>
                        <p class="mt-4 max-w-md text-sm leading-relaxed text-soeradji-50">
                            Kami membantu Anda mengakses dokter, perawat, fisioterapi, dan layanan pendukung rumah sakit dengan proses yang cepat dan aman.
                        </p>

                        <div class="mt-8 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">
                                <div class="flex items-center gap-2 text-sm font-bold text-white"><x-icon name="check-circle" class="h-4 w-4 text-medical-300" /> Booking cepat</div>
                                <p class="mt-2 text-xs text-soeradji-50">Pemrosesan kebutuhan yang efisien dan transparan.</p>
                            </div>
                            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">
                                <div class="flex items-center gap-2 text-sm font-bold text-white"><x-icon name="heart" class="h-4 w-4 text-medical-300" /> Tim profesional</div>
                                <p class="mt-2 text-xs text-soeradji-50">Dokter, perawat, dan tenaga kesehatan berpengalaman.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full">
                    <div class="rounded-[30px] border border-stone-200 bg-white p-5 shadow-soft sm:p-7">
                        @yield('content')
                    </div>
                </div>
            </div>
        </main>

        <footer class="px-4 pb-8 text-center text-xs text-stone-500">
            Kondisi darurat? Hubungi <a href="tel:{{ config('homecare.emergency_number') }}" class="font-bold text-rose-500 underline underline-offset-2">{{ config('homecare.emergency_number') }}</a> atau IGD rumah sakit.
        </footer>
    </div>
@endsection
