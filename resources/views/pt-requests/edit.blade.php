@extends('layouts.app')

@section('title', 'แก้ไขคำขอเปลี่ยน PT - GCMS')

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .page-header {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
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

    .pt-select-card {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1.5rem;
        border: 2px dashed #e5e7eb;
    }

    .pt-arrow {
        font-size: 2rem;
        color: #8b5cf6;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2"><i class="bi bi-pencil me-2"></i>แก้ไขคำขอเปลี่ยน PT</h2>
                <p class="mb-0 opacity-90">แก้ไขข้อมูลคำขอเปลี่ยน PT</p>
            </div>
            <a href="{{ route('pt-requests.show', $ptRequest) }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="form-card">
        <form method="POST" action="{{ route('pt-requests.update', $ptRequest) }}">
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

            <!-- Patient Info (Read-only) -->
            <div class="section-title"><i class="bi bi-person me-2"></i>ข้อมูลผู้ป่วย</div>
            <div class="patient-info-box mb-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 700;">
                            {{ $ptRequest->patient ? substr($ptRequest->patient->name ?? '', 0, 1) : '?' }}
                        </div>
                    </div>
                    <div>
                        <h5 class="mb-0">{{ $ptRequest->patient->name ?? '-' }}</h5>
                        <small class="text-muted">{{ $ptRequest->patient->phone ?? '-' }}</small>
                    </div>
                </div>
                <input type="hidden" name="patient_id" value="{{ $ptRequest->patient_id }}">
            </div>

            <!-- Branch -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label">สาขา <span class="text-danger">*</span></label>
                    <select name="branch_id" class="form-select" required>
                        <option value="">เลือกสาขา</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id', $ptRequest->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- PT Selection -->
            <div class="section-title"><i class="bi bi-people me-2"></i>เลือก PT</div>
            <div class="row align-items-center mb-4">
                <div class="col-md-5">
                    <div class="pt-select-card">
                        <label class="form-label fw-semibold text-danger">
                            <i class="bi bi-person-x me-1"></i> PT เดิม
                        </label>
                        <select name="original_pt_id" class="form-select">
                            <option value="">ไม่มี PT เดิม</option>
                            @foreach($pts as $pt)
                            <option value="{{ $pt->id }}" {{ old('original_pt_id', $ptRequest->original_pt_id) == $pt->id ? 'selected' : '' }}>
                                {{ $pt->first_name }} {{ $pt->last_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2 text-center py-3">
                    <i class="bi bi-arrow-right pt-arrow"></i>
                </div>
                <div class="col-md-5">
                    <div class="pt-select-card">
                        <label class="form-label fw-semibold text-success">
                            <i class="bi bi-person-check me-1"></i> PT ที่ต้องการ <span class="text-danger">*</span>
                        </label>
                        <select name="requested_pt_id" class="form-select" required>
                            <option value="">เลือก PT ที่ต้องการ</option>
                            @foreach($pts as $pt)
                            <option value="{{ $pt->id }}" {{ old('requested_pt_id', $ptRequest->requested_pt_id) == $pt->id ? 'selected' : '' }}>
                                {{ $pt->first_name }} {{ $pt->last_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Reason -->
            <div class="section-title"><i class="bi bi-chat-left-text me-2"></i>เหตุผลในการขอเปลี่ยน</div>
            <div class="mb-4">
                <label class="form-label">รายละเอียดเหตุผล <span class="text-danger">*</span></label>
                <textarea name="reason" class="form-control" rows="3" required>{{ old('reason', $ptRequest->reason) }}</textarea>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                    <i class="bi bi-trash me-1"></i> ลบคำขอ
                </button>
                <div class="d-flex gap-2">
                    <a href="{{ route('pt-requests.show', $ptRequest) }}" class="btn btn-secondary">ยกเลิก</a>
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
                <p>คุณต้องการลบคำขอเปลี่ยน PT นี้หรือไม่?</p>
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
    fetch('{{ route("pt-requests.destroy", $ptRequest) }}', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '{{ route("pt-requests.index") }}';
        } else {
            alert(data.message || 'เกิดข้อผิดพลาด');
        }
    })
    .catch(error => {
        alert('เกิดข้อผิดพลาด');
    });
});
</script>
@endpush
