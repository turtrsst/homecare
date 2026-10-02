<?php

namespace App\Actions;

use App\Enums\AppointmentStatus;
use App\Events\AppointmentStatusChanged;
use App\Models\Appointment;
use App\Models\Assessment;
use App\Models\HealthcareStaff;
use App\Models\ServiceRecord;
use App\Models\StaffAssignment;
use App\Models\User;
use App\Services\AuditService;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Aksi-aksi pelaksanaan kunjungan oleh petugas homecare:
 * berangkat → check-in → mulai pelayanan → (asesmen) → dokumentasi → check-out.
 *
 * Semua aksi memverifikasi bahwa petugas memang ter-assign pada kunjungan.
 */
class VisitActions
{
    public function __construct(private readonly AuditService $audit) {}

    /**
     * Petugas menekan "Konfirmasi Tugas" — ia sudah membaca detail kunjungan dan
     * siap melaksanakannya. Tidak mengubah status apa pun; hanya mencatat
     * `confirmed_at` agar penjadwal tahu tugasnya sudah dibaca.
     */
    public function confirm(Appointment $appointment, HealthcareStaff $staff, User $actor): StaffAssignment
    {
        $assignment = $this->assignmentOf($appointment, $staff);

        if ($assignment->confirmed_at === null) {
            $assignment->update(['confirmed_at' => now()]);

            $this->audit->log('CONFIRMED_TASK', $appointment->request, 'Petugas mengonfirmasi tugas', [
                'appointment_id' => $appointment->id,
                'healthcare_staff_id' => $staff->id,
            ], $actor);
        }

        return $assignment;
    }

    /** Petugas berangkat menuju lokasi. */
    public function depart(Appointment $appointment, HealthcareStaff $staff, User $actor): Appointment
    {
        $this->assertAssigned($appointment, $staff);

        return $this->transition($appointment, AppointmentStatus::OnTheWay, $actor, 'DEPARTED_TO_VISIT', 'Petugas berangkat ke lokasi');
    }

    /** Petugas tiba / check-in di lokasi. */
    public function checkIn(Appointment $appointment, HealthcareStaff $staff, User $actor, ?string $notes = null): Appointment
    {
        $this->assertAssigned($appointment, $staff);

        $appointment->update(['checkin_at' => now()]);

        return $this->transition($appointment, AppointmentStatus::CheckedIn, $actor, 'CHECKED_IN_VISIT', 'Petugas check-in di lokasi', ['notes' => $notes]);
    }

    /** Pelayanan mulai dilaksanakan. */
    public function startService(Appointment $appointment, HealthcareStaff $staff, User $actor): Appointment
    {
        $this->assertAssigned($appointment, $staff);

        return $this->transition($appointment, AppointmentStatus::InService, $actor, 'STARTED_SERVICE', 'Pelayanan dimulai');
    }

    /** Simpan asesmen pasien (tanda vital dll). */
    public function saveAssessment(Appointment $appointment, HealthcareStaff $staff, array $data, User $actor): Assessment
    {
        $this->assertAssigned($appointment, $staff);

        $assessment = $appointment->assessments()->create([
            'healthcare_staff_id' => $staff->id,
            'assessed_at' => $data['assessed_at'] ?? now(),
            ...collect($data)->except(['assessed_at'])->all(),
        ]);

        $this->audit->log('SAVED_ASSESSMENT', $appointment->request, 'Asesmen pasien dicatat', [
            'appointment_id' => $appointment->id,
            'assessment_id' => $assessment->id,
        ], $actor);

        return $assessment;
    }

    /**
     * Check-out: simpan dokumentasi pelayanan (service record) dan selesaikan
     * kunjungan. Request induk otomatis menjadi SELESAI lewat listener.
     */
    public function complete(Appointment $appointment, HealthcareStaff $staff, array $recordData, User $actor): Appointment
    {
        $this->assertAssigned($appointment, $staff);

        return DB::transaction(function () use ($appointment, $staff, $recordData, $actor) {
            $record = ServiceRecord::updateOrCreate(
                ['appointment_id' => $appointment->id, 'healthcare_staff_id' => $staff->id],
                [
                    'actions_taken' => $recordData['actions_taken'],
                    'results' => $recordData['results'] ?? null,
                    'recommendations' => $recordData['recommendations'] ?? null,
                    'follow_up_needed' => (bool) ($recordData['follow_up_needed'] ?? false),
                    'follow_up_notes' => $recordData['follow_up_notes'] ?? null,
                    'started_at' => $appointment->checkin_at ?? now(),
                    'ended_at' => now(),
                ],
            );

            $this->audit->log('SAVED_SERVICE_RECORD', $appointment->request, 'Dokumentasi pelayanan disimpan', [
                'appointment_id' => $appointment->id,
                'service_record_id' => $record->id,
            ], $actor);

            // Bila petugas check-out tanpa sempat menekan "mulai pelayanan".
            if ($appointment->status === AppointmentStatus::CheckedIn) {
                $this->transition($appointment, AppointmentStatus::InService, $actor, 'STARTED_SERVICE', 'Pelayanan dimulai (otomatis saat check-out)');
            }

            $appointment->update(['checkout_at' => now()]);

            return $this->transition($appointment, AppointmentStatus::Completed, $actor, 'COMPLETED_VISIT', 'Kunjungan selesai (check-out)');
        });
    }

    private function assertAssigned(Appointment $appointment, HealthcareStaff $staff): void
    {
        $this->assignmentOf($appointment, $staff);
    }

    private function assignmentOf(Appointment $appointment, HealthcareStaff $staff): StaffAssignment
    {
        $assignment = $appointment->assignments()
            ->where('healthcare_staff_id', $staff->id)
            ->whereIn('status', ['assigned', 'active'])
            ->first();

        if ($assignment === null) {
            throw new DomainException('Anda tidak ditugaskan pada kunjungan ini.');
        }

        return $assignment;
    }

    private function transition(
        Appointment $appointment,
        AppointmentStatus $target,
        User $actor,
        string $auditEvent,
        string $auditDescription,
        array $meta = [],
    ): Appointment {
        $from = $appointment->status;

        if ($from === $target) {
            return $appointment;
        }

        if (! $from->canTransitionTo($target)) {
            throw new DomainException("Langkah «{$from->label()}» → «{$target->label()}» tidak diizinkan.");
        }

        $appointment->update(['status' => $target]);

        AppointmentStatusChanged::dispatch($appointment->refresh(), $from, $target, $actor);

        $this->audit->log($auditEvent, $appointment->request, $auditDescription, [
            'appointment_id' => $appointment->id,
            ...$meta,
        ], $actor);

        return $appointment;
    }
}
