<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StorePatientAddressRequest;
use App\Http\Resources\V1\AddressResource;
use App\Models\PatientAddress;
use App\Models\PatientProfile;
use App\Services\PatientService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class AddressController extends Controller
{
    public function __construct(private readonly PatientService $patients) {}

    /** GET /api/v1/addresses — semua alamat milik user. */
    public function index(Request $request): JsonResponse
    {
        $addresses = PatientAddress::query()
            ->whereHas('patientProfile', fn ($q) => $q->where('user_id', $request->user()->id))
            ->with('patientProfile')
            ->get();

        return ApiResponse::success(AddressResource::collection($addresses)->resolve());
    }

    /** GET /api/v1/patients/{patient}/addresses */
    public function indexForPatient(Request $request, PatientProfile $patient): JsonResponse
    {
        Gate::authorize('view', $patient);

        return ApiResponse::success(
            AddressResource::collection($patient->addresses()->get())->resolve(),
        );
    }

    /** POST /api/v1/patients/{patient}/addresses */
    public function storeForPatient(StorePatientAddressRequest $request, PatientProfile $patient): JsonResponse
    {
        $address = $this->patients->addAddress($request->user(), $patient, $request->validated());

        return ApiResponse::success(new AddressResource($address), 'Alamat ditambahkan', Response::HTTP_CREATED);
    }

    /** PUT /api/v1/addresses/{address} */
    public function update(Request $request, PatientAddress $address): JsonResponse
    {
        $patient = $address->patientProfile;
        abort_unless($patient && $patient->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:64'],
            'recipient_name' => ['sometimes', 'required', 'string', 'max:120'],
            'phone' => ['sometimes', 'required', 'string', 'max:32'],
            'address_line' => ['sometimes', 'required', 'string', 'max:500'],
            'city' => ['sometimes', 'required', 'string', 'max:96'],
            'province' => ['nullable', 'string', 'max:96'],
            'postal_code' => ['nullable', 'string', 'max:16'],
            'notes' => ['nullable', 'string', 'max:500'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $address = $this->patients->updateAddress($request->user(), $address, $validated);

        return ApiResponse::success(new AddressResource($address), 'Alamat diperbarui');
    }
}
