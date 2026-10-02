@extends('layouts.admin')

@php
    $patient = $request->patient;
    $appointment = $request->appointment;
    $status = $request->status->value;
    $inVerification = in_array($status, ['submitted', 'under_review', 'need_information']);
@endphp

@section('title', $request->code.' — Pengajuan')

@section('content')
    <nav class="text-sm mb-4">
        <a href="{{ route('operasional.pengajuan.index') }}" class="font-semibold text-stone-400 hover:text-brand-700">← Semua pengajuan</a>
    </nav>

    {{-- Header --}}
    <div class="bg-white rounded-3xl ring-1 ring-stone-200/70 shadow-card p-6 mb-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="font-mono text-xs font-bold text-stone-400">{{ $request->code }}</p>
                <h1 class="mt-1 text-xl sm:text-2xl font-extrabold text-stone-900">{{ $patient->name }}</h1>
                <p class="mt-1 text-sm text-stone-500">
                    {{ $request->items->pluck('service_name')->unique()->implode(', ') }}
                    · diajukan {{ $request->submitted_at?->translatedFormat('j M Y, H.i') }} oleh {{ $request->user->name }}
                </p>
            </div>
            <div class="flex flex-col items-end gap-2">
                <x-status-badge :status="$request->status" />
                <x-status-badge :status="$request->payment_status" />
            </div>
        </div>

        {{-- Bilah aksi sesuai status --}}
        <div class="mt-6 flex flex-wrap gap-2.5">
            @if ($status === 'submitted' && $canVerify)
                <form method="POST" action="{{ route('operasional.pengajuan.verifikasi', $request) }}">
                    @csrf
                    <x-button type="submit" icon="search" size="sm">Mulai Verifikasi</x-button>
                </form>
            @endif

            @if ($inVerification && $canVerify)
                <x-button variant="secondary" icon="question" size="sm" x-data x-on:click="$dispatch('open-modal', 'modal-info')">
                    Minta Informasi
                </x-button>
                <x-button variant="secondary" icon="check" size="sm" x-data x-on:click="$dispatch('open-modal', 'modal-approve')">
                    Setujui
                </x-button>
                <x-button variant="danger-soft" icon="x-circle" size="sm" x-data x-on:click="$dispatch('open-modal', 'modal-reject')">
                    Tolak
                </x-button>
            @endif

            @if ($status === 'approved' && $canSchedule)
                <x-button icon="calendar" size="sm" x-data x-on:click="$dispatch('open-modal', 'modal-schedule')">
                    Tetapkan Jadwal & Petugas
                </x-button>
            @endif

            @if ($canRecordPayment && ! in_array($request->payment_status->value, ['paid', 'waived']))
                <x-button variant="secondary" icon="wallet" size="sm" x-data x-on:click="$dispatch('open-modal', 'modal-payment')">
                    Catat Pembayaran
                </x-button>
            @endif

            @if ($canVerify && ! $request->status->isFinal() && $status !== 'completed')
                <x-button variant="danger-soft" icon="trash" size="sm" x-data x-on:click="$dispatch('open-modal', 'modal-cancel')">
                    Batalkan
                </x-button>
            @endif

            @if ($appointment)
                <x-button :href="route('operasional.jadwal.show', $appointment)" variant="ghost" icon="activity" size="sm">
                    Monitor Kunjungan
                </x-button>
            @endif
        </div>

        {{-- Status khusus --}}
        @if ($status === 'need_information')
            <x-alert type="warning" class="mt-5" title="Menunggu jawaban pasien">
                <p><span class="font-bold">Kita tanyakan:</span> {{ $request->information_request }}</p>
                @if ($request->information_response)
                    <p class="mt-2 rounded-xl bg-white/70 px-3.5 py-2.5">
                        <span class="font-bold">Jawaban pasien:</span> {{ $request->information_response }}
                    </p>
                @else
                    <p class="mt-2 text-sm">Pasien belum menjawab. Anda tetap bisa menyetujui/menolak bila informasi sudah cukup.</p>
                @endif
            </x-alert>
        @elseif ($status === 'rejected')
            <x-alert type="error" class="mt-5" title="Ditolak">
                {{ $request->rejected_reason }}
                @if ($request->screener) · oleh {{ $request->screener->name }} @endif
            </x-alert>
        @elseif ($status === 'cancelled')
            <x-alert type="info" class="mt-5" title="Dibatalkan">
                {{ $request->cancellation_reason }}
            </x-alert>
        @endif

        @if ($request->screening_notes)
            <div class="mt-4 rounded-2xl bg-stone-50 ring-1 ring-stone-200/70 p-4 text-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-stone-400 mb-1.5">Catatan skrining</p>
                <p class="text-stone-600 leading-relaxed">{{ $request->screening_notes }}</p>
            </div>
        @endif
    </div>

    <div class="grid lg:grid-cols-5 gap-5 items-start">
        {{-- Kolom utama --}}
        <div class="lg:col-span-3 space-y-5">
            {{-- Jadwal & petugas --}}
            @if ($appointment)
                <x-card>
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                        <h2 class="font-extrabold text-stone-900">Jadwal & Petugas</h2>
                        <x-status-badge :status="$appointment->status" />
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-stone-400">Waktu</p>
                            <p class="mt-1 font-extrabold text-stone-900">{{ $appointment->scheduled_at->translatedFormat('l, j M Y') }}</p>
                            <p class="text-stone-500">{{ $appointment->scheduled_at->format('H.i') }} WIB · ±{{ $appointment->estimated_duration_minutes }} mnt</p>
                            @if ($appointment->checkin_at)
                                <p class="mt-2 text-xs text-stone-400">Check-in {{ $appointment->checkin_at->format('H.i') }}
                                    @if ($appointment->checkout_at) · Check-out {{ $appointment->checkout_at->format('H.i') }} @endif
                                </p>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-stone-400">Petugas ditugaskan</p>
                            <div class="mt-2 space-y-2">
                                @forelse ($appointment->assignments->whereNotIn('status', ['cancelled']) as $assignment)
                                    <div class="flex items-center justify-between gap-2 rounded-xl bg-stone-50 ring-1 ring-stone-200/70 px-3 py-2">
                                        <span class="min-w-0">
                                            <span class="block font-bold text-stone-800 text-sm truncate">{{ $assignment->staff->name }}</span>
                                            <span class="block text-xs text-stone-400">{{ $assignment->staff->profession->label() }}</span>
                                        </span>
                                        <x-status-badge :status="$assignment->status" />
                                    </div>
                                @empty
                                    <p class="text-stone-400 text-sm">Belum ada petugas.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    @if ($appointment->notes)
                        <p class="mt-3 text-sm text-stone-500 bg-stone-50 rounded-xl px-3.5 py-2.5">📝 {{ $appointment->notes }}</p>
                    @endif

                    {{-- Ringkasan pelaksanaan --}}
                    @if ($appointment->assessments->isNotEmpty() || $appointment->serviceRecords->isNotEmpty())
                        <div class="mt-5 pt-5 border-t-2 border-dashed border-stone-200 space-y-4">
                            @if ($appointment->assessments->isNotEmpty())
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wide text-stone-400 mb-2">Asesmen ({{ $appointment->assessments->count() }})</p>
                                    @foreach ($appointment->assessments as $assessment)
                                        <div class="rounded-xl bg-stone-50 ring-1 ring-stone-200/70 p-3.5 text-sm mb-2">
                                            <p class="text-xs text-stone-400 mb-1.5">{{ $assessment->assessed_at->translatedFormat('j M, H.i') }} · {{ $assessment->staff->name }}</p>
                                            <p class="text-stone-700">
                                                @if ($assessment->systolic_bp) TD {{ $assessment->systolic_bp }}/{{ $assessment->diastolic_bp }} · @endif
                                                @if ($assessment->pulse) N {{ $assessment->pulse }} · @endif
                                                @if ($assessment->temperature_c) S {{ $assessment->temperature_c }}°C · @endif
                                                @if ($assessment->oxygen_saturation) SpO₂ {{ $assessment->oxygen_saturation }}% @endif
                                            </p>
                                            @if ($assessment->findings)
                                                <p class="mt-1.5 text-stone-500 text-xs leading-relaxed">{{ $assessment->findings }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            @if ($appointment->serviceRecords->isNotEmpty())
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wide text-stone-400 mb-2">Dokumentasi Pelayanan</p>
                                    @foreach ($appointment->serviceRecords as $record)
                                        <div class="rounded-xl bg-emerald-50/60 ring-1 ring-emerald-100 p-3.5 text-sm mb-2">
                                            <p class="font-semibold text-stone-800">{{ $record->actions_taken }}</p>
                                            @if ($record->results) <p class="mt-1 text-stone-600 text-xs">Hasil: {{ $record->results }}</p> @endif
                                            @if ($record->recommendations) <p class="mt-1 text-stone-600 text-xs">Anjuran: {{ $record->recommendations }}</p> @endif
                                            @if ($record->follow_up_needed)
                                                <p class="mt-1.5 text-xs font-bold text-warm-600">Perlu tindak lanjut{{ $record->follow_up_notes ? ': '.$record->follow_up_notes : '' }}</p>
                                            @endif
                                            <p class="mt-1.5 text-xs text-stone-400">{{ $record->staff->name }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </x-card>
            @endif

            {{-- Permintaan keluarga --}}
            <x-card>
                <h2 class="font-extrabold text-stone-900 mb-3">Permintaan Keluarga</h2>
                <p class="text-sm text-stone-600 leading-relaxed">{{ $request->complaint }}</p>
                @if ($request->notes)
                    <p class="mt-3 text-sm text-stone-500 bg-stone-50 rounded-xl px-3.5 py-2.5">📝 {{ $request->notes }}</p>
                @endif
                @if ($patient->medical_notes)
                    <p class="mt-3 text-sm text-warm-600 bg-warm-50 ring-1 ring-warm-100 rounded-xl px-3.5 py-2.5 font-medium">
                        Catatan kesehatan: {{ $patient->medical_notes }}
                    </p>
                @endif
                <div class="mt-4 grid sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-stone-400">Preferensi jadwal</p>
                        <p class="mt-1 font-semibold text-stone-800">
                            {{ $request->preferred_date?->translatedFormat('l, j F Y') }}
                        </p>
                        @if ($request->preferred_time_window)
                            <p class="text-stone-500 text-xs">{{ config("homecare.time_windows.{$request->preferred_time_window->value}.label") }}</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-stone-400">Pengaju</p>
                        <p class="mt-1 font-semibold text-stone-800">{{ $request->user->name }}</p>
                    </div>
                </div>
            </x-card>

            {{-- Timeline --}}
            <x-card>
                <h2 class="font-extrabold text-stone-900 mb-5">Riwayat Status</h2>
                <x-status-timeline :steps="$timeline" />
            </x-card>
        </div>

        {{-- Kolom samping --}}
        <div class="lg:col-span-2 space-y-5">
            {{-- Pasien & lokasi --}}
            <x-card>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <h2 class="font-extrabold text-stone-900">Pasien & Lokasi</h2>
                    <a href="{{ route('operasional.pasien.show', $patient) }}" class="text-xs font-bold text-brand-700 hover:text-brand-800">Profil →</a>
                </div>
                <dl class="text-sm space-y-2.5">
                    <div class="flex justify-between gap-3">
                        <dt class="text-stone-400">Nama</dt>
                        <dd class="font-semibold text-stone-800 text-right">{{ $patient->name }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-stone-400">Usia / JK</dt>
                        <dd class="font-semibold text-stone-800">
                            {{ $patient->age() ? $patient->age().' th' : '—' }} · {{ $patient->gender?->label() ?? '—' }}
                        </dd>
                    </div>
                    @if ($patient->nik)
                        <div class="flex justify-between gap-3">
                            <dt class="text-stone-400">NIK</dt>
                            <dd class="font-mono text-xs font-semibold text-stone-600">{{ $patient->nik }}</dd>
                        </div>
                    @endif
                    @foreach ($patient->contacts as $contact)
                        <div class="flex justify-between gap-3">
                            <dt class="text-stone-400">{{ $contact->label ?: $contact->type->label() }}</dt>
                            <dd><a href="tel:{{ preg_replace('/\s+/', '', $contact->value) }}" class="font-semibold text-brand-700 hover:underline">{{ $contact->value }}</a></dd>
                        </div>
                    @endforeach
                </dl>
                <div class="mt-4 pt-4 border-t border-stone-100 text-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-stone-400 mb-1.5">Alamat kunjungan</p>
                    <p class="font-semibold text-stone-800">{{ $request->address->label ?: 'Alamat' }}</p>
                    <p class="mt-0.5 text-stone-500 leading-relaxed">{{ $request->address->oneLine() }}</p>
                    <p class="mt-1 text-xs text-stone-400">a.n. {{ $request->address->recipient_name }} ·
                        <a href="tel:{{ preg_replace('/\s+/', '', $request->address->phone) }}" class="font-semibold text-brand-700 hover:underline">{{ $request->address->phone }}</a>
                    </p>
                    @if ($request->address->notes)
                        <p class="mt-2 text-xs text-warm-600 bg-warm-50 rounded-lg px-2.5 py-1.5 font-medium">📝 {{ $request->address->notes }}</p>
                    @endif
                </div>
                <div class="mt-4 pt-4 border-t border-stone-100 text-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-stone-400 mb-1">Dipesan oleh</p>
                    <p class="font-semibold text-stone-800">{{ $request->user->name }}</p>
                    <p class="text-xs text-stone-400">{{ $request->user->email }} @if($request->user->phone) · {{ $request->user->phone }} @endif</p>
                </div>
            </x-card>

            {{-- Layanan & biaya --}}
            <x-card>
                <h2 class="font-extrabold text-stone-900 mb-3">Layanan & Biaya</h2>
                <ul class="space-y-2.5 text-sm">
                    @foreach ($request->items as $item)
                        <li class="flex items-start justify-between gap-3">
                            <span class="min-w-0">
                                <span class="block font-semibold text-stone-800">{{ $item->service_name }}</span>
                                <span class="block text-xs text-stone-400">
                                    {{ $item->formattedUnitPrice() }} @if ($item->quantity > 1) × {{ $item->quantity }} @endif
                                    @if ($item->notes) · {{ $item->notes }} @endif
                                </span>
                            </span>
                            <span class="font-bold text-stone-800 shrink-0">{{ $item->formattedSubtotal() }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-4 pt-4 border-t-2 border-dashed border-stone-200 flex items-center justify-between">
                    <span class="font-extrabold text-stone-900">Total</span>
                    <span class="font-extrabold text-brand-700 text-lg">{{ $request->formattedTotal() }}</span>
                </div>

                {{-- Pembayaran --}}
                @if ($request->payments->isNotEmpty())
                    <ul class="mt-4 space-y-2">
                        @foreach ($request->payments as $payment)
                            <li class="rounded-xl bg-stone-50 ring-1 ring-stone-200/70 p-3 text-sm">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-bold text-stone-800">{{ $payment->formattedAmount() }}</span>
                                    <x-status-badge :status="$payment->status" />
                                </div>
                                <p class="mt-1 text-xs text-stone-400">
                                    {{ $payment->method->label() }}
                                    @if ($payment->reference_number) · ref {{ $payment->reference_number }} @endif
                                    · {{ $payment->paid_at?->translatedFormat('j M Y') }}
                                </p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>

            {{-- Lampiran --}}
            <x-card>
                <h2 class="font-extrabold text-stone-900 mb-3">Lampiran ({{ $request->attachments->count() }})</h2>
                @if ($request->attachments->isEmpty())
                    <p class="text-sm text-stone-400">Tidak ada dokumen yang diunggah.</p>
                @else
                    <ul class="space-y-2">
                        @foreach ($request->attachments as $attachment)
                            <li class="flex items-center gap-3 rounded-xl ring-1 ring-stone-200/70 bg-stone-50 p-3 text-sm">
                                <x-icon name="{{ str_contains($attachment->mime_type, 'pdf') ? 'document' : 'camera' }}" class="w-5 h-5 text-stone-400 shrink-0" />
                                <span class="min-w-0 flex-1">
                                    <span class="block font-semibold text-stone-700 truncate">{{ $attachment->original_name }}</span>
                                    <span class="block text-xs text-stone-400">{{ $attachment->collectionLabel() }} · {{ $attachment->uploader?->name ?? '—' }}</span>
                                </span>
                                <a href="{{ route('attachments.download', $attachment) }}" class="rounded-lg p-2 text-stone-400 hover:text-brand-700 hover:bg-white transition shrink-0" aria-label="Unduh">
                                    <x-icon name="chevron-down" class="w-4.5 h-4.5" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>

            {{-- Audit trail --}}
            <x-card>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <h2 class="font-extrabold text-stone-900">Audit Trail</h2>
                    @can('audit.view')
                        <a href="{{ route('operasional.audit.index', ['q' => $request->code]) }}" class="text-xs font-bold text-brand-700 hover:text-brand-800">Semua →</a>
                    @endcan
                </div>
                @if ($request->auditLogs->isEmpty())
                    <p class="text-sm text-stone-400">Belum ada aktivitas tercatat.</p>
                @else
                    <ol class="space-y-3">
                        @foreach ($request->auditLogs->sortByDesc('created_at')->take(10) as $log)
                            <li class="flex gap-3 text-sm">
                                <span class="mt-1 w-2 h-2 rounded-full bg-brand-400 shrink-0" aria-hidden="true"></span>
                                <span class="min-w-0">
                                    <span class="block font-semibold text-stone-700">{{ $log->description }}</span>
                                    <span class="block text-xs text-stone-400 mt-0.5">
                                        <span class="font-mono">{{ $log->event }}</span> · {{ $log->user?->name ?? 'Sistem' }} · {{ $log->created_at->translatedFormat('j M, H.i') }}
                                    </span>
                                </span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-card>
        </div>
    </div>

    {{-- Modal: minta informasi --}}
    <x-modal name="modal-info" title="Minta Informasi Tambahan" maxWidth="max-w-md">
        <form method="POST" action="{{ route('operasional.pengajuan.minta-informasi', $request) }}" class="space-y-4">
            @csrf
            <p class="text-sm text-stone-500">Pertanyaan akan dikirim ke pasien (notifikasi + dashboard). Pengajuan berstatus <span class="font-bold">Perlu Informasi</span> sampai mereka menjawab.</p>
            <x-textarea name="questions" label="Yang perlu ditanyakan" required rows="4" maxlength="2000"
                        placeholder="Contoh: Mohon kirimkan foto luka terkini dan daftar obat yang sedang dikonsumsi." />
            <div class="flex gap-3 pt-1">
                <x-button type="button" variant="secondary" full x-on:click="$dispatch('close-modal', 'modal-info')">Batal</x-button>
                <x-button type="submit" variant="warm" full icon="question">Kirim</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Modal: setujui --}}
    <x-modal name="modal-approve" title="Setujui Pengajuan" maxWidth="max-w-lg">
        <form method="POST" action="{{ route('operasional.pengajuan.setujui', $request) }}" class="space-y-4"
              x-data="{ rows: [] }">
            @csrf
            <x-alert type="info">Skrining: pastikan layanan sesuai kondisi pasien. Anda dapat menyesuaikan item layanan bila perlu.</x-alert>

            <x-textarea name="screening_notes" label="Catatan skrining (opsional)" rows="3" maxlength="2000"
                        placeholder="Contoh: pasien stabil, layak untuk perawatan luka di rumah; butuh perawat wound care." />

            {{-- Penyesuaian layanan (opsional) --}}
            <div>
                <p class="block text-sm font-semibold text-stone-700 mb-2">Sesuaikan layanan <span class="font-normal text-stone-400">(opsional — kosongkan bila sama dengan permintaan)</span></p>
                <template x-for="(row, idx) in rows" :key="idx">
                    <div class="flex gap-2 mb-2">
                        <select :name="`services[${idx}][homecare_service_id]`" required
                                class="flex-1 rounded-xl ring-1 ring-stone-300 bg-white px-3.5 py-2.5 text-sm text-stone-800 focus:outline-none focus:ring-2 focus:ring-brand-500 min-h-11">
                            <option value="" disabled selected>Pilih layanan...</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }} — {{ $service->formattedPrice() }}</option>
                            @endforeach
                        </select>
                        <input type="number" :name="`services[${idx}][quantity]`" value="1" min="1" max="99"
                               class="w-20 rounded-xl ring-1 ring-stone-300 bg-white px-3 py-2.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-brand-500 min-h-11" />
                        <button type="button" @click="rows.splice(idx, 1)" aria-label="Hapus baris"
                                class="rounded-xl p-2.5 text-stone-400 hover:text-rose-500 hover:bg-rose-50 transition shrink-0">
                            <x-icon name="trash" class="w-4.5 h-4.5" />
                        </button>
                    </div>
                </template>
                <button type="button" @click="rows.push({})" x-show="rows.length < 8"
                        class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-700 hover:text-brand-800 min-h-9">
                    <x-icon name="plus" class="w-4 h-4" /> Tambah layanan
                </button>
            </div>

            <div class="flex gap-3 pt-1">
                <x-button type="button" variant="secondary" full x-on:click="$dispatch('close-modal', 'modal-approve')">Batal</x-button>
                <x-button type="submit" full icon="check">Setujui</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Modal: tolak --}}
    <x-modal name="modal-reject" title="Tolak Pengajuan" maxWidth="max-w-md">
        <form method="POST" action="{{ route('operasional.pengajuan.tolak', $request) }}" class="space-y-4">
            @csrf
            <p class="text-sm text-stone-500">Alasan akan ditampilkan ke pasien. Tulis dengan jelas dan empatik.</p>
            <x-textarea name="reason" label="Alasan penolakan" required rows="3" maxlength="1000"
                        placeholder="Contoh: kondisi pasien memerlukan perawatan rawat inap, disarankan ke poli penyakit dalam." />
            <div class="flex gap-3 pt-1">
                <x-button type="button" variant="secondary" full x-on:click="$dispatch('close-modal', 'modal-reject')">Batal</x-button>
                <x-button type="submit" variant="danger" full icon="x-circle">Tolak Pengajuan</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Modal: jadwalkan --}}
    <x-modal name="modal-schedule" title="Tetapkan Jadwal & Petugas" maxWidth="max-w-lg">
        <form method="POST" action="{{ route('operasional.pengajuan.jadwalkan', $request) }}" class="space-y-4">
            @csrf

            <x-input name="scheduled_at" type="datetime-local" label="Jadwal kunjungan" required icon="calendar"
                     :min="now()->format('Y-m-d\TH.i')"
                     :value="$request->preferred_date?->format('Y-m-d').'T09:00'"
                     hint="Preferensi pasien: {{ $request->preferred_date?->translatedFormat('j F Y') }} {{ $request->preferred_time_window?->label() }}" />

            <div x-data="{ q: '' }">
                <p class="block text-sm font-semibold text-stone-700 mb-2">
                    Petugas ditugaskan <span class="text-rose-500">*</span>
                </p>
                <div class="relative mb-2">
                    <x-icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400" />
                    <input type="search" x-model.debounce.150ms="q" placeholder="Cari nama / profesi petugas..."
                           class="w-full rounded-lg border-0 bg-white py-2 pl-9 pr-3 text-sm ring-1 ring-stone-200 placeholder:text-stone-400 focus:ring-2 focus:ring-brand-500" />
                </div>
                <div class="rounded-xl ring-1 ring-stone-200 bg-stone-50 p-3 space-y-2 max-h-56 overflow-y-auto">
                    @php
                        $checkedIds = (array) old('staff_ids', []);
                        $unchecked = $availableStaff->reject(fn ($m) => in_array($m->id, $checkedIds));
                        $checked = $availableStaff->filter(fn ($m) => in_array($m->id, $checkedIds));
                    @endphp
                    @forelse ($unchecked as $member)
                        <label x-show="!q || $el.dataset.search.includes(q.toLowerCase())"
                               data-search="{{ strtolower($member->name.' '.$member->profession->label().' '.$member->specialization) }}"
                               class="flex items-center gap-3 cursor-pointer rounded-lg bg-white ring-1 ring-stone-200/70 px-3 py-2.5 hover:ring-brand-300 transition">
                            <input type="checkbox" name="staff_ids[]" value="{{ $member->id }}"
                                   @checked(in_array($member->id, $checkedIds))
                                   class="w-4.5 h-4.5 rounded border-stone-300 text-brand-600 focus:ring-brand-500 focus:ring-offset-0" />
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-bold text-stone-800">{{ $member->name }}</span>
                                <span class="block text-xs text-stone-400">
                                    {{ $member->profession->label() }}
                                    @if ($member->specialization) · {{ $member->specialization }} @endif
                                </span>
                            </span>
                        </label>
                    @empty
                        @if (!$checked->count())
                            <p class="text-sm text-stone-400 p-2">Tidak ada petugas aktif. Tambahkan lewat menu Tenaga Kesehatan.</p>
                        @endif
                    @endforelse
                    @if ($checked->count())
                        <div class="pt-2 border-t border-stone-200 mt-2">
                            <p class="text-[11px] font-bold text-stone-400 uppercase tracking-wider mb-2">Sudah dipilih</p>
                            @foreach ($checked as $member)
                                <label class="flex items-center gap-3 cursor-pointer rounded-lg bg-brand-50 ring-1 ring-brand-200 px-3 py-2.5">
                                    <input type="checkbox" name="staff_ids[]" value="{{ $member->id }}" checked
                                           class="w-4.5 h-4.5 rounded border-stone-300 text-brand-600 focus:ring-brand-500 focus:ring-offset-0" />
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-bold text-stone-800">{{ $member->name }}</span>
                                        <span class="block text-xs text-stone-400">
                                            {{ $member->profession->label() }}
                                            @if ($member->specialization) · {{ $member->specialization }} @endif
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
                @error('staff_ids') <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p> @enderror
            </div>

            <x-input name="total_amount" type="number" step="1000" min="0" label="Total biaya final (Rp)" inputmode="numeric"
                     :value="(int) $request->total_amount"
                     hint="Kosongkan bila tetap {{ $request->formattedTotal() }}. Invoice otomatis dibuat saat jadwal terbit." />

            <x-textarea name="notes" label="Catatan untuk petugas (opsional)" rows="2" maxlength="1000"
                        placeholder="Contoh: bawa set ganti balutan steril, koordinasi dengan anak pasien (08xx)." />

            <div class="flex gap-3 pt-1">
                <x-button type="button" variant="secondary" full x-on:click="$dispatch('close-modal', 'modal-schedule')">Batal</x-button>
                <x-button type="submit" full icon="calendar">Terbitkan Jadwal</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Modal: pembayaran --}}
    <x-modal name="modal-payment" title="Catat Pembayaran" maxWidth="max-w-md">
        <form method="POST" action="{{ route('operasional.pengajuan.pembayaran', $request) }}" class="space-y-4">
            @csrf
            @php
                $paid = $request->payments->where('status', 'paid')->sum('amount');
                $remaining = max(0, (float) $request->total_amount - (float) $paid);
            @endphp
            <p class="text-sm text-stone-500">
                Total tagihan <span class="font-bold text-stone-800">{{ $request->formattedTotal() }}</span>
                @if ($paid > 0) · sudah dibayar <span class="font-bold text-emerald-600">Rp {{ number_format($paid, 0, ',', '.') }}</span> @endif
            </p>

            <x-select name="method" label="Metode" required
                      :options="collect(\App\Enums\PaymentMethod::cases())->mapWithKeys(fn ($m) => [$m->value => $m->label()])->all()"
                      placeholder="Pilih metode..." />
            <x-input name="amount" type="number" step="100" min="1" label="Nominal (Rp)" required inputmode="numeric"
                     :value="(int) $remaining" :hint="$remaining > 0 ? 'Sisa tagihan: Rp '.number_format($remaining, 0, ',', '.') : null" />
            <x-input name="reference_number" label="Nomor referensi (opsional)" maxlength="96"
                     placeholder="No. struk / bukti transfer" />
            <x-input name="paid_at" type="date" label="Tanggal bayar" icon="calendar" :value="now()->toDateString()" />
            <x-textarea name="notes" label="Catatan (opsional)" rows="2" maxlength="500" />

            <div class="flex gap-3 pt-1">
                <x-button type="button" variant="secondary" full x-on:click="$dispatch('close-modal', 'modal-payment')">Batal</x-button>
                <x-button type="submit" full icon="wallet">Simpan Pembayaran</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Modal: batalkan --}}
    <x-modal name="modal-cancel" title="Batalkan Pengajuan?" maxWidth="max-w-md">
        <form method="POST" action="{{ route('operasional.pengajuan.batal', $request) }}" class="space-y-4">
            @csrf
            <p class="text-sm text-stone-500">Pengajuan <span class="font-mono font-bold text-stone-800">{{ $request->code }}</span> akan dibatalkan. Pasien & petugas terkait akan diberi tahu.</p>
            <x-textarea name="reason" label="Alasan pembatalan" required rows="3" maxlength="1000"
                        placeholder="Contoh: pasien sudah dirawat inap di RS lain." />
            <div class="flex gap-3 pt-1">
                <x-button type="button" variant="secondary" full x-on:click="$dispatch('close-modal', 'modal-cancel')">Tidak Jadi</x-button>
                <x-button type="submit" variant="danger" full icon="trash">Batalkan Pengajuan</x-button>
            </div>
        </form>
    </x-modal>
@endsection
