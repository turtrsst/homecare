# 🏥 Soeradji Care

Aplikasi layanan **homecare** rumah sakit: pasien/keluarga memesan kunjungan tenaga
kesehatan ke rumah (dokter, perawat, bidan, fisioterapis, dll.), sementara tim rumah
sakit memverifikasi, menjadwalkan, menugaskan petugas, dan memantau pelaksanaan
kunjungan — lengkap dengan dokumentasi klinis, pembayaran, dan audit trail.

Dibangun dengan **Laravel 13 + Blade + Tailwind CSS v4 + Alpine.js**, seluruh antarmuka
berbahasa Indonesia, mobile-first, dan siap diintegrasikan dengan SIMRS/SATUSEHAT
melalui lapisan integrasi berbasis kontrak.

---

## ✨ Fitur Utama

### 🌐 Zona Publik
- Landing page dengan katalog layanan unggulan, alur kerja, FAQ, dan kontak
- Katalog layanan per kategori + halaman detail (harga, durasi, persiapan kunjungan)
- **Disclaimer kegawatdaruratan** (119 / IGD) di seluruh halaman relevan

### 👨‍👩‍👧 Zona Pasien (`/akun`)
- **Wizard pemesanan 7 langkah**: Pasien → Layanan → Kebutuhan → Lokasi → Jadwal → Review → Konfirmasi
- Dashboard personal: pengajuan aktif, jadwal kunjungan, riwayat
- **Timeline status visual** untuk setiap pengajuan
- Manajemen data pasien keluarga + banyak alamat kunjungan
- Jawab permintaan informasi tambahan, unggah dokumen (surat rujukan, hasil lab, foto kondisi)
- Notifikasi in-app untuk setiap perubahan status

### 🏥 Zona Operasional (`/operasional`) — Admin, Koordinator, Manajer
- Dashboard antrean kerja + kunjungan hari ini
- Siklus hidup pengajuan: **verifikasi → minta informasi → skrining → setujui/tolak → jadwalkan → monitor → selesai**
- Penjadwalan dengan assignment petugas (multi-petugas, primary/support) & penyesuaian item layanan
- Monitoring kunjungan real-time (check-in, asesmen, dokumentasi, check-out)
- Pencatatan pembayaran (tunai/transfer/asuransi) + invoice otomatis
- Master data: layanan & tarif, tenaga kesehatan
- Laporan periode (status, layanan terlaris, pendapatan) & **audit trail** lengkap

### 🩺 Zona Petugas (`/tugas`) — Tenaga Kesehatan
- Daftar tugas hari ini / akan datang
- Alur pelaksanaan: **berangkat → check-in → mulai → asesmen tanda vital → dokumentasi → check-out**
- Riwayat asesmen, catatan tim, unggah dokumentasi pelayanan

### 🔌 API v1 (`/api/v1`) — Sanctum Bearer Token
- Envelope JSON konsisten: `{ success, message, data | errors, meta }`
- Auth (register/login/logout/me), pasien & alamat, katalog layanan
- CRUD pengajuan homecare, status, jadwal kunjungan, pembatalan, notifikasi

## 🧱 Arsitektur

```
app/
├── Actions/        # Use-case transaksional (Submit, Verify, Approve, Schedule, Visit, Payment)
├── Enums/          # Status & klasifikasi sebagai PHP enum (bukan string ajaib)
├── Events/         # Peristiwa domain (perubahan status, assignment petugas)
├── Integrations/   # Kontrak provider eksternal + driver Local/Null (+ stub SIMRS/SATUSEHAT)
├── Listeners/      # Audit, notifikasi, sinkronisasi status (atribut #[Listens])
├── Models/         # 17+ model Eloquent dengan relasi & index yang tepat
├── Policies/       # Otorisasi per-model
├── Services/       # Layanan domain (katalog, penjadwalan, timeline, audit, dll.)
└── Support/        # Peta permission terpusat + envelope ApiResponse
```

Prinsip yang dipegang:

