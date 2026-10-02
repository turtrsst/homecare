<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class CompleteVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('visit', $this->route('appointment'));
    }

    public function rules(): array
    {
        return [
            'actions_taken' => ['required', 'string', 'min:10', 'max:3000'],
            'results' => ['nullable', 'string', 'max:3000'],
            'recommendations' => ['nullable', 'string', 'max:3000'],
            'follow_up_needed' => ['nullable', 'boolean'],
            'follow_up_notes' => ['nullable', 'string', 'max:1000', 'required_if:follow_up_needed,true'],
        ];
    }

    public function attributes(): array
    {
        return [
            'actions_taken' => 'Tindakan yang dilakukan',
            'results' => 'Hasil/evaluasi tindakan',
            'recommendations' => 'Anjuran untuk pasien/keluarga',
            'follow_up_needed' => 'Perlu tindak lanjut',
            'follow_up_notes' => 'Catatan tindak lanjut',
        ];
    }

    public function messages(): array
    {
        return [
            'actions_taken.required' => 'Tuliskan tindakan yang telah dilakukan pada kunjungan ini.',
            'actions_taken.min' => 'Jelaskan tindakan minimal 10 karakter.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['follow_up_needed' => $this->boolean('follow_up_needed')]);
    }
}
