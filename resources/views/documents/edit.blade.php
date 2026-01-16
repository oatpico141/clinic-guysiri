@extends('layouts.app')

@section('title', 'แก้ไขเอกสาร - GCMS')

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .page-header {
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
        border-radius: 20px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }

    .section-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e5e7eb;
    }

    .type-option {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.3s;
        text-align: center;
    }

    .type-option:hover {
        border-color: #64748b;
    }

    .type-option.selected {
        border-color: #64748b;
        background: #f8fafc;
    }

    .type-option input[type="radio"] {
        display: none;
    }

    .type-option i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .type-receipt i { color: #059669; }
    .type-invoice i { color: #2563eb; }
    .type-medical i { color: #d97706; }
    .type-consent i { color: #7c3aed; }
    .type-other i { color: #64748b; }

    .current-file {
        background: #f0fdf4;
        border: 2px solid #22c55e;
        border-radius: 12px;
        padding: 1rem;
    }

    .file-upload-area {
        border: 2px dashed #e5e7eb;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }

    .file-upload-area:hover {
        border-color: #64748b;
        background: #f8fafc;
    }

    .file-upload-area.has-file {
        border-color: #059669;
        background: #f0fdf4;
    }

    .doc-number-display {
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 0.9rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2"><i class="bi bi-pencil me-2"></i>แก้ไขเอกสาร</h2>
                <p class="mb-0 opacity-90">
                    <span class="doc-number-display">{{ $document->document_number }}</span>
                </p>
            </div>
            <a href="{{ route('documents.show', $document) }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="form-card">
        <form method="POST" action="{{ route('documents.update', $document) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Document Type -->
            <div class="section-title"><i class="bi bi-tag me-2"></i>ประเภทเอกสาร</div>
            <div class="row g-3 mb-4">
                <div class="col-md-2 col-6">
                    <label class="type-option type-receipt d-block">
                        <input type="radio" name="document_type" value="receipt" {{ old('document_type', $document->document_type) == 'receipt' ? 'checked' : '' }}>
                        <i class="bi bi-receipt d-block"></i>
                        <div class="fw-semibold">ใบเสร็จ</div>
                    </label>
                </div>
                <div class="col-md-2 col-6">
                    <label class="type-option type-invoice d-block">
                        <input type="radio" name="document_type" value="invoice" {{ old('document_type', $document->document_type) == 'invoice' ? 'checked' : '' }}>
                        <i class="bi bi-file-text d-block"></i>
                        <div class="fw-semibold">ใบแจ้งหนี้</div>
                    </label>
                </div>
                <div class="col-md-3 col-6">
                    <label class="type-option type-medical d-block">
                        <input type="radio" name="document_type" value="medical_certificate" {{ old('document_type', $document->document_type) == 'medical_certificate' ? 'checked' : '' }}>
                        <i class="bi bi-file-medical d-block"></i>
                        <div class="fw-semibold">ใบรับรองแพทย์</div>
                    </label>
                </div>
                <div class="col-md-2 col-6">
                    <label class="type-option type-consent d-block">
                        <input type="radio" name="document_type" value="consent_form" {{ old('document_type', $document->document_type) == 'consent_form' ? 'checked' : '' }}>
                        <i class="bi bi-file-earmark-check d-block"></i>
                        <div class="fw-semibold">ใบยินยอม</div>
                    </label>
                </div>
                <div class="col-md-3 col-6">
                    <label class="type-option type-other d-block">
                        <input type="radio" name="document_type" value="other" {{ old('document_type', $document->document_type) == 'other' ? 'checked' : '' }}>
                        <i class="bi bi-file-earmark d-block"></i>
                        <div class="fw-semibold">อื่นๆ</div>
                    </label>
                </div>
            </div>

            <!-- Basic Info -->
            <div class="section-title"><i class="bi bi-info-circle me-2"></i>ข้อมูลเอกสาร</div>
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="form-label">สาขา <span class="text-danger">*</span></label>
                    <select name="branch_id" class="form-select" required>
                        <option value="">เลือกสาขา</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id', $document->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">วันที่เอกสาร <span class="text-danger">*</span></label>
                    <input type="date" name="document_date" class="form-control" value="{{ old('document_date', $document->document_date ? \Carbon\Carbon::parse($document->document_date)->format('Y-m-d') : '') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">สถานะ <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="active" {{ old('status', $document->status) == 'active' ? 'selected' : '' }}>ใช้งาน</option>
                        <option value="archived" {{ old('status', $document->status) == 'archived' ? 'selected' : '' }}>เก็บถาวร</option>
                        <option value="cancelled" {{ old('status', $document->status) == 'cancelled' ? 'selected' : '' }}>ยกเลิก</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">ผู้ป่วย</label>
                    <select name="patient_id" class="form-select">
                        <option value="">ไม่ระบุ</option>
                        @foreach($patients as $patient)
                        <option value="{{ $patient->id }}" {{ old('patient_id', $document->patient_id) == $patient->id ? 'selected' : '' }}>{{ $patient->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">ใบแจ้งหนี้อ้างอิง</label>
                    <select name="invoice_id" class="form-select">
                        <option value="">ไม่ระบุ</option>
                        @foreach($invoices as $invoice)
                        <option value="{{ $invoice->id }}" {{ old('invoice_id', $document->invoice_id) == $invoice->id ? 'selected' : '' }}>{{ $invoice->invoice_number }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Current File -->
            @if($document->file_path)
            <div class="section-title"><i class="bi bi-paperclip me-2"></i>ไฟล์ปัจจุบัน</div>
            <div class="current-file mb-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-file-earmark-check fs-3 text-success me-3"></i>
                        <div>
                            <div class="fw-semibold">{{ $document->file_name }}</div>
                            <small class="text-muted">{{ number_format($document->file_size / 1024, 2) }} KB</small>
                        </div>
                    </div>
                    <a href="{{ route('documents.download', $document) }}" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-download me-1"></i> ดาวน์โหลด
                    </a>
                </div>
            </div>
            @endif

            <!-- File Upload -->
            <div class="section-title"><i class="bi bi-upload me-2"></i>{{ $document->file_path ? 'เปลี่ยนไฟล์ (ถ้าต้องการ)' : 'อัปโหลดไฟล์' }}</div>
            <div class="mb-4">
                <label for="fileInput" class="file-upload-area d-block" id="uploadArea">
                    <i class="bi bi-cloud-arrow-up d-block mb-2" style="font-size: 3rem; color: #94a3b8;"></i>
                    <div id="uploadText">คลิกเพื่อเลือกไฟล์ใหม่</div>
                    <small class="text-muted">รองรับไฟล์ PDF, รูปภาพ, Word, Excel (สูงสุด 10MB)</small>
                </label>
                <input type="file" name="file" id="fileInput" class="d-none" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
            </div>

            <!-- Notes -->
            <div class="section-title"><i class="bi bi-sticky me-2"></i>หมายเหตุ</div>
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $document->notes) }}</textarea>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                    <i class="bi bi-trash me-1"></i> ลบ
                </button>
                <div class="d-flex gap-2">
                    <a href="{{ route('documents.show', $document) }}" class="btn btn-secondary">ยกเลิก</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> บันทึก
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ยืนยันการลบ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>คุณต้องการลบเอกสาร <strong>{{ $document->document_number }}</strong> หรือไม่?</p>
                @if($document->file_path)
                <p class="text-warning mb-0"><small><i class="bi bi-exclamation-triangle me-1"></i>ไฟล์แนบจะถูกลบไปด้วย</small></p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">ลบ</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Type selection UI
    const typeOptions = document.querySelectorAll('.type-option');

    function updateTypeUI() {
        typeOptions.forEach(opt => {
            const radio = opt.querySelector('input[type="radio"]');
            if (radio.checked) {
                opt.classList.add('selected');
            } else {
                opt.classList.remove('selected');
            }
        });
    }

    typeOptions.forEach(opt => {
        opt.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
            updateTypeUI();
        });
    });

    updateTypeUI();

    // File upload
    const fileInput = document.getElementById('fileInput');
    const uploadArea = document.getElementById('uploadArea');
    const uploadText = document.getElementById('uploadText');

    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            uploadText.innerHTML = '<strong>' + file.name + '</strong><br><small class="text-muted">' + formatFileSize(file.size) + '</small>';
            uploadArea.classList.add('has-file');
        }
    });

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
});

function confirmDelete() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    fetch('{{ route("documents.destroy", $document) }}', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '{{ route("documents.index") }}';
        } else {
            alert(data.message || 'เกิดข้อผิดพลาด');
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
        }
    })
    .catch(error => {
        alert('เกิดข้อผิดพลาด');
    });
});
</script>
@endpush
