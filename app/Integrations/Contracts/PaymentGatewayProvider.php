<?php

namespace App\Integrations\Contracts;

use App\Models\HomecareRequest;
use App\Models\Payment;

/**
 * Abstraksi pembayaran. Default: ManualPaymentProvider (pencatatan oleh
 * petugas). Gateway nyata (Midtrans/Xendit/...) mengimplementasikan kontrak
 * yang sama.
 */
interface PaymentGatewayProvider
{
    /** Buat tagihan/invoice untuk sebuah pengajuan. */
    public function createInvoice(HomecareRequest $request): Payment;

    /** Verifikasi status pembayaran dari sumber eksternal (webhook/polling). */
    public function verify(Payment $payment): bool;

    public function name(): string;
}
