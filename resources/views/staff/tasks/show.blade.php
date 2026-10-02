@extends('layouts.app')

@php
    $request = $appointment->request;
    $patient = $request->patient;
    $address = $request->address;
    $status = $appointment->status->value;
    $requestStatus = $request->status->value;
    $latestAssessment = $appointment->assessments->sortByDesc('assessed_at')->first();

    // Alur pesanan dilihat dari sisi operasional: pengajuan → verifikasi →
    // persetujuan → penjadwalan → pembayaran → kunjungan → selesai.
    $settled = $request->payment_status?->isSettled() || (float) $request->total_amount <= 0;
    $approved = in_array($requestStatus, ['approved', 'scheduled', 'in_progress', 'completed'], true);

    $flowSteps = [
        ['label' => 'Pengajuan', 'note' => $request->submitted_at?->translatedFormat('j M Y, H.i') ?: 'Menunggu pengiriman', 'done' => $request->submitted_at !== null],
        ['label' => 'Verifikasi', 'note' => $request->verified_at?->translatedFormat('j M Y, H.i') ?: 'Menunggu koordinator', 'done' => $request->verified_at !== null || $approved],
        ['label' => 'Disetujui', 'note' => $approved ? 'Kebutuhan disetujui' : 'Menunggu keputusan', 'done' => $approved],
        ['label' => 'Terjadwal', 'note' => $appointment->scheduled_at?->translatedFormat('j M Y, H.i') ?: 'Belum ada jadwal', 'done' => $appointment->scheduled_at !== null],
        ['label' => 'Pembayaran', 'note' => $settled ? $request->payment_status->label() : $request->payment_status->label().' · '.$request->formattedTotal(), 'done' => $settled],
        ['label' => 'Kunjungan', 'note' => $appointment->status->label(), 'done' => in_array($status, ['on_the_way', 'checked_in', 'in_service', 'completed'], true)],
        ['label' => 'Selesai', 'note' => $request->completed_at?->translatedFormat('j M Y, H.i') ?: 'Belum selesai', 'done' => $requestStatus === 'completed'],
    ];
    $currentFlow = collect($flowSteps)->search(fn (array $step) => ! $step['done']);
    if ($currentFlow === false) {
        $currentFlow = count($flowSteps);
    }

    $needsConfirm = $myAssignment !== null
        && $myAssignment->confirmed_at === null
        && $canVisit
        && ! $appointment->status->isFinal();
@endphp

@section('title', 'Kunjungan — '.$patient->name)

