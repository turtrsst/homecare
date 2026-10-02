<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use App\Models\PatientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', PatientProfile::class);

        $search = trim((string) $request->query('q', ''));

        $patients = PatientProfile::query()
            ->with(['user', 'addresses'])
            ->withCount('homecareRequests')
            ->when($search, fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('nik', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('operational.patients.index', [
            'patients' => $patients,
            'search' => $search,
        ]);
    }

    public function show(PatientProfile $patient): View
    {
        Gate::authorize('view', $patient);

        $patient->load(['user', 'contacts', 'addresses']);

        return view('operational.patients.show', [
            'patient' => $patient,
            'requests' => $patient->homecareRequests()
                ->with(['items', 'appointment'])
                ->latest('submitted_at')
                ->paginate(10),
        ]);
    }
}
