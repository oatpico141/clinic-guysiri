@extends('layouts.app')

@section('title', 'รายละเอียดคำขอลา - GCMS')

@push('styles')
<style>
    .leave-header {
        background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
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
    .badge-approved { background: #d1fae5; color: #065f46; }
    .badge-rejected { background: #fee2e2; color: #991b1b; }

    .days-box {
        background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
        border-radius: 12px;
        padding: 1.5rem;
        color: white;
        text-align: center;
    }

    .days-box .days {
        font-size: 2.5rem;
        font-weight: 700;
    }

    .timeline-item {
        border-left: 2px solid #e5e7eb;
        padding-left: 1rem;
        margin-left: 0.5rem;
        padding-bottom: 1rem;
    }

    .timeline-item:last-child {
        border-left: 2px solid transparent;
    }

    .timeline-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #ec4899;
        position: absolute;
        left: -7px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="leave-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <a href="{{ route('leave-requests.index') }}" class="btn btn-sm btn-light mb-3">
                    <i class="bi bi-arrow-left me-1"></i> กลับ
                </a>
                <h2 class="mb-2"><i class="bi bi-calendar-x me-2"></i>{{ $leaveRequest->leave_number ?? 'คำขอลา' }}</h2>
                <p class="mb-0 opacity-90">
                    {{ $leaveRequest->staff->first_name ?? '' }} {{ $leaveRequest->staff->last_name ?? '' }} |
                    @switch($leaveRequest->leave_type)
                        @case('annual') ลาพักร้อน @break
                        @case('sick') ลาป่วย @break
                        @case('personal') ลากิจ @break
                        @case('maternity') ลาคลอด @break
                        @case('unpaid') ลาไม่รับเงิน @break
                        @default {{ $leaveRequest->leave_type }}
                    @endswitch
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                @if($leaveRequest->status === 'pending')
                <a href="{{ route('leave-requests.edit', $leaveRequest) }}" class="btn btn-light me-2">
                    <i class="bi bi-pencil me-1"></i> แก้ไข
                </a>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Leave Details -->
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>รายละเอียดการลา</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-label">เลขที่คำขอ</div>
                            <div class="info-value"><code>{{ $leaveRequest->leave_number }}</code></div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">สถานะ</div>
                            <div class="info-value">
                                @switch($leaveRequest->status)
                                    @case('pending')
                                        <span class="badge badge-pending">รออนุมัติ</span>
                                        @break
                                    @case('approved')
                                        <span class="badge badge-approved">อนุมัติแล้ว</span>
                                        @break
                                    @case('rejected')
                                        <span class="badge badge-rejected">ไม่อนุมัติ</span>
                                        @break
                                @endswitch
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">ประเภทการลา</div>
                            <div class="info-value">
                                @switch($leaveRequest->leave_type)
                                    @case('annual')
                                        <i class="bi bi-sun text-info me-1"></i> ลาพักร้อน
                                        @break
                                    @case('sick')
                                        <i class="bi bi-thermometer-half text-danger me-1"></i> ลาป่วย
                                        @break
                                    @case('personal')
                                        <i class="bi bi-person text-secondary me-1"></i> ลากิจ
                                        @break
                                    @case('maternity')
                                        <i class="bi bi-heart me-1" style="color: #ec4899;"></i> ลาคลอด
                                        @break
                                    @case('unpaid')
                                        <i class="bi bi-cash-stack text-dark me-1"></i> ลาไม่รับเงิน
                                        @break
                                    @default
                                        {{ $leaveRequest->leave_type }}
                                @endswitch
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">สาขา</div>
                            <div class="info-value">{{ $leaveRequest->branch->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">วันที่เริ่มลา</div>
                            <div class="info-value">{{ $leaveRequest->start_date?->format('d/m/Y') ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">วันที่สิ้นสุด</div>
                            <div class="info-value">{{ $leaveRequest->end_date?->format('d/m/Y') ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reason -->
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>เหตุผลการลา</h6>
                </div>
                <div class="card-body">
                    {{ $leaveRequest->reason ?? '-' }}
                </div>
            </div>

            <!-- Staff Info -->
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-person me-2"></i>ข้อมูลพนักงาน</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-label">ชื่อพนักงาน</div>
                            <div class="info-value">
                                @if($leaveRequest->staff)
                                <a href="{{ route('staff.show', $leaveRequest->staff) }}">
                                    {{ $leaveRequest->staff->first_name }} {{ $leaveRequest->staff->last_name }}
                                </a>
                                @else
                                -
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">ตำแหน่ง</div>
                            <div class="info-value">{{ $leaveRequest->staff->position ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approval Info -->
            @if($leaveRequest->status !== 'pending')
            <div class="detail-card">
                <div class="card-header {{ $leaveRequest->status === 'approved' ? 'bg-success text-white' : 'bg-danger text-white' }}">
                    <h6 class="mb-0">
                        <i class="bi bi-{{ $leaveRequest->status === 'approved' ? 'check-circle' : 'x-circle' }} me-2"></i>
                        ผลการพิจารณา
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-label">ผู้พิจารณา</div>
                            <div class="info-value">{{ $leaveRequest->approvedBy->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">วันที่พิจารณา</div>
                            <div class="info-value">{{ $leaveRequest->approved_at?->format('d/m/Y H:i') ?? '-' }}</div>
                        </div>
                        @if($leaveRequest->status === 'rejected' && $leaveRequest->rejection_reason)
                        <div class="col-12">
                            <div class="info-label">เหตุผลที่ไม่อนุมัติ</div>
                            <div class="info-value text-danger">{{ $leaveRequest->rejection_reason }}</div>
                        </div>
                        @endif
                        @if($leaveRequest->approval_notes)
                        <div class="col-12">
                            <div class="info-label">หมายเหตุ</div>
                            <div class="info-value">{{ $leaveRequest->approval_notes }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Days Box -->
            <div class="days-box mb-4">
                <div class="small opacity-75">จำนวนวันลา</div>
                <div class="days">{{ $leaveRequest->total_days ?? 0 }}</div>
                <div>วัน</div>
            </div>

            <!-- Actions -->
            @if($leaveRequest->status === 'pending')
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>การดำเนินการ</h6>
                </div>
                <div class="card-body d-grid gap-2">
                    <button type="button" class="btn btn-success" onclick="approveLeave()">
                        <i class="bi bi-check-circle me-1"></i> อนุมัติ
                    </button>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bi bi-x-circle me-1"></i> ไม่อนุมัติ
                    </button>
                    <hr>
                    <button type="button" class="btn btn-outline-danger" onclick="deleteLeave()">
                        <i class="bi bi-trash me-1"></i> ลบคำขอ
                    </button>
                </div>
            </div>
            @endif

            <!-- Metadata -->
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>ข้อมูลระบบ</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <div class="info-label">ส่งคำขอเมื่อ</div>
                        <div class="info-value small">{{ $leaveRequest->submitted_at?->format('d/m/Y H:i') ?? '-' }}</div>
                    </div>
                    <div class="mb-2">
                        <div class="info-label">สร้างเมื่อ</div>
                        <div class="info-value small">{{ $leaveRequest->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="info-label">แก้ไขล่าสุด</div>
                        <div class="info-value small">{{ $leaveRequest->updated_at?->format('d/m/Y H:i') ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>ไม่อนุมัติคำขอลา</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">เหตุผลที่ไม่อนุมัติ <span class="text-danger">*</span></label>
                    <textarea id="rejectionReason" class="form-control" rows="3" required placeholder="ระบุเหตุผล..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-danger" onclick="confirmReject()">
                    <i class="bi bi-x-lg me-1"></i> ไม่อนุมัติ
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const leaveId = '{{ $leaveRequest->id }}';

function approveLeave() {
    if (confirm('ยืนยันการอนุมัติคำขอลานี้?')) {
        fetch(`/leave-requests/${leaveId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
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
    }
}

function confirmReject() {
    const reason = document.getElementById('rejectionReason').value;

    if (!reason.trim()) {
        alert('กรุณาระบุเหตุผล');
        return;
    }

    fetch(`/leave-requests/${leaveId}/reject`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ rejection_reason: reason })
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
}

function deleteLeave() {
    if (confirm('ยืนยันการลบคำขอลานี้?')) {
        fetch(`/leave-requests/${leaveId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route('leave-requests.index') }}';
            } else {
                alert(data.message);
            }
        })
        .catch(err => {
            alert('เกิดข้อผิดพลาด');
            console.error(err);
        });
    }
}
</script>
@endpush
