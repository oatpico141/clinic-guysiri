<?php

namespace App\Http\Controllers;

use App\Models\TreatmentAuditLog;
use App\Models\Treatment;
use App\Models\Staff;
use Illuminate\Http\Request;

class TreatmentAuditLogController extends Controller
{
    /**
     * Display audit log list
     */
    public function index(Request $request)
    {
        $query = TreatmentAuditLog::with(['treatment.patient', 'treatment.service', 'performedBy']);

        // Filter by treatment
        if ($request->filled('treatment_id')) {
            $query->where('treatment_id', $request->treatment_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by user
        if ($request->filled('performed_by')) {
            $query->where('performed_by', $request->performed_by);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('field_name', 'like', "%{$search}%")
                  ->orWhere('old_value', 'like', "%{$search}%")
                  ->orWhere('new_value', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        $auditLogs = $query->orderBy('created_at', 'desc')->paginate(30);

        // Stats
        $totalLogs = TreatmentAuditLog::count();
        $todayCount = TreatmentAuditLog::whereDate('created_at', today())->count();
        $createCount = TreatmentAuditLog::where('action', 'create')->count();
        $updateCount = TreatmentAuditLog::where('action', 'update')->count();

        // Get action types for filter
        $actions = TreatmentAuditLog::distinct()->pluck('action')->filter();

        return view('treatment-audit-logs.index', compact(
            'auditLogs', 'actions',
            'totalLogs', 'todayCount', 'createCount', 'updateCount'
        ));
    }

    /**
     * Display audit log details
     */
    public function show($id)
    {
        $auditLog = TreatmentAuditLog::with([
            'treatment.patient',
            'treatment.service',
            'treatment.pt',
            'performedBy'
        ])->findOrFail($id);

        // Get related logs for same treatment
        $relatedLogs = TreatmentAuditLog::where('treatment_id', $auditLog->treatment_id)
            ->where('id', '!=', $id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('treatment-audit-logs.show', compact('auditLog', 'relatedLogs'));
    }

    /**
     * Get audit history for a specific treatment
     */
    public function treatmentHistory($treatmentId)
    {
        $treatment = Treatment::with(['patient', 'service', 'pt'])->findOrFail($treatmentId);

        $auditLogs = TreatmentAuditLog::where('treatment_id', $treatmentId)
            ->with('performedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('treatment-audit-logs.treatment-history', compact('treatment', 'auditLogs'));
    }

    // Note: create, store, edit, update, destroy methods are not implemented
    // because audit logs should be created automatically by the system, not manually
}
