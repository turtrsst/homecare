<?php

namespace App\Http\Requests\Operational;

use Illuminate\Foundation\Http\FormRequest;

class RejectRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('verify', $this->route('request'));
    }

    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'max:1000']];
    }

    public function attributes(): array
    {
        return ['reason' => 'Alasan penolakan'];
    }

    public function messages(): array
    {
        return ['reason.required' => 'Tuliskan alasan penolakan agar pasien memahami keputusan ini.'];
    }
}
