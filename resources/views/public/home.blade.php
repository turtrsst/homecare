@extends('layouts.public')

@section('title', 'Layanan Kesehatan di Rumah, Lebih Mudah')

@section('content')
    {{-- ================= HERO ================= --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-brand-50 via-white to-white">
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-brand-100/50 blur-3xl"></div>
            <div class="absolute top-40 -left-24 w-72 h-72 rounded-full bg-warm-100/40 blur-3xl"></div>
        </div>

        <div class="container-app relative py-14 sm:py-20 lg:py-24">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-white ring-1 ring-brand-200 px-4 py-1.5 text-sm font-semibold text-brand-700 shadow-sm">
                        <x-icon name="shield" class="w-4.5 h-4.5" />
                        Layanan resmi {{ config('homecare.hospital_name') }}
                    </div>

                    <h1 class="mt-5 text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-[1.08] text-stone-900">
                        Layanan Kesehatan di Rumah,
                        <span class="text-brand-600">Lebih Mudah.</span>
                    </h1>

                    <p class="mt-5 text-lg sm:text-xl text-stone-600 leading-relaxed max-w-xl">
                        Dapatkan pelayanan kesehatan dari tenaga profesional rumah sakit
                        tanpa harus datang ke rumah sakit.
                    </p>

                    <div class="mt-8 flex flex-col sm:flex-row gap-3">
                        <x-button :href="auth()->check() ? route('akun.pesan.step', 'pasien') : route('register')" size="lg" icon="plus" class="text-base">
                            Pesan Homecare
                        </x-button>
                        <x-button :href="route('services.index')" size="lg" variant="secondary" icon="heart">
                            Lihat Layanan
                        </x-button>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-3 text-sm text-stone-500">
                        <span class="flex items-center gap-2">
                            <x-icon name="check-circle" class="w-5 h-5 text-brand-600" />
                            Tenaga kesehatan bersertifikat
                        </span>
                        <span class="flex items-center gap-2">
                            <x-icon name="check-circle" class="w-5 h-5 text-brand-600" />
                            Status pesanan terpantau
                        </span>
                        <span class="flex items-center gap-2">
                            <x-icon name="check-circle" class="w-5 h-5 text-brand-600" />
                            Data Anda aman & privat
                        </span>
                    </div>
                </div>

                {{-- Kartu ilustrasi alur --}}
                <div class="relative max-w-md mx-auto lg:mx-0 lg:ml-auto w-full" aria-hidden="true">
                    <div class="absolute inset-0 bg-brand-600/10 rounded-[2.5rem] rotate-3"></div>
                    <div class="relative bg-white rounded-[2rem] shadow-pop ring-1 ring-stone-200/60 p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-stone-400 uppercase tracking-wide">Kunjungan berikutnya</p>
                                <p class="font-extrabold text-stone-900 text-lg">Perawatan Luka</p>
                            </div>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200 px-3 py-1 text-xs font-bold">
                                <x-icon name="calendar" class="w-3.5 h-3.5" /> Terjadwal
                            </span>
                        </div>
                        <div class="rounded-2xl bg-stone-50 ring-1 ring-stone-200/60 p-4 text-sm space-y-2.5">
                            <p class="flex items-center gap-2.5 text-stone-600">
                                <x-icon name="clock" class="w-4.5 h-4.5 text-brand-600 shrink-0" />
                                Besok, 09.00 – 12.00 WIB
                            </p>
                            <p class="flex items-center gap-2.5 text-stone-600">
                                <x-icon name="map-pin" class="w-4.5 h-4.5 text-brand-600 shrink-0" />
                                Jl. Melati No. 12, Klaten
                            </p>
                            <p class="flex items-center gap-2.5 text-stone-600">
                                <x-icon name="stethoscope" class="w-4.5 h-4.5 text-brand-600 shrink-0" />
                                Ns. Siti Rahmawati · Perawat
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-full bg-brand-600 text-white flex items-center justify-center">
                                <x-icon name="check" class="w-4 h-4" />
                            </span>
                            <div class="flex-1 h-1.5 rounded-full bg-stone-100 overflow-hidden">
                                <div class="h-full w-3/4 rounded-full bg-brand-500"></div>
                            </div>
                            <span class="text-xs font-semibold text-stone-400">Tahap 3/4</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Peringatan kegawatdaruratan --}}
            <div class="mt-12 max-w-3xl">
                <x-emergency-banner />
            </div>
        </div>
    </section>

    {{-- ================= LAYANAN UNGGULAN ================= --}}
    <section class="py-16 sm:py-20">
        <div class="container-app">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <x-section-heading
                    title="Layanan Unggulan"
                    subtitle="Pelayanan yang paling sering dibutuhkan keluarga Indonesia — dikerjakan oleh tenaga profesional rumah sakit." />
                <a href="{{ route('services.index') }}" class="text-sm font-bold text-brand-700 hover:text-brand-800 flex items-center gap-1 shrink-0">
                    Lihat semua layanan <x-icon name="chevron-right" class="w-4 h-4" />
                </a>
            </div>

            <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                @forelse ($featuredServices as $service)
                    <x-service-card :service="$service" featured />
                @empty
                    @foreach ($services->take(4) as $service)
                        <x-service-card :service="$service" />
                    @endforeach
                @endforelse
            </div>
        </div>
    </section>

    {{-- ================= CARA KERJA ================= --}}
    <section class="py-16 sm:py-20 bg-white border-y border-stone-200/70">
        <div class="container-app">
            <x-section-heading align="center"
                title="Cara Kerjanya Sederhana"
                subtitle="Empat langkah tanpa ribet. Kami kabari Anda di setiap tahapnya." />

            <div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach ([
                    ['icon' => 'clipboard', 'title' => '1. Ajukan Kebutuhan', 'desc' => 'Ceritakan kondisi pasien dan pilih layanan dalam beberapa langkah singkat.'],
                    ['icon' => 'check-circle', 'title' => '2. Kami Verifikasi', 'desc' => 'Tim homecare memeriksa pengajuan Anda dan memastikan layanan yang tepat.'],
                    ['icon' => 'calendar', 'title' => '3. Jadwal & Petugas', 'desc' => 'Anda mendapat kepastian jadwal, nama petugas, dan rincian biaya.'],
                    ['icon' => 'heart', 'title' => '4. Pelayanan di Rumah', 'desc' => 'Petugas datang tepat waktu, melayani dengan ramah, dan mendokumentasikan semuanya.'],
                ] as $step)
                    <div class="relative text-center sm:text-left">
                        <div class="mx-auto sm:mx-0 w-14 h-14 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center ring-1 ring-brand-100">
                            <x-icon :name="$step['icon']" class="w-7 h-7" />
                        </div>
                        <h3 class="mt-4 font-bold text-stone-900">{{ $step['title'] }}</h3>
                        <p class="mt-1.5 text-sm text-stone-500 leading-relaxed">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-12 text-center">
                <x-button :href="route('how-it-works')" variant="soft" size="lg">
                    Pelajari lebih detail
                </x-button>
            </div>
        </div>
    </section>

    {{-- ================= MANFAAT ================= --}}
    <section class="py-16 sm:py-20">
        <div class="container-app grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <x-section-heading
                    title="Kenapa Keluarga Memilih Homecare?"
                    subtitle="Dirancang untuk mengurangi beban keluarga, bukan menambahnya." />

                <ul class="mt-8 space-y-5">
                    @foreach ([
                        ['icon' => 'home', 'title' => 'Nyaman di rumah sendiri', 'desc' => 'Pemulihan sering kali lebih cepat dan tenang di lingkungan yang familiar.'],
                        ['icon' => 'ambulance', 'title' => 'Tanpa antre & perjalanan', 'desc' => 'Tidak perlu menembus macet atau menunggu lama di rumah sakit — terutama untuk lansia dan pasien pasca-rawat inap.'],
                        ['icon' => 'users', 'title' => 'Keluarga tetap bisa bekerja', 'desc' => 'Satu orang tidak perlu mengorbankan pekerjaan untuk mengantar berobat.'],
                        ['icon' => 'info', 'title' => 'Semua jelas dan terpantau', 'desc' => 'Status pengajuan, jadwal, nama petugas, dan biaya terlihat transparan di satu tempat.'],
                    ] as $benefit)
                        <li class="flex gap-4">
                            <div class="w-11 h-11 rounded-xl bg-white ring-1 ring-stone-200 text-brand-600 flex items-center justify-center shrink-0 shadow-sm">
                                <x-icon :name="$benefit['icon']" class="w-5.5 h-5.5" />
                            </div>
                            <div>
                                <h3 class="font-bold text-stone-900">{{ $benefit['title'] }}</h3>
                                <p class="mt-0.5 text-sm text-stone-500 leading-relaxed">{{ $benefit['desc'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Keamanan --}}
            <div class="rounded-3xl bg-stone-900 text-white p-8 sm:p-10 shadow-pop">
                <div class="w-12 h-12 rounded-2xl bg-brand-600 flex items-center justify-center">
                    <x-icon name="shield" class="w-6 h-6" />
                </div>
                <h3 class="mt-5 text-2xl font-extrabold">Data kesehatan Anda dijaga serius</h3>
                <p class="mt-3 text-stone-300 leading-relaxed text-sm sm:text-base">
                    Dokumen seperti KTP, surat rujukan, dan foto kondisi disimpan di penyimpanan privat
                    yang tidak bisa diakses publik. Setiap akses dicatat, dan hanya pihak berwenang yang
                    dapat melihatnya.
                </p>
                <ul class="mt-6 space-y-3 text-sm">
                    @foreach ([
                        'Dokumen disimpan di penyimpanan privat terenkripsi',
                        'Akses dokumen tercatat di audit trail',
                        'Petugas hanya melihat data pasien yang menjadi tugasnya',
                        'Tidak ada penjualan data ke pihak ketiga',
                    ] as $point)
                        <li class="flex items-start gap-2.5 text-stone-200">
                            <x-icon name="check" class="w-4.5 h-4.5 mt-0.5 text-brand-400 shrink-0" />
                            {{ $point }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- ================= TENAGA KESEHATAN ================= --}}
    <section class="py-16 sm:py-20 bg-white border-y border-stone-200/70">
        <div class="container-app">
            <x-section-heading align="center"
                title="Ditangani Tenaga Kesehatan Rumah Sakit"
                subtitle="Dokter, perawat, bidan, fisioterapis, dan tenaga kesehatan lain yang terlatih serta memiliki izin praktik." />

            <div class="mt-10 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach (\App\Enums\Profession::cases() as $profession)
                    @if ($profession !== \App\Enums\Profession::Other)
                        <div class="rounded-2xl ring-1 ring-stone-200/70 bg-stone-50 p-4 text-center">
                            <div class="mx-auto w-10 h-10 rounded-xl bg-white text-brand-600 flex items-center justify-center shadow-sm ring-1 ring-stone-200/60">
                                <x-icon name="stethoscope" class="w-5 h-5" />
                            </div>
                            <p class="mt-2.5 text-xs sm:text-sm font-bold text-stone-700">{{ $profession->label() }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= FAQ RINGKAS ================= --}}
    <section class="py-16 sm:py-20">
        <div class="container-app max-w-3xl">
            <x-section-heading align="center" title="Pertanyaan yang Sering Diajukan" />

            <div class="mt-8 space-y-3" x-data="{ open: 0 }">
                @foreach ([
                    ['q' => 'Apakah ini layanan darurat?', 'a' => 'Bukan. Untuk kondisi gawat darurat, segera hubungi '.config('homecare.emergency_number').' atau IGD '.config('homecare.hospital_name').' di '.config('homecare.contact.phone').'.'],
                    ['q' => 'Berapa lama proses verifikasi pengajuan?', 'a' => 'Pada jam kerja biasanya beberapa jam. Anda akan menerima notifikasi setiap status pengajuan berubah.'],
                    ['q' => 'Bagaimana saya tahu siapa yang akan datang?', 'a' => 'Setelah jadwal ditetapkan, nama dan profesi petugas muncul di halaman detail pengajuan Anda.'],
                    ['q' => 'Bisakah memesan untuk orang tua atau anak?', 'a' => 'Bisa. Satu akun dapat menyimpan beberapa profil pasien — diri sendiri maupun anggota keluarga.'],
                ] as $index => $faq)
                    <div class="rounded-2xl ring-1 ring-stone-200/70 bg-white overflow-hidden">
                        <button type="button" x-on:click="open === {{ $index }} ? open = false : open = {{ $index }}"
                                class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left min-h-11"
                                :aria-expanded="open === {{ $index }}" aria-controls="faq-home-{{ $index }}">
                            <span class="font-bold text-stone-800 text-sm sm:text-base">{{ $faq['q'] }}</span>
                            <x-icon name="chevron-down" class="w-5 h-5 text-stone-400 shrink-0 transition-transform" ::class="open === {{ $index }} && 'rotate-180'" />
                        </button>
                        <div x-show="open === {{ $index }}" x-collapse x-cloak id="faq-home-{{ $index }}">
                            <p class="px-5 pb-4 text-sm text-stone-500 leading-relaxed">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="mt-6 text-center text-sm text-stone-500">
                Masih ada pertanyaan?
                <a href="{{ route('faq') }}" class="font-bold text-brand-700 hover:text-brand-800 underline underline-offset-2">Lihat semua FAQ</a>
                atau <a href="{{ route('contact') }}" class="font-bold text-brand-700 hover:text-brand-800 underline underline-offset-2">hubungi kami</a>.
            </p>
        </div>
    </section>

    {{-- ================= CTA PENUTUP ================= --}}
    <section class="pb-16 sm:pb-20">
        <div class="container-app">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-700 via-brand-600 to-teal-500 p-8 sm:p-14 text-center text-white shadow-pop">
                <div class="absolute -top-20 -right-20 w-72 h-72 rounded-full bg-white/10 blur-2xl" aria-hidden="true"></div>
                <div class="absolute -bottom-24 -left-16 w-80 h-80 rounded-full bg-brand-900/20 blur-2xl" aria-hidden="true"></div>

                <div class="relative">
                    <h2 class="text-3xl sm:text-4xl font-extrabold">Siap memesan layanan di rumah?</h2>
                    <p class="mt-3 text-brand-50 max-w-xl mx-auto text-base sm:text-lg">
                        Butuh beberapa menit saja. Tim kami akan segera menindaklanjuti dan memberi kabar.
                    </p>
                    <div class="mt-8 flex flex-col sm:flex-row justify-center gap-3">
                        <x-button :href="auth()->check() ? route('akun.pesan.step', 'pasien') : route('register')"
                                  size="lg"
                                  class="bg-white text-brand-700 hover:bg-brand-50 shadow-pop">
                            Pesan Homecare Sekarang
                        </x-button>
                        <a href="tel:{{ config('homecare.contact.phone') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-xl px-7 min-h-12 py-3 text-base font-semibold text-white ring-1 ring-white/40 hover:bg-white/10 transition">
                            <x-icon name="phone" class="w-5 h-5" />
                            {{ config('homecare.contact.phone') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
