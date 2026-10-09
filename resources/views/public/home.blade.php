@extends('layouts.public')

@section('title', 'AI Assistant Soeradji Care')

@section('content')
    <section class="py-12 sm:py-16">
        <div class="container-app">
            <div class="mx-auto max-w-4xl">
                <div class="mb-8 text-center">
                    <span class="brand-badge">
                        <x-icon name="sparkles" class="h-4 w-4" />
                        AI Assistant Soeradji Care
                    </span>
                    <h1 class="mt-5 text-3xl font-black text-clinic-900 sm:text-5xl">
                        Cukup ketik sekali, lalu kami bantu proses booking-nya.
                    </h1>
                    <p class="mx-auto mt-4 max-w-2xl text-base text-clinic-600 sm:text-lg">
                        Tulis kebutuhan Anda seperti chat biasa, lalu sistem akan menyiapkan layanan, jadwal, dan draft pesanan otomatis.
                    </p>
                </div>

                <div class="rounded-[30px] border border-clinic-200 bg-white p-5 shadow-soft sm:p-8">
                    <form method="POST" action="{{ route('ai-assistant.process') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="message" class="mb-2 block text-sm font-bold text-clinic-700">Ketik kebutuhan Anda</label>
                            <textarea id="message" name="message" rows="5" placeholder="Contoh: Saya butuh perawatan luka untuk ibu saya besok pagi di Klaten."
                                class="w-full rounded-2xl border border-clinic-200 bg-clinic-50 px-4 py-3 text-sm text-clinic-900 placeholder:text-clinic-400 focus:border-soeradji-500 focus:outline-none focus:ring-2 focus:ring-soeradji-100" required></textarea>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="text-sm text-clinic-500">
                                Contoh: <span class="font-semibold text-clinic-700">"Saya butuh perawat untuk ibu, besok pagi di Klaten"</span>
                            </div>
                            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-soeradji-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-soeradji-600/20 transition hover:bg-soeradji-700">
                                <x-icon name="sparkles" class="h-4 w-4" />
                                Proses AI Booking
                            </button>
                        </div>
                    </form>
                </div>

                <div class="mt-8 grid gap-4 md:grid-cols-3">
                    <div class="rounded-[26px] border border-clinic-200 bg-white p-5 shadow-soft">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-soeradji-50 text-soeradji-700">
                            <x-icon name="message-circle" class="h-5 w-5" />
                        </div>
                        <h3 class="mt-4 text-lg font-black text-clinic-900">1. Ketik kebutuhan</h3>
                        <p class="mt-2 text-sm text-clinic-600">Bahasa sehari-hari, tidak perlu pakai form yang panjang.</p>
                    </div>

                    <div class="rounded-[26px] border border-clinic-200 bg-white p-5 shadow-soft">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-medical-50 text-medical-700">
                            <x-icon name="brain" class="h-5 w-5" />
                        </div>
                        <h3 class="mt-4 text-lg font-black text-clinic-900">2. AI membaca maksud</h3>
                        <p class="mt-2 text-sm text-clinic-600">Sistem mengenali layanan, jadwal, dan kebutuhan pasien.</p>
                    </div>

                    <div class="rounded-[26px] border border-clinic-200 bg-white p-5 shadow-soft">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-700">
                            <x-icon name="check-circle" class="h-5 w-5" />
                        </div>
                        <h3 class="mt-4 text-lg font-black text-clinic-900">3. Booking otomatis</h3>
                        <p class="mt-2 text-sm text-clinic-600">Langsung lanjut ke review konfirmasi atau form pemesanan.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

