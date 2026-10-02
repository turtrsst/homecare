<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomecareRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'patient_label' => $this->status->patientLabel(),
            'payment_status' => $this->payment_status->value,
            'payment_status_label' => $this->payment_status->label(),
            'complaint' => $this->complaint,
            'notes' => $this->notes,
            'preferred_date' => $this->preferred_date?->toDateString(),
            'preferred_time_window' => $this->preferred_time_window?->value,
            'preferred_time_window_label' => $this->preferred_time_window?->label(),
            'total_amount' => (float) $this->total_amount,
            'total_formatted' => $this->formattedTotal(),
            'information_request' => $this->information_request,
            'rejected_reason' => $this->rejected_reason,
            'cancellation_reason' => $this->cancellation_reason,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'service_name' => $item->service_name,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'subtotal' => (float) $item->subtotal,
                'notes' => $item->notes,
            ])),
            'patient' => new PatientResource($this->whenLoaded('patient')),
            'address' => new AddressResource($this->whenLoaded('address')),
            'appointment' => new AppointmentResource($this->whenLoaded('appointment')),
        ];
    }
}
