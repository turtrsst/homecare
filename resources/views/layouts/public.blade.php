@extends('layouts.base')

@section('body')
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-stone-200/70">
        <div class="container-app">
            <div class="flex items-center justify-between h-16 sm:h-18" x-data="{ mobileNav: false }">
                <a href="{{ rtrim(route('home'), '/') }}/" class="flex items-center gap-2.5 group" aria-label="Beranda {{ config('app.name') }}">
                    <span class="w-10 h-10 rounded-xl bg-brand-600 text-white flex items-center justify-center shadow-sm group-hover:bg-brand-700 transition">
                        <x-icon name="heart" class="w-6 h-6" />
                    </span>
                    <span class="leading-tight">
                        <span class="block font-extrabold text-stone-900">{{ config('app.name') }}</span>
                        <span class="block text-[11px] font-semibold text-brand-700 -mt-0.5">{{ config('homecare.hospital_name') }}</span>
                    </span>
                </a>

                <nav class="hidden md:flex items-center gap-1" aria-label="Navigasi utama">
                    @foreach ([
                        ['label' => 'Layanan', 'route' => 'services.index'],
                        ['label' => 'Cara Kerja', 'route' => 'how-it-works'],
                        ['label' => 'FAQ', 'route' => 'faq'],
                        ['label' => 'Kontak', 'route' => 'contact'],
                    ] as $item)
                        <a href="{{ route($item['route']) }}"
                           class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs($item['route']) ? 'text-brand-700 bg-brand-50' : 'text-stone-600 hover:text-stone-900 hover:bg-stone-100' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="hidden md:flex items-center gap-2">
                    @auth
                        @php
                            $me = auth()->user();
                            $dashboardUrl = $me->role->value === 'medical_staff'
                                ? route('tugas.index')
                                : ($me->isOperational() ? route('operasional.dashboard') : route('akun.dashboard'));
                        @endphp
                        <x-button :href="$dashboardUrl" variant="soft" size="sm">
                            Dashboard Saya
                        </x-button>
                    @else
                        <x-button :href="route('login')" variant="secondary" size="sm">Masuk</x-button>
                        <x-button :href="route('register')" size="sm">Pesan Homecare</x-button>
                    @endauth
                </div>

                <button type="button" class="md:hidden rounded-xl p-2.5 text-stone-600 hover:bg-stone-100"
                        x-on:click="mobileNav = !mobileNav" aria-expanded="false" x-bind:aria-expanded="mobileNav"
                        aria-controls="mobile-nav" aria-label="Buka menu">
                    <x-icon name="menu" class="w-6 h-6" x-show="!mobileNav" />
                    <x-icon name="x" class="w-6 h-6" x-show="mobileNav" x-cloak />
                </button>
            </div>

            <div id="mobile-nav" x-show="mobileNav" x-cloak x-collapse class="md:hidden pb-4 space-y-1">
                @foreach ([
                    ['label' => 'Layanan', 'route' => 'services.index', 'icon' => 'heart'],
                    ['label' => 'Cara Kerja', 'route' => 'how-it-works', 'icon' => 'list'],
                    ['label' => 'FAQ', 'route' => 'faq', 'icon' => 'info'],
                    ['label' => 'Kontak', 'route' => 'contact', 'icon' => 'phone'],
                ] as $item)
                    <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-base font-semibold {{ request()->routeIs($item['route']) ? 'text-brand-700 bg-brand-50' : 'text-stone-700 hover:bg-stone-100' }}">
                        <x-icon :name="$item['icon']" class="w-5 h-5" /> {{ $item['label'] }}
                    </a>
                @endforeach
                <div class="pt-2 flex flex-col gap-2">
                    @auth
                        @php
                            $meMobile = auth()->user();
                            $dashboardUrlMobile = $meMobile->role->value === 'medical_staff'
                                ? route('tugas.index')
                                : ($meMobile->isOperational() ? route('operasional.dashboard') : route('akun.dashboard'));
                        @endphp
                        <x-button :href="$dashboardUrlMobile" full>Dashboard Saya</x-button>
                    @else
                        <x-button :href="route('login')" variant="secondary" full>Masuk</x-button>
                        <x-button :href="route('register')" full>Pesan Homecare</x-button>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="bg-stone-900 text-stone-300 mt-16">
        <div class="container-app py-12 grid gap-10 md:grid-cols-4">
            <div class="md:col-span-2">
                <div class="flex items-center gap-2.5">
                    <span class="w-10 h-10 rounded-xl bg-brand-600 text-white flex items-center justify-center">
                        <x-icon name="heart" class="w-6 h-6" />
                    </span>
                    <span class="leading-tight">
                        <span class="block font-extrabold text-white">{{ config('app.name') }}</span>
                        <span class="block text-xs text-brand-300">{{ config('homecare.hospital_name') }}</span>
                    </span>
                </div>
                <p class="mt-4 text-sm leading-relaxed max-w-md text-stone-400">
                    Pelayanan kesehatan dari tenaga profesional rumah sakit, langsung di rumah Anda.
                    Mudah dipesan, jelas statusnya, aman datanya.
                </p>
                <div class="mt-5 rounded-xl bg-rose-950/60 ring-1 ring-rose-900 p-4 text-sm">
                    <p class="font-bold text-rose-200 flex items-center gap-2">
                        <x-icon name="ambulance" class="w-5 h-5" /> Gawat darurat?
                    </p>
                    <p class="mt-1 text-rose-300/90">
                        Hubungi <a class="font-bold underline" href="tel:{{ config('homecare.emergency_number') }}">{{ config('homecare.emergency_number') }}</a>
                        atau IGD {{ config('homecare.contact.phone') }}. Layanan homecare bukan untuk kegawatdaruratan.
                    </p>
                </div>
            </div>

            <div>
                <h3 class="text-white font-bold text-sm uppercase tracking-wider">Layanan</h3>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a class="hover:text-white transition" href="{{ route('services.index') }}">Semua Layanan</a></li>
                    <li><a class="hover:text-white transition" href="{{ route('how-it-works') }}">Cara Kerja</a></li>
                    <li><a class="hover:text-white transition" href="{{ route('faq') }}">FAQ</a></li>
                    <li><a class="hover:text-white transition" href="{{ route('contact') }}">Kontak</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-white font-bold text-sm uppercase tracking-wider">Hubungi Kami</h3>
                <ul class="mt-4 space-y-3 text-sm">
                    <li class="flex items-start gap-2.5">
                        <x-icon name="phone" class="w-4.5 h-4.5 mt-0.5 text-brand-400 shrink-0" />
                        <span>{{ config('homecare.contact.phone') }}<br><span class="text-stone-500">Senin–Jumat, 08.00–17.00 (IGD 24 jam)</span></span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <x-icon name="mail" class="w-4.5 h-4.5 mt-0.5 text-brand-400 shrink-0" />
                        <span>{{ config('homecare.contact.email') }}</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <x-icon name="map-pin" class="w-4.5 h-4.5 mt-0.5 text-brand-400 shrink-0" />
                        <span>{{ config('homecare.contact.address') }}</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <x-icon name="info" class="w-4.5 h-4.5 mt-0.5 text-brand-400 shrink-0" />
                        <a class="hover:text-white transition" href="{{ config('homecare.website') }}" target="_blank" rel="noopener">rsupsoeradji.id</a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="border-t border-stone-800">
            <div class="container-app py-5 text-xs text-stone-500 flex flex-col sm:flex-row items-center justify-between gap-2">
                <p>© {{ date('Y') }} {{ config('app.name') }} — {{ config('homecare.hospital_name') }}. Hak cipta dilindungi.</p>
                <p>Layanan kesehatan di rumah, lebih mudah.</p>
            </div>
        </div>
    </footer>
@endsection
