@extends('layouts.public')

@section('title', 'Daftar Layanan Homecare')

@section('content')
    <section class="bg-gradient-to-b from-brand-50 to-white py-12 sm:py-16">
        <div class="container-app">
            <x-section-heading
                title="Layanan Homecare Kami"
                subtitle="Semua layanan dikerjakan oleh tenaga kesehatan {{ config('homecare.hospital_name') }} langsung di rumah Anda. Tarif transparan — tidak ada biaya tersembunyi." />
            <div class="mt-8 max-w-2xl">
                <x-emergency-banner compact />
            </div>
            <x-tariff-note class="mt-4" />
        </div>
    </section>

    <section class="pb-16">
        <div class="container-app" x-data="{ q: '' }">
            <div class="max-w-md">
                <div class="relative">
                    <x-icon name="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400" />
                    <input type="search" x-model.debounce.200ms="q" placeholder="Cari layanan... (mis. luka, USG, fisio)"
                           class="w-full rounded-xl border-0 bg-white py-3 pl-11 pr-4 text-sm shadow-card ring-1 ring-stone-200 placeholder:text-stone-400 focus:ring-2 focus:ring-brand-500" />
                </div>
            </div>

            @php $grouped = $services->groupBy(fn ($s) => $s->category ?: 'Layanan Lainnya'); @endphp

            @forelse ($grouped as $category => $items)
                <div class="mt-10 first:mt-0" x-show="!q || {{ $items->filter(fn ($s) => str_contains(strtolower($s->name.' '.$s->short_description), strtolower('__Q__')))->count() }} > 0">
                    <h2 class="text-lg font-extrabold text-stone-800 flex items-center gap-2">
                        <span class="w-1.5 h-6 rounded-full bg-brand-500" aria-hidden="true"></span>
                        {{ $category }}
                    </h2>
                    <div class="mt-4 grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                        @foreach ($items as $service)
                            <div x-show="!q || '{{ strtolower($service->name.' '.$service->short_description) }}'.includes(q.toLowerCase())">
                                <x-service-card :service="$service" :showCategory="false" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <x-card>
                    <x-empty-state icon="heart" title="Belum ada layanan yang dipublikasikan">
                        Katalog layanan sedang disiapkan. Silakan hubungi kami untuk kebutuhan khusus.
                    </x-empty-state>
                </x-card>
            @endforelse

            <div x-show="q" class="mt-8 text-center text-sm text-stone-400">
                Tidak ada layanan yang cocok dengan pencarian.
            </div>

            <div class="mt-12 rounded-3xl bg-brand-600 text-white p-8 sm:p-10 text-center shadow-pop">
                <h2 class="text-2xl font-extrabold">Butuh layanan yang tidak ada di daftar?</h2>
                <p class="mt-2 text-brand-50 max-w-lg mx-auto">
                    Ceritakan kebutuhan Anda melalui form pengajuan — tim kami akan menilai dan
                    menyiapkan layanan yang paling sesuai.
                </p>
                <div class="mt-6 flex flex-col sm:flex-row justify-center gap-3">
                    <x-button :href="auth()->check() ? route('akun.pesan.step', 'pasien') : route('register')"
                              class="bg-white text-brand-700 hover:bg-brand-50">
                        Pesan Homecare
                    </x-button>
                    <x-button :href="route('contact')" variant="ghost" class="text-white hover:bg-white/10">
                        Hubungi Kami
                    </x-button>
                </div>
            </div>
        </div>
    </section>
@endsection
