<?php

namespace App\Http\Controllers\Patient;

use App\Actions\CancelHomecareRequest;
use App\Actions\StoreReviewAction;
use App\Actions\VerifyHomecareRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\CancelRequestRequest;
use App\Http\Requests\Patient\ProvideInformationRequest;
use App\Http\Requests\Patient\StoreReviewRequest;
use App\Models\HomecareRequest;
use App\Services\RequestTimeline;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RequestController extends Controller
{
    public function __construct(
        private readonly RequestTimeline $timeline,
        private readonly CancelHomecareRequest $cancelAction,
        private readonly VerifyHomecareRequest $verifyAction,
        private readonly StoreReviewAction $reviewAction,
    ) {}

    public function index(Request $request): View
    {
        $requests = $request->user()->homecareRequests()
            ->with(['items', 'patient', 'appointment'])
            ->latest('submitted_at')
            ->paginate(10);

        return view('patient.requests.index', [
            'requests' => $requests,
            'activeCount' => $request->user()->homecareRequests()->open()->count(),
        ]);
    }

    public function show(Request $httpRequest, HomecareRequest $request): View
    {
        $this->authorize('view', $request);

        $request->load([
            'items.service',
            'patient',
            'address',
            'appointment.assignments.staff',
            'appointment.assessments',
            'appointment.serviceRecords',
            'payments',
            'attachments',
            'review.staff',
        ]);

        return view('patient.requests.show', [
            'request' => $request,
            'timeline' => $this->timeline->build($request),
            'canCancel' => $httpRequest->user()->can('cancel', $request),
            'canProvideInformation' => $httpRequest->user()->can('provideInformation', $request),
            'canReview' => $request->review === null && $request->canBeReviewed(),
        ]);
    }

    public function cancel(CancelRequestRequest $httpRequest, HomecareRequest $request): RedirectResponse
    {
        try {
            $this->cancelAction->byPatient($request, $httpRequest->validated('reason'), $httpRequest->user());

            return back()->with('success', 'Pengajuan berhasil dibatalkan.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function provideInformation(ProvideInformationRequest $httpRequest, HomecareRequest $request): RedirectResponse
    {
        try {
            $this->verifyAction->provideInformation($request, $httpRequest->validated('information_response'), $httpRequest->user());

            return back()->with('success', 'Terima kasih! Informasi tambahan Anda sudah kami terima dan sedang diperiksa.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /** Ulasan + rating bintang 1–5 dari pasien untuk petugas pemeriksa. */
    public function storeReview(StoreReviewRequest $form, HomecareRequest $request): RedirectResponse
    {
        try {
            $this->reviewAction->execute($request, $form->validated(), $form->user());

            return redirect()->route('akun.pengajuan.show', $request->code)
                ->with('success', 'Terima kasih! Ulasan Anda sudah kami terima.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /** Compat alias untuk authorize() controller lama. */
    protected function authorize(string $ability, $model): void
    {
        \Illuminate\Support\Facades\Gate::authorize($ability, $model);
    }
}
