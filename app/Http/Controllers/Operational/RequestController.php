<?php

namespace App\Http\Controllers\Operational;

use App\Actions\ApproveHomecareRequest;
use App\Actions\CancelHomecareRequest;
use App\Actions\RecordPaymentAction;
use App\Actions\ScheduleHomecare;
use App\Actions\VerifyHomecareRequest;
use App\Enums\HomecareRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operational\ApproveRequestRequest;
use App\Http\Requests\Operational\RecordPaymentRequest;
use App\Http\Requests\Operational\RejectRequestRequest;
use App\Http\Requests\Operational\RequestInformationAdminRequest;
use App\Http\Requests\Operational\ScheduleRequestRequest;
use App\Models\HomecareRequest;
use App\Services\HomecareCatalog;
use App\Services\RequestTimeline;
use App\Services\SchedulingService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RequestController extends Controller
{
    public function __construct(
        private readonly VerifyHomecareRequest $verifyAction,
        private readonly ApproveHomecareRequest $approveAction,
        private readonly ScheduleHomecare $scheduleAction,
        private readonly CancelHomecareRequest $cancelAction,
        private readonly RecordPaymentAction $paymentAction,
        private readonly SchedulingService $scheduling,
        private readonly HomecareCatalog $catalog,
        private readonly RequestTimeline $timeline,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('q', ''));

        $requests = HomecareRequest::query()
            ->with(['patient', 'items', 'user', 'appointment'])
            ->when($status && HomecareRequestStatus::tryFrom($status), fn ($q) => $q->status($status))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('code', 'like', "%{$search}%")
                        ->orWhereHas('patient', fn ($p) => $p->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('submitted_at')
            ->paginate(15)
            ->withQueryString();

        return view('operational.requests.index', [
            'requests' => $requests,
            'statuses' => HomecareRequestStatus::cases(),
            'currentStatus' => $status,
            'search' => $search,
        ]);
    }

    public function show(Request $httpRequest, HomecareRequest $request): View
    {
        Gate::authorize('view', $request);

        $request->load([
            'patient.contacts',
            'address',
            'user',
            'items.service',
            'appointment.assignments.staff',
            'appointment.assessments.staff',
            'appointment.serviceRecords.staff',
            'appointment.clinicalNotes.staff',
            'payments',
            'attachments.uploader',
            'auditLogs.user',
            'verifier',
            'screener',
        ]);

        return view('operational.requests.show', [
            'request' => $request,
            'timeline' => $this->timeline->build($request),
            'services' => $this->catalog->activeServices(),
            'availableStaff' => $this->scheduling->availableStaff(),
            'canVerify' => $httpRequest->user()->can('verify', $request),
            'canSchedule' => $httpRequest->user()->can('schedule', $request),
            'canRecordPayment' => $httpRequest->user()->can('recordPayment', $request),
        ]);
    }

    public function startReview(Request $httpRequest, HomecareRequest $request): RedirectResponse
    {
        return $this->run(fn () => $this->verifyAction->startReview($request, $httpRequest->user()),
            'Verifikasi dimulai.', $httpRequest);
    }

    public function requestInformation(RequestInformationAdminRequest $form, HomecareRequest $request): RedirectResponse
    {
        return $this->run(
            fn () => $this->verifyAction->requestInformation($request, $form->validated('questions'), $form->user()),
            'Permintaan informasi tambahan dikirim ke pasien.',
            $form,
        );
    }

    public function reject(RejectRequestRequest $form, HomecareRequest $request): RedirectResponse
    {
        return $this->run(
            fn () => $this->verifyAction->reject($request, $form->validated('reason'), $form->user()),
            'Pengajuan ditolak dan pasien telah diberi tahu.',
            $form,
        );
    }

    public function approve(ApproveRequestRequest $form, HomecareRequest $request): RedirectResponse
    {
        return $this->run(
            fn () => $this->approveAction->execute(
                $request,
                $form->validated('screening_notes'),
                $form->validated('services'),
                $form->user(),
            ),
            'Pengajuan disetujui. Silakan tetapkan jadwal dan petugas.',
            $form,
        );
    }

    public function schedule(ScheduleRequestRequest $form, HomecareRequest $request): RedirectResponse
    {
        $validated = $form->validated();

        if (filled($validated['total_amount'] ?? null)) {
            $request->update(['total_amount' => $validated['total_amount']]);
        }

        return $this->run(
            fn () => $this->scheduleAction->execute(
                $request,
                Carbon::parse($validated['scheduled_at']),
                $validated['staff_ids'],
                $validated['notes'] ?? null,
                $form->user(),
            ),
            'Jadwal diterbitkan. Pasien dan petugas telah diberi tahu.',
            $form,
        );
    }

    public function cancel(Request $httpRequest, HomecareRequest $request): RedirectResponse
    {
        // validated() hanya ada di FormRequest; Request biasa memakai validate()
        // yang sekaligus mengembalikan array hasil validasi.
        $validated = $httpRequest->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ], [], ['reason' => 'Alasan pembatalan']);

        return $this->run(
            fn () => $this->cancelAction->byStaff($request, $validated['reason'], $httpRequest->user()),
            'Pengajuan dibatalkan.',
            $httpRequest,
        );
    }

    public function recordPayment(RecordPaymentRequest $form, HomecareRequest $request): RedirectResponse
    {
        return $this->run(
            fn () => $this->paymentAction->execute($request, $form->validated(), $form->user()),
            'Pembayaran dicatat.',
            $form,
        );
    }

    /** Jalankan aksi; ubah DomainException menjadi flash error yang ramah. */
    private function run(callable $action, string $successMessage, Request $form): RedirectResponse
    {
        try {
            $action();

            return back()->with('success', $successMessage);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
