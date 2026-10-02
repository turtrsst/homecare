@extends('patient.booking._layout')

@section('step-content')
    <div class="space-y-4">
        <div>
            <h2 class="text-lg sm:text-xl font-extrabold text-stone-900">{{ $stepTitles['kebutuhan'] }}</h2>
            <p class="mt-1 text-sm text-stone-500">Ceritakan kondisi pasien dengan bahasa Anda sendiri — semakin jelas, semakin tepat persiapan petugas kami.</p>
        </div>

        <x-alert type="warning">
            <strong>Penting:</strong> layanan ini bukan untuk kondisi gawat darurat. Bila pasien mengalami sesak berat, nyeri dada, penurunan kesadaran, atau perdarahan hebat, segera hubungi
            <a href="tel:{{ config('homecare.emergency_number') }}" class="font-extrabold underline">{{ config('homecare.emergency_number') }}</a> atau bawa ke IGD terdekat.
        </x-alert>

        <form method="POST" action="{{ route('akun.pesan.save', 'kebutuhan') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <x-card class="space-y-5">
                <x-textarea name="complaint" label="Apa kebutuhan atau keluhan utamanya?" required rows="4"
                            :value="$booking['complaint'] ?? null"
                            placeholder="Contoh: Ibu saya pasca operasi usus buntu 5 hari lalu, luka operasi perlu diganti perbannya setiap hari. Beliau sulit duduk lama." />

                <x-textarea name="notes" label="Informasi tambahan (opsional)" rows="3"
                            :value="$booking['notes'] ?? null"
                            placeholder="Contoh: riwayat diabetes, alergi obat penicillin, sedang minum obat pengencer darah, kamar di lantai 1." />
            </x-card>

            {{-- Lampiran: foto kondisi, gambar galeri, atau berkas PDF --}}
            <x-card class="space-y-4">
                <div>
                    <h3 class="font-extrabold text-stone-900">
                        Lampiran pendukung
                        <span class="ml-1 align-middle text-xs font-semibold text-stone-400">opsional</span>
                    </h3>
                    <p class="mt-1 text-sm text-stone-500">
                        Foto kondisi pasien, surat rujukan, atau hasil pemeriksaan. JPG/PNG/WEBP/PDF, maksimal
                        {{ round((int) config('homecare.uploads.max_size_kb', 5120) / 1024) }} MB per berkas dan maksimal 5 berkas.
                    </p>
                </div>

                <x-select name="collection" label="Jenis dokumen"
                          :options="config('homecare.uploads.collections')"
                          value="{{ old('collection', 'other') }}" />

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="flex flex-col gap-2 rounded-2xl bg-white p-4 ring-1 ring-stone-300 cursor-pointer transition hover:ring-brand-400 focus-within:ring-brand-500"
                           x-data="{ label: 'Ketuk untuk memilih' }">
                        <input type="file" name="files[]" accept="image/*" capture="environment" class="sr-only"
                               x-on:change="label = [...$event.target.files].map(f => f.name).join(', ')">
                        <span class="w-10 h-10 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center">
                            <x-icon name="camera" class="w-5 h-5" />
                        </span>
                        <span class="text-sm font-bold text-stone-800">Ambil Foto</span>
                        <span class="text-xs text-stone-500 truncate" x-text="label"></span>
                    </label>

                    <label class="flex flex-col gap-2 rounded-2xl bg-white p-4 ring-1 ring-stone-300 cursor-pointer transition hover:ring-brand-400 focus-within:ring-brand-500"
                           x-data="{ label: 'Ketuk untuk memilih' }">
                        <input type="file" name="files[]" accept="image/*" multiple class="sr-only"
                               x-on:change="label = [...$event.target.files].map(f => f.name).join(', ')">
                        <span class="w-10 h-10 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center">
                            <x-icon name="image" class="w-5 h-5" />
                        </span>
                        <span class="text-sm font-bold text-stone-800">Dari Galeri</span>
                        <span class="text-xs text-stone-500 truncate" x-text="label"></span>
                    </label>

                    <label class="flex flex-col gap-2 rounded-2xl bg-white p-4 ring-1 ring-stone-300 cursor-pointer transition hover:ring-brand-400 focus-within:ring-brand-500"
                           x-data="{ label: 'Ketuk untuk memilih' }">
                        <input type="file" name="files[]" accept="application/pdf,.pdf" multiple class="sr-only"
                               x-on:change="label = [...$event.target.files].map(f => f.name).join(', ')">
                        <span class="w-10 h-10 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center">
                            <x-icon name="document" class="w-5 h-5" />
                        </span>
                        <span class="text-sm font-bold text-stone-800">Dokumen PDF</span>
                        <span class="text-xs text-stone-500 truncate" x-text="label"></span>
                    </label>
                </div>

                @php
                    $fileErrorKeys = $errors->keys()->filter(
                        fn (string $key) => $key === 'files' || str_starts_with($key, 'files.')
                    );
                @endphp
                @if ($fileErrorKeys->isNotEmpty())
                    <div class="rounded-xl bg-rose-50 ring-1 ring-rose-200 px-4 py-3 text-sm font-medium text-rose-700 space-y-1" role="alert">
                        @foreach ($fileErrorKeys as $key)
                            @foreach ($errors->get($key) as $message)
                                <p>{{ $message }}</p>
                            @endforeach
                        @endforeach
                    </div>
                @endif

                @if (!empty($booking['draft_attachments']))
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-stone-400 mb-2">
                            Terlampir ({{ count($booking['draft_attachments']) }}/5)
                        </p>
                        <ul class="space-y-2">
                            @foreach ($booking['draft_attachments'] as $index => $draft)
                                <li class="flex items-center gap-3 rounded-2xl bg-stone-50 ring-1 ring-stone-200/70 px-3.5 py-3">
                                    <span class="w-9 h-9 rounded-lg bg-white ring-1 ring-stone-200 flex items-center justify-center shrink-0 text-stone-500">
                                        <x-icon :name="str_starts_with((string) ($draft['mime_type'] ?? ''), 'image/') ? 'image' : 'document'" class="w-5 h-5" />
                                    </span>

                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-semibold text-stone-800">
                                            {{ $draft['original_name'] ?? 'Berkas' }}
                                        </span>
                                        <span class="block text-xs text-stone-500">
                                            {{ config('homecare.uploads.collections.'.$draft['collection'], 'Dokumen Lain') }}
                                            ·
                                            @if (($draft['size'] ?? 0) > 1048576)
                                                {{ round($draft['size'] / 1048576, 1) }} MB
                                            @else
                                                {{ max(1, (int) ceil(($draft['size'] ?? 0) / 1024)) }} KB
                                            @endif
                                        </span>
                                    </span>

                                    <button type="submit"
                                            formaction="{{ route('akun.pesan.lampiran.hapus', $index) }}"
                                            class="shrink-0 inline-flex items-center gap-1.5 rounded-lg px-2.5 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 transition">
                                        <x-icon name="trash" class="w-4 h-4" />
                                        Hapus
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </x-card>

            <div class="pt-1 flex flex-col-reverse sm:flex-row sm:justify-between gap-3">
                <x-button :href="route('akun.pesan.step', 'layanan')" variant="ghost" icon="chevron-left" class="justify-center">
                    Kembali
                </x-button>
                <x-button type="submit" size="lg" icon="chevron-right" class="w-full sm:w-auto">
                    Lanjut ke Lokasi
                </x-button>
            </div>
        </form>
    </div>
@endsection
