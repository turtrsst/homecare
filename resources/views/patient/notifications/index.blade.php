@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-stone-900">Notifikasi</h1>
            <p class="mt-1 text-sm text-stone-500">Pembaruan penting seputar pengajuan & kunjungan Anda.</p>
        </div>
        @if ($notifications->contains(fn ($n) => is_null($n->read_at)))
            <form method="POST" action="{{ route('akun.notifikasi.baca-semua') }}">
                @csrf
                <x-button type="submit" variant="soft" icon="check" size="sm">Tandai semua dibaca</x-button>
            </form>
        @endif
    </div>

    @if ($notifications->isEmpty())
        <x-card :padding="false">
            <x-empty-state icon="bell" title="Belum ada notifikasi"
                           actionHref="{{ route('akun.dashboard') }}" actionLabel="Ke Dashboard">
                Pemberitahuan tentang pengajuan & kunjungan Anda akan muncul di sini.
            </x-empty-state>
        </x-card>
    @else
        <div class="space-y-2.5">
            @foreach ($notifications as $notification)
                @php
                    $unread = is_null($notification->read_at);
                    $icon = match (data_get($notification->data, 'type')) {
                        'request_needs_information' => 'alert',
                        'appointment_scheduled' => 'calendar',
                        'task_assigned' => 'stethoscope',
                        default => 'bell',
                    };
                @endphp
                <div class="flex items-start gap-3.5 rounded-2xl ring-1 p-4 transition {{ $unread ? 'bg-brand-50/70 ring-brand-200' : 'bg-white ring-stone-200/70' }}">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $unread ? 'bg-brand-600 text-white' : 'bg-stone-100 text-stone-400' }}">
                        <x-icon :name="$icon" class="w-5 h-5" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="font-bold text-stone-900 text-sm leading-snug {{ $unread ? '' : 'font-semibold text-stone-600' }}">
                            {{ data_get($notification->data, 'title', 'Pemberitahuan') }}
                        </p>
                        @if ($body = data_get($notification->data, 'message'))
                            <p class="mt-0.5 text-sm text-stone-500 leading-relaxed">{{ $body }}</p>
                        @endif
                        <p class="mt-1.5 text-xs text-stone-400">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-1.5 shrink-0">
                        @if ($unread)
                            <span class="w-2.5 h-2.5 rounded-full bg-brand-500" aria-label="Belum dibaca"></span>
                        @endif
                        <form method="POST" action="{{ route('akun.notifikasi.baca', $notification->id) }}">
                            @csrf
                            <button type="submit" class="rounded-lg px-2.5 py-1.5 text-xs font-bold text-brand-700 hover:bg-brand-50 transition min-h-9">
                                {{ data_get($notification->data, 'url') ? 'Buka' : 'Tandai dibaca' }}
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">{{ $notifications->links() }}</div>
    @endif
@endsection
