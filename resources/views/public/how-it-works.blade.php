@extends('layouts.public')

@section('title', 'Cara Kerja')
@section('description', 'Empat langkah memesan homecare di Soeradji Care: ketik kebutuhan, verifikasi, jadwal kunjungan, dan layanan di rumah.')

@section('content')
    <section class="mesh-bg py-14 sm:py-20">
        <div class="container-app text-center">
            <span class="eyebrow">Cara kerja</span>
            <h1 class="mx-auto mt-4 max-w-3xl text-4xl font-extrabold leading-tight sm:text-5xl">Dari satu ketikan, <span class="text-gradient">sampai ke rumah Anda</span></h1>
            <p class="mx-auto mt-5 max-w-2xl text-lg text-clinic-600">Tidak perlu antre atau bolak-balik ke rumah sakit. Prosesnya singkat dan semua tahap terpantau.</p>
        </div>
    </section>

    <section class="py-16">
        <div class="container-app">
            <ol class="relative grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['icon' => 'sparkles', 'title' => 'Ketik kebutuhan Anda', 'text' => 'Tulis atau ucapkan sekali saja: layanan apa, untuk siapa, kapan, dan di mana. Sora menyusun pesanannya.'],
                    ['icon' => 'shield-check', 'title' => 'Verifikasi tim kami', 'text' => 'Koordinator memeriksa kelengkapan data dan melakukan skrining kebutuhan medis Anda.'],
                    ['icon' => 'calendar', 'title' => 'Jadwal dikonfirmasi', 'text' => 'Kami mengonfirmasi tanggal dan jam kunjungan, serta total biaya final, lewat notifikasi.'],
                    ['icon' => 'home', 'title' => 'Layanan di rumah', 'text' => 'Tenaga profesional datang ke alamat Anda. Hasil dan catatan kunjungan tersimpan di akun.'],
                ] as $i => $step)
                    <li class="surface relative p-7">
                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-soeradji-600 to-soeradji-500 text-white shadow-lg shadow-soeradji-600/25">
                            <x-icon :name="$step['icon']" class="h-7 w-7" />
                        </span>
                        <p class="mt-6 text-xs font-extrabold uppercase tracking-widest text-soeradji-600">Langkah {{ $i + 1 }}</p>
                        <h2 class="mt-2 text-xl font-extrabold text-clinic-900">{{ $step['title'] }}</h2>
                        <p class="mt-3 leading-relaxed text-clinic-600">{{ $step['text'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="pb-16">
        <div class="container-app">
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="surface p-8">
                    <h2 class="text-2xl font-extrabold">Yang perlu disiapkan</h2>
                    <ul class="mt-6 space-y-4">
                        @foreach ([
                            'Identitas pasien (KTP, KK, atau kartu berobat)',
                            'Surat rujukan atau hasil pemeriksaan terakhir, bila ada',
                            'Alamat lengkap dan nomor yang bisa dihubungi',
                            'Preferensi hari dan jam kunjungan',
                        ] as $item)
                            <li class="flex items-start gap-3 text-clinic-700"><x-icon name="check-circle" class="mt-0.5 h-5 w-5 shrink-0 text-medical-600" />{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-clinic-900 via-soeradji-900 to-soeradji-700 p-8 text-white">
                    <div class="absolute -right-12 -top-12 h-48 w-48 rounded-full bg-medical-400/20 blur-3xl"></div>
                    <h2 class="relative text-2xl font-extrabold">Gawat darurat? Jangan menunggu.</h2>
                    <p class="relative mt-4 leading-relaxed text-soeradji-100">Homecare tidak untuk kondisi gawat darurat. Hubungi <strong class="text-white">{{ config('homecare.emergency_number') }}</strong> atau datang ke IGD {{ config('homecare.hospital_name') }} (24 jam).</p>
                    <a href="{{ route('ai-assistant') }}" class="btn-primary relative mt-8 !bg-white !text-soeradji-800 hover:!bg-soeradji-50"><x-icon name="sparkles" class="h-5 w-5" /> Mulai pesan lewat Sora</a>
                </div>
            </div>
        </div>
    </section>
@endsection
