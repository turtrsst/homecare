@extends('layouts.app')

@section('body')
    @php $user = auth()->user(); @endphp
    <div class="min-h-full bg-stone-50">
        <header class="sticky top-0 z-40 border-b border-stone-200/80 bg-white/85 backdrop-blur-xl">
            <div class="container-app">
                <div class="flex h-16 items-center justify-between gap-4 sm:h-18">
                    <a href="{{ route('akun.dashboard') }}" class="flex items-center gap-3" aria-label="Beranda pasien">
                        <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-2xl bg-soeradji-600 shadow-sm ring-1 ring-soeradji-100">
                            <img src="{{ asset(config('homecare.logo_path')) }}" alt="Logo Soeradji Care" class="h-7 w-7 object-contain">
                        </span>
                        <span class="leading-tight">
                            <span class="block text-sm font-black text-clinic-900">Soeradji Care</span>
                            <span class="block text-[10px] font-bold uppercase tracking-[0.18em] text-soeradji-700">{{ config('homecare.hospital_name') }}</span>
                        </span>
                    </a>

                    <nav class="hidden items-center gap-1 md:flex" aria-label="Navigasi utama pasien">
                        @foreach([
                            ['label' => 'Dashboard', 'route' => 'akun.dashboard'],
                            ['label' => 'Pesanan', 'route' => 'akun.pengajuan.index'],
                            ['label' => 'Layanan', 'route' => 'services.index'],
                            ['label' => 'AI Assistant', 'route' => 'ai-assistant'],
                        ] as $item)
                            <a href="{{ route($item['route']) }}"
                               class="rounded-xl px-3 py-2 text-sm font-semibold transition {{ request()->routeIs($item['route']) ? 'bg-soeradji-50 text-soeradji-700' : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900' }}">
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </nav>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('akun.notifikasi.index') }}" class="relative rounded-xl p-2.5 text-stone-500 transition hover:bg-stone-100" aria-label="Notifikasi">
                            <x-icon name="bell" class="h-5 w-5" />
                            @php $unread = $user->unreadNotifications()->limit(50)->count(); @endphp
                            @if ($unread > 0)
                                <span class="absolute right-1.5 top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">{{ $unread > 9 ? '9+' : $unread }}</span>
                            @endif
                        </a>

                        <a href="{{ route('akun.pesan.step', 'pasien') }}" class="hidden rounded-xl bg-soeradji-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-soeradji-700 sm:inline-flex">
                            Pesan baru
                        </a>

                        <div x-data="{ open: false }" class="relative">
                            <button type="button" x-on:click="open = !open" class="flex items-center gap-2 rounded-xl p-1.5 pr-2 hover:bg-stone-100" aria-expanded="false" x-bind:aria-expanded="open">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-soeradji-100 text-sm font-black text-soeradji-700">
                                    {{ strtoupper(substr((string) $user->name, 0, 1)) }}
                                </span>
                                <x-icon name="chevron-down" class="hidden h-4 w-4 text-stone-400 sm:block" />
                            </button>

                            <div x-show="open" x-cloak x-collapse class="absolute right-0 top-[calc(100%+0.75rem)] w-52 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-pop">
                                <a href="{{ route('akun.profil.index') ?? route('akun.dashboard') }}" class="block px-4 py-3 text-sm font-semibold text-stone-700 hover:bg-stone-50">Profil saya</a>
                                <a href="{{ route('akun.pengajuan.index') }}" class="block px-4 py-3 text-sm font-semibold text-stone-700 hover:bg-stone-50">Riwayat pesanan</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full px-4 py-3 text-left text-sm font-semibold text-rose-600 hover:bg-rose-50">Keluar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="container-app py-6 sm:py-8">
            @yield('content')
        </main>
    </div>
@endsection
