<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'relationship' => ['required', 'string', 'max:64', Rule::in($this->relationshipChoices())],
            'nik' => ['nullable', 'digits:16', Rule::unique('patient_profiles', 'nik')],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'blood_type' => ['nullable', Rule::in(['A', 'B', 'AB', 'O', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'tidak_tahu'])],
            'medical_notes' => ['nullable', 'string', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:32', 'regex:/^[0-9+\-\s]+$/'],
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
            'phone' => 'Nomor telepon',
        ];
    }

    public function messages(): array
    {
        return [
            'nik.digits' => 'NIK harus terdiri dari 16 digit angka.',
            'medical_notes.max' => 'Catatan kesehatan maksimal 2000 karakter.',
        ];
    }

    /** @return list<string> */
    public static function relationshipChoices(): array
    {
        return ['diri_sendiri', 'suami', 'istri', 'ayah', 'ibu', 'anak', 'saudara', 'kakek', 'nenek', 'lainnya'];
    }

    public function relationshipLabel(): string
    {
        $value = (string) $this->input('relationship');

        return match ($value) {
            'diri_sendiri' => 'Diri Sendiri',
            'suami' => 'Suami',
            'istri' => 'Istri',
            'ayah' => 'Ayah',
            'ibu' => 'Ibu',
            'anak' => 'Anak',
            'saudara' => 'Saudara',
            'kakek' => 'Kakek',
            'nenek' => 'Nenek',
            default => ucfirst(str_replace('_', ' ', $value)),
        };
    }
}
