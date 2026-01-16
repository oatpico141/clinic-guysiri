@extends('layouts.app')

@section('title', 'แก้ไข OPD - GCMS')

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .page-header {
        background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
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

    .patient-info-box {
        background: #f0fdf4;
        border: 2px solid #22c55e;
        border-radius: 12px;
        padding: 1rem;
    }

    .opd-number-display {
        font-family: 'Monaco', 'Consolas', monospace;
        font-weight: 600;
        color: #0d9488;
    }

    .temp-toggle {
        background: #fef3c7;
        border-radius: 12px;
        padding: 1rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2"><i class="bi bi-pencil me-2"></i>แก้ไข OPD</h2>
                <p class="mb-0 opacity-90">
                    <span class="opd-number-display">{{ $opdRecord->opd_number }}</span>
                </p>
            </div>
            <a href="{{ route('opd-records.show', $opdRecord) }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="form-card">
        <form method="POST" action="{{ route('opd-records.update', $opdRecord) }}">
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

            <!-- Patient Info (Display Only) -->
            <div class="section-title"><i class="bi bi-person me-2"></i>ข้อมูลผู้ป่วย</div>
            <div class="patient-info-box mb-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #14b8a6, #0d9488); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 700;">
                            {{ $opdRecord->patient ? substr($opdRecord->patient->name ?? '', 0, 1) : '?' }}
                        </div>
                    </div>
                    <div>
                        <h5 class="mb-0">{{ $opdRecord->patient->name ?? '-' }}</h5>
                        <small class="text-muted">{{ $opdRecord->patient->phone ?? '-' }}</small>
                    </div>
                </div>
                <input type="hidden" name="patient_id" value="{{ $opdRecord->patient_id }}">
            </div>

            <!-- Branch & Status -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label">สาขา <span class="text-danger">*</span></label>
                    <select name="branch_id" class="form-select" required>
                        <option value="">เลือกสาขา</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id', $opdRecord->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">สถานะ <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="active" {{ old('status', $opdRecord->status) == 'active' ? 'selected' : '' }}>กำลังดำเนินการ</option>
                        <option value="closed" {{ old('status', $opdRecord->status) == 'closed' ? 'selected' : '' }}>ปิดแล้ว</option>
                        <option value="cancelled" {{ old('status', $opdRecord->status) == 'cancelled' ? 'selected' : '' }}>ยกเลิก</option>
                    </select>
                </div>
            </div>

            <!-- Chief Complaint -->
            <div class="section-title"><i class="bi bi-chat-left-text me-2"></i>อาการเบื้องต้น</div>
            <div class="mb-4">
                <label class="form-label">อาการสำคัญ / Chief Complaint</label>
                <textarea name="chief_complaint" class="form-control" rows="3">{{ old('chief_complaint', $opdRecord->chief_complaint) }}</textarea>
            </div>

            <!-- Temporary Toggle -->
            <div class="temp-toggle mb-4">
                <div class="form-check">
                    <input type="checkbox" name="is_temporary" value="1" class="form-check-input" id="isTemporary" {{ old('is_temporary', $opdRecord->is_temporary) ? 'checked' : '' }}>
                    <label class="form-check-label" for="isTemporary">
                        <strong><i class="bi bi-clock-history me-1"></i> OPD ชั่วคราว</strong>
                        <br><small class="text-muted">สำหรับผู้ป่วยที่ยังไม่ได้ลงทะเบียนถาวร</small>
                    </label>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                    <i class="bi bi-trash me-1"></i> ลบ OPD
                </button>
                <div class="d-flex gap-2">
                    <a href="{{ route('opd-records.show', $opdRecord) }}" class="btn btn-secondary">ยกเลิก</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> บันทึก
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ยืนยันการลบ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>คุณต้องการลบ OPD <strong>{{ $opdRecord->opd_number }}</strong> หรือไม่?</p>
                <p class="text-danger mb-0"><small>หมายเหตุ: ไม่สามารถลบได้หากมีข้อมูลการรักษาหรือใบแจ้งหนี้</small></p>
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
function confirmDelete() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    fetch('{{ route("opd-records.destroy", $opdRecord) }}', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '{{ route("opd-records.index") }}';
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
