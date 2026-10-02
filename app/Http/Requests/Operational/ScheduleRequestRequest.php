<?php

namespace App\Http\Requests\Operational;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Tetapkan jadwal + tenaga kesehatan + konfirmasi biaya. */
class ScheduleRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('schedule', $this->route('request'));
    }

    public function rules(): array
    {
        return [
            'scheduled_at' => ['required', 'date', 'after:now'],
            'staff_ids' => ['required', 'array', 'min:1'],
            'staff_ids.*' => ['integer', Rule::exists('healthcare_staff', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'notes' => ['nullable', 'string', 'max:1000'],
            'total_amount' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'scheduled_at' => 'Jadwal kunjungan',
            'staff_ids' => 'Tenaga kesehatan',
            'notes' => 'Catatan kunjungan',
            'total_amount' => 'Total biaya',
        ];
    }

    public function messages(): array
    {
        return [
            'scheduled_at.after' => 'Jadwal kunjungan harus di waktu yang akan datang.',
            'staff_ids.required' => 'Pilih minimal satu tenaga kesehatan untuk kunjungan ini.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normalisasi input datetime-local ("2026-09-25T09:00") ke format yang bisa diparse.
        if ($this->filled('scheduled_at')) {
            $this->merge(['scheduled_at' => str_replace('T', ' ', (string) $this->input('scheduled_at'))]);
        }
    }
}
