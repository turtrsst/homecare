{{-- Toast flash messages (session) — Alpine, auto-hide, aksesibel --}}
<div x-data="{ show: true }"
     x-show="show"
     x-init="setTimeout(() => show = false, 6000)"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     class="fixed bottom-20 sm:bottom-6 left-1/2 -translate-x-1/2 z-50 w-[calc(100%-2rem)] max-w-md"
     role="status" aria-live="polite">
    @foreach (['success' => 'success', 'error' => 'error', 'warning' => 'warning', 'info' => 'info'] as $key => $type)
        @if (session($key))
            <x-alert :type="$type" class="shadow-pop">
                {{ session($key) }}
            </x-alert>
        @endif
    @endforeach
</div>