@section('content')
    <nav class="text-sm mb-4">
        <a href="{{ route('tugas.index') }}" class="font-semibold text-stone-400 hover:text-brand-700">← Tugas Saya</a>
    </nav>

    {{-- Header kunjungan --}}
    <div class="bg-white rounded-3xl ring-1 ring-stone-200/70 shadow-card p-6 mb-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase tracking-wider text-stone-400">{{ $request->code }}</p>
                <h1 class="mt-1 text-xl sm:text-2xl font-extrabold text-stone-900">{{ $patient->name }}</h1>
                <p class="mt-1 text-sm text-stone-500">
                    {{ $request->items->pluck('service_name')->unique()->implode(', ') }}
                </p>
            </div>
            <div class="text-right">
                <x-status-badge :status="$appointment->status" />
                <p class="mt-2 text-sm font-bold text-stone-800">
                    {{ $appointment->scheduled_at->translatedFormat('D, j M Y') }}
                </p>
                <p class="text-sm text-stone-500">{{ $appointment->scheduled_at->format('H.i') }} WIB · ±{{ $appointment->estimated_duration_minutes }} mnt</p>
            </div>
        </div>

        {{-- Alur aksi petugas --}}
        @if ($canVisit && ! $appointment->status->isFinal())
            <div class="mt-6 rounded-2xl bg-stone-50 ring-1 ring-stone-200/70 p-4">
                <p class="text-xs font-bold uppercase tracking-wider text-stone-400 mb-3">Langkah Anda</p>

                @if ($needsConfirm)
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-brand-50 ring-1 ring-brand-100 p-4">
                        <div class="min-w-0">
                            <p class="text-sm font-extrabold text-brand-800">Konfirmasi penugasan</p>
                            <p class="mt-0.5 text-xs text-brand-700">
                                Baca dulu detail pasien, kebutuhan, dan lokasi. Tekan konfirmasi agar koordinator tahu tugas sudah Anda terima.
                            </p>
                        </div>
                        <form method="POST" action="{{ route('tugas.konfirmasi', $appointment) }}" class="shrink-0">
                            @csrf
                            <x-button type="submit" size="md" icon="check">Konfirmasi Tugas</x-button>
                        </form>
                    </div>
                @elseif ($myAssignment?->confirmed_at)
                    <p class="mb-4 inline-flex items-center gap-1.5 rounded-xl bg-emerald-50 ring-1 ring-emerald-200 px-3 py-1.5 text-xs font-bold text-emerald-700">
                        <x-icon name="check-circle" class="w-4 h-4" />
                        Tugas dikonfirmasi · {{ $myAssignment->confirmed_at->translatedFormat('j M Y, H.i') }}
                    </p>
                @endif

                <div class="flex flex-wrap items-center gap-2 text-sm">
                    @php
                        $flow = [
                            ['key' => 'scheduled', 'label' => 'Terjadwal', 'icon' => 'calendar'],
                            ['key' => 'on_the_way', 'label' => 'Dalam Perjalanan', 'icon' => 'map-pin'],
                            ['key' => 'checked_in', 'label' => 'Tiba di Lokasi', 'icon' => 'check'],
                            ['key' => 'in_service', 'label' => 'Pelayanan', 'icon' => 'heart'],
                            ['key' => 'completed', 'label' => 'Selesai', 'icon' => 'clipboard'],
                        ];
                        $currentIdx = collect($flow)->search(fn ($f) => $f['key'] === $status);
                    @endphp
                    @foreach ($flow as $i => $step)
                        <span class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 font-semibold
                            {{ $i < $currentIdx ? 'bg-emerald-100 text-emerald-700' : ($i === $currentIdx ? 'bg-brand-600 text-white shadow-sm' : 'bg-white text-stone-400 ring-1 ring-stone-200') }}">
                            <x-icon :name="$step['icon']" class="w-4 h-4" /> {{ $step['label'] }}
                        </span>
                        @unless ($loop->last)
                            <x-icon name="chevron-right" class="w-4 h-4 text-stone-300 shrink-0 hidden sm:block" />
                        @endunless
                    @endforeach
                </div>

                <div class="mt-4">
                    @if ($status === 'scheduled')
                        <form method="POST" action="{{ route('tugas.berangkat', $appointment) }}">
                            @csrf
                            <x-button type="submit" size="lg" icon="map-pin" full>
                                Saya Berangkat ke Lokasi
                            </x-button>
                        </form>
                        @unless ($appointment->scheduled_at->isToday())
                            <p class="mt-2 text-xs text-stone-400">Kunjungan ini belum hari ini — tombol tetap tersedia bila Anda mulai lebih awal.</p>
                        @endunless
                    @elseif ($status === 'on_the_way')
                        <x-button size="lg" icon="check" full x-data x-on:click="$dispatch('open-modal', 'modal-checkin')">
                            Saya Sudah Tiba — Check-in
                        </x-button>
                    @elseif ($status === 'checked_in')
                        <form method="POST" action="{{ route('tugas.mulai', $appointment) }}">
                            @csrf
                            <x-button type="submit" size="lg" icon="heart" full>Mulai Pelayanan</x-button>
                        </form>
                    @elseif ($status === 'in_service')
                        <x-button variant="secondary" icon="activity" full class="mb-2.5" x-data x-on:click="$dispatch('open-modal', 'modal-asesmen')">
                            Catat Asesmen / Tanda Vital
                        </x-button>
                        <x-button size="lg" icon="check" full x-data x-on:click="$dispatch('open-modal', 'modal-selesai')">
                            Selesaikan Kunjungan (Check-out)
                        </x-button>
                    @endif
                </div>
            </div>
        @endif

        @if ($appointment->status->value === 'completed')
            <div class="mt-6 rounded-2xl bg-emerald-50 ring-1 ring-emerald-200 p-4 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
                    <x-icon name="check" class="w-5 h-5" />
                </span>
                <p class="text-sm text-emerald-800 font-semibold">
                    Kunjungan selesai
                    @if ($appointment->checkout_at) · check-out {{ $appointment->checkout_at->translatedFormat('j M Y, H.i') }} @endif
                </p>
            </div>
        @elseif ($appointment->status->value === 'cancelled')
            <x-alert type="error" class="mt-6" title="Kunjungan dibatalkan">
                {{ $request->cancellation_reason ?: 'Koordinator membatalkan kunjungan ini.' }}
            </x-alert>
        @endif
    </div>

    <div class="grid lg:grid-cols-5 gap-5 items-start">
        {{-- Kolom utama: info pasien + dokumentasi --}}
        <div class="lg:col-span-3 space-y-5">
            {{-- Alur pesanan --}}
            <x-card>
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <h2 class="font-extrabold text-stone-900">Alur Pesanan</h2>
                    <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $request->status->badgeClasses() }}">
                        {{ $request->status->patientLabel() }}
                    </span>
                </div>

                <ol>
                    @foreach ($flowSteps as $i => $step)
                        <li class="flex gap-3">
                            <div class="flex flex-col items-center shrink-0">
                                <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0
                                    {{ $step['done'] ? 'bg-brand-600 text-white shadow-sm' : ($i === $currentFlow ? 'bg-white text-brand-700 ring-2 ring-brand-500' : 'bg-white text-stone-400 ring-1 ring-stone-300') }}">
                                    @if ($step['done'])
                                        <x-icon name="check" class="w-4 h-4" />
                                    @else
                                        {{ $i + 1 }}
                                    @endif
                                </span>
                                @unless ($loop->last)
                                    <span class="w-0.5 flex-1 my-1 {{ $step['done'] ? 'bg-brand-300' : 'bg-stone-200' }}"></span>
                                @endunless
                            </div>
                            <div class="pb-4 min-w-0">
                                <p class="text-sm font-bold {{ $step['done'] || $i === $currentFlow ? 'text-stone-800' : 'text-stone-400' }}">
                                    {{ $step['label'] }}
                                    @if ($i === $currentFlow && ! $step['done'])
                                        <span class="ml-1.5 align-middle rounded-full bg-amber-50 ring-1 ring-amber-200 px-2 py-0.5 text-[11px] font-bold text-amber-700">sekarang</span>
                                    @endif
                                </p>
                                <p class="mt-0.5 text-xs text-stone-400">{{ $step['note'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </x-card>

            {{-- Kebutuhan & catatan pengajuan --}}
            <x-card>
                <h2 class="font-extrabold text-stone-900 mb-3">Kebutuhan dari Keluarga</h2>
                <p class="text-sm text-stone-600 leading-relaxed">{{ $request->complaint }}</p>
                @if ($request->notes)
                    <p class="mt-3 text-sm text-stone-500 bg-stone-50 rounded-xl px-3.5 py-2.5">📝 {{ $request->notes }}</p>
                @endif
                @if ($patient->medical_notes)
                    <p class="mt-3 text-sm text-warm-600 bg-warm-50 ring-1 ring-warm-100 rounded-xl px-3.5 py-2.5 font-medium">
                        <span class="font-bold">Catatan kesehatan:</span> {{ $patient->medical_notes }}
                    </p>
                @endif
            </x-card>

            {{-- Asesmen --}}
            <x-card>
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h2 class="font-extrabold text-stone-900">Asesmen Pasien</h2>
                    @if ($canVisit && ! $appointment->status->isFinal())
                        <button type="button" x-data @click="$dispatch('open-modal', 'modal-asesmen')"
                                class="text-xs font-bold text-brand-700 hover:text-brand-800 inline-flex items-center gap-1 min-h-9 px-2">
                            <x-icon name="plus" class="w-4 h-4" /> Catat asesmen
                        </button>
                    @endif
                </div>

                @if ($appointment->assessments->isEmpty())
                    <p class="text-sm text-stone-400">
                        Belum ada asesmen tercatat. Catat tanda vital & temuan setelah tiba di lokasi.
                    </p>
                @else
                    <div class="space-y-3">
                        @foreach ($appointment->assessments->sortByDesc('assessed_at') as $assessment)
                            <div class="rounded-2xl ring-1 ring-stone-200/70 bg-stone-50 p-4">
                                <p class="text-xs font-bold text-stone-400 mb-2.5">
                                    {{ $assessment->assessed_at->translatedFormat('j M Y, H.i') }} · {{ $assessment->staff->name }}
                                </p>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-2.5 text-sm">
                                    @if ($assessment->systolic_bp)
                                        <div><p class="text-xs text-stone-400">Tekanan darah</p><p class="font-bold text-stone-800">{{ $assessment->systolic_bp }}/{{ $assessment->diastolic_bp }} mmHg</p></div>
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
                                    @if ($assessment->consciousness)
                                        <div><p class="text-xs text-stone-400">Kesadaran</p><p class="font-bold text-stone-800 capitalize">{{ $assessment->consciousness }}</p></div>
                                    @endif
                                    @if (! is_null($assessment->pain_scale))
                                        <div><p class="text-xs text-stone-400">Skala nyeri</p><p class="font-bold text-stone-800">{{ $assessment->pain_scale }}/10</p></div>
                                    @endif
                                    @if ($assessment->weight_kg)
                                        <div><p class="text-xs text-stone-400">Berat badan</p><p class="font-bold text-stone-800">{{ $assessment->weight_kg }} kg</p></div>
                                    @endif
                                    @if ($assessment->height_cm)
                                        <div><p class="text-xs text-stone-400">Tinggi badan</p><p class="font-bold text-stone-800">{{ $assessment->height_cm }} cm</p></div>
                                    @endif
                                </div>
                                @if ($assessment->findings)
                                    <p class="mt-3 text-sm text-stone-600 leading-relaxed border-t border-stone-200/70 pt-3">{{ $assessment->findings }}</p>
                                @endif
                                @if ($assessment->patient_designated_staff)
                                    <p class="mt-2 text-sm text-stone-500">
                                        <span class="font-semibold text-stone-600">Petugas ditunjuk pasien:</span> {{ $assessment->patient_designated_staff }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>

            {{-- Dokumentasi pelayanan --}}
            @if ($appointment->serviceRecords->isNotEmpty())
                <x-card>
                    <h2 class="font-extrabold text-stone-900 mb-4">Dokumentasi Pelayanan</h2>
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
                                    <p class="rounded-xl bg-warm-50 ring-1 ring-warm-100 px-3 py-2 text-warm-600 font-semibold">
                                        Perlu tindak lanjut{{ $record->follow_up_notes ? ': '.$record->follow_up_notes : '' }}
                                    </p>
                                @endif
                                <p class="text-xs text-stone-400">{{ $record->staff->name }} · {{ $record->started_at?->format('H.i') }}–{{ $record->ended_at?->format('H.i') }}</p>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif

            {{-- Catatan klinis dari tim lain --}}
            @if ($appointment->clinicalNotes->isNotEmpty())
                <x-card>
                    <h2 class="font-extrabold text-stone-900 mb-3">Catatan Tim</h2>
                    <ul class="space-y-2.5">
                        @foreach ($appointment->clinicalNotes as $note)
                            <li class="text-sm rounded-xl bg-stone-50 ring-1 ring-stone-200/70 p-3.5">
                                <p class="text-stone-600 leading-relaxed">{{ $note->content }}</p>
                                <p class="mt-1.5 text-xs text-stone-400">{{ $note->staff?->name ?? 'Tim homecare' }} · {{ $note->created_at->translatedFormat('j M, H.i') }}</p>
                            </li>
                        @endforeach
                    </ul>
                </x-card>
            @endif
        </div>

        {{-- Kolom samping: lokasi, kontak, lampiran --}}
        <div class="lg:col-span-2 space-y-5">
            <x-card>
                <h2 class="font-extrabold text-stone-900 mb-3">Lokasi & Kontak</h2>
                <div class="space-y-3.5 text-sm">
                    <div class="flex gap-3">
                        <span class="w-9 h-9 rounded-xl bg-stone-100 text-stone-500 flex items-center justify-center shrink-0">
                            <x-icon name="map-pin" class="w-4.5 h-4.5" />
                        </span>
                        <div>
                            <p class="font-bold text-stone-800">{{ $address->label ?: 'Alamat kunjungan' }}</p>
                            <p class="mt-0.5 text-stone-500 leading-relaxed">{{ $address->oneLine() }}</p>
                            @if ($address->latitude && $address->longitude)
                                <p class="mt-2">
                                    <a href="https://www.google.com/maps?q={{ $address->latitude }},{{ $address->longitude }}" target="_blank" rel="noopener"
                                       class="inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-3 py-2 text-xs font-bold text-white hover:bg-brand-700 transition min-h-9">
                                        <x-icon name="map-pin" class="w-4 h-4" /> Navigasi ke lokasi
                                    </a>
                                </p>
                            @endif
                            @if ($address->notes)
                                <p class="mt-1.5 text-xs text-warm-600 bg-warm-50 rounded-lg px-2.5 py-1.5 font-medium">📝 {{ $address->notes }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <span class="w-9 h-9 rounded-xl bg-stone-100 text-stone-500 flex items-center justify-center shrink-0">
                            <x-icon name="phone" class="w-4.5 h-4.5" />
                        </span>
                        <div class="min-w-0">
                            <p class="font-bold text-stone-800">{{ $address->recipient_name }}</p>
                            <a href="tel:{{ preg_replace('/\s+/', '', $address->phone) }}" class="text-brand-700 font-semibold hover:underline">{{ $address->phone }}</a>
                        </div>
                    </div>
                    @foreach ($patient->contacts as $contact)
                        <div class="flex gap-3">
                            <span class="w-9 h-9 rounded-xl bg-stone-100 text-stone-500 flex items-center justify-center shrink-0">
                                <x-icon name="users" class="w-4.5 h-4.5" />
                            </span>
                            <div>
                                <p class="font-bold text-stone-800">{{ $contact->label ?: $contact->type->label() }}</p>
                                <a href="tel:{{ preg_replace('/\s+/', '', $contact->value) }}" class="text-brand-700 font-semibold hover:underline">{{ $contact->value }}</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>

            <x-card>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <h2 class="font-extrabold text-stone-900">Pembayaran</h2>
                    <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $request->payment_status?->badgeClasses() ?? 'bg-stone-100 text-stone-600 ring-stone-200' }}">
                        {{ $request->payment_status?->label() ?? 'Belum Bayar' }}
                    </span>
                </div>

                <div class="flex items-center justify-between gap-3 text-sm">
                    <span class="text-stone-400">Total tagihan</span>
                    <span class="font-extrabold text-stone-800">{{ $request->formattedTotal() }}</span>
                </div>

                @if ($payments->isEmpty())
                    <p class="mt-3 text-xs text-stone-400">Belum ada transaksi tercatat untuk pengajuan ini.</p>
                @else
                    <ul class="mt-3 space-y-2">
                        @foreach ($payments as $payment)
                            <li class="rounded-xl bg-stone-50 ring-1 ring-stone-200/70 px-3.5 py-3 text-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-bold text-stone-800">{{ $payment->formattedAmount() }}</span>
                                    <span class="rounded-full px-2 py-0.5 text-[11px] font-bold ring-1 {{ $payment->status->badgeClasses() }}">
                                        {{ $payment->status->label() }}
                                    </span>
                                </div>
                                <p class="mt-1 text-xs text-stone-400">
                                    {{ $payment->method->label() }}
                                    @if ($payment->paid_at) · {{ $payment->paid_at->translatedFormat('j M Y, H.i') }} @endif
                                    @if ($payment->reference_number) · Ref {{ $payment->reference_number }} @endif
                                </p>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($request->review)
                    <div class="mt-3 rounded-xl bg-amber-50 ring-1 ring-amber-200 px-3.5 py-3">
                        <p class="text-xs font-bold text-amber-700">Rating dari keluarga pasien</p>
                        <div class="mt-1.5 flex items-center gap-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <x-icon name="star" fill="currentColor"
                                        class="w-5 h-5 {{ $i <= $request->review->rating ? 'text-amber-400' : 'text-stone-300' }}" />
                            @endfor
                            <span class="ml-1.5 text-sm font-extrabold text-amber-700">{{ $request->review->rating }}/5</span>
                        </div>
                        @if ($request->review->comment)
                            <p class="mt-1.5 text-xs text-stone-600 leading-relaxed">{{ $request->review->comment }}</p>
                        @endif
                    </div>
                @endif
            </x-card>

            <x-card>
                <h2 class="font-extrabold text-stone-900 mb-3">Profil Pasien</h2>
                <dl class="text-sm space-y-2.5">
                    <div class="flex justify-between gap-3">
                        <dt class="text-stone-400">Nama</dt>
                        <dd class="font-semibold text-stone-800 text-right">{{ $patient->name }}</dd>
                    </div>
                    @if ($patient->age())
                        <div class="flex justify-between gap-3">
                            <dt class="text-stone-400">Usia</dt>
                            <dd class="font-semibold text-stone-800">{{ $patient->age() }} tahun</dd>
                        </div>
                    @endif
                    @if ($patient->gender)
                        <div class="flex justify-between gap-3">
                            <dt class="text-stone-400">Jenis kelamin</dt>
                            <dd class="font-semibold text-stone-800">{{ $patient->gender->label() }}</dd>
                        </div>
                    @endif
                    @if ($patient->blood_type && $patient->blood_type !== 'tidak_tahu')
                        <div class="flex justify-between gap-3">
                            <dt class="text-stone-400">Golongan darah</dt>
                            <dd class="font-semibold text-stone-800">{{ $patient->blood_type }}</dd>
                        </div>
                    @endif
                    @if ($latestAssessment)
                        <div class="flex justify-between gap-3">
                            <dt class="text-stone-400">TD terakhir</dt>
                            <dd class="font-semibold text-stone-800">
                                {{ $latestAssessment->systolic_bp ? $latestAssessment->systolic_bp.'/'.$latestAssessment->diastolic_bp.' mmHg' : '—' }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </x-card>

            {{-- Lampiran --}}
            <x-card>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <h2 class="font-extrabold text-stone-900">Lampiran</h2>
                    @if ($canVisit)
                        <button type="button" x-data @click="$dispatch('open-modal', 'modal-upload')"
                                class="text-xs font-bold text-brand-700 hover:text-brand-800 inline-flex items-center gap-1 min-h-9 px-2">
                            <x-icon name="plus" class="w-4 h-4" /> Unggah
                        </button>
                    @endif
                </div>
                @if ($appointment->attachments->isEmpty())
                    <p class="text-sm text-stone-400">Belum ada lampiran. Unggah dokumentasi pelayanan (foto sebelum/sesudah, lembar kerja, dll).</p>
                @else
                    <ul class="space-y-2">
                        @foreach ($appointment->attachments as $attachment)
                            <li class="flex items-center gap-3 rounded-xl ring-1 ring-stone-200/70 bg-stone-50 p-3 text-sm">
                                <x-icon name="{{ str_contains($attachment->mime_type, 'pdf') ? 'document' : 'camera' }}" class="w-5 h-5 text-stone-400 shrink-0" />
                                <span class="min-w-0 flex-1">
                                    <span class="block font-semibold text-stone-700 truncate">{{ $attachment->original_name }}</span>
                                    <span class="block text-xs text-stone-400">{{ $attachment->collectionLabel() }} · {{ $attachment->formattedSize() }}</span>
                                </span>
                                <a href="{{ route('attachments.download', $attachment) }}" class="rounded-lg p-2 text-stone-400 hover:text-brand-700 hover:bg-white transition shrink-0" aria-label="Unduh">
                                    <x-icon name="chevron-down" class="w-4.5 h-4.5" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>

            <x-emergency-banner compact />
        </div>
    </div>

    {{-- Modal: check-in --}}
    <x-modal name="modal-checkin" title="Check-in di Lokasi" maxWidth="max-w-md">
        <form method="POST" action="{{ route('tugas.checkin', $appointment) }}" class="space-y-4">
            @csrf
            <p class="text-sm text-stone-500">Tandai bahwa Anda sudah tiba di lokasi kunjungan.</p>
            <x-textarea name="notes" label="Catatan check-in (opsional)" rows="2" maxlength="500"
                        placeholder="Contoh: diterima keluarga, pasien sedang tidur." />
            <div class="flex gap-3 pt-1">
                <x-button type="button" variant="secondary" full x-on:click="$dispatch('close-modal', 'modal-checkin')">Batal</x-button>
                <x-button type="submit" full icon="check">Check-in</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Modal: asesmen --}}
    <x-modal name="modal-asesmen" title="Catat Asesmen Pasien" maxWidth="max-w-lg">
        <form method="POST" action="{{ route('tugas.asesmen', $appointment) }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <x-input name="systolic_bp" type="number" label="Sistole (mmHg)" inputmode="numeric" min="50" max="300"
                         :value="$latestAssessment->systolic_bp ?? null" />
                <x-input name="diastolic_bp" type="number" label="Diastole (mmHg)" inputmode="numeric" min="30" max="200"
                         :value="$latestAssessment->diastolic_bp ?? null" />
                <x-input name="pulse" type="number" label="Nadi (×/mnt)" inputmode="numeric" min="20" max="300"
                         :value="$latestAssessment->pulse ?? null" />
                <x-input name="respiratory_rate" type="number" label="Napas (×/mnt)" inputmode="numeric" min="5" max="80"
                         :value="$latestAssessment->respiratory_rate ?? null" />
                <x-input name="temperature_c" type="number" step="0.1" label="Suhu (°C)" inputmode="decimal" min="30" max="45"
                         :value="$latestAssessment->temperature_c ?? null" />
                <x-input name="oxygen_saturation" type="number" label="SpO₂ (%)" inputmode="numeric" min="50" max="100"
                         :value="$latestAssessment->oxygen_saturation ?? null" />
                <x-input name="pain_scale" type="number" label="Skala nyeri (0–10)" inputmode="numeric" min="0" max="10"
                         :value="$latestAssessment->pain_scale ?? null" />
                <x-select name="consciousness" label="Kesadaran" placeholder="Pilih..."
                          :options="['compos_mentis' => 'Compos mentis', 'apatis' => 'Apatis', 'somnolen' => 'Somnolen', 'sopor' => 'Sopor', 'koma' => 'Koma']"
                          :value="$latestAssessment->consciousness ?? null" />
                <x-input name="weight_kg" type="number" step="0.1" label="Berat (kg)" inputmode="decimal" min="1" max="400"
                         :value="$latestAssessment->weight_kg ?? null" />
                <x-input name="height_cm" type="number" step="0.1" label="Tinggi (cm)" inputmode="decimal" min="20" max="260"
                         :value="$latestAssessment->height_cm ?? null" />
            </div>
            <x-textarea name="findings" label="Temuan pemeriksaan" rows="3" maxlength="3000"
                        placeholder="Contoh: luka operasi bersih, tidak ada tanda infeksi, pasien mampu duduk tegak 15 menit." />
            <x-input name="patient_designated_staff" type="text" label="Petugas yang ditunjuk pasien (opsional)"
                     :value="$latestAssessment->patient_designated_staff ?? null"
                     placeholder="Contoh: Ns. Siti Rahmawati" />
            <div class="flex gap-3 pt-1">
                <x-button type="button" variant="secondary" full x-on:click="$dispatch('close-modal', 'modal-asesmen')">Batal</x-button>
                <x-button type="submit" full icon="activity">Simpan Asesmen</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Modal: check-out / dokumentasi --}}
    <x-modal name="modal-selesai" title="Selesaikan Kunjungan" maxWidth="max-w-lg">
        <form method="POST" action="{{ route('tugas.selesai', $appointment) }}" class="space-y-4">
            @csrf
            <x-alert type="info">Dokumentasi ini akan terlihat oleh koordinator & keluarga pasien. Tulis dengan jelas dan profesional.</x-alert>

            <x-textarea name="actions_taken" label="Tindakan yang dilakukan" required rows="3" maxlength="3000"
                        placeholder="Contoh: mengganti balutan luka operasi, membersihkan sekitar luka dengan NaCl, menutup kassa steril." />
            <x-textarea name="results" label="Hasil / evaluasi tindakan" rows="3" maxlength="3000"
                        placeholder="Contoh: luka tampak bersih, jaringan kemerahan berkurang, tidak ada pus." />
            <x-textarea name="recommendations" label="Anjuran untuk pasien/keluarga" rows="2" maxlength="3000"
                        placeholder="Contoh: jaga balutan tetap kering, kontrol ke poli bedah 3 hari lagi." />

            <div x-data="{ followUp: {{ old('follow_up_needed', false) ? 'true' : 'false' }} }">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="follow_up_needed" value="0">
                    <input type="checkbox" name="follow_up_needed" value="1" x-model="followUp"
                           class="w-5 h-5 rounded-lg border-stone-300 text-brand-600 focus:ring-brand-500 focus:ring-offset-0" />
                    <span class="text-sm font-semibold text-stone-700">Perlu kunjungan tindak lanjut</span>
                </label>
                <div x-show="followUp" x-collapse class="mt-3">
                    <x-textarea name="follow_up_notes" label="Catatan tindak lanjut" rows="2" maxlength="1000"
                                placeholder="Contoh: ganti balutan ulang 2 hari lagi." />
                </div>
            </div>

            <div class="flex gap-3 pt-1">
                <x-button type="button" variant="secondary" full x-on:click="$dispatch('close-modal', 'modal-selesai')">Batal</x-button>
                <x-button type="submit" size="lg" full icon="check">Check-out & Simpan</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Modal: unggah lampiran --}}
    <x-modal name="modal-upload" title="Unggah Lampiran" maxWidth="max-w-md">
        <form method="POST" action="{{ route('attachments.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="attachable_type" value="appointment">
            <input type="hidden" name="attachable_id" value="{{ $appointment->id }}">

            <x-select name="collection" label="Jenis dokumen" required
                      :options="config('homecare.uploads.collections')" placeholder="Pilih jenis..." />

            <div class="space-y-1.5">
                <label for="file" class="block text-sm font-semibold text-stone-700">Berkas <span class="text-rose-500">*</span></label>
                <input type="file" id="file" name="file" required accept=".jpg,.jpeg,.png,.webp,.pdf"
                       class="block w-full text-sm text-stone-600 file:mr-3 file:rounded-xl file:border-0 file:bg-brand-50 file:px-4 file:py-2.5 file:text-sm file:font-bold file:text-brand-700 hover:file:bg-brand-100 file:cursor-pointer file:min-h-11 rounded-xl ring-1 ring-stone-300 bg-white p-1.5" />
                <p class="text-xs text-stone-400">JPG/PNG/WEBP/PDF, maks {{ round(config('homecare.uploads.max_size_kb') / 1024) }} MB.</p>
                @error('file') <p class="text-sm font-medium text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-1">
                <x-button type="button" variant="secondary" full x-on:click="$dispatch('close-modal', 'modal-upload')">Batal</x-button>
                <x-button type="submit" full icon="camera">Unggah</x-button>
            </div>
        </form>
    </x-modal>
@endsection
