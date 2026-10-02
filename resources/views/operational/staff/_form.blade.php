{{-- Form tenaga kesehatan bersama (create/edit). Butuh: $staff, $professions, $linkableUsers, $formRoute --}}
<form method="POST" action="{{ $formRoute }}" class="space-y-5">
    @csrf
    @if ($staff->exists) @method('PUT') @endif

    <x-input name="name" label="Nama lengkap (dengan gelar)" required maxlength="191" icon="user"
             :value="$staff->name" placeholder="Contoh: Ns. Siti Rahayu, S.Kep." />

    <div class="grid sm:grid-cols-2 gap-5">
        <x-select name="profession" label="Profesi" required placeholder="Pilih profesi..."
                  :options="$professions->mapWithKeys(fn ($p) => [$p->value => $p->label()])->all()"
                  :value="$staff->profession?->value" />
        <x-input name="specialization" label="Spesialisasi/keahlian (opsional)" maxlength="128"
                 :value="$staff->specialization" placeholder="Contoh: wound care, geriatri" />
    </div>

    <div class="grid sm:grid-cols-2 gap-5">
        <x-input name="license_number" label="Nomor STR/SIP (opsional)" maxlength="64" icon="document"
                 :value="$staff->license_number" />
        <x-input name="phone" type="tel" label="Nomor telepon" inputmode="tel" icon="phone" maxlength="32"
                 :value="$staff->phone" />
    </div>

    <x-input name="email" type="email" label="Email (opsional)" icon="mail" maxlength="191"
             :value="$staff->email" />

    <x-select name="user_id" label="Hubungkan ke akun login (opsional)" placeholder="Tidak dihubungkan"
              :options="$linkableUsers->mapWithKeys(fn ($u) => [$u->id => $u->name.' — '.$u->email])->all()"
              :value="$staff->user_id"
              hint="Akun ber-role tenaga kesehatan yang bisa login ke area tugas. Bila dipilih, role akun otomatis disesuaikan." />

    <x-textarea name="bio" label="Profil singkat (opsional)" rows="3" maxlength="1000"
                :value="$staff->bio" placeholder="Pengalaman & keahlian — ditampilkan ke pasien." />

    <label class="flex items-center gap-3 cursor-pointer">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked((bool) ($staff->is_active ?? true))
               class="w-5 h-5 rounded-lg border-stone-300 text-brand-600 focus:ring-brand-500 focus:ring-offset-0" />
        <span class="text-sm font-semibold text-stone-700">Aktif (bisa ditugaskan)</span>
    </label>

    <div class="flex gap-3 pt-2">
        <x-button :href="route('operasional.petugas.index')" variant="ghost" full class="justify-center">Batal</x-button>
        <x-button type="submit" full icon="check" class="sm:w-auto sm:flex-none">Simpan</x-button>
    </div>
</form>
