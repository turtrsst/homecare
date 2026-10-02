@extends('layouts.public')

@section('title', 'FAQ — Pertanyaan yang Sering Diajukan')

@section('content')
    <section class="bg-gradient-to-b from-brand-50 to-white py-12 sm:py-16">
        <div class="container-narrow">
            <x-section-heading align="center"
                title="Pertanyaan yang Sering Diajukan"
                subtitle="Belum yakin? Mungkin jawabannya ada di sini." />
        </div>
    </section>

    <section class="pb-16">
        <div class="container-narrow">
            <div class="space-y-3" x-data="{ open: 1 }">
                @foreach ($faqs as $index => $faq)
                    <div class="rounded-2xl ring-1 ring-stone-200/70 bg-white overflow-hidden shadow-sm">
                        <h2>
                            <button type="button" x-on:click="open === {{ $index }} ? open = false : open = {{ $index }}"
                                    class="w-full flex items-center justify-between gap-4 px-5 sm:px-6 py-4 text-left min-h-11"
                                    :aria-expanded="open === {{ $index }}" aria-controls="faq-{{ $index }}">
                                <span class="font-bold text-stone-800 text-sm sm:text-base">{{ $faq['q'] }}</span>
                                <x-icon name="chevron-down" class="w-5 h-5 text-stone-400 shrink-0 transition-transform duration-200" ::class="open === {{ $index }} && 'rotate-180'" />
                            </button>
                        </h2>
                        <div x-show="open === {{ $index }}" x-collapse x-cloak id="faq-{{ $index }}">
                            <p class="px-5 sm:px-6 pb-5 text-sm sm:text-base text-stone-500 leading-relaxed">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <x-card class="mt-10 text-center">
                <h2 class="font-extrabold text-stone-900 text-lg">Tidak menemukan jawabannya?</h2>
                <p class="mt-1.5 text-sm text-stone-500">Tim kami siap membantu pada jam kerja.</p>
                <div class="mt-5 flex flex-col sm:flex-row justify-center gap-3">
                    <x-button :href="route('contact')" icon="phone">Hubungi Kami</x-button>
                    <x-button :href="auth()->check() ? route('akun.dashboard') : route('register')" variant="secondary" icon="plus">
                        Pesan Homecare
                    </x-button>
                </div>
            </x-card>
        </div>
    </section>
@endsection
