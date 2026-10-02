<?php

namespace App\Http\Requests\Booking;

use Illuminate\Validation\Rule;

class StepAddressRequest extends BookingStepRequest
{
    public function rules(): array
    {
        $patientId = $this->session()->get('booking.patient_profile_id');

        return [
            'patient_address_id' => [
                'required', 'integer',
                Rule::exists('patient_addresses', 'id')
                    ->where('patient_profile_id', $patientId),
            ],
        ];
    }

    public function attributes(): array
    {
        return ['patient_address_id' => 'Alamat kunjungan'];
    }

    public function messages(): array
    {
        return [
            'patient_address_id.required' => 'Pilih alamat tempat kunjungan dilakukan.',
            'patient_address_id.exists' => 'Alamat bukan milik pasien yang dipilih.',
        ];
    }
}
