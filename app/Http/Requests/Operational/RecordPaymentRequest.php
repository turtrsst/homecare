<?php

namespace App\Http\Requests\Operational;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('recordPayment', $this->route('request'));
    }

    public function rules(): array
    {
        return [
            'method' => ['required', Rule::enum(\App\Enums\PaymentMethod::class)],
            'amount' => ['required', 'numeric', 'min:1', 'max:100000000'],
            'reference_number' => ['nullable', 'string', 'max:96'],
            'paid_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'method' => 'Metode pembayaran',
            'amount' => 'Jumlah pembayaran',
            'reference_number' => 'Nomor referensi',
            'paid_at' => 'Tanggal pembayaran',
            'notes' => 'Catatan',
        ];
    }
}
