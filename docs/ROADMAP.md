# Roadmap — Soeradji Gocare

## PHASE 1 — FOUNDATION ✅
- Bootstrap Laravel 13 (PHP 8.2+), Blade + Tailwind v4 + Alpine + Vite.
- Environment, SQLite(dev)/MySQL(prod), autentikasi session (register/login/logout).
- Design system: layout public/app/admin, komponen Blade reusable, token warna/tipografi.
- Landing page + halaman publik (layanan, cara kerja, FAQ, kontak).
- Struktur `docs/` (dokumen ini), Git workflow per milestone.

## PHASE 2 — PATIENT ✅
- Profil pasien (multiple per akun), kontak, alamat (private data).
- Katalog layanan (master data, bukan hardcode) + cache.
- Booking wizard 7 langkah dengan validasi per langkah + session state.
- Status tracking dengan timeline ramah-manusia, riwayat, empty states.
- Upload dokumen (private disk, MIME/size validation, authorized download).

## PHASE 3 — OPERATIONAL ✅
- Dashboard operasional (ringkasan + antrean kerja).
- Verifikasi (terima/tolak/minta info), skrining (layanan final), penjadwalan,
  assignment petugas, konfirmasi biaya & pencatatan pembayaran.
- Manajemen master: layanan/tarif, tenaga kesehatan, pasien; monitoring kunjungan; laporan dasar.

## PHASE 4 — SERVICE DELIVERY ✅
- Dashboard petugas (tugas saya, hari ini).
- Berangkat → check-in → asesmen → tindakan → dokumentasi → check-out.
- Clinical notes + service record + attachment dokumentasi.

## PHASE 5 — API ✅
- Sanctum token auth; `/api/v1` (auth, services, patients, addresses, homecare,
  status, appointment, notifications); envelope konsisten; OpenAPI doc.

## PHASE 6 — HARDENING ✅
- Policies/Gates penuh, rate limiting, audit trail terpusat, error handling ramah,
  feature tests (workflow + authorization + API + UI sanity), seeder demo,
  README profesional.

## NEXT (backlog, tidak dikerjakan di versi ini)
- PWA (manifest + service worker) & mobile app (Flutter) di atas API v1.
- Integrasi nyata: `SimrsPatientProvider`, `SatusehatPatientProvider`,
  payment gateway, WhatsApp gateway, Google Maps geocoding.
- Queue worker produksi, redis, telemetry, SSO petugas RS.
- Telekonsultasi, penjadwalan recurring, klaim asuransi.

Catatan: versi pertama **standalone/local-first** — integrasi eksternal hadir sebagai
kontrak + stub terdokumentasi, bukan fake integration.
