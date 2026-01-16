@extends('layouts.app')

@section('title', 'แก้ไขสินค้า - GCMS')

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .page-header {
        background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
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
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2"><i class="bi bi-pencil me-2"></i>แก้ไขสินค้า</h2>
                <p class="mb-0 opacity-90">{{ $stockItem->item_code }} - {{ $stockItem->name }}</p>
            </div>
            <a href="{{ route('stock-items.show', $stockItem) }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="form-card">
        <form method="POST" action="{{ route('stock-items.update', $stockItem) }}">
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

            <!-- Basic Info -->
            <div class="section-title"><i class="bi bi-info-circle me-2"></i>ข้อมูลพื้นฐาน</div>
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="form-label">รหัสสินค้า</label>
                    <input type="text" name="item_code" class="form-control" value="{{ old('item_code', $stockItem->item_code) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ชื่อสินค้า <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $stockItem->name) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">หมวดหมู่</label>
                    <input type="text" name="category" class="form-control" value="{{ old('category', $stockItem->category) }}" list="categoryList">
                    <datalist id="categoryList">
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}">
                        @endforeach
                    </datalist>
                </div>
                <div class="col-md-6">
                    <label class="form-label">หน่วย <span class="text-danger">*</span></label>
                    <input type="text" name="unit" class="form-control" value="{{ old('unit', $stockItem->unit) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">สาขา <span class="text-danger">*</span></label>
                    <select name="branch_id" class="form-select" required>
                        <option value="">เลือกสาขา</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id', $stockItem->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">รายละเอียด</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $stockItem->description) }}</textarea>
                </div>
            </div>

            <!-- Stock Levels -->
            <div class="section-title"><i class="bi bi-boxes me-2"></i>ระดับสต็อก</div>
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="form-label">คงเหลือปัจจุบัน</label>
                    <div class="form-control-plaintext fw-bold text-primary fs-5">
                        {{ number_format($stockItem->quantity_on_hand) }} {{ $stockItem->unit }}
                    </div>
                    <small class="text-muted">ปรับจำนวนที่หน้ารายละเอียด</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">จำนวนขั้นต่ำ</label>
                    <input type="number" name="minimum_quantity" class="form-control" value="{{ old('minimum_quantity', $stockItem->minimum_quantity) }}" min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">จำนวนสูงสุด</label>
                    <input type="number" name="maximum_quantity" class="form-control" value="{{ old('maximum_quantity', $stockItem->maximum_quantity) }}" min="0">
                </div>
            </div>

            <!-- Pricing -->
            <div class="section-title"><i class="bi bi-currency-exchange me-2"></i>ราคา</div>
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="form-label">ราคาต้นทุน (บาท)</label>
                    <div class="input-group">
                        <span class="input-group-text">฿</span>
                        <input type="number" name="unit_cost" class="form-control" value="{{ old('unit_cost', $stockItem->unit_cost) }}" min="0" step="0.01">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">ราคาขาย (บาท)</label>
                    <div class="input-group">
                        <span class="input-group-text">฿</span>
                        <input type="number" name="unit_price" class="form-control" value="{{ old('unit_price', $stockItem->unit_price) }}" min="0" step="0.01">
                    </div>
                </div>
            </div>

            <!-- Supplier -->
            <div class="section-title"><i class="bi bi-truck me-2"></i>ผู้จำหน่าย</div>
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label">ชื่อผู้จำหน่าย</label>
                    <input type="text" name="supplier" class="form-control" value="{{ old('supplier', $stockItem->supplier) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">รหัสสินค้าของผู้จำหน่าย</label>
                    <input type="text" name="supplier_item_code" class="form-control" value="{{ old('supplier_item_code', $stockItem->supplier_item_code) }}">
                </div>
            </div>

            <!-- Status -->
            <div class="section-title"><i class="bi bi-toggles me-2"></i>สถานะ</div>
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive" {{ old('is_active', $stockItem->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">ใช้งาน</label>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="section-title"><i class="bi bi-sticky me-2"></i>หมายเหตุ</div>
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $stockItem->notes) }}</textarea>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                    <i class="bi bi-trash me-1"></i> ลบ
                </button>
                <div class="d-flex gap-2">
                    <a href="{{ route('stock-items.show', $stockItem) }}" class="btn btn-secondary">ยกเลิก</a>
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
                <p>คุณต้องการลบสินค้า <strong>{{ $stockItem->name }}</strong> หรือไม่?</p>
                <p class="text-danger mb-0"><small>หมายเหตุ: ไม่สามารถลบได้หากมีประวัติการเคลื่อนไหว</small></p>
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
    fetch('{{ route("stock-items.destroy", $stockItem) }}', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '{{ route("stock-items.index") }}';
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
