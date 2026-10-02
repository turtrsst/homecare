<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'nik' => $this->nik,
            'gender' => $this->gender?->value,
            'gender_label' => $this->gender?->label(),
            'birth_date' => $this->birth_date?->toDateString(),
            'relationship' => $this->relationship,
            'medical_notes' => $this->medical_notes,
            'contacts' => $this->whenLoaded('contacts', fn () => $this->contacts->map(fn ($c) => [
                'id' => $c->id,
                'type' => $c->type->value,
                'value' => $c->value,
                'label' => $c->label,
                'is_primary' => $c->is_primary,
            ])),
            'addresses' => AddressResource::collection($this->whenLoaded('addresses')),
        ];
    }
}
