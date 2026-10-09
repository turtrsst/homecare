# UI/UX — Soeradji Care

Bahasa utama: **Bahasa Indonesia**, istilah awam (bukan istilah teknis/administratif).
Mobile-first. Target: *"Pasien/keluarga mengerti apa yang harus dilakukan tanpa belajar sistem."*

## Prinsip

1. **Jelas** — pengguna selalu tahu: saya di mana, harus apa, apa yang terjadi setelah klik,
   status pengajuan, kapan petugas datang, berapa biaya, siapa yang datang, apa yang disiapkan.
2. **Tenang & terpercaya** — palet lembut, banyak ruang putih, tipografi besar dan terbaca,
   tanpa angka/dashboard ramai di sisi pasien.
3. **Aman** — banner kegawatdaruratan (119/IGD) di landing & booking; tanpa diagnosis otomatis;
   tanpa klaim medis berlebihan.
4. **Aksesibel** — kontras AA, semantic HTML, label form, focus state terlihat, navigasi
   keyboard, aria secukupnya, status tidak hanya disampaikan lewat warna (ikon + teks).
5. **Tidak membuat user mengira aplikasi hang** — loading state/skeleton; empty state + CTA.

## Design Tokens (Tailwind v4 `@theme`)

- Warna utama `brand` = teal-600 family (tenang, medis, tidak dingin).
- Aksen `warm` untuk CTA penting; netral `stone/slate` untuk teks.
- Status: sukses=hijau, proses=biru, menunggu=amber, gagal/batal=merah — selalu
  **pasangan ikon + label teks**, bukan warna saja.
- Radius besar (`rounded-2xl`), shadow halus, touch target ≥ 44px (`min-h-11`),
  body text ≥ 16px.

## Layout

| Area | Layout | Navigasi |
|---|---|---|
| Public (landing, layanan, FAQ) | `layouts/public` | Topbar sederhana + footer (kontak, emergency) |
| Auth (masuk/daftar) | `layouts/guest` | Kartu terpusat |
| Pasien | `layouts/app` | **Bottom nav mobile** (Beranda, Pesan, Riwayat, Profil) + topbar desktop; **tanpa sidebar besar** |
| Operasional (admin/koordinator/manager) | `layouts/admin` | Sidebar (boleh padat-informasi) + topbar |
| Petugas | `layouts/app` (varian staff) | Bottom nav: Tugas, Hari Ini, Profil |

## Komponen Blade reusable (`resources/views/components`)

`button`, `input`, `select`, `textarea`, `card`, `badge` (+`status-badge`),
`alert`, `modal` (Alpine), `toast`, `empty-state`, `skeleton`, `stepper`,
`status-timeline`, `patient-card`, `service-card`, `appointment-card`,
`emergency-banner`, `icon` (sprite inline SVG).

## Sitemap Pasien

```
/ (landing: hero, layanan unggulan, cara kerja, manfaat, keamanan, nakes, FAQ, kontak, disclaimer)
/layanan, /layanan/{slug}, /cara-kerja, /faq, /kontak
/masuk, /daftar
/akun                    → dashboard ("Halo, X 👋 Apa yang Anda butuhkan hari ini?" + CTA besar + kartu pengajuan aktif + layanan + riwayat)
/akun/pesan/{step}       → wizard 7 langkah (progress terlihat, validasi per langkah, input tidak hilang saat gagal)
/akun/pengajuan          → riwayat (kartus, filter status sederhana)
/akun/pengajuan/{code}   → detail: status, timeline, jadwal, petugas, biaya, persiapan, aksi (batal/lengkapi info)
/akun/pasien, /akun/alamat, /akun/profil, /akun/notifikasi
```

## Wizard Booking (bukan form panjang)

```
1 Pasien → 2 Layanan → 3 Kebutuhan → 4 Lokasi → 5 Jadwal → 6 Review → 7 Konfirmasi
```

- Progress bar + label langkah selalu terlihat.
- Simpan state di session; `old()` + session repopulate → **input tidak hilang** saat validasi gagal.
- Ringkasan kumulatif (sticky di mobile) agar pengguna tahu apa yang sudah dipilih.

## Admin (boleh information-dense, tetap modern)

Dashboard: kartu ringkasan (Menunggu Verifikasi, Perlu Ditindaklanjuti, Terjadwal Hari Ini,
Sedang Berlangsung, Selesai) + antrean kerja. Tabel dengan **filter, search, pagination,
sorting, status badge, quick action** — informasi bertahap, tidak semua sekaligus.
Di mobile tabel berubah menjadi kartu bertumpuk.

## Error & Empty State

- Error sistem → "Maaf, permintaan belum dapat diproses. Silakan coba kembali."
  (tanpa `SQLSTATE...`).
- Validasi → Bahasa Indonesia ("Nomor telepon wajib diisi."), inline di bawah field.
- Empty state → ilustrasi/ikon + kalimat menenangkan + CTA ("Pesan Homecare").

## Responsible Healthcare

Banner tetap: **"Homecare bukan layanan kegawatdaruratan. Kondisi darurat? Hubungi 119
atau IGD rumah sakit terdekat."** Tidak ada diagnosis otomatis, tidak ada klaim medis
tanpa dasar; konten edukasi bersifat informatif.
