# Workflow & State Machine — Soeradji Care

## Alur Utama

```
PASIEN / KELUARGA
  Landing page → Pilih layanan → Login/Registrasi → Pilih/Tambah pasien
  → Isi kebutuhan → Pilih alamat → Pilih tanggal & waktu → Review → Submit
        ▼
STATUS: submitted  (pasien melihat "Menunggu Verifikasi")
        ▼
ADMIN / KOORDINATOR
  ├─ Tolak            → rejected  (+ alasan, notifikasi ke pasien)
  ├─ Minta informasi  → need_information (+ daftar pertanyaan; pasien melengkapi → submitted)
  └─ Terima           → under_review → approved (skrining: tentukan layanan final)
        ▼
  Tentukan tenaga kesehatan + jadwal + konfirmasi biaya → scheduled
        ▼
  Pembayaran dicatat → payment_status: paid (tidak memblokir status; kebijakan RS)
        ▼
PETUGAS HOMECARE (hari kunjungan)
  Berangkat → on_the_way
  Check-in lokasi → checked_in
  Asesmen pasien → (assessment tersimpan)
  Pelaksanaan tindakan + dokumentasi → in_service
  Check-out → completed
        ▼
Evaluasi / Follow-up → riwayat pasien (+ optional clinical note follow-up)
```

Pembatalan: `cancelled` dapat terjadi dari status manapun sebelum `completed`
(dengan alasan; oleh pasien untuk status awal, oleh staff untuk status lanjut).

## Enum & Transisi

### HomecareRequestStatus

| Status | Label (pasien) | Transisi valid ke |
|---|---|---|
| `draft` | Draft | submitted, cancelled |
| `submitted` | Menunggu Verifikasi | under_review, need_information, rejected, cancelled |
| `under_review` | Sedang Diverifikasi | approved, need_information, rejected, cancelled |
| `need_information` | Perlu Informasi Tambahan | submitted, cancelled |
| `approved` | Disetujui | scheduled, cancelled, rejected |
| `scheduled` | Terjadwal | in_progress, completed, cancelled |
| `in_progress` | Sedang Berlangsung | completed, cancelled |
| `completed` | Selesai | — |
| `cancelled` | Dibatalkan | — |
| `rejected` | Ditolak | — |

Status kunjungan yang lebih rinci (`on_the_way`, `checked_in`, `in_service`) hidup di
**AppointmentStatus** dan diproyeksikan ke `HomecareRequestStatus.in_progress` agar model
request tetap sederhana namun tetap ekspresif dan mudah diperluas.

### AppointmentStatus

`scheduled → on_the_way → checked_in → in_service → completed` (+ `cancelled`).

### StaffAssignmentStatus

`assigned → active → completed` (+ `cancelled`). `assigned` = ditugaskan koordinator,
`active` = petugas mulai bekerja (check-in).

### PaymentStatus (request & payment)

`unpaid → partial → paid` (+ `failed`, `refunded`, `waived`).

### PaymentMethod

`cash`, `transfer`, `insurance`, `gateway` (masa depan).

## Aturan Bisnis (ditegakkan di Actions/Policies, bukan Blade)

1. Pasien hanya dapat membatalkan request miliknya selama status ≤ `scheduled`
   (belum `in_progress`/`completed`).
2. Verifikasi/skrining/penjadwalan/assignment hanya oleh `coordinator`/`admin`
   (gate `requests.verify`, `requests.schedule`).
3. Check-in dst. hanya oleh petugas yang **ter-assign** pada appointment terkait
   (policy per-resource, bukan sekadar role).
4. `scheduled` mensyaratkan: layanan final ≥ 1 item, minimal 1 staff assignment,
   jadwal terisi, dan total biaya terkonfirmasi.
5. `completed` (request) otomatis mengikuti appointment `completed` + service record ada.
6. Setiap transisi status: catat **audit log** + kirim **notifikasi** ke pihak relevan.
7. Tanggal pilihan pasien minimal `H+1` (konfigurasi `config/homecare.php`), slot waktu
   pagi/siang/sore.

## Timeline untuk Pasien

Timeline pasien adalah proyeksi ramah-manusia dari status teknis:

```
✓ Pengajuan diterima → ✓ Sedang diverifikasi → ✓ Homecare disetujui
→ ✓ Petugas & jadwal ditetapkan → ● Petugas menuju lokasi → ○ Pelayanan → ○ Selesai
```

Dibangun oleh `RequestTimeline` (service) dari: status request, status appointment,
waktu verifikasi/approval/scheduling, check-in/out, dan entri audit — **bukan** hanya
menampilkan `Status: APPROVED`.
