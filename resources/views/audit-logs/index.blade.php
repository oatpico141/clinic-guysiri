@extends('layouts.app')

@section('title', 'Audit Logs - GCMS')

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
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
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .filter-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        margin-bottom: 1.5rem;
    }

    .log-table {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .log-table thead th {
        background: #f8fafc;
        font-weight: 600;
        font-size: 0.85rem;
        color: #64748b;
        border-bottom: 2px solid #e2e8f0;
    }

    .log-table tbody tr:hover {
        background-color: #f8fafc;
    }

    .action-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .action-create { background: #d1fae5; color: #065f46; }
    .action-update { background: #dbeafe; color: #1e40af; }
    .action-delete { background: #fee2e2; color: #991b1b; }
    .action-login { background: #e0e7ff; color: #3730a3; }
    .action-logout { background: #f1f5f9; color: #475569; }
    .action-view { background: #fef3c7; color: #92400e; }

    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1e293b, #0f172a);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .ip-badge {
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 0.8rem;
        background: #f1f5f9;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
    }

    .time-display {
        font-size: 0.85rem;
        color: #64748b;
    }

    /* Mobile Cards */
    .log-card {
        display: none;
        background: white;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    @media (max-width: 992px) {
        .log-table { display: none !important; }
        .log-card { display: block; }
        .stat-card { margin-bottom: 1rem; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="mb-2"><i class="bi bi-clock-history me-2"></i>Audit Logs</h2>
                <p class="mb-0 opacity-90">ติดตามกิจกรรมและการเปลี่ยนแปลงในระบบ</p>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="fs-2 fw-bold text-primary">{{ number_format($todayLogs) }}</div>
                <div class="text-muted">วันนี้</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="fs-2 fw-bold text-info">{{ number_format($thisWeekLogs) }}</div>
                <div class="text-muted">สัปดาห์นี้</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="fs-2 fw-bold text-success">{{ number_format($createActions) }}</div>
                <div class="text-muted">การสร้าง</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="fs-2 fw-bold text-warning">{{ number_format($updateActions) }}</div>
                <div class="text-muted">การแก้ไข</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('audit-logs.index') }}">
            <div class="row g-3">
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control" placeholder="ค้นหา..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="user_id" class="form-select">
                        <option value="">ผู้ใช้ทั้งหมด</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="action" class="form-select">
                        <option value="">ทุกการกระทำ</option>
                        <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>สร้าง</option>
                        <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>แก้ไข</option>
                        <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>ลบ</option>
                        <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>เข้าสู่ระบบ</option>
                        <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>ออกจากระบบ</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="module" class="form-select">
                        <option value="">ทุกโมดูล</option>
                        @foreach($modules as $module)
                        <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>{{ $module }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="จากวันที่">
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-search"></i> ค้นหา
                        </button>
                        <a href="{{ route('audit-logs.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Table (Desktop) -->
    <div class="log-table">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>เวลา</th>
                    <th>ผู้ใช้</th>
                    <th>การกระทำ</th>
                    <th>โมดูล</th>
                    <th>รายละเอียด</th>
                    <th>IP</th>
                    <th class="text-end">ดู</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td>
                        <div class="time-display">
                            {{ $log->created_at->format('d/m/Y') }}<br>
                            <small>{{ $log->created_at->format('H:i:s') }}</small>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="user-avatar">
                                {{ $log->user ? substr($log->user->name, 0, 1) : '?' }}
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $log->user->name ?? 'ระบบ' }}</div>
                                <small class="text-muted">{{ $log->branch->name ?? '-' }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        @switch($log->action)
                            @case('create')
                                <span class="action-badge action-create"><i class="bi bi-plus-circle me-1"></i>สร้าง</span>
                                @break
                            @case('update')
                                <span class="action-badge action-update"><i class="bi bi-pencil me-1"></i>แก้ไข</span>
                                @break
                            @case('delete')
                                <span class="action-badge action-delete"><i class="bi bi-trash me-1"></i>ลบ</span>
                                @break
                            @case('login')
                                <span class="action-badge action-login"><i class="bi bi-box-arrow-in-right me-1"></i>เข้าสู่ระบบ</span>
                                @break
                            @case('logout')
                                <span class="action-badge action-logout"><i class="bi bi-box-arrow-right me-1"></i>ออกจากระบบ</span>
                                @break
                            @case('view')
                                <span class="action-badge action-view"><i class="bi bi-eye me-1"></i>ดู</span>
                                @break
                            @default
                                <span class="action-badge" style="background: #f1f5f9; color: #475569;">{{ $log->action }}</span>
                        @endswitch
                    </td>
                    <td>
                        <span class="text-muted">{{ $log->module ?? '-' }}</span>
                    </td>
                    <td>
                        <div style="max-width: 250px;">
                            {{ \Illuminate\Support\Str::limit($log->description, 50) }}
                        </div>
                    </td>
                    <td>
                        <span class="ip-badge">{{ $log->ip_address ?? '-' }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('audit-logs.show', $log) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-clock-history fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">ไม่พบข้อมูล Audit Log</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Cards (Mobile) -->
    @foreach($logs as $log)
    <div class="log-card">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="time-display">
                {{ $log->created_at->format('d/m/Y H:i:s') }}
            </div>
            @switch($log->action)
                @case('create')
                    <span class="action-badge action-create">สร้าง</span>
                    @break
                @case('update')
                    <span class="action-badge action-update">แก้ไข</span>
                    @break
                @case('delete')
                    <span class="action-badge action-delete">ลบ</span>
                    @break
                @case('login')
                    <span class="action-badge action-login">เข้าสู่ระบบ</span>
                    @break
                @case('logout')
                    <span class="action-badge action-logout">ออกจากระบบ</span>
                    @break
                @default
                    <span class="action-badge" style="background: #f1f5f9; color: #475569;">{{ $log->action }}</span>
            @endswitch
        </div>
        <div class="d-flex align-items-center gap-2 mb-2">
            <div class="user-avatar">
                {{ $log->user ? substr($log->user->name, 0, 1) : '?' }}
            </div>
            <div>
                <div class="fw-semibold">{{ $log->user->name ?? 'ระบบ' }}</div>
                <small class="text-muted">{{ $log->module ?? '-' }}</small>
            </div>
        </div>
        <p class="text-muted small mb-2">{{ $log->description ?? '-' }}</p>
        <div class="d-flex justify-content-between align-items-center">
            <span class="ip-badge">{{ $log->ip_address ?? '-' }}</span>
            <a href="{{ route('audit-logs.show', $log) }}" class="btn btn-sm btn-outline-primary">ดูรายละเอียด</a>
        </div>
    </div>
    @endforeach

    <!-- Pagination -->
    <div class="mt-4 d-flex justify-content-center">
        {{ $logs->withQueryString()->links() }}
    </div>
</div>
@endsection
