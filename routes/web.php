<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Operational;
use App\Http\Controllers\Patient;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\Staff;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIC — masyarakat umum
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('/layanan', [PublicPageController::class, 'services'])->name('services.index');
Route::get('/layanan/{service:slug}', [PublicPageController::class, 'serviceDetail'])->name('services.show');
Route::get('/cara-kerja', [PublicPageController::class, 'howItWorks'])->name('how-it-works');
Route::get('/faq', [PublicPageController::class, 'faq'])->name('faq');
Route::get('/kontak', [PublicPageController::class, 'contact'])->name('contact');

/*
|--------------------------------------------------------------------------
| AUTH — masuk / daftar
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/masuk', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:6,1');
    Route::get('/daftar', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisteredUserController::class, 'store'])->middleware('throttle:6,1');
});

Route::post('/keluar', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| PATIENT AREA — /akun
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('akun')->name('akun.')->group(function () {
    Route::get('/', [Patient\DashboardController::class, 'index'])->name('dashboard');

    // Wizard booking (multi-step)
    Route::controller(Patient\BookingWizardController::class)
        ->prefix('pesan')
        ->name('pesan.')
        ->group(function () {
            Route::get('/{step?}', 'show')
                ->whereIn('step', Patient\BookingWizardController::STEPS)
                ->name('step');
            Route::post('/{step}', 'save')
                ->whereIn('step', Patient\BookingWizardController::STEPS)
                ->name('save');
            Route::post('/konfirmasi/kirim', 'submit')->name('submit');
            Route::post('/reset', 'reset')->name('reset');
            Route::post('/lampiran/{index}', 'removeDraft')->whereNumber('index')->name('lampiran.hapus');
        });

    // Pengajuan & riwayat
    Route::get('/pengajuan', [Patient\RequestController::class, 'index'])->name('pengajuan.index');
    Route::get('/pengajuan/{request:code}', [Patient\RequestController::class, 'show'])->name('pengajuan.show');
    Route::post('/pengajuan/{request:code}/batal', [Patient\RequestController::class, 'cancel'])->name('pengajuan.batal');
    Route::post('/pengajuan/{request:code}/informasi', [Patient\RequestController::class, 'provideInformation'])->name('pengajuan.informasi');
    Route::post('/pengajuan/{request:code}/ulasan', [Patient\RequestController::class, 'storeReview'])->name('pengajuan.ulasan');

    // Pasien (profil) & alamat
    Route::resource('/pasien', Patient\PatientProfileController::class)
        ->except(['show'])
        ->parameters(['pasien' => 'patient']);
    Route::resource('pasien.alamat', Patient\PatientAddressController::class)
        ->except(['show'])
        ->parameters(['pasien' => 'patient', 'alamat' => 'address']);

    // Profil akun
    Route::get('/profil', [Patient\ProfileController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [Patient\ProfileController::class, 'update'])->name('profil.update');

    // Notifikasi in-app
    Route::get('/notifikasi', [Patient\NotificationController::class, 'index'])->name('notifikasi.index');
    Route::post('/notifikasi/{notification}/baca', [Patient\NotificationController::class, 'markRead'])->name('notifikasi.baca');
    Route::post('/notifikasi/baca-semua', [Patient\NotificationController::class, 'markAllRead'])->name('notifikasi.baca-semua');
});

/*
|--------------------------------------------------------------------------
| ATTACHMENTS — upload privat + unduhan terotorisasi
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/lampiran/{attachment}/unduh', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::post('/lampiran', [AttachmentController::class, 'store'])->name('attachments.store');
    Route::delete('/lampiran/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');
});

/*
|--------------------------------------------------------------------------
| STAFF AREA — /tugas (tenaga kesehatan)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:medical_staff,admin,coordinator'])
    ->prefix('tugas')
    ->name('tugas.')
    ->group(function () {
        Route::get('/', [Staff\TaskController::class, 'index'])->name('index');
        Route::get('/{appointment}', [Staff\TaskController::class, 'show'])->name('show');
        Route::post('/{appointment}/konfirmasi', [Staff\TaskController::class, 'confirm'])->name('konfirmasi');
        Route::post('/{appointment}/berangkat', [Staff\TaskController::class, 'depart'])->name('berangkat');
        Route::post('/{appointment}/check-in', [Staff\TaskController::class, 'checkIn'])->name('checkin');
        Route::post('/{appointment}/mulai', [Staff\TaskController::class, 'startService'])->name('mulai');
        Route::post('/{appointment}/asesmen', [Staff\TaskController::class, 'saveAssessment'])->name('asesmen');
        Route::post('/{appointment}/selesai', [Staff\TaskController::class, 'complete'])->name('selesai');
    });

/*
|--------------------------------------------------------------------------
| OPERATIONAL AREA — /operasional (admin, koordinator, manajer)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,coordinator,manager'])
    ->prefix('operasional')
    ->name('operasional.')
    ->group(function () {
        Route::get('/', [Operational\DashboardController::class, 'index'])->name('dashboard');

        // Permintaan homecare: verifikasi, skrining, jadwal, biaya
        Route::get('/pengajuan', [Operational\RequestController::class, 'index'])->name('pengajuan.index');
        Route::get('/pengajuan/{request:code}', [Operational\RequestController::class, 'show'])->name('pengajuan.show');
        Route::post('/pengajuan/{request:code}/mulai-verifikasi', [Operational\RequestController::class, 'startReview'])->middleware('can:requests.verify')->name('pengajuan.verifikasi');
        Route::post('/pengajuan/{request:code}/minta-informasi', [Operational\RequestController::class, 'requestInformation'])->middleware('can:requests.verify')->name('pengajuan.minta-informasi');
        Route::post('/pengajuan/{request:code}/tolak', [Operational\RequestController::class, 'reject'])->middleware('can:requests.verify')->name('pengajuan.tolak');
        Route::post('/pengajuan/{request:code}/setujui', [Operational\RequestController::class, 'approve'])->middleware('can:requests.verify')->name('pengajuan.setujui');
        Route::post('/pengajuan/{request:code}/jadwalkan', [Operational\RequestController::class, 'schedule'])->middleware('can:requests.schedule')->name('pengajuan.jadwalkan');
        Route::post('/pengajuan/{request:code}/batal', [Operational\RequestController::class, 'cancel'])->middleware('can:requests.verify')->name('pengajuan.batal');
        Route::post('/pengajuan/{request:code}/pembayaran', [Operational\RequestController::class, 'recordPayment'])->middleware('can:payments.manage')->name('pengajuan.pembayaran');

        // Jadwal & monitoring kunjungan
        Route::get('/jadwal', [Operational\AppointmentController::class, 'index'])->name('jadwal.index');
        Route::get('/jadwal/{appointment}', [Operational\AppointmentController::class, 'show'])->name('jadwal.show');

        // Pasien (semua akun)
        Route::get('/pasien', [Operational\PatientController::class, 'index'])->name('pasien.index');
        Route::get('/pasien/{patient}', [Operational\PatientController::class, 'show'])->name('pasien.show');

        // Laporan
        Route::get('/laporan', [Operational\ReportController::class, 'index'])->middleware('can:reports.view')->name('laporan.index');

        // Audit trail
        Route::get('/audit', [Operational\AuditLogController::class, 'index'])->middleware('can:audit.view')->name('audit.index');

        // Master data: layanan & tarif
        Route::resource('/layanan', Operational\ServiceController::class)
            ->except(['show'])
            ->middleware('can:services.manage')
            ->parameters(['layanan' => 'service']);

        // Master data: tenaga kesehatan
        Route::resource('/petugas', Operational\StaffController::class)
            ->except(['show'])
            ->middleware('can:staff.manage')
            ->parameters(['petugas' => 'staff']);
    });
