@extends('layouts.public')

@section('title', 'Soeradji Care | Layanan Kesehatan di Rumah')

@section('content')
    <section class="hero-shell relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute -top-20 right-0 h-72 w-72 rounded-full bg-soeradji-200/50 blur-3xl"></div>
            <div class="absolute bottom-8 left-0 h-64 w-64 rounded-full bg-medical-200/55 blur-3xl"></div>
        </div>

        <div class="container-app relative py-14 sm:py-18 lg:py-20">
            <div class="grid items-center gap-10 lg:grid-cols-[1.08fr_0.92fr]">
                <div>
                    <div class="brand-badge">
                        <x-icon name="shield" class="h-4 w-4" />
                        Layanan resmi RSUP Dr. Soeradji Tirtonegoro Klaten
                    </div>

                    <h1 class="mt-6 text-4xl font-black leading-[1.05] text-clinic-900 sm:text-5xl lg:text-6xl">
                        Soeradji Care
                        <span class="block text-soeradji-600">Kesehatan di Rumah, Tanpa Ribet.</span>
                    </h1>

                    <p class="mt-5 max-w-xl text-lg leading-relaxed text-clinic-700">
                        Dapatkan pelayanan dokter, perawat, fisioterapi, dan pemeriksaan di rumah dengan proses cepat,
                        aman, dan terpantau sesuai standar RSUP Dr. Soeradji Tirtonegoro Klaten.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <x-button :href="auth()->check() ? route('akun.pesan.step', 'pasien') : route('register')" size="lg" icon="plus" class="text-base">
                            Pesan Sekarang
                        </x-button>
                        <x-button :href="route('services.index')" size="lg" variant="secondary" icon="heart">
                            Lihat Layanan
                        </x-button>
                    </div>

                    <div class="mt-8 flex flex-wrap gap-x-6 gap-y-3 text-sm text-clinic-700">
                        <span class="inline-flex items-center gap-2">
                            <x-icon name="check-circle" class="h-5 w-5 text-medical-600" />
                            Tenaga kesehatan bersertifikat
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <x-icon name="check-circle" class="h-5 w-5 text-medical-600" />
                            Jadwal jelas dan terpantau
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <x-icon name="check-circle" class="h-5 w-5 text-medical-600" />
                            Data pasien aman
                        </span>
                    </div>
                </div>

                <div id="ai-booking" class="relative">
                    <div class="glass-panel relative overflow-hidden p-5 sm:p-6">
                        <div class="absolute inset-x-0 top-0 h-28 bg-gradient-to-r from-soeradji-600 via-soeradji-500 to-medical-500"></div>

                        <div class="relative">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-soeradji-100">
                                        AI Assistant
                                    </p>
                                    <h3 class="mt-2 text-xl font-bold text-white">
                                        Booking Cepat
                                    </h3>
                                </div>
                                <span class="ai-badge border-white/30 bg-white/10 text-white">
                                    <x-icon name="sparkles" class="h-4 w-4" />
                                    AI aktif
                                </span>
                            </div>

                            <div class="mt-6 rounded-3xl bg-white/10 p-4 text-white ring-1 ring-white/20 backdrop-blur-sm">
                                <p class="text-sm leading-relaxed text-soeradji-50">
                                    “Saya butuh perawatan luka untuk ibu saya, besok pagi di Klaten.”
                                </p>
                                <div class="mt-4 flex flex-wrap items-center gap-2">
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
                                <div class="section-panel bg-white p-3">
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

                                <div class="section-panel bg-white p-3">
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
            <div class="flex flex-wrap items-end justify-between gap-4">
                <x-section-heading
                    title="Layanan Unggulan"
                    subtitle="Solusi kesehatan rumah yang cepat, aman, dan sesuai kebutuhan pasien." />
                <a href="{{ route('services.index') }}" class="flex items-center gap-1 text-sm font-bold text-soeradji-700 hover:text-soeradji-800">
                    Lihat semua layanan <x-icon name="chevron-right" class="h-4 w-4" />
                </a>
            </div>

            <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                @forelse ($featuredServices as $service)
                    <div class="service-card-hover rounded-[26px] border border-clinic-200 bg-white p-5 shadow-soft">
                        <div class="rounded-2xl bg-soeradji-50 p-3">
                            <img src="{{ $service->thumbnail ?? '/images/services/default.svg' }}" alt="{{ $service->name }}" class="h-28 w-full rounded-xl object-cover">
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
                                <span class="font-extrabold text-soeradji-700">Rp {{ number_format($service->price ?? 0, 0, ',', '.') }}</span>
                                <a href="{{ route('services.show', $service->slug ?? $service->id) }}" class="text-sm font-bold text-soeradji-700">Lihat detail</a>
                            </div>
                        </div>
                    </div>
                @empty
                    @foreach ($services->take(4) as $service)
                        <div class="service-card-hover rounded-[26px] border border-clinic-200 bg-white p-5 shadow-soft">
                            <div class="rounded-2xl bg-soeradji-50 p-3">
                                <img src="{{ $service->thumbnail ?? '/images/services/default.svg' }}" alt="{{ $service->name }}" class="h-28 w-full rounded-xl object-cover">
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
                                    <span class="font-extrabold text-soeradji-700">Rp {{ number_format($service->price ?? 0, 0, ',', '.') }}</span>
                                    <a href="{{ route('services.show', $service->slug ?? $service->id) }}" class="text-sm font-bold text-soeradji-700">Lihat detail</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endforelse
            </div>
        </div>
    </section>

    <section class="py-16 sm:py-20 bg-white">
        <div class="container-app grid gap-12 lg:grid-cols-2 lg:items-center">
            <div>
                <x-section-heading
                    title="Kenapa memilih Soeradji Care?"
                    subtitle="Dirancang untuk membuat perawatan pasien lebih nyaman, teratur, dan tenang di rumah." />

                <ul class="mt-8 space-y-5">
                    @foreach([
                        ['icon' => 'home', 'title' => 'Nyaman di rumah', 'desc' => 'Pemulihan lebih tenang dan nyaman tanpa harus datang ke rumah sakit.'],
                        ['icon' => 'clock', 'title' => 'Jadwal yang jelas', 'desc' => 'Status pemesanan, jadwal, dan petugas bisa dipantau dengan mudah.'],
                        ['icon' => 'shield', 'title' => 'Aman & terpercaya', 'desc' => 'Semua proses dikelola dengan sistem yang terstruktur dan aman.'],
                        ['icon' => 'users', 'title' => 'Mudah untuk keluarga', 'desc' => 'Satu akun bisa mengelola kebutuhan pasien dan anggota keluarga.'],
                    ] as $benefit)
                        <li class="flex gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-soeradji-50 text-soeradji-700 ring-1 ring-soeradji-100 shadow-sm">
                                <x-icon :name="$benefit['icon']" class="h-5 w-5" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-clinic-900">{{ $benefit['title'] }}</h3>
                                <p class="mt-1 text-sm leading-relaxed text-clinic-600">{{ $benefit['desc'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="rounded-[30px] bg-clinic-900 p-8 text-white shadow-panel sm:p-10">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-soeradji-600">
                    <x-icon name="shield" class="h-6 w-6" />
                </div>
                <h3 class="mt-5 text-2xl font-black">Standar pelayanan yang terjaga</h3>
                <p class="mt-3 text-sm leading-relaxed text-clinic-200 sm:text-base">
                    Dokumen kesehatan, kebutuhan pasien, dan akses pelayanan disusun dengan prinsip keamanan, kebersihan,
                    dan akuntabilitas agar keluarga merasa tenang saat menunggu pelayanan di rumah.
                </p>

                <ul class="mt-6 space-y-3 text-sm text-clinic-200">
                    @foreach([
                        'Dokumen disimpan dengan sistem yang aman',
                        'Akses tercatat di audit trail',
                        'Petugas hanya melihat data yang relevan',
                        'Proses jelas dan mudah dipantau',
                    ] as $point)
                        <li class="flex items-start gap-2.5">
                            <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-medical-400" />
                            <span>{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    <section class="py-16 sm:py-20">
        <div class="container-app">
            <x-section-heading
                align="center"
                title="Ditangani tenaga kesehatan rumah sakit"
                subtitle="Dokter, perawat, bidan, fisioterapis, dan tim lainnya yang siap melayani di rumah." />

            <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                @foreach (\App\Enums\Profession::cases() as $profession)
                    @if ($profession !== \App\Enums\Profession::Other)
                        <div class="rounded-2xl border border-clinic-200 bg-white p-4 text-center shadow-soft">
                            <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-soeradji-50 text-soeradji-600 ring-1 ring-soeradji-100 shadow-sm">
                                <x-icon name="stethoscope" class="h-5 w-5" />
                            </div>
                            <p class="mt-2.5 text-xs font-bold text-clinic-700 sm:text-sm">{{ $profession->label() }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    <x-emergency-banner />

    <section class="py-16 sm:py-20">
        <div class="container-app max-w-3xl">
            <x-section-heading align="center" title="Pertanyaan yang sering ditanyakan" />

            <div class="mt-8 space-y-3" x-data="{ open: 0 }">
                @foreach ([
                    ['q' => 'Apakah ini layanan darurat?', 'a' => 'Bukan. Untuk kondisi gawat darurat, segera hubungi '.config('homecare.emergency_number').' atau IGD '.config('homecare.contact.phone').'.'],
                    ['q' => 'Berapa lama proses verifikasi pengajuan?', 'a' => 'Biasanya beberapa jam pada jam kerja. Anda akan menerima notifikasi setiap status berubah.'],
                    ['q' => 'Bagaimana saya tahu siapa yang akan datang?', 'a' => 'Setelah jadwal dibuat, nama dan profesi petugas akan muncul di halaman pengajuan Anda.'],
                    ['q' => 'Bisakah memesan untuk orang tua atau anak?', 'a' => 'Bisa. Satu akun dapat mengelola beberapa profil pasien sekaligus.'],
                ] as $index => $faq)
                    <div class="overflow-hidden rounded-2xl border border-clinic-200 bg-white">
                        <button type="button" x-on:click="open === {{ $index }} ? open = false : open = {{ $index }}"
                                class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left min-h-11"
                                :aria-expanded="open === {{ $index }}" aria-controls="faq-home-{{ $index }}">
                            <span class="text-sm font-bold text-clinic-800 sm:text-base">{{ $faq['q'] }}</span>
                            <x-icon name="chevron-down" class="h-5 w-5 text-clinic-400 transition-transform" ::class="open === {{ $index }} && 'rotate-180'" />
                        </button>
                        <div x-show="open === {{ $index }}" x-collapse x-cloak id="faq-home-{{ $index }}">
                            <p class="px-5 pb-4 text-sm leading-relaxed text-clinic-600">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="mt-6 text-center text-sm text-clinic-600">
                Masih ada pertanyaan?
                <a href="{{ route('faq') }}" class="font-bold text-soeradji-700 underline underline-offset-2 hover:text-soeradji-800">Lihat semua FAQ</a>
                atau <a href="{{ route('contact') }}" class="font-bold text-soeradji-700 underline underline-offset-2 hover:text-soeradji-800">hubungi kami</a>.
            </p>
        </div>
    </section>

    <section class="pb-16 sm:pb-20">
        <div class="container-app">
            <div class="relative overflow-hidden rounded-[32px] bg-gradient-to-r from-soeradji-600 via-soeradji-500 to-medical-500 p-8 text-center text-white shadow-panel sm:p-12">
                <div class="absolute -right-12 -top-12 h-44 w-44 rounded-full bg-white/10 blur-2xl" aria-hidden="true"></div>
                <div class="absolute -bottom-12 -left-12 h-52 w-52 rounded-full bg-soeradji-900/15 blur-2xl" aria-hidden="true"></div>

                <div class="relative">
                    <h2 class="text-3xl font-black sm:text-4xl">Siap memesan layanan di rumah?</h2>
                    <p class="mx-auto mt-3 max-w-2xl text-base text-soeradji-50 sm:text-lg">
                        Buat janji konsultasi atau booking layanan rumah dengan cepat, aman, dan terpercaya.
                    </p>
                    <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                        <x-button :href="auth()->check() ? route('akun.pesan.step', 'pasien') : route('register')" size="lg" class="bg-white text-soeradji-700 hover:bg-soeradji-50 shadow-pop">
                            Pesan Sekarang
                        </x-button>
                        <a href="tel:{{ config('homecare.contact.phone') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/40 px-6 py-3 text-base font-semibold text-white hover:bg-white/10">
                            <x-icon name="phone" class="h-5 w-5" />
                            {{ config('homecare.contact.phone') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
