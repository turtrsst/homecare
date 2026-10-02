# Panduan Instalasi — Soeradji Gocare

Panduan lengkap dari nol: prasyarat → database → konfigurasi → menjalankan server →
mengakses aplikasi (web & API) → pengujian → troubleshooting.

---

## 1. Prasyarat

| Kebutuhan | Versi minimum | Keterangan |
|---|---|---|
| PHP | **8.3** | Ekstensi: `pdo`, `pdo_sqlite` (atau `pdo_mysql`), `mbstring`, `openssl`, `curl`, `fileinfo`, `ctype`, `json`, `tokenizer`, `xml`. `intl` disarankan (format angka/tanggal Indonesia). |
| Composer | 2.x | Manajer dependensi PHP |
| Node.js | **20** | Untuk membangun aset (Vite + Tailwind v4) |
| npm | 10.x | Ikut terpasang dengan Node.js |
| Git | — | Mengunduh kode |
| Database | SQLite 3 **atau** MySQL 8 / MariaDB 10.6+ | SQLite = default zero-config (dev); MySQL = disarankan untuk produksi |

Cek cepat:

```bash
php -v && composer -V && node -v && npm -v
php -m | grep -E "pdo_sqlite|pdo_mysql|mbstring|intl"
```

---

## 2. Unduh Kode & Pasang Dependensi

```bash
git clone https://github.com/turtrsst/homecare.git
cd homecare

# Dependensi PHP (Laravel 13, Sanctum, dll.)
composer install

# Dependensi frontend (Tailwind v4, Alpine.js, Vite)
npm install
```

---

## 3. Konfigurasi Environment (`.env`)

```bash
cp .env.example .env
php artisan key:generate
```

`key:generate` **wajib** — tanpa APP_KEY, sesi & enkripsi tidak berfungsi.

Isi `.env` yang paling penting:

```dotenv
APP_NAME="Soeradji Gocare"
APP_ENV=local                 # produksi: production
APP_DEBUG=true                # produksi: false (WAJIB)
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Jakarta

# Identitas rumah sakit & aturan pemesanan (dipakai di UI + validasi)
HOMECARE_HOSPITAL_NAME="RS Harapan Sejahtera"
HOMECARE_EMERGENCY_NUMBER="119"
HOMECARE_CONTACT_PHONE="(021) 555-0100"
HOMECARE_CONTACT_WA="0811-5550-100"
HOMECARE_MIN_LEAD_DAYS=1      # pemesanan minimal H+1
HOMECARE_MAX_ADVANCE_DAYS=30  # maksimal H+30

# Lapisan integrasi (default: driver lokal, tanpa layanan eksternal)
INTEGRATION_PATIENT_PROVIDER=local
INTEGRATION_PAYMENT_PROVIDER=manual
INTEGRATION_MESSAGE_SENDER=log
INTEGRATION_MAPS_PROVIDER=null
```

Default yang sudah aman tanpa perubahan: `SESSION_DRIVER=database`,
`QUEUE_CONNECTION=database`, `CACHE_STORE=file`, `MAIL_MAILER=log`
(email tercatat di `storage/logs/laravel.log`). Untuk produksi, ganti mail ke
SMTP/gateway sesuai kebutuhan.

---

## 4. Setup Database

### Opsi A — SQLite (default, untuk pengembangan)

```bash
touch database/database.sqlite
```

Selesai — tidak perlu server database. Blok `DB_*` di `.env` cukup:

```dotenv
DB_CONNECTION=sqlite
```

### Opsi B — MySQL/MariaDB (disarankan untuk produksi)

1. Buat database & user:

```sql
CREATE DATABASE homecare CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'homecare'@'localhost' IDENTIFIED BY 'password-kuat-anda';
GRANT ALL PRIVILEGES ON homecare.* TO 'homecare'@'localhost';
FLUSH PRIVILEGES;
```

2. Sesuaikan `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=homecare
DB_USERNAME=homecare
DB_PASSWORD=password-kuat-anda
```

3. Uji koneksi:

```bash
php artisan db:show
```

---

## 5. Migrasi & Data Awal

Migrasi membuat **22 tabel** (users, patient_profiles, homecare_services,
homecare_requests, appointments, staff_assignments, assessments, service_records,
payments, audit_logs, attachments, notifications, personal_access_tokens, dst.)
lengkap dengan index dan foreign key.

```bash
# Lingkungan development/demo — reset + isi data demo:
php artisan migrate:fresh --seed
```

Seeder demo membuat:

- **9 akun** lintas peran (pasien, koordinator, admin, manajer, 4 tenaga kesehatan)
- **82 tindakan homecare** sesuai SK Direktur Utama No. HK.02.03/D.XXVI/5441/2024
  (18 Maret 2024) dalam 5 kelompok; 2 baris kembar pada lampiran disimpan
  dengan status tidak aktif. Data dibaca dari `database/data/sk_tarif_homecare.json`.
