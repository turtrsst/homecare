<?php

namespace App\Http\Requests\Operational;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Skrining: catatan + (opsional) layanan final hasil rekomendasi. */
class ApproveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('verify', $this->route('request'));
    }

    public function rules(): array
    {
        return [
            'screening_notes' => ['nullable', 'string', 'max:2000'],
            'services' => ['nullable', 'array', 'max:8'],
            'services.*.homecare_service_id' => [
                'required_with:services', 'integer',
                Rule::exists('homecare_services', 'id')->where('is_active', true)->whereNull('deleted_at'),
            ],
            'services.*.quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'services.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'screening_notes' => 'Catatan skrining',
            'services' => 'Layanan final',
            'services.*.homecare_service_id' => 'Layanan',
            'services.*.quantity' => 'Jumlah',
        ];
    }
}
