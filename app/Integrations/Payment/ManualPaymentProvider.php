<?php

namespace App\Integrations\Payment;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Integrations\Contracts\PaymentGatewayProvider;
use App\Models\HomecareRequest;
use App\Models\Payment;

/**
 * Pembayaran dicatat manual oleh petugas (tunai/transfer/asuransi).
 * Tidak ada gateway eksternal — cocok untuk mode standalone.
 */
class ManualPaymentProvider implements PaymentGatewayProvider
{
    public function createInvoice(HomecareRequest $request): Payment
    {
        return Payment::create([
            'homecare_request_id' => $request->id,
            'method' => PaymentMethod::Cash,
            'status' => PaymentStatus::Unpaid,
            'amount' => $request->total_amount,
            'notes' => 'Tagihan dibuat otomatis saat penjadwalan.',
        ]);
    }

    public function verify(Payment $payment): bool
    {
        return $payment->status === PaymentStatus::Paid;
    }

    public function name(): string
    {
        return 'manual';
    }
}
