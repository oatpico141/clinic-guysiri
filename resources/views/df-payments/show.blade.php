@extends('layouts.app')

@section('title', 'รายละเอียดค่ามือ - GCMS')

@push('styles')
<style>
    .df-header {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 20px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }

    .detail-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        margin-bottom: 1.5rem;
    }

    .detail-card .card-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 1rem 1.5rem;
        border-radius: 16px 16px 0 0;
    }

    .detail-card .card-body {
        padding: 1.5rem;
    }

    .info-label {
        font-size: 0.8rem;
        color: #64748b;
        margin-bottom: 0.25rem;
    }

    .info-value {
        font-weight: 500;
        color: #1e293b;
    }

    .badge-pending { background: #fef3c7; color: #92400e; }
    .badge-approved { background: #dbeafe; color: #1e40af; }
    .badge-paid { background: #d1fae5; color: #065f46; }

    .amount-box {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 12px;
        padding: 1.5rem;
        color: white;
        text-align: center;
    }

    .amount-box .amount {
        font-size: 2rem;
        font-weight: 700;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="df-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <a href="{{ route('df-payments.index') }}" class="btn btn-sm btn-light mb-3">
                    <i class="bi bi-arrow-left me-1"></i> กลับ
                </a>
                <h2 class="mb-2"><i class="bi bi-hand-index-thumb me-2"></i>{{ $dfPayment->df_number ?? 'ค่ามือ' }}</h2>
                <p class="mb-0 opacity-90">
                    PT: {{ $dfPayment->pt->name ?? '-' }} |
                    วันที่: {{ $dfPayment->df_date?->format('d/m/Y') ?? '-' }}
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                @if(in_array($dfPayment->status, ['pending', 'approved']))
                <button type="button" class="btn btn-light" id="payBtn">
                    <i class="bi bi-cash me-1"></i> จ่ายค่ามือ
                </button>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- DF Details -->
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>รายละเอียดค่ามือ</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-label">เลขที่</div>
                            <div class="info-value"><code>{{ $dfPayment->df_number ?? '-' }}</code></div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">สถานะ</div>
                            <div class="info-value">
                                @switch($dfPayment->status)
                                    @case('pending')
                                        <span class="badge badge-pending">รอดำเนินการ</span>
                                        @break
                                    @case('approved')
                                        <span class="badge badge-approved">อนุมัติแล้ว</span>
                                        @break
                                    @case('paid')
                                        <span class="badge badge-paid">จ่ายแล้ว</span>
                                        @break
                                @endswitch
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">ประเภท</div>
                            <div class="info-value">
                                @switch($dfPayment->source_type)
                                    @case('course_usage')
                                        ใช้คอร์ส (Course Usage)
                                        @break
                                    @case('per_session')
                                        รายครั้ง (Per Session)
                                        @break
                                    @default
                                        {{ $dfPayment->source_type ?? '-' }}
                                @endswitch
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">วันที่</div>
                            <div class="info-value">{{ $dfPayment->df_date?->format('d/m/Y') ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">PT</div>
                            <div class="info-value">{{ $dfPayment->pt->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">สาขา</div>
                            <div class="info-value">{{ $dfPayment->branch->name ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Treatment Info -->
            @if($dfPayment->treatment)
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-heart-pulse me-2"></i>ข้อมูลการรักษา</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-label">ลูกค้า</div>
                            <div class="info-value">
                                @if($dfPayment->treatment->patient)
                                <a href="{{ route('patients.show', $dfPayment->treatment->patient) }}">
                                    {{ $dfPayment->treatment->patient->name }}
                                </a>
                                @else
                                -
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">บริการ</div>
                            <div class="info-value">{{ $dfPayment->treatment->service->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">วันที่รักษา</div>
                            <div class="info-value">{{ $dfPayment->treatment->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Course Info -->
            @if($dfPayment->coursePurchase)
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-collection me-2"></i>ข้อมูลคอร์ส</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-label">แพ็คเกจ</div>
                            <div class="info-value">{{ $dfPayment->coursePurchase->package->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">จำนวนครั้งที่เหลือ</div>
                            <div class="info-value">{{ $dfPayment->coursePurchase->remaining_sessions ?? '-' }} ครั้ง</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Payment Info (if paid) -->
            @if($dfPayment->status === 'paid')
            <div class="detail-card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-check-circle me-2"></i>ข้อมูลการจ่าย</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-label">จ่ายเมื่อ</div>
                            <div class="info-value">{{ $dfPayment->paid_at?->format('d/m/Y H:i') ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">เลขอ้างอิง</div>
                            <div class="info-value">{{ $dfPayment->payment_reference ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Amount Box -->
            <div class="amount-box mb-4">
                <div class="small opacity-75">ยอดค่ามือ</div>
                <div class="amount">฿{{ number_format($dfPayment->df_amount, 2) }}</div>
            </div>

            <!-- Calculation -->
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-calculator me-2"></i>การคำนวณ</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">ยอดบริการ</span>
                        <span>฿{{ number_format($dfPayment->base_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">อัตราค่ามือ</span>
                        <span>{{ number_format($dfPayment->df_rate, 2) }}%</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>ค่ามือ</span>
                        <span class="text-success">฿{{ number_format($dfPayment->df_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($dfPayment->notes)
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-sticky me-2"></i>หมายเหตุ</h6>
                </div>
                <div class="card-body">
                    {{ $dfPayment->notes }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Pay Modal -->
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-cash me-2"></i>จ่ายค่ามือ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>จ่ายค่ามือ <strong>฿{{ number_format($dfPayment->df_amount, 2) }}</strong> ให้ <strong>{{ $dfPayment->pt->name ?? '-' }}</strong></p>
                <div class="mb-3">
                    <label class="form-label">เลขอ้างอิงการจ่าย</label>
                    <input type="text" class="form-control" id="paymentReference" placeholder="เช่น เลขที่โอน, เลขเช็ค">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-success" id="confirmPayBtn">
                    <i class="bi bi-check-lg me-1"></i> ยืนยันการจ่าย
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dfPaymentId = '{{ $dfPayment->id }}';

    // Pay button
    document.getElementById('payBtn')?.addEventListener('click', function() {
        new bootstrap.Modal(document.getElementById('payModal')).show();
    });

    // Confirm pay
    document.getElementById('confirmPayBtn')?.addEventListener('click', function() {
        const reference = document.getElementById('paymentReference').value;

        fetch(`/df-payments/${dfPaymentId}/pay`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ payment_reference: reference })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(err => {
            alert('เกิดข้อผิดพลาด');
            console.error(err);
        });
    });
});
</script>
@endpush
