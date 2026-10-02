@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    {{-- Sapaan + CTA utama --}}
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900">
            Halo, {{ $user->firstName() }} 👋
        </h1>
        <p class="mt-1 text-stone-500 text-base sm:text-lg">Apa yang Anda butuhkan hari ini?</p>

        <div class="mt-5">
            <a href="{{ route('akun.pesan.step', 'pasien') }}"
               class="group flex items-center gap-4 rounded-3xl bg-gradient-to-r from-brand-600 to-teal-500 p-5 sm:p-6 text-white shadow-pop transition hover:shadow-lg active:scale-[0.99]">
                <span class="w-14 h-14 rounded-2xl bg-white/15 flex items-center justify-center shrink-0 group-hover:bg-white/25 transition">
                    <x-icon name="stethoscope" class="w-8 h-8" />
                </span>
                <span class="flex-1 min-w-0">
                    <span class="block text-lg sm:text-xl font-extrabold">Pesan Homecare</span>
                    <span class="block text-sm text-brand-50 mt-0.5">Dokter, perawat, fisioterapi & lainnya datang ke rumah</span>
                </span>
                <x-icon name="chevron-right" class="w-6 h-6 shrink-0 opacity-70 group-hover:translate-x-0.5 transition" />
            </a>
        </div>
    </div>

    {{-- Pengajuan aktif --}}
    @if ($activeRequest)
        <section class="mb-8" aria-labelledby="active-heading">
            <h2 id="active-heading" class="text-sm font-bold uppercase tracking-wider text-stone-400 mb-3">Pengajuan Anda</h2>

            <div class="bg-white rounded-3xl ring-1 ring-stone-200/70 shadow-card overflow-hidden">
                <div class="p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="font-extrabold text-stone-900 text-lg">
                                {{ $activeRequest->items->pluck('service_name')->unique()->implode(', ') ?: 'Homecare' }}
                            </h3>
                            <p class="mt-1 text-sm text-stone-500">
                                Untuk <span class="font-semibold text-stone-700">{{ $activeRequest->patient->name }}</span>
                            </p>
                        </div>
                        <x-status-badge :status="$activeRequest->status" />
                    </div>

                    @if ($activeRequest->appointment)
                        <div class="mt-4 rounded-2xl bg-indigo-50/60 ring-1 ring-indigo-100 p-4">
                            <p class="flex items-center gap-2.5 text-sm font-bold text-indigo-900">
                                <x-icon name="calendar" class="w-5 h-5 text-indigo-500" />
                                {{ $activeRequest->appointment->scheduled_at->translatedFormat('l, j F Y') }}
                                pukul {{ $activeRequest->appointment->scheduled_at->format('H.i') }} WIB
                            </p>
                            @php $primaryStaff = $activeRequest->appointment->primaryStaff(); @endphp
                            @if ($primaryStaff)
                                <p class="mt-2 flex items-center gap-2.5 text-sm text-indigo-800">
                                    <x-icon name="stethoscope" class="w-5 h-5 text-indigo-500" />
                                    <span><span class="font-bold">{{ $primaryStaff->name }}</span> · {{ $primaryStaff->profession->label() }}</span>
                                </p>
                            @endif
                            <p class="mt-2 text-xs text-indigo-600">
                                {{ $activeRequest->appointment->status->patientLabel() }}
                            </p>
                        </div>
                    @elseif ($activeRequest->status->value === 'need_information')
                        <div class="mt-4 rounded-2xl bg-orange-50 ring-1 ring-orange-200 p-4 text-sm text-orange-800">
                            <p class="font-bold flex items-center gap-2">
                                <x-icon name="alert" class="w-5 h-5" /> Kami butuh informasi tambahan
                            </p>
                            <p class="mt-1">{{ $activeRequest->information_request }}</p>
                        </div>
                    @else
                        <div class="mt-4 rounded-2xl bg-stone-50 ring-1 ring-stone-200/70 p-4 text-sm text-stone-500">
                            <p class="flex items-center gap-2">
                                <x-icon name="clock" class="w-4.5 h-4.5" />
                                {{ $activeRequest->status->patientLabel() }} — Anda akan dikabari begitu ada perkembangan.
                            </p>
                        </div>
                    @endif

                    <div class="mt-5">
                        <x-button :href="route('akun.pengajuan.show', $activeRequest->code)" variant="soft" full>
                            Lihat Detail Pengajuan
                        </x-button>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Layanan kami --}}
    <section class="mb-8" aria-labelledby="services-heading">
        <div class="flex items-center justify-between mb-3">
            <h2 id="services-heading" class="text-sm font-bold uppercase tracking-wider text-stone-400">Layanan Kami</h2>
            <a href="{{ route('services.index') }}" class="text-xs font-bold text-brand-700 hover:text-brand-800">Semua →</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
            @foreach ($services->take(6) as $service)
                <a href="{{ route('akun.pesan.step', 'layanan') }}"
                   class="bg-white rounded-2xl ring-1 ring-stone-200/70 shadow-card p-4 text-center transition hover:ring-brand-300 hover:shadow-pop">
                    @if ($service->thumbnailUrl())
                        <img src="{{ $service->thumbnailUrl() }}" alt="" aria-hidden="true" loading="lazy"
                             class="mx-auto w-11 h-11 rounded-xl object-cover" />
                    @else
                        <span class="mx-auto w-11 h-11 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                            <x-icon name="{{ $service->displayIcon() }}" class="w-6 h-6" />
                        </span>
                    @endif
                    <p class="mt-2.5 text-sm font-bold text-stone-800 leading-snug">{{ $service->name }}</p>
                    <p class="mt-1 text-xs font-semibold text-brand-700">{{ $service->formattedPrice() }}</p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Riwayat singkat --}}
    <section aria-labelledby="history-heading">
        <div class="flex items-center justify-between mb-3">
            <h2 id="history-heading" class="text-sm font-bold uppercase tracking-wider text-stone-400">Riwayat</h2>
            <a href="{{ route('akun.pengajuan.index') }}" class="text-xs font-bold text-brand-700 hover:text-brand-800">Lihat semua →</a>
        </div>

        @if ($recentRequests->isEmpty())
            <x-card :padding="false">
                <x-empty-state icon="clipboard" title="Belum ada riwayat Homecare"
                               actionHref="{{ route('akun.pesan.step', 'pasien') }}" actionLabel="Pesan Homecare">
                    Setelah Anda melakukan pemesanan, riwayatnya akan muncul di sini.
                </x-empty-state>
            </x-card>
        @else
            <div class="space-y-3">
                @foreach ($recentRequests as $request)
                    <x-request-card :request="$request" />
                @endforeach
            </div>
        @endif
    </section>
@endsection