- **9 pengajuan** yang mencakup *seluruh* status (draft → selesai/ditolak/dibatalkan)
- Kunjungan terjadwal, asesmen, pembayaran, 22 entri audit, notifikasi

> ⚠️ **Produksi:** jalankan `php artisan migrate --force` **tanpa** `--seed`
> (seeder berisi data demo, bukan data asli). Buat akun admin pertama lewat
> seeder kustom atau `php artisan tinker`:
>
> ```php
> \App\Models\User::create([
>     'name' => 'Administrator', 'email' => 'admin@rs-anda.id',
>     'password' => bcrypt('ganti-saya'), 'role' => \App\Enums\UserRole::Admin,
>     'is_active' => true, 'email_verified_at' => now(),
> ]);
> ```

---

## 6. Bangun Aset Frontend

```bash
npm run build        # produksi / sekali jalan
# atau
npm run dev          # development dengan hot-reload (Vite dev server)
```

Hasil build masuk ke `public/build/` (di-git-ignore — setiap deployment wajib build).

### Thumbnail katalog layanan

82 thumbnail SVG (satu per tindakan pada SK tarif) sudah tersimpan di
`public/images/services/`. Bila daftar layanan atau ikonnya berubah, bangun ulang:

```bash
node scripts/generate-service-thumbnails.mjs
```

Generator membaca `database/data/sk_tarif_homecare.json` (data tarif) dan
`resources/views/components/icon.blade.php` (kumpulan ikon) — keduanya menjadi
satu sumber yang sama dengan seeder.

---

## 7. Jalankan Server

### Development

```bash
php artisan serve
# → http://localhost:8000
```

### Produksi (ringkasan)

1. Web server (nginx/Apache) mengarah ke folder **`public/`** saja.
2. `.env`: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` domain asli, MySQL terisi.
3. Optimasi:

   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan config:cache && php artisan route:cache \
     && php artisan view:cache && php artisan event:cache
   ```

4. Izin tulis untuk web server user:

   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

5. Queue worker (notifikasi & tugas latar — `QUEUE_CONNECTION=database`):

   ```bash
   php artisan queue:work --tries=3   # via systemd/supervisor
   ```

> `php artisan storage:link` **tidak diperlukan** — seluruh dokumen/lampiran
> disimpan di disk *privat* dan diunduh melalui controller dengan pengecekan policy.

---

## 8. Akses Aplikasi (Web)

| URL | Zona | Siapa |
|---|---|---|
| `/` | Publik | Landing page, semua orang |
| `/layanan` | Publik | Katalog + detail layanan |
| `/faq`, `/kontak` | Publik | Informasi & kontak RS |
| `/masuk`, `/daftar` | Auth | Login / registrasi pasien |
| `/akun` | Pasien | Dashboard, wizard pemesanan, timeline status, dokumen |
| `/operasional` | RS | Verifikasi, skrining, penjadwalan, monitoring, pembayaran, laporan, audit |
| `/tugas` | Petugas | Daftar tugas, check-in/out, asesmen, dokumentasi |

### Akun demo (setelah `--seed`) — password semua: `password`

| Email | Peran | Masuk ke |
|---|---|---|
| `budi@example.com` | Pasien (keluarga) | `/akun` |
| `sari@example.com` | Pasien (keluarga) | `/akun` |
| `koordinator@homecare.rs` | Koordinator Homecare | `/operasional` |
| `admin@homecare.rs` | Administrator | `/operasional` (semua menu) |
| `manajer@homecare.rs` | Manajer | `/operasional` (laporan & audit) |
| `dr.andini@homecare.rs` | Dokter | `/tugas` |
| `ns.siti@homecare.rs` | Perawat wound care | `/tugas` |
| `ns.bagus@homecare.rs` | Perawat | `/tugas` |
| `ft.rina@homecare.rs` | Fisioterapis | `/tugas` |

### Skenario demo end-to-end

1. Login `budi@example.com` → **Ajukan Homecare** → ikuti wizard 7 langkah → konfirmasi.
2. Login `koordinator@homecare.rs` → verifikasi → skrining → setujui → jadwalkan
   (pilih tanggal, petugas, item layanan) → invoice otomatis dibuat.
3. Login `ns.siti@homecare.rs` → buka tugas → berangkat → check-in → mulai →
   isi asesmen tanda vital → dokumentasi → check-out.
4. Kembali ke akun `budi` → status pengajuan otomatis **Selesai** + riwayat pembayaran.

---

## 9. Akses API v1

