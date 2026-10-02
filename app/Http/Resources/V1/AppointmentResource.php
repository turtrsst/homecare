<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'patient_label' => $this->status->patientLabel(),
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'scheduled_at_formatted' => $this->scheduled_at?->translatedFormat('l, j F Y H.i'),
            'estimated_duration_minutes' => $this->estimated_duration_minutes,
            'address' => $this->address_snapshot,
            'checkin_at' => $this->checkin_at?->toIso8601String(),
            'checkout_at' => $this->checkout_at?->toIso8601String(),
            'notes' => $this->notes,
            'staff' => $this->whenLoaded('assignments', fn () => $this->assignments
                ->whereNotIn('status', ['cancelled'])
                ->map(fn ($a) => [
                    'name' => $a->staff->name,
                    'profession' => $a->staff->profession->value,
                    'profession_label' => $a->staff->profession->label(),
                    'role' => $a->role,
                    'status' => $a->status->value,
                ])),
        ];
    }
}
