@extends('patient.booking._layout')

@section('step-content')
    <div class="space-y-4">
        <div>
            <h2 class="text-lg sm:text-xl font-extrabold text-stone-900">{{ $stepTitles['layanan'] }}</h2>
            <p class="mt-1 text-sm text-stone-500">Pilih satu atau beberapa layanan (maksimal 5). Bingung? Pilih yang paling mendekati — koordinator kami akan menyesuaikan setelah skrining.</p>
        </div>

        <form method="POST" action="{{ route('akun.pesan.save', 'layanan') }}" class="space-y-4">
            @csrf

            @if ($errors->has('service_ids'))
                <x-alert type="error">{{ $errors->first('service_ids') }}</x-alert>
            @endif

            @php $selected = old('service_ids', $booking['service_ids'] ?? []); @endphp

            @foreach ($services as $category => $items)
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-stone-400 mb-2.5">{{ $category }}</h3>
                    <div class="grid sm:grid-cols-2 gap-3">
                        @foreach ($items as $service)
                            <label class="block cursor-pointer">
                                <input type="checkbox" name="service_ids[]" value="{{ $service->id }}"
                                       @checked(in_array($service->id, (array) $selected))
                                       class="peer sr-only" />
                                <div class="h-full rounded-2xl ring-1 bg-white p-4 transition peer-checked:ring-2 peer-checked:ring-brand-600 peer-checked:bg-brand-50/50 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand-600 hover:ring-brand-300">
                                    <div class="flex items-start gap-3">
                                        <span class="shrink-0">
                                            @if ($service->thumbnailUrl())
                                                <img src="{{ $service->thumbnailUrl() }}" alt="" aria-hidden="true" loading="lazy"
                                                     class="w-10 h-10 rounded-xl object-cover" />
                                            @else
                                                <span class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                                                    <x-icon name="{{ $service->displayIcon() }}" class="w-5 h-5" />
                                                </span>
                                            @endif
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="choice-name block font-bold text-stone-900 text-sm">{{ $service->name }}</span>
                                            @if ($service->short_description)
                                                <span class="block mt-0.5 text-xs text-stone-500 line-clamp-2">{{ $service->short_description }}</span>
                                            @endif
                                            <span class="mt-2 flex items-center justify-between">
                                                <span class="text-sm font-extrabold text-brand-700">{{ $service->formattedPrice() }}</span>
                                                <span class="text-[11px] text-stone-400 flex items-center gap-1">
                                                    <x-icon name="clock" class="w-3.5 h-3.5" /> ±{{ $service->duration_minutes }} mnt
                                                </span>
                                            </span>
                                        </span>
                                        <span class="choice-dot w-6 h-6 rounded-lg border-2 border-stone-300 flex items-center justify-center shrink-0 transition
                                            {{ in_array($service->id, (array) $selected) ? 'border-brand-600 bg-brand-600' : '' }}">
                                            <x-icon name="check" class="w-3.5 h-3.5 text-white" />
                                        </span>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="pt-2 flex flex-col-reverse sm:flex-row sm:justify-between gap-3">
                <x-button :href="route('akun.pesan.step', 'pasien')" variant="ghost" icon="chevron-left" class="justify-center">
                    Kembali
                </x-button>
                <x-button type="submit" size="lg" icon="chevron-right" class="w-full sm:w-auto">
                    Lanjut
                </x-button>
            </div>
        </form>
    </div>
@endsection