Base URL: **`http://localhost:8000/api/v1`** — autentikasi **Bearer token (Sanctum)**.
Semua respons memakai envelope konsisten:

```json
{ "success": true, "message": "...", "data": { }, "meta": { } }
{ "success": false, "message": "...", "errors": { } }
```

### a. Register

```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{
    "name": "Pasien API",
    "email": "api@example.com",
    "phone": "081234567890",
    "password": "rahasia-api-1",
    "password_confirmation": "rahasia-api-1"
  }'
```

### b. Login → ambil token

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{ "email": "api@example.com", "password": "rahasia-api-1" }'
# → data.token  (simpan; kirim sebagai header Authorization)
```

### c. Endpoint utama

```bash
TOKEN="salin-token-dari-login"

# Publik (tanpa token)
curl http://localhost:8000/api/v1/services -H "Accept: application/json"
curl http://localhost:8000/api/v1/services/perawatan-luka-ganti-balutan

# Wajib token
curl http://localhost:8000/api/v1/auth/me        -H "Authorization: Bearer $TOKEN"
curl http://localhost:8000/api/v1/patients       -H "Authorization: Bearer $TOKEN"
curl http://localhost:8000/api/v1/addresses      -H "Authorization: Bearer $TOKEN"
curl http://localhost:8000/api/v1/homecare       -H "Authorization: Bearer $TOKEN"   # daftar pengajuan saya
curl http://localhost:8000/api/v1/notifications  -H "Authorization: Bearer $TOKEN"

# Buat pengajuan homecare (patient_id = id profil dari /patients,
# service_ids + quantities, alamat, jadwal)
curl -X POST http://localhost:8000/api/v1/homecare \
  -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{ ... }'   # skema lengkap + contoh: docs/API.md

# Detail, status, jadwal kunjungan, pembatalan
curl http://localhost:8000/api/v1/homecare/HC-202609-0005          -H "Authorization: Bearer $TOKEN"
curl http://localhost:8000/api/v1/homecare/HC-202609-0005/status   -H "Authorization: Bearer $TOKEN"
curl http://localhost:8000/api/v1/homecare/HC-202609-0005/appointment -H "Authorization: Bearer $TOKEN"
curl -X POST http://localhost:8000/api/v1/homecare/HC-202609-0005/cancel -H "Authorization: Bearer $TOKEN"

# Logout (mencabut token)
curl -X POST http://localhost:8000/api/v1/auth/logout -H "Authorization: Bearer $TOKEN"
```

Rate limit: login/register 6x/menit/IP, pembuatan pengajuan 10x/menit/user.
Dokumentasi endpoint lengkap (field, validasi, contoh respons): **`docs/API.md`**.

---

## 10. Menjalankan Test

```bash
php artisan test          # atau: php vendor/bin/phpunit
```

66 test feature/unit (autentikasi, otorisasi per-role, wizard, alur operasional,
kunjungan petugas, API v1). Test memakai database terpisah
`database/testing.sqlite` — tidak menyentuh data development.

> Di sandbox php-wasm gunakan `bin/run-tests.sh` (migrasi in-process tidak
> didukung runtime tersebut; skrip menyiapkan testing.sqlite lebih dulu).

---

## 11. Troubleshooting

| Gejala | Penyebab & solusi |
|---|---|
| `No application encryption key has been specified` | Jalankan `php artisan key:generate` |
| `could not find driver` saat migrate | Ekstensi PDO kurang: pasang `pdo_sqlite` (atau `pdo_mysql`) lalu ulangi |
| Halaman 500 setelah ganti `.env` | Cache konfigurasi basi: `php artisan config:clear` (dev) atau `config:cache` ulang (produksi) |
| CSS/JS tidak muncul / tampilan polos | Aset belum di-build: `npm install && npm run build` (atau `npm run dev`) |
| Gambar layanan (thumbnail) kosong | SVG belum digenerate: `node scripts/generate-service-thumbnails.mjs` |
| Login selalu gagal / sesi tidak menempel | Pastikan `SESSION_DRIVER=database` dan migrasi sudah dijalankan; cek izin tulis `storage/` |
| Test error `unable to open database file` | Buat file test DB: `touch database/testing.sqlite` (atau jalankan `bin/run-tests.sh`) |
| Unggahan dokumen gagal | Izin tulis: `chmod -R 775 storage bootstrap/cache` |
| Halaman publik menampilkan koleksi aneh (`__PHP_Incomplete_Class`) | Jangan pakai `CACHE_STORE=database` di runtime php-wasm; gunakan `file` lalu `php artisan cache:clear` |
| Port 8000 sudah terpakai | `php artisan serve --port=8080` |

---

Setelah semua langkah di atas: buka **http://localhost:8000**, login dengan akun
demo, dan aplikasi siap digunakan. 🏥
