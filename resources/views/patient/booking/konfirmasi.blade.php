@extends('patient.booking._layout')

@section('step-content')
    <div class="space-y-5">
        <div>
            <h2 class="text-lg sm:text-xl font-extrabold text-stone-900">{{ $stepTitles['konfirmasi'] }}</h2>
            <p class="mt-1 text-sm text-stone-500">Langkah terakhir — kirim pengajuan Anda ke tim homecare.</p>
        </div>

        @include('patient.booking._summary')

        <x-emergency-banner compact />

        <x-card class="bg-brand-50/60 ring-brand-200">
            <h3 class="font-bold text-stone-900 flex items-center gap-2">
                <x-icon name="info" class="w-5 h-5 text-brand-600" /> Apa yang terjadi setelah ini?
            </h3>
            <ol class="mt-3 space-y-2 text-sm text-stone-600">
                <li class="flex gap-2.5"><span class="font-bold text-brand-700 shrink-0">1.</span> Tim kami memverifikasi pengajuan (biasanya beberapa jam pada jam kerja).</li>
                <li class="flex gap-2.5"><span class="font-bold text-brand-700 shrink-0">2.</span> Anda diberi tahu jadwal pasti, nama petugas, dan biaya final.</li>
                <li class="flex gap-2.5"><span class="font-bold text-brand-700 shrink-0">3.</span> Petugas datang sesuai jadwal — Anda bisa memantau statusnya kapan saja.</li>
            </ol>
        </x-card>

        <form method="POST" action="{{ route('akun.pesan.submit') }}">
            @csrf
            <div class="flex flex-col-reverse sm:flex-row sm:justify-between gap-3">
                <x-button :href="route('akun.pesan.step', 'review')" variant="ghost" icon="chevron-left" class="justify-center">
                    Kembali
                </x-button>
                <x-button type="submit" size="lg" icon="check" class="w-full sm:w-auto">
                    Kirim Pengajuan
                </x-button>
            </div>
        </form>
    </div>
@endsection
