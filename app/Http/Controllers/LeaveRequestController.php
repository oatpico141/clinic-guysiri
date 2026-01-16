<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\Staff;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    /**
     * Display leave request list
     */
    public function index(Request $request)
    {
        $query = LeaveRequest::with(['staff', 'branch', 'approvedBy']);

        // Filter by staff
        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by leave type
        if ($request->filled('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }

        $leaveRequests = $query->orderBy('created_at', 'desc')->paginate(20);
        $staffs = Staff::where('employment_status', 'active')->orderBy('first_name')->get();
        $branches = Branch::all();

        // Stats
        $totalRequests = LeaveRequest::count();
        $pendingCount = LeaveRequest::where('status', 'pending')->count();
        $approvedCount = LeaveRequest::where('status', 'approved')->count();
        $rejectedCount = LeaveRequest::where('status', 'rejected')->count();

        return view('leave-requests.index', compact(
            'leaveRequests', 'staffs', 'branches',
            'totalRequests', 'pendingCount', 'approvedCount', 'rejectedCount'
        ));
    }

    /**
     * Show create leave request form
     */
    public function create()
    {
        $staffs = Staff::where('employment_status', 'active')->orderBy('first_name')->get();
        $branches = Branch::all();

        return view('leave-requests.create', compact('staffs', 'branches'));
    }

    /**
     * Store new leave request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'branch_id' => 'required|exists:branches,id',
            'leave_type' => 'required|in:annual,sick,personal,maternity,unpaid,other',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            // Calculate total days
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            $totalDays = $startDate->diffInDays($endDate) + 1;

            // Generate leave number
            $prefix = 'LV';
            $date = now()->format('Ymd');
            $lastLeave = LeaveRequest::whereDate('created_at', now())->count() + 1;
            $leaveNumber = $prefix . $date . str_pad($lastLeave, 4, '0', STR_PAD_LEFT);

            $leaveRequest = LeaveRequest::create([
                'leave_number' => $leaveNumber,
                'staff_id' => $validated['staff_id'],
                'branch_id' => $validated['branch_id'],
                'leave_type' => $validated['leave_type'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'total_days' => $totalDays,
                'status' => 'pending',
                'reason' => $validated['reason'],
                'submitted_at' => now(),
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()
                ->route('leave-requests.show', $leaveRequest)
                ->with('success', 'ส่งคำขอลาสำเร็จ');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Display leave request details
     */
    public function show($id)
    {
        $leaveRequest = LeaveRequest::with(['staff', 'branch', 'approvedBy'])->findOrFail($id);

        return view('leave-requests.show', compact('leaveRequest'));
    }

    /**
     * Show edit leave request form
     */
    public function edit($id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        if ($leaveRequest->status !== 'pending') {
            return redirect()
                ->route('leave-requests.show', $leaveRequest)
                ->with('error', 'ไม่สามารถแก้ไขคำขอที่ดำเนินการแล้ว');
        }

        $staffs = Staff::where('employment_status', 'active')->orderBy('first_name')->get();
        $branches = Branch::all();

        return view('leave-requests.edit', compact('leaveRequest', 'staffs', 'branches'));
    }

    /**
     * Update leave request
     */
    public function update(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        if ($leaveRequest->status !== 'pending') {
            return redirect()
                ->route('leave-requests.show', $leaveRequest)
                ->with('error', 'ไม่สามารถแก้ไขคำขอที่ดำเนินการแล้ว');
        }

        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'branch_id' => 'required|exists:branches,id',
            'leave_type' => 'required|in:annual,sick,personal,maternity,unpaid,other',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
        ]);

        try {
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            $validated['total_days'] = $startDate->diffInDays($endDate) + 1;

            $leaveRequest->update($validated);

            return redirect()
                ->route('leave-requests.show', $leaveRequest)
                ->with('success', 'แก้ไขคำขอลาสำเร็จ');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Delete leave request
     */
    public function destroy($id)
    {
        try {
            $leaveRequest = LeaveRequest::findOrFail($id);

            if ($leaveRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถลบคำขอที่ดำเนินการแล้ว'
                ], 400);
            }

            $leaveRequest->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบคำขอลาสำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve leave request
     */
    public function approve(Request $request, $id)
    {
        try {
            $leaveRequest = LeaveRequest::findOrFail($id);

            if ($leaveRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'คำขอนี้ดำเนินการแล้ว'
                ], 400);
            }

            $leaveRequest->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
                'approval_notes' => $request->input('approval_notes'),
            ]);

            // Update staff employment_status if needed
            $staff = $leaveRequest->staff;
            if ($staff && $leaveRequest->start_date <= now() && $leaveRequest->end_date >= now()) {
                $staff->update(['employment_status' => 'on_leave']);
            }

            return response()->json([
                'success' => true,
                'message' => 'อนุมัติคำขอลาสำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject leave request
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        try {
            $leaveRequest = LeaveRequest::findOrFail($id);

            if ($leaveRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'คำขอนี้ดำเนินการแล้ว'
                ], 400);
            }

            $leaveRequest->update([
                'status' => 'rejected',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
                'rejection_reason' => $request->input('rejection_reason'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'ปฏิเสธคำขอลาสำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get leave summary for staff
     */
    public function staffSummary($staffId)
    {
        $staff = Staff::findOrFail($staffId);
        $year = now()->year;

        $summary = [
            'annual' => [
                'used' => LeaveRequest::where('staff_id', $staffId)
                    ->where('leave_type', 'annual')
                    ->where('status', 'approved')
                    ->whereYear('start_date', $year)
                    ->sum('total_days'),
                'allowance' => 10, // Default annual leave
            ],
            'sick' => [
                'used' => LeaveRequest::where('staff_id', $staffId)
                    ->where('leave_type', 'sick')
                    ->where('status', 'approved')
                    ->whereYear('start_date', $year)
                    ->sum('total_days'),
                'allowance' => 30, // Default sick leave
            ],
            'personal' => [
                'used' => LeaveRequest::where('staff_id', $staffId)
                    ->where('leave_type', 'personal')
                    ->where('status', 'approved')
                    ->whereYear('start_date', $year)
                    ->sum('total_days'),
                'allowance' => 3, // Default personal leave
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }
}
