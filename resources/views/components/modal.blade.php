@props(['name', 'title' => null, 'maxWidth' => 'max-w-lg'])

{{-- Modal Alpine: fokus terperangkap sederhana, tutup dengan Esc/klik luar --}}
<div x-data="{ open: false }"
     x-on:open-modal.window="if ($event.detail === '{{ $name }}') { open = true }"
     x-on:close-modal.window="if ($event.detail === '{{ $name }}') { open = false }"
     x-on:keydown.escape.window="open = false"
     x-show="open"
     style="display: none"
     class="relative z-50"
     role="dialog" aria-modal="true" @if($title) aria-label="{{ $title }}" @endif>

    <div x-show="open" x-transition.opacity class="fixed inset-0 bg-stone-900/50 backdrop-blur-sm"></div>

    <div class="fixed inset-0 flex items-end sm:items-center justify-center p-0 sm:p-4 overflow-y-auto">
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             class="relative w-full {{ $maxWidth }} bg-white rounded-t-3xl sm:rounded-3xl shadow-pop max-h-[92vh] overflow-y-auto">
            <div class="flex items-center justify-between px-5 sm:px-6 pt-5 pb-2 sticky top-0 bg-white rounded-t-3xl border-b border-stone-100">
                <h2 class="text-lg font-bold text-stone-900">{{ $title }}</h2>
                <button type="button" x-on:click="open = false"
                        class="rounded-full p-2 text-stone-400 hover:bg-stone-100 hover:text-stone-600"
                        aria-label="Tutup dialog">
                    <x-icon name="x" class="w-5 h-5" />
                </button>
            </div>
            <div class="px-5 sm:px-6 py-4">
                {{ $slot }}
            </div>
            @isset($footer)
                <div class="px-5 sm:px-6 py-4 border-t border-stone-100 bg-stone-50 rounded-b-3xl">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
