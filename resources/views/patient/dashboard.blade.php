@extends('layouts.base')

@section('body')
    @php $user = auth()->user(); @endphp
    <div class="min-h-full flex flex-col bg-sand-50">
        <header class="sticky top-0 z-40 border-b border-stone-200/80 bg-white/90 backdrop-blur">
            <div class="container-app">
                <div class="flex h-16 items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('operasional.dashboard') }}" class="flex items-center gap-2.5" aria-label="Beranda operasional">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-soeradji-600 text-white shadow-sm">
                                <x-icon name="heart" class="h-5 w-5" />
                            </span>
                            <span class="leading-tight">
                                <span class="block text-sm font-black text-stone-900">Soeradji Care</span>
                                <span class="block text-[10px] font-semibold text-soeradji-700">Operasional</span>
                            </span>
                        </a>
                    </div>

                    <nav class="hidden items-center gap-1 md:flex" aria-label="Navigasi operasional">
                        @foreach([
                            ['label' => 'Dashboard', 'route' => 'operasional.dashboard', 'icon' => 'home'],
                            ['label' => 'Pengajuan', 'route' => 'operasional.pengajuan.index', 'icon' => 'clipboard'],
                            ['label' => 'Jadwal', 'route' => 'operasional.jadwal.index', 'icon' => 'calendar'],
                            ['label' => 'Pasien', 'route' => 'operasional.pasien.index', 'icon' => 'users'],
                            ['label' => 'Layanan', 'route' => 'operasional.layanan.index', 'icon' => 'heart'],
                            ['label' => 'Petugas', 'route' => 'operasional.petugas.index', 'icon' => 'stethoscope'],
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
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50">
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1">
            <div class="container-app py-6 sm:py-8">
                @yield('content')
            </div>
        </main>
    </div>
@endsection
