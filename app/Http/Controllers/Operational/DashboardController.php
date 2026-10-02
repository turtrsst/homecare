<?php

namespace App\Http\Controllers\Operational;

use App\Enums\AppointmentStatus;
use App\Enums\HomecareRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\HomecareRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $today = now()->toDateString();

        $counts = [
            'menunggu_verifikasi' => HomecareRequest::query()
                ->status([HomecareRequestStatus::Submitted, HomecareRequestStatus::UnderReview])->count(),
            'perlu_tindak_lanjut' => HomecareRequest::query()
                ->status([HomecareRequestStatus::NeedInformation, HomecareRequestStatus::Approved])->count(),
            'terjadwal_hari_ini' => Appointment::query()
                ->scheduledOn($today)
                ->whereIn('status', [AppointmentStatus::Scheduled->value, AppointmentStatus::OnTheWay->value])
                ->count(),
            'sedang_berlangsung' => Appointment::query()
                ->whereIn('status', [AppointmentStatus::CheckedIn->value, AppointmentStatus::InService->value])
                ->count(),
            'selesai_hari_ini' => Appointment::query()
                ->scheduledOn($today)
                ->where('status', AppointmentStatus::Completed->value)
                ->count(),
        ];

        // Antrean kerja: pengajuan yang menunggu keputusan.
        $queue = HomecareRequest::query()
            ->with(['patient', 'items', 'user'])
            ->status([
                HomecareRequestStatus::Submitted,
                HomecareRequestStatus::UnderReview,
                HomecareRequestStatus::NeedInformation,
                HomecareRequestStatus::Approved,
            ])
            ->orderBy('submitted_at')
            ->take(8)
            ->get();

        $todayAppointments = Appointment::query()
            ->with(['request.patient', 'assignments.staff'])
            ->scheduledOn($today)
            ->orderBy('scheduled_at')
            ->take(8)
            ->get();

        return view('operational.dashboard', [
            'counts' => $counts,
            'queue' => $queue,
            'todayAppointments' => $todayAppointments,
        ]);
    }
}
