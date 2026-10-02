<?php

namespace App\Http\Requests\Booking;

class StepPatientRequest extends BookingStepRequest
{
    public function rules(): array
    {
        return [
            'patient_profile_id' => [
                'required', 'integer',
                \Illuminate\Validation\Rule::exists('patient_profiles', 'id')
                    ->where('user_id', $this->user()->id),
            ],
        ];
    }

    public function attributes(): array
    {
        return ['patient_profile_id' => 'Pasien'];
    }

    public function messages(): array
    {
        return ['patient_profile_id.required' => 'Pilih pasien yang akan dilayani.'];
    }
}
