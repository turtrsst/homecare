<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\HomecareRequestStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\StaffAssignmentStatus;
use App\Enums\TimeWindow;
use App\Models\Appointment;
use App\Models\Assessment;
use App\Models\AuditLog;
use App\Models\HealthcareStaff;
use App\Models\HomecareRequest;
use App\Models\HomecareRequestItem;
use App\Models\HomecareService;
use App\Models\Payment;
use App\Models\ServiceRecord;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Demo pengajuan homecare di berbagai tahap siklus hidup:
 * selesai, sedang berlangsung, terjadwal, disetujui, menunggu verifikasi,
 * perlu informasi, ditolak, dan dibatalkan.
 */
class DemoRequestSeeder extends Seeder
{
    public function run(): void
    {
        $ctx = app(DemoUserSeeder::class)->run();

        /** @var User $admin */
        $coordinator = $ctx['coordinator'];
        /** @var array<string, HealthcareStaff> $staff */
        $staff = $ctx['staff'];
        $p = $ctx['patients'];

        $seq = 0;
        $nextCode = function () use (&$seq) {
            return 'HC-'.now()->format('Ym').'-'.str_pad((string) ++$seq, 4, '0', STR_PAD_LEFT);
        };

        $createRequest = function (array $attrs, array $items) use ($nextCode): HomecareRequest {
            return DB::transaction(function () use ($attrs, $items, $nextCode) {
                $request = HomecareRequest::create(['code' => $nextCode()] + $attrs);

                $total = 0.0;
                foreach ($items as [$code, $qty, $notes]) {
                    /** @var HomecareService $service */
                    $service = HomecareService::where('code', $code)->firstOrFail();
                    $subtotal = (float) $service->price * $qty;
                    $total += $subtotal;

                    HomecareRequestItem::create([
                        'homecare_request_id' => $request->id,
                        'homecare_service_id' => $service->id,
                        'service_name' => $service->name,
                        'quantity' => $qty,
                        'unit_price' => $service->price,
                        'subtotal' => $subtotal,
                        'notes' => $notes,
                    ]);
                }

                $request->update(['total_amount' => $total]);

                return $request;
            });
        };

        $audit = function (string $event, HomecareRequest $r, string $desc, ?User $user, $at) {
            AuditLog::create([
                'user_id' => $user?->id, 'event' => $event, 'description' => $desc,
                'auditable_type' => $r->getMorphClass(), 'auditable_id' => $r->id,
                'properties' => ['code' => $r->code, 'seeder' => true],
                'created_at' => $at,
            ]);
        };

        /* 1 — SELESAI: perawatan luka diabetes ibu Budi, minggu lalu. */
        $r1 = $createRequest([
            'user_id' => $p['ibunyaBudi']->user_id,
            'patient_profile_id' => $p['ibunyaBudi']->id,
            'patient_address_id' => $p['alamatBudi']->id,
            'status' => HomecareRequestStatus::Completed,
            'complaint' => 'Luka di telapak kaki kanan (ulkus diabetikum grade 1) perlu diganti balutannya setiap 2 hari. Ibu sulit berjalan jauh ke klinik.',
            'notes' => 'Gula darah pagi tadi 210 mg/dL. Obat metformin diminum teratur.',
            'preferred_date' => now()->subDays(8)->toDateString(),
            'preferred_time_window' => TimeWindow::Morning,
            'verified_by' => $coordinator->id, 'verified_at' => now()->subDays(8)->setTime(10, 15),
            'screened_by' => $coordinator->id, 'screened_at' => now()->subDays(8)->setTime(10, 40),
            'screening_notes' => 'Layak homecare; luka grade 1, tidak ada tanda infeksi sistemik. Tugaskan perawat wound care.',
            'payment_status' => PaymentStatus::Paid,
            'submitted_at' => now()->subDays(8)->setTime(9, 30),
            'completed_at' => now()->subDays(6)->setTime(10, 20),
        ], [
            ['MED-12', 3, 'Rencana 3× kunjungan ganti balutan'],
        ]);

        $a1 = Appointment::create([
            'homecare_request_id' => $r1->id,
            'status' => AppointmentStatus::Completed,
            'scheduled_at' => now()->subDays(6)->setTime(9, 0),
            'estimated_duration_minutes' => 45,
            'address_snapshot' => ['one_line' => $p['alamatBudi']->oneLine(), 'recipient_name' => $p['alamatBudi']->recipient_name, 'phone' => $p['alamatBudi']->phone],
            'checkin_at' => now()->subDays(6)->setTime(9, 5),
            'checkout_at' => now()->subDays(6)->setTime(9, 52),
            'notes' => 'Bawa set ganti balutan steril + NaCl 0,9%.',
        ]);
        $a1->assignments()->create([
            'healthcare_staff_id' => $staff['nsSiti']->id,
            'status' => StaffAssignmentStatus::Completed,
            'role' => 'primary',
            'assigned_by' => $coordinator->id,
            'assigned_at' => now()->subDays(7)->setTime(14, 0),
        ]);
        Assessment::create([
            'appointment_id' => $a1->id,
            'healthcare_staff_id' => $staff['nsSiti']->id,
            'systolic_bp' => 145, 'diastolic_bp' => 90, 'pulse' => 82, 'respiratory_rate' => 18,
            'temperature_c' => 36.6, 'oxygen_saturation' => 98, 'consciousness' => 'compos_mentis',
            'pain_scale' => 3, 'weight_kg' => 62.5,
            'findings' => 'Ulkus diameter ±2 cm di plantar kaki kanan, dasar luka merah granulasi baik, tidak ada pus, eksudat minimal. Kemerahan sekitar luka berkurang dibanding kunjungan sebelumnya.',
            'assessed_at' => now()->subDays(6)->setTime(9, 15),
        ]);
        ServiceRecord::create([
            'appointment_id' => $a1->id,
            'healthcare_staff_id' => $staff['nsSiti']->id,
            'actions_taken' => 'Pembersihan luka dengan NaCl 0,9%, nekrotik minimal dibuang (debridement ringan), balutan diganti dengan kassa steril + hydrogel, edukasi keluarga tentang menjaga balutan tetap kering.',
            'results' => 'Luka bersih, granulasi baik, pasien nyaman selama tindakan.',
            'recommendations' => 'Jaga balutan tetap kering, kontrol gula darah harian, gunakan alas kaki khusus di dalam rumah. Ganti balutan berikutnya 2 hari lagi.',
            'follow_up_needed' => true,
            'follow_up_notes' => 'Lanjutkan seri ganti balutan (2 kunjungan lagi sudah dipesan keluarga).',
            'started_at' => now()->subDays(6)->setTime(9, 10),
            'ended_at' => now()->subDays(6)->setTime(9, 50),
        ]);
        Payment::create([
            'homecare_request_id' => $r1->id,
            'method' => PaymentMethod::Cash,
            'status' => PaymentStatus::Paid,
            'amount' => $r1->total_amount,
            'paid_at' => now()->subDays(6)->setTime(10, 0),
            'received_by' => $coordinator->id,
            'notes' => 'Diterima tunai dari keluarga saat kunjungan.',
        ]);
        $audit('SUBMITTED_REQUEST', $r1, 'Pengajuan homecare dikirim', null, now()->subDays(8)->setTime(9, 30));
        $audit('APPROVED_REQUEST', $r1, 'Pengajuan disetujui setelah skrining', $coordinator, now()->subDays(8)->setTime(10, 40));
        $audit('SCHEDULED_VISIT', $r1, 'Jadwal kunjungan diterbitkan', $coordinator, now()->subDays(7)->setTime(14, 0));
        $audit('COMPLETED_VISIT', $r1, 'Kunjungan selesai (check-out)', $staff['nsSiti']->user, now()->subDays(6)->setTime(9, 52));
        $audit('RECORDED_PAYMENT', $r1, 'Pembayaran tunai dicatat (lunas)', $coordinator, now()->subDays(6)->setTime(10, 0));

        /* 2 — SEDANG BERLANGSUNG: fisioterapi ayah Sari, hari ini. */
        $r2 = $createRequest([
            'user_id' => $p['ayahnyaSari']->user_id,
            'patient_profile_id' => $p['ayahnyaSari']->id,
            'patient_address_id' => $p['alamatSari']->id,
            'status' => HomecareRequestStatus::InProgress,
            'complaint' => 'Ayah saya pasca-stroke 2 bulan lalu, sisi kiri tubuh masih lemah. Butuh fisioterapi rutin agar bisa berjalan lagi.',
            'notes' => 'Sesi ke-4 dari rencana 8 sesi. Progres sudah bisa berdiri berpegangan.',
            'preferred_date' => now()->toDateString(),
            'preferred_time_window' => TimeWindow::Morning,
            'verified_by' => $coordinator->id, 'verified_at' => now()->subDays(5)->setTime(11, 0),
            'screened_by' => $coordinator->id, 'screened_at' => now()->subDays(5)->setTime(11, 20),
            'screening_notes' => 'Kondisi stabil, layak fisioterapi di rumah. Teruskan fisioterapis yang sama untuk kontinuitas.',
            'payment_status' => PaymentStatus::Paid,
            'submitted_at' => now()->subDays(5)->setTime(10, 0),
        ], [
            ['REH-12', 1, 'Sesi ke-4 (lanjutan)'],
        ]);

        $a2 = Appointment::create([
            'homecare_request_id' => $r2->id,
            'status' => AppointmentStatus::InService,
            'scheduled_at' => now()->setTime(9, 0),
            'estimated_duration_minutes' => 60,
            'address_snapshot' => ['one_line' => $p['alamatSari']->oneLine(), 'recipient_name' => $p['alamatSari']->recipient_name, 'phone' => $p['alamatSari']->phone],
            'checkin_at' => now()->setTime(9, 5),
        ]);
        $a2->assignments()->create([
            'healthcare_staff_id' => $staff['ftRina']->id,
            'status' => StaffAssignmentStatus::Active,
            'role' => 'primary',
            'assigned_by' => $coordinator->id,
            'assigned_at' => now()->subDays(2)->setTime(13, 0),
        ]);
        Assessment::create([
            'appointment_id' => $a2->id,
            'healthcare_staff_id' => $staff['ftRina']->id,
            'systolic_bp' => 138, 'diastolic_bp' => 85, 'pulse' => 78,
            'consciousness' => 'compos_mentis', 'pain_scale' => 2,
            'findings' => 'Kekuatan otot ekstremitas kiri atas 3/5, bawah 2+/5. Keseimbangan duduk baik, berdiri dengan pegangan ±2 menit.',
            'assessed_at' => now()->setTime(9, 10),
        ]);
        Payment::create([
            'homecare_request_id' => $r2->id,
            'method' => PaymentMethod::Transfer,
            'status' => PaymentStatus::Paid,
            'amount' => $r2->total_amount,
            'reference_number' => 'TRF-'.now()->format('ymd').'-0042',
            'paid_at' => now()->subDay()->setTime(16, 30),
            'received_by' => $coordinator->id,
            'notes' => 'Transfer bank, sudah diverifikasi.',
        ]);
        $audit('SUBMITTED_REQUEST', $r2, 'Pengajuan homecare dikirim', null, now()->subDays(5)->setTime(10, 0));
        $audit('APPROVED_REQUEST', $r2, 'Pengajuan disetujui setelah skrining', $coordinator, now()->subDays(5)->setTime(11, 20));
        $audit('CHECKED_IN_VISIT', $r2, 'Petugas check-in di lokasi', $staff['ftRina']->user, now()->setTime(9, 5));

        /* 3 — TERJADWAL: kunjungan dokter + ambil lab untuk ibu Budi, besok. */
        $r3 = $createRequest([
            'user_id' => $p['ibunyaBudi']->user_id,
            'patient_profile_id' => $p['ibunyaBudi']->id,
            'patient_address_id' => $p['alamatBudi']->id,
            'status' => HomecareRequestStatus::Scheduled,
            'complaint' => 'Ibu perlu kontrol rutin diabetes & hipertensi bulanan, sekalian periksa darah lengkap (HbA1c, profil lipid).',
            'preferred_date' => now()->addDay()->toDateString(),
            'preferred_time_window' => TimeWindow::Morning,
            'verified_by' => $coordinator->id, 'verified_at' => now()->subDay()->setTime(9, 0),
            'screened_by' => $coordinator->id, 'screened_at' => now()->subDay()->setTime(9, 20),
            'screening_notes' => 'Kontrol rutin, tidak ada keluhan akut. Dokter umum + ATLM.',
            'payment_status' => PaymentStatus::Unpaid,
            'submitted_at' => now()->subDay()->setTime(8, 15),
        ], [
            ['DOK-01', 1, null],
            ['MED-18', 1, 'Puasa 8 jam sebelum pengambilan'],
        ]);

        $a3 = Appointment::create([
            'homecare_request_id' => $r3->id,
            'status' => AppointmentStatus::Scheduled,
            'scheduled_at' => now()->addDay()->setTime(10, 0),
            'estimated_duration_minutes' => 75,
            'address_snapshot' => ['one_line' => $p['alamatBudi']->oneLine(), 'recipient_name' => $p['alamatBudi']->recipient_name, 'phone' => $p['alamatBudi']->phone],
            'notes' => 'Pasien puasa sejak pukul 02.00 untuk pemeriksaan lipid. Bawa form lab.',
        ]);
        $a3->assignments()->create([
            'healthcare_staff_id' => $staff['drAndini']->id,
            'status' => StaffAssignmentStatus::Assigned,
            'role' => 'primary',
            'assigned_by' => $coordinator->id,
            'assigned_at' => now()->subDay()->setTime(10, 0),
        ]);
        $a3->assignments()->create([
            'healthcare_staff_id' => $staff['nsBagus']->id,
            'status' => StaffAssignmentStatus::Assigned,
            'role' => 'support',
            'assigned_by' => $coordinator->id,
            'assigned_at' => now()->subDay()->setTime(10, 0),
        ]);
        $audit('SUBMITTED_REQUEST', $r3, 'Pengajuan homecare dikirim', null, now()->subDay()->setTime(8, 15));
        $audit('APPROVED_REQUEST', $r3, 'Pengajuan disetujui setelah skrining', $coordinator, now()->subDay()->setTime(9, 20));
        $audit('SCHEDULED_VISIT', $r3, 'Jadwal kunjungan diterbitkan', $coordinator, now()->subDay()->setTime(10, 0));

        /* 4 — DISETUJUI (siap dijadwalkan): latihan koordinasi pasca stroke ayah Sari. */
        $r4 = $createRequest([
            'user_id' => $p['ayahnyaSari']->user_id,
            'patient_profile_id' => $p['ayahnyaSari']->id,
            'patient_address_id' => $p['alamatSari']->id,
            'status' => HomecareRequestStatus::Approved,
            'complaint' => 'Bicara ayah masih pelo dan kadang sulit menelan sejak stroke. Mohon latihan koordinasi khusus pasca stroke.',
            'preferred_date' => now()->addDays(3)->toDateString(),
            'preferred_time_window' => TimeWindow::Midday,
            'verified_by' => $coordinator->id, 'verified_at' => now()->subDay()->setTime(13, 0),
            'screened_by' => $coordinator->id, 'screened_at' => now()->subDay()->setTime(13, 30),
            'screening_notes' => 'Disartria pasca-stroke + disfagia ringan. Jadwalkan latihan koordinasi khusus (NDT), digabung dengan sesi fisioterapi hari yang sama bila memungkinkan.',
            'payment_status' => PaymentStatus::Unpaid,
            'submitted_at' => now()->subDays(2)->setTime(19, 40),
        ], [
            ['REH-16', 1, null],
        ]);
        $audit('SUBMITTED_REQUEST', $r4, 'Pengajuan homecare dikirim', null, now()->subDays(2)->setTime(19, 40));
        $audit('APPROVED_REQUEST', $r4, 'Pengajuan disetujui setelah skrining', $coordinator, now()->subDay()->setTime(13, 30));

        /* 5 — MENUNGGU VERIFIKASI: cek gula darah untuk Budi sendiri. */
        $r5 = $createRequest([
            'user_id' => $p['budiSelf']->user_id,
            'patient_profile_id' => $p['budiSelf']->id,
            'patient_address_id' => $p['alamatBudi']->id,
            'status' => HomecareRequestStatus::Submitted,
            'complaint' => 'Saya ingin cek gula darah untuk program diet diabetes (pencegahan) karena ada riwayat keluarga, sekalian memantau kondisi ibu di rumah.',
            'preferred_date' => now()->addDays(4)->toDateString(),
            'preferred_time_window' => TimeWindow::Afternoon,
            'payment_status' => PaymentStatus::Unpaid,
            'submitted_at' => now()->setTime(7, 45),
        ], [
            ['LAB-01', 1, null],
        ]);
        $audit('SUBMITTED_REQUEST', $r5, 'Pengajuan homecare dikirim', null, now()->setTime(7, 45));

        /* 6 — PERLU INFORMASI: injeksi insulin untuk ibu Budi. */
        $r6 = $createRequest([
            'user_id' => $p['ibunyaBudi']->user_id,
            'patient_profile_id' => $p['ibunyaBudi']->id,
            'patient_address_id' => $p['alamatBudi']->id,
            'status' => HomecareRequestStatus::NeedInformation,
            'complaint' => 'Ibu butuh suntik insulin setiap malam, keluarga belum berani menyuntik sendiri. Mohon dibantu perawat.',
            'preferred_date' => now()->addDays(2)->toDateString(),
            'preferred_time_window' => TimeWindow::Afternoon,
            'verified_by' => $coordinator->id, 'verified_at' => now()->subDays(2)->setTime(10, 0),
            'information_request' => 'Mohon kirimkan foto resep insulin terbaru (jenis & dosis), serta foto alat suntik/pen yang digunakan. Apakah ada riwayat hipoglikemia (gula darah drop) sebelumnya?',
            'payment_status' => PaymentStatus::Unpaid,
            'submitted_at' => now()->subDays(2)->setTime(9, 10),
        ], [
            ['MED-08', 5, 'Rencana 5 malam berturut-turut'],
        ]);
        $audit('SUBMITTED_REQUEST', $r6, 'Pengajuan homecare dikirim', null, now()->subDays(2)->setTime(9, 10));
        $audit('REQUESTED_INFORMATION', $r6, 'Koordinator meminta informasi tambahan', $coordinator, now()->subDays(2)->setTime(10, 30));

        /* 7 — DALAM REVIEW: perawatan kateter ayah Sari. */
        $r7 = $createRequest([
            'user_id' => $p['ayahnyaSari']->user_id,
            'patient_profile_id' => $p['ayahnyaSari']->id,
            'patient_address_id' => $p['alamatSari']->id,
            'status' => HomecareRequestStatus::UnderReview,
            'complaint' => 'Ayah terpasang kateter sejak keluar RS, sudah 12 hari. Perlu diganti dan dibersihkan.',
            'preferred_date' => now()->addDays(2)->toDateString(),
            'preferred_time_window' => TimeWindow::Morning,
            'verified_by' => $coordinator->id, 'verified_at' => now()->subDay()->setTime(15, 0),
            'payment_status' => PaymentStatus::Unpaid,
            'submitted_at' => now()->subDay()->setTime(14, 20),
        ], [
            ['MED-01', 1, null],
        ]);
        $audit('SUBMITTED_REQUEST', $r7, 'Pengajuan homecare dikirim', null, now()->subDay()->setTime(14, 20));
        $audit('STARTED_REVIEW', $r7, 'Verifikasi dimulai', $coordinator, now()->subDay()->setTime(15, 0));

        /* 8 — DITOLAK: permintaan yang di luar cakupan layanan. */
        $r8 = $createRequest([
            'user_id' => $p['budiSelf']->user_id,
            'patient_profile_id' => $p['budiSelf']->id,
            'patient_address_id' => $p['alamatBudi']->id,
            'status' => HomecareRequestStatus::Rejected,
            'complaint' => 'Minta infus whitening/vitamin C untuk kecantikan di rumah.',
            'verified_by' => $coordinator->id, 'verified_at' => now()->subDays(3)->setTime(9, 30),
            'screened_by' => $coordinator->id, 'screened_at' => now()->subDays(3)->setTime(10, 0),
            'rejected_reason' => 'Mohon maaf, layanan infus untuk tujuan estetika tidak termasuk cakupan homecare medis rumah sakit kami dan berisiko tanpa indikasi medis. Silakan konsultasikan ke dokter untuk kebutuhan Anda.',
            'payment_status' => PaymentStatus::Unpaid,
            'submitted_at' => now()->subDays(3)->setTime(8, 50),
        ], [
            ['MED-07', 1, 'Permintaan infus vitamin'],
        ]);
        $audit('SUBMITTED_REQUEST', $r8, 'Pengajuan homecare dikirim', null, now()->subDays(3)->setTime(8, 50));
        $audit('REJECTED_REQUEST', $r8, 'Pengajuan ditolak setelah skrining', $coordinator, now()->subDays(3)->setTime(10, 0));

        /* 9 — DIBATALKAN pasien: jadwal tidak cocok. */
        $r9 = $createRequest([
            'user_id' => $p['ibunyaBudi']->user_id,
            'patient_profile_id' => $p['ibunyaBudi']->id,
            'patient_address_id' => $p['alamatBudi']->id,
            'status' => HomecareRequestStatus::Cancelled,
            'complaint' => 'Butuh latihan perawatan diri (ADL) harian selama seminggu untuk ibu.',
            'preferred_date' => now()->subDays(2)->toDateString(),
            'preferred_time_window' => TimeWindow::Morning,
            'verified_by' => $coordinator->id, 'verified_at' => now()->subDays(5)->setTime(9, 0),
            'payment_status' => PaymentStatus::Unpaid,
            'submitted_at' => now()->subDays(5)->setTime(8, 0),
            'cancelled_at' => now()->subDays(4)->setTime(12, 0),
            'cancellation_reason' => 'Kakak saya bisa cuti minggu ini dan akan menemani ibu langsung, jadi latihan harian dibatalkan. Terima kasih.',
            'cancelled_by' => $ctx['budi']->id,
        ], [
            ['REH-25', 5, 'Paket 5 hari'],
        ]);
        $audit('SUBMITTED_REQUEST', $r9, 'Pengajuan homecare dikirim', null, now()->subDays(5)->setTime(8, 0));
        $audit('CANCELLED_REQUEST', $r9, 'Pengajuan dibatalkan oleh pasien', $ctx['budi'], now()->subDays(4)->setTime(12, 0));

        /* Notifikasi database untuk keluarga pasien (demo dashboard). */
        $this->seedNotifications($ctx);
    }

