<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class ProvideInformationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('provideInformation', $this->route('request'));
    }

    public function rules(): array
    {
        return [
            'information_response' => ['required', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return ['information_response' => 'Jawaban / informasi tambahan'];
    }

    public function messages(): array
    {
        return [
            'information_response.required' => 'Tuliskan informasi tambahan yang diminta tim kami.',
        ];
    }
}
