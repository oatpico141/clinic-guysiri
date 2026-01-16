<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceLog;
use App\Models\Equipment;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaintenanceLogController extends Controller
{
    /**
     * Display maintenance log list
     */
    public function index(Request $request)
    {
        $query = MaintenanceLog::with(['equipment', 'branch']);

        // Filter by equipment
        if ($request->filled('equipment_id')) {
            $query->where('equipment_id', $request->equipment_id);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by maintenance type
        if ($request->filled('maintenance_type')) {
            $query->where('maintenance_type', $request->maintenance_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('maintenance_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('maintenance_date', '<=', $request->end_date);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('maintenance_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('service_provider', 'like', "%{$search}%")
                  ->orWhereHas('equipment', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $maintenanceLogs = $query->orderBy('maintenance_date', 'desc')->paginate(20);
        $equipments = Equipment::orderBy('name')->get();
        $branches = Branch::all();

        // Stats
        $totalLogs = MaintenanceLog::count();
        $pendingCount = MaintenanceLog::where('status', 'pending')->count();
        $completedCount = MaintenanceLog::where('status', 'completed')->count();
        $totalCost = MaintenanceLog::sum('cost');

        return view('maintenance-logs.index', compact(
            'maintenanceLogs', 'equipments', 'branches',
            'totalLogs', 'pendingCount', 'completedCount', 'totalCost'
        ));
    }

    /**
     * Show create maintenance log form
     */
    public function create(Request $request)
    {
        $equipments = Equipment::orderBy('name')->get();
        $branches = Branch::all();
        $selectedEquipmentId = $request->equipment_id;

        return view('maintenance-logs.create', compact('equipments', 'branches', 'selectedEquipmentId'));
    }

    /**
     * Store new maintenance log
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:equipment,id',
            'branch_id' => 'required|exists:branches,id',
            'maintenance_type' => 'required|in:preventive,corrective,emergency,inspection',
            'maintenance_date' => 'required|date',
            'description' => 'required|string',
            'work_performed' => 'nullable|string',
            'performed_by' => 'nullable|string|max:255',
            'service_provider' => 'nullable|string|max:255',
            'cost' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'next_maintenance_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Generate maintenance number
            $today = now()->format('Ymd');
            $lastLog = MaintenanceLog::whereDate('created_at', today())
                ->orderBy('maintenance_number', 'desc')
                ->first();

            if ($lastLog && preg_match('/MT' . $today . '(\d{4})/', $lastLog->maintenance_number, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            } else {
                $nextNumber = 1;
            }

            $validated['maintenance_number'] = 'MT' . $today . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            $validated['created_by'] = auth()->id();

            $maintenanceLog = MaintenanceLog::create($validated);

            // Update equipment next maintenance date
            if ($validated['next_maintenance_date']) {
                $equipment = Equipment::find($validated['equipment_id']);
                if ($equipment) {
                    $equipment->update(['next_maintenance_date' => $validated['next_maintenance_date']]);
                }
            }

            DB::commit();

            return redirect()
                ->route('maintenance-logs.show', $maintenanceLog)
                ->with('success', 'บันทึกการซ่อมบำรุงสำเร็จ');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Display maintenance log details
     */
    public function show($id)
    {
        $maintenanceLog = MaintenanceLog::with(['equipment', 'branch'])->findOrFail($id);

        // Get other logs for same equipment
        $relatedLogs = MaintenanceLog::where('equipment_id', $maintenanceLog->equipment_id)
            ->where('id', '!=', $id)
            ->orderBy('maintenance_date', 'desc')
            ->limit(5)
            ->get();

        return view('maintenance-logs.show', compact('maintenanceLog', 'relatedLogs'));
    }

    /**
     * Show edit maintenance log form
     */
    public function edit($id)
    {
        $maintenanceLog = MaintenanceLog::findOrFail($id);
        $equipments = Equipment::orderBy('name')->get();
        $branches = Branch::all();

        return view('maintenance-logs.edit', compact('maintenanceLog', 'equipments', 'branches'));
    }

    /**
     * Update maintenance log
     */
    public function update(Request $request, $id)
    {
        $maintenanceLog = MaintenanceLog::findOrFail($id);

        $validated = $request->validate([
            'equipment_id' => 'required|exists:equipment,id',
            'branch_id' => 'required|exists:branches,id',
            'maintenance_type' => 'required|in:preventive,corrective,emergency,inspection',
            'maintenance_date' => 'required|date',
            'description' => 'required|string',
            'work_performed' => 'nullable|string',
            'performed_by' => 'nullable|string|max:255',
            'service_provider' => 'nullable|string|max:255',
            'cost' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'next_maintenance_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        try {
            $maintenanceLog->update($validated);

            // Update equipment next maintenance date
            if ($validated['next_maintenance_date']) {
                $equipment = Equipment::find($validated['equipment_id']);
                if ($equipment) {
                    $equipment->update(['next_maintenance_date' => $validated['next_maintenance_date']]);
                }
            }

            return redirect()
                ->route('maintenance-logs.show', $maintenanceLog)
                ->with('success', 'แก้ไขการซ่อมบำรุงสำเร็จ');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Delete maintenance log
     */
    public function destroy($id)
    {
        try {
            $maintenanceLog = MaintenanceLog::findOrFail($id);
            $maintenanceLog->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบการซ่อมบำรุงสำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }
}
