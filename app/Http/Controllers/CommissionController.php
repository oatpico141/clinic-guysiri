<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommissionController extends Controller
{
    /**
     * Display commission list
     */
    public function index(Request $request)
    {
        $query = Commission::with(['pt', 'invoice.patient', 'treatment', 'branch']);

        // Filter by PT
        if ($request->filled('pt_id')) {
            $query->where('pt_id', $request->pt_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by commission type
        if ($request->filled('commission_type')) {
            $query->where('commission_type', $request->commission_type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('commission_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('commission_date', '<=', $request->date_to);
        }

        // Default: current month if no date filter
        if (!$request->filled('date_from') && !$request->filled('date_to')) {
            $query->whereMonth('commission_date', now()->month)
                  ->whereYear('commission_date', now()->year);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('commission_number', 'like', "%{$search}%")
                  ->orWhereHas('pt', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $commissions = $query->orderBy('commission_date', 'desc')
                             ->orderBy('created_at', 'desc')
                             ->paginate(20);

        // Get PTs for filter dropdown
        $pts = User::whereHas('roles', function($q) {
            $q->where('name', 'pt');
        })->orWhere('username', 'like', '%pt%')->get();

        // Stats
        $statsQuery = Commission::query();
        if ($request->filled('date_from')) {
            $statsQuery->whereDate('commission_date', '>=', $request->date_from);
        } else {
            $statsQuery->whereMonth('commission_date', now()->month)
                       ->whereYear('commission_date', now()->year);
        }
        if ($request->filled('date_to')) {
            $statsQuery->whereDate('commission_date', '<=', $request->date_to);
        }

        $totalPending = (clone $statsQuery)->where('status', 'pending')->sum('commission_amount');
        $totalApproved = (clone $statsQuery)->where('status', 'approved')->sum('commission_amount');
        $totalPaid = (clone $statsQuery)->where('status', 'paid')->sum('commission_amount');
        $totalClawedBack = (clone $statsQuery)->where('status', 'clawed_back')->sum('commission_amount');

        $pendingCount = (clone $statsQuery)->where('status', 'pending')->count();
        $approvedCount = (clone $statsQuery)->where('status', 'approved')->count();

        return view('commissions.index', compact(
            'commissions', 'pts',
            'totalPending', 'totalApproved', 'totalPaid', 'totalClawedBack',
            'pendingCount', 'approvedCount'
        ));
    }

    /**
     * Display commission details
     */
    public function show($id)
    {
        $commission = Commission::with([
            'pt',
            'invoice.patient',
            'invoice.items',
            'invoiceItem',
            'treatment.service',
            'branch',
            'splits.pt',
            'clawbackRefund'
        ])->findOrFail($id);

        return view('commissions.show', compact('commission'));
    }

    /**
     * Update commission (status, notes)
     */
    public function update(Request $request, $id)
    {
        $commission = Commission::findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,approved,paid,clawed_back',
            'notes' => 'nullable|string',
            'payment_reference' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Handle status changes
            if (isset($validated['status'])) {
                if ($validated['status'] === 'paid' && $commission->status !== 'paid') {
                    $validated['paid_at'] = now();
                    $validated['paid_by'] = auth()->id();
                }

                if ($validated['status'] === 'clawed_back' && $commission->status !== 'clawed_back') {
                    $validated['clawed_back_at'] = now();
                    $validated['clawed_back_by'] = auth()->id();
                }
            }

            $commission->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'อัพเดทคอมมิชชั่นสำเร็จ',
                'commission' => $commission->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark commission as paid
     */
    public function markAsPaid(Request $request, $id)
    {
        $commission = Commission::findOrFail($id);

        if ($commission->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'คอมมิชชั่นนี้จ่ายแล้ว'
            ], 400);
        }

        if ($commission->status === 'clawed_back') {
            return response()->json([
                'success' => false,
                'message' => 'ไม่สามารถจ่ายคอมมิชชั่นที่ถูก clawback แล้ว'
            ], 400);
        }

        try {
            $commission->update([
                'status' => 'paid',
                'paid_at' => now(),
                'paid_by' => auth()->id(),
                'payment_reference' => $request->payment_reference,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'บันทึกการจ่ายคอมมิชชั่นสำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk mark as paid
     */
    public function bulkPay(Request $request)
    {
        $validated = $request->validate([
            'commission_ids' => 'required|array',
            'commission_ids.*' => 'exists:commissions,id',
            'payment_reference' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $updated = Commission::whereIn('id', $validated['commission_ids'])
                ->whereIn('status', ['pending', 'approved'])
                ->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'paid_by' => auth()->id(),
                    'payment_reference' => $validated['payment_reference'] ?? null,
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "จ่ายคอมมิชชั่น {$updated} รายการสำเร็จ"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clawback commission
     */
    public function clawback(Request $request, $id)
    {
        $commission = Commission::findOrFail($id);

        if (!$commission->is_clawback_eligible) {
            return response()->json([
                'success' => false,
                'message' => 'คอมมิชชั่นนี้ไม่สามารถ clawback ได้'
            ], 400);
        }

        if ($commission->status === 'clawed_back') {
            return response()->json([
                'success' => false,
                'message' => 'คอมมิชชั่นนี้ถูก clawback แล้ว'
            ], 400);
        }

        $validated = $request->validate([
            'clawback_reason' => 'required|string|min:5',
        ]);

        try {
            $commission->update([
                'status' => 'clawed_back',
                'clawed_back_at' => now(),
                'clawed_back_by' => auth()->id(),
                'clawback_reason' => $validated['clawback_reason'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Clawback คอมมิชชั่นสำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete commission
     */
    public function destroy($id)
    {
        try {
            $commission = Commission::findOrFail($id);
            $commission->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบคอมมิชชั่นสำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get PT commission summary
     */
    public function ptSummary(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $summary = Commission::select('pt_id')
            ->selectRaw('SUM(commission_amount) as total_commission')
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('SUM(CASE WHEN status = "pending" THEN commission_amount ELSE 0 END) as pending_amount')
            ->selectRaw('SUM(CASE WHEN status = "paid" THEN commission_amount ELSE 0 END) as paid_amount')
            ->whereMonth('commission_date', $month)
            ->whereYear('commission_date', $year)
            ->groupBy('pt_id')
            ->with('pt')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }
}