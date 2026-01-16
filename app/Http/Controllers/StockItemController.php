<?php

namespace App\Http\Controllers;

use App\Models\StockItem;
use App\Models\StockTransaction;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockItemController extends Controller
{
    /**
     * Display stock item list
     */
    public function index(Request $request)
    {
        $query = StockItem::with(['branch']);

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $query->where('is_active', true);
            } elseif ($request->status == 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status == 'low_stock') {
                $query->whereColumn('quantity_on_hand', '<=', 'minimum_quantity');
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('item_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('supplier', 'like', "%{$search}%");
            });
        }

        $stockItems = $query->orderBy('name')->paginate(20);
        $branches = Branch::all();

        // Get categories for filter
        $categories = StockItem::distinct()->pluck('category')->filter();

        // Stats
        $totalItems = StockItem::count();
        $activeCount = StockItem::where('is_active', true)->count();
        $lowStockCount = StockItem::whereColumn('quantity_on_hand', '<=', 'minimum_quantity')->count();
        $totalValue = StockItem::selectRaw('SUM(quantity_on_hand * unit_cost) as total')->value('total') ?? 0;

        return view('stock-items.index', compact(
            'stockItems', 'branches', 'categories',
            'totalItems', 'activeCount', 'lowStockCount', 'totalValue'
        ));
    }

    /**
     * Show create stock item form
     */
    public function create()
    {
        $branches = Branch::all();
        $categories = StockItem::distinct()->pluck('category')->filter();

        return view('stock-items.create', compact('branches', 'categories'));
    }

    /**
     * Store new stock item
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'item_code' => 'nullable|string|max:50|unique:stock_items,item_code',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'unit' => 'required|string|max:50',
            'branch_id' => 'required|exists:branches,id',
            'quantity_on_hand' => 'nullable|integer|min:0',
            'minimum_quantity' => 'nullable|integer|min:0',
            'maximum_quantity' => 'nullable|integer|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'supplier_item_code' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Generate item code if not provided
            if (empty($validated['item_code'])) {
                $count = StockItem::count() + 1;
                $validated['item_code'] = 'ITM' . str_pad($count, 5, '0', STR_PAD_LEFT);
            }

            $validated['is_active'] = true;
            $validated['quantity_on_hand'] = $validated['quantity_on_hand'] ?? 0;
            $validated['minimum_quantity'] = $validated['minimum_quantity'] ?? 0;
            $validated['created_by'] = auth()->id();

            $stockItem = StockItem::create($validated);

            // Create initial stock transaction if has quantity
            if ($validated['quantity_on_hand'] > 0) {
                StockTransaction::create([
                    'stock_item_id' => $stockItem->id,
                    'branch_id' => $stockItem->branch_id,
                    'transaction_type' => 'in',
                    'quantity' => $validated['quantity_on_hand'],
                    'unit_cost' => $validated['unit_cost'] ?? 0,
                    'reason' => 'ยอดเริ่มต้น',
                    'performed_by' => auth()->id(),
                ]);
            }

            DB::commit();

            return redirect()
                ->route('stock-items.show', $stockItem)
                ->with('success', 'เพิ่มสินค้าสำเร็จ');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Display stock item details
     */
    public function show($id)
    {
        $stockItem = StockItem::with(['branch'])->findOrFail($id);

        // Get recent transactions
        $transactions = StockTransaction::where('stock_item_id', $id)
            ->with('performedBy')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('stock-items.show', compact('stockItem', 'transactions'));
    }

    /**
     * Show edit stock item form
     */
    public function edit($id)
    {
        $stockItem = StockItem::findOrFail($id);
        $branches = Branch::all();
        $categories = StockItem::distinct()->pluck('category')->filter();

        return view('stock-items.edit', compact('stockItem', 'branches', 'categories'));
    }

    /**
     * Update stock item
     */
    public function update(Request $request, $id)
    {
        $stockItem = StockItem::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'item_code' => 'nullable|string|max:50|unique:stock_items,item_code,' . $id,
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'unit' => 'required|string|max:50',
            'branch_id' => 'required|exists:branches,id',
            'minimum_quantity' => 'nullable|integer|min:0',
            'maximum_quantity' => 'nullable|integer|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'supplier_item_code' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        try {
            $validated['is_active'] = $request->boolean('is_active');
            $stockItem->update($validated);

            return redirect()
                ->route('stock-items.show', $stockItem)
                ->with('success', 'แก้ไขสินค้าสำเร็จ');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Delete stock item
     */
    public function destroy($id)
    {
        try {
            $stockItem = StockItem::findOrFail($id);

            // Check if has transactions
            if ($stockItem->transactions()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถลบได้ มีประวัติการเคลื่อนไหว'
                ], 400);
            }

            $stockItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบสินค้าสำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Adjust stock quantity
     */
    public function adjust(Request $request, $id)
    {
        $request->validate([
            'adjustment_type' => 'required|in:in,out,adjust',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:500',
            'unit_cost' => 'nullable|numeric|min:0',
        ]);

        try {
            $stockItem = StockItem::findOrFail($id);

            DB::beginTransaction();

            $type = $request->adjustment_type;
            $quantity = $request->quantity;

            if ($type == 'out' && $stockItem->quantity_on_hand < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'จำนวนไม่เพียงพอ (คงเหลือ: ' . $stockItem->quantity_on_hand . ')'
                ], 400);
            }

            // Update quantity
            if ($type == 'in') {
                $stockItem->quantity_on_hand += $quantity;
            } elseif ($type == 'out') {
                $stockItem->quantity_on_hand -= $quantity;
            } else { // adjust
                $stockItem->quantity_on_hand = $quantity;
            }
            $stockItem->save();

            // Create transaction
            StockTransaction::create([
                'stock_item_id' => $stockItem->id,
                'branch_id' => $stockItem->branch_id,
                'transaction_type' => $type,
                'quantity' => $type == 'adjust' ? $quantity : ($type == 'in' ? $quantity : -$quantity),
                'unit_cost' => $request->unit_cost ?? $stockItem->unit_cost,
                'reason' => $request->reason,
                'performed_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'ปรับจำนวนสำเร็จ',
                'new_quantity' => $stockItem->quantity_on_hand
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }
}
