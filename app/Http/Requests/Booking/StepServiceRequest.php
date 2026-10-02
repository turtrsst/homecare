<?php

namespace App\Http\Requests\Booking;

use Illuminate\Validation\Rule;

class StepServiceRequest extends BookingStepRequest
{
    public function rules(): array
    {
        return [
            'service_ids' => ['required', 'array', 'min:1', 'max:5'],
            'service_ids.*' => [
                'integer',
                Rule::exists('homecare_services', 'id')->where('is_active', true)->whereNull('deleted_at'),
            ],
        ];
    }

    public function attributes(): array
    {
        return ['service_ids' => 'Layanan'];
    }

    public function messages(): array
    {
        return [
            'service_ids.required' => 'Pilih minimal satu layanan yang dibutuhkan.',
            'service_ids.min' => 'Pilih minimal satu layanan yang dibutuhkan.',
        ];
    }
}
