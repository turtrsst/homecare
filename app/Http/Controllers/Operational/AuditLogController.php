<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $logs = AuditLog::query()
            ->with(['user', 'auditable'])
            ->when($search, fn ($q) => $q->where('event', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%"))
            ->latest('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('operational.audit.index', [
            'logs' => $logs,
            'search' => $search,
        ]);
    }
}
