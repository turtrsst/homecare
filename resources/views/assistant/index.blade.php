@extends('layouts.public')

@section('title', 'Asisten Sora')
@section('description', 'Sora, asisten AI Soeradji Care: ketik kebutuhan homecare Anda sekali, pesanan dibuat otomatis.')

@section('content')
    @php
        $configJs = [
            'endpoint' => route('ai-assistant.message'),
            'resetEndpoint' => route('ai-assistant.reset'),
            'isLoggedIn' => $isLoggedIn,
            'draft' => $draft,
            'greeting' => config('homecare.assistant.greeting'),
            'quickReplies' => $quickReplies,
            'autoResume' => $autoResume,
            'prefill' => $prefill,
        ];
    @endphp

    <section class="mesh-bg py-6 sm:py-10">
        <div class="container-app" x-data="sora(@js($configJs))">
            <div class="grid gap-6 lg:grid-cols-12">
                {{-- Percakapan --}}
                <div class="surface flex h-[calc(100dvh-12.5rem)] min-h-[500px] flex-col overflow-hidden lg:col-span-8 lg:h-[700px]">
                    <header class="flex items-center justify-between gap-3 border-b border-clinic-100 px-4 py-3.5 sm:px-6">
                        <div class="flex items-center gap-3">
                            <span class="relative flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-soeradji-600 to-medical-500 text-white shadow-lg shadow-soeradji-600/25">
                                <x-icon name="sparkles" class="h-5 w-5" />
                                <span class="absolute -right-0.5 -top-0.5 h-3 w-3 rounded-full bg-emerald-400 ring-2 ring-white"></span>
                            </span>
                            <div>
                                <p class="font-extrabold leading-tight text-clinic-900">{{ config('homecare.assistant.name') }} <span class="font-semibold text-clinic-400">· Asisten pemesanan</span></p>
                                <p class="text-xs text-clinic-500">Ketik sekali — pesanan dibuat otomatis</p>
                            </div>
                        </div>
                        <button type="button" @click="resetConversation()" class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-xs font-bold text-clinic-600 ring-1 ring-clinic-200 transition hover:bg-clinic-50">
                            <x-icon name="refresh" class="h-3.5 w-3.5" /> Mulai ulang
                        </button>
                    </header>

                    <div x-ref="thread" class="scroll-thin flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6" aria-live="polite">
                        <template x-for="m in messages" :key="m.id">
                            <div :class="m.role === 'user' ? 'flex justify-end' : 'flex items-start gap-3'">
                                <span x-show="m.role === 'bot'" class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-soeradji-50 text-soeradji-700">
                                    <x-icon name="sparkles" class="h-4 w-4" />
                                </span>

                                <div class="max-w-[85%] space-y-3">
                                    <div x-show="m.typing" class="inline-flex items-center gap-1.5 rounded-2xl rounded-tl-md bg-clinic-100 px-4 py-3" x-cloak>
                                        <span class="typing-dot h-2 w-2 rounded-full bg-clinic-400"></span>
                                        <span class="typing-dot h-2 w-2 rounded-full bg-clinic-400" style="animation-delay:.15s"></span>
                                        <span class="typing-dot h-2 w-2 rounded-full bg-clinic-400" style="animation-delay:.3s"></span>
                                    </div>

                                    <div x-show="!m.typing" x-cloak
                                         :class="m.role === 'user' ? 'rounded-2xl rounded-tr-md bg-clinic-900 text-white' : (m.error ? 'rounded-2xl rounded-tl-md bg-rose-50 text-rose-800 ring-1 ring-rose-100' : 'rounded-2xl rounded-tl-md bg-white text-clinic-800 ring-1 ring-clinic-100 shadow-card')"
                                         class="px-4 py-3 text-sm leading-relaxed sm:text-[15px]">
                                        <p x-html="m.html"></p>
                                    </div>

                                    <div x-show="m.action" class="flex flex-wrap gap-2" x-cloak>
                                        <a :href="m.action?.url" class="btn-primary !py-2.5 !text-xs" x-text="m.action?.label"></a>
                                    </div>

                                    <a x-show="m.request" :href="m.request?.url" x-cloak
                                       class="flex items-center gap-3 rounded-2xl border border-medical-100 bg-medical-50 p-4 text-sm transition hover:bg-medical-100/70">
                                        <x-icon name="badge-check" class="h-6 w-6 shrink-0 text-medical-600" />
                                        <span class="flex-1">
                                            <span class="block text-xs font-semibold text-medical-700">Kode pengajuan</span>
                                            <span class="block font-mono font-extrabold text-clinic-900" x-text="m.request?.code"></span>
                                        </span>
                                        <x-icon name="arrow-right" class="h-4 w-4 text-medical-700" />
                                    </a>

                                    <div x-show="m.quick && m.quick.length" class="flex flex-wrap gap-2" x-cloak>
                                        <template x-for="q in (m.quick || [])" :key="q.label + q.text">
                                            <button type="button" @click="useSuggestion(q.text)" :disabled="loading"
                                                    class="rounded-full bg-white px-3.5 py-2 text-xs font-bold text-soeradji-700 ring-1 ring-soeradji-200 transition hover:bg-soeradji-50 disabled:opacity-50"
                                                    x-text="q.label"></button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="border-t border-clinic-100 bg-white/80 p-3 backdrop-blur sm:p-4">
                        <div class="scroll-thin -mx-1 mb-3 flex gap-2 overflow-x-auto px-1 pb-1" x-show="messages.length <= 1" x-cloak>
                            @foreach ($suggestions as $suggestion)
                                <button type="button" @click="useSuggestion(@js($suggestion))"
                                        class="shrink-0 rounded-full bg-clinic-50 px-3.5 py-2 text-xs font-semibold text-clinic-700 ring-1 ring-clinic-200 transition hover:bg-white">{{ \Illuminate\Support\Str::limit($suggestion, 48) }}</button>
                            @endforeach
                        </div>

                        <form @submit.prevent="submit()" class="flex items-end gap-2">
                            <label class="sr-only" for="sora-input">Tulis kebutuhan Anda</label>
                            <div class="relative flex-1">
                                <textarea id="sora-input" x-model="input" rows="1" maxlength="1000"
                                          @keydown.enter="if (! $event.shiftKey) { $event.preventDefault(); submit(); }"
                                          placeholder="Contoh: ganti kateter untuk ayah, besok pagi di rumah"
                                          class="max-h-32 w-full resize-none rounded-2xl border border-clinic-200 bg-white px-4 py-3.5 pr-4 text-sm shadow-card placeholder:text-clinic-400 focus:border-soeradji-400 focus:outline-none focus:ring-4 focus:ring-soeradji-100"></textarea>
                            </div>
                            <button type="button" x-show="voiceSupported" @click="toggleVoice()"
                                    :aria-pressed="listening" aria-label="Bicara"
                                    :class="listening ? 'bg-rose-500 text-white animate-pulse' : 'bg-clinic-100 text-clinic-700 hover:bg-clinic-200'"
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl transition">
                                <x-icon name="mic" class="h-5 w-5" />
                            </button>
                            <button type="submit" :disabled="loading || input.trim() === ''" aria-label="Kirim"
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-soeradji-600 to-soeradji-500 text-white shadow-lg shadow-soeradji-600/25 transition hover:-translate-y-0.5 disabled:translate-y-0 disabled:opacity-40 disabled:shadow-none">
                                <x-icon name="send" class="h-5 w-5" />
                            </button>
                        </form>
                        <p class="mt-2 px-1 text-[11px] text-clinic-400" x-show="listening" x-cloak>Mendengarkan… ucapkan kebutuhan Anda dalam bahasa Indonesia.</p>
                    </div>
                </div>

                {{-- Ringkasan: mobile di <details>, desktop di panel samping --}}
                <details class="surface group p-5 lg:hidden">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 font-extrabold text-clinic-900">
                        <span class="flex items-center gap-2"><x-icon name="list" class="h-5 w-5 text-soeradji-600" /> Ringkasan pesanan</span>
                        <span class="flex items-center gap-2 text-xs font-bold text-soeradji-700">
                            <span x-text="draft.services.length + ' layanan'"></span>
                            <x-icon name="chevron-down" class="h-4 w-4 transition group-open:rotate-180" />
                        </span>
                    </summary>
                    <div class="mt-5 border-t border-clinic-100 pt-5">
                        @include('assistant._summary')
                    </div>
                </details>

                <aside class="hidden lg:col-span-4 lg:block">
                    <div class="surface sticky top-28 p-6">
                        @include('assistant._summary')
                    </div>
                </aside>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                @foreach ([
                    ['icon' => 'shield-check', 'title' => 'Data terlindungi', 'text' => 'Percakapan hanya dipakai untuk menyusun pesanan Anda.'],
                    ['icon' => 'clock', 'title' => 'Hemat waktu', 'text' => 'Tanpa mengisi banyak formulir — satu ketikan sudah cukup.'],
                    ['icon' => 'user', 'title' => 'Untuk keluarga', 'text' => 'Pesan untuk diri sendiri, orang tua, atau anak dalam satu akun.'],
                ] as $item)
                    <div class="surface flex items-start gap-3 p-5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-soeradji-50 text-soeradji-700"><x-icon :name="$item['icon']" class="h-5 w-5" /></span>
                        <div>
                            <p class="font-bold text-clinic-900">{{ $item['title'] }}</p>
                            <p class="mt-1 text-sm text-clinic-500">{{ $item['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
