<?php

namespace App\Http\Controllers;

use App\Models\OpdRecord;
use App\Models\Patient;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpdRecordController extends Controller
{
    /**
     * Display OPD record list
     */
    public function index(Request $request)
    {
        $query = OpdRecord::with(['patient', 'branch', 'createdBy']);

        // Filter by patient
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by OPD number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('opd_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $opdRecords = $query->orderBy('created_at', 'desc')->paginate(20);
        $branches = Branch::all();

        // Stats
        $totalRecords = OpdRecord::count();
        $activeCount = OpdRecord::where('status', 'active')->count();
        $todayCount = OpdRecord::whereDate('created_at', today())->count();
        $temporaryCount = OpdRecord::where('is_temporary', true)->count();

        return view('opd-records.index', compact(
            'opdRecords', 'branches',
            'totalRecords', 'activeCount', 'todayCount', 'temporaryCount'
        ));
    }

    /**
     * Show create OPD record form
     */
    public function create(Request $request)
    {
        $patients = Patient::orderBy('name')->limit(100)->get();
        $branches = Branch::all();
        $selectedPatientId = $request->patient_id;

        return view('opd-records.create', compact('patients', 'branches', 'selectedPatientId'));
    }

    /**
     * Store new OPD record
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'branch_id' => 'required|exists:branches,id',
            'chief_complaint' => 'nullable|string',
            'is_temporary' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            // Generate OPD number
            $today = now()->format('Ymd');
            $lastOpd = OpdRecord::whereDate('created_at', today())
                ->orderBy('opd_number', 'desc')
                ->first();

            if ($lastOpd && preg_match('/OPD' . $today . '(\d{4})/', $lastOpd->opd_number, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            } else {
                $nextNumber = 1;
            }

            $validated['opd_number'] = 'OPD' . $today . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            $validated['status'] = 'active';
            $validated['is_temporary'] = $request->boolean('is_temporary');
            $validated['created_by'] = auth()->id();

            $opdRecord = OpdRecord::create($validated);

            DB::commit();

            return redirect()
                ->route('opd-records.show', $opdRecord)
                ->with('success', 'สร้าง OPD สำเร็จ: ' . $opdRecord->opd_number);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Display OPD record details
     */
    public function show($id)
    {
        $opdRecord = OpdRecord::with([
            'patient',
            'branch',
            'createdBy',
            'treatments.service',
            'treatments.pt',
            'invoices.payments'
        ])->findOrFail($id);

        return view('opd-records.show', compact('opdRecord'));
    }

    /**
     * Show edit OPD record form
     */
    public function edit($id)
    {
        $opdRecord = OpdRecord::with(['patient', 'branch'])->findOrFail($id);
        $patients = Patient::orderBy('name')->limit(100)->get();
        $branches = Branch::all();

        return view('opd-records.edit', compact('opdRecord', 'patients', 'branches'));
    }

    /**
     * Update OPD record
     */
    public function update(Request $request, $id)
    {
        $opdRecord = OpdRecord::findOrFail($id);

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'branch_id' => 'required|exists:branches,id',
            'status' => 'required|in:active,closed,cancelled',
            'chief_complaint' => 'nullable|string',
            'is_temporary' => 'boolean',
        ]);

        try {
            $validated['is_temporary'] = $request->boolean('is_temporary');
            $opdRecord->update($validated);

            return redirect()
                ->route('opd-records.show', $opdRecord)
                ->with('success', 'แก้ไข OPD สำเร็จ');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Delete OPD record
     */
    public function destroy($id)
    {
        try {
            $opdRecord = OpdRecord::findOrFail($id);

            // Check if has related records
            if ($opdRecord->treatments()->count() > 0 || $opdRecord->invoices()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถลบได้ มีข้อมูลการรักษาหรือใบแจ้งหนี้ที่เกี่ยวข้อง'
                ], 400);
            }

            $opdRecord->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบ OPD สำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Close OPD record
     */
    public function close($id)
    {
        try {
            $opdRecord = OpdRecord::findOrFail($id);

            if ($opdRecord->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'OPD นี้ปิดแล้ว'
                ], 400);
            }

            $opdRecord->update(['status' => 'closed']);

            return response()->json([
                'success' => true,
                'message' => 'ปิด OPD สำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reopen OPD record
     */
    public function reopen($id)
    {
        try {
            $opdRecord = OpdRecord::findOrFail($id);

            if ($opdRecord->status !== 'closed') {
                return response()->json([
                    'success' => false,
                    'message' => 'OPD นี้ไม่ได้ปิด'
                ], 400);
            }

            $opdRecord->update(['status' => 'active']);

            return response()->json([
                'success' => true,
                'message' => 'เปิด OPD อีกครั้งสำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }
}
