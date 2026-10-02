<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StorePatientProfileRequest;
use App\Http\Requests\Patient\UpdatePatientProfileRequest;
use App\Http\Resources\V1\PatientResource;
use App\Models\PatientProfile;
use App\Services\PatientService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class PatientController extends Controller
{
    public function __construct(private readonly PatientService $patients) {}

    /** GET /api/v1/patients — hanya milik user terautentikasi. */
    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success(
            PatientResource::collection(
                $request->user()->patientProfiles()->with(['contacts', 'addresses'])->orderBy('name')->get()
            )->resolve(),
        );
    }

    /** POST /api/v1/patients */
    public function store(StorePatientProfileRequest $request): JsonResponse
    {
        $data = $request->validated();
        $phone = $data['phone'] ?? null;
        unset($data['phone']);

        $profile = $this->patients->createProfile($request->user(), $data, $phone);

        return ApiResponse::success(
            new PatientResource($profile->load('contacts')),
            'Pasien berhasil ditambahkan',
            Response::HTTP_CREATED,
        );
    }

    /** GET /api/v1/patients/{id} */
    public function show(Request $request, PatientProfile $patient): JsonResponse
    {
        Gate::authorize('view', $patient);

        return ApiResponse::success(new PatientResource($patient->load(['contacts', 'addresses'])));
    }

    /** PUT /api/v1/patients/{id} */
    public function update(UpdatePatientProfileRequest $request, PatientProfile $patient): JsonResponse
    {
        $profile = $this->patients->updateProfile($request->user(), $patient, $request->validated());

        return ApiResponse::success(new PatientResource($profile), 'Data pasien diperbarui');
    }
}
