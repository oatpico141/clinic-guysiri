@extends('layouts.app')

@section('title', 'เพิ่มสินค้า - GCMS')

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
                <h2 class="mb-2"><i class="bi bi-plus-circle me-2"></i>เพิ่มสินค้า</h2>
                <p class="mb-0 opacity-90">เพิ่มวัสดุสิ้นเปลืองใหม่</p>
            </div>
            <a href="{{ route('stock-items.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="form-card">
        <form method="POST" action="{{ route('stock-items.store') }}">
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

            <!-- Basic Info -->
            <div class="section-title"><i class="bi bi-info-circle me-2"></i>ข้อมูลพื้นฐาน</div>
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="form-label">รหัสสินค้า</label>
                    <input type="text" name="item_code" class="form-control" value="{{ old('item_code') }}" placeholder="ระบบจะสร้างให้อัตโนมัติ">
                    <small class="text-muted">เว้นว่างให้ระบบสร้างอัตโนมัติ</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">ชื่อสินค้า <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">หมวดหมู่</label>
                    <input type="text" name="category" class="form-control" value="{{ old('category') }}" list="categoryList" placeholder="พิมพ์หรือเลือก">
                    <datalist id="categoryList">
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}">
                        @endforeach
                        <option value="วัสดุทางการแพทย์">
                        <option value="เวชภัณฑ์">
                        <option value="อุปกรณ์สำนักงาน">
                        <option value="วัสดุทำความสะอาด">
                    </datalist>
                </div>
                <div class="col-md-6">
                    <label class="form-label">หน่วย <span class="text-danger">*</span></label>
                    <input type="text" name="unit" class="form-control" value="{{ old('unit') }}" required list="unitList" placeholder="เช่น ชิ้น, กล่อง, แพ็ค">
                    <datalist id="unitList">
                        <option value="ชิ้น">
                        <option value="กล่อง">
                        <option value="แพ็ค">
                        <option value="ขวด">
                        <option value="หลอด">
                        <option value="ม้วน">
                        <option value="ถุง">
                    </datalist>
                </div>
                <div class="col-md-6">
                    <label class="form-label">สาขา <span class="text-danger">*</span></label>
                    <select name="branch_id" class="form-select" required>
                        <option value="">เลือกสาขา</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">รายละเอียด</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                </div>
            </div>

            <!-- Quantity -->
            <div class="section-title"><i class="bi bi-boxes me-2"></i>จำนวนและราคา</div>
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <label class="form-label">จำนวนเริ่มต้น</label>
                    <input type="number" name="quantity_on_hand" class="form-control" value="{{ old('quantity_on_hand', 0) }}" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label">จำนวนขั้นต่ำ</label>
                    <input type="number" name="minimum_quantity" class="form-control" value="{{ old('minimum_quantity', 0) }}" min="0">
                    <small class="text-muted">แจ้งเตือนเมื่อต่ำกว่านี้</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">จำนวนสูงสุด</label>
                    <input type="number" name="maximum_quantity" class="form-control" value="{{ old('maximum_quantity') }}" min="0">
                </div>
                <div class="col-md-3">
                    &nbsp;
                </div>
                <div class="col-md-3">
                    <label class="form-label">ราคาต้นทุน (บาท)</label>
                    <div class="input-group">
                        <span class="input-group-text">฿</span>
                        <input type="number" name="unit_cost" class="form-control" value="{{ old('unit_cost') }}" min="0" step="0.01">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">ราคาขาย (บาท)</label>
                    <div class="input-group">
                        <span class="input-group-text">฿</span>
                        <input type="number" name="unit_price" class="form-control" value="{{ old('unit_price') }}" min="0" step="0.01">
                    </div>
                </div>
            </div>

            <!-- Supplier -->
            <div class="section-title"><i class="bi bi-truck me-2"></i>ผู้จำหน่าย</div>
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label">ชื่อผู้จำหน่าย</label>
                    <input type="text" name="supplier" class="form-control" value="{{ old('supplier') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">รหัสสินค้าของผู้จำหน่าย</label>
                    <input type="text" name="supplier_item_code" class="form-control" value="{{ old('supplier_item_code') }}">
                </div>
            </div>

            <!-- Notes -->
            <div class="section-title"><i class="bi bi-sticky me-2"></i>หมายเหตุ</div>
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <textarea name="notes" class="form-control" rows="2" placeholder="หมายเหตุเพิ่มเติม...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('stock-items.index') }}" class="btn btn-secondary">ยกเลิก</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> บันทึก
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
