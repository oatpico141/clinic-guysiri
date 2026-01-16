<?php

namespace App\Http\Controllers;

use App\Models\PtServiceRate;
use App\Models\User;
use App\Models\Service;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PtServiceRateController extends Controller
{
    public function index(Request $request)
    {
        $query = PtServiceRate::with(['pt', 'service', 'branch'])->latest();

        // Filter by PT
        if ($request->filled('pt_id')) {
            $query->where('pt_id', $request->pt_id);
        }

        // Filter by service
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } else {
                $query->where('is_active', false);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('pt', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('service', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                });
            });
        }

        $rates = $query->paginate(20);

        // Get PTs (users with PT role)
        $pts = User::whereHas('role', function ($q) {
            $q->where('name', 'like', '%PT%');
        })->orderBy('name')->get();

        $services = Service::orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();

        // Stats
        $totalRates = PtServiceRate::count();
        $activeRates = PtServiceRate::where('is_active', true)->count();
        $ptCount = PtServiceRate::select('pt_id')->distinct()->count();
        $serviceCount = PtServiceRate::select('service_id')->distinct()->count();

        return view('pt-service-rates.index', compact(
            'rates', 'pts', 'services', 'branches',
            'totalRates', 'activeRates', 'ptCount', 'serviceCount'
        ));
    }

    public function create()
    {
        $pts = User::whereHas('role', function ($q) {
            $q->where('name', 'like', '%PT%');
        })->orderBy('name')->get();

        $services = Service::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();

        return view('pt-service-rates.create', compact('pts', 'services', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pt_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'branch_id' => 'required|exists:branches,id',
            'price' => 'required|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'df_rate' => 'nullable|numeric|min:0',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
            'is_active' => 'boolean',
        ]);

        // Check for duplicate
        $exists = PtServiceRate::where('pt_id', $validated['pt_id'])
            ->where('service_id', $validated['service_id'])
            ->where('branch_id', $validated['branch_id'])
            ->where('is_active', true)
            ->exists();

        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'มีอัตราค่าบริการนี้อยู่แล้วสำหรับ PT และสาขานี้');
        }

        try {
            $validated['is_active'] = $request->has('is_active');
            $validated['created_by'] = auth()->id();

            PtServiceRate::create($validated);

            return redirect()
                ->route('pt-service-rates.index')
                ->with('success', 'เพิ่มอัตราค่าบริการเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $rate = PtServiceRate::with(['pt', 'service', 'branch', 'createdBy'])->findOrFail($id);

        return view('pt-service-rates.show', compact('rate'));
    }

    public function edit($id)
    {
        $rate = PtServiceRate::findOrFail($id);

        $pts = User::whereHas('role', function ($q) {
            $q->where('name', 'like', '%PT%');
        })->orderBy('name')->get();

        $services = Service::orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();

        return view('pt-service-rates.edit', compact('rate', 'pts', 'services', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $rate = PtServiceRate::findOrFail($id);

        $validated = $request->validate([
            'pt_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'branch_id' => 'required|exists:branches,id',
            'price' => 'required|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'df_rate' => 'nullable|numeric|min:0',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
            'is_active' => 'boolean',
        ]);

        // Check for duplicate (exclude current)
        $exists = PtServiceRate::where('pt_id', $validated['pt_id'])
            ->where('service_id', $validated['service_id'])
            ->where('branch_id', $validated['branch_id'])
            ->where('is_active', true)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists && $request->has('is_active')) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'มีอัตราค่าบริการนี้อยู่แล้วสำหรับ PT และสาขานี้');
        }

        try {
            $validated['is_active'] = $request->has('is_active');

            $rate->update($validated);

            return redirect()
                ->route('pt-service-rates.index')
                ->with('success', 'อัปเดตอัตราค่าบริการเรียบร้อยแล้ว');
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
            $rate = PtServiceRate::findOrFail($id);
            $rate->delete();

            return response()->json(['success' => true, 'message' => 'ลบอัตราค่าบริการเรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // API: Get rates for PT
    public function getPtRates($ptId)
    {
        $rates = PtServiceRate::with(['service', 'branch'])
            ->where('pt_id', $ptId)
            ->where('is_active', true)
            ->get();

        return response()->json(['success' => true, 'rates' => $rates]);
    }
}
