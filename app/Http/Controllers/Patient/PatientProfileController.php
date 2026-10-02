<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StorePatientProfileRequest;
use App\Http\Requests\Patient\UpdatePatientProfileRequest;
use App\Models\PatientProfile;
use App\Services\PatientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientProfileController extends Controller
{
    public function __construct(private readonly PatientService $patients) {}

    public function index(Request $request): View
    {
        return view('patient.patients.index', [
            'patients' => $this->patients->profilesFor($request->user()),
        ]);
    }

    public function create(Request $httpRequest): View
    {
        return view('patient.patients.create', [
            'patient' => new PatientProfile(),
            'phone' => null,
            'relationships' => StorePatientProfileRequest::relationshipChoices(),
            'returnTo' => self::internalUrl($httpRequest->query('return_to'), $httpRequest->getHost()),
        ]);
    }

    public function store(StorePatientProfileRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $phone = $data['phone'] ?? null;
        unset($data['phone']);

        $profile = $this->patients->createProfile($request->user(), $data, $phone);

        $returnTo = self::internalUrl($request->input('return_to'), $request->getHost());

        return redirect($returnTo ?? route('akun.pasien.index'))
            ->with('success', 'Pasien «'.$profile->name.'» berhasil ditambahkan.');
    }

    public function edit(Request $request, PatientProfile $patient): View
    {
        $this->authorizePatient($request, $patient);

        return view('patient.patients.edit', [
            'patient' => $patient->load('contacts'),
            'relationships' => StorePatientProfileRequest::relationshipChoices(),
        ]);
    }

    public function update(UpdatePatientProfileRequest $request, PatientProfile $patient): RedirectResponse
    {
        $this->patients->updateProfile($request->user(), $patient, $request->validated());

        return redirect()->route('akun.pasien.index')
            ->with('success', 'Data pasien diperbarui.');
    }

    public function destroy(Request $request, PatientProfile $patient): RedirectResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('delete', $patient);

        if ($patient->homecareRequests()->exists()) {
            return back()->with('error', 'Pasien ini memiliki riwayat pengajuan sehingga tidak dapat dihapus.');
        }

        $patient->delete();

        return redirect()->route('akun.pasien.index')->with('success', 'Data pasien dihapus.');
    }

    private function authorizePatient(Request $request, PatientProfile $patient): void
    {
        abort_unless($request->user()->id === $patient->user_id, 403, 'Data pasien ini bukan milik Anda.');
    }

    /** Hanya izinkan redirect ke path internal (anti open-redirect). URL absolut sehost juga diterima. */
    public static function internalUrl(?string $url, ?string $currentHost = null): ?string
    {
        if (! is_string($url) || $url === '') {
            return null;
        }

        $parts = parse_url($url);

        if (($parts['host'] ?? null) !== null) {
            $allowed = array_filter([
                strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST)),
                $currentHost !== null ? strtolower($currentHost) : null,
            ]);

            if (! in_array(strtolower($parts['host']), $allowed, true)) {
                return null;
            }

            $url = ($parts['path'] ?? '/').(isset($parts['query']) ? '?'.$parts['query'] : '');
        }

        if (! str_starts_with($url, '/') || str_starts_with($url, '//')) {
            return null;
        }

        return $url;
    }
}
