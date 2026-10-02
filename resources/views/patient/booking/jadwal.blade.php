@extends('patient.booking._layout')

@section('step-content')
    <div class="space-y-4">
        <div>
            <h2 class="text-lg sm:text-xl font-extrabold text-stone-900">{{ $stepTitles['jadwal'] }}</h2>
            <p class="mt-1 text-sm text-stone-500">Ini perkiraan waktu yang Anda inginkan — jadwal pasti akan dikonfirmasi koordinator kami.</p>
        </div>

        <form method="POST" action="{{ route('akun.pesan.save', 'jadwal') }}" class="space-y-5">
            @csrf

            <x-card class="space-y-5">
                <x-input name="preferred_date" type="date" label="Tanggal kunjungan" required
                         :value="$booking['preferred_date'] ?? null"
                         :min="$minDate" :max="$maxDate" icon="calendar"
                         hint="Paling cepat besok, paling lambat 30 hari ke depan." />

                <div>
                    <p class="block text-sm font-semibold text-stone-700 mb-2.5">
                        Perkiraan waktu <span class="text-rose-500">*</span>
                    </p>
                    <div class="grid sm:grid-cols-3 gap-3">
                        @foreach ($timeWindows as $key => $window)
                            <label class="block cursor-pointer">
                                <input type="radio" name="preferred_time_window" value="{{ $key }}"
                                       @checked(($booking['preferred_time_window'] ?? '') === $key)
                                       class="peer sr-only" required />
                                <div class="relative rounded-2xl ring-1 bg-white p-4 text-center transition peer-checked:ring-2 peer-checked:ring-brand-600 peer-checked:bg-brand-50/60 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand-600 hover:ring-brand-300">
                                    <span class="choice-dot absolute top-2 right-2 w-6 h-6 rounded-full border-2 border-stone-300 bg-white flex items-center justify-center transition
                                        {{ ($booking['preferred_time_window'] ?? '') === $key ? 'border-brand-600 bg-brand-600' : '' }}">
                                        <x-icon name="check" class="w-3.5 h-3.5 text-white" />
                                    </span>
                                    <x-icon name="{{ $key === 'morning' ? 'sparkles' : ($key === 'midday' ? 'clock' : 'home') }}"
                                            class="choice-icon w-6 h-6 mx-auto text-brand-600" />
                                    <p class="choice-name mt-2 font-bold text-sm text-stone-800">{{ explode('(', $window['label'])[0] }}</p>
                                    <p class="text-xs text-stone-400 mt-0.5">{{ $window['start'] }} – {{ $window['end'] }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('preferred_time_window')
                        <p class="mt-2 text-sm font-medium text-rose-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </x-card>

            <x-alert type="info">
                Butuh kunjungan secepatnya atau di luar jam di atas? Sebutkan pada catatan di langkah sebelumnya,
                atau hubungi kami di <strong>{{ config('homecare.contact.phone') }}</strong>.
            </x-alert>

            <div class="pt-1 flex flex-col-reverse sm:flex-row sm:justify-between gap-3">
                <x-button :href="route('akun.pesan.step', 'lokasi')" variant="ghost" icon="chevron-left" class="justify-center">
                    Kembali
                </x-button>
                <x-button type="submit" size="lg" icon="chevron-right" class="w-full sm:w-auto">
                    Lanjut ke Review
                </x-button>
            </div>
        </form>
    </div>
@endsection
