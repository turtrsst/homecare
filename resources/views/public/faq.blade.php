@extends('layouts.public')

@section('title', 'Pertanyaan Umum')
@section('description', 'Jawaban atas pertanyaan umum seputar layanan homecare Soeradji Care.')

@section('content')
    <section class="mesh-bg py-14 sm:py-16">
        <div class="container-app grid gap-10 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <span class="eyebrow">FAQ</span>
                <h1 class="mt-4 text-4xl font-extrabold sm:text-5xl">Ada yang ingin ditanyakan?</h1>
                <p class="mt-4 text-lg text-clinic-600">Berikut jawaban untuk pertanyaan yang paling sering kami terima. Belum terjawab? Tanya Sora atau hubungi kami.</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('ai-assistant') }}" class="btn-primary"><x-icon name="sparkles" class="h-5 w-5" /> Tanya Sora</a>
                    <a href="{{ route('contact') }}" class="btn-ghost">Hubungi kami</a>
                </div>
            </div>

            <div class="lg:col-span-7" x-data="{ open: 0 }">
                <div class="space-y-3">
                    @foreach ($faqs as $i => $faq)
                        <div class="surface overflow-hidden">
                            <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}" :aria-expanded="open === {{ $i }}"
                                    class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left sm:px-6 sm:py-5">
                                <span class="font-bold text-clinic-900">{{ $faq['q'] }}</span>
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-clinic-100 text-clinic-700 transition" :class="open === {{ $i }} && 'rotate-180 bg-soeradji-600 text-white'">
                                    <x-icon name="chevron-down" class="h-4 w-4" />
                                </span>
                            </button>
                            <div x-show="open === {{ $i }}" x-collapse x-cloak class="border-t border-clinic-100 px-5 pb-5 pt-4 text-clinic-600 sm:px-6">
                                <p class="leading-relaxed">{{ $faq['a'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