- **Tanpa `if ($user->role == ...)`** — semua otorisasi lewat Gate terpusat
  (`app/Support/Permissions.php`), Policy, dan middleware `role:`.
- **Controller tipis** — logika bisnis hidup di Actions/Services; Blade bebas logika domain.
- **Semua transisi status** melalui `RequestStatusManager`/`VisitActions` → memicu event →
  audit log + notifikasi otomatis. Tidak ada update status liar.
- **Integration-ready** — `PatientProviderInterface`, `NotificationChannelInterface`, dll.
  dengan driver lokal sebagai default; SIMRS/SATUSEHAT tinggal ganti binding (tanpa integrasi palsu).
- Status & master data **tidak pernah di-hardcode** di view — enum + tabel database.

📚 Detail: [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) · [docs/DATABASE.md](docs/DATABASE.md) ·
[docs/WORKFLOW.md](docs/WORKFLOW.md) · [docs/API.md](docs/API.md) · [docs/UI-UX.md](docs/UI-UX.md) ·
[docs/ROADMAP.md](docs/ROADMAP.md)

## 🚀 Menjalankan Aplikasi

> 📖 Panduan instalasi lengkap (database SQLite/MySQL, produksi, akses API,
> troubleshooting): **[docs/INSTALLATION.md](docs/INSTALLATION.md)**

Persyaratan: **PHP ≥ 8.3**, Composer, Node.js ≥ 20. SQLite dipakai default (zero-config);
MySQL/PostgreSQL siap pakai untuk produksi.

```bash
git clone https://github.com/turtrsst/homecare.git
cd homecare

composer install
cp .env.example .env
php artisan key:generate

# Database demo (10 layanan, 9 akun, 9 pengajuan di berbagai status):
php artisan migrate:fresh --seed

npm install
npm run build        # atau: npm run dev

php artisan serve    # http://localhost:8000
```

### 🔑 Akun Demo (password: `password`)

| Email | Peran | Area |
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

> Alur demo lengkap: login sebagai `budi@example.com` → pesan homecare lewat wizard →
> login `koordinator@homecare.rs` → verifikasi/setujui/jadwalkan → login `ns.siti@homecare.rs`
> → jalankan kunjungan sampai check-out → status pengajuan pasien otomatis **Selesai**.

## ✅ Pengujian

66 test feature/unit (autentikasi, otorisasi per-role, wizard pemesanan, siklus hidup
operasional, alur kunjungan petugas, API v1 + envelope).

```bash
php artisan test
# atau
php vendor/bin/phpunit
```

> Catatan sandbox: pada runtime php-wasm, gunakan `bin/run-tests.sh` — skrip ini
> menyiapkan database testing lebih dulu karena migrasi in-process tidak didukung
> runtime tersebut.

## 🔐 Keamanan & Privasi

- Otorisasi berlapis: middleware `role:` → Gate terpusat → Policy per-model
- Dokumen klinis disimpan di **disk privat** (bukan `/storage` publik), unduhan lewat policy
- Audit trail permanen (append-only) untuk setiap perubahan status & tindakan penting
- Data pasien hanya terlihat oleh pemilik akun dan petugas yang berwenang
- API dilindungi Sanctum + rate limiting; envelope error tidak membocorkan stack trace

## ⚠️ Batasan Layanan

Aplikasi ini **bukan layanan gawat darurat**. Seluruh antarmuka menampilkan arahan:
untuk kondisi mengancam nyawa (sesak berat, nyeri dada, penurunan kesadaran, perdarahan
hebat) hubungi **119** atau datangi **IGD** terdekat.

## 🗺️ Roadmap

Fase berikutnya (lihat [docs/ROADMAP.md](docs/ROADMAP.md)): integrasi SIMRS & SATUSEHAT,
pembayaran gateway, penjadwalan berulang (paket kunjungan), telekonsultasi, dan aplikasi
mobile petugas.

## 📄 Lisensi

MIT — lihat [LICENSE](LICENSE).
"# homecare" 
