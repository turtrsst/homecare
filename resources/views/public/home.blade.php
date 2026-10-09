@extends('layouts.public')

@section('title', 'Soeradji Care — Homecare resmi RSUP Dr. Soeradji Tirtonegoro Klaten')
@section('description', 'Dokter, perawat, dan fisioterapis datang ke rumah Anda. Pesan cukup dengan satu kalimat lewat Sora, asisten AI Soeradji Care.')

@section('content')
    @php
        $categories = $services->pluck('category')->filter()->unique()->values();
        $serviceCount = \App\Models\HomecareService::active()->count();
        $sampleFeatured = $featuredServices->isNotEmpty() ? $featuredServices : $services->take(4);
    @endphp

    {{-- HERO --}}
    <section class="relative overflow-hidden mesh-bg">
        <div class="pointer-events-none absolute inset-0 grid-dots opacity-60 [mask-image:linear-gradient(to_bottom,black,transparent_85%)]" aria-hidden="true"></div>

        <div class="container-app relative grid items-center gap-12 py-14 sm:py-20 lg:grid-cols-12 lg:py-24">
            <div class="animate-fade-up lg:col-span-6">
                <span class="eyebrow">
                    <x-icon name="badge-check" class="h-4 w-4 text-medical-600" />
                    Layanan resmi {{ config('homecare.hospital_name') }}
                </span>

                <h1 class="mt-6 text-4xl font-extrabold leading-[1.08] text-clinic-900 sm:text-5xl lg:text-6xl">
                    Tenaga kesehatan<br class="hidden sm:block">
                    <span class="text-gradient">datang ke rumah Anda.</span>
                </h1>

                <p class="mt-6 max-w-xl text-lg leading-relaxed text-clinic-600">
                    Cukup ketik atau ucapkan kebutuhan Anda sekali — <strong class="font-bold text-clinic-800">“perawatan luka untuk ibu, besok pagi di Klaten”</strong> — dan Sora akan menyusun serta mengirim pesanan Anda otomatis.
                </p>

                <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('ai-assistant') }}" class="btn-primary px-6 py-4 text-base">
                        <x-icon name="sparkles" class="h-5 w-5" />
                        Pesan dengan Sora
                    </a>
                    <a href="{{ route('services.index') }}" class="btn-ghost px-6 py-4 text-base">
                        Lihat katalog layanan
                        <x-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>

                <ul class="mt-10 grid grid-cols-2 gap-3 text-sm sm:max-w-lg">
                    <li class="flex items-center gap-2 rounded-2xl bg-white/80 px-3 py-2.5 font-semibold text-clinic-700 ring-1 ring-clinic-200/70"><x-icon name="shield-check" class="h-4 w-4 text-medical-600" /> Tarif sesuai SK Direktur</li>
                    <li class="flex items-center gap-2 rounded-2xl bg-white/80 px-3 py-2.5 font-semibold text-clinic-700 ring-1 ring-clinic-200/70"><x-icon name="users" class="h-4 w-4 text-soeradji-600" /> Keluarga bisa diwakili</li>
                    <li class="flex items-center gap-2 rounded-2xl bg-white/80 px-3 py-2.5 font-semibold text-clinic-700 ring-1 ring-clinic-200/70"><x-icon name="bell" class="h-4 w-4 text-soeradji-600" /> Notifikasi tiap tahap</li>
                    <li class="flex items-center gap-2 rounded-2xl bg-white/80 px-3 py-2.5 font-semibold text-clinic-700 ring-1 ring-clinic-200/70"><x-icon name="lock" class="h-4 w-4 text-soeradji-600" /> Data medis terlindungi</li>
                </ul>
            </div>

            {{-- Kartu demo Sora --}}
            <div class="relative lg:col-span-6">
                <div class="absolute -left-6 top-10 hidden h-40 w-40 rounded-full bg-medical-300/30 blur-3xl sm:block" aria-hidden="true"></div>
                <div class="absolute -right-4 bottom-0 h-48 w-48 rounded-full bg-soeradji-300/40 blur-3xl" aria-hidden="true"></div>

                <div class="relative mx-auto max-w-lg">
                    <div class="overflow-hidden rounded-[32px] border border-white/70 bg-white/80 shadow-panel backdrop-blur-xl">
                        <div class="flex items-center gap-3 border-b border-clinic-100 px-5 py-4">
                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-soeradji-600 to-medical-500 text-white"><x-icon name="sparkles" class="h-5 w-5" /></span>
                            <div>
                                <p class="text-sm font-extrabold text-clinic-900">Sora · Asisten Soeradji Care</p>
                                <p class="flex items-center gap-1.5 text-xs font-semibold text-medical-700"><span class="h-2 w-2 rounded-full bg-medical-500"></span> Online</p>
                            </div>
                        </div>

                        <div class="space-y-4 p-5 text-sm">
                            <div class="ml-auto max-w-[85%] rounded-3xl rounded-br-lg bg-soeradji-600 px-4 py-3 text-white shadow-lg shadow-soeradji-600/20">
                                Ibu saya perlu perawatan luka dan cek gula darah, besok pagi di Klaten.
                            </div>
                            <div class="max-w-[90%] rounded-3xl rounded-bl-lg bg-clinic-100 px-4 py-3 text-clinic-800">
                                Siap! Saya siapkan <strong>Perawatan luka sedang</strong> + <strong>GDS</strong> untuk Ibu, besok pagi (08.00–12.00), alamat rumah Klaten.
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-medical-700 ring-1 ring-medical-200">✓ Layanan</span>
                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-medical-700 ring-1 ring-medical-200">✓ Tanggal</span>
                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-medical-700 ring-1 ring-medical-200">✓ Alamat</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 rounded-2xl border border-medical-200 bg-medical-50 px-4 py-3 text-medical-800">
                                <x-icon name="check-circle" class="h-5 w-5 shrink-0" />
                                <p class="text-xs font-semibold">Pesanan terkirim otomatis · Kode <span class="font-extrabold">HC-202610-0001</span></p>
                            </div>
                        </div>
                    </div>

                    <div class="animate-float-slow absolute -bottom-6 -left-4 hidden items-center gap-3 rounded-2xl border border-white bg-white px-4 py-3 shadow-pop sm:flex">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-soeradji-50 text-soeradji-700"><x-icon name="calendar" class="h-5 w-5" /></span>
                        <div>
                            <p class="text-xs font-semibold text-clinic-500">Kunjungan</p>
                            <p class="text-sm font-extrabold text-clinic-900">Besok · Pagi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- STATISTIK (dari data nyata) --}}
    <section class="container-app -mt-6 relative z-10">
        <div class="grid grid-cols-2 gap-3 rounded-[28px] border border-white bg-white/90 p-4 shadow-panel backdrop-blur md:grid-cols-4 md:p-6">
            @foreach ([
                ['value' => $serviceCount.'+', 'label' => 'Layanan homecare', 'icon' => 'heart', 'tone' => 'soeradji'],
                ['value' => $categories->count(), 'label' => 'Kelompok layanan', 'icon' => 'stethoscope', 'tone' => 'medical'],
                ['value' => 'H-1', 'label' => 'Pemesanan minimal sehari sebelumnya', 'icon' => 'calendar', 'tone' => 'soeradji'],
                ['value' => '24 jam', 'label' => 'IGD rumah sakit siaga', 'icon' => 'ambulance', 'tone' => 'medical'],
            ] as $stat)
                <div class="flex items-center gap-3 rounded-2xl px-3 py-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $stat['tone'] === 'medical' ? 'bg-medical-50 text-medical-700' : 'bg-soeradji-50 text-soeradji-700' }}">
                        <x-icon :name="$stat['icon']" class="h-5 w-5" />
                    </span>
                    <div>
                        <p class="text-xl font-extrabold text-clinic-900">{{ $stat['value'] }}</p>
                        <p class="text-xs font-semibold leading-snug text-clinic-500">{{ $stat['label'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- CARA KERJA SORA --}}
    <section class="container-app py-20">
        <div class="mx-auto max-w-2xl text-center">
            <span class="eyebrow"><x-icon name="sparkles" class="h-4 w-4 text-soeradji-600" /> Ketik sekali</span>
            <h2 class="mt-4 text-3xl font-extrabold sm:text-4xl">Dari kalimat biasa menjadi pesanan dalam hitungan detik</h2>
            <p class="mt-4 text-clinic-600">Tidak perlu mengisi formulir panjang. Sora memahami layanan, hari, waktu, dan anggota keluarga yang Anda maksud.</p>
        </div>

        <div class="mt-12 grid gap-5 md:grid-cols-3">
            @foreach ([
                ['n' => '01', 'icon' => 'mic', 'title' => 'Ketik atau ucapkan', 'text' => 'Tulis atau bicara dalam bahasa sehari-hari. Contoh: “ganti kateter untuk ayah, lusa siang”.'],
                ['n' => '02', 'icon' => 'refresh', 'title' => 'Sora melengkapi', 'text' => 'Yang belum jelas ditanyakan singkat dengan pilihan cepat — layanan, pasien, tanggal, dan alamat.'],
                ['n' => '03', 'icon' => 'send', 'title' => 'Terkirim otomatis', 'text' => 'Begitu lengkap, pengajuan langsung masuk antrean verifikasi. Anda diberi kode dan notifikasi.'],
            ] as $step)
                <div class="surface group relative overflow-hidden p-7 transition hover:-translate-y-1 hover:shadow-pop">
                    <span class="absolute right-5 top-4 text-6xl font-black text-clinic-100 transition group-hover:text-soeradji-100">{{ $step['n'] }}</span>
                    <span class="relative flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-soeradji-600 to-medical-500 text-white shadow-lg shadow-soeradji-600/20">
                        <x-icon :name="$step['icon']" class="h-5 w-5" />
                    </span>
                    <h3 class="relative mt-5 text-lg font-extrabold">{{ $step['title'] }}</h3>
                    <p class="relative mt-2 text-sm leading-relaxed text-clinic-600">{{ $step['text'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-10 flex justify-center">
            <a href="{{ route('ai-assistant') }}" class="btn-primary px-7 py-4 text-base">
                <x-icon name="sparkles" class="h-5 w-5" /> Coba sekarang — gratis
            </a>
        </div>
    </section>

    {{-- LAYANAN UNGGULAN --}}
    <section class="bg-white py-20">
        <div class="container-app">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <span class="eyebrow">Layanan unggulan</span>
                    <h2 class="mt-4 text-3xl font-extrabold sm:text-4xl">Yang paling sering dipesan keluarga</h2>
                </div>
                <a href="{{ route('services.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-soeradji-700 hover:text-soeradji-800">
                    Semua layanan <x-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </div>

            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @forelse ($sampleFeatured as $service)
                    <a href="{{ route('services.show', $service) }}" class="surface group flex h-full flex-col p-5 transition hover:-translate-y-1 hover:border-soeradji-200 hover:shadow-pop">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-soeradji-50 text-soeradji-700 transition group-hover:bg-soeradji-600 group-hover:text-white">
                            <x-icon :name="$service->displayIcon()" class="h-6 w-6" />
                        </span>
                        <p class="mt-4 text-xs font-bold uppercase tracking-wider text-medical-700">{{ $service->category }}</p>
                        <h3 class="mt-1 line-clamp-2 text-base font-extrabold text-clinic-900">{{ $service->name }}</h3>
                        <p class="mt-2 line-clamp-2 flex-1 text-sm text-clinic-500">{{ $service->short_description }}</p>
                        <div class="mt-5 flex items-center justify-between border-t border-clinic-100 pt-4 text-sm">
                            <span class="font-extrabold text-clinic-900">{{ $service->formattedPrice() }}</span>
                            <span class="text-clinic-500">{{ $service->duration_minutes }} mnt</span>
                        </div>
                    </a>
                @empty
                    <p class="col-span-full rounded-3xl border border-dashed border-clinic-300 p-10 text-center text-clinic-500">Katalog layanan sedang disiapkan.</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- KATEGORI --}}
    @if ($categories->isNotEmpty())
        <section class="container-app py-20">
            <div class="mb-8 text-center">
                <h2 class="text-3xl font-extrabold sm:text-4xl">Pilih kebutuhan Anda</h2>
                <p class="mt-3 text-clinic-600">Jelajahi berdasarkan kelompok layanan.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($categories as $category)
                    <a href="{{ route('services.index', ['kategori' => $category]) }}" class="flex items-center justify-between gap-4 rounded-3xl border border-clinic-200/80 bg-white p-5 shadow-card transition hover:-translate-y-0.5 hover:border-medical-200 hover:shadow-pop">
                        <div class="flex items-center gap-4">
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-medical-50 text-medical-700"><x-icon name="stethoscope" class="h-6 w-6" /></span>
                            <div>
                                <p class="font-extrabold text-clinic-900">{{ $category }}</p>
                                <p class="text-xs text-clinic-500">{{ $services->where('category', $category)->count() }} layanan</p>
                            </div>
                        </div>
                        <x-icon name="chevron-right" class="h-5 w-5 text-clinic-400" />
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- GAMBAR RUMAH SAKIT + CTA --}}
    <section class="container-app pb-20">
        <div class="relative overflow-hidden rounded-[36px] bg-clinic-900 text-white shadow-panel">
            <img src="{{ asset(config('homecare.hospital_photo')) }}" alt="Gedung {{ config('homecare.hospital_name') }}" class="absolute inset-0 h-full w-full object-cover opacity-30" loading="lazy">
            <div class="absolute inset-0 bg-gradient-to-r from-clinic-900 via-clinic-900/90 to-soeradji-900/70"></div>
            <div class="relative grid items-center gap-8 p-8 sm:p-12 lg:grid-cols-2 lg:p-16">
                <div>
                    <h2 class="text-3xl font-extrabold leading-tight sm:text-4xl">Layanan rumah sakit, rasa rumah.</h2>
                    <p class="mt-4 max-w-lg text-clinic-200">Pesan kunjungan untuk orang tua, anak, atau diri sendiri. Pantau setiap tahap dari verifikasi hingga kunjungan selesai.</p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('ai-assistant') }}" class="btn-primary px-6 py-3.5"><x-icon name="sparkles" class="h-5 w-5" /> Tanya Sora</a>
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white/10 px-6 py-3.5 text-sm font-bold text-white ring-1 ring-white/20 transition hover:bg-white/15">Buat akun gratis</a>
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ([
                        ['icon' => 'shield-check', 'title' => 'Aman & privat', 'text' => 'Dokumen medis disimpan terenkripsi di penyimpanan privat.'],
                        ['icon' => 'activity', 'title' => 'Transparan', 'text' => 'Tarif tercantum sebelum Anda mengirim pesanan.'],
                    ] as $card)
                        <div class="rounded-3xl bg-white/10 p-5 ring-1 ring-white/15 backdrop-blur">
                            <x-icon :name="$card['icon']" class="h-6 w-6 text-medical-300" />
                            <p class="mt-3 font-extrabold">{{ $card['title'] }}</p>
                            <p class="mt-1 text-sm text-clinic-300">{{ $card['text'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-6 flex flex-col gap-3 rounded-3xl border border-rose-200 bg-rose-50 p-5 text-rose-900 sm:flex-row sm:items-center sm:justify-between">
            <p class="flex items-center gap-3 text-sm font-semibold">
                <x-icon name="ambulance" class="h-6 w-6 shrink-0 text-rose-600" />
                Homecare <u>bukan</u> layanan gawat darurat. Sesak berat, nyeri dada, atau pingsan? Segera hubungi {{ config('homecare.emergency_number') }} atau datangi IGD.
            </p>
        </div>
    </section>
@endsection
