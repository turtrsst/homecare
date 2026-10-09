@extends('layouts.base')

@section('body')
    <header class="sticky top-0 z-40 border-b border-stone-200/80 bg-white/90 backdrop-blur-xl">
        <div class="container-app">
            <div class="flex h-16 items-center justify-between gap-4 sm:h-20">
                <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="Soeradji Care">
                    <span class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-2xl bg-soeradji-600 shadow-sm ring-1 ring-soeradji-200">
                        <img src="{{ asset(config('homecare.logo_path')) }}" alt="Logo Soeradji Care" class="h-8 w-8 object-contain">
                    </span>
                    <span class="leading-tight">
                        <span class="block text-base font-black text-stone-900">{{ config('homecare.brand_name') }}</span>
                        <span class="block text-[10px] font-semibold uppercase tracking-[0.18em] text-soeradji-700">{{ config('homecare.hospital_name') }}</span>
                    </span>
                </a>

                <nav class="hidden items-center gap-1 md:flex" aria-label="Navigasi utama">
                    @foreach([
                        ['label' => 'Layanan', 'route' => 'services.index'],
                        ['label' => 'AI Assistant', 'route' => 'ai-assistant'],
                        ['label' => 'Cara Kerja', 'route' => 'how-it-works'],
                        ['label' => 'FAQ', 'route' => 'faq'],
                        ['label' => 'Kontak', 'route' => 'contact'],
                    ] as $item)
                        <a href="{{ route($item['route']) }}"
                           class="rounded-xl px-3.5 py-2 text-sm font-semibold transition {{ request()->routeIs($item['route']) ? 'bg-soeradji-50 text-soeradji-700' : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="hidden items-center gap-2 md:flex">
                    @auth
                        <a href="{{ route('akun.dashboard') }}" class="rounded-xl bg-stone-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-stone-700">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-xl border border-stone-200 bg-white px-4 py-2.5 text-sm font-semibold text-stone-700 hover:bg-stone-50">Masuk</a>
                        <a href="{{ route('register') }}" class="rounded-xl bg-soeradji-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-soeradji-700">Pesan Homecare</a>
                    @endauth
                </div>

                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-stone-200 bg-white p-2.5 text-stone-700 md:hidden"
                        x-data="{}" x-on:click="$dispatch('toggle-mobile-nav')" aria-label="Buka menu">
                    <x-icon name="menu" class="h-5 w-5" />
                </button>
            </div>
        </div>

        <div x-data="{ open: false }" x-on:toggle-mobile-nav.window="open = !open" x-show="open" x-cloak x-collapse class="border-t border-stone-200 bg-white md:hidden">
            <div class="container-app space-y-2 py-4">
                @foreach([
                    ['label' => 'Layanan', 'route' => 'services.index', 'icon' => 'heart'],
                    ['label' => 'AI Assistant', 'route' => 'ai-assistant', 'icon' => 'sparkles'],
                    ['label' => 'Cara Kerja', 'route' => 'how-it-works', 'icon' => 'list'],
                    ['label' => 'FAQ', 'route' => 'faq', 'icon' => 'question'],
                    ['label' => 'Kontak', 'route' => 'contact', 'icon' => 'phone'],
                ] as $item)
                    <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-stone-700 hover:bg-stone-100 {{ request()->routeIs($item['route']) ? 'bg-soeradji-50 text-soeradji-700' : '' }}">
                        <x-icon :name="$item['icon']" class="h-4 w-4" />
                        {{ $item['label'] }}
                    </a>
                @endforeach
                <div class="pt-2">
                    @auth
                        <a href="{{ route('akun.dashboard') }}" class="block rounded-xl bg-stone-900 px-4 py-3 text-center text-sm font-bold text-white">Dashboard</a>
                    @else
                        <div class="grid gap-2">
                            <a href="{{ route('login') }}" class="block rounded-xl border border-stone-200 bg-white px-4 py-3 text-center text-sm font-semibold text-stone-700">Masuk</a>
                            <a href="{{ route('register') }}" class="block rounded-xl bg-soeradji-600 px-4 py-3 text-center text-sm font-bold text-white">Pesan Homecare</a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1">@yield('content')</main>

    <footer class="mt-16 bg-clinic-900 text-stone-300">
        <div class="container-app py-12">
            <div class="grid gap-10 md:grid-cols-3">
                <div class="md:col-span-1">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-2xl bg-soeradji-600 ring-1 ring-white/10">
                            <img src="{{ asset(config('homecare.logo_path')) }}" alt="Logo Soeradji Care" class="h-8 w-8 object-contain">
                        </span>
                        <div>
                            <p class="text-base font-black text-white">{{ config('homecare.brand_name') }}</p>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-soeradji-200">{{ config('homecare.hospital_name') }}</p>
                        </div>
                    </div>
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-stone-300">
                        Layanan kesehatan di rumah yang aman, cepat, dan terintegrasi dengan sistem rumah sakit terpercaya.
                    </p>
                </div>

                <div>
                    <h3 class="text-sm font-black uppercase tracking-[0.18em] text-stone-200">Navigasi</h3>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li><a href="{{ route('services.index') }}" class="hover:text-white">Layanan</a></li>
                        <li><a href="{{ route('ai-assistant') }}" class="hover:text-white">AI Assistant</a></li>
                        <li><a href="{{ route('how-it-works') }}" class="hover:text-white">Cara Kerja</a></li>
                        <li><a href="{{ route('faq') }}" class="hover:text-white">FAQ</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-black uppercase tracking-[0.18em] text-stone-200">Kontak</h3>
                    <ul class="mt-4 space-y-3 text-sm text-stone-300">
                        <li class="flex items-start gap-2"><x-icon name="phone" class="mt-0.5 h-4 w-4 text-soeradji-300" /> {{ config('homecare.contact.phone') }}</li>
                        <li class="flex items-start gap-2"><x-icon name="mail" class="mt-0.5 h-4 w-4 text-soeradji-300" /> {{ config('homecare.contact.email') }}</li>
                        <li class="flex items-start gap-2"><x-icon name="map-pin" class="mt-0.5 h-4 w-4 text-soeradji-300" /> {{ config('homecare.contact.address') }}</li>
                    </ul>
                </div>
            </div>
            <div class="mt-10 border-t border-stone-800 pt-5 text-xs text-stone-400">
                © {{ date('Y') }} {{ config('homecare.brand_name') }} — {{ config('homecare.hospital_name') }}
            </div>
        </div>
    </footer>
@endsection
