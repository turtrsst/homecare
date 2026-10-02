# Architecture — Soeradji Gocare

## Ringkasan

Aplikasi **Soeradji Gocare** berbasis Laravel yang memungkinkan pasien/keluarga
mengajukan pelayanan kesehatan di rumah, dan memungkinkan rumah sakit memverifikasi,
menjadwalkan, menugaskan tenaga kesehatan, memonitor kunjungan, serta mendokumentasikan
pelayanan.

Prinsip utama:

1. **Standalone-first** — database lokal adalah source of truth. Integrasi eksternal
   (SIMRS, SATUSEHAT, payment gateway, WhatsApp) disiapkan melalui *interface* tanpa
   fake integration.
2. **API-first** — seluruh fitur konsumen tersedia juga melalui REST API `/api/v1`
   (Sanctum) untuk aplikasi mobile di masa depan.
3. **Domain-driven ringan** — business rule hidup di `Actions`, `Services`, `Policies`,
   `Form Requests`, dan `Enums`, bukan di controller atau Blade.
4. **Human-centered UI** — Blade + Tailwind + Alpine, mobile-first, Bahasa Indonesia.

## Layering

```
HTTP (Blade controllers)      API v1 (Sanctum controllers)
        │                              │
        ▼                              ▼
   Form Requests (validasi) ──── Policies / Gates (otorisasi)
        │                              │
        └──────────────┬───────────────┘
                       ▼
        app/Actions        (use-case tunggal: SubmitHomecareRequest, ApproveHomecareRequest, ...)
                       ▼
        app/Services       (orkestrasi domain: PatientService, SchedulingService, PaymentService, ...)
                       ▼
        app/Integrations   (kontrak provider eksternal + implementasi Local/Null)
                       ▼
        Eloquent Models + Enums (state machine)
                       ▼
        Events → Listeners → Notifications (database, mail; channel lain menyusul)
                       ▼
        AuditService (audit trail terpusat)
```

Aturan:

- Controller **tidak** memanggil `Http::` / query kompleks langsung; ia mendelegasikan ke
  Action/Service.
- Blade **tidak** berisi business rule; hanya presentasi + komponen.
- Setiap entitas ber-status memakai **PHP enum** dengan label Bahasa Indonesia dan
  transisi yang didefinisikan eksplisit (`canTransitionTo()`).

## Integrasi Eksternal (kontrak, bukan implementasi prematur)

| Kebutuhan                | Kontrak                              | Driver aktif sekarang      | Driver masa depan                |
|--------------------------|--------------------------------------|----------------------------|----------------------------------|
| Data pasien eksternal    | `PatientDataProvider`                | `LocalPatientProvider`     | `SimrsPatientProvider`, `SatusehatPatientProvider` |
| Pembayaran               | `PaymentGatewayProvider`             | `ManualPaymentProvider`    | gateway (Xendit/Midtrans/dll.)   |
| Pesan singkat/WA         | `MessageSender`                      | `LogMessageSender`         | `WhatsAppGatewaySender`          |
| Geocoding/peta           | `MapsProvider`                       | `NullMapsProvider`         | `GoogleMapsProvider`             |

Driver dipilih lewat `config/integrations.php` + environment, dan dikonfigurasi per-sistem
melalui tabel `api_integrations`. Provider eksternal yang belum tersedia **tidak**
berpura-pura terhubung — ia adalah stub terdokumentasi yang melempar
`IntegrationNotConfiguredException` bila dipanggil tanpa konfigurasi.

## Autentikasi & Otorisasi

- Web: session + CSRF (auth bawaan Laravel, controller ringan di `app/Http/Controllers/Auth`).
- API: **Laravel Sanctum** personal access token (`/api/v1/auth/login`).
- Role: enum `UserRole` = `patient | admin | coordinator | medical_staff | manager`.
- Tidak ada `if ($user->role == 'admin')` tersebar — pakai:
  - `middleware role:...` (kasar, per grup route),
  - **Gates** (`admin.access`, `requests.verify`, `requests.schedule`, dst.) yang
    didefinisikan terpusat dari peta role→permission di `app/Support/Permissions.php`,
  - **Policies** per model untuk kepemilikan resource (pasien hanya melihat datanya,
    petugas hanya melihat tugasnya).

## Notifikasi

Laravel Notifications dengan channel `database` (in-app) + `mail` (log driver saat dev).
Kontrak `MessageSender` disiapkan untuk WhatsApp/SMS/push. Semua notifikasi penting
workflow (status berubah, jadwal terbit, petugas ditugaskan) dikirim via event listener
sehingga channel baru cukup didaftarkan tanpa mengubah alur bisnis.

## Audit Trail

`AuditService::log()` menulis ke `audit_logs` (user, event, entitas morph, IP, user agent,
metadata JSON). Metadata **tidak pernah** memuat password/token/secret. Semua Action
penting (submit, verifikasi, skrining, penjadwalan, assignment, check-in/out, pembayaran,
pembatalan) tercatat.

## Penyimpanan File

Dokumen sensitif (KTP, rujukan, hasil lab, foto kondisi) disimpan di disk `private`
(`storage/app/private`), nama file di-random, validasi MIME + ukuran, dan hanya dapat
diunduh melalui controller yang memeriksa policy (`AttachmentController`).

## Struktur Direktori Penting

```
app/
  Actions/          # use case atomik (Submit/Approve/Schedule/Assign/CheckIn/Complete/Cancel...)
  Enums/            # UserRole, HomecareRequestStatus, AppointmentStatus, ...
  Integrations/     # Contracts/ + Local/ + Simrs/ + Satusehat/ + Payment/ + Maps/ + Messaging/
  Models/
  Notifications/
  Observers/
  Policies/
  Services/         # PatientService, HomecareService, SchedulingService, PaymentService, AuditService, AttachmentService
  Support/          # Permissions, ResponseEnvelope (API), dsb.
  Http/
    Controllers/{Auth, Patient, Operational, Staff, Api/V1}
    Middleware/     # EnsureUserHasRole
    Requests/     # Form Requests per use case
docs/               # dokumen ini
routes/             # web.php (public/patient/operational/staff), api.php (v1)
```

## Performa

- Eager loading eksplisit di controller/service (tidak ada query di Blade).
- Index pada kolom status, tanggal, dan FK.
- Cache master data layanan (`Cache::remember`, invalidasi via observer).
- Queue driver `sync` untuk standalone; siap dipindah ke `database`/redis worker.
