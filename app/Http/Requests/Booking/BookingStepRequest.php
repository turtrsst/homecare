<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Basis validasi per langkah wizard booking.
 * Setiap langkah punya rules sendiri; data tersimpan di session sehingga
 * input user TIDAK hilang ketika validasi gagal.
 */
abstract class BookingStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }
}
