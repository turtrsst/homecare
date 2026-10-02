<?php

namespace App\Http\Requests\Patient;

use App\Models\HomecareRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $request = $this->route('request');

        // Kelayakan (sudah selesai & lunas, belum pernah ulas) diperiksa di
        // aksi agar pasien mendapat pesan ramah, bukan halaman 403.
        return $request instanceof HomecareRequest
            && $this->user()?->can('view', $request) === true;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'rating' => 'Rating',
            'comment' => 'Ulasan',
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'Pilih dulu jumlah bintangnya (1–5).',
            'rating.min' => 'Rating minimal 1 bintang.',
            'rating.max' => 'Rating maksimal 5 bintang.',
        ];
    }
}
