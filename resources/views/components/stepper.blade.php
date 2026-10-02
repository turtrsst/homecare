@props(['steps' => [], 'stepTitles' => [], 'current' => 1])

{{-- Progress wizard: angka + label, selalu terlihat posisi pengguna --}}
<nav aria-label="Progres pemesanan" class="w-full">
    <ol class="flex items-center gap-1 overflow-x-auto pb-1 -mx-1 px-1 scrollbar-none">
        @foreach ($steps as $index => $stepKey)
            @php
                $number = $index + 1;
                $isCurrent = $number === $current;
                $isDone = $number < $current;
            @endphp
            <li class="flex items-center gap-1 shrink-0">
                <a href="{{ $isDone ? route('akun.pesan.step', $stepKey) : '#' }}"
                   @if(! $isDone) aria-current="{{ $isCurrent ? 'step' : 'false' }}" tabindex="{{ $isDone ? 0 : -1 }}" @endif
                   class="flex items-center gap-2 rounded-full px-3 py-2 {{ $isCurrent ? 'bg-brand-600 text-white' : ($isDone ? 'bg-brand-50 text-brand-700 hover:bg-brand-100' : 'bg-stone-100 text-stone-400') }}">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold {{ $isCurrent ? 'bg-white/20' : ($isDone ? 'bg-brand-600 text-white' : 'bg-stone-200 text-stone-500') }}">
                        @if ($isDone)
                            <x-icon name="check" class="w-3.5 h-3.5" />
                        @else
                            {{ $number }}
                        @endif
                    </span>
                    <span class="text-sm font-semibold whitespace-nowrap">{{ $stepTitles[$stepKey] ?? ucfirst($stepKey) }}</span>
                </a>
                @if (! $loop->last)
                    <span class="w-4 h-px bg-stone-200 shrink-0" aria-hidden="true"></span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
