@extends('layouts.app')

@section('title', 'Pengajuan '.$request->code)

@section('content')
    <nav class="text-sm text-stone-400 mb-4" aria-label="Breadcrumb">
        <a href="{{ route('akun.pengajuan.index') }}" class="hover:text-brand-700 font-semibold">← Semua pengajuan</a>
    </nav>

    {{-- Header: layanan + status --}}
    <div class="bg-white rounded-3xl ring-1 ring-stone-200/70 shadow-card p-6 mb-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-extrabold text-stone-900">
                    {{ $request->items->pluck('service_name')->unique()->implode(', ') ?: 'Pengajuan Homecare' }}
                </h1>
                <p class="mt-1 text-sm text-stone-500">
                    Kode <span class="font-mono font-bold text-stone-700">{{ $request->code }}</span>
                    · diajukan {{ $request->submitted_at?->translatedFormat('j M Y, H.i') }}
                </p>
            </div>
            <x-status-badge :status="$request->status" />
        </div>

        {{-- Pesan sesuai status --}}
        @if ($request->status->value === 'need_information')
            <x-alert type="warning" class="mt-5" title="Kami butuh informasi tambahan">
                <p>{{ $request->information_request }}</p>
                @if ($canProvideInformation)
                    <button type="button" x-data @click="$dispatch('open-modal', 'modal-info')"
                            class="mt-3 inline-flex items-center gap-2 rounded-xl bg-warm-500 hover:bg-warm-600 text-white text-sm font-bold px-4 py-2.5 min-h-11 transition">
                        <x-icon name="edit" class="w-4 h-4" /> Jawab Sekarang
                    </button>
                @endif
            </x-alert>
        @elseif ($request->status->value === 'rejected')
            <x-alert type="error" class="mt-5" title="Pengajuan ditolak">
                <p>{{ $request->rejected_reason ?: 'Tim kami memutuskan pengajuan ini belum dapat dilayani.' }}</p>
                <p class="mt-2 text-sm">Bila ada yang ingin ditanyakan, silakan <a class="font-bold underline" href="{{ route('contact') }}">hubungi kami</a>.</p>
            </x-alert>
        @elseif ($request->status->value === 'cancelled')
            <x-alert type="info" class="mt-5" title="Pengajuan dibatalkan">
                <p>{{ $request->cancellation_reason }}</p>
            </x-alert>
        @elseif ($request->status->value === 'submitted' || $request->status->value === 'under_review')
            <x-alert type="info" class="mt-5">
                <strong>{{ $request->status->patientLabel() }}.</strong> Tim homecare sedang memeriksa pengajuan Anda — biasanya beberapa jam pada jam kerja. Anda akan dikabari bila ada perkembangan.
            </x-alert>
        @endif

        {{-- Kartu jadwal (bila sudah ada) --}}
        @if ($request->appointment)
            @php $appointment = $request->appointment; @endphp
            <div class="mt-5 rounded-2xl ring-1 ring-indigo-100 bg-indigo-50/50 p-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="font-extrabold text-indigo-900 flex items-center gap-2">
                        <x-icon name="calendar" class="w-5 h-5 text-indigo-500" /> Jadwal Kunjungan
                    </h2>
                    <x-status-badge :status="$appointment->status" />
                </div>

                <div class="mt-4 grid sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-indigo-500 text-xs font-bold uppercase tracking-wide">Kapan</p>
                        <p class="mt-1 font-extrabold text-indigo-950 text-base">{{ $appointment->scheduled_at->translatedFormat('l, j F Y') }}</p>
                        <p class="text-indigo-700">{{ $appointment->scheduled_at->format('H.i') }} WIB · ±{{ $appointment->estimated_duration_minutes }} menit</p>
                    </div>
                    <div>
                        <p class="text-indigo-500 text-xs font-bold uppercase tracking-wide">Di mana</p>
                        <p class="mt-1 font-semibold text-indigo-950">{{ $appointment->address_snapshot['one_line'] ?? $request->address->oneLine() }}</p>
                        <p class="text-indigo-600 text-xs mt-0.5">a.n. {{ $appointment->address_snapshot['recipient_name'] ?? $request->address->recipient_name }}</p>
                    </div>
                </div>

                @if ($appointment->assignments->whereNotIn('status', ['cancelled'])->isNotEmpty())
                    <p class="mt-4 text-xs font-bold uppercase tracking-wide text-indigo-500">Siapa yang datang</p>
                    <div class="mt-2 space-y-2">
                        @foreach ($appointment->assignments->whereNotIn('status', ['cancelled']) as $assignment)
                            <div class="flex items-center gap-3 rounded-xl bg-white ring-1 ring-indigo-100 p-3">
                                <span class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold shrink-0">
                                    {{ mb_substr($assignment->staff->name, 0, 1) }}
                                </span>
                                <div class="min-w-0">
                                    <p class="font-bold text-stone-900 text-sm">{{ $assignment->staff->name }}</p>
                                    <p class="text-xs text-stone-500">{{ $assignment->staff->profession->label() }}
                                        @if ($assignment->staff->specialization) · {{ $assignment->staff->specialization }} @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Apa yang harus disiapkan --}}
                <div class="mt-4 rounded-xl bg-white/70 ring-1 ring-indigo-100 p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-indigo-500 flex items-center gap-1.5">
                        <x-icon name="clipboard" class="w-4 h-4" /> Yang perlu disiapkan
                    </p>
                    <ul class="mt-2 space-y-1.5 text-sm text-indigo-900/80">
                        <li class="flex gap-2"><x-icon name="check" class="w-4 h-4 mt-0.5 text-indigo-400 shrink-0" /> Kartu identitas pasien & kartu berobat (bila ada)</li>
                        <li class="flex gap-2"><x-icon name="check" class="w-4 h-4 mt-0.5 text-indigo-400 shrink-0" /> Obat-obatan yang sedang dikonsumsi</li>
                        <li class="flex gap-2"><x-icon name="check" class="w-4 h-4 mt-0.5 text-indigo-400 shrink-0" /> Hasil pemeriksaan terakhir (bila ada)</li>
                        <li class="flex gap-2"><x-icon name="check" class="w-4 h-4 mt-0.5 text-indigo-400 shrink-0" /> Ruangan nyaman dengan pencahayaan cukup</li>
                    </ul>
                </div>
            </div>
        @endif
    </div>

    <div class="grid lg:grid-cols-5 gap-5 items-start">
        {{-- Timeline --}}
        <div class="lg:col-span-3">
            <x-card>
                <h2 class="font-extrabold text-stone-900 mb-5">Perkembangan Pengajuan</h2>
                <x-status-timeline :steps="$timeline" />
            </x-card>

            {{-- Ringkasan pelayanan (bila selesai) --}}
            @if ($request->appointment?->serviceRecords->isNotEmpty())
                <x-card class="mt-5">
                    <h2 class="font-extrabold text-stone-900 mb-3">Ringkasan Pelayanan</h2>
                    @foreach ($request->appointment->serviceRecords as $record)
                        <div class="rounded-2xl bg-stone-50 ring-1 ring-stone-200/70 p-4 text-sm space-y-2.5">
                            <p><span class="font-bold text-stone-700">Tindakan:</span> <span class="text-stone-600">{{ $record->actions_taken }}</span></p>
                            @if ($record->results)
                                <p><span class="font-bold text-stone-700">Hasil:</span> <span class="text-stone-600">{{ $record->results }}</span></p>
                            @endif
                            @if ($record->recommendations)
                                <p><span class="font-bold text-stone-700">Anjuran:</span> <span class="text-stone-600">{{ $record->recommendations }}</span></p>
                            @endif
                            @if ($record->follow_up_needed)
                                <p class="rounded-xl bg-warm-50 ring-1 ring-warm-100 px-3 py-2 text-warm-600 font-semibold">
                                    Perlu tindak lanjut{{ $record->follow_up_notes ? ': '.$record->follow_up_notes : '' }}
                                </p>
                            @endif
                            <p class="text-xs text-stone-400">Oleh {{ $record->staff->name }} · {{ $record->ended_at?->translatedFormat('j M Y, H.i') }}</p>
                        </div>
                    @endforeach
                </x-card>
            @endif

            {{-- Ulasan & rating petugas (muncul setelah pelayanan selesai + lunas) --}}
            @if ($request->review)
                <x-card class="mt-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="font-extrabold text-stone-900">Ulasan Anda</h2>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 ring-1 ring-amber-200 px-3 py-1 text-xs font-bold text-amber-700">
                            {{ $request->review->rating }}/5 · {{ $request->review->label() }}
                        </span>
                    </div>

                    <div class="mt-3 flex items-center gap-1">
                        @for ($i = 1; $i <= 5; $i++)
                            <x-icon name="star" fill="currentColor"
                                    class="w-6 h-6 {{ $i <= $request->review->rating ? 'text-amber-400' : 'text-stone-200' }}" />
                        @endfor
                    </div>

                    @if ($request->review->comment)
                        <p class="mt-3 text-sm text-stone-600 leading-relaxed">{{ $request->review->comment }}</p>
                    @endif

                    @if ($request->review->staff)
                        <p class="mt-3 text-xs text-stone-400">
                            Untuk <span class="font-semibold text-stone-600">{{ $request->review->staff->name }}</span>
                            · {{ $request->review->created_at->translatedFormat('j M Y') }}
                        </p>
                    @endif
                </x-card>
            @elseif ($canReview)
                <x-card class="mt-5">
                    <h2 class="font-extrabold text-stone-900">Bagaimana pelayanan petugas kami?</h2>
                    <p class="mt-1 text-sm text-stone-500">
                        Beri bintang 1–5 untuk
                        <span class="font-semibold text-stone-700">
                            {{ $request->appointment?->primaryStaff()?->name ?? 'petugas yang memeriksa' }}
                        </span>.
                    </p>

                    <form method="POST" action="{{ route('akun.pengajuan.ulasan', $request) }}" class="mt-4"
                          x-data="{ rating: {{ (int) old('rating') }}, hover: 0, picked() { return this.hover || this.rating } }">
                        @csrf

                        <div class="flex items-center gap-1.5" role="radiogroup" aria-label="Rating bintang">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button"
                                        x-on:click="rating = {{ $i }}"
                                        x-on:mouseenter="hover = {{ $i }}"
                                        x-on:mouseleave="hover = 0"
                                        x-bind:aria-checked="rating === {{ $i }}"
                                        role="radio"
                                        x-bind:aria-label="'Bintang {{ $i }}'"
                                        class="rounded-lg p-1 transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5"
                                         class="w-8 h-8 transition-colors"
                                         x-bind:class="picked() >= {{ $i }} ? 'text-amber-400' : 'text-stone-300'"
                                         fill="currentColor" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.563.563 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.563.563 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                                    </svg>
                                </button>
                            @endfor

                            <span class="ml-2 text-sm font-bold text-amber-600" x-show="rating > 0" x-cloak>
                                <span x-text="rating"></span>/5
                            </span>
                        </div>

                        <input type="hidden" name="rating" x-bind:value="rating">

                        <div class="mt-4">
                            <x-textarea name="comment" label="Ulasan (opsional)" rows="3" maxlength="1000"
                                        placeholder="Contoh: petugas datang tepat waktu, penjelasan jelas, dan sangat sabar." />
                        </div>

                        @error('rating') <p class="text-sm font-medium text-rose-600">{{ $message }}</p> @enderror

                        <div class="mt-4">
                            <x-button type="submit" size="lg" icon="sparkles" full>Kirim Ulasan</x-button>
                        </div>
                    </form>
                </x-card>
            @endif
        </div>

        {{-- Sisi kanan: pasien, biaya, dokumen, aksi --}}
        <div class="lg:col-span-2 space-y-5">
            <x-card>
                <h2 class="font-extrabold text-stone-900 mb-3">Detail</h2>
                <dl class="text-sm space-y-3">
                    <div>
                        <dt class="text-stone-400 text-xs font-bold uppercase tracking-wide">Pasien</dt>
                        <dd class="mt-0.5 font-semibold text-stone-800">{{ $request->patient->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-400 text-xs font-bold uppercase tracking-wide">Kebutuhan</dt>
                        <dd class="mt-0.5 text-stone-600 leading-relaxed">{{ $request->complaint }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-400 text-xs font-bold uppercase tracking-wide">Alamat</dt>
                        <dd class="mt-0.5 text-stone-600">{{ $request->address->oneLine() }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-400 text-xs font-bold uppercase tracking-wide">Layanan</dt>
                        <dd class="mt-1 space-y-1.5">
                            @foreach ($request->items as $item)
                                <p class="flex justify-between gap-2">
                                    <span class="text-stone-600">{{ $item->service_name }} @if($item->quantity > 1) ×{{ $item->quantity }} @endif</span>
                                    <span class="font-semibold text-stone-800 shrink-0">{{ $item->formattedSubtotal() }}</span>
                                </p>
                            @endforeach
                        </dd>
                    </div>
                </dl>

                <div class="mt-4 pt-4 border-t-2 border-dashed border-stone-200">
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-stone-900">Total biaya</span>
                        <span class="font-extrabold text-brand-700 text-lg">{{ $request->formattedTotal() }}</span>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-sm">
                        <span class="text-stone-500">Pembayaran</span>
                        <x-status-badge :status="$request->payment_status" />
                    </div>
                    @if ($request->payment_status->value !== 'paid' && $request->payment_status->value !== 'waived' && (float) $request->total_amount > 0)
                        <p class="mt-3 text-xs text-stone-400 leading-relaxed">
                            Pembayaran diselesaikan sesuai arahan koordinator (tunai saat kunjungan atau transfer). Hubungi kami bila ada pertanyaan.
                        </p>
                    @endif
                </div>
            </x-card>

            {{-- Dokumen --}}
            <x-card>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <h2 class="font-extrabold text-stone-900">Dokumen</h2>
                    <button type="button" x-data @click="$dispatch('open-modal', 'modal-upload')"
                            class="text-xs font-bold text-brand-700 hover:text-brand-800 inline-flex items-center gap-1 min-h-9 px-2">
                        <x-icon name="plus" class="w-4 h-4" /> Unggah
                    </button>
                </div>

                @if ($request->attachments->isEmpty())
                    <p class="text-sm text-stone-400">
                        Belum ada dokumen. Anda dapat mengunggah surat rujukan, hasil pemeriksaan, atau foto kondisi.
                    </p>
                @else
                    <ul class="space-y-2">
                        @foreach ($request->attachments as $attachment)
                            <li class="flex items-center gap-3 rounded-xl ring-1 ring-stone-200/70 bg-stone-50 p-3 text-sm">
                                <x-icon name="{{ str_contains($attachment->mime_type, 'pdf') ? 'document' : 'camera' }}" class="w-5 h-5 text-stone-400 shrink-0" />
                                <span class="min-w-0 flex-1">
                                    <span class="block font-semibold text-stone-700 truncate">{{ $attachment->original_name }}</span>
                                    <span class="block text-xs text-stone-400">{{ $attachment->collectionLabel() }} · {{ $attachment->formattedSize() }}</span>
                                </span>
                                <a href="{{ route('attachments.download', $attachment) }}" class="rounded-lg p-2 text-stone-400 hover:text-brand-700 hover:bg-white transition shrink-0" aria-label="Unduh {{ $attachment->original_name }}">
                                    <x-icon name="chevron-down" class="w-4.5 h-4.5" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>

            {{-- Aksi --}}
            @if ($canCancel || $canProvideInformation)
                <div class="space-y-2.5">
                    @if ($canProvideInformation)
                        <x-button variant="warm" full icon="edit" x-data x-on:click="$dispatch('open-modal', 'modal-info')">
                            Lengkapi Informasi
                        </x-button>
                    @endif
                    @if ($canCancel)
                        <x-button variant="danger-soft" full icon="x-circle" x-data x-on:click="$dispatch('open-modal', 'modal-cancel')">
                            Batalkan Pengajuan
                        </x-button>
                    @endif
                </div>
            @endif

            <div class="rounded-2xl bg-stone-100 p-4 text-xs text-stone-500 leading-relaxed">
                Ada pertanyaan tentang pengajuan ini? Hubungi kami di
                <a href="tel:{{ config('homecare.contact.phone') }}" class="font-bold text-brand-700 underline underline-offset-2">{{ config('homecare.contact.phone') }}</a>.
                Untuk kondisi darurat hubungi <a href="tel:{{ config('homecare.emergency_number') }}" class="font-bold text-rose-600 underline underline-offset-2">{{ config('homecare.emergency_number') }}</a>.
            </div>
        </div>
    </div>

    {{-- Modal: jawab permintaan informasi --}}
    <x-modal name="modal-info" title="Lengkapi Informasi">
        <form method="POST" action="{{ route('akun.pengajuan.informasi', $request) }}" class="space-y-4">
            @csrf
            <div class="rounded-xl bg-orange-50 ring-1 ring-orange-200 p-4 text-sm text-orange-800">
                <p class="font-bold">Pertanyaan tim kami:</p>
                <p class="mt-1">{{ $request->information_request }}</p>
            </div>
            <x-textarea name="information_response" label="Jawaban Anda" required rows="4"
                        placeholder="Tuliskan jawaban sejelas mungkin..." />
            <div class="flex gap-3 pt-1">
                <x-button type="button" variant="secondary" full x-on:click="$dispatch('close-modal', 'modal-info')">Nanti Saja</x-button>
                <x-button type="submit" variant="warm" full>Kirim Jawaban</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Modal: pembatalan --}}
    <x-modal name="modal-cancel" title="Batalkan Pengajuan?" maxWidth="max-w-md">
        <form method="POST" action="{{ route('akun.pengajuan.batal', $request) }}" class="space-y-4">
            @csrf
            <p class="text-sm text-stone-500">
                Pengajuan <span class="font-bold text-stone-800">{{ $request->code }}</span> akan dibatalkan dan petugas tidak akan datang.
                Tindakan ini tidak dapat dibatalkan.
            </p>
            <x-textarea name="reason" label="Alasan pembatalan (opsional)" rows="3"
                        placeholder="Contoh: pasien sudah membaik / jadwal tidak cocok" />
            <div class="flex gap-3 pt-1">
                <x-button type="button" variant="secondary" full x-on:click="$dispatch('close-modal', 'modal-cancel')">Tidak Jadi</x-button>
                <x-button type="submit" variant="danger" full>Ya, Batalkan</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Modal: unggah dokumen --}}
    <x-modal name="modal-upload" title="Unggah Dokumen" maxWidth="max-w-md">
        <form method="POST" action="{{ route('attachments.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="attachable_type" value="homecare_request">
            <input type="hidden" name="attachable_id" value="{{ $request->id }}">

            <x-select name="collection" label="Jenis dokumen" required
                      :options="config('homecare.uploads.collections')" placeholder="Pilih jenis dokumen..." />

            <div class="space-y-1.5">
                <label for="file" class="block text-sm font-semibold text-stone-700">Berkas <span class="text-rose-500">*</span></label>
                <input type="file" id="file" name="file" required accept=".jpg,.jpeg,.png,.webp,.pdf"
                       class="block w-full text-sm text-stone-600 file:mr-3 file:rounded-xl file:border-0 file:bg-brand-50 file:px-4 file:py-2.5 file:text-sm file:font-bold file:text-brand-700 hover:file:bg-brand-100 file:cursor-pointer file:min-h-11 rounded-xl ring-1 ring-stone-300 bg-white p-1.5" />
                <p class="text-xs text-stone-400">JPG/PNG/WEBP/PDF, maks {{ round(config('homecare.uploads.max_size_kb') / 1024) }} MB. Disimpan privat.</p>
                @error('file') <p class="text-sm font-medium text-rose-600">{{ $message }}</p> @enderror
                @error('collection') <p class="text-sm font-medium text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-1">
                <x-button type="button" variant="secondary" full x-on:click="$dispatch('close-modal', 'modal-upload')">Batal</x-button>
                <x-button type="submit" full icon="camera">Unggah</x-button>
            </div>
        </form>
    </x-modal>
@endsection
