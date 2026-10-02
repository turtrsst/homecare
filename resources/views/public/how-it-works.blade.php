@extends('layouts.public')

@section('title', 'Cara Kerja Layanan Homecare')

@section('content')
    <section class="bg-gradient-to-b from-brand-50 to-white py-12 sm:py-16">
        <div class="container-narrow">
            <x-section-heading align="center"
                title="Cara Kerja Layanan Homecare"
                subtitle="Dari pengajuan sampai petugas pulang — inilah yang terjadi di setiap tahap, dan apa yang Anda perlu lakukan." />
        </div>
    </section>

    <section class="pb-16">
        <div class="container-narrow space-y-5">
            @foreach ([
                [
                    'icon' => 'clipboard', 'stage' => 'Tahap 1', 'title' => 'Anda mengajukan kebutuhan',
                    'desc' => 'Masuk atau daftar, pilih pasien (boleh anggota keluarga), pilih layanan, ceritakan kondisi, tentukan alamat dan perkiraan waktu kunjungan. Semuanya dalam langkah-langkah singkat dengan panduan jelas.',
                    'you' => 'Isi form pemesanan ± 5 menit.',
                ],
                [
                    'icon' => 'shield', 'stage' => 'Tahap 2', 'title' => 'Tim kami memverifikasi',
                    'desc' => 'Koordinator homecare memeriksa kelengkapan dan kelayakan pengajuan. Bila ada informasi yang kurang, kami akan bertanya lewat halaman pengajuan — Anda cukup menjawab di sana.',
                    'you' => 'Tunggu kabar; jawab bila kami butuh info tambahan.',
                ],
                [
                    'icon' => 'stethoscope', 'stage' => 'Tahap 3', 'title' => 'Skrining & penetapan layanan',
                    'desc' => 'Berdasarkan kondisi pasien, koordinator menetapkan layanan final yang paling sesuai — bisa sama dengan pilihan Anda, bisa disesuaikan agar lebih tepat guna.',
                    'you' => 'Tidak perlu melakukan apa pun.',
                ],
                [
                    'icon' => 'calendar', 'stage' => 'Tahap 4', 'title' => 'Jadwal, petugas, dan biaya dipastikan',
                    'desc' => 'Anda menerima kepastian: kapan petugas datang, siapa namanya dan apa profesinya, serta total biaya yang harus dibayarkan. Status pengajuan berubah menjadi TERJADWAL.',
                    'you' => 'Periksa detail jadwal di halaman pengajuan.',
                ],
                [
                    'icon' => 'home', 'stage' => 'Tahap 5', 'title' => 'Petugas datang & melayani',
                    'desc' => 'Pada hari kunjungan Anda dapat melihat pergerakan status: petugas berangkat, tiba (check-in), melakukan asesmen, melaksanakan tindakan, dan mendokumentasikan pelayanan sebelum pamit (check-out).',
                    'you' => 'Sambut petugas; siapkan dokumen & obat bila ada.',
                ],
                [
                    'icon' => 'check-circle', 'stage' => 'Tahap 6', 'title' => 'Selesai & tindak lanjut',
                    'desc' => 'Setelah pelayanan selesai, catatan tindakan dan anjuran perawatan tersimpan di riwayat Anda. Bila perlu kunjungan lanjutan, koordinator akan mengabari.',
                    'you' => 'Lihat ringkasan pelayanan di riwayat.',
                ],
            ] as $index => $step)
                <x-card class="relative">
                    <div class="flex gap-4 sm:gap-5">
                        <div class="flex flex-col items-center shrink-0">
                            <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center ring-1 ring-brand-100">
                                <x-icon :name="$step['icon']" class="w-6 h-6" />
                            </div>
                            @unless ($loop->last)
                                <span class="w-0.5 flex-1 bg-brand-100 mt-2" aria-hidden="true"></span>
                            @endunless
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wider text-brand-600">{{ $step['stage'] }}</p>
                            <h2 class="mt-0.5 font-extrabold text-stone-900 text-lg">{{ $step['title'] }}</h2>
                            <p class="mt-2 text-sm sm:text-base text-stone-500 leading-relaxed">{{ $step['desc'] }}</p>
                            <p class="mt-3 inline-flex items-center gap-2 rounded-xl bg-warm-50 ring-1 ring-warm-100 px-3 py-2 text-xs sm:text-sm font-semibold text-warm-600">
                                <x-icon name="user" class="w-4 h-4 shrink-0" />
                                Peran Anda: {{ $step['you'] }}
                            </p>
                        </div>
                    </div>
                </x-card>
            @endforeach

            <x-emergency-banner class="mt-8" />

            <div class="text-center pt-2">
                <x-button :href="auth()->check() ? route('akun.pesan.step', 'pasien') : route('register')" size="lg" icon="plus">
                    Pesan Homecare
                </x-button>
            </div>
        </div>
    </section>
@endsection
