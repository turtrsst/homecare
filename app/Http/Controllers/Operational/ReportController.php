<?php

namespace App\Http\Controllers\Operational;

use App\Enums\HomecareRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\HomecareRequest;
use App\Models\HomecareService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $from = $this->parseDate($request, 'from') ?? now()->subDays(30)->startOfDay();
        $to = $this->parseDate($request, 'to') ?? now();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        $base = fn () => HomecareRequest::query()
            ->whereBetween('submitted_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);

        $byStatus = $base()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $byService = HomecareRequest::query()
            ->whereHas('items')
            ->whereBetween('submitted_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->join('homecare_request_items', 'homecare_request_items.homecare_request_id', '=', 'homecare_requests.id')
            ->groupBy('homecare_request_items.service_name')
            ->select('homecare_request_items.service_name', DB::raw('SUM(homecare_request_items.quantity) as total'))
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $completedVisits = Appointment::query()
            ->where('status', 'completed')
            ->whereBetween('scheduled_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->count();

        $revenue = HomecareRequest::query()
            ->where('payment_status', 'paid')
            ->whereBetween('submitted_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->sum('total_amount');

        return view('operational.reports.index', [
            'from' => $from,
            'to' => $to,
            'totals' => [
                'requests' => $base()->count(),
                'completed' => (int) ($byStatus[HomecareRequestStatus::Completed->value] ?? 0),
                'cancelled' => (int) ($byStatus[HomecareRequestStatus::Cancelled->value] ?? 0),
                'rejected' => (int) ($byStatus[HomecareRequestStatus::Rejected->value] ?? 0),
                'visits' => $completedVisits,
                'revenue' => (float) $revenue,
            ],
            'byStatus' => $byStatus,
            'byService' => $byService,
            'statuses' => HomecareRequestStatus::cases(),
        ]);
    }

    /**
     * Baca query string ?from=&to= dengan aman. Request::date() menulis
     * argumen kedua sebagai FORMAT (string), bukan default — mengirim objek
     * Carbon ke sana memicu TypeError di Carbon::rawCreateFromFormat().
     */
    private function parseDate(Request $request, string $key): ?Carbon
    {
        $value = $request->query($key);

        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
