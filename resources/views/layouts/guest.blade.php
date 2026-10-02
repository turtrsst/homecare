@extends('layouts.base')

@section('body')
    <div class="min-h-full flex flex-col bg-gradient-to-b from-brand-50 via-stone-50 to-stone-50">
        <header class="container-app pt-8">
            <a href="{{ rtrim(route('home'), '/') }}/" class="inline-flex items-center gap-2.5 group">
                <span class="w-10 h-10 rounded-xl bg-brand-600 text-white flex items-center justify-center shadow-sm">
                    <x-icon name="heart" class="w-6 h-6" />
                </span>
                <span class="leading-tight">
                    <span class="block font-extrabold text-stone-900">{{ config('app.name') }}</span>
                    <span class="block text-[11px] font-semibold text-brand-700 -mt-0.5">{{ config('homecare.hospital_name') }}</span>
                </span>
            </a>
        </header>

        <main class="flex-1 flex items-center justify-center px-4 py-10">
            <div class="w-full max-w-md">
                @yield('content')
            </div>
        </main>

        <footer class="pb-8 text-center text-xs text-stone-400 px-4">
            Kondisi darurat? Hubungi <a href="tel:{{ config('homecare.emergency_number') }}" class="font-bold text-rose-500 underline">{{ config('homecare.emergency_number') }}</a> atau IGD terdekat.
        </footer>
    </div>
@endsection