    private function seedNotifications(array $ctx): void
    {
        $p = $ctx['patients'];
        $requests = HomecareRequest::orderBy('id')->get();

        $r1 = $requests[0];
        $r2 = $requests[1];
        $r3 = $requests[2];
        $r6 = $requests[5];

        $insert = function (User $user, string $type, string $title, string $message, string $url, $createdAt) {
            DB::table('notifications')->insert([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'type' => \App\Notifications\RequestStatusChanged::class,
                'notifiable_type' => $user->getMorphClass(),
                'notifiable_id' => $user->id,
                'data' => json_encode([
                    'type' => $type, 'title' => $title, 'message' => $message, 'url' => $url,
                ]),
                'read_at' => null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        };

        $budi = $ctx['budi'];
        $sari = $ctx['sari'];

        $insert($budi, 'request_status', 'Pengajuan '.$r1->code.': Selesai',
            'Kunjungan perawatan luka telah selesai. Ringkasan pelayanan dapat dilihat di detail pengajuan.',
            '/akun/pengajuan/'.$r1->code, now()->subDays(6)->setTime(10, 0));
        $insert($budi, 'appointment_scheduled', 'Kunjungan terjadwal: '.now()->addDay()->translatedFormat('j M Y').' 10.00',
            'Pengajuan '.$r3->code.' telah mendapatkan jadwal dan petugas.',
            '/akun/pengajuan/'.$r3->code, now()->subDay()->setTime(10, 0));
        $insert($budi, 'request_needs_information', 'Perlu informasi tambahan untuk '.$r6->code,
            'Mohon kirimkan foto resep insulin terbaru (jenis & dosis), serta foto alat suntik/pen yang digunakan.',
            '/akun/pengajuan/'.$r6->code, now()->subDays(2)->setTime(10, 30));
        $insert($sari, 'request_status', 'Pengajuan '.$r2->code.': Sedang Dilayani',
            'Fisioterapis sedang memberikan pelayanan di lokasi.',
            '/akun/pengajuan/'.$r2->code, now()->setTime(9, 10));

    }
}
