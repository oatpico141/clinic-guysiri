@extends('layouts.app')

@section('title', 'แก้ไขอุปกรณ์ - GCMS')

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .page-header {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        border-radius: 20px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2"><i class="bi bi-pencil me-2"></i>แก้ไขอุปกรณ์</h2>
                <p class="mb-0 opacity-90">{{ $equipment->name }}</p>
            </div>
            <a href="{{ route('equipment.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="form-card">
        <form method="POST" action="{{ route('equipment.update', $equipment) }}" id="equipmentForm">
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

            <div class="row g-4">
                <div class="col-md-4">
                    <label class="form-label">รหัสอุปกรณ์</label>
                    <input type="text" class="form-control" value="{{ $equipment->equipment_code }}" readonly>
                </div>
                <div class="col-md-8">
                    <label class="form-label">ชื่ออุปกรณ์ <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $equipment->name) }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label">รายละเอียด</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $equipment->description) }}</textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label">หมวดหมู่ <span class="text-danger">*</span></label>
                    <select name="category" class="form-select" required>
                        <option value="treatment_equipment" {{ old('category', $equipment->category) == 'treatment_equipment' ? 'selected' : '' }}>อุปกรณ์รักษา</option>
                        <option value="office_equipment" {{ old('category', $equipment->category) == 'office_equipment' ? 'selected' : '' }}>อุปกรณ์สำนักงาน</option>
                        <option value="furniture" {{ old('category', $equipment->category) == 'furniture' ? 'selected' : '' }}>เฟอร์นิเจอร์</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">สาขา <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" value="{{ $equipment->branch->name ?? 'ไม่ระบุสาขา' }}" disabled>
                    <input type="hidden" name="branch_id" value="{{ $equipment->branch_id }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">สถานะ <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="available" {{ old('status', $equipment->status) == 'available' ? 'selected' : '' }}>พร้อมใช้งาน</option>
                        <option value="in_use" {{ old('status', $equipment->status) == 'in_use' ? 'selected' : '' }}>กำลังใช้งาน</option>
                        <option value="maintenance" {{ old('status', $equipment->status) == 'maintenance' ? 'selected' : '' }}>ซ่อมบำรุง</option>
                        <option value="retired" {{ old('status', $equipment->status) == 'retired' ? 'selected' : '' }}>ปลดระวาง</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Serial Number</label>
                    <input type="text" name="serial_number" class="form-control" value="{{ old('serial_number', $equipment->serial_number) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">วันที่ซื้อ</label>
                    <input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date', $equipment->purchase_date ? $equipment->purchase_date->format('Y-m-d') : '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ราคาซื้อ</label>
                    <div class="input-group">
                        <span class="input-group-text">฿</span>
                        <input type="number" name="purchase_price" class="form-control" value="{{ old('purchase_price', $equipment->purchase_price) }}" min="0" step="0.01">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">ผู้จัดจำหน่าย</label>
                    <input type="text" name="supplier" class="form-control" value="{{ old('supplier', $equipment->supplier) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">เลขที่รับประกัน</label>
                    <input type="text" name="warranty_number" class="form-control" value="{{ old('warranty_number', $equipment->warranty_number) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">วันหมดประกัน</label>
                    <input type="date" name="warranty_expiry" class="form-control" value="{{ old('warranty_expiry', $equipment->warranty_expiry ? $equipment->warranty_expiry->format('Y-m-d') : '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">รอบซ่อมบำรุง (วัน)</label>
                    <input type="number" name="maintenance_interval_days" class="form-control" value="{{ old('maintenance_interval_days', $equipment->maintenance_interval_days) }}" min="1">
                </div>
                <div class="col-12">
                    <label class="form-label">หมายเหตุ</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $equipment->notes) }}</textarea>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-danger" onclick="deleteEquipment()">
                    <i class="bi bi-trash me-1"></i> ลบอุปกรณ์
                </button>
                <div class="d-flex gap-2">
                    <a href="{{ route('equipment.index') }}" class="btn btn-secondary">ยกเลิก</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> บันทึก
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('equipmentForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('บันทึกการแก้ไขสำเร็จ');
            window.location.href = '{{ route('equipment.index') }}';
        } else {
            alert('เกิดข้อผิดพลาด: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('เกิดข้อผิดพลาดในการบันทึก');
    });
});

function deleteEquipment() {
    if (confirm('ยืนยันการลบอุปกรณ์นี้?')) {
        fetch('{{ route('equipment.destroy', $equipment) }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('ลบอุปกรณ์สำเร็จ');
                window.location.href = '{{ route('equipment.index') }}';
            } else {
                alert('เกิดข้อผิดพลาด: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการลบ');
        });
    }
}
</script>
@endpush
