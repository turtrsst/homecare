@extends('layouts.public')

@section('title', 'Kontak')
@section('description', 'Hubungi layanan homecare Soeradji Care, RSUP Dr. Soeradji Tirtonegoro Klaten.')

@section('content')
    @php
        $phone = config('homecare.contact.phone');
        $wa = config('homecare.contact.whatsapp');
        $waDigits = $wa ? preg_replace('/\D+/', '', $wa) : null;
    @endphp

    <section class="mesh-bg py-14 sm:py-16">
        <div class="container-app">
            <span class="eyebrow">Kontak</span>
            <h1 class="mt-4 text-4xl font-extrabold sm:text-5xl">Kami siap membantu</h1>
            <p class="mt-4 max-w-2xl text-lg text-clinic-600">Untuk pemesanan tercepat, gunakan Sora. Untuk pertanyaan lain, hubungi kami pada jam layanan.</p>

            <div class="mt-10 grid gap-6 lg:grid-cols-12">
                <div class="grid gap-4 sm:grid-cols-2 lg:col-span-7">
                    <a href="tel:{{ preg_replace('/[^+\d]/', '', (string) $phone) }}" class="surface group p-6 transition hover:-translate-y-1 hover:shadow-pop">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-soeradji-50 text-soeradji-700 group-hover:bg-soeradji-600 group-hover:text-white"><x-icon name="phone" class="h-6 w-6" /></span>
                        <p class="mt-5 text-sm font-semibold text-clinic-500">Telepon</p>
                        <p class="mt-1 font-extrabold text-clinic-900">{{ $phone }}</p>
                    </a>

                    @if ($waDigits)
                        <a href="https://wa.me/{{ $waDigits }}" target="_blank" rel="noopener" class="surface group p-6 transition hover:-translate-y-1 hover:shadow-pop">
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-medical-50 text-medical-700 group-hover:bg-medical-600 group-hover:text-white"><x-icon name="message-circle" class="h-6 w-6" /></span>
                            <p class="mt-5 text-sm font-semibold text-clinic-500">WhatsApp</p>
                            <p class="mt-1 font-extrabold text-clinic-900">Chat koordinator</p>
                        </a>
                    @endif

                    <a href="mailto:{{ config('homecare.contact.email') }}" class="surface group p-6 transition hover:-translate-y-1 hover:shadow-pop">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-clinic-100 text-clinic-700 group-hover:bg-clinic-900 group-hover:text-white"><x-icon name="mail" class="h-6 w-6" /></span>
                        <p class="mt-5 text-sm font-semibold text-clinic-500">Email</p>
                        <p class="mt-1 break-all font-extrabold text-clinic-900">{{ config('homecare.contact.email') }}</p>
                    </a>

                    <div class="surface p-6">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-soeradji-50 text-soeradji-700"><x-icon name="clock" class="h-6 w-6" /></span>
                        <p class="mt-5 text-sm font-semibold text-clinic-500">Jam layanan</p>
                        <p class="mt-1 font-extrabold text-clinic-900">{{ config('homecare.hours') }}</p>
                    </div>

                    <div class="surface p-6 sm:col-span-2">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-soeradji-50 text-soeradji-700"><x-icon name="map-pin" class="h-6 w-6" /></span>
                        <p class="mt-5 text-sm font-semibold text-clinic-500">Alamat</p>
                        <p class="mt-1 font-extrabold leading-relaxed text-clinic-900">{{ config('homecare.contact.address') }}</p>
                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode(config('homecare.hospital_name').', '.config('homecare.contact.address')) }}" target="_blank" rel="noopener"
                           class="mt-4 inline-flex items-center gap-3 rounded-2xl bg-white px-4 py-2.5 text-sm font-bold text-clinic-800 ring-1 ring-clinic-200 transition hover:-translate-y-0.5 hover:shadow-pop">
                            <img src="{{ asset('images/brand/google-maps-logo.png') }}" alt="" class="h-6 w-6 object-contain" loading="lazy">
                            Buka di Google Maps
                        </a>
                        @if (config('homecare.website'))
                            <a href="{{ config('homecare.website') }}" target="_blank" rel="noopener" class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-soeradji-700 hover:underline"><x-icon name="globe" class="h-4 w-4" /> Situs resmi RSUP Dr. Soeradji Tirtonegoro</a>
                        @endif
                    </div>

                    <div class="rounded-3xl border border-rose-200 bg-rose-50 p-6 sm:col-span-2">
                        <p class="flex items-center gap-2 font-extrabold text-rose-800"><x-icon name="alert" class="h-5 w-5" /> Kondisi gawat darurat</p>
                        <p class="mt-2 text-sm leading-relaxed text-rose-700">Jangan gunakan homecare untuk kondisi darurat. Hubungi <strong>{{ config('homecare.emergency_number') }}</strong> atau datang langsung ke IGD (24 jam).</p>
                    </div>
                </div>

                <div class="lg:col-span-5">
                    <div class="relative overflow-hidden rounded-3xl shadow-pop">
                        <img src="{{ asset(ltrim(config('homecare.hospital_photo'), '/')) }}" alt="Gedung {{ config('homecare.hospital_name') }}" class="h-full min-h-[320px] w-full object-cover" loading="lazy">
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-clinic-950/90 to-transparent p-6 text-white">
                            <p class="font-extrabold">{{ config('homecare.hospital_name') }}</p>
                            <p class="text-sm text-soeradji-100">Klaten, Jawa Tengah</p>
                        </div>
                    </div>
                    <a href="{{ route('ai-assistant') }}" class="btn-primary mt-6 w-full py-4"><x-icon name="sparkles" class="h-5 w-5" /> Pesan lewat Sora</a>
                </div>
            </div>
        </div>
    </section>
@endsection
