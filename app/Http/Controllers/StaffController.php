<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    /**
     * Display staff list
     */
    public function index(Request $request)
    {
        $query = Staff::with(['user', 'branch']);

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by position
        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }

        // Filter by employment status
        if ($request->filled('employment_status')) {
            $query->where('employment_status', $request->employment_status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $staffs = $query->orderBy('first_name')->paginate(20);
        $branches = Branch::all();

        // Stats
        $totalStaff = Staff::count();
        $activeCount = Staff::where('employment_status', 'active')->count();
        $ptCount = Staff::where('position', 'pt')->where('employment_status', 'active')->count();
        $onLeaveCount = Staff::where('employment_status', 'on_leave')->count();

        return view('staff.index', compact(
            'staffs', 'branches',
            'totalStaff', 'activeCount', 'ptCount', 'onLeaveCount'
        ));
    }

    /**
     * Show create staff form
     */
    public function create()
    {
        $branches = Branch::all();
        $users = User::whereDoesntHave('staff')->get();

        return view('staff.create', compact('branches', 'users'));
    }

    /**
     * Store new staff
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id|unique:staff,user_id',
            'branch_id' => 'required|exists:branches,id',
            'employee_id' => 'required|string|max:50|unique:staff,employee_id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'position' => 'required|in:pt,receptionist,manager,admin,nurse,assistant',
            'department' => 'nullable|string|max:100',
            'hire_date' => 'required|date',
            'employment_status' => 'required|in:active,on_leave,terminated',
            'employment_type' => 'required|in:full_time,part_time,contract',
            'license_number' => 'nullable|string|max:100',
            'license_expiry' => 'nullable|date',
            'base_salary' => 'nullable|numeric|min:0',
            'salary_type' => 'nullable|in:monthly,hourly,commission_only',
            'notes' => 'nullable|string',
        ]);

        try {
            $validated['created_by'] = auth()->id();

            $staff = Staff::create($validated);

            return redirect()
                ->route('staff.show', $staff)
                ->with('success', 'เพิ่มพนักงานสำเร็จ');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Display staff details
     */
    public function show($id)
    {
        $staff = Staff::with([
            'user',
            'branch',
            'schedules' => function($q) {
                $q->where('schedule_date', '>=', now()->subDays(7))
                  ->orderBy('schedule_date', 'desc');
            },
            'leaveRequests' => function($q) {
                $q->orderBy('created_at', 'desc')->limit(10);
            },
            'evaluations' => function($q) {
                $q->orderBy('evaluation_date', 'desc')->limit(5);
            }
        ])->findOrFail($id);

        return view('staff.show', compact('staff'));
    }

    /**
     * Show edit staff form
     */
    public function edit($id)
    {
        $staff = Staff::findOrFail($id);
        $branches = Branch::all();
        $users = User::whereDoesntHave('staff')
                     ->orWhere('id', $staff->user_id)
                     ->get();

        return view('staff.edit', compact('staff', 'branches', 'users'));
    }

    /**
     * Update staff
     */
    public function update(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id|unique:staff,user_id,' . $id,
            'branch_id' => 'required|exists:branches,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'position' => 'required|in:pt,receptionist,manager,admin,nurse,assistant',
            'department' => 'nullable|string|max:100',
            'hire_date' => 'required|date',
            'termination_date' => 'nullable|date',
            'employment_status' => 'required|in:active,on_leave,terminated',
            'employment_type' => 'required|in:full_time,part_time,contract',
            'license_number' => 'nullable|string|max:100',
            'license_expiry' => 'nullable|date',
            'base_salary' => 'nullable|numeric|min:0',
            'salary_type' => 'nullable|in:monthly,hourly,commission_only',
            'notes' => 'nullable|string',
        ]);

        try {
            $staff->update($validated);

            return redirect()
                ->route('staff.show', $staff)
                ->with('success', 'แก้ไขข้อมูลพนักงานสำเร็จ');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete staff
     */
    public function destroy($id)
    {
        try {
            $staff = Staff::findOrFail($id);
            $staff->employment_status = 'terminated';
            $staff->termination_date = now();
            $staff->save();
            $staff->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบข้อมูลพนักงานสำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }
}
