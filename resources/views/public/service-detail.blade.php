@extends('layouts.public')

@section('title', $service->name)
@section('description', $service->short_description ?: 'Layanan '.$service->name.' di rumah oleh tenaga profesional '.config('homecare.hospital_name').'.')

@section('content')
    <section class="bg-gradient-to-b from-brand-50 to-white py-10 sm:py-14">
        <div class="container-narrow">
            <nav class="text-sm text-stone-500 flex items-center gap-1.5" aria-label="Breadcrumb">
                <a href="{{ rtrim(route('home'), '/') }}/" class="hover:text-brand-700">Beranda</a>
                <x-icon name="chevron-right" class="w-3.5 h-3.5" />
                <a href="{{ route('services.index') }}" class="hover:text-brand-700">Layanan</a>
                <x-icon name="chevron-right" class="w-3.5 h-3.5" />
                <span class="text-stone-700 font-semibold truncate">{{ $service->name }}</span>
            </nav>

            <div class="mt-6 bg-white rounded-3xl ring-1 ring-stone-200/70 shadow-card p-6 sm:p-9">
                @if ($service->thumbnailUrl())
                    <img src="{{ $service->thumbnailUrl() }}" alt="{{ $service->name }}" decoding="async"
                         class="w-full h-40 sm:h-52 object-cover rounded-2xl mb-6" />
                @endif

                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center ring-1 ring-brand-100 shrink-0">
                            <x-icon name="{{ $service->displayIcon() }}" class="w-7 h-7" />
                        </div>
                        <div>
                            @if ($service->category)
                                <p class="text-xs font-bold uppercase tracking-wider text-brand-600">{{ $service->category }}</p>
                            @endif
                            <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900">{{ $service->name }}</h1>
                        </div>
                    </div>
                    @if ($service->is_featured)
                        <x-badge color="warm" icon="sparkles">Layanan Unggulan</x-badge>
                    @endif
                </div>

                <div class="mt-6 flex flex-wrap gap-x-8 gap-y-3 text-sm">
                    <p class="flex items-center gap-2 text-stone-600">
                        <x-icon name="money" class="w-5 h-5 text-brand-600" />
                        <span class="text-xl font-extrabold text-stone-900">{{ $service->formattedPrice() }}</span>
                        @if ($service->price_note)
                            <span class="text-stone-400">({{ $service->price_note }})</span>
                        @endif
                    </p>
                    <p class="flex items-center gap-2 text-stone-600">
                        <x-icon name="clock" class="w-5 h-5 text-brand-600" />
                        Perkiraan {{ $service->duration_minutes }} menit per kunjungan
                    </p>
                </div>

                @if ($service->hasTariffBreakdown())
                    <div class="mt-4 rounded-2xl bg-stone-50 ring-1 ring-stone-200/70 px-4 py-3 text-sm">
                        <p class="text-xs font-bold uppercase tracking-wider text-stone-400">Rincian tarif</p>
                        <dl class="mt-2 flex flex-wrap gap-x-8 gap-y-1">
                            <div class="flex items-baseline gap-2">
                                <dt class="text-stone-500">Jasa Sarana</dt>
                                <dd class="font-semibold text-stone-800">{{ $service->jasa_sarana > 0 ? $service->formattedSarana() : '—' }}</dd>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <dt class="text-stone-500">Jasa Pelayanan</dt>
                                <dd class="font-semibold text-stone-800">{{ $service->jasa_pelayanan > 0 ? $service->formattedPelayanan() : '—' }}</dd>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <dt class="text-stone-500">Jumlah</dt>
                                <dd class="font-bold text-brand-700">{{ $service->formattedPrice() }}</dd>
                            </div>
                        </dl>
                    </div>
                @endif

                @if ($service->description)
                    <div class="mt-7 prose prose-stone prose-sm sm:prose-base max-w-none">
                        {!! nl2br(e($service->description)) !!}
                    </div>
                @endif

                <x-tariff-note />

                <div class="mt-8 flex flex-col sm:flex-row gap-3">
                    <x-button :href="auth()->check() ? route('akun.pesan.step', 'layanan') : route('register')" size="lg" icon="plus">
                        Pesan Layanan Ini
                    </x-button>
                    <x-button :href="route('contact')" size="lg" variant="secondary" icon="phone">
                        Tanya Dulu
                    </x-button>
                </div>
            </div>

            {{-- Apa yang perlu disiapkan --}}
            <div class="mt-6 grid sm:grid-cols-2 gap-4">
                <x-card>
                    <h2 class="font-bold text-stone-900 flex items-center gap-2">
                        <x-icon name="clipboard" class="w-5 h-5 text-brand-600" /> Yang perlu disiapkan
                    </h2>
                    <ul class="mt-3 space-y-2 text-sm text-stone-600">
                        <li class="flex gap-2"><x-icon name="check" class="w-4 h-4 mt-0.5 text-brand-500 shrink-0" /> Kartu identitas pasien (KTP/KK/KIA)</li>
                        <li class="flex gap-2"><x-icon name="check" class="w-4 h-4 mt-0.5 text-brand-500 shrink-0" /> Surat rujukan atau hasil pemeriksaan terakhir (bila ada)</li>
                        <li class="flex gap-2"><x-icon name="check" class="w-4 h-4 mt-0.5 text-brand-500 shrink-0" /> Obat-obatan yang sedang dikonsumsi</li>
                        <li class="flex gap-2"><x-icon name="check" class="w-4 h-4 mt-0.5 text-brand-500 shrink-0" /> Ruangan yang nyaman dan cukup cahaya untuk pemeriksaan</li>
                    </ul>
                </x-card>
                <x-card>
                    <h2 class="font-bold text-stone-900 flex items-center gap-2">
                        <x-icon name="info" class="w-5 h-5 text-brand-600" /> Yang perlu diketahui
                    </h2>
                    <ul class="mt-3 space-y-2 text-sm text-stone-600">
                        <li class="flex gap-2"><x-icon name="check" class="w-4 h-4 mt-0.5 text-brand-500 shrink-0" /> Kunjungan dilakukan tenaga kesehatan berizin praktik</li>
                        <li class="flex gap-2"><x-icon name="check" class="w-4 h-4 mt-0.5 text-brand-500 shrink-0" /> Biaya final dikonfirmasi koordinator setelah skrining</li>
                        <li class="flex gap-2"><x-icon name="check" class="w-4 h-4 mt-0.5 text-brand-500 shrink-0" /> Setiap tindakan didokumentasikan dan dapat Anda lihat</li>
                        <li class="flex gap-2"><x-icon name="alert" class="w-4 h-4 mt-0.5 text-rose-400 shrink-0" /> Bukan layanan kegawatdaruratan — hubungi {{ config('homecare.emergency_number') }} bila darurat</li>
                    </ul>
                </x-card>
            </div>

            @if ($relatedServices->isNotEmpty())
                <div class="mt-10">
                    <h2 class="text-lg font-extrabold text-stone-800">Layanan sejenis</h2>
                    <div class="mt-4 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($relatedServices as $related)
                            <x-service-card :service="$related" />
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
