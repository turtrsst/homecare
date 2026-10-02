@extends('layouts.base')

@section('body')
    @php $user = auth()->user(); @endphp
    @php $isStaff = $user->role->value === 'medical_staff'; @endphp
    <div class="min-h-full flex flex-col bg-stone-50">
        {{-- Topbar ringkas: tanpa sidebar besar di sisi pasien --}}
        <header class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-stone-200/70">
            <div class="container-narrow">
                <div class="flex items-center justify-between h-16">
                    <a href="{{ $isStaff ? route('tugas.index') : route('akun.dashboard') }}" class="flex items-center gap-2.5" aria-label="Beranda akun">
                        <span class="w-9 h-9 rounded-xl bg-brand-600 text-white flex items-center justify-center shadow-sm">
                            <x-icon name="heart" class="w-5 h-5" />
                        </span>
                        <span class="leading-tight hidden sm:block">
                            <span class="block font-extrabold text-stone-900 text-sm">{{ config('app.name') }}</span>
                            <span class="block text-[10px] font-semibold text-brand-700 -mt-0.5">{{ config('homecare.hospital_name') }}</span>
                        </span>
                    </a>

                    <div class="flex items-center gap-1.5">
                        @if ($user->role->value === 'medical_staff')
                            <a href="{{ route('tugas.index') }}" class="rounded-xl px-3.5 py-2 text-sm font-semibold {{ request()->routeIs('tugas.*') ? 'bg-brand-50 text-brand-700' : 'text-stone-600 hover:bg-stone-100' }}">
                                Tugas Saya
                            </a>
                        @endif
                        @if ($user->isOperational())
                            <a href="{{ route('operasional.dashboard') }}" class="rounded-xl px-3.5 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-100">
                                Operasional
                            </a>
                        @endif

                        <a href="{{ route('akun.notifikasi.index') }}" class="relative rounded-xl p-2.5 text-stone-500 hover:bg-stone-100 transition" aria-label="Notifikasi">
                            <x-icon name="bell" class="w-5.5 h-5.5" />
                            @php $unread = $user->unreadNotifications()->limit(50)->count(); @endphp
                            @if ($unread > 0)
                                <span class="absolute top-1.5 right-1.5 min-w-4.5 h-4.5 px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center">
                                    {{ $unread > 9 ? '9+' : $unread }}
                                </span>
                            @endif
                        </a>

                        <div x-data="{ open: false }" class="relative">
                            <button type="button" x-on:click="open = !open" class="flex items-center gap-2 rounded-xl p-1.5 pr-3 hover:bg-stone-100 transition" aria-expanded="false" x-bind:aria-expanded="open" aria-label="Menu akun">
                                <span class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-sm">
                                    {{ mb_substr($user->name, 0, 1) }}
                                </span>
                                <x-icon name="chevron-down" class="w-4 h-4 text-stone-400 hidden sm:block" />
                            </button>

                            <div x-show="open" x-cloak x-on:click.outside="open = false" x-transition
                                 class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-pop ring-1 ring-stone-200 py-2 z-50">
                                <div class="px-4 py-2.5 border-b border-stone-100">
                                    <p class="font-bold text-stone-900 text-sm truncate">{{ $user->name }}</p>
                                    <p class="text-xs text-stone-500 truncate">{{ $user->email }}</p>
                                    <x-badge color="teal" class="mt-1.5">{{ $user->role->label() }}</x-badge>
                                </div>
                                <a href="{{ route('akun.profil.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-stone-700 hover:bg-stone-50">
                                    <x-icon name="user" class="w-4.5 h-4.5 text-stone-400" /> Profil Saya
                                </a>
                                @if ($isStaff)
                                    <a href="{{ route('tugas.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-stone-700 hover:bg-stone-50">
                                        <x-icon name="clipboard" class="w-4.5 h-4.5 text-stone-400" /> Tugas Saya
                                    </a>
                                @else
                                    <a href="{{ route('akun.pasien.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-stone-700 hover:bg-stone-50">
                                        <x-icon name="users" class="w-4.5 h-4.5 text-stone-400" /> Data Pasien
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-rose-600 hover:bg-rose-50">
                                        <x-icon name="logout" class="w-4.5 h-4.5" /> Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 pb-bnav sm:pb-10">
            <div class="container-narrow py-6 sm:py-8">
                @yield('content')
            </div>
        </main>

        {{-- Bottom navigation (mobile) — target utama pengguna ponsel --}}
        <nav class="sm:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur border-t border-stone-200 pb-[env(safe-area-inset-bottom)]" aria-label="Navigasi bawah">
            <div class="grid {{ $isStaff ? 'grid-cols-3' : 'grid-cols-4' }}">
                @php
                    $navItems = $isStaff
                        ? [
                            ['label' => 'Tugas', 'route' => 'tugas.index', 'match' => 'tugas.*', 'icon' => 'clipboard'],
                            ['label' => 'Notifikasi', 'route' => 'akun.notifikasi.index', 'match' => 'akun.notifikasi.*', 'icon' => 'bell'],
                            ['label' => 'Profil', 'route' => 'akun.profil.edit', 'match' => 'akun.profil.*', 'icon' => 'user'],
                        ]
                        : [
                            ['label' => 'Beranda', 'route' => 'akun.dashboard', 'match' => 'akun.dashboard', 'icon' => 'home'],
                            ['label' => 'Riwayat', 'route' => 'akun.pengajuan.index', 'match' => 'akun.pengajuan.*', 'icon' => 'list'],
                            ['label' => 'Notifikasi', 'route' => 'akun.notifikasi.index', 'match' => 'akun.notifikasi.*', 'icon' => 'bell'],
                            ['label' => 'Profil', 'route' => 'akun.profil.edit', 'match' => 'akun.profil.*', 'icon' => 'user'],
                        ];
                @endphp
                @foreach ($navItems as $index => $item)
                    @if ($item['route'] === 'akun.profil.edit' && $user->role->value !== 'medical_staff')
                        {{-- Sisipkan CTA pesan di tengah untuk pasien --}}
                    @endif
                    <a href="{{ route($item['route']) }}"
                       class="flex flex-col items-center justify-center gap-0.5 py-2.5 min-h-14 text-[11px] font-semibold transition {{ request()->routeIs($item['match']) ? 'text-brand-700' : 'text-stone-400 hover:text-stone-600' }}"
                       @if(request()->routeIs($item['match'])) aria-current="page" @endif>
                        <x-icon :name="$item['icon']" class="w-6 h-6" />
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
        </nav>

        {{-- Sticky CTA pesan homecare untuk pasien (mobile) --}}
        @if ($user->role->value === 'patient' && ! request()->routeIs('akun.pesan.*'))
            <div class="sm:hidden fixed bottom-[4.75rem] inset-x-0 z-30 px-4 pb-2 pointer-events-none">
                <a href="{{ route('akun.pesan.step', 'pasien') }}"
                   class="pointer-events-auto flex items-center justify-center gap-2 w-full rounded-2xl bg-brand-600 text-white font-bold py-3.5 shadow-pop active:scale-[0.98] transition">
                    <x-icon name="plus" class="w-5 h-5" />
                    Pesan Homecare
                </a>
            </div>
        @endif
    </div>
@endsection
