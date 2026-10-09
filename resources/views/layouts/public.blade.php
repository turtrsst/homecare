@extends('layouts.base')

@section('body')
    @php
        $nav = [
            ['label' => 'Layanan', 'route' => 'services.index', 'match' => 'services.*', 'icon' => 'heart'],
            ['label' => 'Asisten Sora', 'route' => 'ai-assistant', 'match' => 'ai-assistant', 'icon' => 'sparkles'],
            ['label' => 'Cara Kerja', 'route' => 'how-it-works', 'match' => 'how-it-works', 'icon' => 'list'],
            ['label' => 'FAQ', 'route' => 'faq', 'match' => 'faq', 'icon' => 'question'],
            ['label' => 'Kontak', 'route' => 'contact', 'match' => 'contact', 'icon' => 'phone'],
        ];
        $bottomNav = [
            ['label' => 'Beranda', 'route' => 'home', 'match' => 'home', 'icon' => 'home'],
            ['label' => 'Layanan', 'route' => 'services.index', 'match' => 'services.*', 'icon' => 'heart'],
            ['label' => 'Sora', 'route' => 'ai-assistant', 'match' => 'ai-assistant', 'icon' => 'sparkles', 'accent' => true],
            ['label' => 'FAQ', 'route' => 'faq', 'match' => 'faq', 'icon' => 'question'],
            ['label' => 'Akun', 'route' => auth()->check() ? 'akun.dashboard' : 'login', 'match' => 'akun.*|login', 'icon' => 'user'],
        ];
        $wa = config('homecare.contact.whatsapp');
        $isAssistant = request()->routeIs('ai-assistant');
    @endphp

    <div class="flex min-h-full flex-col">
        <a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-xl focus:bg-white focus:px-4 focus:py-2 focus:shadow-pop">Lewati ke konten</a>

        <header class="sticky top-0 z-40 border-b border-white/60 bg-white/80 backdrop-blur-xl" x-data="{ open: false }">
            <div class="container-app">
                <div class="flex h-16 items-center justify-between gap-4 lg:h-20">
                    <a href="{{ route('home') }}" aria-label="Beranda {{ config('homecare.brand_name') }}" class="shrink-0">
                        <x-brand size="sm" :tagline="false" />
                    </a>

                    <nav class="hidden items-center gap-1 rounded-2xl bg-slate-100/70 p-1 lg:flex" aria-label="Navigasi utama">
                        @foreach ($nav as $item)
                            @php $active = request()->routeIs($item['match']); @endphp
                            <a href="{{ route($item['route']) }}"
                               @class([
                                   'flex items-center gap-2 rounded-xl px-3.5 py-2 text-sm font-semibold transition',
                                   'bg-white text-soeradji-700 shadow-sm' => $active,
                                   'text-clinic-600 hover:text-clinic-900' => ! $active,
                               ])
                               @if ($active) aria-current="page" @endif>
                                @if ($item['icon'] === 'sparkles')
                                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-md bg-gradient-to-br from-soeradji-500 to-medical-500 text-white"><x-icon name="sparkles" class="h-3 w-3" /></span>
                                @endif
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </nav>

                    <div class="hidden items-center gap-2 lg:flex">
                        @auth
                            <a href="{{ route('akun.dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-clinic-900 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-clinic-800">
                                <x-icon name="home" class="h-4 w-4" /> Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-clinic-700 transition hover:bg-slate-100">Masuk</a>
                            <a href="{{ route('register') }}" class="rounded-xl bg-gradient-to-r from-soeradji-600 to-soeradji-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-soeradji-600/25 transition hover:-translate-y-0.5 hover:shadow-soeradji-600/35">Daftar & Pesan</a>
                        @endauth
                    </div>

                    <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-clinic-200 bg-white text-clinic-700 lg:hidden"
                            x-on:click="open = !open" :aria-expanded="open" aria-controls="menu-mobile" aria-label="Buka menu">
                        <x-icon name="menu" class="h-5 w-5" x-show="!open" />
                        <x-icon name="x" class="h-5 w-5" x-show="open" x-cloak />
                    </button>
                </div>
            </div>

            <div id="menu-mobile" x-show="open" x-cloak x-collapse class="border-t border-clinic-100 bg-white lg:hidden">
                <div class="container-app space-y-1 py-4">
                    @foreach ($nav as $item)
                        <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-clinic-800 hover:bg-slate-50">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-soeradji-50 text-soeradji-700"><x-icon :name="$item['icon']" class="h-4 w-4" /></span>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                    <div class="grid gap-2 pt-3">
                        @auth
                            <a href="{{ route('akun.dashboard') }}" class="rounded-2xl bg-clinic-900 px-4 py-3 text-center text-sm font-bold text-white">Buka Dashboard</a>
                        @else
                            <a href="{{ route('register') }}" class="rounded-2xl bg-soeradji-600 px-4 py-3 text-center text-sm font-bold text-white">Daftar & Pesan Homecare</a>
                            <a href="{{ route('login') }}" class="rounded-2xl border border-clinic-200 px-4 py-3 text-center text-sm font-semibold text-clinic-700">Masuk</a>
                        @endauth
                    </div>
                </div>
            </div>
        </header>

        <main id="konten" class="flex-1 pb-28 lg:pb-0">@yield('content')</main>

        {{-- Tombol melayang "Tanya Sora" (desktop) --}}
        @unless ($isAssistant)
            <a href="{{ route('ai-assistant') }}" class="group fixed bottom-6 right-6 z-40 hidden items-center gap-3 rounded-full bg-clinic-900 py-3 pl-3 pr-5 text-sm font-bold text-white shadow-pop transition hover:-translate-y-0.5 lg:inline-flex">
                <span class="relative flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-soeradji-500 to-medical-500">
                    <x-icon name="sparkles" class="h-4 w-4" />
                    <span class="absolute inset-0 animate-ping rounded-full bg-soeradji-400/40"></span>
                </span>
                Tanya Sora
            </a>
        @endunless

        {{-- Navigasi bawah (mobile) --}}
        <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-clinic-200/80 bg-white/90 pb-[env(safe-area-inset-bottom)] backdrop-blur-xl lg:hidden" aria-label="Navigasi bawah">
            <ul class="mx-auto grid max-w-md grid-cols-5 px-2 pt-2">
                @foreach ($bottomNav as $item)
                    @php $active = request()->routeIs(...explode('|', $item['match'])); @endphp
                    <li>
                        <a href="{{ route($item['route']) }}" class="flex flex-col items-center gap-1 rounded-2xl py-1.5 text-[11px] font-semibold {{ $active ? 'text-soeradji-700' : 'text-clinic-500' }}" @if($active) aria-current="page" @endif>
                            @if (! empty($item['accent']))
                                <span class="-mt-7 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-soeradji-600 to-medical-500 text-white shadow-lg shadow-soeradji-600/30 ring-4 ring-white">
                                    <x-icon name="sparkles" class="h-6 w-6" />
                                </span>
                            @else
                                <span class="flex h-8 w-12 items-center justify-center rounded-full {{ $active ? 'bg-soeradji-100' : '' }}">
                                    <x-icon :name="$item['icon']" class="h-5 w-5" />
                                </span>
                            @endif
                            <span @class(['mt-0.5', '-mt-1' => ! empty($item['accent'])])>{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <footer class="mt-20 border-t border-clinic-800 bg-clinic-900 text-clinic-200">
            <div class="container-app py-14">
                <div class="grid gap-12 lg:grid-cols-12">
                    <div class="lg:col-span-5">
                        <x-brand size="md" :dark="true" />
                        <p class="mt-5 max-w-md text-sm leading-relaxed text-clinic-300">
                            Layanan homecare resmi {{ config('homecare.hospital_name') }}. Tenaga kesehatan profesional datang ke rumah Anda — dipesan cukup dengan satu kalimat.
                        </p>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ route('ai-assistant') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 text-sm font-semibold text-white ring-1 ring-white/15 transition hover:bg-white/15">
                                <x-icon name="sparkles" class="h-4 w-4 text-medical-300" /> Pesan lewat Sora
                            </a>
                            @if ($wa)
                                <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 text-sm font-semibold text-white ring-1 ring-white/15 transition hover:bg-white/15">
                                    <x-icon name="message-circle" class="h-4 w-4 text-medical-300" /> WhatsApp
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="grid gap-8 sm:grid-cols-2 lg:col-span-4 lg:col-start-7">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-[0.16em] text-clinic-400">Jelajahi</h3>
                            <ul class="mt-4 space-y-2.5 text-sm">
                                <li><a href="{{ route('services.index') }}" class="hover:text-white">Katalog layanan</a></li>
                                <li><a href="{{ route('ai-assistant') }}" class="hover:text-white">Asisten Sora</a></li>
                                <li><a href="{{ route('how-it-works') }}" class="hover:text-white">Cara kerja</a></li>
                                <li><a href="{{ route('faq') }}" class="hover:text-white">Pertanyaan umum</a></li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-[0.16em] text-clinic-400">Kontak</h3>
                            <ul class="mt-4 space-y-3 text-sm">
                                <li class="flex gap-2"><x-icon name="phone" class="mt-0.5 h-4 w-4 shrink-0 text-medical-300" /> {{ config('homecare.contact.phone') }}</li>
                                <li class="flex gap-2"><x-icon name="mail" class="mt-0.5 h-4 w-4 shrink-0 text-medical-300" /> {{ config('homecare.contact.email') }}</li>
                                <li class="flex gap-2"><x-icon name="map-pin" class="mt-0.5 h-4 w-4 shrink-0 text-medical-300" /> {{ config('homecare.contact.address') }}</li>
                                <li class="flex gap-2"><x-icon name="clock" class="mt-0.5 h-4 w-4 shrink-0 text-medical-300" /> {{ config('homecare.hours') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="mt-12 flex flex-col gap-3 rounded-2xl bg-rose-500/10 p-4 text-sm ring-1 ring-rose-400/20 sm:flex-row sm:items-center sm:justify-between">
                    <p class="flex items-center gap-2 font-semibold text-rose-200">
                        <x-icon name="ambulance" class="h-5 w-5" /> Kondisi gawat darurat? Jangan menunggu kunjungan rumah.
                    </p>
                    <a href="tel:{{ config('homecare.emergency_number') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-500 px-4 py-2 font-bold text-white hover:bg-rose-600">
                        Hubungi {{ config('homecare.emergency_number') }} / IGD
                    </a>
                </div>

                <div class="mt-8 flex flex-col gap-2 border-t border-clinic-800 pt-6 text-xs text-clinic-400 sm:flex-row sm:justify-between">
                    <p>© {{ date('Y') }} {{ config('homecare.brand_name') }} — {{ config('homecare.hospital_name') }}</p>
                    <p>Dibangun untuk pelayanan yang aman, cepat, dan transparan.</p>
                </div>
            </div>
        </footer>
    </div>
@endsection
