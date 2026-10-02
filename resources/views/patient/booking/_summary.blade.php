{{-- Ringkasan pengajuan — dipakai langkah review & konfirmasi --}}
@php
    $patient = $summary['patient'] ?? null;
    $address = $summary['address'] ?? null;
    $services = $summary['services'] ?? collect();
    $total = $summary['total'] ?? 0;
    $window = $booking['preferred_time_window'] ?? null;
@endphp

<div class="space-y-4">
    {{-- Pasien --}}
    <x-card>
        <div class="flex items-center justify-between gap-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-stone-400 flex items-center gap-1.5">
                <x-icon name="user" class="w-4 h-4" /> Pasien
            </h3>
            <a href="{{ route('akun.pesan.step', 'pasien') }}" class="text-xs font-bold text-brand-700 hover:text-brand-800">Ubah</a>
        </div>
        @if ($patient)
            <p class="mt-2 font-bold text-stone-900">{{ $patient->name }}</p>
            <p class="text-sm text-stone-500">
                {{ $patient->relationship ? ucfirst(str_replace('_', ' ', $patient->relationship)) : 'Pasien' }}
                @if ($patient->age()) · {{ $patient->age() }} tahun @endif
                @if ($patient->gender) · {{ $patient->gender->label() }} @endif
            </p>
        @else
            <p class="mt-2 text-sm text-rose-600 font-semibold">Pasien belum dipilih.</p>
        @endif
    </x-card>

    {{-- Layanan --}}
    <x-card>
        <div class="flex items-center justify-between gap-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-stone-400 flex items-center gap-1.5">
                <x-icon name="heart" class="w-4 h-4" /> Layanan
            </h3>
            <a href="{{ route('akun.pesan.step', 'layanan') }}" class="text-xs font-bold text-brand-700 hover:text-brand-800">Ubah</a>
        </div>
        @if ($services->isNotEmpty())
            <ul class="mt-3 divide-y divide-stone-100">
                @foreach ($services as $service)
                    <li class="py-2.5 flex items-center justify-between gap-3 text-sm">
                        <span class="font-semibold text-stone-800">{{ $service->name }}</span>
                        <span class="font-bold text-stone-600 shrink-0">{{ $service->formattedPrice() }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="mt-3 pt-3 border-t-2 border-dashed border-stone-200 flex items-center justify-between">
                <span class="font-extrabold text-stone-900">Perkiraan total</span>
                <span class="font-extrabold text-brand-700 text-lg">Rp {{ number_format($total, 0, ',', '.') }}</span>
            </div>
            <p class="mt-1.5 text-xs text-stone-400">Biaya final dikonfirmasi koordinator setelah skrining.</p>
        @else
            <p class="mt-2 text-sm text-rose-600 font-semibold">Layanan belum dipilih.</p>
        @endif
    </x-card>

    {{-- Kebutuhan --}}
    <x-card>
        <div class="flex items-center justify-between gap-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-stone-400 flex items-center gap-1.5">
                <x-icon name="clipboard" class="w-4 h-4" /> Kebutuhan
            </h3>
            <a href="{{ route('akun.pesan.step', 'kebutuhan') }}" class="text-xs font-bold text-brand-700 hover:text-brand-800">Ubah</a>
        </div>
        <p class="mt-2 text-sm text-stone-600 leading-relaxed">{{ $booking['complaint'] ?? '—' }}</p>
        @if (! empty($booking['notes']))
            <p class="mt-2 text-xs text-stone-400 bg-stone-50 rounded-xl px-3 py-2">Catatan: {{ $booking['notes'] }}</p>
        @endif

        @if (! empty($booking['draft_attachments']))
            <div class="mt-3">
                <p class="text-xs font-bold text-stone-400 mb-1.5">
                    Lampiran ({{ count($booking['draft_attachments']) }}/5)
                </p>
                <ul class="space-y-1.5">
                    @foreach ($booking['draft_attachments'] as $draft)
                        <li class="flex items-center gap-2 text-xs text-stone-600 bg-stone-50 rounded-lg px-2.5 py-2">
                            <x-icon :name="str_starts_with((string) ($draft['mime_type'] ?? ''), 'image/') ? 'image' : 'document'"
                                    class="w-4 h-4 text-stone-400 shrink-0" />
                            <span class="truncate">{{ $draft['original_name'] ?? 'Berkas' }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </x-card>

    {{-- Lokasi --}}
    <x-card>
        <div class="flex items-center justify-between gap-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-stone-400 flex items-center gap-1.5">
                <x-icon name="map-pin" class="w-4 h-4" /> Lokasi kunjungan
            </h3>
            <a href="{{ route('akun.pesan.step', 'lokasi') }}" class="text-xs font-bold text-brand-700 hover:text-brand-800">Ubah</a>
        </div>
        @if ($address)
            <p class="mt-2 text-sm text-stone-600">{{ $address->oneLine() }}</p>
            <p class="text-xs text-stone-400 mt-1">a.n. {{ $address->recipient_name }} · {{ $address->phone }}</p>
        @else
            <p class="mt-2 text-sm text-rose-600 font-semibold">Alamat belum dipilih.</p>
        @endif
    </x-card>

    {{-- Jadwal --}}
    <x-card>
        <div class="flex items-center justify-between gap-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-stone-400 flex items-center gap-1.5">
                <x-icon name="calendar" class="w-4 h-4" /> Jadwal permintaan
            </h3>
            <a href="{{ route('akun.pesan.step', 'jadwal') }}" class="text-xs font-bold text-brand-700 hover:text-brand-800">Ubah</a>
        </div>
        <p class="mt-2 font-bold text-stone-900">
            {{ isset($booking['preferred_date']) ? \Illuminate\Support\Carbon::parse($booking['preferred_date'])->translatedFormat('l, j F Y') : '—' }}
        </p>
        @if ($window)
            <p class="text-sm text-stone-500 mt-0.5">{{ config("homecare.time_windows.$window.label") }}</p>
        @endif
    </x-card>
</div>
