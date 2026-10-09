@extends('layouts.public')

@section('title', 'Soeradji Care | Layanan Kesehatan di Rumah')

@section('content')
    <section class="relative overflow-hidden bg-gradient-to-b from-soeradji-50 via-white to-white">
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute -top-20 right-0 h-72 w-72 rounded-full bg-soeradji-200/40 blur-3xl"></div>
            <div class="absolute bottom-8 left-0 h-64 w-64 rounded-full bg-medical-200/50 blur-3xl"></div>
        </div>

        <div class="container-app relative py-12 sm:py-16 lg:py-20">
            <div class="grid items-center gap-10 lg:grid-cols-[1.1fr_0.9fr]">
                <div>
                    <div class="brand-badge">
                        <x-icon name="shield" class="h-4 w-4" />
                        Layanan resmi RSUP Dr. Soeradji Tirtonegoro Klaten
                    </div>

                    <h1 class="mt-6 text-4xl font-black leading-[1.05] text-clinic-900 sm:text-5xl lg:text-6xl">
                        Soeradji Care
                        <span class="block text-soeradji-600">Kesehatan di Rumah, Lebih Mudah.</span>
                    </h1>

                    <p class="mt-5 max-w-xl text-lg leading-relaxed text-clinic-700">
                        Dapatkan layanan dokter, perawat, fisioterapi, dan pemeriksaan di rumah dengan proses yang cepat, aman, dan terpantau.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <x-button :href="auth()->check() ? route('akun.pesan.step', 'pasien') : route('register')" size="lg" icon="plus" class="text-base">
                            Pesan Sekarang
                        </x-button>
                        <x-button :href="route('services.index')" size="lg" variant="secondary" icon="heart">
                            Lihat Layanan
                        </x-button>
                    </div>

                    <div class="mt-8 flex flex-wrap gap-4 text-sm text-clinic-700">
                        <span class="inline-flex items-center gap-2">
                            <x-icon name="check-circle" class="h-5 w-5 text-medical-600" />
                            Tim profesional
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <x-icon name="check-circle" class="h-5 w-5 text-medical-600" />
                            Jadwal terpantau
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <x-icon name="check-circle" class="h-5 w-5 text-medical-600" />
                            Data aman
                        </span>
                    </div>
                </div>

                <div class="relative">
                    <div class="rounded-[30px] border border-white/50 bg-white/80 p-5 shadow-pop backdrop-blur-sm sm:p-6">
                        <div class="absolute inset-x-0 top-0 h-28 rounded-t-[30px] bg-gradient-to-r from-soeradji-600 via-soeradji-500 to-medical-500"></div>

                        <div class="relative">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-soeradji-100">AI Assistant</p>
                                    <h3 class="mt-2 text-xl font-bold text-white">Booking Cepat</h3>
                                </div>
                                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-2.5 py-1 text-[10px] font-bold text-white">
                                    <x-icon name="sparkles" class="h-3.5 w-3.5" />
                                    AI aktif
                                </span>
                            </div>

                            <div class="mt-6 rounded-2xl bg-white/10 p-4 text-white ring-1 ring-white/20 backdrop-blur-sm">
                                <p class="text-sm text-soeradji-50">
                                    “Saya butuh perawatan luka untuk ibu saya, besok pagi di Klaten.”
                                </p>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-white/10 px-2.5 py-1 text-[10px] font-semibold text-white">
                                        <x-icon name="sparkles" class="h-3.5 w-3.5" />
                                        AI memahami kebutuhan
                                    </span>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-white/10 px-2.5 py-1 text-[10px] font-semibold text-white">
                                        <x-icon name="check-circle" class="h-3.5 w-3.5" />
                                        Booking otomatis
                                    </span>
                                </div>
                            </div>

                            <div class="mt-5 space-y-3">
                                <div class="rounded-2xl bg-white p-3 shadow-sm ring-1 ring-clinic-200">
                                    <div class="flex items-center justify-between text-sm">
                                        <p class="font-bold text-clinic-900">Perawatan Luka</p>
                                        <span class="rounded-full bg-medical-100 px-2 py-0.5 text-[10px] font-bold text-medical-700">
                                            Tersedia
                                        </span>
                                    </div>
                                    <div class="mt-2 flex items-center gap-2 text-xs text-clinic-600">
                                        <x-icon name="clock" class="h-4 w-4 text-soeradji-600" />
                                        08.00 – 11.00 WIB
                                    </div>
                                    <div class="mt-1 flex items-center gap-2 text-xs text-clinic-600">
                                        <x-icon name="map-pin" class="h-4 w-4 text-soeradji-600" />
                                        Jl. Merdeka, Klaten
                                    </div>
                                </div>

                                <div class="rounded-2xl bg-white p-3 shadow-sm ring-1 ring-clinic-200">
                                    <div class="flex items-center justify-between text-sm">
                                        <p class="font-bold text-clinic-900">Dokter Umum</p>
                                        <span class="rounded-full bg-soeradji-100 px-2 py-0.5 text-[10px] font-bold text-soeradji-700">
                                            Siap datang
                                        </span>
                                    </div>
                                    <div class="mt-2 flex items-center gap-2 text-xs text-clinic-600">
                                        <x-icon name="stethoscope" class="h-4 w-4 text-soeradji-600" />
                                        Rekomendasi AI
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="mt-5 w-full rounded-2xl bg-clinic-900 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-clinic-900/15">
                                Coba AI Booking
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <div class="stat-card">
                    <div class="flex items-center justify-between">
                        <div class="text-3xl font-black text-soeradji-700">24/7</div>
                        <x-icon name="phone" class="h-6 w-6 text-soeradji-500" />
                    </div>
                    <p class="mt-2 text-sm text-clinic-600">Hotline medis & dukungan</p>
                </div>
                <div class="stat-card">
                    <div class="flex items-center justify-between">
                        <div class="text-3xl font-black text-medical-600">1.200+</div>
                        <x-icon name="heart" class="h-6 w-6 text-medical-500" />
                    </div>
                    <p class="mt-2 text-sm text-clinic-600">Kunjungan selesai</p>
                </div>
                <div class="stat-card">
                    <div class="flex items-center justify-between">
                        <div class="text-3xl font-black text-soeradji-600">4.9/5</div>
                        <x-icon name="star" class="h-6 w-6 text-soeradji-500" />
                    </div>
                    <p class="mt-2 text-sm text-clinic-600">Rating pasien dan keluarga</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 sm:py-20">
        <div class="container-app">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-black text-clinic-900 sm:text-4xl">Layanan unggulan</h2>
                    <p class="mt-2 max-w-2xl text-base text-clinic-600">
                        Solusi kesehatan rumah: perawatan, pemeriksaan, terapi, dan pemantauan yang terjadwal.
                    </p>
                </div>
                <a href="{{ route('services.index') }}" class="text-sm font-bold text-soeradji-700 hover:text-soeradji-800">
                    Lihat semua layanan
                </a>
            </div>

            <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($featuredServices as $service)
                    <div class="service-card-hover rounded-[28px] border border-clinic-200 bg-white p-5 shadow-soft">
                        <div class="rounded-2xl bg-soeradji-50 p-3">
                            <img src="{{ $service->thumbnailUrl() ?? '/images/services/default.svg' }}" alt="{{ $service->name }}" class="h-28 w-full rounded-xl object-cover">
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="text-lg font-bold text-clinic-900">{{ $service->name }}</h3>
                                <span class="rounded-full bg-medical-100 px-2 py-1 text-[10px] font-bold text-medical-700">Popular</span>
                            </div>
                            <p class="mt-2 text-sm leading-relaxed text-clinic-600">
                                {{ $service->description ?? 'Pelayanan kesehatan di rumah yang disesuaikan kebutuhan Anda.' }}
                            </p>
                            <div class="mt-4 flex items-center justify-between">
                                <span class="font-extrabold text-soeradji-700">{{ $service->formattedPrice() }}</span>
                                <a href="{{ route('services.show', $service) }}" class="text-sm font-bold text-soeradji-700">Lihat detail</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-16 sm:py-20 bg-white">
        <div class="container-app">
            <div class="grid gap-10 lg:grid-cols-2">
                <div>
                    <h2 class="text-3xl font-black sm:text-4xl text-clinic-900">Kenapa pasien memilih Soeradji Care?</h2>
                    <ul class="mt-8 space-y-5">
                        <li class="flex gap-4">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-soeradji-50 text-soeradji-700">
                                <x-icon name="home" class="h-5 w-5" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold">Nyaman di rumah</h3>
                                <p class="mt-1 text-sm text-clinic-600">Pelayanan datang langsung ke rumah tanpa antre dan tanpa ribet.</p>
                            </div>
                        </li>
                        <li class="flex gap-4">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-medical-50 text-medical-700">
                                <x-icon name="clock" class="h-5 w-5" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold">Jadwal jelas</h3>
                                <p class="mt-1 text-sm text-clinic-600">Status pemesanan, jadwal, dan petugas tersedia secara real-time.</p>
                            </div>
                        </li>
                        <li class="flex gap-4">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sand-100 text-clinic-700">
                                <x-icon name="shield" class="h-5 w-5" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold">Aman & terjamin</h3>
                                <p class="mt-1 text-sm text-clinic-600">Dokumen pasien dan akses ditangani dengan sistem aman serta audit trail.</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="rounded-[28px] bg-clinic-900 p-7 text-white shadow-panel">
                    <div class="mb-6 flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-soeradji-600">
                            <x-icon name="shield-check" class="h-6 w-6" />
                        </div>
                        <h3 class="text-2xl font-black">Soeradji Care Guarantee</h3>
                    </div>

                    <ul class="space-y-4">
                        <li class="flex gap-3"><x-icon name="check" class="h-5 w-5 text-medical-400" /><span>Tenaga kesehatan terverifikasi dan berlisensi</span></li>
                        <li class="flex gap-3"><x-icon name="check" class="h-5 w-5 text-medical-400" /><span>Proses booking cepat dan mudah</span></li>
                        <li class="flex gap-3"><x-icon name="check" class="h-5 w-5 text-medical-400" /><span>Pembayaran jelas dan transparan</span></li>
                        <li class="flex gap-3"><x-icon name="check" class="h-5 w-5 text-medical-400" /><span>Dokumen dan status dapat dipantau</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <x-emergency-banner />

    <section class="py-16 sm:py-20">
        <div class="container-app max-w-3xl">
            <x-section-heading title="Pertanyaan yang sering ditanyakan" subtitle="Semua informasi yang Anda butuhkan, dari booking sampai pelayanan." />
            <div class="mt-8 space-y-3" x-data="{ open: 0 }">
                @foreach ([
                    ['q' => 'Apakah layanan homecare ini aman?', 'a' => 'Ya, semua tenaga kesehatan divalidasi dan prosesnya terpantau dengan sistem yang aman.'],
                    ['q' => 'Berapa lama proses booking?', 'a' => 'Biasanya hanya beberapa menit setelah data dan kebutuhan Anda lengkap.'],
                    ['q' => 'Apakah bisa dibuat otomatis dengan AI?', 'a' => 'Ya, AI dapat membantu menerjemahkan kebutuhan Anda menjadi form booking yang siap dikonfirmasi.'],
                    ['q' => 'Apakah bisa untuk keluarga?', 'a' => 'Bisa, satu akun dapat mengelola data pasien dari anggota keluarga.'],
                ] as $index => $faq)
                    <div class="overflow-hidden rounded-2xl border border-clinic-200 bg-white">
                        <button type="button" x-on:click="open === {{ $index }} ? open = false : open = {{ $index }}"
                                class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left"
                                :aria-expanded="open === {{ $index }}" aria-controls="faq-home-{{ $index }}">
                            <span class="font-bold">{{ $faq['q'] }}</span>
                            <x-icon name="chevron-down" class="h-5 w-5 text-clinic-400" />
                        </button>
                        <div x-show="open === {{ $index }}" x-collapse x-cloak id="faq-home-{{ $index }}">
                            <p class="px-5 pb-4 text-sm leading-relaxed text-clinic-600">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="pb-16 sm:pb-20">
        <div class="container-app">
            <div class="rounded-[30px] bg-gradient-to-r from-soeradji-600 via-soeradji-500 to-medical-500 p-8 text-center text-white shadow-panel sm:p-12">
                <h2 class="text-3xl font-black sm:text-4xl">Siap mulai pemesanan?</h2>
                <p class="mx-auto mt-3 max-w-2xl text-white/90">
                    Buat janji konsultasi atau booking layanan rumah dengan cepat, aman, dan terpercaya.
                </p>
                <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                    <x-button :href="auth()->check() ? route('akun.pesan.step', 'pasien') : route('register')" size="lg" class="bg-white text-soeradji-700">
                        Pesan Sekarang
                    </x-button>
                    <a href="tel:{{ config('homecare.contact.phone') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/40 px-6 py-3 text-base font-semibold text-white hover:bg-white/10">
                        <x-icon name="phone" class="h-5 w-5" />
                        {{ config('homecare.contact.phone') }}
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
