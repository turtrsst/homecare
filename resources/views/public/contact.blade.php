@extends('layouts.public')

@section('title', 'Kontak Kami')

@section('content')
    <section class="bg-gradient-to-b from-brand-50 to-white py-12 sm:py-16">
        <div class="container-narrow">
            <x-section-heading align="center"
                title="Hubungi Kami"
                subtitle="Ada pertanyaan sebelum memesan? Kami dengan senang hati membantu." />
        </div>
    </section>

    <section class="pb-16">
        <div class="container-narrow grid sm:grid-cols-2 gap-5">
            <x-card class="text-center sm:text-left">
                <div class="mx-auto sm:mx-0 w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center ring-1 ring-brand-100">
                    <x-icon name="phone" class="w-6 h-6" />
                </div>
                <h2 class="mt-4 font-extrabold text-stone-900">Telepon / WhatsApp</h2>
                <p class="mt-1 text-sm text-stone-500">Senin–Jumat, 08.00–17.00 WIB (IGD 24 jam)</p>
                <div class="mt-4 space-y-2 text-sm">
                    <p>
                        <span class="text-stone-400">Telepon:</span>
                        <a href="tel:{{ config('homecare.contact.phone') }}" class="font-bold text-stone-800 hover:text-brand-700 underline underline-offset-2">{{ config('homecare.contact.phone') }}</a>
                    </p>
                    <p>
                        <span class="text-stone-400">WhatsApp:</span>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', config('homecare.contact.whatsapp')) }}" class="font-bold text-stone-800 hover:text-brand-700 underline underline-offset-2">{{ config('homecare.contact.whatsapp') }}</a>
                    </p>
                </div>
            </x-card>

            <x-card class="text-center sm:text-left">
                <div class="mx-auto sm:mx-0 w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center ring-1 ring-brand-100">
                    <x-icon name="mail" class="w-6 h-6" />
                </div>
                <h2 class="mt-4 font-extrabold text-stone-900">Email & Alamat</h2>
                <p class="mt-1 text-sm text-stone-500">Untuk pertanyaan tidak mendesak</p>
                <div class="mt-4 space-y-2 text-sm">
                    <p>
                        <a href="mailto:{{ config('homecare.contact.email') }}" class="font-bold text-stone-800 hover:text-brand-700 underline underline-offset-2">{{ config('homecare.contact.email') }}</a>
                    </p>
                    <p class="text-stone-600">{{ config('homecare.contact.address') }}</p>
                </div>
            </x-card>

            <div class="sm:col-span-2">
                <x-emergency-banner />
            </div>

            <div class="sm:col-span-2 text-center pt-2">
                <x-button :href="auth()->check() ? route('akun.pesan.step', 'pasien') : route('register')" size="lg" icon="plus">
                    Pesan Homecare
                </x-button>
            </div>
        </div>
    </section>
@endsection
