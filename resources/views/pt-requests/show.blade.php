@extends('layouts.app')

@section('title', 'รายละเอียดคำขอเปลี่ยน PT - GCMS')

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
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

    .patient-card {
        background: #f8fafc;
        border-radius: 16px;
        padding: 1.5rem;
    }

    .patient-avatar-lg {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 700;
    }

    .pt-swap-display {
        background: #f8fafc;
        border-radius: 16px;
        padding: 2rem;
    }

    .pt-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .pt-avatar-lg {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        font-weight: 700;
        color: white;
        margin: 0 auto 1rem;
    }

    .pt-avatar-lg.from {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }

    .pt-avatar-lg.to {
        background: linear-gradient(135deg, #10b981, #059669);
    }

    .swap-arrow-lg {
        font-size: 3rem;
        color: #8b5cf6;
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

    .status-display.pending {
        background: #fef3c7;
        border-left: 4px solid #d97706;
    }

    .status-display.approved {
        background: #d1fae5;
        border-left: 4px solid #059669;
    }

    .status-display.rejected {
        background: #fee2e2;
        border-left: 4px solid #dc2626;
    }

    .action-buttons {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1.5rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2"><i class="bi bi-person-lines-fill me-2"></i>รายละเอียดคำขอเปลี่ยน PT</h2>
                <p class="mb-0 opacity-90">
                    ส่งเมื่อ {{ $ptRequest->requested_at ? \Carbon\Carbon::parse($ptRequest->requested_at)->format('d/m/Y H:i') : '-' }}
                </p>
            </div>
            <div class="d-flex gap-2">
                @if($ptRequest->status == 'pending')
                <a href="{{ route('pt-requests.edit', $ptRequest) }}" class="btn btn-light">
                    <i class="bi bi-pencil me-1"></i> แก้ไข
                </a>
                @endif
                <a href="{{ route('pt-requests.index') }}" class="btn btn-outline-light">
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
                            {{ $ptRequest->patient ? substr($ptRequest->patient->name ?? '', 0, 1) : '?' }}
                        </div>
                        <div>
                            <h4 class="mb-1">{{ $ptRequest->patient->name ?? '-' }}</h4>
                            <p class="text-muted mb-0">
                                <i class="bi bi-telephone me-1"></i>{{ $ptRequest->patient->phone ?? '-' }}
                            </p>
                            @if($ptRequest->branch)
                            <p class="text-muted mb-0">
                                <i class="bi bi-building me-1"></i>{{ $ptRequest->branch->name }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- PT Change -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-people me-2"></i>การเปลี่ยน PT</div>
                <div class="pt-swap-display">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <div class="pt-card">
                                @if($ptRequest->originalPt)
                                <div class="pt-avatar-lg from">
                                    {{ substr($ptRequest->originalPt->first_name, 0, 1) }}
                                </div>
                                <h5 class="mb-1">{{ $ptRequest->originalPt->first_name }} {{ $ptRequest->originalPt->last_name }}</h5>
                                @else
                                <div class="pt-avatar-lg from" style="background: #9ca3af;">
                                    <i class="bi bi-person-slash"></i>
                                </div>
                                <h5 class="mb-1 text-muted">ไม่มี PT เดิม</h5>
                                @endif
                                <span class="badge bg-danger">PT เดิม</span>
                            </div>
                        </div>
                        <div class="col-md-2 text-center py-3">
                            <i class="bi bi-arrow-right swap-arrow-lg"></i>
                        </div>
                        <div class="col-md-5">
                            <div class="pt-card">
                                <div class="pt-avatar-lg to">
                                    {{ $ptRequest->requestedPt ? substr($ptRequest->requestedPt->first_name, 0, 1) : '?' }}
                                </div>
                                <h5 class="mb-1">{{ $ptRequest->requestedPt->first_name ?? '-' }} {{ $ptRequest->requestedPt->last_name ?? '' }}</h5>
                                <span class="badge bg-success">PT ที่ต้องการ</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reason -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-chat-left-text me-2"></i>เหตุผลในการขอเปลี่ยน</div>
                <p class="mb-0">{{ $ptRequest->reason ?? '-' }}</p>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Status -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-flag me-2"></i>สถานะ</div>

                @if($ptRequest->status == 'pending')
                <div class="status-display pending">
                    <i class="bi bi-hourglass-split fs-1 d-block mb-2 text-warning"></i>
                    <h5 class="mb-1">รอดำเนินการ</h5>
                    <p class="text-muted mb-0 small">คำขอกำลังรอการอนุมัติ</p>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons mt-3">
                    <h6 class="mb-3">ดำเนินการ</h6>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success" onclick="approveRequest()">
                            <i class="bi bi-check-circle me-1"></i> อนุมัติคำขอ
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="showRejectModal()">
                            <i class="bi bi-x-circle me-1"></i> ปฏิเสธคำขอ
                        </button>
                    </div>
                </div>
                @elseif($ptRequest->status == 'approved')
                <div class="status-display approved">
                    <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                    <h5 class="mb-1">อนุมัติแล้ว</h5>
                    <p class="text-muted mb-0 small">
                        เมื่อ {{ $ptRequest->processed_at ? \Carbon\Carbon::parse($ptRequest->processed_at)->format('d/m/Y H:i') : '-' }}
                    </p>
                </div>
                @elseif($ptRequest->status == 'rejected')
                <div class="status-display rejected">
                    <i class="bi bi-x-circle fs-1 d-block mb-2 text-danger"></i>
                    <h5 class="mb-1">ปฏิเสธแล้ว</h5>
                    <p class="text-muted mb-0 small">
                        เมื่อ {{ $ptRequest->processed_at ? \Carbon\Carbon::parse($ptRequest->processed_at)->format('d/m/Y H:i') : '-' }}
                    </p>
                </div>

                @if($ptRequest->rejection_reason)
                <div class="alert alert-danger mt-3 mb-0">
                    <strong>เหตุผลที่ปฏิเสธ:</strong><br>
                    {{ $ptRequest->rejection_reason }}
                </div>
                @endif
                @endif
            </div>

            <!-- Meta Info -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-info-circle me-2"></i>ข้อมูลเพิ่มเติม</div>

                <div class="info-item">
                    <div class="info-label">วันที่ส่งคำขอ</div>
                    <div class="info-value">
                        {{ $ptRequest->requested_at ? \Carbon\Carbon::parse($ptRequest->requested_at)->format('d/m/Y H:i') : '-' }}
                    </div>
                </div>

                @if($ptRequest->processedBy)
                <div class="info-item">
                    <div class="info-label">ดำเนินการโดย</div>
                    <div class="info-value">{{ $ptRequest->processedBy->name ?? '-' }}</div>
                </div>
                @endif

                <div class="info-item">
                    <div class="info-label">สาขา</div>
                    <div class="info-value">{{ $ptRequest->branch->name ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ปฏิเสธคำขอ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">เหตุผลในการปฏิเสธ <span class="text-danger">*</span></label>
                    <textarea id="rejectionReason" class="form-control" rows="3" placeholder="ระบุเหตุผล..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-danger" onclick="rejectRequest()">ปฏิเสธคำขอ</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function approveRequest() {
    if (!confirm('ยืนยันอนุมัติคำขอนี้?')) return;

    fetch('{{ route("pt-requests.approve", $ptRequest) }}', {
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

function showRejectModal() {
    document.getElementById('rejectionReason').value = '';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function rejectRequest() {
    const reason = document.getElementById('rejectionReason').value;

    if (!reason.trim()) {
        alert('กรุณาระบุเหตุผลในการปฏิเสธ');
        return;
    }

    fetch('{{ route("pt-requests.reject", $ptRequest) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ rejection_reason: reason })
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
