@extends('layouts.admin')

@php
    $request = $appointment->request;
    $patient = $request->patient;
@endphp

@section('title', 'Kunjungan #'.$appointment->id.' — Monitor')

@section('content')
    <nav class="text-sm mb-4">
        <a href="{{ route('operasional.jadwal.index', ['tanggal' => $appointment->scheduled_at->toDateString()]) }}" class="font-semibold text-stone-400 hover:text-brand-700">← Jadwal {{ $appointment->scheduled_at->translatedFormat('j M Y') }}</a>
    </nav>

    {{-- Header --}}
    <div class="bg-white rounded-3xl ring-1 ring-stone-200/70 shadow-card p-6 mb-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-extrabold text-stone-900">{{ $patient->name }}</h1>
                <p class="mt-1 text-sm text-stone-500">
                    <a href="{{ route('operasional.pengajuan.show', $request->code) }}" class="font-mono font-bold text-brand-700 hover:underline">{{ $request->code }}</a>
                    · {{ $request->items->pluck('service_name')->unique()->implode(', ') }}
                </p>
            </div>
            <div class="text-right">
                <x-status-badge :status="$appointment->status" />
                <p class="mt-2 text-sm font-bold text-stone-800">{{ $appointment->scheduled_at->translatedFormat('l, j M Y') }}</p>
                <p class="text-sm text-stone-500">{{ $appointment->scheduled_at->format('H.i') }} WIB · ±{{ $appointment->estimated_duration_minutes }} mnt</p>
            </div>
        </div>

        {{-- Jam pelaksanaan --}}
        <div class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-3">
            @php
                $duration = ($appointment->checkin_at && $appointment->checkout_at)
                    ? $appointment->checkin_at->diffInMinutes($appointment->checkout_at)
                    : null;
            @endphp
            @foreach ([
                ['label' => 'Terjadwal', 'at' => $appointment->scheduled_at, 'icon' => 'calendar'],
                ['label' => 'Check-in', 'at' => $appointment->checkin_at, 'icon' => 'check'],
                ['label' => 'Check-out', 'at' => $appointment->checkout_at, 'icon' => 'check-circle'],
                ['label' => 'Durasi', 'at' => $duration, 'icon' => 'clock'],
            ] as $mark)
                <div class="rounded-2xl ring-1 p-3.5 text-center {{ $mark['at'] ? 'bg-brand-50/60 ring-brand-200' : 'bg-stone-50 ring-stone-200/70' }}">
                    <x-icon :name="$mark['icon']" class="w-5 h-5 mx-auto {{ $mark['at'] ? 'text-brand-600' : 'text-stone-300' }}" />
                    <p class="mt-1.5 text-xs font-bold uppercase tracking-wide {{ $mark['at'] ? 'text-brand-700' : 'text-stone-400' }}">{{ $mark['label'] }}</p>
                    <p class="mt-0.5 text-sm font-extrabold {{ $mark['at'] ? 'text-stone-900' : 'text-stone-300' }}">
                        @if ($mark['label'] === 'Durasi')
                            {{ $duration !== null ? round($duration).' menit' : '—' }}
                        @else
                            {{ $mark['at'] ? $mark['at']->format('H.i') : '—' }}
                        @endif
                    </p>
                </div>
            @endforeach
        </div>

        @if ($appointment->status->value === 'cancelled')
            <x-alert type="error" class="mt-5" title="Kunjungan dibatalkan">
                {{ $request->cancellation_reason ?: $appointment->notes ?: 'Tanpa keterangan.' }}
            </x-alert>
        @endif
    </div>

    <div class="grid lg:grid-cols-3 gap-5 items-start">
        <div class="lg:col-span-2 space-y-5">
            {{-- Petugas --}}
            <x-card>
                <h2 class="font-extrabold text-stone-900 mb-3">Petugas Ditugaskan</h2>
                @if ($appointment->assignments->isEmpty())
                    <p class="text-sm text-stone-400">Belum ada petugas.</p>
                @else
                    <div class="space-y-2">
                        @foreach ($appointment->assignments as $assignment)
                            <div class="flex items-center gap-3.5 rounded-xl ring-1 ring-stone-200/70 bg-stone-50 p-3.5">
                                <span class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold shrink-0">
                                    {{ mb_substr($assignment->staff->name, 0, 1) }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-stone-900 text-sm">{{ $assignment->staff->name }}</p>
                                    <p class="text-xs text-stone-400">
                                        {{ $assignment->staff->profession->label() }}
                                        @if ($assignment->staff->phone) · {{ $assignment->staff->phone }} @endif
                                    </p>
                                </div>
                                <x-status-badge :status="$assignment->status" />
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>

            {{-- Asesmen --}}
            <x-card>
                <h2 class="font-extrabold text-stone-900 mb-3">Asesmen ({{ $appointment->assessments->count() }})</h2>
                @if ($appointment->assessments->isEmpty())
                    <p class="text-sm text-stone-400">Belum ada asesmen tercatat dari petugas.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($appointment->assessments->sortByDesc('assessed_at') as $assessment)
                            <div class="rounded-2xl ring-1 ring-stone-200/70 bg-stone-50 p-4">
                                <p class="text-xs font-bold text-stone-400 mb-2.5">
                                    {{ $assessment->assessed_at->translatedFormat('j M Y, H.i') }} · {{ $assessment->staff->name }}
                                </p>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-2.5 text-sm">
                                    @if ($assessment->systolic_bp)
                                        <div><p class="text-xs text-stone-400">TD</p><p class="font-bold text-stone-800">{{ $assessment->systolic_bp }}/{{ $assessment->diastolic_bp }} mmHg</p></div>
                                    @endif
                                    @if ($assessment->pulse)
                                        <div><p class="text-xs text-stone-400">Nadi</p><p class="font-bold text-stone-800">{{ $assessment->pulse }} ×/mnt</p></div>
                                    @endif
                                    @if ($assessment->respiratory_rate)
                                        <div><p class="text-xs text-stone-400">Napas</p><p class="font-bold text-stone-800">{{ $assessment->respiratory_rate }} ×/mnt</p></div>
                                    @endif
                                    @if ($assessment->temperature_c)
                                        <div><p class="text-xs text-stone-400">Suhu</p><p class="font-bold text-stone-800">{{ $assessment->temperature_c }} °C</p></div>
                                    @endif
                                    @if ($assessment->oxygen_saturation)
                                        <div><p class="text-xs text-stone-400">SpO₂</p><p class="font-bold text-stone-800">{{ $assessment->oxygen_saturation }}%</p></div>
                                    @endif
                                    @if (! is_null($assessment->pain_scale))
                                        <div><p class="text-xs text-stone-400">Nyeri</p><p class="font-bold text-stone-800">{{ $assessment->pain_scale }}/10</p></div>
                                    @endif
                                </div>
                                @if ($assessment->findings)
                                    <p class="mt-3 pt-3 border-t border-stone-200/70 text-sm text-stone-600 leading-relaxed">{{ $assessment->findings }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>

            {{-- Dokumentasi pelayanan --}}
            <x-card>
                <h2 class="font-extrabold text-stone-900 mb-3">Dokumentasi Pelayanan</h2>
                @if ($appointment->serviceRecords->isEmpty())
                    <p class="text-sm text-stone-400">Belum ada dokumentasi — diisi petugas saat check-out.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($appointment->serviceRecords as $record)
                            <div class="rounded-2xl ring-1 ring-stone-200/70 p-4 text-sm space-y-2.5">
                                <div>
                                    <p class="text-xs font-bold text-stone-400 uppercase tracking-wide">Tindakan</p>
                                    <p class="mt-0.5 text-stone-700 leading-relaxed">{{ $record->actions_taken }}</p>
                                </div>
                                @if ($record->results)
                                    <div>
                                        <p class="text-xs font-bold text-stone-400 uppercase tracking-wide">Hasil</p>
                                        <p class="mt-0.5 text-stone-600 leading-relaxed">{{ $record->results }}</p>
                                    </div>
                                @endif
                                @if ($record->recommendations)
                                    <div>
                                        <p class="text-xs font-bold text-stone-400 uppercase tracking-wide">Anjuran</p>
                                        <p class="mt-0.5 text-stone-600 leading-relaxed">{{ $record->recommendations }}</p>
                                    </div>
                                @endif
                                @if ($record->follow_up_needed)
                                    <p class="rounded-xl bg-warm-50 ring-1 ring-warm-100 px-3 py-2 text-warm-600 font-semibold text-sm">
                                        Perlu tindak lanjut{{ $record->follow_up_notes ? ': '.$record->follow_up_notes : '' }}
                                    </p>
                                @endif
                                <p class="text-xs text-stone-400">{{ $record->staff->name }} · {{ $record->started_at?->format('H.i') }}–{{ $record->ended_at?->format('H.i') }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>

            {{-- Catatan tim --}}
            @if ($appointment->clinicalNotes->isNotEmpty())
                <x-card>
                    <h2 class="font-extrabold text-stone-900 mb-3">Catatan Tim</h2>
                    <ul class="space-y-2.5">
                        @foreach ($appointment->clinicalNotes as $note)
                            <li class="text-sm rounded-xl bg-stone-50 ring-1 ring-stone-200/70 p-3.5">
                                <p class="text-stone-600 leading-relaxed">{{ $note->content }}</p>
                                <p class="mt-1.5 text-xs text-stone-400">{{ $note->staff?->name ?? 'Tim' }} · {{ $note->created_at->translatedFormat('j M, H.i') }}</p>
                            </li>
                        @endforeach
                    </ul>
                </x-card>
            @endif
        </div>

        {{-- Samping --}}
        <div class="space-y-5">
            <x-card>
                <h2 class="font-extrabold text-stone-900 mb-3">Pasien & Lokasi</h2>
                <p class="font-bold text-stone-900">{{ $patient->name }}</p>
                <p class="text-sm text-stone-500">
                    {{ $patient->age() ? $patient->age().' tahun · ' : '' }}{{ $patient->gender?->label() ?? '' }}
                </p>
                <div class="mt-3 text-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-stone-400 mb-1">Alamat</p>
                    <p class="text-stone-600 leading-relaxed">{{ $request->address->oneLine() }}</p>
                    @if ($request->address->latitude && $request->address->longitude)
                        <p class="mt-2">
                            <a href="https://www.google.com/maps?q={{ $request->address->latitude }},{{ $request->address->longitude }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-700 hover:underline">
                                <x-icon name="map-pin" class="w-3.5 h-3.5" /> Lihat di Google Maps
                            </a>
                        </p>
                    @endif
                    <p class="mt-1 text-xs text-stone-400">a.n. {{ $request->address->recipient_name }} ·
                        <a href="tel:{{ preg_replace('/\s+/', '', $request->address->phone) }}" class="font-semibold text-brand-700 hover:underline">{{ $request->address->phone }}</a>
                    </p>
                </div>
                @foreach ($patient->contacts as $contact)
                    <p class="mt-2 text-xs text-stone-500">{{ $contact->label ?: $contact->type->label() }}:
                        <a href="tel:{{ preg_replace('/\s+/', '', $contact->value) }}" class="font-semibold text-brand-700 hover:underline">{{ $contact->value }}</a>
                    </p>
                @endforeach
                <div class="mt-4 pt-4 border-t border-stone-100 flex gap-2">
                    <x-button :href="route('operasional.pasien.show', $patient)" variant="soft" size="sm" icon="user">Profil Pasien</x-button>
                    <x-button :href="route('operasional.pengajuan.show', $request->code)" variant="ghost" size="sm" icon="clipboard">Pengajuan</x-button>
                </div>
            </x-card>

            <x-card>
                <h2 class="font-extrabold text-stone-900 mb-3">Lampiran Kunjungan</h2>
                @if ($appointment->attachments->isEmpty())
                    <p class="text-sm text-stone-400">Belum ada lampiran.</p>
                @else
                    <ul class="space-y-2">
                        @foreach ($appointment->attachments as $attachment)
                            <li class="flex items-center gap-3 rounded-xl ring-1 ring-stone-200/70 bg-stone-50 p-3 text-sm">
                                <x-icon name="{{ str_contains($attachment->mime_type, 'pdf') ? 'document' : 'camera' }}" class="w-5 h-5 text-stone-400 shrink-0" />
                                <span class="min-w-0 flex-1">
                                    <span class="block font-semibold text-stone-700 truncate">{{ $attachment->original_name }}</span>
                                    <span class="block text-xs text-stone-400">{{ $attachment->collectionLabel() }}</span>
                                </span>
                                <a href="{{ route('attachments.download', $attachment) }}" class="rounded-lg p-2 text-stone-400 hover:text-brand-700 hover:bg-white transition shrink-0" aria-label="Unduh">
                                    <x-icon name="chevron-down" class="w-4.5 h-4.5" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>
        </div>
    </div>
@endsection
