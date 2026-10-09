@extends('layouts.app')

@section('title', 'Detail Pesanan')

@section('content')
    @php
        $statusColors = [
            'pending' => ['bg' => 'amber-50', 'border' => 'amber-200', 'text' => 'amber-700', 'icon' => 'clock'],
            'approved' => ['bg' => 'blue-50', 'border' => 'blue-200', 'text' => 'blue-700', 'icon' => 'check-circle'],
            'ongoing' => ['bg' => 'indigo-50', 'border' => 'indigo-200', 'text' => 'indigo-700', 'icon' => 'activity'],
            'completed' => ['bg' => 'green-50', 'border' => 'green-200', 'text' => 'green-700', 'icon' => 'check-circle2'],
            'cancelled' => ['bg' => 'rose-50', 'border' => 'rose-200', 'text' => 'rose-700', 'icon' => 'x-circle'],
        ];
        $colors = $statusColors[$request->status->value] ?? $statusColors['pending'];
    @endphp

    <div class="mb-6">
        <a href="{{ route('akun.pengajuan.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-soeradji-700 hover:text-soeradji-800">
            <x-icon name="chevron-left" class="h-4 w-4" />
            Kembali ke pesanan
        </a>
    </div>

    <div class="mb-8 grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <div class="space-y-6">
            <div class="rounded-[30px] border border-stone-200 bg-white p-5 shadow-card sm:p-6">
                <div class="flex items-center justify-between gap-4 border-b border-stone-200 pb-5">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-clinic-400">Nomor pesanan</p>
                        <h1 class="mt-2 text-2xl font-black text-clinic-900">{{ $request->code }}</h1>
                    </div>
                    <span class="flex items-center gap-2 rounded-full border px-4 py-2 font-bold" :class="'border-{{ $colors['border'] }} bg-{{ $colors['bg'] }} text-{{ $colors['text'] }}'">
                        <x-icon :name="$colors['icon']" class="h-4 w-4" />
                        {{ $request->status->label() }}
                    </span>
                </div>

                <div class="mt-5 space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-soeradji-50 text-soeradji-600">
                            <x-icon name="calendar" class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-sm text-clinic-500">Tanggal kunjungan</p>
                            <p class="mt-1 font-black text-clinic-900">{{ $request->visit_date?->translatedFormat('l, d MMMM Y') ?? 'Belum dijadwalkan' }}</p>
                            @if ($request->visit_time)
                                <p class="mt-1 text-sm text-clinic-600">Jam: {{ $request->visit_time }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-medical-50 text-medical-600">
                            <x-icon name="map-pin" class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-sm text-clinic-500">Lokasi layanan</p>
                            <p class="mt-1 font-black text-clinic-900">{{ $request->address ?? 'Tidak ada alamat' }}</p>
                        </div>
                    </div>

                    @if ($request->appointment && $request->appointment->staff)
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                                <x-icon name="stethoscope" class="h-6 w-6" />
                            </div>
                            <div>
                                <p class="text-sm text-clinic-500">Petugas kesehatan</p>
                                <p class="mt-1 font-black text-clinic-900">{{ $request->appointment->staff->name }}</p>
                                <p class="mt-1 text-xs text-clinic-600">{{ $request->appointment->staff->profession?->label() ?? 'Tim kesehatan' }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-[30px] border border-stone-200 bg-white p-5 shadow-card sm:p-6">
                <h2 class="text-xl font-black text-clinic-900">Layanan yang dipesan</h2>
                <div class="mt-5 space-y-3">
                    @foreach ($request->items as $item)
                        <div class="flex items-center justify-between gap-3 rounded-2xl border border-stone-200 bg-clinic-50 px-4 py-3">
                            <div>
                                <p class="font-bold text-clinic-900">{{ $item->service_name }}</p>
                                <p class="mt-1 text-xs text-clinic-500">Jumlah: {{ $item->quantity }} × Rp {{ number_format($item->unit_price, 0, ',', '.') }}</p>
                            </div>
                            <p class="font-black text-soeradji-700">Rp {{ number_format($item->total_price, 0, ',', '.') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($request->notes)
                <div class="rounded-[30px] border border-stone-200 bg-white p-5 shadow-card sm:p-6">
                    <h2 class="text-xl font-black text-clinic-900">Catatan khusus</h2>
                    <p class="mt-4 leading-relaxed text-clinic-700">{{ $request->notes }}</p>
                </div>
            @endif

            @if ($request->status->value === 'ongoing')
                <div class="rounded-[30px] bg-gradient-to-r from-indigo-50 to-blue-50 p-5 ring-1 ring-indigo-100 sm:p-6">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-white">
                            <x-icon name="activity" class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="font-black text-indigo-900">Layanan sedang berlangsung</p>
                            <p class="mt-1 text-sm text-indigo-700">Tim kesehatan sudah sampai dan melakukan pelayanan sesuai jadwal. Anda dapat menghubungi kami jika ada kebutuhan tambahan.</p>
                        </div>
                    </div>
                </div>
            @endif

            @if ($request->status->value === 'completed')
                <div class="rounded-[30px] bg-gradient-to-r from-green-50 to-emerald-50 p-5 ring-1 ring-green-100 sm:p-6">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-600 text-white">
                            <x-icon name="check-circle2" class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="font-black text-green-900">Layanan telah selesai</p>
                            <p class="mt-1 text-sm text-green-700">Terima kasih telah menggunakan Soeradji Care. Bagaimana pengalaman Anda? Berikan review untuk membantu kami terus meningkatkan kualitas layanan.</p>
                            <a href="{{ route('akun.pengajuan.review', $request) }}" class="mt-3 inline-flex items-center gap-2 rounded-xl bg-green-600 px-4 py-2 text-sm font-bold text-white hover:bg-green-700">
                                <x-icon name="star" class="h-4 w-4" />
                                Beri review
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="rounded-[30px] border border-stone-200 bg-white p-5 shadow-card sm:p-6">
                <h2 class="text-xl font-black text-clinic-900">Ringkasan pesanan</h2>
                <div class="mt-5 space-y-3 border-b border-stone-200 pb-5">
                    @php
                        $subtotal = $request->items->sum('total_price') ?? 0;
                        $tax = $subtotal * 0.1;
                        $total = $subtotal + $tax;
                    @endphp
                    <div class="flex items-center justify-between text-sm text-clinic-600">
                        <span>Subtotal</span>
                        <span class="font-semibold text-clinic-900">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm text-clinic-600">
                        <span>Pajak (10%)</span>
                        <span class="font-semibold text-clinic-900">Rp {{ number_format($tax, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm text-clinic-600">
                        <span>Biaya admin</span>
                        <span class="font-semibold text-clinic-900">Rp 0</span>
                    </div>
                </div>
                <div class="mt-5 flex items-center justify-between">
                    <span class="text-lg font-black text-clinic-900">Total pembayaran</span>
                    <span class="text-2xl font-black text-soeradji-700">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="rounded-[30px] border border-stone-200 bg-white p-5 shadow-card sm:p-6">
                <h2 class="text-xl font-black text-clinic-900">Status pembayaran</h2>
                @php
                    $paymentStatus = $request->payment_status ?? 'unpaid';
                    $paymentIcon = match($paymentStatus) {
                        'paid' => 'check-circle2',
                        'pending' => 'clock',
                        'failed' => 'x-circle',
                        default => 'alert-circle'
                    };
                    $paymentColor = match($paymentStatus) {
                        'paid' => 'green',
                        'pending' => 'amber',
                        'failed' => 'rose',
                        default => 'stone'
                    };
                @endphp
                <div class="mt-5 rounded-2xl border-2 px-4 py-4" :class="'border-{{ $paymentColor }}-200 bg-{{ $paymentColor }}-50'">
                    <div class="flex items-center gap-3">
                        <x-icon :name="$paymentIcon" :class="'h-6 w-6 text-' . $paymentColor . '-600'" />
                        <div>
                            <p class="font-black text-{{ $paymentColor }}-900">{{ ucfirst($paymentStatus) }}</p>
                            <p class="text-sm text-{{ $paymentColor }}-700">
                                @if ($paymentStatus === 'paid')
                                    Pembayaran sudah diterima
                                @elseif ($paymentStatus === 'pending')
                                    Menunggu verifikasi pembayaran
                                @else
                                    Pembayaran gagal. Silakan hubungi kami.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                @if ($paymentStatus !== 'paid')
                    <a href="{{ route('akun.pembayaran.show', $request) }}" class="mt-4 block w-full rounded-2xl bg-soeradji-600 px-4 py-3 text-center text-sm font-bold text-white hover:bg-soeradji-700">
                        Bayar sekarang
                    </a>
                @endif
            </div>

            <div class="rounded-[30px] border border-stone-200 bg-white p-5 shadow-card sm:p-6">
                <h2 class="text-lg font-black text-clinic-900">Butuh bantuan?</h2>
                <p class="mt-2 text-sm text-clinic-600">Hubungi tim kami untuk pertanyaan atau perubahan pesanan Anda.</p>
                <div class="mt-4 space-y-2">
                    <a href="tel:{{ config('homecare.contact.phone') }}" class="block rounded-xl border border-stone-200 bg-stone-50 px-3.5 py-2.5 text-center text-sm font-semibold text-stone-700 hover:bg-stone-100">
                        <x-icon name="phone" class="mr-2 inline h-4 w-4" />
                        {{ config('homecare.contact.phone') }}
                    </a>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', config('homecare.contact.whatsapp')) }}" target="_blank" class="block rounded-xl border border-green-200 bg-green-50 px-3.5 py-2.5 text-center text-sm font-semibold text-green-700 hover:bg-green-100">
                        <x-icon name="message-square" class="mr-2 inline h-4 w-4" />
                        WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
