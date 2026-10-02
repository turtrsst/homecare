@extends('layouts.admin')

@section('title', $patient->name.' — Pasien')

@section('content')
    <nav class="text-sm mb-4">
        <a href="{{ route('operasional.pasien.index') }}" class="font-semibold text-stone-400 hover:text-brand-700">← Data Pasien</a>
    </nav>

    <div class="grid lg:grid-cols-3 gap-5 items-start">
        {{-- Profil --}}
        <div class="space-y-5">
            <x-card>
                <div class="flex items-center gap-4">
                    <span class="w-16 h-16 rounded-2xl bg-brand-100 text-brand-700 flex items-center justify-center text-2xl font-extrabold shrink-0">
                        {{ mb_substr($patient->name, 0, 1) }}
                    </span>
                    <div class="min-w-0">
                        <h1 class="text-xl font-extrabold text-stone-900 truncate">{{ $patient->name }}</h1>
                        <p class="text-sm text-stone-500">
                            {{ $patient->age() ? $patient->age().' tahun · ' : '' }}{{ $patient->gender?->label() ?? '' }}
                        </p>
                        @if ($patient->relationship)
                            <x-badge color="slate" class="mt-1.5">{{ ucfirst(str_replace('_', ' ', $patient->relationship)) }} dari {{ $patient->user->name }}</x-badge>
                        @endif
                    </div>
                </div>

                <dl class="mt-5 text-sm space-y-2.5">
                    @if ($patient->nik)
                        <div class="flex justify-between gap-3">
                            <dt class="text-stone-400">NIK</dt>
                            <dd class="font-mono text-xs font-semibold text-stone-600">{{ $patient->nik }}</dd>
                        </div>
                    @endif
                    @if ($patient->birth_date)
                        <div class="flex justify-between gap-3">
                            <dt class="text-stone-400">Tanggal lahir</dt>
                            <dd class="font-semibold text-stone-800">{{ $patient->birth_date->translatedFormat('j F Y') }}</dd>
                        </div>
                    @endif
                    @if ($patient->blood_type && $patient->blood_type !== 'tidak_tahu')
                        <div class="flex justify-between gap-3">
                            <dt class="text-stone-400">Golongan darah</dt>
                            <dd class="font-semibold text-stone-800">{{ $patient->blood_type }}</dd>
                        </div>
                    @endif
                    @foreach ($patient->contacts as $contact)
                        <div class="flex justify-between gap-3">
                            <dt class="text-stone-400">{{ $contact->label ?: $contact->type->label() }}</dt>
                            <dd><a href="tel:{{ preg_replace('/\s+/', '', $contact->value) }}" class="font-semibold text-brand-700 hover:underline">{{ $contact->value }}</a></dd>
                        </div>
                    @endforeach
                    <div class="flex justify-between gap-3">
                        <dt class="text-stone-400">Didaftarkan</dt>
                        <dd class="font-semibold text-stone-800">{{ $patient->created_at->translatedFormat('j M Y') }}</dd>
                    </div>
                </dl>

                @if ($patient->medical_notes)
                    <p class="mt-4 text-sm text-warm-600 bg-warm-50 ring-1 ring-warm-100 rounded-xl px-3.5 py-2.5 font-medium leading-relaxed">
                        <span class="font-bold">Catatan kesehatan:</span> {{ $patient->medical_notes }}
                    </p>
                @endif
            </x-card>

            <x-card>
                <h2 class="font-extrabold text-stone-900 mb-3">Alamat ({{ $patient->addresses->count() }})</h2>
                @if ($patient->addresses->isEmpty())
                    <p class="text-sm text-stone-400">Belum ada alamat.</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($patient->addresses as $address)
                            <li class="rounded-xl ring-1 ring-stone-200/70 bg-stone-50 p-3.5 text-sm">
                                <div class="flex items-center gap-2">
                                    <p class="font-bold text-stone-800">{{ $address->label ?: 'Alamat' }}</p>
                                    @if ($address->is_primary) <x-badge color="teal">Utama</x-badge> @endif
                                </div>
                                <p class="mt-1 text-stone-500 leading-relaxed">{{ $address->oneLine() }}</p>
                                <p class="mt-1 text-xs text-stone-400">a.n. {{ $address->recipient_name }} · {{ $address->phone }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>
        </div>

        {{-- Riwayat pengajuan --}}
        <div class="lg:col-span-2">
            <h2 class="text-sm font-bold uppercase tracking-wider text-stone-400 mb-3">
                Riwayat Pengajuan ({{ $patient->homecare_requests_count }})
            </h2>

            @if ($requests->isEmpty())
                <x-card :padding="false">
                    <x-empty-state icon="clipboard" title="Belum ada pengajuan">
                        Pasien ini belum pernah memesan layanan homecare.
                    </x-empty-state>
                </x-card>
            @else
                <div class="space-y-3">
                    @foreach ($requests as $request)
                        <x-request-card :request="$request" :href="route('operasional.pengajuan.show', $request->code)" />
                    @endforeach
                </div>
                <div class="mt-6">{{ $requests->links() }}</div>
            @endif
        </div>
    </div>
@endsection
