<?php

namespace App\Http\Controllers;

use App\Models\DfPayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DfPaymentController extends Controller
{
    /**
     * Display DF payment list
     */
    public function index(Request $request)
    {
        $query = DfPayment::with(['pt', 'treatment.patient', 'treatment.service', 'service', 'branch']);

        // Filter by PT
        if ($request->filled('pt_id')) {
            $query->where('pt_id', $request->pt_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by source type
        if ($request->filled('source_type')) {
            $query->where('source_type', $request->source_type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('df_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('df_date', '<=', $request->date_to);
        }

        // Default: current month if no date filter
        if (!$request->filled('date_from') && !$request->filled('date_to')) {
            $query->whereMonth('df_date', now()->month)
                  ->whereYear('df_date', now()->year);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('df_number', 'like', "%{$search}%")
                  ->orWhereHas('pt', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $dfPayments = $query->orderBy('df_date', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->paginate(20);

        // Get PTs for filter dropdown
        $pts = User::whereHas('roles', function($q) {
            $q->where('name', 'pt');
        })->orWhere('username', 'like', '%pt%')->get();

        // Stats
        $statsQuery = DfPayment::query();
        if ($request->filled('date_from')) {
            $statsQuery->whereDate('df_date', '>=', $request->date_from);
        } else {
            $statsQuery->whereMonth('df_date', now()->month)
                       ->whereYear('df_date', now()->year);
        }
        if ($request->filled('date_to')) {
            $statsQuery->whereDate('df_date', '<=', $request->date_to);
        }

        $totalPending = (clone $statsQuery)->where('status', 'pending')->sum('df_amount');
        $totalApproved = (clone $statsQuery)->where('status', 'approved')->sum('df_amount');
        $totalPaid = (clone $statsQuery)->where('status', 'paid')->sum('df_amount');

        $pendingCount = (clone $statsQuery)->where('status', 'pending')->count();
        $approvedCount = (clone $statsQuery)->where('status', 'approved')->count();

        return view('df-payments.index', compact(
            'dfPayments', 'pts',
            'totalPending', 'totalApproved', 'totalPaid',
            'pendingCount', 'approvedCount'
        ));
    }

    /**
     * Display DF payment details
     */
    public function show($id)
    {
        $dfPayment = DfPayment::with([
            'pt',
            'treatment.patient',
            'treatment.service',
            'service',
            'coursePurchase.package',
            'branch'
        ])->findOrFail($id);

        return view('df-payments.show', compact('dfPayment'));
    }

    /**
     * Update DF payment
     */
    public function update(Request $request, $id)
    {
        $dfPayment = DfPayment::findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,approved,paid',
            'notes' => 'nullable|string',
            'payment_reference' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Handle status changes
            if (isset($validated['status'])) {
                if ($validated['status'] === 'paid' && $dfPayment->status !== 'paid') {
                    $validated['paid_at'] = now();
                    $validated['paid_by'] = auth()->id();
                }
            }

            $dfPayment->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'อัพเดทค่ามือสำเร็จ',
                'dfPayment' => $dfPayment->fresh()
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
     * Mark DF payment as paid
     */
    public function markAsPaid(Request $request, $id)
    {
        $dfPayment = DfPayment::findOrFail($id);

        if ($dfPayment->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'ค่ามือนี้จ่ายแล้ว'
            ], 400);
        }

        try {
            $dfPayment->update([
                'status' => 'paid',
                'paid_at' => now(),
                'paid_by' => auth()->id(),
                'payment_reference' => $request->payment_reference,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'บันทึกการจ่ายค่ามือสำเร็จ'
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
            'df_payment_ids' => 'required|array',
            'df_payment_ids.*' => 'exists:df_payments,id',
            'payment_reference' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $updated = DfPayment::whereIn('id', $validated['df_payment_ids'])
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
                'message' => "จ่ายค่ามือ {$updated} รายการสำเร็จ"
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
     * Soft delete DF payment
     */
    public function destroy($id)
    {
        try {
            $dfPayment = DfPayment::findOrFail($id);
            $dfPayment->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบค่ามือสำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get PT DF summary
     */
    public function ptSummary(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $summary = DfPayment::select('pt_id')
            ->selectRaw('SUM(df_amount) as total_df')
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('SUM(CASE WHEN status = "pending" THEN df_amount ELSE 0 END) as pending_amount')
            ->selectRaw('SUM(CASE WHEN status = "paid" THEN df_amount ELSE 0 END) as paid_amount')
            ->whereMonth('df_date', $month)
            ->whereYear('df_date', $year)
            ->groupBy('pt_id')
            ->with('pt')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }
}
