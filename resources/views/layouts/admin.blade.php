{{-- Layout area operasional RSUP: sidebar di desktop, drawer di mobile. --}}
@extends('layouts.base')

@section('body')
    @php
        $user = auth()->user();
        $sections = [
            ['label' => 'Ringkasan', 'route' => 'operasional.dashboard', 'match' => 'operasional.dashboard', 'icon' => 'home'],
            ['label' => 'Permintaan', 'route' => 'operasional.pengajuan.index', 'match' => 'operasional.pengajuan.*', 'icon' => 'clipboard'],
            ['label' => 'Jadwal kunjungan', 'route' => 'operasional.jadwal.index', 'match' => 'operasional.jadwal.*', 'icon' => 'calendar'],
            ['label' => 'Pasien', 'route' => 'operasional.pasien.index', 'match' => 'operasional.pasien.*', 'icon' => 'users'],
            ['label' => 'Layanan & tarif', 'route' => 'operasional.layanan.index', 'match' => 'operasional.layanan.*', 'icon' => 'stethoscope', 'can' => 'services.manage'],
            ['label' => 'Tenaga kesehatan', 'route' => 'operasional.petugas.index', 'match' => 'operasional.petugas.*', 'icon' => 'user', 'can' => 'staff.manage'],
            ['label' => 'Laporan', 'route' => 'operasional.laporan.index', 'match' => 'operasional.laporan.*', 'icon' => 'chart', 'can' => 'reports.view'],
            ['label' => 'Audit trail', 'route' => 'operasional.audit.index', 'match' => 'operasional.audit.*', 'icon' => 'shield', 'can' => 'audit.view'],
        ];
    @endphp

    <div class="min-h-full" x-data="{ drawer: false }">
        <a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-xl focus:bg-white focus:px-4 focus:py-2">Lewati ke konten</a>

        {{-- Sidebar (desktop) --}}
        <aside class="fixed inset-y-0 left-0 z-30 hidden w-72 flex-col border-r border-clinic-200/80 bg-white lg:flex">
            @include('layouts.partials.admin-nav', ['sections' => $sections, 'user' => $user])
        </aside>

        {{-- Drawer (mobile) --}}
        <div x-show="drawer" x-cloak class="fixed inset-0 z-40 lg:hidden" role="dialog" aria-modal="true" aria-label="Menu operasional">
            <div class="absolute inset-0 bg-clinic-900/40 backdrop-blur-sm" @click="drawer = false"></div>
            <aside x-show="drawer" x-transition:enter="transition duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                   x-transition:leave="transition duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                   @keydown.escape.window="drawer = false"
                   class="absolute inset-y-0 left-0 flex w-80 max-w-[85vw] flex-col bg-white shadow-panel">
                <div class="flex items-center justify-between border-b border-clinic-100 p-4">
                    <x-brand size="sm" :tagline="false" />
                    <button type="button" @click="drawer = false" class="rounded-xl p-2 text-clinic-500 hover:bg-clinic-100" aria-label="Tutup menu"><x-icon name="x" class="h-5 w-5" /></button>
                </div>
                @include('layouts.partials.admin-nav', ['sections' => $sections, 'user' => $user])
            </aside>
        </div>

        <div class="lg:pl-72">
            <header class="sticky top-0 z-20 border-b border-white/60 bg-white/85 backdrop-blur-xl">
                <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <button type="button" @click="drawer = true" class="rounded-xl p-2 text-clinic-600 hover:bg-clinic-100 lg:hidden" aria-label="Buka menu"><x-icon name="menu" class="h-6 w-6" /></button>
                        <div class="min-w-0">
                            <p class="truncate text-xs font-semibold uppercase tracking-[0.16em] text-soeradji-700">Operasional</p>
                            <p class="truncate text-sm font-bold text-clinic-900">@yield('title', 'Dashboard')</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('akun.dashboard') }}" class="hidden rounded-xl px-3 py-2 text-sm font-semibold text-clinic-600 hover:bg-clinic-100 sm:inline-flex">Lihat sebagai pasien</a>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-soeradji-500 to-medical-500 text-sm font-extrabold text-white" title="{{ $user->name }}">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                    </div>
                </div>
            </header>

            <main id="konten" class="px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
@endsection
