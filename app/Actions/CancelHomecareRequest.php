<?php

namespace App\Actions;

use App\Enums\AppointmentStatus;
use App\Enums\HomecareRequestStatus;
use App\Models\HomecareRequest;
use App\Models\User;
use App\Services\RequestStatusManager;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Pembatalan pengajuan — oleh pasien (status awal) atau petugas (dengan alasan).
 */
class CancelHomecareRequest
{
    public function __construct(private readonly RequestStatusManager $status) {}

    public function byPatient(HomecareRequest $request, ?string $reason, User $patient): HomecareRequest
    {
        if ($request->user_id !== $patient->id) {
            throw new DomainException('Pengajuan ini bukan milik Anda.');
        }

        if (! $request->status->cancellableByPatient()) {
            throw new DomainException('Pengajuan dengan status «'.$request->status->label().'» tidak dapat dibatalkan sendiri. Silakan hubungi kami.');
        }

        return $this->cancel($request, $reason ?: 'Dibatalkan oleh pasien/keluarga.', $patient);
    }

    public function byStaff(HomecareRequest $request, string $reason, User $staff): HomecareRequest
    {
        if ($request->status->isFinal()) {
            throw new DomainException('Pengajuan sudah berstatus akhir dan tidak dapat dibatalkan.');
        }

        return $this->cancel($request, $reason, $staff);
    }

    private function cancel(HomecareRequest $request, string $reason, User $actor): HomecareRequest
    {
        return DB::transaction(function () use ($request, $reason, $actor) {
            $appointment = $request->appointment;

            if ($appointment && ! $appointment->status->isFinal()) {
                $appointment->update(['status' => AppointmentStatus::Cancelled]);
            }

            $request->update([
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'cancelled_by' => $actor->id,
            ]);

            return $this->status->transitionTo($request, HomecareRequestStatus::Cancelled, $actor, [
                'reason' => $reason,
            ]);
        });
    }
}
