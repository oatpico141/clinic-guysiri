<?php

namespace App\Http\Controllers;

use App\Models\CourseRenewal;
use App\Models\CoursePurchase;
use App\Models\Patient;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CourseRenewalController extends Controller
{
    public function index(Request $request)
    {
        $query = CourseRenewal::with(['coursePurchase.coursePackage', 'patient', 'branch'])->latest();

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by patient
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('renewal_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('renewal_date', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('renewal_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('coursePurchase.coursePackage', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $renewals = $query->paginate(20);
        $branches = Branch::orderBy('name')->get();

        // Stats
        $totalRenewals = CourseRenewal::count();
        $thisMonthRenewals = CourseRenewal::whereMonth('renewal_date', now()->month)->count();
        $totalRevenue = CourseRenewal::whereMonth('renewal_date', now()->month)->sum('renewal_fee');
        $avgExtension = CourseRenewal::avg('extension_days') ?? 0;

        return view('course-renewals.index', compact(
            'renewals', 'branches',
            'totalRenewals', 'thisMonthRenewals', 'totalRevenue', 'avgExtension'
        ));
    }

    public function create(Request $request)
    {
        $branches = Branch::orderBy('name')->get();
        $patients = Patient::orderBy('name')->get();

        // If course_purchase_id is provided, pre-load it
        $selectedPurchase = null;
        if ($request->filled('course_purchase_id')) {
            $selectedPurchase = CoursePurchase::with(['patient', 'coursePackage'])->find($request->course_purchase_id);
        }

        // Get expiring courses (within 30 days or already expired)
        $expiringCourses = CoursePurchase::with(['patient', 'coursePackage'])
            ->where('status', 'active')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->orderBy('expiry_date')
            ->limit(20)
            ->get();

        return view('course-renewals.create', compact('branches', 'patients', 'selectedPurchase', 'expiringCourses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_purchase_id' => 'required|exists:course_purchases,id',
            'branch_id' => 'required|exists:branches,id',
            'renewal_date' => 'required|date',
            'extension_days' => 'required|integer|min:1|max:365',
            'renewal_fee' => 'nullable|numeric|min:0',
            'renewal_reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $coursePurchase = CoursePurchase::findOrFail($validated['course_purchase_id']);

            // Generate renewal number
            $date = now()->format('Ymd');
            $lastRenewal = CourseRenewal::where('renewal_number', 'like', "RNW{$date}%")
                ->orderBy('renewal_number', 'desc')
                ->first();

            if ($lastRenewal) {
                $lastNumber = intval(substr($lastRenewal->renewal_number, -4));
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            $renewalNumber = 'RNW' . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

            // Calculate new expiry date
            $oldExpiryDate = $coursePurchase->expiry_date;
            $newExpiryDate = Carbon::parse($oldExpiryDate)->addDays($validated['extension_days']);

            // Create renewal record
            $renewal = CourseRenewal::create([
                'renewal_number' => $renewalNumber,
                'course_purchase_id' => $validated['course_purchase_id'],
                'patient_id' => $coursePurchase->patient_id,
                'branch_id' => $validated['branch_id'],
                'renewal_date' => $validated['renewal_date'],
                'old_expiry_date' => $oldExpiryDate,
                'new_expiry_date' => $newExpiryDate,
                'extension_days' => $validated['extension_days'],
                'renewal_fee' => $validated['renewal_fee'] ?? 0,
                'renewal_reason' => $validated['renewal_reason'],
                'notes' => $validated['notes'],
                'created_by' => auth()->id(),
            ]);

            // Update course purchase expiry date
            $coursePurchase->update([
                'expiry_date' => $newExpiryDate,
            ]);

            DB::commit();

            return redirect()
                ->route('course-renewals.show', $renewal)
                ->with('success', 'ต่ออายุคอร์สเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $renewal = CourseRenewal::with([
            'coursePurchase.coursePackage',
            'coursePurchase.usageLogs',
            'patient',
            'branch',
            'invoice'
        ])->findOrFail($id);

        return view('course-renewals.show', compact('renewal'));
    }

    public function edit($id)
    {
        $renewal = CourseRenewal::with(['coursePurchase.coursePackage', 'patient'])->findOrFail($id);
        $branches = Branch::orderBy('name')->get();

        return view('course-renewals.edit', compact('renewal', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $renewal = CourseRenewal::findOrFail($id);

        $validated = $request->validate([
            'renewal_fee' => 'nullable|numeric|min:0',
            'renewal_reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $renewal->update($validated);

            return redirect()
                ->route('course-renewals.show', $renewal)
                ->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $renewal = CourseRenewal::findOrFail($id);

            // Revert course purchase expiry date
            $coursePurchase = $renewal->coursePurchase;
            if ($coursePurchase) {
                $coursePurchase->update([
                    'expiry_date' => $renewal->old_expiry_date,
                ]);
            }

            $renewal->delete();

            return response()->json(['success' => true, 'message' => 'ยกเลิกการต่ออายุแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // API: Get course purchase details
    public function getCoursePurchase($id)
    {
        $purchase = CoursePurchase::with(['patient', 'coursePackage'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'purchase' => $purchase,
            'patient' => $purchase->patient,
            'course' => $purchase->coursePackage,
        ]);
    }
}
