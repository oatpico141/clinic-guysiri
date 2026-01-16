@extends('layouts.app')

@section('title', 'ประวัติแก้ไข Treatment - GCMS')

@push('styles')
<style>
    .audit-header {
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
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

    .stat-icon.total { background: #e2e8f0; color: #475569; }
    .stat-icon.today { background: #dbeafe; color: #2563eb; }
    .stat-icon.create { background: #d1fae5; color: #059669; }
    .stat-icon.update { background: #fef3c7; color: #d97706; }

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

    .action-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .action-create { background: #d1fae5; color: #065f46; }
    .action-update { background: #fef3c7; color: #92400e; }
    .action-delete { background: #fee2e2; color: #991b1b; }

    .field-badge {
        background: #e0e7ff;
        color: #3730a3;
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-family: 'Monaco', 'Consolas', monospace;
    }

    .value-change {
        font-size: 0.85rem;
    }

    .old-value {
        color: #dc2626;
        text-decoration: line-through;
    }

    .new-value {
        color: #059669;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="audit-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-2"><i class="bi bi-clock-history me-2"></i>ประวัติแก้ไข Treatment</h2>
                <p class="mb-0 opacity-90">ติดตามการเปลี่ยนแปลงข้อมูลการรักษา</p>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon total me-3">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">{{ number_format($totalLogs) }}</div>
                        <div class="text-muted small">บันทึกทั้งหมด</div>
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
                    <div class="stat-icon create me-3">
                        <i class="bi bi-plus-circle"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-success">{{ number_format($createCount) }}</div>
                        <div class="text-muted small">สร้างใหม่</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon update me-3">
                        <i class="bi bi-pencil"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-warning">{{ number_format($updateCount) }}</div>
                        <div class="text-muted small">แก้ไข</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('treatment-audit-logs.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="ค้นหา..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="action" class="form-select">
                        <option value="">ทุก Action</option>
                        @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ ucfirst($action) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search me-1"></i> ค้นหา
                    </button>
                    <a href="{{ route('treatment-audit-logs.index') }}" class="btn btn-outline-secondary">รีเซ็ต</a>
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
                        <th>วันที่/เวลา</th>
                        <th>Treatment</th>
                        <th>Action</th>
                        <th>Field</th>
                        <th>การเปลี่ยนแปลง</th>
                        <th>ผู้ดำเนินการ</th>
                        <th class="text-center">ดู</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLogs as $log)
                    <tr>
                        <td>
                            <small>
                                {{ $log->created_at?->format('d/m/Y') }}<br>
                                <span class="text-muted">{{ $log->created_at?->format('H:i:s') }}</span>
                            </small>
                        </td>
                        <td>
                            @if($log->treatment)
                            <strong>{{ $log->treatment->patient->name ?? '-' }}</strong>
                            <br><small class="text-muted">{{ $log->treatment->service->name ?? '-' }}</small>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($log->action == 'create')
                                <span class="action-badge action-create">สร้าง</span>
                            @elseif($log->action == 'update')
                                <span class="action-badge action-update">แก้ไข</span>
                            @elseif($log->action == 'delete')
                                <span class="action-badge action-delete">ลบ</span>
                            @else
                                <span class="action-badge">{{ $log->action }}</span>
                            @endif
                        </td>
                        <td>
                            @if($log->field_name)
                            <span class="field-badge">{{ $log->field_name }}</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="value-change">
                                @if($log->old_value || $log->new_value)
                                    @if($log->old_value)
                                    <span class="old-value">{{ \Illuminate\Support\Str::limit($log->old_value, 30) }}</span>
                                    @endif
                                    @if($log->old_value && $log->new_value)
                                    <i class="bi bi-arrow-right mx-1 text-muted"></i>
                                    @endif
                                    @if($log->new_value)
                                    <span class="new-value">{{ \Illuminate\Support\Str::limit($log->new_value, 30) }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </td>
                        <td>{{ $log->performedBy->name ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('treatment-audit-logs.show', $log) }}" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            ไม่พบประวัติการแก้ไข
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($auditLogs->hasPages())
        <div class="p-3 border-top">
            {{ $auditLogs->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
