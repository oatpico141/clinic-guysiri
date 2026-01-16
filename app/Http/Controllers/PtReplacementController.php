<?php

namespace App\Http\Controllers;

use App\Models\PtReplacement;
use App\Models\Staff;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PtReplacementController extends Controller
{
    /**
     * Display PT replacement list
     */
    public function index(Request $request)
    {
        $query = PtReplacement::with(['originalPt', 'replacementPt', 'appointment', 'treatment', 'branch']);

        // Filter by original PT
        if ($request->filled('original_pt_id')) {
            $query->where('original_pt_id', $request->original_pt_id);
        }

        // Filter by replacement PT
        if ($request->filled('replacement_pt_id')) {
            $query->where('replacement_pt_id', $request->replacement_pt_id);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('replacement_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('replacement_date', '<=', $request->end_date);
        }

        $replacements = $query->orderBy('replacement_date', 'desc')->paginate(20);
        $pts = Staff::where('position', 'pt')->where('employment_status', 'active')->orderBy('first_name')->get();
        $branches = Branch::all();

        // Stats
        $totalReplacements = PtReplacement::count();
        $thisMonthCount = PtReplacement::whereMonth('replacement_date', now()->month)
            ->whereYear('replacement_date', now()->year)->count();

        return view('pt-replacements.index', compact(
            'replacements', 'pts', 'branches',
            'totalReplacements', 'thisMonthCount'
        ));
    }

    /**
     * Show create PT replacement form
     */
    public function create()
    {
        $pts = Staff::where('position', 'pt')->where('employment_status', 'active')->orderBy('first_name')->get();
        $branches = Branch::all();

        return view('pt-replacements.create', compact('pts', 'branches'));
    }

    /**
     * Store new PT replacement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'original_pt_id' => 'required|exists:staff,id',
            'replacement_pt_id' => 'required|exists:staff,id|different:original_pt_id',
            'branch_id' => 'required|exists:branches,id',
            'replacement_date' => 'required|date',
            'reason' => 'required|string',
            'commission_handling' => 'nullable|in:original,replacement,split',
            'commission_split_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        try {
            $validated['created_by'] = auth()->id();

            $replacement = PtReplacement::create($validated);

            return redirect()
                ->route('pt-replacements.show', $replacement)
                ->with('success', 'บันทึกการแทน PT สำเร็จ');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Display PT replacement details
     */
    public function show($id)
    {
        $replacement = PtReplacement::with(['originalPt', 'replacementPt', 'appointment', 'treatment', 'branch'])->findOrFail($id);

        return view('pt-replacements.show', compact('replacement'));
    }

    /**
     * Show edit PT replacement form
     */
    public function edit($id)
    {
        $replacement = PtReplacement::findOrFail($id);
        $pts = Staff::where('position', 'pt')->where('employment_status', 'active')->orderBy('first_name')->get();
        $branches = Branch::all();

        return view('pt-replacements.edit', compact('replacement', 'pts', 'branches'));
    }

    /**
     * Update PT replacement
     */
    public function update(Request $request, $id)
    {
        $replacement = PtReplacement::findOrFail($id);

        $validated = $request->validate([
            'original_pt_id' => 'required|exists:staff,id',
            'replacement_pt_id' => 'required|exists:staff,id|different:original_pt_id',
            'branch_id' => 'required|exists:branches,id',
            'replacement_date' => 'required|date',
            'reason' => 'required|string',
            'commission_handling' => 'nullable|in:original,replacement,split',
            'commission_split_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        try {
            $replacement->update($validated);

            return redirect()
                ->route('pt-replacements.show', $replacement)
                ->with('success', 'แก้ไขการแทน PT สำเร็จ');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Delete PT replacement
     */
    public function destroy($id)
    {
        try {
            $replacement = PtReplacement::findOrFail($id);
            $replacement->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบการแทน PT สำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }
}
