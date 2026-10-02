<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('patient'));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'relationship' => ['required', 'string', 'max:64'],
            'nik' => ['nullable', 'digits:16', Rule::unique('patient_profiles', 'nik')->ignore($this->route('patient'))],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'blood_type' => ['nullable', 'string', 'max:8'],
            'medical_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama pasien',
            'relationship' => 'Hubungan dengan Anda',
            'nik' => 'NIK',
            'gender' => 'Jenis kelamin',
            'birth_date' => 'Tanggal lahir',
            'blood_type' => 'Golongan darah',
            'medical_notes' => 'Catatan kesehatan',
        ];
    }
}
