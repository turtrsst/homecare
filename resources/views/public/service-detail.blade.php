@extends('layouts.public')

@section('title', $service->name)
@section('description', $service->short_description ?: 'Layanan homecare '.$service->name)

@section('content')
    <section class="mesh-bg py-12 sm:py-16">
        <div class="container-app">
            <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm text-clinic-500" aria-label="Breadcrumb">
                <a href="{{ route('services.index') }}" class="hover:text-clinic-800">Layanan</a>
                <x-icon name="chevron-right" class="h-3.5 w-3.5" />
                <a href="{{ route('services.index', ['kategori' => $service->category]) }}" class="hover:text-clinic-800">{{ $service->category }}</a>
                <x-icon name="chevron-right" class="h-3.5 w-3.5" />
                <span class="font-semibold text-clinic-800">{{ \Illuminate\Support\Str::limit($service->name, 40) }}</span>
            </nav>

            <div class="grid gap-8 lg:grid-cols-12">
                <div class="lg:col-span-7">
                    <span class="inline-flex items-center gap-2 rounded-full bg-medical-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-medical-700 ring-1 ring-medical-100">{{ $service->category }}</span>
                    <h1 class="mt-4 text-4xl font-extrabold leading-tight sm:text-5xl">{{ $service->name }}</h1>
                    @if ($service->short_description)
                        <p class="mt-4 text-lg leading-relaxed text-clinic-600">{{ $service->short_description }}</p>
                    @endif

                    <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <div class="surface p-4"><x-icon name="clock" class="h-5 w-5 text-soeradji-600" /><p class="mt-2 text-xs font-semibold text-clinic-500">Durasi kunjungan</p><p class="font-extrabold">{{ $service->duration_minutes }} menit</p></div>
                        <div class="surface p-4"><x-icon name="calendar" class="h-5 w-5 text-soeradji-600" /><p class="mt-2 text-xs font-semibold text-clinic-500">Pemesanan</p><p class="font-extrabold">Minimal H-1</p></div>
                        <div class="surface col-span-2 p-4 sm:col-span-1"><x-icon name="home" class="h-5 w-5 text-soeradji-600" /><p class="mt-2 text-xs font-semibold text-clinic-500">Lokasi</p><p class="font-extrabold">Di rumah pasien</p></div>
                    </div>

                    @if ($service->description)
                        <div class="surface mt-8 p-6 sm:p-8">
                            <h2 class="text-xl font-extrabold">Tentang layanan ini</h2>
                            <div class="prose prose-slate mt-4 max-w-none whitespace-pre-line text-clinic-600">{{ $service->description }}</div>
                        </div>
                    @endif
                </div>

                <aside class="lg:col-span-5">
                    <div class="surface sticky top-28 overflow-hidden">
                        <div class="bg-gradient-to-br from-soeradji-600 to-soeradji-500 p-6 text-white">
                            <p class="text-sm font-semibold text-soeradji-100">Tarif layanan</p>
                            <p class="mt-1 text-4xl font-extrabold">{{ $service->formattedPrice() }}</p>
                            <p class="mt-2 text-xs text-soeradji-100">Sesuai SK Direktur Utama {{ config('homecare.hospital_name') }}.</p>
                        </div>

                        @if ($service->hasTariffBreakdown())
                            <dl class="divide-y divide-clinic-100 px-6 text-sm">
                                <div class="flex justify-between py-3"><dt class="text-clinic-500">Jasa sarana</dt><dd class="font-bold">{{ $service->formattedSarana() }}</dd></div>
                                <div class="flex justify-between py-3"><dt class="text-clinic-500">Jasa pelayanan</dt><dd class="font-bold">{{ $service->formattedPelayanan() }}</dd></div>
                            </dl>
                        @endif

                        <div class="space-y-3 p-6">
                            <a href="{{ route('ai-assistant', ['tanya' => 'Saya butuh '.mb_strtolower($service->name)]) }}" class="btn-primary w-full py-3.5">
                                <x-icon name="sparkles" class="h-5 w-5" /> Pesan lewat Sora
                            </a>
                            <a href="{{ route('register') }}" class="btn-ghost w-full py-3.5">Buat akun & pesan manual</a>
                            <p class="pt-2 text-center text-xs text-clinic-500">Total biaya final dikonfirmasi koordinator setelah skrining.</p>
                        </div>
                    </div>
                </aside>
            </div>

            @if ($relatedServices->isNotEmpty())
                <div class="mt-16">
                    <h2 class="text-2xl font-extrabold">Layanan serupa</h2>
                    <div class="mt-6 grid gap-5 sm:grid-cols-3">
                        @foreach ($relatedServices as $related)
                            <a href="{{ route('services.show', $related) }}" class="surface p-5 transition hover:-translate-y-1 hover:shadow-pop">
                                <p class="text-xs font-bold uppercase tracking-wider text-medical-700">{{ $related->category }}</p>
                                <p class="mt-2 font-extrabold text-clinic-900">{{ $related->name }}</p>
                                <p class="mt-3 text-sm font-bold text-soeradji-700">{{ $related->formattedPrice() }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
