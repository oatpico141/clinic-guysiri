@extends('layouts.app')

@section('title', 'เพิ่มเอกสาร - GCMS')

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

    .file-upload-area i {
        font-size: 3rem;
        color: #94a3b8;
    }

    .file-upload-area.has-file i {
        color: #059669;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2"><i class="bi bi-file-earmark-plus me-2"></i>เพิ่มเอกสาร</h2>
                <p class="mb-0 opacity-90">อัปโหลดและบันทึกเอกสารใหม่</p>
            </div>
            <a href="{{ route('documents.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="form-card">
        <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
            @csrf

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
                        <input type="radio" name="document_type" value="receipt" {{ old('document_type', 'receipt') == 'receipt' ? 'checked' : '' }}>
                        <i class="bi bi-receipt d-block"></i>
                        <div class="fw-semibold">ใบเสร็จ</div>
                    </label>
                </div>
                <div class="col-md-2 col-6">
                    <label class="type-option type-invoice d-block">
                        <input type="radio" name="document_type" value="invoice" {{ old('document_type') == 'invoice' ? 'checked' : '' }}>
                        <i class="bi bi-file-text d-block"></i>
                        <div class="fw-semibold">ใบแจ้งหนี้</div>
                    </label>
                </div>
                <div class="col-md-3 col-6">
                    <label class="type-option type-medical d-block">
                        <input type="radio" name="document_type" value="medical_certificate" {{ old('document_type') == 'medical_certificate' ? 'checked' : '' }}>
                        <i class="bi bi-file-medical d-block"></i>
                        <div class="fw-semibold">ใบรับรองแพทย์</div>
                    </label>
                </div>
                <div class="col-md-2 col-6">
                    <label class="type-option type-consent d-block">
                        <input type="radio" name="document_type" value="consent_form" {{ old('document_type') == 'consent_form' ? 'checked' : '' }}>
                        <i class="bi bi-file-earmark-check d-block"></i>
                        <div class="fw-semibold">ใบยินยอม</div>
                    </label>
                </div>
                <div class="col-md-3 col-6">
                    <label class="type-option type-other d-block">
                        <input type="radio" name="document_type" value="other" {{ old('document_type') == 'other' ? 'checked' : '' }}>
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
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">วันที่เอกสาร <span class="text-danger">*</span></label>
                    <input type="date" name="document_date" class="form-control" value="{{ old('document_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">ผู้ป่วย</label>
                    <select name="patient_id" class="form-select">
                        <option value="">ไม่ระบุ</option>
                        @foreach($patients as $patient)
                        <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>{{ $patient->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">ใบแจ้งหนี้อ้างอิง</label>
                    <select name="invoice_id" class="form-select">
                        <option value="">ไม่ระบุ</option>
                        @foreach($invoices as $invoice)
                        <option value="{{ $invoice->id }}" {{ old('invoice_id') == $invoice->id ? 'selected' : '' }}>{{ $invoice->invoice_number }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- File Upload -->
            <div class="section-title"><i class="bi bi-upload me-2"></i>อัปโหลดไฟล์</div>
            <div class="mb-4">
                <label for="fileInput" class="file-upload-area d-block" id="uploadArea">
                    <i class="bi bi-cloud-arrow-up d-block mb-2"></i>
                    <div id="uploadText">คลิกเพื่อเลือกไฟล์ หรือลากไฟล์มาวางที่นี่</div>
                    <small class="text-muted">รองรับไฟล์ PDF, รูปภาพ, Word, Excel (สูงสุด 10MB)</small>
                </label>
                <input type="file" name="file" id="fileInput" class="d-none" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
            </div>

            <!-- Notes -->
            <div class="section-title"><i class="bi bi-sticky me-2"></i>หมายเหตุ</div>
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <textarea name="notes" class="form-control" rows="3" placeholder="หมายเหตุเพิ่มเติม...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('documents.index') }}" class="btn btn-secondary">ยกเลิก</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> บันทึก
                </button>
            </div>
        </form>
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

    // Drag and drop
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#64748b';
        this.style.background = '#f8fafc';
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        if (!this.classList.contains('has-file')) {
            this.style.borderColor = '#e5e7eb';
            this.style.background = '';
        }
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            fileInput.files = e.dataTransfer.files;
            const file = e.dataTransfer.files[0];
            uploadText.innerHTML = '<strong>' + file.name + '</strong><br><small class="text-muted">' + formatFileSize(file.size) + '</small>';
            this.classList.add('has-file');
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
</script>
@endpush
