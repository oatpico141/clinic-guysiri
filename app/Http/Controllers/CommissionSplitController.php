<?php

namespace App\Http\Controllers;

use App\Models\CommissionSplit;
use App\Models\Commission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommissionSplitController extends Controller
{
    /**
     * Display commission splits list
     */
    public function index(Request $request)
    {
        $query = CommissionSplit::with(['commission.pt', 'commission.invoice.patient', 'pt']);

        // Filter by PT
        if ($request->filled('pt_id')) {
            $query->where('pt_id', $request->pt_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $splits = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get PTs for filter dropdown
        $pts = User::whereHas('roles', function($q) {
            $q->where('name', 'pt');
        })->orWhere('username', 'like', '%pt%')->get();

        return view('commission-splits.index', compact('splits', 'pts'));
    }

    /**
     * Store new commission split
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'commission_id' => 'required|exists:commissions,id',
            'pt_id' => 'required|exists:users,id',
            'split_percentage' => 'required|numeric|min:0|max:100',
            'split_reason' => 'nullable|string|in:primary_pt,assistant_pt,supervisor',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $commission = Commission::findOrFail($validated['commission_id']);

            // Calculate split amount
            $splitAmount = $commission->commission_amount * ($validated['split_percentage'] / 100);

            $split = CommissionSplit::create([
                'commission_id' => $validated['commission_id'],
                'pt_id' => $validated['pt_id'],
                'split_percentage' => $validated['split_percentage'],
                'split_amount' => $splitAmount,
                'split_reason' => $validated['split_reason'] ?? null,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'เพิ่มการแบ่งคอมมิชชั่นสำเร็จ',
                'split' => $split->load('pt')
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
     * Display commission split details
     */
    public function show($id)
    {
        $split = CommissionSplit::with([
            'commission.pt',
            'commission.invoice.patient',
            'pt'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'split' => $split
        ]);
    }

    /**
     * Update commission split
     */
    public function update(Request $request, $id)
    {
        $split = CommissionSplit::findOrFail($id);

        $validated = $request->validate([
            'split_percentage' => 'sometimes|numeric|min:0|max:100',
            'status' => 'sometimes|in:pending,approved,paid,clawed_back',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Recalculate split amount if percentage changed
            if (isset($validated['split_percentage'])) {
                $commission = $split->commission;
                $validated['split_amount'] = $commission->commission_amount * ($validated['split_percentage'] / 100);
            }

            // Handle status changes
            if (isset($validated['status'])) {
                if ($validated['status'] === 'paid' && $split->status !== 'paid') {
                    $validated['paid_at'] = now();
                    $validated['paid_by'] = auth()->id();
                }

                if ($validated['status'] === 'clawed_back' && $split->status !== 'clawed_back') {
                    $validated['clawed_back_at'] = now();
                    $validated['clawed_back_by'] = auth()->id();
                }
            }

            $split->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'อัพเดทการแบ่งคอมมิชชั่นสำเร็จ',
                'split' => $split->fresh()
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
     * Delete commission split
     */
    public function destroy($id)
    {
        try {
            $split = CommissionSplit::findOrFail($id);
            $split->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบการแบ่งคอมมิชชั่นสำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }
}
