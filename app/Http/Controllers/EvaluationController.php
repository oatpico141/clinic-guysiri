<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\Staff;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EvaluationController extends Controller
{
    /**
     * Display evaluation list
     */
    public function index(Request $request)
    {
        $query = Evaluation::with(['staff', 'evaluator', 'branch']);

        // Filter by staff
        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by evaluation type
        if ($request->filled('evaluation_type')) {
            $query->where('evaluation_type', $request->evaluation_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by year
        if ($request->filled('year')) {
            $query->whereYear('evaluation_date', $request->year);
        }

        $evaluations = $query->orderBy('evaluation_date', 'desc')->paginate(20);
        $staffs = Staff::where('employment_status', 'active')->orderBy('first_name')->get();
        $branches = Branch::all();

        // Stats
        $totalEvaluations = Evaluation::count();
        $pendingCount = Evaluation::where('status', 'pending')->count();
        $completedCount = Evaluation::where('status', 'completed')->count();
        $avgScore = Evaluation::where('status', 'completed')->avg('overall_score') ?? 0;

        return view('evaluations.index', compact(
            'evaluations', 'staffs', 'branches',
            'totalEvaluations', 'pendingCount', 'completedCount', 'avgScore'
        ));
    }

    /**
     * Show create evaluation form
     */
    public function create()
    {
        $staffs = Staff::where('employment_status', 'active')->orderBy('first_name')->get();
        $branches = Branch::all();

        return view('evaluations.create', compact('staffs', 'branches'));
    }

    /**
     * Store new evaluation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'branch_id' => 'required|exists:branches,id',
            'evaluation_type' => 'required|in:probation,quarterly,annual,improvement_plan',
            'evaluation_date' => 'required|date',
            'evaluation_period' => 'nullable|string|max:100',
            'ratings' => 'nullable|array',
            'overall_score' => 'nullable|numeric|min:0|max:100',
            'overall_rating' => 'nullable|in:excellent,good,satisfactory,needs_improvement,unsatisfactory',
            'strengths' => 'nullable|string',
            'areas_for_improvement' => 'nullable|string',
            'goals' => 'nullable|string',
            'action_plan' => 'nullable|string',
            'evaluator_comments' => 'nullable|string',
            'next_evaluation_date' => 'nullable|date|after:evaluation_date',
        ]);

        try {
            DB::beginTransaction();

            $validated['evaluator_id'] = auth()->id();
            $validated['status'] = 'draft';
            $validated['created_by'] = auth()->id();

            // Calculate overall score from ratings if provided
            if (!empty($validated['ratings'])) {
                $ratings = collect($validated['ratings'])->filter();
                if ($ratings->count() > 0) {
                    $validated['overall_score'] = $ratings->avg();
                }
            }

            $evaluation = Evaluation::create($validated);

            DB::commit();

            return redirect()
                ->route('evaluations.show', $evaluation)
                ->with('success', 'สร้างการประเมินสำเร็จ');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Display evaluation details
     */
    public function show($id)
    {
        $evaluation = Evaluation::with(['staff', 'evaluator', 'branch'])->findOrFail($id);

        return view('evaluations.show', compact('evaluation'));
    }

    /**
     * Show edit evaluation form
     */
    public function edit($id)
    {
        $evaluation = Evaluation::findOrFail($id);

        if ($evaluation->status === 'completed') {
            return redirect()
                ->route('evaluations.show', $evaluation)
                ->with('error', 'ไม่สามารถแก้ไขการประเมินที่เสร็จสิ้นแล้ว');
        }

        $staffs = Staff::where('employment_status', 'active')->orderBy('first_name')->get();
        $branches = Branch::all();

        return view('evaluations.edit', compact('evaluation', 'staffs', 'branches'));
    }

    /**
     * Update evaluation
     */
    public function update(Request $request, $id)
    {
        $evaluation = Evaluation::findOrFail($id);

        if ($evaluation->status === 'completed') {
            return redirect()
                ->route('evaluations.show', $evaluation)
                ->with('error', 'ไม่สามารถแก้ไขการประเมินที่เสร็จสิ้นแล้ว');
        }

        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'branch_id' => 'required|exists:branches,id',
            'evaluation_type' => 'required|in:probation,quarterly,annual,improvement_plan',
            'evaluation_date' => 'required|date',
            'evaluation_period' => 'nullable|string|max:100',
            'ratings' => 'nullable|array',
            'overall_score' => 'nullable|numeric|min:0|max:100',
            'overall_rating' => 'nullable|in:excellent,good,satisfactory,needs_improvement,unsatisfactory',
            'strengths' => 'nullable|string',
            'areas_for_improvement' => 'nullable|string',
            'goals' => 'nullable|string',
            'action_plan' => 'nullable|string',
            'evaluator_comments' => 'nullable|string',
            'staff_comments' => 'nullable|string',
            'next_evaluation_date' => 'nullable|date|after:evaluation_date',
            'status' => 'nullable|in:draft,pending,completed',
        ]);

        try {
            // Calculate overall score from ratings if provided
            if (!empty($validated['ratings'])) {
                $ratings = collect($validated['ratings'])->filter();
                if ($ratings->count() > 0) {
                    $validated['overall_score'] = $ratings->avg();
                }
            }

            $evaluation->update($validated);

            return redirect()
                ->route('evaluations.show', $evaluation)
                ->with('success', 'แก้ไขการประเมินสำเร็จ');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Delete evaluation
     */
    public function destroy($id)
    {
        try {
            $evaluation = Evaluation::findOrFail($id);

            if ($evaluation->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถลบการประเมินที่เสร็จสิ้นแล้ว'
                ], 400);
            }

            $evaluation->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบการประเมินสำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete evaluation
     */
    public function complete($id)
    {
        try {
            $evaluation = Evaluation::findOrFail($id);

            if ($evaluation->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'การประเมินนี้เสร็จสิ้นแล้ว'
                ], 400);
            }

            $evaluation->update(['status' => 'completed']);

            return response()->json([
                'success' => true,
                'message' => 'บันทึกการประเมินสำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }
}
