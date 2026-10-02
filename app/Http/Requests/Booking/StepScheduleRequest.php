<?php

namespace App\Http\Requests\Booking;

use Illuminate\Validation\Rule;

class StepScheduleRequest extends BookingStepRequest
{
    public function rules(): array
    {
        $min = now()->addDays((int) config('homecare.min_lead_days', 1))->startOfDay();
        $max = now()->addDays((int) config('homecare.max_advance_days', 30))->endOfDay();

        return [
            'preferred_date' => ['required', 'date', 'after_or_equal:'.$min->toDateString(), 'before_or_equal:'.$max->toDateString()],
            'preferred_time_window' => ['required', Rule::in(array_keys(config('homecare.time_windows', [])))],
        ];
    }

    public function attributes(): array
    {
        return [
            'preferred_date' => 'Tanggal kunjungan',
            'preferred_time_window' => 'Waktu kunjungan',
        ];
    }

    public function messages(): array
    {
        return [
            'preferred_date.after_or_equal' => 'Kunjungan paling cepat besok. Untuk kebutuhan mendesak hari ini, hubungi kami lewat halaman Kontak.',
            'preferred_date.before_or_equal' => 'Jadwal maksimal 30 hari ke depan.',
            'preferred_time_window.required' => 'Pilih perkiraan waktu kunjungan.',
        ];
    }
}
