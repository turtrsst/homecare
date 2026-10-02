<?php

namespace App\Http\Controllers\Staff;

use App\Actions\VisitActions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\AssessmentRequest;
use App\Http\Requests\Staff\CompleteVisitRequest;
use App\Models\Appointment;
use App\Models\HealthcareStaff;
use App\Services\AuditService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Area petugas homecare: daftar tugas + pelaksanaan kunjungan
 * (berangkat → check-in → asesmen → pelayanan → dokumentasi → check-out).
 */
class TaskController extends Controller
{
    public function __construct(
        private readonly VisitActions $visits,
        private readonly AuditService $audit,
    ) {}

    public function index(Request $request): View
    {
        $staff = $this->staffOrFail($request);

        $assignments = $staff->assignments()
            ->with(['appointment.request.patient', 'appointment.request.items'])
            ->whereIn('status', ['assigned', 'active'])
            ->get();

        $appointments = Appointment::query()
            ->with(['request.patient', 'request.items', 'assignments.staff'])
            ->forStaff($staff)
            ->orderBy('scheduled_at')
            ->get()
            ->partition(fn (Appointment $a) => $a->scheduled_at->isToday());

        $history = Appointment::query()
            ->with(['request.patient'])
            ->forStaff($staff)
            ->whereIn('status', ['completed'])
            ->latest('scheduled_at')
            ->take(5)
            ->get();

        return view('staff.tasks.index', [
            'staff' => $staff,
            'today' => $appointments[0] ?? collect(),
            'upcoming' => $appointments[1] ?? collect(),
            'history' => $history,
        ]);
    }

    public function show(Request $request, Appointment $appointment): View
    {
        Gate::authorize('view', $appointment);

        $appointment->load([
            'request.patient.contacts',
            'request.items',
            'request.address',
            'request.payments',
            'request.review.staff',
            'assignments.staff',
            'assessments.staff',
            'serviceRecords.staff',
            'clinicalNotes.staff',
            'attachments',
        ]);

        $staff = $request->user()->staffProfile;

        return view('staff.tasks.show', [
            'appointment' => $appointment,
            'staff' => $staff,
            'myAssignment' => $staff === null
                ? null
                : $appointment->assignments->first(
                    fn ($assignment) => $assignment->healthcare_staff_id === $staff->id
                ),
            'payments' => $appointment->request->payments->sortByDesc('created_at')->values(),
            'canVisit' => $request->user()->can('visit', $appointment),
        ]);
    }

    /** Petugas menyetujui tugas yang ditugaskan kepadanya. */
    public function confirm(Request $request, Appointment $appointment): RedirectResponse
    {
        return $this->run(
            fn ($staff) => $this->visits->confirm($appointment, $staff, $request->user()),
            $request,
            'Tugas dikonfirmasi. Terima kasih, semoga lancar!',
        );
    }

    public function depart(Request $request, Appointment $appointment): RedirectResponse
    {
        return $this->run(fn ($staff) => $this->visits->depart($appointment, $staff, $request->user()),
            $request, 'Perjalanan dimulai. Hati-hati di jalan!');
    }

    public function checkIn(Request $request, Appointment $appointment): RedirectResponse
    {
        $request->validate(['notes' => ['nullable', 'string', 'max:500']], [], ['notes' => 'Catatan check-in']);

        return $this->run(
            fn ($staff) => $this->visits->checkIn($appointment, $staff, $request->user(), $request->input('notes')),
            $request,
            'Check-in berhasil. Silakan lakukan asesmen pasien.',
        );
    }

    public function startService(Request $request, Appointment $appointment): RedirectResponse
    {
        return $this->run(fn ($staff) => $this->visits->startService($appointment, $staff, $request->user()),
            $request, 'Pelayanan dimulai.');
    }

    public function saveAssessment(AssessmentRequest $form, Appointment $appointment): RedirectResponse
    {
        $staff = $this->staffOrFail($form);

        try {
            $this->visits->saveAssessment($appointment, $staff, $form->validated(), $form->user());

            return back()->with('success', 'Asesmen pasien tersimpan.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function complete(CompleteVisitRequest $form, Appointment $appointment): RedirectResponse
    {
        $staff = $this->staffOrFail($form);

        try {
            $this->visits->complete($appointment, $staff, $form->validated(), $form->user());

            return redirect()->route('tugas.show', $appointment)
                ->with('success', 'Kunjungan selesai dan terdokumentasi. Kerja bagus!');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /* ------------------------------------------------------------------ */

    private function run(callable $action, Request $request, string $success): RedirectResponse
    {
        $staff = $this->staffOrFail($request);

        try {
            $action($staff);

            return back()->with('success', $success);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function staffOrFail(Request $request): HealthcareStaff
    {
        $staff = $request->user()->staffProfile;

        abort_if($staff === null, 403, 'Akun Anda belum terhubung ke data tenaga kesehatan. Hubungi koordinator.');

        return $staff;
    }
}
