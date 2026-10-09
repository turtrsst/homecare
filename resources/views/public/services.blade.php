@extends('layouts.public')

@section('title', 'Katalog Layanan Homecare')
@section('description', 'Katalog layanan homecare resmi RSUP Dr. Soeradji Tirtonegoro Klaten: tindakan medik, laboratorium, rehabilitasi, kunjungan dokter, dan sewa alat.')

@section('content')
    @php
        $categories = $services->pluck('category')->filter()->unique()->values();
        $searchIndex = $services->map(fn ($s) => [
            'category' => (string) $s->category,
            'text' => mb_strtolower($s->name.' '.$s->category.' '.$s->short_description),
        ])->values();
    @endphp

    <section class="mesh-bg border-b border-clinic-200/60 py-12 sm:py-16">
        <div class="container-app">
            <span class="eyebrow">Katalog resmi</span>
            <h1 class="mt-4 text-4xl font-extrabold sm:text-5xl">Layanan homecare</h1>
            <p class="mt-4 max-w-2xl text-lg text-clinic-600">Tarif mengacu pada SK Direktur Utama {{ config('homecare.hospital_name') }}. Tarif tercantum sebelum Anda memesan.</p>

            <div x-data="{
                q: '',
                cat: @js(request('kategori', '')),
                items: @js($searchIndex),
                match(i) {
                    const item = this.items[i];
                    const term = this.q.trim().toLowerCase();
                    return (this.cat === '' || this.cat === item.category) && (term === '' || item.text.includes(term));
                },
                get count() {
                    return this.items.reduce((n, _, i) => n + (this.match(i) ? 1 : 0), 0);
                },
            }" class="mt-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <label class="relative block w-full lg:max-w-md">
                        <span class="sr-only">Cari layanan</span>
                        <x-icon name="search" class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-clinic-400" />
                        <input type="search" x-model="q" placeholder="Cari: luka, kateter, dokter, darah…"
                               class="w-full rounded-2xl border border-clinic-200 bg-white py-3.5 pl-12 pr-4 text-sm shadow-card placeholder:text-clinic-400 focus:border-soeradji-400 focus:outline-none focus:ring-4 focus:ring-soeradji-100">
                    </label>

                    <div class="scroll-thin -mx-4 flex gap-2 overflow-x-auto px-4 pb-1 lg:mx-0 lg:flex-wrap lg:px-0" role="group" aria-label="Filter kategori">
                        <button type="button" @click="cat = ''" :aria-pressed="cat === ''"
                                :class="cat === '' ? 'bg-clinic-900 text-white' : 'bg-white text-clinic-600 ring-1 ring-clinic-200 hover:bg-clinic-50'"
                                class="shrink-0 rounded-full px-4 py-2 text-sm font-bold transition">Semua</button>
                        @foreach ($categories as $category)
                            <button type="button" @click="cat = @js($category)" :aria-pressed="cat === @js($category)"
                                    :class="cat === @js($category) ? 'bg-clinic-900 text-white' : 'bg-white text-clinic-600 ring-1 ring-clinic-200 hover:bg-clinic-50'"
                                    class="shrink-0 rounded-full px-4 py-2 text-sm font-bold transition">{{ $category }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-5 pt-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($services as $i => $service)
                        <article x-show="match({{ $i }})" x-transition.opacity.duration.200ms
                                 class="surface group flex flex-col p-5 transition hover:-translate-y-1 hover:shadow-pop">
                            <div class="flex items-start justify-between gap-3">
                                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-soeradji-50 text-soeradji-700 transition group-hover:bg-soeradji-600 group-hover:text-white">
                                    <x-icon :name="$service->displayIcon()" class="h-6 w-6" />
                                </span>
                                <span class="rounded-full bg-clinic-100 px-2.5 py-1 text-[11px] font-bold text-clinic-600">{{ $service->duration_minutes }} mnt</span>
                            </div>
                            <p class="mt-4 text-[11px] font-bold uppercase tracking-wider text-medical-700">{{ $service->category }}</p>
                            <h2 class="mt-1 text-base font-extrabold leading-snug text-clinic-900">{{ $service->name }}</h2>
                            <p class="mt-2 line-clamp-2 flex-1 text-sm text-clinic-500">{{ $service->short_description }}</p>
                            <div class="mt-5 flex items-center justify-between gap-3 border-t border-clinic-100 pt-4">
                                <div>
                                    <p class="text-[11px] font-semibold text-clinic-400">Tarif</p>
                                    <p class="font-extrabold text-clinic-900">{{ $service->formattedPrice() }}</p>
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ route('services.show', $service) }}" class="rounded-xl px-3 py-2 text-xs font-bold text-clinic-700 ring-1 ring-clinic-200 hover:bg-clinic-50">Detail</a>
                                    <a href="{{ route('ai-assistant', ['tanya' => 'Saya butuh '.mb_strtolower($service->name)]) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-soeradji-600 px-3 py-2 text-xs font-bold text-white hover:bg-soeradji-700">
                                        <x-icon name="sparkles" class="h-3.5 w-3.5" /> Pesan
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($services->isEmpty())
                    <p class="mt-8 rounded-3xl border border-dashed border-clinic-300 p-10 text-center text-clinic-500">Belum ada layanan aktif.</p>
                @else
                    <div x-show="count === 0" x-cloak class="mt-8 rounded-3xl border border-dashed border-clinic-300 p-10 text-center text-clinic-500">
                        Tidak ada layanan yang cocok. Coba kata kunci lain, atau <a href="{{ route('ai-assistant') }}" class="font-bold text-soeradji-700">tanyakan ke Sora</a>.
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
