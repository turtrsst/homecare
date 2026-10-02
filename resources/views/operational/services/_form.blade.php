{{-- Form layanan bersama (create/edit). Butuh: $service, $formRoute --}}
<form method="POST" action="{{ $formRoute }}" class="space-y-5">
    @csrf
    @if ($service->exists) @method('PUT') @endif

    <div class="grid sm:grid-cols-3 gap-5">
        <x-input name="code" label="Kode layanan" required maxlength="32" icon="list"
                 :value="$service->code" placeholder="HC-RAWAT-LUKA" />
        <div class="sm:col-span-2">
            <x-input name="name" label="Nama layanan" required maxlength="191" icon="heart"
                     :value="$service->name" placeholder="Contoh: Perawatan Luka di Rumah" />
        </div>
    </div>

    <x-input name="category" label="Kategori" maxlength="96"
             :value="$service->category" placeholder="Contoh: Perawatan Medis"
             hint="Dipakai untuk mengelompokkan layanan di katalog publik." />

    <div class="grid sm:grid-cols-2 gap-5">
        <x-select name="icon" label="Ikon" :options="\App\Models\HomecareService::ICON_OPTIONS"
                  :value="$service->icon" placeholder="Ikon bawaan (hati)"
                  hint="Dipakai sebagai gambar bila thumbnail kosong." />
        <x-input name="thumbnail" label="Thumbnail" maxlength="191" icon="image"
                 :value="$service->thumbnail" placeholder="images/services/MED-01.svg"
                 hint="Path dari folder public/ atau URL lengkap gambar." />
    </div>

    <x-input name="short_description" label="Deskripsi singkat" maxlength="255"
             :value="$service->short_description" placeholder="Satu kalimat yang jelas & menarik." />

    <x-textarea name="description" label="Deskripsi lengkap" rows="4" maxlength="5000"
                :value="$service->description"
                placeholder="Jelaskan lingkup layanan, siapa yang mengerjakan, alat yang dibawa, dll." />

    <div class="grid sm:grid-cols-3 gap-5">
        <x-input name="duration_minutes" type="number" label="Durasi (menit)" required inputmode="numeric" min="15" max="720" step="15"
                 :value="$service->duration_minutes" />
        <x-input name="price" type="number" label="Harga (Rp)" required inputmode="numeric" min="0" step="1000"
                 :value="(int) $service->price" />
        <x-input name="sort_order" type="number" label="Urutan tampil" inputmode="numeric" min="0" max="999"
                 :value="$service->sort_order ?? 0" />
    </div>

    <x-input name="price_note" label="Catatan harga (opsional)" maxlength="191"
             :value="$service->price_note" placeholder="Contoh: belum termasuk bahan habis pakai" />

    <div class="flex flex-wrap gap-6">
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked((bool) ($service->is_active ?? true))
                   class="w-5 h-5 rounded-lg border-stone-300 text-brand-600 focus:ring-brand-500 focus:ring-offset-0" />
            <span class="text-sm font-semibold text-stone-700">Aktif (tampil di katalog)</span>
        </label>
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="hidden" name="is_featured" value="0">
            <input type="checkbox" name="is_featured" value="1" @checked((bool) $service->is_featured)
                   class="w-5 h-5 rounded-lg border-stone-300 text-brand-600 focus:ring-brand-500 focus:ring-offset-0" />
            <span class="text-sm font-semibold text-stone-700">Unggulan (tampil di beranda)</span>
        </label>
    </div>

    <div class="flex gap-3 pt-2">
        <x-button :href="route('operasional.layanan.index')" variant="ghost" full class="justify-center">Batal</x-button>
        <x-button type="submit" full icon="check" class="sm:w-auto sm:flex-none">Simpan Layanan</x-button>
    </div>
</form>
