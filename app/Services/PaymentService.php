<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\HomecareRequest;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Pencatatan pembayaran & penurunan payment_status pengajuan.
 * Gateway eksternal (masa depan) diimplementasikan lewat PaymentGatewayProvider.
 */
class PaymentService
{
    public function __construct(private readonly AuditService $audit) {}

    /**
     * @param  array{method: string, amount: float|int, reference_number?: string|null, paid_at?: string|null, notes?: string|null}  $data
     */
    public function recordPayment(HomecareRequest $request, array $data, ?User $actor = null): Payment
    {
        return DB::transaction(function () use ($request, $data, $actor) {
            $payment = $request->payments()->create([
                'method' => PaymentMethod::from($data['method']),
                'status' => PaymentStatus::Paid,
                'amount' => $data['amount'],
                'reference_number' => $data['reference_number'] ?? null,
                'paid_at' => isset($data['paid_at']) ? $data['paid_at'] : now(),
                'received_by' => $actor?->id,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->refreshPaymentStatus($request);

            $this->audit->log('RECORDED_PAYMENT', $request, 'Pembayaran dicatat', [
                'payment_id' => $payment->id,
                'amount' => (float) $payment->amount,
                'method' => $payment->method->value,
            ], $actor);

            return $payment;
        });
    }

    public function waive(HomecareRequest $request, ?string $notes, ?User $actor = null): void
    {
        $request->update(['payment_status' => PaymentStatus::Waived]);

        $this->audit->log('WAIVED_PAYMENT', $request, 'Biaya dibebaskan', ['notes' => $notes], $actor);
    }

    public function refreshPaymentStatus(HomecareRequest $request): PaymentStatus
    {
        $paid = (float) $request->payments()
            ->whereIn('status', [PaymentStatus::Paid->value, PaymentStatus::Partial->value])
            ->sum('amount');

        $total = (float) $request->total_amount;

        $status = match (true) {
            $total <= 0 => PaymentStatus::Unpaid,
            $paid >= $total => PaymentStatus::Paid,
            $paid > 0 => PaymentStatus::Partial,
            default => PaymentStatus::Unpaid,
        };

        $request->update(['payment_status' => $status]);

        return $status;
    }

    public function outstandingAmount(HomecareRequest $request): float
    {
        $paid = (float) $request->payments()->where('status', PaymentStatus::Paid->value)->sum('amount');

        return max(0, (float) $request->total_amount - $paid);
    }
}
