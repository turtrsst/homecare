@extends('patient.booking._layout')

@section('step-content')
    <div class="space-y-5">
        <div>
            <h2 class="text-lg sm:text-xl font-extrabold text-stone-900">{{ $stepTitles['review'] }}</h2>
            <p class="mt-1 text-sm text-stone-500">Periksa sekali lagi sebelum dikirim. Ketuk "Ubah" untuk memperbaiki.</p>
        </div>

        @include('patient.booking._summary')

        <div class="flex flex-col-reverse sm:flex-row sm:justify-between gap-3 pt-1">
            <x-button :href="route('akun.pesan.step', 'jadwal')" variant="ghost" icon="chevron-left" class="justify-center">
                Kembali
            </x-button>
            <x-button :href="route('akun.pesan.step', 'konfirmasi')" size="lg" icon="chevron-right" class="w-full sm:w-auto">
                Lanjut ke Konfirmasi
            </x-button>
        </div>
    </div>
@endsection
