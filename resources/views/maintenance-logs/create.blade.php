@extends('layouts.app')

@section('title', 'บันทึกการซ่อมบำรุง - GCMS')

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

    .equipment-preview {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1rem;
        display: none;
    }

    .equipment-preview.active {
        display: block;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2"><i class="bi bi-wrench-adjustable me-2"></i>บันทึกการซ่อมบำรุง</h2>
                <p class="mb-0 opacity-90">บันทึกข้อมูลการซ่อมบำรุงอุปกรณ์</p>
            </div>
            <a href="{{ route('maintenance-logs.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="form-card">
        <form method="POST" action="{{ route('maintenance-logs.store') }}">
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

            <!-- Equipment Selection -->
            <div class="section-title"><i class="bi bi-gear me-2"></i>อุปกรณ์</div>
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label">สาขา <span class="text-danger">*</span></label>
                    <select name="branch_id" class="form-select" id="branchSelect" required>
                        <option value="">เลือกสาขา</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id', request('branch_id')) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">อุปกรณ์ <span class="text-danger">*</span></label>
                    <select name="equipment_id" class="form-select" id="equipmentSelect" required>
                        <option value="">เลือกอุปกรณ์</option>
                        @foreach($equipment as $eq)
                        <option value="{{ $eq->id }}" data-branch="{{ $eq->branch_id }}" {{ old('equipment_id', request('equipment_id')) == $eq->id ? 'selected' : '' }}>
                            {{ $eq->name }} ({{ $eq->equipment_code ?? '-' }})
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Maintenance Type -->
            <div class="section-title"><i class="bi bi-tag me-2"></i>ประเภทการซ่อมบำรุง</div>
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <label class="type-option d-block" id="typePreventive">
                        <input type="radio" name="maintenance_type" value="preventive" {{ old('maintenance_type', 'preventive') == 'preventive' ? 'checked' : '' }}>
                        <i class="bi bi-shield-check type-preventive d-block"></i>
                        <div class="fw-semibold">ป้องกัน</div>
                        <small class="text-muted">Preventive</small>
                    </label>
                </div>
                <div class="col-md-3 col-6">
                    <label class="type-option d-block" id="typeCorrective">
                        <input type="radio" name="maintenance_type" value="corrective" {{ old('maintenance_type') == 'corrective' ? 'checked' : '' }}>
                        <i class="bi bi-tools type-corrective d-block"></i>
                        <div class="fw-semibold">แก้ไข</div>
                        <small class="text-muted">Corrective</small>
                    </label>
                </div>
                <div class="col-md-3 col-6">
                    <label class="type-option d-block" id="typeEmergency">
                        <input type="radio" name="maintenance_type" value="emergency" {{ old('maintenance_type') == 'emergency' ? 'checked' : '' }}>
                        <i class="bi bi-exclamation-triangle type-emergency d-block"></i>
                        <div class="fw-semibold">ฉุกเฉิน</div>
                        <small class="text-muted">Emergency</small>
                    </label>
                </div>
                <div class="col-md-3 col-6">
                    <label class="type-option d-block" id="typeInspection">
                        <input type="radio" name="maintenance_type" value="inspection" {{ old('maintenance_type') == 'inspection' ? 'checked' : '' }}>
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
                    <input type="date" name="maintenance_date" class="form-control" value="{{ old('maintenance_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">สถานะ <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>รอดำเนินการ</option>
                        <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>กำลังดำเนินการ</option>
                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>เสร็จสิ้น</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">รายละเอียด <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="3" placeholder="อธิบายรายละเอียดการซ่อมบำรุง..." required>{{ old('description') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">งานที่ดำเนินการ</label>
                    <textarea name="work_performed" class="form-control" rows="2" placeholder="รายการงานที่ได้ทำ...">{{ old('work_performed') }}</textarea>
                </div>
            </div>

            <!-- Cost & Service -->
            <div class="section-title"><i class="bi bi-cash me-2"></i>ค่าใช้จ่ายและผู้ให้บริการ</div>
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="form-label">ค่าใช้จ่าย (บาท)</label>
                    <div class="input-group">
                        <span class="input-group-text">฿</span>
                        <input type="number" name="cost" class="form-control" value="{{ old('cost', 0) }}" min="0" step="0.01">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">ผู้ให้บริการ</label>
                    <input type="text" name="service_provider" class="form-control" value="{{ old('service_provider') }}" placeholder="ชื่อบริษัท/ช่าง">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ผู้ดำเนินการ</label>
                    <input type="text" name="performed_by" class="form-control" value="{{ old('performed_by') }}" placeholder="ชื่อผู้ดำเนินการ">
                </div>
            </div>

            <!-- Next Maintenance -->
            <div class="section-title"><i class="bi bi-calendar-event me-2"></i>กำหนดการครั้งถัดไป</div>
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label">วันที่ซ่อมบำรุงครั้งถัดไป</label>
                    <input type="date" name="next_maintenance_date" class="form-control" value="{{ old('next_maintenance_date') }}">
                    <small class="text-muted">ระบุเพื่อแจ้งเตือนเมื่อถึงกำหนด</small>
                </div>
            </div>

            <!-- Notes -->
            <div class="section-title"><i class="bi bi-sticky me-2"></i>หมายเหตุ</div>
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <textarea name="notes" class="form-control" rows="2" placeholder="หมายเหตุเพิ่มเติม (ถ้ามี)...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('maintenance-logs.index') }}" class="btn btn-secondary">ยกเลิก</a>
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

    // Filter equipment by branch
    const branchSelect = document.getElementById('branchSelect');
    const equipmentSelect = document.getElementById('equipmentSelect');
    const equipmentOptions = equipmentSelect.querySelectorAll('option[data-branch]');

    function filterEquipment() {
        const selectedBranch = branchSelect.value;

        equipmentOptions.forEach(opt => {
            if (!selectedBranch || opt.dataset.branch === selectedBranch) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
                if (opt.selected) {
                    equipmentSelect.value = '';
                }
            }
        });
    }

    branchSelect.addEventListener('change', filterEquipment);
    filterEquipment();
});
</script>
@endpush
