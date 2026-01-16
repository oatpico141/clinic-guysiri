@extends('layouts.app')

@section('title', 'รายละเอียดอัตราค่าบริการ - GCMS')

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border-radius: 20px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }

    .detail-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        margin-bottom: 1.5rem;
    }

    .section-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e5e7eb;
    }

    .info-item {
        padding: 0.75rem 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-label {
        font-size: 0.85rem;
        color: #6b7280;
        margin-bottom: 0.25rem;
    }

    .info-value {
        font-weight: 500;
        color: #1f2937;
    }

    .pt-badge-lg {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 25px;
        font-size: 1rem;
        font-weight: 600;
        display: inline-block;
    }

    .service-card {
        background: #f0f9ff;
        border-radius: 12px;
        padding: 1.5rem;
        border-left: 4px solid #0ea5e9;
    }

    .price-display-lg {
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 2rem;
        font-weight: 700;
        color: #059669;
    }

    .rate-box {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
    }

    .rate-value {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .rate-label {
        font-size: 0.85rem;
        color: #6b7280;
    }

    .commission-value { color: #059669; }
    .df-value { color: #7c3aed; }

    .status-badge-lg {
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-weight: 600;
    }

    .status-active { background: #d1fae5; color: #059669; }
    .status-inactive { background: #fee2e2; color: #dc2626; }

    .calculation-box {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-radius: 12px;
        padding: 1.5rem;
    }

    .calculation-total {
        font-size: 1.75rem;
        font-weight: 700;
        color: #92400e;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="mb-2"><i class="bi bi-currency-exchange me-2"></i>รายละเอียดอัตราค่าบริการ</h2>
                <p class="mb-0 opacity-90">ข้อมูลราคาและค่าตอบแทน PT</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('pt-service-rates.edit', $rate) }}" class="btn btn-light">
                    <i class="bi bi-pencil me-1"></i> แก้ไข
                </a>
                <a href="{{ route('pt-service-rates.index') }}" class="btn btn-outline-light">
                    <i class="bi bi-arrow-left me-1"></i> กลับ
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- PT & Service Info -->
            <div class="detail-card">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <span class="pt-badge-lg">
                        <i class="bi bi-person me-2"></i>{{ $rate->pt->name ?? '-' }}
                    </span>
                    @if($rate->is_active)
                    <span class="status-badge-lg status-active"><i class="bi bi-check-circle me-1"></i>ใช้งาน</span>
                    @else
                    <span class="status-badge-lg status-inactive"><i class="bi bi-x-circle me-1"></i>ไม่ใช้งาน</span>
                    @endif
                </div>

                <div class="service-card mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="info-label">บริการ</div>
                            <h4 class="mb-1">{{ $rate->service->name ?? '-' }}</h4>
                            <span class="text-muted">
                                <i class="bi bi-building me-1"></i>{{ $rate->branch->name ?? '-' }}
                            </span>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="info-label">ราคา</div>
                            <div class="price-display-lg">฿{{ number_format($rate->price, 2) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Rates -->
                <div class="section-title"><i class="bi bi-cash-stack me-2"></i>อัตราค่าตอบแทน</div>
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="rate-box">
                            <div class="rate-label">Commission Rate</div>
                            <div class="rate-value commission-value">
                                @if($rate->commission_rate)
                                {{ number_format($rate->commission_rate, 1) }}%
                                @else
                                -
                                @endif
                            </div>
                            @if($rate->commission_rate)
                            <small class="text-muted">
                                = ฿{{ number_format($rate->price * ($rate->commission_rate / 100), 2) }}
                            </small>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="rate-box">
                            <div class="rate-label">DF Rate (ค่ามือ)</div>
                            <div class="rate-value df-value">
                                @if($rate->df_rate)
                                ฿{{ number_format($rate->df_rate, 2) }}
                                @else
                                -
                                @endif
                            </div>
                            <small class="text-muted">คงที่ต่อครั้ง</small>
                        </div>
                    </div>
                </div>

                <!-- Calculation -->
                <div class="calculation-box">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="info-label">รวมค่าตอบแทน PT ต่อครั้ง</div>
                            <small class="text-muted">
                                @if($rate->commission_rate)
                                Commission: ฿{{ number_format($rate->price * ($rate->commission_rate / 100), 2) }}
                                @endif
                                @if($rate->commission_rate && $rate->df_rate) + @endif
                                @if($rate->df_rate)
                                DF: ฿{{ number_format($rate->df_rate, 2) }}
                                @endif
                            </small>
                        </div>
                        <div class="col-md-4 text-md-end">
                            @php
                                $totalCompensation = ($rate->price * ($rate->commission_rate ?? 0) / 100) + ($rate->df_rate ?? 0);
                            @endphp
                            <div class="calculation-total">฿{{ number_format($totalCompensation, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Period -->
            @if($rate->effective_from || $rate->effective_to)
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-calendar me-2"></i>ช่วงเวลามีผล</div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">เริ่มมีผล</div>
                            <div class="info-value">{{ $rate->effective_from?->format('d/m/Y') ?? 'ไม่ระบุ' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">สิ้นสุด</div>
                            <div class="info-value">{{ $rate->effective_to?->format('d/m/Y') ?? 'ไม่ระบุ' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Meta Info -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-info-circle me-2"></i>ข้อมูลเพิ่มเติม</div>

                <div class="info-item">
                    <div class="info-label">สร้างโดย</div>
                    <div class="info-value">{{ $rate->createdBy->name ?? '-' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">สร้างเมื่อ</div>
                    <div class="info-value">{{ $rate->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">อัปเดตล่าสุด</div>
                    <div class="info-value">{{ $rate->updated_at?->format('d/m/Y H:i') ?? '-' }}</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-lightning me-2"></i>ดำเนินการ</div>

                <div class="d-grid gap-2">
                    <a href="{{ route('pt-service-rates.edit', $rate) }}" class="btn btn-outline-warning">
                        <i class="bi bi-pencil me-2"></i>แก้ไข
                    </a>
                    <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                        <i class="bi bi-trash me-2"></i>ลบ
                    </button>
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
