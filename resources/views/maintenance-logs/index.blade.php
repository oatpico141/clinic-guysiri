@extends('layouts.app')

@section('title', 'ประวัติซ่อมบำรุง - GCMS')

@push('styles')
<style>
    .maint-header {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
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
    .stat-icon.completed { background: #d1fae5; color: #059669; }
    .stat-icon.cost { background: #fee2e2; color: #dc2626; }

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

    .maint-number {
        font-family: 'Monaco', 'Consolas', monospace;
        color: #7c3aed;
        font-weight: 600;
    }

    .type-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .type-preventive { background: #dbeafe; color: #1e40af; }
    .type-corrective { background: #fef3c7; color: #92400e; }
    .type-emergency { background: #fee2e2; color: #991b1b; }
    .type-inspection { background: #d1fae5; color: #065f46; }

    .status-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 10px;
        font-size: 0.7rem;
    }

    .status-pending { background: #fef3c7; color: #92400e; }
    .status-in_progress { background: #dbeafe; color: #1e40af; }
    .status-completed { background: #d1fae5; color: #065f46; }
    .status-cancelled { background: #e5e7eb; color: #374151; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="maint-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-2"><i class="bi bi-wrench-adjustable me-2"></i>ประวัติซ่อมบำรุง</h2>
                <p class="mb-0 opacity-90">จัดการประวัติการซ่อมบำรุงอุปกรณ์</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="{{ route('maintenance-logs.create') }}" class="btn btn-light">
                    <i class="bi bi-plus-lg me-1"></i> บันทึกซ่อมบำรุง
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
                        <i class="bi bi-wrench-adjustable"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">{{ number_format($totalLogs) }}</div>
                        <div class="text-muted small">ทั้งหมด</div>
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
                        <div class="text-muted small">รอดำเนินการ</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon completed me-3">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-success">{{ number_format($completedCount) }}</div>
                        <div class="text-muted small">เสร็จสิ้น</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon cost me-3">
                        <i class="bi bi-currency-exchange"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-danger">฿{{ number_format($totalCost, 0) }}</div>
                        <div class="text-muted small">ค่าใช้จ่ายรวม</div>
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
        <form method="GET" action="{{ route('maintenance-logs.index') }}">
            <div class="row g-3">
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control" placeholder="ค้นหา..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="equipment_id" class="form-select">
                        <option value="">อุปกรณ์ทั้งหมด</option>
                        @foreach($equipments as $eq)
                        <option value="{{ $eq->id }}" {{ request('equipment_id') == $eq->id ? 'selected' : '' }}>{{ $eq->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="maintenance_type" class="form-select">
                        <option value="">ทุกประเภท</option>
                        <option value="preventive" {{ request('maintenance_type') == 'preventive' ? 'selected' : '' }}>ป้องกัน</option>
                        <option value="corrective" {{ request('maintenance_type') == 'corrective' ? 'selected' : '' }}>แก้ไข</option>
                        <option value="emergency" {{ request('maintenance_type') == 'emergency' ? 'selected' : '' }}>ฉุกเฉิน</option>
                        <option value="inspection" {{ request('maintenance_type') == 'inspection' ? 'selected' : '' }}>ตรวจสอบ</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">ทุกสถานะ</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>รอดำเนินการ</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>กำลังดำเนินการ</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>เสร็จสิ้น</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i>
                    </button>
                    <a href="{{ route('maintenance-logs.index') }}" class="btn btn-outline-secondary">รีเซ็ต</a>
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
                        <th>เลขที่</th>
                        <th>อุปกรณ์</th>
                        <th>ประเภท</th>
                        <th>วันที่</th>
                        <th>ผู้ให้บริการ</th>
                        <th class="text-end">ค่าใช้จ่าย</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($maintenanceLogs as $log)
                    <tr>
                        <td><span class="maint-number">{{ $log->maintenance_number }}</span></td>
                        <td>
                            <strong>{{ $log->equipment->name ?? '-' }}</strong>
                            <br><small class="text-muted">{{ $log->branch->name ?? '-' }}</small>
                        </td>
                        <td>
                            @switch($log->maintenance_type)
                                @case('preventive')
                                    <span class="type-badge type-preventive">ป้องกัน</span>
                                    @break
                                @case('corrective')
                                    <span class="type-badge type-corrective">แก้ไข</span>
                                    @break
                                @case('emergency')
                                    <span class="type-badge type-emergency">ฉุกเฉิน</span>
                                    @break
                                @case('inspection')
                                    <span class="type-badge type-inspection">ตรวจสอบ</span>
                                    @break
                            @endswitch
                        </td>
                        <td>{{ $log->maintenance_date?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $log->service_provider ?? $log->performed_by ?? '-' }}</td>
                        <td class="text-end">{{ $log->cost ? '฿' . number_format($log->cost, 2) : '-' }}</td>
                        <td class="text-center">
                            @switch($log->status)
                                @case('pending')
                                    <span class="status-badge status-pending">รอดำเนินการ</span>
                                    @break
                                @case('in_progress')
                                    <span class="status-badge status-in_progress">กำลังดำเนินการ</span>
                                    @break
                                @case('completed')
                                    <span class="status-badge status-completed">เสร็จสิ้น</span>
                                    @break
                                @case('cancelled')
                                    <span class="status-badge status-cancelled">ยกเลิก</span>
                                    @break
                            @endswitch
                        </td>
                        <td class="text-center">
                            <a href="{{ route('maintenance-logs.show', $log) }}" class="btn btn-sm btn-outline-info" title="ดูรายละเอียด">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('maintenance-logs.edit', $log) }}" class="btn btn-sm btn-outline-secondary" title="แก้ไข">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            ไม่พบประวัติซ่อมบำรุง
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($maintenanceLogs->hasPages())
        <div class="p-3 border-top">
            {{ $maintenanceLogs->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
