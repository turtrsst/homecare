<?php

namespace App\Actions;

use App\Models\HomecareRequest;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\RequestStatusChanged;
use App\Services\AuditService;
use App\Services\PaymentService;

/** Catat pembayaran (manual oleh petugas) + kabari pasien. */
class RecordPaymentAction
{
    public function __construct(
        private readonly PaymentService $payments,
        private readonly AuditService $audit,
    ) {}

    /** @param array<string, mixed> $data */
    public function execute(HomecareRequest $request, array $data, User $actor): Payment
    {
        $before = $request->payment_status;

        $payment = $this->payments->recordPayment($request, $data, $actor);

        $request->refresh();

        if ($before !== $request->payment_status && $request->payment_status->value === 'paid') {
            $request->user?->notify(new RequestStatusChanged(
                $request,
                'Pembayaran Lunas',
                'Pembayaran untuk pengajuan '.$request->code.' telah kami terima. Terima kasih.',
            ));
        }

        return $payment;
    }
}
