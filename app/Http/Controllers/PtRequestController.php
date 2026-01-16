<?php

namespace App\Http\Controllers;

use App\Models\PtRequest;
use App\Models\Staff;
use App\Models\Patient;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PtRequestController extends Controller
{
    /**
     * Display PT request list
     */
    public function index(Request $request)
    {
        $query = PtRequest::with(['patient', 'branch', 'originalPt', 'requestedPt', 'processedBy']);

        // Filter by patient
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        // Filter by requested PT
        if ($request->filled('requested_pt_id')) {
            $query->where('requested_pt_id', $request->requested_pt_id);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $ptRequests = $query->orderBy('requested_at', 'desc')->paginate(20);
        $pts = Staff::where('position', 'pt')->where('employment_status', 'active')->orderBy('first_name')->get();
        $branches = Branch::all();

        // Stats
        $totalRequests = PtRequest::count();
        $pendingCount = PtRequest::where('status', 'pending')->count();
        $approvedCount = PtRequest::where('status', 'approved')->count();
        $rejectedCount = PtRequest::where('status', 'rejected')->count();

        return view('pt-requests.index', compact(
            'ptRequests', 'pts', 'branches',
            'totalRequests', 'pendingCount', 'approvedCount', 'rejectedCount'
        ));
    }

    /**
     * Show create PT request form
     */
    public function create()
    {
        $pts = Staff::where('position', 'pt')->where('employment_status', 'active')->orderBy('first_name')->get();
        $patients = Patient::orderBy('name')->limit(100)->get();
        $branches = Branch::all();

        return view('pt-requests.create', compact('pts', 'patients', 'branches'));
    }

    /**
     * Store new PT request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'branch_id' => 'required|exists:branches,id',
            'original_pt_id' => 'nullable|exists:staff,id',
            'requested_pt_id' => 'required|exists:staff,id',
            'reason' => 'required|string',
        ]);

        try {
            $validated['status'] = 'pending';
            $validated['requested_at'] = now();
            $validated['created_by'] = auth()->id();

            $ptRequest = PtRequest::create($validated);

            return redirect()
                ->route('pt-requests.show', $ptRequest)
                ->with('success', 'ส่งคำขอเปลี่ยน PT สำเร็จ');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Display PT request details
     */
    public function show($id)
    {
        $ptRequest = PtRequest::with(['patient', 'branch', 'originalPt', 'requestedPt', 'processedBy'])->findOrFail($id);

        return view('pt-requests.show', compact('ptRequest'));
    }

    /**
     * Show edit PT request form
     */
    public function edit($id)
    {
        $ptRequest = PtRequest::findOrFail($id);

        if ($ptRequest->status !== 'pending') {
            return redirect()
                ->route('pt-requests.show', $ptRequest)
                ->with('error', 'ไม่สามารถแก้ไขคำขอที่ดำเนินการแล้ว');
        }

        $pts = Staff::where('position', 'pt')->where('employment_status', 'active')->orderBy('first_name')->get();
        $patients = Patient::orderBy('name')->limit(100)->get();
        $branches = Branch::all();

        return view('pt-requests.edit', compact('ptRequest', 'pts', 'patients', 'branches'));
    }

    /**
     * Update PT request
     */
    public function update(Request $request, $id)
    {
        $ptRequest = PtRequest::findOrFail($id);

        if ($ptRequest->status !== 'pending') {
            return redirect()
                ->route('pt-requests.show', $ptRequest)
                ->with('error', 'ไม่สามารถแก้ไขคำขอที่ดำเนินการแล้ว');
        }

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'branch_id' => 'required|exists:branches,id',
            'original_pt_id' => 'nullable|exists:staff,id',
            'requested_pt_id' => 'required|exists:staff,id',
            'reason' => 'required|string',
        ]);

        try {
            $ptRequest->update($validated);

            return redirect()
                ->route('pt-requests.show', $ptRequest)
                ->with('success', 'แก้ไขคำขอเปลี่ยน PT สำเร็จ');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Delete PT request
     */
    public function destroy($id)
    {
        try {
            $ptRequest = PtRequest::findOrFail($id);

            if ($ptRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถลบคำขอที่ดำเนินการแล้ว'
                ], 400);
            }

            $ptRequest->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบคำขอเปลี่ยน PT สำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve PT request
     */
    public function approve(Request $request, $id)
    {
        try {
            $ptRequest = PtRequest::findOrFail($id);

            if ($ptRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'คำขอนี้ดำเนินการแล้ว'
                ], 400);
            }

            $ptRequest->update([
                'status' => 'approved',
                'processed_at' => now(),
                'processed_by' => auth()->id(),
            ]);

            // Update patient's preferred PT
            if ($ptRequest->patient) {
                $ptRequest->patient->update(['preferred_pt_id' => $ptRequest->requested_pt_id]);
            }

            return response()->json([
                'success' => true,
                'message' => 'อนุมัติคำขอเปลี่ยน PT สำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject PT request
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        try {
            $ptRequest = PtRequest::findOrFail($id);

            if ($ptRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'คำขอนี้ดำเนินการแล้ว'
                ], 400);
            }

            $ptRequest->update([
                'status' => 'rejected',
                'processed_at' => now(),
                'processed_by' => auth()->id(),
                'rejection_reason' => $request->input('rejection_reason'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'ปฏิเสธคำขอเปลี่ยน PT สำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }
}
