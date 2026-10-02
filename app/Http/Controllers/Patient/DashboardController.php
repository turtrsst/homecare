<?php

namespace App\Http\Controllers\Patient;

use App\Enums\HomecareRequestStatus;
use App\Http\Controllers\Controller;
use App\Services\HomecareCatalog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly HomecareCatalog $catalog) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $activeRequest = $user->homecareRequests()
            ->open()
            ->with(['items', 'appointment.assignments.staff', 'patient'])
            ->latest('updated_at')
            ->first();

        $recentRequests = $user->homecareRequests()
            ->with(['items', 'patient'])
            ->where('status', '!=', HomecareRequestStatus::Draft->value)
            ->latest('submitted_at')
            ->take(4)
            ->get();

        return view('patient.dashboard', [
            'user' => $user,
            'activeRequest' => $activeRequest,
            'recentRequests' => $recentRequests,
            'services' => $this->catalog->activeServices()->take(6),
            'unreadNotifications' => $user->unreadNotifications()->count(),
        ]);
    }
}
