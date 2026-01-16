@extends('layouts.app')

@section('title', 'รายละเอียดการต่ออายุ - GCMS')

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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

    .renewal-number-display {
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 1.25rem;
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

    .date-comparison {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1.5rem;
    }

    .date-box {
        text-align: center;
        padding: 1rem;
        border-radius: 8px;
    }

    .date-box.old {
        background: #fee2e2;
        color: #991b1b;
    }

    .date-box.new {
        background: #d1fae5;
        color: #065f46;
    }

    .extension-display {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        border-radius: 50%;
        width: 80px;
        height: 80px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }

    .patient-card {
        background: #f0fdf4;
        border: 2px solid #22c55e;
        border-radius: 12px;
        padding: 1rem;
    }

    .course-card {
        background: #f8fafc;
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
                <h2 class="mb-2">
                    <i class="bi bi-arrow-repeat me-2"></i>
                    <span class="renewal-number-display">{{ $renewal->renewal_number }}</span>
                </h2>
                <p class="mb-0 opacity-90">
                    {{ $renewal->renewal_date?->format('d/m/Y') ?? '-' }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('course-renewals.edit', $renewal) }}" class="btn btn-light">
                    <i class="bi bi-pencil me-1"></i> แก้ไข
                </a>
                <a href="{{ route('course-renewals.index') }}" class="btn btn-outline-light">
                    <i class="bi bi-arrow-left me-1"></i> กลับ
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Date Comparison -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-calendar-event me-2"></i>การขยายอายุ</div>
                <div class="date-comparison">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="date-box old">
                                <small class="d-block mb-1">วันหมดอายุเดิม</small>
                                <div class="fs-4 fw-bold">{{ $renewal->old_expiry_date?->format('d/m/Y') ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center py-3">
                            <div class="extension-display">
                                <span class="fs-3 fw-bold">+{{ $renewal->extension_days }}</span>
                                <small>วัน</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="date-box new">
                                <small class="d-block mb-1">วันหมดอายุใหม่</small>
                                <div class="fs-4 fw-bold">{{ $renewal->new_expiry_date?->format('d/m/Y') ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course Info -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-box me-2"></i>คอร์สที่ต่ออายุ</div>
                @if($renewal->coursePurchase)
                <div class="course-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div style="width: 60px; height: 60px; border-radius: 12px; background: linear-gradient(135deg, #10b981, #059669); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                <i class="bi bi-box"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">{{ $renewal->coursePurchase->coursePackage->name ?? '-' }}</h5>
                            <p class="text-muted mb-0">
                                <i class="bi bi-hash me-1"></i>{{ $renewal->coursePurchase->purchase_number ?? '-' }}
                            </p>
                            @if($renewal->coursePurchase->sessions_remaining !== null)
                            <small class="text-success">
                                <i class="bi bi-check-circle me-1"></i>เหลือ {{ $renewal->coursePurchase->sessions_remaining }} ครั้ง
                            </small>
                            @endif
                        </div>
                        <div>
                            <a href="{{ route('course-purchases.show', $renewal->coursePurchase) }}" class="btn btn-outline-success">
                                ดูคอร์ส <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @else
                <p class="text-muted">ไม่พบข้อมูลคอร์ส</p>
                @endif
            </div>

            <!-- Reason & Notes -->
            @if($renewal->renewal_reason || $renewal->notes)
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-chat-left-text me-2"></i>เหตุผลและหมายเหตุ</div>

                @if($renewal->renewal_reason)
                <h6 class="fw-semibold">เหตุผลในการต่ออายุ</h6>
                <p>{{ $renewal->renewal_reason }}</p>
                @endif

                @if($renewal->notes)
                <h6 class="fw-semibold mt-4">หมายเหตุ</h6>
                <p class="mb-0">{{ $renewal->notes }}</p>
                @endif
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <!-- Patient Info -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-person me-2"></i>ลูกค้า</div>
                @if($renewal->patient)
                <div class="patient-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #10b981, #059669); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 700;">
                                {{ substr($renewal->patient->name ?? '', 0, 1) }}
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $renewal->patient->name }}</h6>
                            <small class="text-muted">{{ $renewal->patient->phone ?? '-' }}</small>
                        </div>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="{{ route('patients.show', $renewal->patient) }}" class="btn btn-sm btn-outline-primary w-100">
                        <i class="bi bi-person me-1"></i> ดูข้อมูลลูกค้า
                    </a>
                </div>
                @else
                <p class="text-muted mb-0">ไม่พบข้อมูลลูกค้า</p>
                @endif
            </div>

            <!-- Fee -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-cash me-2"></i>ค่าธรรมเนียม</div>
                <div class="text-center py-3">
                    @if($renewal->renewal_fee > 0)
                    <div class="fs-2 fw-bold text-success">฿{{ number_format($renewal->renewal_fee, 2) }}</div>
                    @else
                    <div class="fs-4 text-muted">ฟรี</div>
                    @endif
                </div>

                @if($renewal->invoice)
                <hr>
                <div class="info-item">
                    <div class="info-label">ใบแจ้งหนี้</div>
                    <div class="info-value">
                        <a href="{{ route('invoices.show', $renewal->invoice) }}">{{ $renewal->invoice->invoice_number }}</a>
                    </div>
                </div>
                @endif
            </div>

            <!-- Branch & Timestamps -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-building me-2"></i>ข้อมูลเพิ่มเติม</div>

                <div class="info-item">
                    <div class="info-label">สาขา</div>
                    <div class="info-value">{{ $renewal->branch->name ?? '-' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">สร้างเมื่อ</div>
                    <div class="info-value">{{ $renewal->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
