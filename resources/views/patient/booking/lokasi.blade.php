@extends('patient.booking._layout')

@section('step-content')
    <div class="space-y-4">
        <div>
            <h2 class="text-lg sm:text-xl font-extrabold text-stone-900">{{ $stepTitles['lokasi'] }}</h2>
            <p class="mt-1 text-sm text-stone-500">
                Ke mana petugas kami datang?
                @if ($patient) <span class="font-semibold text-stone-700">Pasien: {{ $patient->name }}</span> @endif
            </p>
        </div>

        <form method="POST" action="{{ route('akun.pesan.save', 'lokasi') }}" class="space-y-3">
            @csrf

            @if ($errors->has('patient_address_id'))
                <x-alert type="error">{{ $errors->first('patient_address_id') }}</x-alert>
            @endif

            @php $selected = (int) (old('patient_address_id', $booking['patient_address_id'] ?? 0)); @endphp

            @forelse ($addresses as $address)
                <label class="block cursor-pointer">
                    <input type="radio" name="patient_address_id" value="{{ $address->id }}"
                           @checked($selected === $address->id) class="peer sr-only" required />
                    <div class="rounded-2xl ring-1 bg-white p-4 sm:p-5 transition peer-checked:ring-2 peer-checked:ring-brand-600 peer-checked:bg-brand-50/50 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand-600 hover:ring-brand-300">
                        <div class="flex items-start gap-3.5">
                            <span class="w-10 h-10 rounded-xl bg-stone-100 text-stone-500 flex items-center justify-center shrink-0">
                                <x-icon name="map-pin" class="w-5 h-5" />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex flex-wrap items-center gap-2">
                                    <span class="font-bold text-stone-900">{{ $address->label ?: 'Alamat' }}</span>
                                    @if ($address->is_primary) <x-badge color="teal">Utama</x-badge> @endif
                                </span>
                                <span class="block mt-1 text-sm text-stone-500">{{ $address->oneLine() }}</span>
                                <span class="block mt-1 text-xs text-stone-400">a.n. {{ $address->recipient_name }} · {{ $address->phone }}</span>
                                @if ($address->notes)
                                    <span class="block mt-1.5 text-xs text-stone-500 bg-stone-50 rounded-lg px-2.5 py-1.5">📝 {{ $address->notes }}</span>
                                @endif
                            </span>
                            <span class="choice-dot w-6 h-6 rounded-full border-2 flex items-center justify-center shrink-0 transition
                                {{ $selected === $address->id ? 'border-brand-600 bg-brand-600' : 'border-stone-300' }}">
                                <x-icon name="check" class="w-3.5 h-3.5 text-white" />
                            </span>
                        </div>
                    </div>
                </label>
            @empty
                <x-card :padding="false">
                    <x-empty-state icon="map-pin" title="Belum ada alamat tersimpan">
                        Tambahkan alamat kunjungan untuk pasien ini terlebih dahulu.
                    </x-empty-state>
                </x-card>
            @endforelse

            @if ($patient)
                <a href="{{ route('akun.pasien.alamat.create', [$patient, 'return_to' => route('akun.pesan.step', 'lokasi')]) }}"
                   class="flex items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-stone-300 p-4 text-sm font-bold text-stone-500 hover:border-brand-400 hover:text-brand-700 transition min-h-11">
                    <x-icon name="plus" class="w-5 h-5" />
                    Tambah alamat baru
                </a>
            @endif

            @if ($addresses->isNotEmpty())
                <div class="pt-2 flex flex-col-reverse sm:flex-row sm:justify-between gap-3">
                    <x-button :href="route('akun.pesan.step', 'kebutuhan')" variant="ghost" icon="chevron-left" class="justify-center">
                        Kembali
                    </x-button>
                    <x-button type="submit" size="lg" icon="chevron-right" class="w-full sm:w-auto">
                        Lanjut ke Jadwal
                    </x-button>
                </div>
            @endif
        </form>
    </div>
@endsection
