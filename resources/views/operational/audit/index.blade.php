@extends('layouts.admin')

@section('title', 'Audit Trail')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-stone-900">Audit Trail</h1>
        <p class="mt-1 text-sm text-stone-500">Catatan permanen setiap perubahan status & tindakan penting pada sistem.</p>
    </div>

    <form method="GET" class="mb-6 flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <x-icon name="search" class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 pointer-events-none" />
            <input type="search" name="q" value="{{ $search }}" placeholder="Cari event (mis. HC-202609-0001, APPROVED_REQUEST)..."
                   class="w-full rounded-xl ring-1 ring-stone-300 bg-white pl-11 pr-4 py-2.5 text-sm text-stone-800 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-brand-500 min-h-11" />
        </div>
        <x-button type="submit" variant="secondary" icon="search" class="sm:w-auto">Cari</x-button>
    </form>

    @if ($logs->isEmpty())
        <x-card :padding="false">
            <x-empty-state icon="shield" title="Tidak ada catatan audit">
                {{ $search ? 'Coba kata kunci lain.' : 'Aktivitas sistem akan tercatat otomatis di sini.' }}
            </x-empty-state>
        </x-card>
    @else
        <x-card :padding="false" class="overflow-hidden">
            <ol class="divide-y divide-stone-100">
                @foreach ($logs as $log)
                    <li class="p-4 sm:p-5 hover:bg-stone-50/60 transition">
                        <div class="flex flex-wrap items-start gap-3">
                            <span class="mt-0.5 w-9 h-9 rounded-xl bg-stone-100 text-stone-500 flex items-center justify-center shrink-0">
                                <x-icon name="{{ str_contains(strtolower($log->event), 'cancel') || str_contains(strtolower($log->event), 'reject') ? 'x-circle' : (str_contains(strtolower($log->event), 'complet') || str_contains(strtolower($log->event), 'approv') ? 'check-circle' : 'shield') }}" class="w-4.5 h-4.5" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-stone-800">{{ $log->description }}</p>
                                <p class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-stone-400">
                                    <span class="font-mono font-bold text-stone-500 bg-stone-100 rounded-md px-1.5 py-0.5">{{ $log->event }}</span>
                                    <span>oleh <span class="font-semibold text-stone-600">{{ $log->user?->name ?? 'Sistem' }}</span></span>
                                    <span>·</span>
                                    <span>{{ $log->created_at->translatedFormat('j M Y, H.i') }} WIB</span>
                                    @if ($log->auditable instanceof \App\Models\HomecareRequest)
                                        <span>·</span>
                                        <a href="{{ route('operasional.pengajuan.show', $log->auditable->code) }}" class="font-mono font-bold text-brand-700 hover:underline">{{ $log->auditable->code }}</a>
                                    @endif
                                </p>
                                @if (! empty($log->properties) && count($log->properties) <= 6)
                                    <details class="mt-2 group">
                                        <summary class="cursor-pointer text-xs font-bold text-stone-400 hover:text-stone-600 transition select-none">
                                    Detail data <span class="inline-block transition-transform group-open:rotate-90">›</span>
                                        </summary>
                                        <pre class="mt-2 rounded-xl bg-stone-900 text-stone-200 text-xs p-3.5 overflow-x-auto leading-relaxed">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </details>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ol>
        </x-card>

        <div class="mt-6">{{ $logs->links() }}</div>
    @endif
@endsection
