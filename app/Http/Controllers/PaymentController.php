<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display payment list
     */
    public function index(Request $request)
    {
        $query = Payment::with(['invoice', 'patient', 'branch']);

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        // Default: today if no date filter
        if (!$request->filled('date_from') && !$request->filled('date_to')) {
            $query->whereDate('payment_date', now()->toDateString());
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $payments = $query->orderBy('payment_date', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);

        // Stats
        $today = now()->toDateString();
        $todayTotal = Payment::whereDate('payment_date', $today)
                             ->where('status', 'completed')
                             ->sum('amount');

        $weekTotal = Payment::whereBetween('payment_date', [now()->startOfWeek(), now()->endOfWeek()])
                            ->where('status', 'completed')
                            ->sum('amount');

        $monthTotal = Payment::whereMonth('payment_date', now()->month)
                             ->whereYear('payment_date', now()->year)
                             ->where('status', 'completed')
                             ->sum('amount');

        $todayCount = Payment::whereDate('payment_date', $today)->count();

        return view('payments.index', compact(
            'payments',
            'todayTotal', 'weekTotal', 'monthTotal', 'todayCount'
        ));
    }

    /**
     * Show create payment form
     */
    public function create(Request $request)
    {
        $invoice = null;
        $patient = null;

        if ($request->filled('invoice_id')) {
            $invoice = Invoice::with('patient')->findOrFail($request->invoice_id);
            $patient = $invoice->patient;
        } elseif ($request->filled('patient_id')) {
            $patient = Patient::findOrFail($request->patient_id);
        }

        // Get pending invoices for dropdown
        $pendingInvoices = Invoice::where('status', '!=', 'paid')
                                  ->with('patient')
                                  ->orderBy('created_at', 'desc')
                                  ->limit(50)
                                  ->get();

        return view('payments.create', compact('invoice', 'patient', 'pendingInvoices'));
    }

    /**
     * Store new payment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,credit_card,debit_card,transfer,qr_code,installment',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'card_type' => 'nullable|string|max:50',
            'card_last_4' => 'nullable|string|max:4',
            'installment_number' => 'nullable|integer|min:1',
            'total_installments' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $invoice = Invoice::findOrFail($validated['invoice_id']);

            // Generate payment number
            $prefix = 'PAY' . now()->format('Ymd');
            $latestPayment = Payment::where('payment_number', 'like', $prefix . '%')
                                    ->orderBy('payment_number', 'desc')
                                    ->first();

            if ($latestPayment) {
                $lastNumber = intval(substr($latestPayment->payment_number, -4));
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $paymentNumber = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            // Create payment
            $payment = Payment::create([
                'payment_number' => $paymentNumber,
                'invoice_id' => $validated['invoice_id'],
                'patient_id' => $invoice->patient_id,
                'branch_id' => $invoice->branch_id ?? session('selected_branch_id'),
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'status' => 'completed',
                'payment_date' => $validated['payment_date'],
                'reference_number' => $validated['reference_number'] ?? null,
                'card_type' => $validated['card_type'] ?? null,
                'card_last_4' => $validated['card_last_4'] ?? null,
                'installment_number' => $validated['installment_number'] ?? null,
                'total_installments' => $validated['total_installments'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Update invoice paid amount
            $totalPaid = Payment::where('invoice_id', $invoice->id)
                                ->where('status', 'completed')
                                ->sum('amount');

            $invoice->paid_amount = $totalPaid;
            $invoice->outstanding_amount = max(0, $invoice->total_amount - $totalPaid);

            if ($invoice->outstanding_amount <= 0) {
                $invoice->status = 'paid';
            } elseif ($totalPaid > 0) {
                $invoice->status = 'partial';
            }

            $invoice->save();

            DB::commit();

            return redirect()
                ->route('payments.show', $payment)
                ->with('success', 'บันทึกการชำระเงินสำเร็จ');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Display payment details
     */
    public function show($id)
    {
        $payment = Payment::with([
            'invoice.patient',
            'invoice.items',
            'patient',
            'branch'
        ])->findOrFail($id);

        return view('payments.show', compact('payment'));
    }

    /**
     * Update payment
     */
    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,completed,cancelled,refunded',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $oldStatus = $payment->status;
            $payment->update($validated);

            // If status changed to cancelled or refunded, update invoice
            if (isset($validated['status']) && in_array($validated['status'], ['cancelled', 'refunded'])) {
                $invoice = $payment->invoice;
                if ($invoice) {
                    $totalPaid = Payment::where('invoice_id', $invoice->id)
                                        ->where('status', 'completed')
                                        ->sum('amount');

                    $invoice->paid_amount = $totalPaid;
                    $invoice->outstanding_amount = max(0, $invoice->total_amount - $totalPaid);

                    if ($invoice->outstanding_amount <= 0) {
                        $invoice->status = 'paid';
                    } elseif ($totalPaid > 0) {
                        $invoice->status = 'partial';
                    } else {
                        $invoice->status = 'pending';
                    }

                    $invoice->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'อัพเดทการชำระเงินสำเร็จ'
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
     * Cancel payment (soft delete)
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $payment = Payment::findOrFail($id);
            $invoice = $payment->invoice;

            // Update payment status
            $payment->status = 'cancelled';
            $payment->save();
            $payment->delete();

            // Update invoice if exists
            if ($invoice) {
                $totalPaid = Payment::where('invoice_id', $invoice->id)
                                    ->where('status', 'completed')
                                    ->sum('amount');

                $invoice->paid_amount = $totalPaid;
                $invoice->outstanding_amount = max(0, $invoice->total_amount - $totalPaid);

                if ($invoice->outstanding_amount <= 0) {
                    $invoice->status = 'paid';
                } elseif ($totalPaid > 0) {
                    $invoice->status = 'partial';
                } else {
                    $invoice->status = 'pending';
                }

                $invoice->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'ยกเลิกการชำระเงินสำเร็จ'
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
