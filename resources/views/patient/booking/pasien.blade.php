@extends('patient.booking._layout')

@section('step-content')
    <div class="space-y-4">
        <div>
            <h2 class="text-lg sm:text-xl font-extrabold text-stone-900">{{ $stepTitles['pasien'] }}</h2>
            <p class="mt-1 text-sm text-stone-500">Pilih anggota keluarga yang akan menerima kunjungan.</p>
        </div>

        <form method="POST" action="{{ route('akun.pesan.save', 'pasien') }}" class="space-y-3">
            @csrf

            @if ($errors->has('patient_profile_id'))
                <x-alert type="error">{{ $errors->first('patient_profile_id') }}</x-alert>
            @endif

            @forelse ($patients as $patient)
                <x-patient-card :patient="$patient" selectable
                                :selected="(int) ($booking['patient_profile_id'] ?? 0) === $patient->id" />
            @empty
                <x-card :padding="false">
                    <x-empty-state icon="users" title="Belum ada data pasien">
                        Tambahkan dulu data pasien (diri sendiri atau keluarga) untuk melanjutkan pemesanan.
                    </x-empty-state>
                </x-card>
            @endforelse

            <a href="{{ route('akun.pasien.create', ['return_to' => route('akun.pesan.step', 'pasien')]) }}"
               class="flex items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-stone-300 p-4 text-sm font-bold text-stone-500 hover:border-brand-400 hover:text-brand-700 transition min-h-11">
                <x-icon name="plus" class="w-5 h-5" />
                Tambah pasien baru
            </a>

            @if ($patients->isNotEmpty())
                <div class="pt-2 flex justify-end">
                    <x-button type="submit" size="lg" icon="chevron-right" class="w-full sm:w-auto">
                        Lanjut ke Layanan
                    </x-button>
                </div>
            @endif
        </form>
    </div>
@endsection
