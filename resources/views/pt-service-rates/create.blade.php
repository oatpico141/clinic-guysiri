@extends('layouts.app')

@section('title', 'เพิ่มอัตราค่าบริการ PT - GCMS')

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .page-header {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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

    .pt-select-card {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .pt-select-card:hover {
        border-color: #f59e0b;
        background: #fffbeb;
    }

    .pt-select-card.selected {
        border-color: #f59e0b;
        background: #fef3c7;
    }

    .pt-select-card input {
        display: none;
    }

    .preview-box {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1.5rem;
    }

    .preview-label {
        font-size: 0.75rem;
        color: #6b7280;
        text-transform: uppercase;
        margin-bottom: 0.25rem;
    }

    .preview-value {
        font-size: 1.25rem;
        font-weight: 700;
    }

    .commission-preview { color: #059669; }
    .df-preview { color: #7c3aed; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2"><i class="bi bi-plus-lg me-2"></i>เพิ่มอัตราค่าบริการ PT</h2>
                <p class="mb-0 opacity-90">กำหนดราคาและค่าคอมมิชชั่นสำหรับ PT</p>
            </div>
            <a href="{{ route('pt-service-rates.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Form -->
            <div class="form-card">
                <form method="POST" action="{{ route('pt-service-rates.store') }}" id="rateForm">
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

                    <!-- PT Selection -->
                    <div class="section-title"><i class="bi bi-person me-2"></i>เลือก PT</div>
                    <div class="mb-4">
                        <select name="pt_id" class="form-select form-select-lg" id="ptSelect" required>
                            <option value="">-- เลือก PT --</option>
                            @foreach($pts as $pt)
                            <option value="{{ $pt->id }}" {{ old('pt_id') == $pt->id ? 'selected' : '' }}>{{ $pt->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Service & Branch -->
                    <div class="section-title"><i class="bi bi-grid me-2"></i>บริการและสาขา</div>
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">บริการ <span class="text-danger">*</span></label>
                            <select name="service_id" class="form-select" id="serviceSelect" required>
                                <option value="">-- เลือกบริการ --</option>
                                @foreach($services as $service)
                                <option value="{{ $service->id }}" data-price="{{ $service->price ?? 0 }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }}
                                    @if($service->price) (฿{{ number_format($service->price, 0) }}) @endif
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">สาขา <span class="text-danger">*</span></label>
                            <select name="branch_id" class="form-select" required>
                                <option value="">-- เลือกสาขา --</option>
                                @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="section-title"><i class="bi bi-currency-exchange me-2"></i>ราคาและค่าตอบแทน</div>
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">ราคาบริการ (บาท) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">฿</span>
                                <input type="number" name="price" class="form-control" id="priceInput" value="{{ old('price', 0) }}" min="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Commission Rate (%)</label>
                            <div class="input-group">
                                <input type="number" name="commission_rate" class="form-control" id="commissionInput" value="{{ old('commission_rate') }}" min="0" max="100" step="0.1">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">คิดเป็นเปอร์เซ็นต์จากราคาบริการ</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">DF Rate (บาท)</label>
                            <div class="input-group">
                                <span class="input-group-text">฿</span>
                                <input type="number" name="df_rate" class="form-control" id="dfInput" value="{{ old('df_rate') }}" min="0" step="0.01">
                            </div>
                            <small class="text-muted">ค่ามือคงที่ต่อครั้ง</small>
                        </div>
                    </div>

                    <!-- Effective Period -->
                    <div class="section-title"><i class="bi bi-calendar me-2"></i>ช่วงเวลา</div>
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">เริ่มมีผล</label>
                            <input type="date" name="effective_from" class="form-control" value="{{ old('effective_from') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">สิ้นสุด</label>
                            <input type="date" name="effective_to" class="form-control" value="{{ old('effective_to') }}">
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="section-title"><i class="bi bi-toggles me-2"></i>สถานะ</div>
                    <div class="mb-4">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">ใช้งาน</label>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('pt-service-rates.index') }}" class="btn btn-secondary">ยกเลิก</a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg me-1"></i> บันทึก
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Preview -->
            <div class="form-card">
                <h5 class="mb-3"><i class="bi bi-calculator me-2"></i>ตัวอย่างการคำนวณ</h5>
                <div class="preview-box mb-3">
                    <div class="preview-label">ราคาบริการ</div>
                    <div class="preview-value" id="previewPrice">฿0</div>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="preview-box">
                            <div class="preview-label">Commission</div>
                            <div class="preview-value commission-preview" id="previewCommission">฿0</div>
                            <small class="text-muted" id="previewCommissionPct">0%</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="preview-box">
                            <div class="preview-label">DF (ค่ามือ)</div>
                            <div class="preview-value df-preview" id="previewDf">฿0</div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="preview-box" style="background: #fef3c7;">
                    <div class="preview-label">รวมค่าตอบแทน PT</div>
                    <div class="preview-value text-warning" id="previewTotal">฿0</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const priceInput = document.getElementById('priceInput');
    const commissionInput = document.getElementById('commissionInput');
    const dfInput = document.getElementById('dfInput');
    const serviceSelect = document.getElementById('serviceSelect');

    function updatePreview() {
        const price = parseFloat(priceInput.value) || 0;
        const commissionRate = parseFloat(commissionInput.value) || 0;
        const dfRate = parseFloat(dfInput.value) || 0;

        const commissionAmount = price * (commissionRate / 100);
        const total = commissionAmount + dfRate;

        document.getElementById('previewPrice').textContent = '฿' + price.toLocaleString('th-TH', {minimumFractionDigits: 2});
        document.getElementById('previewCommission').textContent = '฿' + commissionAmount.toLocaleString('th-TH', {minimumFractionDigits: 2});
        document.getElementById('previewCommissionPct').textContent = commissionRate + '%';
        document.getElementById('previewDf').textContent = '฿' + dfRate.toLocaleString('th-TH', {minimumFractionDigits: 2});
        document.getElementById('previewTotal').textContent = '฿' + total.toLocaleString('th-TH', {minimumFractionDigits: 2});
    }

    // Update price when service changes
    serviceSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        const servicePrice = option.dataset.price || 0;
        priceInput.value = servicePrice;
        updatePreview();
    });

    // Update preview on input change
    priceInput.addEventListener('input', updatePreview);
    commissionInput.addEventListener('input', updatePreview);
    dfInput.addEventListener('input', updatePreview);

    // Initial preview
    updatePreview();
});
</script>
@endpush
