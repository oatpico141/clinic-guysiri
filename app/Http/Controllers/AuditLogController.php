<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with(['user', 'branch'])->latest();

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by module
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('model_type', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->paginate(50);
        $users = User::orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();

        // Get unique modules for filter
        $modules = AuditLog::select('module')->distinct()->whereNotNull('module')->pluck('module');

        // Stats
        $todayLogs = AuditLog::whereDate('created_at', now())->count();
        $thisWeekLogs = AuditLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $createActions = AuditLog::where('action', 'create')->count();
        $updateActions = AuditLog::where('action', 'update')->count();

        return view('audit-logs.index', compact(
            'logs', 'users', 'branches', 'modules',
            'todayLogs', 'thisWeekLogs', 'createActions', 'updateActions'
        ));
    }

    public function show($id)
    {
        $log = AuditLog::with(['user', 'branch'])->findOrFail($id);

        // Get related logs (same model)
        $relatedLogs = collect();
        if ($log->model_type && $log->model_id) {
            $relatedLogs = AuditLog::where('model_type', $log->model_type)
                ->where('model_id', $log->model_id)
                ->where('id', '!=', $id)
                ->latest()
                ->limit(10)
                ->get();
        }

        return view('audit-logs.show', compact('log', 'relatedLogs'));
    }

    // AuditLog is read-only, no create/update/delete functionality
    public function create() { abort(404); }
    public function store(Request $request) { abort(404); }
    public function edit($id) { abort(404); }
    public function update(Request $request, $id) { abort(404); }
    public function destroy($id) { abort(404); }
}
