<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StorePatientAddressRequest;
use App\Http\Requests\Patient\UpdatePatientAddressRequest;
use App\Models\PatientAddress;
use App\Models\PatientProfile;
use App\Services\PatientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientAddressController extends Controller
{
    public function __construct(private readonly PatientService $patients) {}

    public function index(Request $request, PatientProfile $patient): View
    {
        $this->authorizePatient($request, $patient);

        return view('patient.addresses.index', [
            'patient' => $patient->load('addresses'),
        ]);
    }

    public function create(Request $httpRequest, PatientProfile $patient): View
    {
        $this->authorizePatient($httpRequest, $patient);

        return view('patient.addresses.create', [
            'patient' => $patient,
            'address' => new PatientAddress([
                'recipient_name' => $patient->name,
                'phone' => $httpRequest->user()->phone,
            ]),
            'formRoute' => route('akun.pasien.alamat.store', $patient),
        ]);
    }

    public function store(StorePatientAddressRequest $request, PatientProfile $patient): RedirectResponse
    {
        $this->patients->addAddress($request->user(), $patient, $request->validated());

        $returnTo = PatientProfileController::internalUrl($request->input('return_to'), $request->getHost());

        return redirect($returnTo ?? route('akun.pasien.alamat.index', $patient))
            ->with('success', 'Alamat berhasil ditambahkan.');
    }

    public function edit(Request $httpRequest, PatientProfile $patient, PatientAddress $address): View
    {
        $this->authorizePatient($httpRequest, $patient);
        abort_unless($address->patient_profile_id === $patient->id, 404);

        return view('patient.addresses.edit', [
            'patient' => $patient,
            'address' => $address,
            'formRoute' => route('akun.pasien.alamat.update', [$patient, $address]),
        ]);
    }

    public function update(UpdatePatientAddressRequest $request, PatientProfile $patient, PatientAddress $address): RedirectResponse
    {
        abort_unless($address->patient_profile_id === $patient->id, 404);

        $this->patients->updateAddress($request->user(), $address, $request->validated());

        $returnTo = PatientProfileController::internalUrl($request->input('return_to'), $request->getHost());

        return redirect($returnTo ?? route('akun.pasien.alamat.index', $patient))
            ->with('success', 'Alamat diperbarui.');
    }

    public function destroy(Request $httpRequest, PatientProfile $patient, PatientAddress $address): RedirectResponse
    {
        $this->authorizePatient($httpRequest, $patient);
        abort_unless($address->patient_profile_id === $patient->id, 404);

        if ($address->homecareRequests()->exists()) {
            return back()->with('error', 'Alamat ini masih dipakai pengajuan sehingga tidak dapat dihapus.');
        }

        $address->delete();

        return back()->with('success', 'Alamat dihapus.');
    }

    private function authorizePatient(Request $request, PatientProfile $patient): void
    {
        abort_unless($request->user()->id === $patient->user_id, 403, 'Data pasien ini bukan milik Anda.');
    }
}
