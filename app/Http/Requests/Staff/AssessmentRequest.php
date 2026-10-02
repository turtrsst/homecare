<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class AssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('visit', $this->route('appointment'));
    }

    public function rules(): array
    {
        return [
            'systolic_bp' => ['nullable', 'integer', 'min:50', 'max:300'],
            'diastolic_bp' => ['nullable', 'integer', 'min:30', 'max:200'],
            'pulse' => ['nullable', 'integer', 'min:20', 'max:300'],
            'respiratory_rate' => ['nullable', 'integer', 'min:5', 'max:80'],
            'temperature_c' => ['nullable', 'numeric', 'min:30', 'max:45'],
            'oxygen_saturation' => ['nullable', 'integer', 'min:50', 'max:100'],
            'consciousness' => ['nullable', 'string', 'max:32'],
            'pain_scale' => ['nullable', 'integer', 'min:0', 'max:10'],
            'weight_kg' => ['nullable', 'numeric', 'min:1', 'max:400'],
            'height_cm' => ['nullable', 'numeric', 'min:20', 'max:260'],
            'findings' => ['nullable', 'string', 'max:3000'],
            'patient_designated_staff' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'systolic_bp' => 'Tekanan darah sistole',
            'diastolic_bp' => 'Tekanan darah diastole',
            'pulse' => 'Nadi',
            'respiratory_rate' => 'Frekuensi napas',
            'temperature_c' => 'Suhu tubuh (°C)',
            'oxygen_saturation' => 'Saturasi oksigen (SpO2)',
            'consciousness' => 'Kesadaran',
            'pain_scale' => 'Skala nyeri (0-10)',
            'weight_kg' => 'Berat badan (kg)',
            'height_cm' => 'Tinggi badan (cm)',
            'findings' => 'Temuan pemeriksaan',
            'patient_designated_staff' => 'Petugas yang ditunjuk pasien',
        ];
    }
}
