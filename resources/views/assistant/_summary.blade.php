{{-- Ringkasan draft pesanan. Dipakai di panel desktop dan di <details> mobile. Membaca state `sora` dari x-data induk. --}}
<div class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <h2 class="font-extrabold text-clinic-900">Ringkasan pesanan</h2>
        <span class="rounded-full px-2.5 py-1 text-xs font-bold"
              :class="status === 'submitted' ? 'bg-medical-50 text-medical-700' : (draft.ready ? 'bg-soeradji-50 text-soeradji-700' : 'bg-amber-50 text-amber-700')"
              x-text="readyLabel"></span>
    </div>

    <template x-if="draft.services.length === 0">
        <p class="rounded-2xl bg-clinic-50 p-4 text-sm text-clinic-500">Belum ada layanan. Ceritakan kebutuhan Anda di chat, misalnya "ganti kateter untuk ayah besok pagi".</p>
    </template>

    <ul class="space-y-2" x-show="draft.services.length > 0" x-cloak>
        <template x-for="s in draft.services" :key="s.id">
            <li class="flex items-center justify-between gap-3 rounded-2xl bg-clinic-50 px-3.5 py-3 text-sm">
                <span class="font-semibold text-clinic-800" x-text="s.name"></span>
                <span class="shrink-0 font-bold text-soeradji-700" x-text="s.price"></span>
            </li>
        </template>
    </ul>

    <dl class="divide-y divide-clinic-100 text-sm">
        <div class="flex justify-between gap-4 py-2.5"><dt class="text-clinic-500">Tanggal</dt><dd class="text-right font-semibold text-clinic-800" x-text="draft.date_label || '—'"></dd></div>
        <div class="flex justify-between gap-4 py-2.5"><dt class="text-clinic-500">Waktu</dt><dd class="text-right font-semibold text-clinic-800" x-text="draft.window_label || '—'"></dd></div>
        <div class="flex justify-between gap-4 py-2.5"><dt class="text-clinic-500">Pasien</dt><dd class="text-right font-semibold text-clinic-800" x-text="draft.patient || '—'"></dd></div>
        <div class="flex justify-between gap-4 py-2.5"><dt class="text-clinic-500">Alamat</dt><dd class="text-right font-semibold text-clinic-800" x-text="draft.address || '—'"></dd></div>
        <div class="flex justify-between gap-4 py-2.5"><dt class="text-clinic-500">Estimasi tarif</dt><dd class="text-right font-extrabold text-clinic-900" x-text="draft.services.length ? draft.total_label : '—'"></dd></div>
    </dl>

    <p class="rounded-2xl bg-soeradji-50/70 p-3.5 text-xs leading-relaxed text-soeradji-800" x-show="!isLoggedIn">
        <x-icon name="lock" class="mr-1 inline h-3.5 w-3.5" />
        Pesanan dikirim otomatis setelah Anda masuk. Draft ini tetap tersimpan selama sesi berjalan.
    </p>
    <p class="text-xs leading-relaxed text-clinic-500">Tarif adalah estimasi. Total final dikonfirmasi koordinator setelah skrining.</p>
</div>
