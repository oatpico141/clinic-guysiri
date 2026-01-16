<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Patient;
use App\Models\Invoice;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::with(['patient', 'invoice', 'branch'])->latest();

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by document type
        if ($request->filled('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by patient
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('document_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('document_date', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('document_number', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $documents = $query->paginate(20);
        $branches = Branch::orderBy('name')->get();
        $patients = Patient::orderBy('name')->get();

        // Stats
        $totalDocuments = Document::count();
        $thisMonthDocuments = Document::whereMonth('document_date', now()->month)->count();
        $receiptCount = Document::where('document_type', 'receipt')->count();
        $invoiceCount = Document::where('document_type', 'invoice')->count();

        return view('documents.index', compact(
            'documents', 'branches', 'patients',
            'totalDocuments', 'thisMonthDocuments', 'receiptCount', 'invoiceCount'
        ));
    }

    public function create(Request $request)
    {
        $branches = Branch::orderBy('name')->get();
        $patients = Patient::orderBy('name')->get();
        $invoices = Invoice::orderBy('invoice_number', 'desc')->limit(100)->get();

        return view('documents.create', compact('branches', 'patients', 'invoices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_type' => 'required|in:receipt,invoice,medical_certificate,consent_form,other',
            'patient_id' => 'nullable|exists:patients,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'branch_id' => 'required|exists:branches,id',
            'document_date' => 'required|date',
            'file' => 'nullable|file|max:10240', // 10MB max
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Generate document number
            $prefix = strtoupper(substr($validated['document_type'], 0, 3));
            $date = now()->format('Ymd');
            $lastDoc = Document::where('document_number', 'like', "{$prefix}{$date}%")
                ->orderBy('document_number', 'desc')
                ->first();

            if ($lastDoc) {
                $lastNumber = intval(substr($lastDoc->document_number, -4));
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            $validated['document_number'] = $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
            $validated['status'] = 'active';
            $validated['created_by'] = auth()->id();

            // Handle file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('documents/' . now()->format('Y/m'), 'public');
                $validated['file_path'] = $path;
                $validated['file_name'] = $file->getClientOriginalName();
                $validated['file_size'] = $file->getSize();
            }

            $document = Document::create($validated);

            DB::commit();

            return redirect()
                ->route('documents.show', $document)
                ->with('success', 'บันทึกเอกสารเรียบร้อยแล้ว');
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
        $document = Document::with(['patient', 'invoice.items', 'payment', 'branch'])
            ->findOrFail($id);

        // Related documents (same patient)
        $relatedDocuments = collect();
        if ($document->patient_id) {
            $relatedDocuments = Document::where('patient_id', $document->patient_id)
                ->where('id', '!=', $id)
                ->latest()
                ->limit(5)
                ->get();
        }

        return view('documents.show', compact('document', 'relatedDocuments'));
    }

    public function edit($id)
    {
        $document = Document::findOrFail($id);
        $branches = Branch::orderBy('name')->get();
        $patients = Patient::orderBy('name')->get();
        $invoices = Invoice::orderBy('invoice_number', 'desc')->limit(100)->get();

        return view('documents.edit', compact('document', 'branches', 'patients', 'invoices'));
    }

    public function update(Request $request, $id)
    {
        $document = Document::findOrFail($id);

        $validated = $request->validate([
            'document_type' => 'required|in:receipt,invoice,medical_certificate,consent_form,other',
            'patient_id' => 'nullable|exists:patients,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'branch_id' => 'required|exists:branches,id',
            'document_date' => 'required|date',
            'status' => 'required|in:active,archived,cancelled',
            'file' => 'nullable|file|max:10240',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Handle file upload
            if ($request->hasFile('file')) {
                // Delete old file
                if ($document->file_path) {
                    Storage::disk('public')->delete($document->file_path);
                }

                $file = $request->file('file');
                $path = $file->store('documents/' . now()->format('Y/m'), 'public');
                $validated['file_path'] = $path;
                $validated['file_name'] = $file->getClientOriginalName();
                $validated['file_size'] = $file->getSize();
            }

            $document->update($validated);

            DB::commit();

            return redirect()
                ->route('documents.show', $document)
                ->with('success', 'อัปเดตเอกสารเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $document = Document::findOrFail($id);

            // Delete file if exists
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }

            $document->delete();

            return response()->json(['success' => true, 'message' => 'ลบเอกสารเรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function download($id)
    {
        $document = Document::findOrFail($id);

        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            return redirect()->back()->with('error', 'ไม่พบไฟล์เอกสาร');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }
}
