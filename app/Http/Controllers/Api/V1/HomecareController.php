<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\CancelHomecareRequest;
use App\Actions\SubmitHomecareRequest;
use App\Actions\VerifyHomecareRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AppointmentResource;
use App\Http\Resources\V1\HomecareRequestResource;
use App\Models\HomecareRequest;
use App\Models\HomecareService;
use App\Models\PatientAddress;
use App\Models\PatientProfile;
use App\Services\RequestTimeline;
use App\Support\ApiResponse;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

/**
 * Catatan: parameter route API memakai {request:code} sehingga nama argumen
 * method adalah $request (model). Request HTTP di-inject sebagai $http.
 */
class HomecareController extends Controller
{
    public function __construct(
        private readonly SubmitHomecareRequest $submitAction,
        private readonly CancelHomecareRequest $cancelAction,
        private readonly VerifyHomecareRequest $verifyAction,
        private readonly RequestTimeline $timeline,
    ) {}

    /** GET /api/v1/homecare — riwayat pengajuan milik user. */
    public function index(HttpRequest $http): JsonResponse
    {
        $paginator = $http->user()->homecareRequests()
            ->with(['items', 'patient', 'appointment.assignments.staff'])
            ->latest('submitted_at')
            ->paginate(min(50, (int) $http->input('per_page', 15)));

        $paginator->setCollection(
            collect(HomecareRequestResource::collection($paginator->getCollection())->resolve())
        );

        return ApiResponse::paginated($paginator, 'Riwayat pengajuan homecare');
    }

    /** POST /api/v1/homecare — buat pengajuan baru. */
    public function store(HttpRequest $http): JsonResponse
    {
        $validated = $http->validate([
            'patient_profile_id' => ['required', 'integer'],
            'patient_address_id' => ['required', 'integer'],
            'service_ids' => ['required', 'array', 'min:1', 'max:5'],
            'service_ids.*' => ['integer', 'exists:homecare_services,id'],
            'complaint' => ['required', 'string', 'min:10', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'preferred_date' => [
                'required', 'date',
                'after_or_equal:'.now()->addDays((int) config('homecare.min_lead_days', 1))->toDateString(),
                'before_or_equal:'.now()->addDays((int) config('homecare.max_advance_days', 30))->toDateString(),
            ],
            'preferred_time_window' => ['required', Rule::in(array_keys(config('homecare.time_windows')))],
        ], [
            'complaint.min' => 'Jelaskan kebutuhan/keluhan minimal 10 karakter.',
            'preferred_date.after_or_equal' => 'Kunjungan paling cepat besok.',
            'patient_profile_id.required' => 'Pasien wajib dipilih.',
            'patient_address_id.required' => 'Alamat kunjungan wajib dipilih.',
            'service_ids.required' => 'Pilih minimal satu layanan.',
        ]);

        $user = $http->user();

        // Otorisasi kepemilikan pasien & alamat + layanan harus aktif.
        $patient = PatientProfile::ownedBy($user)->findOrFail($validated['patient_profile_id']);
        $address = PatientAddress::where('patient_profile_id', $patient->id)
            ->findOrFail($validated['patient_address_id']);

        $uniqueIds = array_values(array_unique(array_map('intval', $validated['service_ids'])));
        $activeCount = HomecareService::query()->active()->whereIn('id', $uniqueIds)->count();

        if ($activeCount !== count($uniqueIds)) {
            return ApiResponse::error('Data tidak valid', Response::HTTP_UNPROCESSABLE_ENTITY, [
                'service_ids' => ['Salah satu layanan tidak tersedia.'],
            ]);
        }

        $homecareRequest = $this->submitAction->execute($user, [
            'patient_profile_id' => $patient->id,
            'patient_address_id' => $address->id,
            'complaint' => $validated['complaint'],
            'notes' => $validated['notes'] ?? null,
            'preferred_date' => $validated['preferred_date'],
            'preferred_time_window' => $validated['preferred_time_window'],
            'services' => collect($uniqueIds)
                ->map(fn (int $id) => ['homecare_service_id' => $id, 'quantity' => 1])
                ->all(),
        ]);

        $homecareRequest->load(['items', 'patient', 'address']);

        return ApiResponse::success(
            new HomecareRequestResource($homecareRequest),
            'Pengajuan homecare berhasil dibuat',
            Response::HTTP_CREATED,
        );
    }

    /** GET /api/v1/homecare/{code} */
    public function show(HttpRequest $http, HomecareRequest $request): JsonResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('view', $request);

        $request->load(['items', 'patient', 'address', 'appointment.assignments.staff', 'payments']);

        return ApiResponse::success(new HomecareRequestResource($request));
    }

    /** GET /api/v1/homecare/{code}/status — status + timeline untuk UI mobile. */
    public function status(HttpRequest $http, HomecareRequest $request): JsonResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('view', $request);

        $request->loadMissing('appointment');

        return ApiResponse::success([
            'code' => $request->code,
            'status' => $request->status->value,
            'status_label' => $request->status->label(),
            'patient_label' => $request->status->patientLabel(),
            'timeline' => $this->timeline->build($request),
            'appointment_status' => $request->appointment?->status->value,
            'appointment_status_label' => $request->appointment?->status->patientLabel(),
        ], 'Status pengajuan homecare');
    }

    /** GET /api/v1/homecare/{code}/appointment */
    public function appointment(HttpRequest $http, HomecareRequest $request): JsonResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('view', $request);

        $appointment = $request->appointment?->load('assignments.staff');

        if (! $appointment) {
            return ApiResponse::success(null, 'Jadwal kunjungan belum ditetapkan.');
        }

        return ApiResponse::success(new AppointmentResource($appointment));
    }

    /** POST /api/v1/homecare/{code}/cancel */
    public function cancel(HttpRequest $http, HomecareRequest $request): JsonResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('cancel', $request);

        $validated = $http->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->cancelAction->byPatient($request, $validated['reason'] ?? null, $http->user());

            return ApiResponse::success(
                new HomecareRequestResource($request->refresh()->load('items')),
                'Pengajuan dibatalkan',
            );
        } catch (DomainException $e) {
            return ApiResponse::error($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /** POST /api/v1/homecare/{code}/information */
    public function provideInformation(HttpRequest $http, HomecareRequest $request): JsonResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('provideInformation', $request);

        $validated = $http->validate([
            'information_response' => ['required', 'string', 'max:2000'],
        ], ['information_response.required' => 'Tuliskan informasi tambahan yang diminta.']);

        try {
            $this->verifyAction->provideInformation($request, $validated['information_response'], $http->user());

            return ApiResponse::success(
                new HomecareRequestResource($request->refresh()->load('items')),
                'Informasi tambahan diterima',
            );
        } catch (DomainException $e) {
            return ApiResponse::error($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
