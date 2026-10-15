<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\FreelancerAuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class FreelancerAuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = FreelancerAuditLog::query()
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->get('action')))
            ->when($request->filled('actor_type'), fn ($q) => $q->where('actor_type', $request->get('actor_type')))
            ->when($request->filled('subject_type'), fn ($q) => $q->where('subject_type', 'like', '%' . $request->get('subject_type') . '%'))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->get('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->get('date_to')))
            ->latest()
            ->paginate((int) (getWebConfig(name: 'pagination_limit') ?? 25))
            ->appends($request->query());

        $actions = FreelancerAuditLog::query()->distinct()->orderBy('action')->pluck('action');

        return view('admin-views.freelancer.audit-logs.index', compact('logs', 'actions'));
    }
}
