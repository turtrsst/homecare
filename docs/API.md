# API v1 — Soeradji Care

Base URL: `GET|POST /api/v1/...` — autentikasi **Laravel Sanctum** (Bearer token),
rate limiting `throttle:api` (60/menit) dan `throttle:5,1` untuk login.

## Envelope Respons (konsisten)

Sukses:

```json
{ "success": true, "message": "Data berhasil diperoleh", "data": { } , "meta": { } }
```

Error validasi (HTTP 422):

```json
{ "success": false, "message": "Data tidak valid", "errors": { "phone": ["Nomor telepon wajib diisi."] } }
```

Error lain: 401 (tidak terautentikasi), 403 (tidak berwenang), 404 (tidak ditemukan),
429 (rate limit), 500 (pesan generik aman — detail tidak dibocorkan).

## Endpoints

| Method | Path | Auth | Deskripsi |
|---|---|---|---|
| POST | `/api/v1/auth/register` | – | Registrasi pasien (nama, email, telepon, password) |
| POST | `/api/v1/auth/login` | – | Login → `{ token, user }` |
| POST | `/api/v1/auth/logout` | ✓ | Cabut token aktif |
| GET | `/api/v1/auth/me` | ✓ | Profil user login |
| GET | `/api/v1/services` | – | Katalog layanan homecare aktif |
| GET | `/api/v1/services/{slug}` | – | Detail layanan |
| GET/POST | `/api/v1/patients` | ✓ | Daftar/tambah profil pasien milik user |
| GET/PUT | `/api/v1/patients/{id}` | ✓ | Detail/ubah profil pasien (policy: pemilik) |
| GET/POST | `/api/v1/patients/{id}/addresses` | ✓ | Alamat pasien |
| GET | `/api/v1/addresses` | ✓ | Semua alamat pasien milik user |
| GET/POST | `/api/v1/homecare` | ✓ | Riwayat pengajuan / buat pengajuan baru |
| GET | `/api/v1/homecare/{id}` | ✓ | Detail pengajuan (+ items, appointment, payments) |
| GET | `/api/v1/homecare/{id}/status` | ✓ | Status + timeline untuk UI mobile |
| GET | `/api/v1/homecare/{id}/appointment` | ✓ | Jadwal & petugas ter-assign |
| POST | `/api/v1/homecare/{id}/cancel` | ✓ | Batalkan pengajuan (aturan status) |
| GET | `/api/v1/notifications` | ✓ | Notifikasi in-app (paginated) |
| POST | `/api/v1/notifications/{id}/read` | ✓ | Tandai dibaca |

## Contoh: membuat pengajuan

```http
POST /api/v1/homecare
Authorization: Bearer {token}
Content-Type: application/json

{
  "patient_profile_id": 1,
  "patient_address_id": 2,
  "service_ids": [3],
  "complaint": "Luka operasi belum kering, perlu ganti perban",
  "preferred_date": "2026-09-25",
  "preferred_time_window": "morning",
  "notes": "Rumah pagar hitam, hubungi bila tersesat"
}
```

Respons `201`:

```json
{
  "success": true,
  "message": "Pengajuan homecare berhasil dibuat",
  "data": {
    "id": 12,
    "code": "HC-202609-0012",
    "status": "submitted",
    "status_label": "Menunggu Verifikasi",
    "timeline": [ { "key": "submitted", "label": "Pengajuan diterima", "done": true } ]
  }
}
```

## Dokumentasi Mesin

Spesifikasi OpenAPI 3 tersedia di [`docs/openapi.yaml`](openapi.yaml).

## Versioning

Semua endpoint berada di bawah prefix `v1` (`Route::prefix('v1')` + namespace
`App\Http\Controllers\Api\V1`). Breaking change → `v2` baru, `v1` dipertahankan.
