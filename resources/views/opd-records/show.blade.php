@extends('layouts.app')

@section('title', 'รายละเอียด OPD - GCMS')

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
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

    .opd-number-display {
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 1.5rem;
        font-weight: 700;
        color: #0d9488;
    }

    .patient-card {
        background: #f8fafc;
        border-radius: 16px;
        padding: 1.5rem;
    }

    .patient-avatar-lg {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #14b8a6, #0d9488);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 700;
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

    .status-display {
        padding: 1.5rem;
        border-radius: 12px;
        text-align: center;
    }

    .status-display.active {
        background: #d1fae5;
        border-left: 4px solid #059669;
    }

    .status-display.closed {
        background: #f3f4f6;
        border-left: 4px solid #6b7280;
    }

    .status-display.cancelled {
        background: #fee2e2;
        border-left: 4px solid #dc2626;
    }

    .treatment-item {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.75rem;
    }

    .invoice-item {
        background: #fef3c7;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.75rem;
    }

    .temp-badge {
        background: #fef3c7;
        color: #92400e;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2">
                    <i class="bi bi-clipboard2-pulse me-2"></i>
                    <span class="opd-number-display">{{ $opdRecord->opd_number }}</span>
                    @if($opdRecord->is_temporary)
                    <span class="temp-badge ms-2">ชั่วคราว</span>
                    @endif
                </h2>
                <p class="mb-0 opacity-90">
                    สร้างเมื่อ {{ $opdRecord->created_at?->format('d/m/Y H:i') ?? '-' }}
                </p>
            </div>
            <div class="d-flex gap-2">
                @if($opdRecord->status == 'active')
                <a href="{{ route('opd-records.edit', $opdRecord) }}" class="btn btn-light">
                    <i class="bi bi-pencil me-1"></i> แก้ไข
                </a>
                @endif
                <a href="{{ route('opd-records.index') }}" class="btn btn-outline-light">
                    <i class="bi bi-arrow-left me-1"></i> กลับ
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Patient Info -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-person me-2"></i>ข้อมูลผู้ป่วย</div>
                <div class="patient-card">
                    <div class="d-flex align-items-center">
                        <div class="patient-avatar-lg me-4">
                            {{ $opdRecord->patient ? substr($opdRecord->patient->name ?? '', 0, 1) : '?' }}
                        </div>
                        <div>
                            <h4 class="mb-1">{{ $opdRecord->patient->name ?? '-' }}</h4>
                            <p class="text-muted mb-0">
                                <i class="bi bi-telephone me-1"></i>{{ $opdRecord->patient->phone ?? '-' }}
                            </p>
                            <p class="text-muted mb-0">
                                <i class="bi bi-building me-1"></i>{{ $opdRecord->branch->name ?? '-' }}
                            </p>
                        </div>
                        @if($opdRecord->patient)
                        <div class="ms-auto">
                            <a href="{{ route('patients.show', $opdRecord->patient) }}" class="btn btn-outline-primary">
                                ดูประวัติผู้ป่วย <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Chief Complaint -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-chat-left-text me-2"></i>อาการสำคัญ</div>
                <p class="mb-0">{{ $opdRecord->chief_complaint ?: 'ไม่ได้ระบุ' }}</p>
            </div>

            <!-- Treatments -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-heart-pulse me-2"></i>การรักษา ({{ $opdRecord->treatments->count() }})</div>
                @if($opdRecord->treatments->count() > 0)
                    @foreach($opdRecord->treatments as $treatment)
                    <div class="treatment-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ $treatment->service->name ?? 'ไม่ระบุบริการ' }}</strong>
                                <br><small class="text-muted">
                                    <i class="bi bi-person me-1"></i>PT: {{ $treatment->pt->first_name ?? '-' }} {{ $treatment->pt->last_name ?? '' }}
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold">฿{{ number_format($treatment->price ?? 0, 2) }}</span>
                                <br><small class="text-muted">{{ $treatment->created_at?->format('d/m/Y H:i') }}</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <p class="text-muted text-center py-3 mb-0">
                        <i class="bi bi-inbox d-block fs-3 mb-2"></i>
                        ยังไม่มีการรักษา
                    </p>
                @endif
            </div>

            <!-- Invoices -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-receipt me-2"></i>ใบแจ้งหนี้ ({{ $opdRecord->invoices->count() }})</div>
                @if($opdRecord->invoices->count() > 0)
                    @foreach($opdRecord->invoices as $invoice)
                    <div class="invoice-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $invoice->invoice_number ?? '-' }}</strong>
                                <br><small class="text-muted">{{ $invoice->created_at?->format('d/m/Y H:i') }}</small>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold fs-5">฿{{ number_format($invoice->total_amount ?? 0, 2) }}</span>
                                <br>
                                @if($invoice->payment_status == 'paid')
                                    <span class="badge bg-success">ชำระแล้ว</span>
                                @elseif($invoice->payment_status == 'partial')
                                    <span class="badge bg-warning">ชำระบางส่วน</span>
                                @else
                                    <span class="badge bg-secondary">รอชำระ</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <p class="text-muted text-center py-3 mb-0">
                        <i class="bi bi-inbox d-block fs-3 mb-2"></i>
                        ยังไม่มีใบแจ้งหนี้
                    </p>
                @endif
            </div>
        </div>

        <div class="col-md-4">
            <!-- Status -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-flag me-2"></i>สถานะ</div>

                @if($opdRecord->status == 'active')
                <div class="status-display active">
                    <i class="bi bi-play-circle fs-1 d-block mb-2 text-success"></i>
                    <h5 class="mb-1">กำลังดำเนินการ</h5>
                    <p class="text-muted mb-0 small">OPD ยังเปิดใช้งานอยู่</p>
                </div>

                <div class="d-grid gap-2 mt-3">
                    <button type="button" class="btn btn-success" onclick="closeOpd()">
                        <i class="bi bi-check-circle me-1"></i> ปิด OPD
                    </button>
                </div>
                @elseif($opdRecord->status == 'closed')
                <div class="status-display closed">
                    <i class="bi bi-check-circle fs-1 d-block mb-2 text-secondary"></i>
                    <h5 class="mb-1">ปิดแล้ว</h5>
                    <p class="text-muted mb-0 small">OPD ถูกปิดเรียบร้อย</p>
                </div>

                <div class="d-grid gap-2 mt-3">
                    <button type="button" class="btn btn-outline-primary" onclick="reopenOpd()">
                        <i class="bi bi-arrow-clockwise me-1"></i> เปิดอีกครั้ง
                    </button>
                </div>
                @elseif($opdRecord->status == 'cancelled')
                <div class="status-display cancelled">
                    <i class="bi bi-x-circle fs-1 d-block mb-2 text-danger"></i>
                    <h5 class="mb-1">ยกเลิก</h5>
                    <p class="text-muted mb-0 small">OPD ถูกยกเลิก</p>
                </div>
                @endif
            </div>

            <!-- Meta Info -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-info-circle me-2"></i>ข้อมูลเพิ่มเติม</div>

                <div class="info-item">
                    <div class="info-label">เลข OPD</div>
                    <div class="info-value">{{ $opdRecord->opd_number }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">สาขา</div>
                    <div class="info-value">{{ $opdRecord->branch->name ?? '-' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">ประเภท</div>
                    <div class="info-value">
                        @if($opdRecord->is_temporary)
                            <span class="temp-badge">OPD ชั่วคราว</span>
                        @else
                            OPD ปกติ
                        @endif
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label">สร้างโดย</div>
                    <div class="info-value">{{ $opdRecord->createdBy->name ?? '-' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">วันที่สร้าง</div>
                    <div class="info-value">{{ $opdRecord->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function closeOpd() {
    if (!confirm('ยืนยันปิด OPD นี้?')) return;

    fetch('{{ route("opd-records.show", $opdRecord) }}/close', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'เกิดข้อผิดพลาด');
        }
    })
    .catch(error => {
        alert('เกิดข้อผิดพลาด');
    });
}

function reopenOpd() {
    if (!confirm('ยืนยันเปิด OPD อีกครั้ง?')) return;

    fetch('{{ route("opd-records.show", $opdRecord) }}/reopen', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'เกิดข้อผิดพลาด');
        }
    })
    .catch(error => {
        alert('เกิดข้อผิดพลาด');
    });
}
</script>
@endpush
