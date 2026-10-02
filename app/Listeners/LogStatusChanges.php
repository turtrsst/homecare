<?php

namespace App\Listeners;

use App\Events\AppointmentStatusChanged;
use App\Events\HomecareRequestStatusChanged;
use App\Services\AuditService;
use Illuminate\Events\Attribute\Listens;

/**
 * Semua perubahan status penting tercatat di audit trail — terpusat di
 * listener agar Actions tetap fokus pada transisi state.
 */
class LogStatusChanges
{
    public function __construct(private readonly AuditService $audit) {}

    #[Listens(HomecareRequestStatusChanged::class)]
    public function onRequestStatusChanged(HomecareRequestStatusChanged $event): void
    {
        $this->audit->log(
            'REQUEST_STATUS_'.strtoupper($event->to->value),
            $event->request,
            sprintf(
                'Status pengajuan %s: %s → %s',
                $event->request->code,
                $event->from->label(),
                $event->to->label()
            ),
            [
                'from' => $event->from->value,
                'to' => $event->to->value,
                ...$event->meta,
            ],
            $event->actor,
        );
    }

    #[Listens(AppointmentStatusChanged::class)]
    public function onAppointmentStatusChanged(AppointmentStatusChanged $event): void
    {
        $this->audit->log(
            'APPOINTMENT_STATUS_'.strtoupper($event->to->value),
            $event->appointment->request,
            sprintf(
                'Kunjungan pengajuan %s: %s → %s',
                $event->appointment->request->code,
                $event->from->label(),
                $event->to->label()
            ),
            [
                'appointment_id' => $event->appointment->id,
                'from' => $event->from->value,
                'to' => $event->to->value,
            ],
            $event->actor,
        );
    }
}
