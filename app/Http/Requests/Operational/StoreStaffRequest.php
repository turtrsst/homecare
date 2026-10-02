<?php

namespace App\Http\Requests\Operational;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', \App\Models\HealthcareStaff::class);
    }

    public function rules(): array
    {
        $staff = $this->route('staff');

        return [
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id'), Rule::unique('healthcare_staff', 'user_id')->ignore($staff?->id)],
            'name' => ['required', 'string', 'max:191'],
            'profession' => ['required', Rule::enum(\App\Enums\Profession::class)],
            'license_number' => ['nullable', 'string', 'max:64'],
            'specialization' => ['nullable', 'string', 'max:128'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:191'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'user_id' => 'Akun login terhubung',
            'name' => 'Nama tenaga kesehatan',
            'profession' => 'Profesi',
            'license_number' => 'Nomor STR/SIP',
            'specialization' => 'Spesialisasi/keahlian',
            'phone' => 'Nomor telepon',
            'email' => 'Email',
            'bio' => 'Profil singkat',
            'is_active' => 'Status aktif',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }
}
