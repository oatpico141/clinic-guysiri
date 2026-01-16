@extends('layouts.app')

@section('title', 'รายละเอียดคอมมิชชั่น - GCMS')

@push('styles')
<style>
    .commission-header {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
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
    .badge-clawed_back { background: #fee2e2; color: #991b1b; }

    .amount-box {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
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
    <div class="commission-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <a href="{{ route('commissions.index') }}" class="btn btn-sm btn-light mb-3">
                    <i class="bi bi-arrow-left me-1"></i> กลับ
                </a>
                <h2 class="mb-2"><i class="bi bi-cash-coin me-2"></i>{{ $commission->commission_number ?? 'คอมมิชชั่น' }}</h2>
                <p class="mb-0 opacity-90">
                    PT: {{ $commission->pt->name ?? '-' }} |
                    วันที่: {{ $commission->commission_date?->format('d/m/Y') ?? '-' }}
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                @if(in_array($commission->status, ['pending', 'approved']))
                <button type="button" class="btn btn-light" id="payBtn">
                    <i class="bi bi-cash me-1"></i> จ่ายคอมมิชชั่น
                </button>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Commission Details -->
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>รายละเอียดคอมมิชชั่น</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-label">เลขที่คอมมิชชั่น</div>
                            <div class="info-value"><code>{{ $commission->commission_number ?? '-' }}</code></div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">สถานะ</div>
                            <div class="info-value">
                                @switch($commission->status)
                                    @case('pending')
                                        <span class="badge badge-pending">รอดำเนินการ</span>
                                        @break
                                    @case('approved')
                                        <span class="badge badge-approved">อนุมัติแล้ว</span>
                                        @break
                                    @case('paid')
                                        <span class="badge badge-paid">จ่ายแล้ว</span>
                                        @break
                                    @case('clawed_back')
                                        <span class="badge badge-clawed_back">Clawback</span>
                                        @break
                                @endswitch
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">ประเภท</div>
                            <div class="info-value">
                                @switch($commission->commission_type)
                                    @case('service')
                                        บริการ (Service)
                                        @break
                                    @case('package_sale')
                                        ขายแพ็คเกจ (Package Sale)
                                        @break
                                    @case('package_usage')
                                        ใช้แพ็คเกจ (Package Usage)
                                        @break
                                    @default
                                        {{ $commission->commission_type }}
                                @endswitch
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">วันที่คอมมิชชั่น</div>
                            <div class="info-value">{{ $commission->commission_date?->format('d/m/Y') ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">PT</div>
                            <div class="info-value">{{ $commission->pt->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">สาขา</div>
                            <div class="info-value">{{ $commission->branch->name ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Info -->
            @if($commission->invoice)
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>ข้อมูลใบเสร็จ</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-label">เลขที่ใบเสร็จ</div>
                            <div class="info-value">
                                <a href="{{ route('invoices.show', $commission->invoice) }}">
                                    {{ $commission->invoice->invoice_number }}
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">ลูกค้า</div>
                            <div class="info-value">
                                @if($commission->invoice->patient)
                                <a href="{{ route('patients.show', $commission->invoice->patient) }}">
                                    {{ $commission->invoice->patient->name }}
                                </a>
                                @else
                                -
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">วันที่ใบเสร็จ</div>
                            <div class="info-value">{{ $commission->invoice->invoice_date?->format('d/m/Y') ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">ยอดใบเสร็จ</div>
                            <div class="info-value">฿{{ number_format($commission->invoice->total_amount, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Treatment Info -->
            @if($commission->treatment)
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-heart-pulse me-2"></i>ข้อมูลการรักษา</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-label">บริการ</div>
                            <div class="info-value">{{ $commission->treatment->service->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">วันที่รักษา</div>
                            <div class="info-value">{{ $commission->treatment->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Commission Splits -->
            @if($commission->splits->count() > 0)
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>การแบ่งคอมมิชชั่น</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>PT</th>
                                <th class="text-center">สัดส่วน</th>
                                <th class="text-end">จำนวน</th>
                                <th class="text-center">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($commission->splits as $split)
                            <tr>
                                <td>{{ $split->pt->name ?? '-' }}</td>
                                <td class="text-center">{{ number_format($split->split_percentage, 0) }}%</td>
                                <td class="text-end">฿{{ number_format($split->split_amount, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $split->status }}">{{ $split->status }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Payment Info (if paid) -->
            @if($commission->status === 'paid')
            <div class="detail-card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-check-circle me-2"></i>ข้อมูลการจ่าย</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-label">จ่ายเมื่อ</div>
                            <div class="info-value">{{ $commission->paid_at?->format('d/m/Y H:i') ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">เลขอ้างอิง</div>
                            <div class="info-value">{{ $commission->payment_reference ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Clawback Info (if clawed back) -->
            @if($commission->status === 'clawed_back')
            <div class="detail-card">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>ข้อมูล Clawback</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-label">Clawback เมื่อ</div>
                            <div class="info-value">{{ $commission->clawed_back_at?->format('d/m/Y H:i') ?? '-' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="info-label">เหตุผล</div>
                            <div class="info-value">{{ $commission->clawback_reason ?? '-' }}</div>
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
                <div class="small opacity-75">ยอดคอมมิชชั่น</div>
                <div class="amount">฿{{ number_format($commission->commission_amount, 2) }}</div>
            </div>

            <!-- Calculation -->
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-calculator me-2"></i>การคำนวณ</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">ยอดบริการ</span>
                        <span>฿{{ number_format($commission->base_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">อัตราคอมมิชชั่น</span>
                        <span>{{ number_format($commission->commission_rate, 2) }}%</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>คอมมิชชั่น</span>
                        <span class="text-primary">฿{{ number_format($commission->commission_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($commission->notes)
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-sticky me-2"></i>หมายเหตุ</h6>
                </div>
                <div class="card-body">
                    {{ $commission->notes }}
                </div>
            </div>
            @endif

            <!-- Actions -->
            @if(in_array($commission->status, ['pending', 'approved']) && $commission->is_clawback_eligible)
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>การดำเนินการ</h6>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-outline-danger w-100" id="clawbackBtn">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Clawback
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Clawback Modal -->
<div class="modal fade" id="clawbackModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Clawback คอมมิชชั่น</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    การ Clawback จะยกเลิกคอมมิชชั่นนี้ กรุณาระบุเหตุผล
                </div>
                <div class="mb-3">
                    <label class="form-label">เหตุผล <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="clawbackReason" rows="3" placeholder="ระบุเหตุผลในการ clawback"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-danger" id="confirmClawbackBtn">
                    <i class="bi bi-check-lg me-1"></i> ยืนยัน Clawback
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Pay Modal -->
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-cash me-2"></i>จ่ายคอมมิชชั่น</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>จ่ายคอมมิชชั่น <strong>฿{{ number_format($commission->commission_amount, 2) }}</strong> ให้ <strong>{{ $commission->pt->name ?? '-' }}</strong></p>
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
    const commissionId = '{{ $commission->id }}';

    // Pay button
    document.getElementById('payBtn')?.addEventListener('click', function() {
        new bootstrap.Modal(document.getElementById('payModal')).show();
    });

    // Confirm pay
    document.getElementById('confirmPayBtn')?.addEventListener('click', function() {
        const reference = document.getElementById('paymentReference').value;

        fetch(`/commissions/${commissionId}/pay`, {
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

    // Clawback button
    document.getElementById('clawbackBtn')?.addEventListener('click', function() {
        new bootstrap.Modal(document.getElementById('clawbackModal')).show();
    });

    // Confirm clawback
    document.getElementById('confirmClawbackBtn')?.addEventListener('click', function() {
        const reason = document.getElementById('clawbackReason').value;

        if (!reason || reason.length < 5) {
            alert('กรุณาระบุเหตุผลอย่างน้อย 5 ตัวอักษร');
            return;
        }

        fetch(`/commissions/${commissionId}/clawback`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ clawback_reason: reason })
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
