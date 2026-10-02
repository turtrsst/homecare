@extends('layouts.base')

@section('body')
    @php
        $user = auth()->user();
        $nav = collect([
            ['label' => 'Dashboard', 'route' => 'operasional.dashboard', 'match' => 'operasional.dashboard', 'icon' => 'home', 'gate' => null],
            ['label' => 'Permintaan Homecare', 'route' => 'operasional.pengajuan.index', 'match' => 'operasional.pengajuan.*', 'icon' => 'clipboard', 'gate' => null],
            ['label' => 'Jadwal & Kunjungan', 'route' => 'operasional.jadwal.index', 'match' => 'operasional.jadwal.*', 'icon' => 'calendar', 'gate' => null],
            ['label' => 'Pasien', 'route' => 'operasional.pasien.index', 'match' => 'operasional.pasien.*', 'icon' => 'users', 'gate' => null],
            ['label' => 'Layanan & Tarif', 'route' => 'operasional.layanan.index', 'match' => 'operasional.layanan.*', 'icon' => 'heart', 'gate' => 'services.manage'],
            ['label' => 'Tenaga Kesehatan', 'route' => 'operasional.petugas.index', 'match' => 'operasional.petugas.*', 'icon' => 'stethoscope', 'gate' => 'staff.manage'],
            ['label' => 'Laporan', 'route' => 'operasional.laporan.index', 'match' => 'operasional.laporan.*', 'icon' => 'chart', 'gate' => 'reports.view'],
            ['label' => 'Audit Trail', 'route' => 'operasional.audit.index', 'match' => 'operasional.audit.*', 'icon' => 'shield', 'gate' => 'audit.view'],
        ])->filter(fn ($item) => $item['gate'] === null || $user->can($item['gate']));
    @endphp

    {{-- Sidebar terkunci penuh setinggi layar di desktop (CSS murni agar tidak tergantung hasil build Tailwind) --}}
    @push('head')
    <style>
        @media (min-width: 1024px) {
            .ops-shell { display: block; }
            .ops-shell > aside.ops-sidebar {
                position: fixed; top: 0; bottom: 0; left: 0;
                width: 18rem; height: 100vh; z-index: 40;
                display: flex; flex-direction: column;
                transform: none; transition: none;
            }
            .ops-shell > div.ops-content { margin-left: 18rem; }
        }
    </style>
    @endpush

    <div class="ops-shell min-h-screen lg:flex bg-stone-100" x-data="{ sidebar: false }">
        {{-- Overlay drawer (mobile) --}}
        <div x-show="sidebar" x-cloak x-on:click="sidebar = false" x-transition.opacity
             class="fixed inset-0 bg-stone-900/50 z-40 lg:hidden"></div>

        {{-- Sidebar (desktop) / drawer (mobile) --}}
        <aside class="ops-sidebar fixed inset-y-0 left-0 z-50 w-72 shrink-0 bg-stone-900 text-stone-300 flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-200 lg:static lg:z-auto"
               :class="sidebar && 'translate-x-0'" aria-label="Menu operasional">
                <div class="flex items-center justify-between px-5 h-16 border-b border-stone-800">
                    <a href="{{ route('operasional.dashboard') }}" class="flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-xl bg-brand-600 text-white flex items-center justify-center">
                            <x-icon name="heart" class="w-5 h-5" />
                        </span>
                        <span class="leading-tight">
                            <span class="block font-extrabold text-white text-sm">{{ config('app.name') }} Ops</span>
                            <span class="block text-[10px] text-stone-400">{{ config('homecare.hospital_name') }}</span>
                        </span>
                    </a>
                    <button type="button" x-on:click="sidebar = false" class="lg:hidden rounded-lg p-2 hover:bg-stone-800" aria-label="Tutup menu">
                        <x-icon name="x" class="w-5 h-5" />
                    </button>
                </div>

                <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                    @foreach ($nav as $item)
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition min-h-11
                                  {{ request()->routeIs($item['match']) ? 'bg-brand-600 text-white shadow-sm' : 'text-stone-400 hover:text-white hover:bg-stone-800' }}"
                           @if(request()->routeIs($item['match'])) aria-current="page" @endif>
                            <x-icon :name="$item['icon']" class="w-5 h-5 shrink-0" />
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="p-4 border-t border-stone-800">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-full bg-stone-700 text-white flex items-center justify-center font-bold text-sm">
                            {{ mb_substr($user->name, 0, 1) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-white truncate">{{ $user->name }}</p>
                            <p class="text-xs text-stone-500 truncate">{{ $user->role->label() }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 text-sm font-semibold py-2.5 transition min-h-11">
                            <x-icon name="logout" class="w-4.5 h-4.5" /> Keluar
                        </button>
                    </form>
                </div>
            </aside>

        {{-- Konten --}}
        <div class="ops-content flex-1 min-w-0 flex flex-col">
            <header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-stone-200 lg:hidden">
                <div class="flex items-center justify-between h-14 px-4">
                    <button type="button" x-on:click="sidebar = true" class="rounded-xl p-2.5 text-stone-600 hover:bg-stone-100" aria-label="Buka menu">
                        <x-icon name="menu" class="w-6 h-6" />
                    </button>
                    <span class="font-extrabold text-stone-900 text-sm">{{ config('app.name') }} Ops</span>
                    @php $adminUnread = $user->unreadNotifications()->limit(50)->count(); @endphp
                    <a href="{{ route('akun.notifikasi.index') }}" class="relative rounded-xl p-2.5 text-stone-500 hover:bg-stone-100" aria-label="Notifikasi">
                        <x-icon name="bell" class="w-5 h-5" />
                        @if ($adminUnread > 0)
                            <span class="absolute top-1 right-1 min-w-4 h-4 px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center">
                                {{ $adminUnread > 9 ? '9+' : $adminUnread }}
                            </span>
                        @endif
                    </a>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <div class="max-w-7xl mx-auto">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
@endsection
