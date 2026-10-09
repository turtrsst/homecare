{{-- Layout area akun (pasien & tenaga kesehatan): top bar, navigasi bawah mobile, dan CTA Sora di tengah. --}}
@extends('layouts.base')

@section('body')
    @php
        $user = auth()->user();
        $role = $user->role->value;
        $isStaff = $role === 'medical_staff';
        $isOperational = $user->isOperational();
        $unread = min($user->unreadNotifications()->count(), 99);

        $navItems = $isStaff
            ? [
                ['label' => 'Tugas', 'route' => 'tugas.index', 'match' => 'tugas.*', 'icon' => 'clipboard'],
                ['label' => 'Notifikasi', 'route' => 'akun.notifikasi.index', 'match' => 'akun.notifikasi.*', 'icon' => 'bell'],
                ['label' => 'Profil', 'route' => 'akun.profil.edit', 'match' => 'akun.profil.*', 'icon' => 'user'],
            ]
            : [
                ['label' => 'Beranda', 'route' => 'akun.dashboard', 'match' => 'akun.dashboard', 'icon' => 'home'],
                ['label' => 'Pesanan', 'route' => 'akun.pengajuan.index', 'match' => 'akun.pengajuan.*', 'icon' => 'list'],
                ['label' => 'Sora', 'route' => 'ai-assistant', 'match' => 'ai-assistant', 'icon' => 'sparkles', 'accent' => true],
                ['label' => 'Notifikasi', 'route' => 'akun.notifikasi.index', 'match' => 'akun.notifikasi.*', 'icon' => 'bell'],
                ['label' => 'Profil', 'route' => 'akun.profil.edit', 'match' => 'akun.profil.*', 'icon' => 'user'],
            ];
    @endphp

    <div class="flex min-h-full flex-col">
        <a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-xl focus:bg-white focus:px-4 focus:py-2 focus:shadow-pop">Lewati ke konten</a>

        <header class="sticky top-0 z-40 border-b border-white/60 bg-white/85 backdrop-blur-xl">
            <div class="container-app">
                <div class="flex h-16 items-center justify-between gap-4">
                    <a href="{{ $isStaff ? route('tugas.index') : route('akun.dashboard') }}" aria-label="Beranda akun" class="shrink-0">
                        <x-brand size="sm" :tagline="false" />
                    </a>

                    <nav class="hidden items-center gap-1 rounded-2xl bg-slate-100/70 p-1 md:flex" aria-label="Navigasi akun">
                        @foreach ($navItems as $item)
                            @continue(($item['accent'] ?? false))
                            <a href="{{ route($item['route']) }}" @class([
                                'rounded-xl px-3.5 py-2 text-sm font-semibold transition',
                                'bg-white text-soeradji-700 shadow-sm' => request()->routeIs($item['match']),
                                'text-clinic-600 hover:text-clinic-900' => ! request()->routeIs($item['match']),
                            ]) @if(request()->routeIs($item['match'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                        @endforeach
                        @if ($isOperational)
                            <a href="{{ route('operasional.dashboard') }}" class="rounded-xl px-3.5 py-2 text-sm font-semibold text-clinic-600 hover:text-clinic-900">Operasional</a>
                        @endif
                    </nav>

                    <div class="flex items-center gap-2">
                        @if (! $isStaff)
                            <a href="{{ route('ai-assistant') }}" class="hidden items-center gap-2 rounded-xl bg-gradient-to-r from-soeradji-600 to-soeradji-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-soeradji-600/25 transition hover:-translate-y-0.5 md:inline-flex">
                                <x-icon name="sparkles" class="h-4 w-4" /> Pesan lewat Sora
                            </a>
                        @endif

                        <a href="{{ route('akun.notifikasi.index') }}" class="relative rounded-xl p-2.5 text-clinic-500 transition hover:bg-clinic-100 hover:text-clinic-800" aria-label="Notifikasi{{ $unread ? ' ('.$unread.' belum dibaca)' : '' }}">
                            <x-icon name="bell" class="h-5 w-5" />
                            @if ($unread > 0)
                                <span class="absolute right-1 top-1 flex h-4.5 min-w-4.5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white ring-2 ring-white">{{ $unread > 9 ? '9+' : $unread }}</span>
                            @endif
                        </a>

                        <div x-data="{ open: false }" class="relative">
                            <button type="button" @click="open = !open" :aria-expanded="open" aria-haspopup="menu"
                                    class="flex items-center gap-2 rounded-xl p-1 pr-2.5 transition hover:bg-clinic-100">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-soeradji-500 to-medical-500 text-sm font-extrabold text-white">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                                <x-icon name="chevron-down" class="hidden h-4 w-4 text-clinic-400 sm:block" />
                            </button>

                            <div x-show="open" x-cloak @click.outside="open = false" @keydown.escape.window="open = false"
                                 x-transition.origin.top.right
                                 class="absolute right-0 z-50 mt-2 w-72 overflow-hidden rounded-2xl bg-white shadow-pop ring-1 ring-clinic-200">
                                <div class="border-b border-clinic-100 bg-clinic-50/70 px-4 py-3.5">
                                    <p class="truncate text-sm font-extrabold text-clinic-900">{{ $user->name }}</p>
                                    <p class="truncate text-xs text-clinic-500">{{ $user->email }}</p>
                                    <span class="mt-2 inline-flex rounded-full bg-soeradji-50 px-2 py-0.5 text-[11px] font-bold text-soeradji-700">{{ $user->role->label() }}</span>
                                </div>
                                <div class="py-2 text-sm">
                                    <a href="{{ route('akun.profil.edit') }}" class="flex items-center gap-3 px-4 py-2.5 font-medium text-clinic-700 hover:bg-clinic-50"><x-icon name="user" class="h-4 w-4 text-clinic-400" /> Profil saya</a>
                                    @if ($isStaff)
                                        <a href="{{ route('tugas.index') }}" class="flex items-center gap-3 px-4 py-2.5 font-medium text-clinic-700 hover:bg-clinic-50"><x-icon name="clipboard" class="h-4 w-4 text-clinic-400" /> Tugas saya</a>
                                    @else
                                        <a href="{{ route('akun.pasien.index') }}" class="flex items-center gap-3 px-4 py-2.5 font-medium text-clinic-700 hover:bg-clinic-50"><x-icon name="users" class="h-4 w-4 text-clinic-400" /> Data pasien</a>
                                    @endif
                                    <a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-2.5 font-medium text-clinic-700 hover:bg-clinic-50"><x-icon name="globe" class="h-4 w-4 text-clinic-400" /> Situs publik</a>
                                </div>
                                <form method="POST" action="{{ route('logout') }}" class="border-t border-clinic-100 py-2">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-3 px-4 py-2.5 text-sm font-semibold text-rose-600 hover:bg-rose-50"><x-icon name="logout" class="h-4 w-4" /> Keluar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main id="konten" class="flex-1 pb-bnav md:pb-12">
            <div class="container-app py-6 sm:py-10">
                @yield('content')
            </div>
        </main>

        {{-- Navigasi bawah (mobile): ibu jari-friendly; untuk pasien, Sora berada di tengah --}}
        <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-clinic-200/80 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur-xl md:hidden" aria-label="Navigasi bawah">
            <div class="mx-auto grid max-w-md items-end px-2" style="grid-template-columns: repeat({{ count($navItems) }}, minmax(0, 1fr))">
                @foreach ($navItems as $item)
                    @php $active = request()->routeIs($item['match']); @endphp
                    @if (! empty($item['accent']))
                        <a href="{{ route($item['route']) }}" class="relative -mt-5 flex flex-col items-center gap-1 text-[11px] font-bold text-soeradji-700">
                            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-soeradji-600 to-medical-500 text-white shadow-lg shadow-soeradji-600/35 ring-4 ring-white">
                                <x-icon name="sparkles" class="h-6 w-6" />
                            </span>
                            {{ $item['label'] }}
                        </a>
                    @else
                        <a href="{{ route($item['route']) }}" @if($active) aria-current="page" @endif
                           class="flex min-h-14 flex-col items-center justify-center gap-0.5 py-2.5 text-[11px] font-semibold transition {{ $active ? 'text-soeradji-700' : 'text-clinic-400 hover:text-clinic-700' }}">
                            <x-icon :name="$item['icon']" class="h-6 w-6" />
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </div>
        </nav>
    </div>
@endsection
