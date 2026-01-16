@extends('layouts.app')

@section('title', 'แก้ไขการซ่อมบำรุง - GCMS')

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .page-header {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
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
        border-color: #7c3aed;
    }

    .type-option.selected {
        border-color: #7c3aed;
        background: #f5f3ff;
    }

    .type-option input[type="radio"] {
        display: none;
    }

    .type-option i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .type-preventive { color: #1e40af; }
    .type-corrective { color: #92400e; }
    .type-emergency { color: #991b1b; }
    .type-inspection { color: #065f46; }

    .equipment-info {
        background: #f0fdf4;
        border: 2px solid #22c55e;
        border-radius: 12px;
        padding: 1rem;
    }

    .maint-number-display {
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
                <h2 class="mb-2"><i class="bi bi-pencil me-2"></i>แก้ไขการซ่อมบำรุง</h2>
                <p class="mb-0 opacity-90">
                    <span class="maint-number-display">{{ $maintenanceLog->maintenance_number }}</span>
                </p>
            </div>
            <a href="{{ route('maintenance-logs.show', $maintenanceLog) }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="form-card">
        <form method="POST" action="{{ route('maintenance-logs.update', $maintenanceLog) }}">
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

            <!-- Equipment Info (Read-only) -->
            <div class="section-title"><i class="bi bi-gear me-2"></i>อุปกรณ์</div>
            <div class="equipment-info mb-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div style="width: 50px; height: 50px; border-radius: 12px; background: linear-gradient(135deg, #7c3aed, #6d28d9); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="bi bi-gear"></i>
                        </div>
                    </div>
                    <div>
                        <h5 class="mb-0">{{ $maintenanceLog->equipment->name ?? '-' }}</h5>
                        <small class="text-muted">
                            <i class="bi bi-building me-1"></i>{{ $maintenanceLog->branch->name ?? '-' }}
                        </small>
                    </div>
                </div>
                <input type="hidden" name="equipment_id" value="{{ $maintenanceLog->equipment_id }}">
                <input type="hidden" name="branch_id" value="{{ $maintenanceLog->branch_id }}">
            </div>

            <!-- Maintenance Type -->
            <div class="section-title"><i class="bi bi-tag me-2"></i>ประเภทการซ่อมบำรุง</div>
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <label class="type-option d-block" id="typePreventive">
                        <input type="radio" name="maintenance_type" value="preventive" {{ old('maintenance_type', $maintenanceLog->maintenance_type) == 'preventive' ? 'checked' : '' }}>
                        <i class="bi bi-shield-check type-preventive d-block"></i>
                        <div class="fw-semibold">ป้องกัน</div>
                        <small class="text-muted">Preventive</small>
                    </label>
                </div>
                <div class="col-md-3 col-6">
                    <label class="type-option d-block" id="typeCorrective">
                        <input type="radio" name="maintenance_type" value="corrective" {{ old('maintenance_type', $maintenanceLog->maintenance_type) == 'corrective' ? 'checked' : '' }}>
                        <i class="bi bi-tools type-corrective d-block"></i>
                        <div class="fw-semibold">แก้ไข</div>
                        <small class="text-muted">Corrective</small>
                    </label>
                </div>
                <div class="col-md-3 col-6">
                    <label class="type-option d-block" id="typeEmergency">
                        <input type="radio" name="maintenance_type" value="emergency" {{ old('maintenance_type', $maintenanceLog->maintenance_type) == 'emergency' ? 'checked' : '' }}>
                        <i class="bi bi-exclamation-triangle type-emergency d-block"></i>
                        <div class="fw-semibold">ฉุกเฉิน</div>
                        <small class="text-muted">Emergency</small>
                    </label>
                </div>
                <div class="col-md-3 col-6">
                    <label class="type-option d-block" id="typeInspection">
                        <input type="radio" name="maintenance_type" value="inspection" {{ old('maintenance_type', $maintenanceLog->maintenance_type) == 'inspection' ? 'checked' : '' }}>
                        <i class="bi bi-search type-inspection d-block"></i>
                        <div class="fw-semibold">ตรวจสอบ</div>
                        <small class="text-muted">Inspection</small>
                    </label>
                </div>
            </div>

            <!-- Details -->
            <div class="section-title"><i class="bi bi-info-circle me-2"></i>รายละเอียด</div>
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label">วันที่ดำเนินการ <span class="text-danger">*</span></label>
                    <input type="date" name="maintenance_date" class="form-control" value="{{ old('maintenance_date', $maintenanceLog->maintenance_date ? \Carbon\Carbon::parse($maintenanceLog->maintenance_date)->format('Y-m-d') : '') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">สถานะ <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="pending" {{ old('status', $maintenanceLog->status) == 'pending' ? 'selected' : '' }}>รอดำเนินการ</option>
                        <option value="in_progress" {{ old('status', $maintenanceLog->status) == 'in_progress' ? 'selected' : '' }}>กำลังดำเนินการ</option>
                        <option value="completed" {{ old('status', $maintenanceLog->status) == 'completed' ? 'selected' : '' }}>เสร็จสิ้น</option>
                        <option value="cancelled" {{ old('status', $maintenanceLog->status) == 'cancelled' ? 'selected' : '' }}>ยกเลิก</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">รายละเอียด <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="3" required>{{ old('description', $maintenanceLog->description) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">งานที่ดำเนินการ</label>
                    <textarea name="work_performed" class="form-control" rows="2">{{ old('work_performed', $maintenanceLog->work_performed) }}</textarea>
                </div>
            </div>

            <!-- Cost & Service -->
            <div class="section-title"><i class="bi bi-cash me-2"></i>ค่าใช้จ่ายและผู้ให้บริการ</div>
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="form-label">ค่าใช้จ่าย (บาท)</label>
                    <div class="input-group">
                        <span class="input-group-text">฿</span>
                        <input type="number" name="cost" class="form-control" value="{{ old('cost', $maintenanceLog->cost) }}" min="0" step="0.01">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">ผู้ให้บริการ</label>
                    <input type="text" name="service_provider" class="form-control" value="{{ old('service_provider', $maintenanceLog->service_provider) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ผู้ดำเนินการ</label>
                    <input type="text" name="performed_by" class="form-control" value="{{ old('performed_by', $maintenanceLog->performed_by) }}">
                </div>
            </div>

            <!-- Next Maintenance -->
            <div class="section-title"><i class="bi bi-calendar-event me-2"></i>กำหนดการครั้งถัดไป</div>
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label">วันที่ซ่อมบำรุงครั้งถัดไป</label>
                    <input type="date" name="next_maintenance_date" class="form-control" value="{{ old('next_maintenance_date', $maintenanceLog->next_maintenance_date ? \Carbon\Carbon::parse($maintenanceLog->next_maintenance_date)->format('Y-m-d') : '') }}">
                    <small class="text-muted">ระบุเพื่อแจ้งเตือนเมื่อถึงกำหนด</small>
                </div>
            </div>

            <!-- Notes -->
            <div class="section-title"><i class="bi bi-sticky me-2"></i>หมายเหตุ</div>
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $maintenanceLog->notes) }}</textarea>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                    <i class="bi bi-trash me-1"></i> ลบ
                </button>
                <div class="d-flex gap-2">
                    <a href="{{ route('maintenance-logs.show', $maintenanceLog) }}" class="btn btn-secondary">ยกเลิก</a>
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
                <p>คุณต้องการลบรายการซ่อมบำรุง <strong>{{ $maintenanceLog->maintenance_number }}</strong> หรือไม่?</p>
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
});

function confirmDelete() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    fetch('{{ route("maintenance-logs.destroy", $maintenanceLog) }}', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '{{ route("maintenance-logs.index") }}';
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
