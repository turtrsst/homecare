<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        $patient = $this->route('patient');

        return $patient !== null && ($this->user()->id === $patient->user_id || $this->user()->can('update', $patient));
    }

    public function rules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:64'],
            'recipient_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:32', 'regex:/^[0-9+\-\s]+$/'],
            'address_line' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:96'],
            'province' => ['nullable', 'string', 'max:96'],
            'postal_code' => ['nullable', 'string', 'max:16'],
            'notes' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_primary' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'label' => 'Label alamat',
            'recipient_name' => 'Nama penerima',
            'phone' => 'Nomor telepon yang bisa dihubungi',
            'address_line' => 'Alamat lengkap',
            'city' => 'Kota/Kabupaten',
            'province' => 'Provinsi',
            'postal_code' => 'Kode pos',
            'notes' => 'Catatan untuk petugas',
            'latitude' => 'Garis lintang',
            'longitude' => 'Garis bujur',
        ];
    }
}
