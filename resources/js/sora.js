/**
 * Sora — komponen chat AI Assistant pemesanan homecare.
 *  - Satu kali ketik: setiap pesan dikirim ke server yang menggabungkannya ke draft.
 *  - Input suara (Web Speech API, bahasa Indonesia) bila browser mendukung.
 *  - Quick reply untuk menjawab pertanyaan lanjutan dengan satu ketukan.
 */
const uid = () => Math.random().toString(36).slice(2, 10);

const escapeHtml = (value) => value
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');

export default function sora(config) {
    return {
        endpoint: config.endpoint,
        resetEndpoint: config.resetEndpoint,
        isLoggedIn: config.isLoggedIn,
        draft: config.draft,
        status: 'collecting',
        input: '',
        loading: false,
        messages: [],
        listening: false,
        voiceSupported: false,
        recognition: null,
        lastRequest: null,
        lastAction: null,

        init() {
            this.messages.push({ id: uid(), role: 'bot', html: this.format(config.greeting), quick: config.quickReplies || [] });
            this.setupVoice();

            if (config.prefill) {
                this.input = config.prefill;
            }

            if (config.autoResume) {
                this.send('lanjutkan', { silent: true });
            }
        },

        setupVoice() {
            const Speech = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!Speech) return;

            this.voiceSupported = true;
            this.recognition = new Speech();
            this.recognition.lang = 'id-ID';
            this.recognition.interimResults = true;
            this.recognition.maxAlternatives = 1;

            this.recognition.onresult = (event) => {
                const transcript = Array.from(event.results).map((r) => r[0].transcript).join(' ').trim();
                this.input = transcript;
                if (event.results[event.results.length - 1].isFinal) {
                    this.listening = false;
                    this.send(transcript);
                }
            };
            this.recognition.onend = () => { this.listening = false; };
            this.recognition.onerror = () => { this.listening = false; };
        },

        toggleVoice() {
            if (!this.recognition) return;
            if (this.listening) {
                this.recognition.stop();
                this.listening = false;
                return;
            }
            this.input = '';
            this.listening = true;
            try {
                this.recognition.start();
            } catch (e) {
                this.listening = false;
            }
        },

        submit() {
            const text = this.input.trim();
            if (text === '' || this.loading) return;
            this.send(text);
        },

        useSuggestion(text) {
            this.send(text);
        },

        async send(text, options = {}) {
            if (this.loading) return;
            this.input = '';
            this.loading = true;
            this.lastRequest = text;

            if (!options.silent) {
                this.messages.push({ id: uid(), role: 'user', html: escapeHtml(text) });
            }
            this.scrollDown();
            const typingId = uid();
            this.messages.push({ id: typingId, role: 'bot', typing: true });

            try {
                const response = await fetch(this.endpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({ message: text }),
                });

                const data = await response.json().catch(() => ({}));

                if (response.status === 419) {
                    throw new Error('Sesi Anda telah berakhir. Muat ulang halaman lalu coba lagi.');
                }
                if (response.status === 422) {
                    throw new Error(Object.values(data.errors ?? {})[0]?.[0] ?? data.message ?? 'Pesan tidak valid.');
                }
                if (!response.ok) {
                    throw new Error('Gagal menghubungi asisten. Coba lagi sebentar.');
                }

                this.apply(data, typingId);
            } catch (error) {
                this.replaceTyping(typingId, {
                    role: 'bot',
                    html: this.format(error.message || 'Terjadi kendala. Coba lagi.'),
                    error: true,
                });
            } finally {
                this.loading = false;
                this.scrollDown();
            }
        },

        apply(data, typingId) {
            this.status = data.status;
            this.draft = data.draft;
            this.lastAction = data.action ?? null;

            this.replaceTyping(typingId, {
                role: 'bot',
                html: this.format(data.reply),
                quick: data.quick_replies ?? [],
                action: data.action ?? null,
                request: data.request ?? null,
                status: data.status,
            });

            if (data.status === 'submitted') {
                this.input = '';
                // Sisa draft sudah dikosongkan di server; tampilkan ringkasan kosong.
                this.draft = { ...this.draft, services: [], total: 0, total_label: null, date: null, date_label: null, window: null, window_label: null, patient: null, address: null, ready: false };
            }
        },

        replaceTyping(id, message) {
            const index = this.messages.findIndex((m) => m.id === id);
            const payload = { id: uid(), ...message };
            if (index >= 0) {
                this.messages.splice(index, 1, payload);
            } else {
                this.messages.push(payload);
            }
        },

        scrollDown() {
            this.$nextTick(() => {
                const el = this.$refs.thread;
                if (el) el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' });
            });
        },

        async resetConversation() {
            if (!confirm('Mulai ulang percakapan? Draft pesanan saat ini akan dihapus.')) return;

            try {
                await fetch(this.resetEndpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                });
            } catch (e) {
                // Jaringan bermasalah: tampilan tetap dikosongkan; draft server akan tertimpa pada pesan berikutnya.
            }

            this.status = 'collecting';
            this.draft = { ...this.draft, services: [], total: 0, total_label: null, date: null, date_label: null, window: null, window_label: null, patient: null, address: null, ready: false };
            this.messages = [{ id: uid(), role: 'bot', html: this.format('Percakapan dimulai ulang. Ceritakan kebutuhan Anda.'), quick: config.quickReplies || [] }];
        },

        /** Format ringan: escape HTML, lalu **tebal** dan baris baru. */
        format(text) {
            return escapeHtml(String(text ?? ''))
                .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                .replace(/\n/g, '<br>');
        },

        get readyLabel() {
            if (this.status === 'submitted') return 'Terkirim otomatis';
            if (this.draft.ready) return 'Siap dikirim';
            return 'Menunggu detail';
        },
    };
}
