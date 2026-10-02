<?php

namespace App\Http\Requests\Operational;

use Illuminate\Foundation\Http\FormRequest;

class RequestInformationAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('verify', $this->route('request'));
    }

    public function rules(): array
    {
        return ['questions' => ['required', 'string', 'max:2000']];
    }

    public function attributes(): array
    {
        return ['questions' => 'Informasi yang dibutuhkan'];
    }

    public function messages(): array
    {
        return ['questions.required' => 'Tuliskan informasi apa yang dibutuhkan dari pasien/keluarga.'];
    }
}
