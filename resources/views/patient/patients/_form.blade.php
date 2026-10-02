{{-- Kolom bersama form pasien (create & edit). Butuh: $relationships --}}
@php
    $bloodTypes = ['A','B','AB','O','A+','A-','B+','B-','AB+','AB-','O+','O-','tidak_tahu'];
@endphp

<div class="space-y-5">
    <x-input name="name" label="Nama lengkap pasien" required icon="user" maxlength="120"
             :value="$patient->name ?? null" placeholder="Nama sesuai kartu identitas" />

    <x-select name="relationship" label="Hubungan dengan Anda" required :options="$relationships"
              :value="$patient->relationship ?? null" placeholder="Pilih hubungan..." />

    <div class="grid sm:grid-cols-2 gap-5">
        <div>
            <x-input name="birth_date" type="date" label="Tanggal lahir" icon="calendar"
                     :value="isset($patient->birth_date) ? $patient->birth_date->format('Y-m-d') : null"
                     hint="Opsional — membantu petugas menyiapkan dosis & alat." />
        </div>
        <x-select name="gender" label="Jenis kelamin"
                  :options="['male' => 'Laki-laki', 'female' => 'Perempuan']"
                  :value="$patient->gender?->value ?? null" placeholder="Pilih..." />
    </div>

    <div class="grid sm:grid-cols-2 gap-5">
        <x-input name="nik" label="NIK" inputmode="numeric" maxlength="16" icon="document"
                 :value="$patient->nik ?? null" placeholder="16 digit angka" hint="Opsional." />
        <x-select name="blood_type" label="Golongan darah"
                  :options="array_combine($bloodTypes, array_map(fn ($b) => $b === 'tidak_tahu' ? 'Tidak tahu' : $b, $bloodTypes))"
                  :value="$patient->blood_type ?? null" placeholder="Pilih..." />
    </div>

    <x-input name="phone" type="tel" label="Nomor telepon pasien/keluarga" inputmode="tel" icon="phone"
             :value="$phone ?? ($patient?->contacts?->first()?->value ?? null)"
             placeholder="08xx xxxx xxxx" hint="Opsional — dipakai bila nomor Anda sulit dihubungi." />

    <x-textarea name="medical_notes" label="Catatan kesehatan" rows="3" maxlength="2000"
                :value="$patient->medical_notes ?? null"
                placeholder="Riwayat penyakit, alergi obat, obat rutin, atau hal penting lain yang perlu diketahui petugas." />
</div>
