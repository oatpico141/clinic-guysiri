@extends('layouts.app')

@section('title', 'คำขอเปลี่ยน PT - GCMS')

@push('styles')
<style>
    .pt-header {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
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

    .stat-icon.total { background: #ede9fe; color: #7c3aed; }
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

    .table-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .data-table thead th {
        background: #f8fafc;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        padding: 1rem;
        border: none;
    }

    .data-table tbody td {
        padding: 1rem;
        vertical-align: middle;
    }

    .data-table tbody tr:hover {
        background: #f8fafc;
    }

    .patient-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .patient-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }

    .pt-change {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .pt-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .pt-badge.from { background: #fee2e2; color: #991b1b; }
    .pt-badge.to { background: #d1fae5; color: #065f46; }

    .status-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .status-pending { background: #fef3c7; color: #92400e; }
    .status-approved { background: #d1fae5; color: #065f46; }
    .status-rejected { background: #fee2e2; color: #991b1b; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="pt-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-2"><i class="bi bi-person-lines-fill me-2"></i>คำขอเปลี่ยน PT</h2>
                <p class="mb-0 opacity-90">จัดการคำขอเปลี่ยน PT ประจำของผู้ป่วย</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="{{ route('pt-requests.create') }}" class="btn btn-light">
                    <i class="bi bi-plus-lg me-1"></i> ส่งคำขอใหม่
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
                        <i class="bi bi-envelope-paper"></i>
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
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-warning">{{ number_format($pendingCount) }}</div>
                        <div class="text-muted small">รอดำเนินการ</div>
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
                        <div class="text-muted small">ปฏิเสธ</div>
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
        <form method="GET" action="{{ route('pt-requests.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <select name="requested_pt_id" class="form-select">
                        <option value="">PT ที่ขอเปลี่ยน</option>
                        @foreach($pts as $pt)
                        <option value="{{ $pt->id }}" {{ request('requested_pt_id') == $pt->id ? 'selected' : '' }}>
                            {{ $pt->first_name }} {{ $pt->last_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="branch_id" class="form-select">
                        <option value="">ทุกสาขา</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">ทุกสถานะ</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>รอดำเนินการ</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>อนุมัติแล้ว</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>ปฏิเสธ</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search me-1"></i> ค้นหา
                    </button>
                    <a href="{{ route('pt-requests.index') }}" class="btn btn-outline-secondary">รีเซ็ต</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="table data-table mb-0">
                <thead>
                    <tr>
                        <th>วันที่</th>
                        <th>ผู้ป่วย</th>
                        <th>การเปลี่ยน PT</th>
                        <th>สาขา</th>
                        <th>เหตุผล</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ptRequests as $request)
                    <tr>
                        <td>{{ $request->requested_at ? \Carbon\Carbon::parse($request->requested_at)->format('d/m/Y H:i') : '-' }}</td>
                        <td>
                            <div class="patient-info">
                                <div class="patient-avatar">
                                    {{ $request->patient ? substr($request->patient->name ?? '', 0, 1) : '?' }}
                                </div>
                                <div>
                                    <strong>{{ $request->patient->name ?? '-' }}</strong>
                                    <br><small class="text-muted">{{ $request->patient->phone ?? '-' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="pt-change">
                                @if($request->originalPt)
                                <span class="pt-badge from">{{ $request->originalPt->first_name ?? '-' }}</span>
                                @else
                                <span class="pt-badge from">ไม่มี</span>
                                @endif
                                <i class="bi bi-arrow-right text-muted"></i>
                                <span class="pt-badge to">{{ $request->requestedPt->first_name ?? '-' }}</span>
                            </div>
                        </td>
                        <td>{{ $request->branch->name ?? '-' }}</td>
                        <td>
                            <span class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $request->reason }}">
                                {{ $request->reason ?? '-' }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($request->status == 'pending')
                                <span class="status-badge status-pending">รอดำเนินการ</span>
                            @elseif($request->status == 'approved')
                                <span class="status-badge status-approved">อนุมัติแล้ว</span>
                            @elseif($request->status == 'rejected')
                                <span class="status-badge status-rejected">ปฏิเสธ</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('pt-requests.show', $request) }}" class="btn btn-sm btn-outline-info" title="ดูรายละเอียด">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($request->status == 'pending')
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="approveRequest('{{ $request->id }}')" title="อนุมัติ">
                                <i class="bi bi-check-lg"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="showRejectModal('{{ $request->id }}')" title="ปฏิเสธ">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            ไม่พบคำขอเปลี่ยน PT
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ptRequests->hasPages())
        <div class="p-3 border-top">
            {{ $ptRequests->withQueryString()->links() }}
        </div>
        @endif
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
                <input type="hidden" id="rejectRequestId">
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
function approveRequest(id) {
    if (!confirm('ยืนยันอนุมัติคำขอนี้?')) return;

    fetch(`/pt-requests/${id}/approve`, {
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

function showRejectModal(id) {
    document.getElementById('rejectRequestId').value = id;
    document.getElementById('rejectionReason').value = '';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function rejectRequest() {
    const id = document.getElementById('rejectRequestId').value;
    const reason = document.getElementById('rejectionReason').value;

    if (!reason.trim()) {
        alert('กรุณาระบุเหตุผลในการปฏิเสธ');
        return;
    }

    fetch(`/pt-requests/${id}/reject`, {
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
