@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $userName = $user->name ?: 'Pasien';
        $firstName = trim(explode(' ', $userName)[0] ?? 'Pasien');
        $activeRequest = $activeRequest ?? null;
    @endphp

    <div class="mb-6 flex flex-col gap-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-soeradji-600">Dashboard pasien</p>
                <h1 class="mt-2 text-3xl font-black text-clinic-900 sm:text-4xl">Halo, {{ $firstName }} 👋</h1>
            </div>
            <span class="inline-flex items-center gap-2 rounded-full bg-soeradji-50 px-3 py-1.5 text-xs font-bold text-soeradji-700 ring-1 ring-soeradji-100">
                <x-icon name="shield-check" class="h-4 w-4" />
                Akun terverifikasi
            </span>
        </div>

        <p class="text-base text-clinic-600">Apa yang ingin Anda kelola hari ini?</p>
    </div>

    <div class="mb-8 grid gap-4 xl:grid-cols-[1.3fr_0.7fr]">
        <a href="{{ route('akun.pesan.step', 'pasien') }}" class="group block overflow-hidden rounded-[30px] bg-gradient-to-r from-soeradji-600 via-soeradji-500 to-medical-500 p-5 text-white shadow-panel sm:p-6">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.18em] text-soeradji-50">
                        <x-icon name="sparkles" class="h-3.5 w-3.5" />
                        AI & booking
                    </span>
                    <h2 class="mt-4 text-2xl font-black sm:text-3xl">Pesan layanan rumah sakit</h2>
                    <p class="mt-2 max-w-lg text-sm text-soeradji-50">
                        Cukup jelaskan kebutuhan Anda, lalu tim kami akan menyiapkan layanan dan jadwal yang sesuai.
                    </p>
                </div>
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-white transition group-hover:translate-x-1">
                    <x-icon name="arrow-right" class="h-6 w-6" />
                </span>
            </div>
        </a>

        <div class="rounded-[30px] border border-stone-200 bg-white p-5 shadow-card">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-clinic-400">AI Assistant</p>
                <span class="rounded-full bg-medical-100 px-2 py-1 text-[10px] font-bold text-medical-700">Aktif</span>
            </div>
            <p class="mt-4 text-lg font-black text-clinic-900">Butuh bantuan booking?</p>
            <p class="mt-2 text-sm text-clinic-600">Tulis kebutuhanmu seperti chat biasa dan kami bantu buatkan pesanan.</p>
            <a href="{{ route('ai-assistant') }}" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-clinic-900 px-4 py-3 text-sm font-bold text-white hover:bg-clinic-800">
                <x-icon name="sparkles" class="h-4 w-4" />
                Coba AI Assistant
            </a>
        </div>
    </div>

    <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-clinic-400">Booking</span>
                <span class="rounded-xl bg-soeradji-50 p-2 text-soeradji-600"><x-icon name="clipboard" class="h-4 w-4" /></span>
            </div>
            <p class="mt-4 text-3xl font-black text-clinic-900">{{ $activeRequest ? 1 : 0 }}</p>
            <p class="mt-1 text-sm text-clinic-500">Total aktif</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-clinic-400">Jadwal</span>
                <span class="rounded-xl bg-medical-50 p-2 text-medical-600"><x-icon name="calendar" class="h-4 w-4" /></span>
            </div>
            <p class="mt-4 text-3xl font-black text-clinic-900">2</p>
            <p class="mt-1 text-sm text-clinic-500">Hari ini</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-clinic-400">Status</span>
                <span class="rounded-xl bg-amber-50 p-2 text-amber-600"><x-icon name="activity" class="h-4 w-4" /></span>
            </div>
            <p class="mt-4 text-3xl font-black text-clinic-900">1</p>
            <p class="mt-1 text-sm text-clinic-500">Sedang diproses</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-clinic-400">AI</span>
                <span class="rounded-xl bg-indigo-50 p-2 text-indigo-600"><x-icon name="sparkles" class="h-4 w-4" /></span>
            </div>
            <p class="mt-4 text-3xl font-black text-clinic-900">4</p>
            <p class="mt-1 text-sm text-clinic-500">Rekomendasi</p>
        </div>
    </div>

    @if ($activeRequest)
        <section class="mb-8 rounded-[30px] border border-stone-200 bg-white p-5 shadow-card sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-clinic-400">Pengajuan aktif</p>
                    <h2 class="mt-2 text-xl font-black text-clinic-900">{{ $activeRequest->items->pluck('service_name')->unique()->implode(', ') ?: 'Layanan Homecare' }}</h2>
                </div>
                <x-status-badge :status="$activeRequest->status" />
            </div>

            <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_0.8fr]">
                <div class="rounded-2xl bg-clinic-50 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm text-clinic-500">Nomor pengajuan</p>
                            <p class="mt-1 text-base font-black text-clinic-900">{{ $activeRequest->code }}</p>
                        </div>
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-soeradji-700 ring-1 ring-stone-200">{{ $activeRequest->status->label() }}</span>
                    </div>

                    <div class="mt-4 space-y-3 text-sm text-clinic-600">
                        <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-2">
                            <span>Periode layanan</span>
                            <span class="font-semibold text-clinic-800">{{ $activeRequest->visit_date?->translatedFormat('d M Y') ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-2">
                            <span>Lokasi</span>
                            <span class="font-semibold text-clinic-800">{{ $activeRequest->address ?? 'Rumah pasien' }}</span>
                        </div>
                        @if ($activeRequest->appointment)
                            <div class="flex items-center justify-between gap-3">
                                <span>Petugas</span>
                                <span class="font-semibold text-clinic-800">{{ $activeRequest->appointment->staff?->name ?? 'Tim kami' }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="rounded-2xl bg-gradient-to-br from-soeradji-50 to-medical-50 p-4 ring-1 ring-soeradji-100">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-soeradji-600">Next step</p>
                    <h3 class="mt-3 text-lg font-black text-clinic-900">Pantau status secara real-time</h3>
                    <p class="mt-2 text-sm text-clinic-600">Anda akan menerima update jadwal dan konfirmasi dari tim rumah sakit.</p>
                    <a href="{{ route('akun.pengajuan.index') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-soeradji-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-soeradji-700">
                        Lihat detail
                        <x-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>
            </div>
        </section>
    @endif

    <div class="mb-8 grid gap-4 lg:grid-cols-[1.1fr_0.9fr]">
        <section class="rounded-[30px] border border-stone-200 bg-white p-5 shadow-card sm:p-6">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-clinic-400">Layanan unggulan</p>
                    <h2 class="mt-2 text-xl font-black text-clinic-900">Paling sering dipesan</h2>
                </div>
                <a href="{{ route('services.index') }}" class="text-sm font-bold text-soeradji-700 hover:text-soeradji-800">Lihat semua</a>
            </div>

            <div class="space-y-3">
                @foreach (['Home Visit Dokter', 'Perawatan Luka', 'Fisioterapi', 'Pemeriksaan Darah'] as $service)
                    <div class="flex items-center justify-between gap-3 rounded-2xl border border-stone-200 bg-clinic-50 px-4 py-3">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-soeradji-600 shadow-sm ring-1 ring-stone-200">
                                <x-icon name="stethoscope" class="h-5 w-5" />
                            </span>
                            <div>
                                <p class="font-bold text-clinic-900">{{ $service }}</p>
                                <p class="text-xs text-clinic-500">Tersedia hari ini</p>
                            </div>
                        </div>
                        <a href="{{ route('services.index') }}" class="text-sm font-bold text-soeradji-700">Pesan</a>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-[30px] border border-stone-200 bg-white p-5 shadow-card sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-clinic-400">Jadwal saya</p>
                    <h2 class="mt-2 text-xl font-black text-clinic-900">Agenda terdekat</h2>
                </div>
                <span class="rounded-full bg-medical-50 px-2 py-1 text-[10px] font-bold text-medical-700">2 agenda</span>
            </div>

            <div class="mt-5 space-y-3">
                <div class="rounded-2xl bg-medical-50 p-3 ring-1 ring-medical-100">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="font-bold text-clinic-900">Dokter Umum</p>
                            <p class="mt-1 text-xs text-clinic-500">Besok, 09.00 WIB</p>
                        </div>
                        <span class="rounded-full bg-white px-2 py-1 text-[10px] font-bold text-medical-700">Siap</span>
                    </div>
                </div>
                <div class="rounded-2xl bg-soeradji-50 p-3 ring-1 ring-soeradji-100">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="font-bold text-clinic-900">Fisioterapi</p>
                            <p class="mt-1 text-xs text-clinic-500">Jumat, 13.30 WIB</p>
                        </div>
                        <span class="rounded-full bg-white px-2 py-1 text-[10px] font-bold text-soeradji-700">Dijadwalkan</span>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <section class="rounded-[30px] bg-gradient-to-r from-clinic-900 via-clinic-800 to-soeradji-700 p-5 text-white shadow-panel sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-soeradji-200">Butuh bantuan?</p>
                <h2 class="mt-2 text-xl font-black sm:text-2xl">Tim kami siap membantu Anda</h2>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('contact') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-clinic-900 hover:bg-stone-100">
                    <x-icon name="message-circle" class="h-4 w-4" />
                    Hubungi kami
                </a>
                <a href="tel:{{ config('homecare.contact.phone') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/30 bg-white/5 px-4 py-2.5 text-sm font-bold text-white hover:bg-white/10">
                    <x-icon name="phone" class="h-4 w-4" />
                    {{ config('homecare.contact.phone') }}
                </a>
            </div>
        </div>
    </section>
@endsection
