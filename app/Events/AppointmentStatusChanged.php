<?php

namespace App\Events;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Appointment $appointment,
        public readonly AppointmentStatus $from,
        public readonly AppointmentStatus $to,
        public readonly ?User $actor = null,
    ) {}
}
