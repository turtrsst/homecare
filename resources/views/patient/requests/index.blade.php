@extends('layouts.app')

@section('title', 'Riwayat Pengajuan')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-stone-900">Pengajuan Saya</h1>
            <p class="mt-1 text-sm text-stone-500">
                {{ $activeCount > 0 ? $activeCount.' pengajuan sedang berjalan' : 'Semua pengajuan Anda tercatat di sini' }}
            </p>
        </div>
        <x-button :href="route('akun.pesan.step', 'pasien')" icon="plus" class="hidden sm:inline-flex">Pesan Homecare</x-button>
    </div>

    @if ($requests->isEmpty())
        <x-card :padding="false">
            <x-empty-state icon="clipboard" title="Belum ada riwayat Homecare"
                           actionHref="{{ route('akun.pesan.step', 'pasien') }}" actionLabel="Pesan Homecare">
                Setelah Anda melakukan pemesanan, riwayat akan muncul di sini.
            </x-empty-state>
        </x-card>
    @else
        <div class="space-y-3">
            @foreach ($requests as $request)
                <x-request-card :request="$request" />
            @endforeach
        </div>

        <div class="mt-8">
            {{ $requests->links() }}
        </div>
    @endif

    <div class="sm:hidden mt-6">
        <x-button :href="route('akun.pesan.step', 'pasien')" icon="plus" full>Pesan Homecare</x-button>
    </div>
@endsection
