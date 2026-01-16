@extends('layouts.app')

@section('title', 'แก้ไขอัตราค่าบริการ PT - GCMS')

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

    .current-info {
        background: #fef3c7;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1.5rem;
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
                <h2 class="mb-2"><i class="bi bi-pencil me-2"></i>แก้ไขอัตราค่าบริการ PT</h2>
                <p class="mb-0 opacity-90">
                    {{ $rate->pt->name ?? '-' }} - {{ $rate->service->name ?? '-' }}
                </p>
            </div>
            <a href="{{ route('pt-service-rates.show', $rate) }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Form -->
            <div class="form-card">
                <form method="POST" action="{{ route('pt-service-rates.update', $rate) }}" id="rateForm">
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

                    <!-- Current Info -->
                    <div class="current-info">
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted">PT</small>
                                <div class="fw-bold">{{ $rate->pt->name ?? '-' }}</div>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">บริการ</small>
                                <div class="fw-bold">{{ $rate->service->name ?? '-' }}</div>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">สาขา</small>
                                <div class="fw-bold">{{ $rate->branch->name ?? '-' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- PT Selection -->
                    <div class="section-title"><i class="bi bi-person me-2"></i>PT</div>
                    <div class="mb-4">
                        <select name="pt_id" class="form-select" required>
                            <option value="">-- เลือก PT --</option>
                            @foreach($pts as $pt)
                            <option value="{{ $pt->id }}" {{ old('pt_id', $rate->pt_id) == $pt->id ? 'selected' : '' }}>{{ $pt->name }}</option>
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
                                <option value="{{ $service->id }}" data-price="{{ $service->price ?? 0 }}" {{ old('service_id', $rate->service_id) == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">สาขา <span class="text-danger">*</span></label>
                            <select name="branch_id" class="form-select" required>
                                <option value="">-- เลือกสาขา --</option>
                                @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id', $rate->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
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
                                <input type="number" name="price" class="form-control" id="priceInput" value="{{ old('price', $rate->price) }}" min="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Commission Rate (%)</label>
                            <div class="input-group">
                                <input type="number" name="commission_rate" class="form-control" id="commissionInput" value="{{ old('commission_rate', $rate->commission_rate) }}" min="0" max="100" step="0.1">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">คิดเป็นเปอร์เซ็นต์จากราคาบริการ</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">DF Rate (บาท)</label>
                            <div class="input-group">
                                <span class="input-group-text">฿</span>
                                <input type="number" name="df_rate" class="form-control" id="dfInput" value="{{ old('df_rate', $rate->df_rate) }}" min="0" step="0.01">
                            </div>
                            <small class="text-muted">ค่ามือคงที่ต่อครั้ง</small>
                        </div>
                    </div>

                    <!-- Effective Period -->
                    <div class="section-title"><i class="bi bi-calendar me-2"></i>ช่วงเวลา</div>
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">เริ่มมีผล</label>
                            <input type="date" name="effective_from" class="form-control" value="{{ old('effective_from', $rate->effective_from?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">สิ้นสุด</label>
                            <input type="date" name="effective_to" class="form-control" value="{{ old('effective_to', $rate->effective_to?->format('Y-m-d')) }}">
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="section-title"><i class="bi bi-toggles me-2"></i>สถานะ</div>
                    <div class="mb-4">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive" {{ old('is_active', $rate->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">ใช้งาน</label>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                            <i class="bi bi-trash me-1"></i> ลบ
                        </button>
                        <div class="d-flex gap-2">
                            <a href="{{ route('pt-service-rates.show', $rate) }}" class="btn btn-secondary">ยกเลิก</a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-lg me-1"></i> บันทึก
                            </button>
                        </div>
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
                    <div class="preview-value" id="previewPrice">฿{{ number_format($rate->price, 2) }}</div>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="preview-box">
                            <div class="preview-label">Commission</div>
                            <div class="preview-value commission-preview" id="previewCommission">
                                ฿{{ number_format($rate->price * ($rate->commission_rate ?? 0) / 100, 2) }}
                            </div>
                            <small class="text-muted" id="previewCommissionPct">{{ $rate->commission_rate ?? 0 }}%</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="preview-box">
                            <div class="preview-label">DF (ค่ามือ)</div>
                            <div class="preview-value df-preview" id="previewDf">฿{{ number_format($rate->df_rate ?? 0, 2) }}</div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="preview-box" style="background: #fef3c7;">
                    <div class="preview-label">รวมค่าตอบแทน PT</div>
                    @php
                        $total = ($rate->price * ($rate->commission_rate ?? 0) / 100) + ($rate->df_rate ?? 0);
                    @endphp
                    <div class="preview-value text-warning" id="previewTotal">฿{{ number_format($total, 2) }}</div>
                </div>
            </div>
        </div>
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
                <p>คุณต้องการลบอัตราค่าบริการนี้หรือไม่?</p>
                <div class="alert alert-warning mb-0">
                    <strong>PT:</strong> {{ $rate->pt->name ?? '-' }}<br>
                    <strong>บริการ:</strong> {{ $rate->service->name ?? '-' }}<br>
                    <strong>สาขา:</strong> {{ $rate->branch->name ?? '-' }}
                </div>
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
    const priceInput = document.getElementById('priceInput');
    const commissionInput = document.getElementById('commissionInput');
    const dfInput = document.getElementById('dfInput');

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

    // Update preview on input change
    priceInput.addEventListener('input', updatePreview);
    commissionInput.addEventListener('input', updatePreview);
    dfInput.addEventListener('input', updatePreview);
});

function confirmDelete() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    fetch('{{ route("pt-service-rates.destroy", $rate) }}', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '{{ route("pt-service-rates.index") }}';
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
