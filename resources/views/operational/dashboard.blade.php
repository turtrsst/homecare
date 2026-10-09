@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-6 space-y-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-soeradji-600">Soeradji Care</p>
                <h1 class="mt-2 text-2xl font-black text-stone-900 sm:text-3xl">
                    Halo, {{ $user->firstName() }} 👋
                </h1>
            </div>

            <a href="{{ route('akun.pesan.step', 'pasien') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-soeradji-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-soeradji-600/20 transition hover:bg-soeradji-700">
                <x-icon name="plus" class="h-4 w-4" />
                Pesan Homecare
            </a>
        </div>

        <div class="rounded-[28px] bg-gradient-to-r from-soeradji-700 via-soeradji-600 to-medical-500 p-5 text-white shadow-pop sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-soeradji-50">
                        <x-icon name="sparkles" class="h-3.5 w-3.5" />
                        AI booking assistant
                    </p>
                    <h2 class="mt-3 text-2xl font-black leading-tight">Butuh layanan rumah sakit tanpa antre?</h2>
                    <p class="mt-2 max-w-xl text-sm text-soeradji-50/90">
                        Cukup ketik kebutuhan Anda, lalu sistem akan membantu menyusun pesanan dan jadwal yang paling cocok.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/20 bg-white/10 p-3 backdrop-blur-sm">
                    <p class="text-[10px] uppercase tracking-[0.2em] text-soeradji-100">contoh permintaan</p>
                    <p class="mt-2 max-w-xs text-sm text-white">“Saya butuh perawatan luka untuk ibu, besok pagi di Klaten.”</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-8 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-[0.14em] text-stone-400">Pengajuan</span>
                <span class="rounded-xl bg-soeradji-50 p-2 text-soeradji-600">
                    <x-icon name="clipboard" class="h-4 w-4" />
                </span>
            </div>
            <p class="mt-4 text-3xl font-black text-stone-900">{{ $activeRequest ? 1 : 0 }}</p>
            <p class="mt-1 text-sm text-stone-500">aktif</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-[0.14em] text-stone-400">Jadwal</span>
                <span class="rounded-xl bg-medical-50 p-2 text-medical-600">
                    <x-icon name="calendar" class="h-4 w-4" />
                </span>
            </div>
            <p class="mt-4 text-3xl font-black text-stone-900">{{ $recentRequests->count() }}</p>
            <p class="mt-1 text-sm text-stone-500">riwayat</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-[0.14em] text-stone-400">Status</span>
                <span class="rounded-xl bg-amber-50 p-2 text-amber-600">
                    <x-icon name="activity" class="h-4 w-4" />
                </span>
            </div>
            <p class="mt-4 text-3xl font-black text-stone-900">{{ $activeRequest?->status->label() ?? 'Kosong' }}</p>
            <p class="mt-1 text-sm text-stone-500">terakhir</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-[0.14em] text-stone-400">Kebutuhan</span>
                <span class="rounded-xl bg-rose-50 p-2 text-rose-600">
                    <x-icon name="heart" class="h-4 w-4" />
                </span>
            </div>
            <p class="mt-4 text-3xl font-black text-stone-900">{{ $services->count() }}</p>
            <p class="mt-1 text-sm text-stone-500">layanan tersedia</p>
        </div>
    </div>

    @if ($activeRequest)
        <section class="mb-8" aria-labelledby="active-heading">
            <div class="mb-3 flex items-center justify-between">
                <h2 id="active-heading" class="text-sm font-bold uppercase tracking-[0.15em] text-stone-400">Pengajuan Anda</h2>
                <a href="{{ route('akun.pengajuan.index') }}" class="text-xs font-bold text-soeradji-700 hover:text-soeradji-800">Lihat semua →</a>
            </div>

            <div class="overflow-hidden rounded-[28px] border border-stone-200 bg-white shadow-soft">
                <div class="p-5 sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-soeradji-600">Status aktif</p>
                            <h3 class="mt-2 text-xl font-black text-stone-900">
                                {{ $activeRequest->items->pluck('service_name')->unique()->implode(', ') ?: 'Homecare' }}
                            </h3>
                            <p class="mt-1 text-sm text-stone-500">
                                Untuk <span class="font-semibold text-stone-700">{{ $activeRequest->patient->name }}</span>
                            </p>
                        </div>
                        <x-status-badge :status="$activeRequest->status" />
                    </div>

                    @if ($activeRequest->appointment)
                        <div class="mt-5 rounded-2xl bg-soeradji-50 p-4 ring-1 ring-soeradji-100">
                            <p class="flex items-center gap-2.5 text-sm font-bold text-soeradji-800">
                                <x-icon name="calendar" class="h-5 w-5 text-soeradji-600" />
                                {{ $activeRequest->appointment->scheduled_at->translatedFormat('l, j F Y') }}
                                pukul {{ $activeRequest->appointment->scheduled_at->format('H.i') }} WIB
                            </p>
                            @php $primaryStaff = $activeRequest->appointment->primaryStaff(); @endphp
                            @if ($primaryStaff)
                                <p class="mt-2 flex items-center gap-2.5 text-sm text-soeradji-700">
                                    <x-icon name="stethoscope" class="h-5 w-5 text-soeradji-600" />
                                    <span><span class="font-bold">{{ $primaryStaff->name }}</span> · {{ $primaryStaff->profession->label() }}</span>
                                </p>
                            @endif
                            <p class="mt-2 text-xs text-soeradji-700/80">
                                {{ $activeRequest->appointment->status->patientLabel() }}
                            </p>
                        </div>
                    @elseif ($activeRequest->status->value === 'need_information')
                        <div class="mt-5 rounded-2xl bg-amber-50 p-4 text-sm text-amber-800 ring-1 ring-amber-200">
                            <p class="flex items-center gap-2 font-bold">
                                <x-icon name="alert" class="h-5 w-5" /> Kami butuh informasi tambahan
                            </p>
                            <p class="mt-1">{{ $activeRequest->information_request }}</p>
                        </div>
                    @else
                        <div class="mt-5 rounded-2xl bg-stone-50 p-4 text-sm text-stone-600 ring-1 ring-stone-200">
                            <p class="flex items-center gap-2">
                                <x-icon name="clock" class="h-4 w-4" />
                                {{ $activeRequest->status->patientLabel() }} — Anda akan dikabari begitu ada perkembangan.
                            </p>
                        </div>
                    @endif

                    <div class="mt-5">
                        <x-button :href="route('akun.pengajuan.show', $activeRequest->code)" variant="soft" full>
                            Lihat detail pengajuan
                        </x-button>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="mb-8" aria-labelledby="services-heading">
        <div class="mb-3 flex items-center justify-between">
            <h2 id="services-heading" class="text-sm font-bold uppercase tracking-[0.15em] text-stone-400">Layanan kami</h2>
            <a href="{{ route('services.index') }}" class="text-xs font-bold text-soeradji-700 hover:text-soeradji-800">Semua →</a>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($services->take(6) as $service)
                <a href="{{ route('akun.pesan.step', 'layanan') }}"
                   class="service-card-hover rounded-[24px] border border-stone-200 bg-white p-4 shadow-soft hover:border-soeradji-200">
                    <div class="flex items-center justify-between gap-3">
                        <div class="h-12 w-12 overflow-hidden rounded-2xl bg-soeradji-50 ring-1 ring-soeradji-100">
                            @if ($service->thumbnailUrl())
                                <img src="{{ $service->thumbnailUrl() }}" alt="{{ $service->name }}" class="h-full w-full object-cover" loading="lazy">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-soeradji-600">
                                    <x-icon name="heart" class="h-5 w-5" />
                                </div>
                            @endif
                        </div>
                        <span class="rounded-full bg-medical-100 px-2 py-1 text-[10px] font-bold text-medical-700">Favorit</span>
                    </div>

                    <p class="mt-4 text-base font-black text-stone-900">{{ $service->name }}</p>
                    <p class="mt-1 text-sm leading-relaxed text-stone-500">
                        {{ ? }}
                    </p>
                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-sm font-extrabold text-soeradji-700">{{ $service->formattedPrice() }}</span>
                        <span class="text-xs font-bold text-soeradji-700">Lihat →</span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    <section aria-labelledby="history-heading">
        <div class="mb-3 flex items-center justify-between">
            <h2 id="history-heading" class="text-sm font-bold uppercase tracking-[0.15em] text-stone-400">Riwayat</h2>
            <a href="{{ route('akun.pengajuan.index') }}" class="text-xs font-bold text-soeradji-700 hover:text-soeradji-800">Lihat semua →</a>
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
