<?php

namespace App\Http\Controllers\Operational;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->date('tanggal') ?? now();
        $status = $request->query('status');

        $appointments = Appointment::query()
            ->with(['request.patient', 'request.user', 'assignments.staff'])
            ->whereDate('scheduled_at', $date->toDateString())
            ->when($status && AppointmentStatus::tryFrom($status), fn ($q) => $q->where('status', $status))
            ->orderBy('scheduled_at')
            ->paginate(20)
            ->withQueryString();

        return view('operational.appointments.index', [
            'appointments' => $appointments,
            'date' => $date,
            'statuses' => collect(AppointmentStatus::cases()),
            'currentStatus' => $status,
        ]);
    }

    public function show(Request $request, Appointment $appointment): View
    {
        Gate::authorize('view', $appointment);

        $appointment->load([
            'request.patient.contacts',
            'request.items',
            'request.user',
            'assignments.staff',
            'assessments.staff',
            'serviceRecords.staff',
            'clinicalNotes.staff',
            'attachments',
        ]);

        return view('operational.appointments.show', ['appointment' => $appointment]);
    }
}
