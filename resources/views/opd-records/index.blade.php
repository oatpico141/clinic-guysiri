@extends('layouts.app')

@section('title', 'บันทึก OPD - GCMS')

@push('styles')
<style>
    .opd-header {
        background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
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

    .stat-icon.total { background: #ccfbf1; color: #0d9488; }
    .stat-icon.active { background: #d1fae5; color: #059669; }
    .stat-icon.today { background: #dbeafe; color: #2563eb; }
    .stat-icon.temp { background: #fef3c7; color: #d97706; }

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

    .opd-number {
        font-family: 'Monaco', 'Consolas', monospace;
        font-weight: 600;
        color: #0d9488;
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
        background: linear-gradient(135deg, #14b8a6, #0d9488);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }

    .status-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .status-active { background: #d1fae5; color: #065f46; }
    .status-closed { background: #e5e7eb; color: #374151; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }

    .temp-badge {
        background: #fef3c7;
        color: #92400e;
        font-size: 0.7rem;
        padding: 0.2rem 0.5rem;
        border-radius: 10px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="opd-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-2"><i class="bi bi-clipboard2-pulse me-2"></i>บันทึก OPD</h2>
                <p class="mb-0 opacity-90">จัดการบันทึกผู้ป่วยนอก</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="{{ route('opd-records.create') }}" class="btn btn-light">
                    <i class="bi bi-plus-lg me-1"></i> สร้าง OPD ใหม่
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
                        <i class="bi bi-clipboard2-pulse"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">{{ number_format($totalRecords) }}</div>
                        <div class="text-muted small">OPD ทั้งหมด</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon active me-3">
                        <i class="bi bi-play-circle"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-success">{{ number_format($activeCount) }}</div>
                        <div class="text-muted small">กำลังดำเนินการ</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon today me-3">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-primary">{{ number_format($todayCount) }}</div>
                        <div class="text-muted small">วันนี้</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon temp me-3">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-warning">{{ number_format($temporaryCount) }}</div>
                        <div class="text-muted small">ชั่วคราว</div>
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
        <form method="GET" action="{{ route('opd-records.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="ค้นหาเลข OPD หรือชื่อผู้ป่วย..." value="{{ request('search') }}">
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
                    <select name="status" class="form-select">
                        <option value="">ทุกสถานะ</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>กำลังดำเนินการ</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>ปิดแล้ว</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ยกเลิก</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" placeholder="จากวันที่">
                </div>
                <div class="col-md-2">
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" placeholder="ถึงวันที่">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i>
                    </button>
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
                        <th>เลข OPD</th>
                        <th>ผู้ป่วย</th>
                        <th>สาขา</th>
                        <th>อาการ</th>
                        <th class="text-center">สถานะ</th>
                        <th>วันที่สร้าง</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($opdRecords as $opd)
                    <tr>
                        <td>
                            <span class="opd-number">{{ $opd->opd_number }}</span>
                            @if($opd->is_temporary)
                            <span class="temp-badge ms-2">ชั่วคราว</span>
                            @endif
                        </td>
                        <td>
                            <div class="patient-info">
                                <div class="patient-avatar">
                                    {{ $opd->patient ? substr($opd->patient->name ?? '', 0, 1) : '?' }}
                                </div>
                                <div>
                                    <strong>{{ $opd->patient->name ?? '-' }}</strong>
                                    <br><small class="text-muted">{{ $opd->patient->phone ?? '-' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $opd->branch->name ?? '-' }}</td>
                        <td>
                            <span class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $opd->chief_complaint }}">
                                {{ $opd->chief_complaint ?? '-' }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($opd->status == 'active')
                                <span class="status-badge status-active">กำลังดำเนินการ</span>
                            @elseif($opd->status == 'closed')
                                <span class="status-badge status-closed">ปิดแล้ว</span>
                            @elseif($opd->status == 'cancelled')
                                <span class="status-badge status-cancelled">ยกเลิก</span>
                            @endif
                        </td>
                        <td>{{ $opd->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('opd-records.show', $opd) }}" class="btn btn-sm btn-outline-info" title="ดูรายละเอียด">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($opd->status == 'active')
                            <a href="{{ route('opd-records.edit', $opd) }}" class="btn btn-sm btn-outline-secondary" title="แก้ไข">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="closeOpd('{{ $opd->id }}')" title="ปิด OPD">
                                <i class="bi bi-check-circle"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            ไม่พบบันทึก OPD
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($opdRecords->hasPages())
        <div class="p-3 border-top">
            {{ $opdRecords->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function closeOpd(id) {
    if (!confirm('ยืนยันปิด OPD นี้?')) return;

    fetch(`/opd-records/${id}/close`, {
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
