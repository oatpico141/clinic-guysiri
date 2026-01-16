@extends('layouts.app')

@section('title', 'รายการขอลา - GCMS')

@push('styles')
<style>
    .leave-header {
        background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
        border-radius: 20px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        height: 100%;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .stat-icon.total { background: #fce7f3; color: #db2777; }
    .stat-icon.pending { background: #fef3c7; color: #d97706; }
    .stat-icon.approved { background: #d1fae5; color: #059669; }
    .stat-icon.rejected { background: #fee2e2; color: #dc2626; }

    .filter-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        margin-bottom: 1.5rem;
    }

    .leave-table-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .leave-table thead th {
        background: #f8fafc;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        padding: 1rem;
        border: none;
    }

    .leave-table tbody td {
        padding: 1rem;
        vertical-align: middle;
    }

    .leave-table tbody tr:hover {
        background: #f8fafc;
    }

    .badge-pending { background: #fef3c7; color: #92400e; }
    .badge-approved { background: #d1fae5; color: #065f46; }
    .badge-rejected { background: #fee2e2; color: #991b1b; }

    .leave-type-badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="leave-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-2"><i class="bi bi-calendar-x me-2"></i>รายการขอลา</h2>
                <p class="mb-0 opacity-90">จัดการคำขอลาของพนักงาน</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="{{ route('leave-requests.create') }}" class="btn btn-light">
                    <i class="bi bi-plus-lg me-1"></i> ยื่นคำขอลา
                </a>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon total me-3">
                        <i class="bi bi-calendar-x"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">{{ number_format($totalRequests) }}</div>
                        <div class="text-muted small">คำขอทั้งหมด</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon pending me-3">
                        <i class="bi bi-clock"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-warning">{{ number_format($pendingCount) }}</div>
                        <div class="text-muted small">รออนุมัติ</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon approved me-3">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-success">{{ number_format($approvedCount) }}</div>
                        <div class="text-muted small">อนุมัติแล้ว</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon rejected me-3">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-danger">{{ number_format($rejectedCount) }}</div>
                        <div class="text-muted small">ไม่อนุมัติ</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('leave-requests.index') }}">
            <div class="row g-3">
                <div class="col-md-2">
                    <select name="staff_id" class="form-select">
                        <option value="">พนักงานทุกคน</option>
                        @foreach($staffs as $staff)
                        <option value="{{ $staff->id }}" {{ request('staff_id') == $staff->id ? 'selected' : '' }}>
                            {{ $staff->first_name }} {{ $staff->last_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="branch_id" class="form-select">
                        <option value="">ทุกสาขา</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="leave_type" class="form-select">
                        <option value="">ทุกประเภท</option>
                        <option value="annual" {{ request('leave_type') == 'annual' ? 'selected' : '' }}>ลาพักร้อน</option>
                        <option value="sick" {{ request('leave_type') == 'sick' ? 'selected' : '' }}>ลาป่วย</option>
                        <option value="personal" {{ request('leave_type') == 'personal' ? 'selected' : '' }}>ลากิจ</option>
                        <option value="maternity" {{ request('leave_type') == 'maternity' ? 'selected' : '' }}>ลาคลอด</option>
                        <option value="unpaid" {{ request('leave_type') == 'unpaid' ? 'selected' : '' }}>ลาไม่รับเงิน</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">ทุกสถานะ</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>รออนุมัติ</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>อนุมัติแล้ว</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>ไม่อนุมัติ</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search me-1"></i> ค้นหา
                    </button>
                    <a href="{{ route('leave-requests.index') }}" class="btn btn-outline-secondary">รีเซ็ต</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="leave-table-card">
        <div class="table-responsive">
            <table class="table leave-table mb-0">
                <thead>
                    <tr>
                        <th>เลขที่</th>
                        <th>พนักงาน</th>
                        <th>ประเภท</th>
                        <th>วันที่ลา</th>
                        <th class="text-center">จำนวนวัน</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaveRequests as $leave)
                    <tr>
                        <td><code>{{ $leave->leave_number }}</code></td>
                        <td>
                            <strong>{{ $leave->staff->first_name ?? '' }} {{ $leave->staff->last_name ?? '' }}</strong>
                            <br><small class="text-muted">{{ $leave->branch->name ?? '-' }}</small>
                        </td>
                        <td>
                            @switch($leave->leave_type)
                                @case('annual')
                                    <span class="badge bg-info leave-type-badge">ลาพักร้อน</span>
                                    @break
                                @case('sick')
                                    <span class="badge bg-danger leave-type-badge">ลาป่วย</span>
                                    @break
                                @case('personal')
                                    <span class="badge bg-secondary leave-type-badge">ลากิจ</span>
                                    @break
                                @case('maternity')
                                    <span class="badge bg-pink leave-type-badge" style="background: #ec4899;">ลาคลอด</span>
                                    @break
                                @case('unpaid')
                                    <span class="badge bg-dark leave-type-badge">ลาไม่รับเงิน</span>
                                    @break
                                @default
                                    <span class="badge bg-light text-dark leave-type-badge">{{ $leave->leave_type }}</span>
                            @endswitch
                        </td>
                        <td>
                            {{ $leave->start_date?->format('d/m/Y') }}
                            @if($leave->end_date && $leave->end_date->ne($leave->start_date))
                                - {{ $leave->end_date->format('d/m/Y') }}
                            @endif
                        </td>
                        <td class="text-center">{{ $leave->total_days }} วัน</td>
                        <td class="text-center">
                            @switch($leave->status)
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
                        </td>
                        <td class="text-center">
                            <a href="{{ route('leave-requests.show', $leave) }}" class="btn btn-sm btn-outline-info" title="ดูรายละเอียด">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($leave->status === 'pending')
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="approveLeave('{{ $leave->id }}')" title="อนุมัติ">
                                <i class="bi bi-check-lg"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="showRejectModal('{{ $leave->id }}')" title="ไม่อนุมัติ">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            ไม่พบรายการขอลา
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leaveRequests->hasPages())
        <div class="p-3 border-top">
            {{ $leaveRequests->withQueryString()->links() }}
        </div>
        @endif
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
                <input type="hidden" id="rejectLeaveId">
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
let rejectModal;

document.addEventListener('DOMContentLoaded', function() {
    rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
});

function approveLeave(id) {
    if (confirm('ยืนยันการอนุมัติคำขอลานี้?')) {
        fetch(`/leave-requests/${id}/approve`, {
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

function showRejectModal(id) {
    document.getElementById('rejectLeaveId').value = id;
    document.getElementById('rejectionReason').value = '';
    rejectModal.show();
}

function confirmReject() {
    const id = document.getElementById('rejectLeaveId').value;
    const reason = document.getElementById('rejectionReason').value;

    if (!reason.trim()) {
        alert('กรุณาระบุเหตุผล');
        return;
    }

    fetch(`/leave-requests/${id}/reject`, {
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
</script>
@endpush
