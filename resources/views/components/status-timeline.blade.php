@props(['steps' => []])

{{-- Timeline vertikal ramah-manusia: ikon ✓ / ● / ○ + teks (tidak hanya warna) --}}
<ol class="relative space-y-1" aria-label="Perkembangan pengajuan">
    @foreach ($steps as $step)
        @php
            $state = $step['state'] ?? 'upcoming';
        @endphp
        <li class="relative flex gap-4 pb-6 last:pb-0">
            @unless ($loop->last)
                <span class="absolute left-[15px] top-9 bottom-0 w-0.5 {{ in_array($state, ['done']) ? 'bg-brand-300' : 'bg-stone-200' }}" aria-hidden="true"></span>
            @endunless

            <span class="relative z-10 shrink-0 w-8 h-8 rounded-full flex items-center justify-center ring-4 ring-white
                @if ($state === 'done') bg-brand-600 text-white
                @elseif ($state === 'current') bg-blue-600 text-white animate-pulse
                @elseif ($state === 'failed') bg-rose-100 text-rose-600 ring-rose-50
                @else bg-stone-100 text-stone-400 @endif"
                aria-hidden="true">
                @if ($state === 'done')
                    <x-icon name="check" class="w-4 h-4" />
                @elseif ($state === 'current')
                    <span class="w-2.5 h-2.5 rounded-full bg-white"></span>
                @elseif ($state === 'failed')
                    <x-icon name="alert" class="w-4 h-4" />
                @else
                    <span class="w-2.5 h-2.5 rounded-full border-2 border-stone-300 bg-white"></span>
                @endif
            </span>

            <div class="pt-0.5 min-w-0">
                <p class="font-semibold text-sm sm:text-base
                    @if ($state === 'upcoming') text-stone-400 @elseif ($state === 'failed') text-rose-700 @else text-stone-800 @endif">
                    {{ $step['label'] }}
                    @if ($state === 'current')
                        <span class="ml-1 text-xs font-bold uppercase tracking-wide text-blue-600">(tahap saat ini)</span>
                    @endif
                </p>
                @if (! empty($step['description']) && $state !== 'upcoming')
                    <p class="text-sm text-stone-500 mt-0.5">{{ $step['description'] }}</p>
                @endif
                @if (! empty($step['at']))
                    <p class="text-xs text-stone-400 mt-1 flex items-center gap-1">
                        <x-icon name="clock" class="w-3.5 h-3.5" /> {{ $step['at'] }}
                    </p>
                @endif
            </div>
        </li>
    @endforeach
</ol>
