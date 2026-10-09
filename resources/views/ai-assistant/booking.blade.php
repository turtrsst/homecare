@extends('layouts.public')

@section('title', 'Soeradji Care | AI Assistant Booking')

@section('content')
    <section class="py-10 sm:py-16">
        <div class="container-narrow">
            <div class="mb-8 text-center">
                <div class="brand-badge justify-center">
                    <x-icon name="sparkles" class="h-4 w-4" />
                    AI Assistant Soeradji Care
                </div>
                <h1 class="mt-6 text-4xl font-black text-clinic-900">Pesan Layanan dengan AI</h1>
                <p class="mt-2 text-lg text-clinic-600">Ceritakan kebutuhan Anda, AI kami akan memproses dan membuat janji untuk Anda.</p>
            </div>

            <div class="glass-panel mx-auto max-w-2xl" x-data="aiBooking()">
                <form @submit.prevent="submitForm">
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-clinic-900">
                            Ceritakan kebutuhan Anda
                        </label>
                        <textarea
                            x-model="userMessage"
                            @keydown.enter.ctrl="submitForm()"
                            placeholder="Contoh: Saya butuh perawatan luka untuk ibu saya, besok pagi di Klaten, atau Anak saya mau vaksin DPT hari ini sore..."
                            class="mt-2 w-full rounded-2xl border border-clinic-200 bg-white px-4 py-3 text-sm text-clinic-900 placeholder:text-clinic-400 focus:border-soeradji-600 focus:outline-none focus:ring-1 focus:ring-soeradji-600"
                            rows="4"
                        ></textarea>
                        <p class="mt-2 text-xs text-clinic-500">💡 Tip: Sebutkan jenis layanan, waktu yang diinginkan, dan lokasi rumah Anda.</p>
                    </div>

                    <button type="submit" class="w-full rounded-2xl bg-soeradji-600 px-4 py-3 text-base font-bold text-white hover:bg-soeradji-700 transition">
                        <span x-show="!loading">Proses dengan AI</span>
                        <span x-show="loading" class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Sedang memproses...
                        </span>
                    </button>
                </form>

                <!-- Result dari AI parsing -->
                <div x-show="showResult && !error" x-cloak class="mt-8 border-t border-clinic-200 pt-8">
                    <h3 class="font-bold text-clinic-900 mb-4">Hasil Parsing AI</h3>

                    <div class="space-y-4">
                        <div class="section-panel">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-xs font-semibold text-clinic-600 uppercase">Layanan yang Diinginkan</p>
                                    <p class="mt-1 text-lg font-bold text-clinic-900" x-text="parsedData.service_name || 'Belum terdeteksi'"></p>
                                </div>
                                <span class="rounded-full px-3 py-1 text-xs font-bold" :class="parsedData.confidence > 0.7 ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'">
                                    Confidence: <span x-text="(parsedData.confidence * 100).toFixed(0)"></span>%
                                </span>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="section-panel">
                                <p class="text-xs font-semibold text-clinic-600 uppercase">Tanggal Diinginkan</p>
                                <p class="mt-1 text-lg font-bold text-clinic-900" x-text="parsedData.preferred_date ? new Date(parsedData.preferred_date).toLocaleDateString('id-ID') : 'Belum terdeteksi'"></p>
                            </div>
                            <div class="section-panel">
                                <p class="text-xs font-semibold text-clinic-600 uppercase">Jam Diinginkan</p>
                                <p class="mt-1 text-lg font-bold text-clinic-900">
                                    <span x-show="parsedData.preferred_time === 'morning'">Pagi (08.00 - 12.00)</span>
                                    <span x-show="parsedData.preferred_time === 'midday'">Siang (12.00 - 16.00)</span>
                                    <span x-show="parsedData.preferred_time === 'afternoon'">Sore (16.00 - 20.00)</span>
                                    <span x-show="!parsedData.preferred_time">Belum terdeteksi</span>
                                </p>
                            </div>
                        </div>

                        <div class="section-panel">
                            <p class="text-xs font-semibold text-clinic-600 uppercase">Lokasi Rumah</p>
                            <p class="mt-1 text-lg font-bold text-clinic-900" x-text="parsedData.location || 'Belum terdeteksi'"></p>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                        <button
                            type="button"
                            @click="showResult = false; userMessage = ''; error = null"
                            class="flex-1 rounded-xl border border-clinic-200 px-4 py-3 font-bold text-clinic-900 hover:bg-clinic-50"
                        >
                            Ubah Input
                        </button>
                        <form method="POST" action="{{ route('ai-assistant.proceed') }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="service_id" :value="parsedData.service_id">
                            <input type="hidden" name="preferred_date" :value="parsedData.preferred_date">
                            <input type="hidden" name="preferred_time" :value="parsedData.preferred_time">
                            <input type="hidden" name="location" :value="parsedData.location">
                            <input type="hidden" name="notes" :value="userMessage">
                            <button
                                type="submit"
                                :disabled="!parsedData.service_id"
                                class="w-full rounded-xl bg-soeradji-600 px-4 py-3 font-bold text-white hover:bg-soeradji-700 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Lanjut ke Booking
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Error message -->
                <div x-show="error" x-cloak class="mt-8 rounded-2xl border border-rose-200 bg-rose-50 p-4">
                    <p class="text-sm font-bold text-rose-900">❌ Terjadi kesalahan</p>
                    <p class="mt-2 text-sm text-rose-700" x-text="error"></p>
                </div>
            </div>

            <!-- Quick tips section -->
            <div class="mx-auto mt-12 max-w-2xl">
                <h3 class="font-bold text-clinic-900">💡 Tips untuk hasil terbaik:</h3>
                <ul class="mt-4 space-y-2 text-sm text-clinic-700">
                    <li class="flex items-start gap-2">
                        <span class="text-soeradji-600">✓</span>
                        Sebutkan jenis layanan (luka, dokter, vaksin, dll)
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-soeradji-600">✓</span>
                        Sebutkan waktu yang diinginkan (besok pagi, hari ini sore, dll)
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-soeradji-600">✓</span>
                        Sebutkan lokasi rumah atau alamat
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-soeradji-600">✓</span>
                        Tambahkan info tambahan jika diperlukan (alergi, kondisi khusus, dll)
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <script>
        function aiBooking() {
            return {
                userMessage: '',
                showResult: false,
                loading: false,
                error: null,
                parsedData: {
                    service_id: null,
                    service_name: null,
                    preferred_date: null,
                    preferred_time: null,
                    location: null,
                    confidence: 0,
                },
                async submitForm() {
                    if (!this.userMessage.trim()) {
                        this.error = 'Silakan ceritakan kebutuhan Anda terlebih dahulu';
                        return;
                    }

                    this.loading = true;
                    this.error = null;

                    try {
                        const response = await fetch('{{ route("ai-assistant.parse") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                message: this.userMessage,
                            }),
                        });

                        const result = await response.json();

                        if (result.success) {
                            this.parsedData = result.data;
                            this.showResult = true;
                        } else {
                            this.error = result.message || 'Gagal memproses input';
                        }
                    } catch (e) {
                        this.error = 'Terjadi kesalahan jaringan. Silakan coba lagi.';
                    } finally {
                        this.loading = false;
                    }
                },
            };
        }
    </script>
@endsection
