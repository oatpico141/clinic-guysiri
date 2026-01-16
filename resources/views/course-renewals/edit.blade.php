@extends('layouts.app')

@section('title', 'แก้ไขการต่ออายุ - GCMS')

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .page-header {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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

    .renewal-number-display {
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 0.9rem;
    }

    .readonly-info {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1rem;
    }

    .date-comparison {
        background: #f0fdf4;
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
                <h2 class="mb-2"><i class="bi bi-pencil me-2"></i>แก้ไขการต่ออายุ</h2>
                <p class="mb-0 opacity-90">
                    <span class="renewal-number-display">{{ $renewal->renewal_number }}</span>
                </p>
            </div>
            <a href="{{ route('course-renewals.show', $renewal) }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="form-card">
        <form method="POST" action="{{ route('course-renewals.update', $renewal) }}">
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

            <!-- Readonly Info -->
            <div class="section-title"><i class="bi bi-info-circle me-2"></i>ข้อมูลการต่ออายุ (ไม่สามารถแก้ไข)</div>
            <div class="readonly-info mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <strong>คอร์ส:</strong> {{ $renewal->coursePurchase->coursePackage->name ?? '-' }}<br>
                        <strong>ลูกค้า:</strong> {{ $renewal->patient->name ?? '-' }}
                    </div>
                    <div class="col-md-6">
                        <strong>วันที่ต่ออายุ:</strong> {{ $renewal->renewal_date?->format('d/m/Y') ?? '-' }}<br>
                        <strong>จำนวนวัน:</strong> {{ $renewal->extension_days }} วัน
                    </div>
                </div>
            </div>

            <div class="date-comparison mb-4">
                <div class="row text-center">
                    <div class="col-md-6">
                        <small class="text-muted">หมดอายุเดิม</small>
                        <div class="fw-bold text-danger">{{ $renewal->old_expiry_date?->format('d/m/Y') ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">หมดอายุใหม่</small>
                        <div class="fw-bold text-success">{{ $renewal->new_expiry_date?->format('d/m/Y') ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <!-- Editable Fields -->
            <div class="section-title"><i class="bi bi-pencil-square me-2"></i>ข้อมูลที่สามารถแก้ไข</div>
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label">ค่าธรรมเนียม (บาท)</label>
                    <div class="input-group">
                        <span class="input-group-text">฿</span>
                        <input type="number" name="renewal_fee" class="form-control" value="{{ old('renewal_fee', $renewal->renewal_fee) }}" min="0" step="0.01">
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">เหตุผลในการต่ออายุ</label>
                    <textarea name="renewal_reason" class="form-control" rows="2">{{ old('renewal_reason', $renewal->renewal_reason) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">หมายเหตุ</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $renewal->notes) }}</textarea>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                    <i class="bi bi-trash me-1"></i> ยกเลิกการต่ออายุ
                </button>
                <div class="d-flex gap-2">
                    <a href="{{ route('course-renewals.show', $renewal) }}" class="btn btn-secondary">ยกเลิก</a>
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
                <h5 class="modal-title">ยืนยันการยกเลิก</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>คุณต้องการยกเลิกการต่ออายุ <strong>{{ $renewal->renewal_number }}</strong> หรือไม่?</p>
                <p class="text-danger mb-0">
                    <small><i class="bi bi-exclamation-triangle me-1"></i>
                    วันหมดอายุคอร์สจะกลับไปเป็นวันที่ {{ $renewal->old_expiry_date?->format('d/m/Y') ?? '-' }}</small>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">ยกเลิกการต่ออายุ</button>
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
    fetch('{{ route("course-renewals.destroy", $renewal) }}', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '{{ route("course-renewals.index") }}';
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
