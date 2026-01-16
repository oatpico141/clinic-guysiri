@extends('layouts.app')

@section('title', 'จัดการสิทธิ์ - GCMS')

@push('styles')
<style>
    .page-header {
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
    }

    .filter-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        margin-bottom: 1.5rem;
    }

    .permission-table {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .permission-table thead th {
        background: #f8fafc;
        font-weight: 600;
        font-size: 0.85rem;
        color: #64748b;
        border-bottom: 2px solid #e2e8f0;
    }

    .permission-table tbody tr:hover {
        background-color: #f8fafc;
    }

    .module-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        background: #e0e7ff;
        color: #3730a3;
    }

    .action-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .action-view { background: #dbeafe; color: #1e40af; }
    .action-create { background: #d1fae5; color: #065f46; }
    .action-update { background: #fef3c7; color: #92400e; }
    .action-delete { background: #fee2e2; color: #991b1b; }
    .action-other { background: #f1f5f9; color: #475569; }

    .role-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
    }

    .role-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 500;
        background: #f1f5f9;
        color: #475569;
    }

    .permission-card {
        display: none;
        background: white;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    @media (max-width: 768px) {
        .permission-table { display: none !important; }
        .permission-card { display: block; }
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
                <h2 class="mb-2"><i class="bi bi-shield-lock me-2"></i>จัดการสิทธิ์</h2>
                <p class="mb-0 opacity-90">กำหนดสิทธิ์การเข้าถึงระบบ</p>
            </div>
            <a href="{{ route('permissions.create') }}" class="btn btn-light">
                <i class="bi bi-plus-lg me-1"></i> เพิ่มสิทธิ์
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-6 col-md-4">
            <div class="stat-card text-center">
                <div class="fs-2 fw-bold text-primary">{{ number_format($totalPermissions) }}</div>
                <div class="text-muted">สิทธิ์ทั้งหมด</div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card text-center">
                <div class="fs-2 fw-bold text-success">{{ number_format($totalRoles) }}</div>
                <div class="text-muted">บทบาท</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stat-card text-center">
                <div class="fs-2 fw-bold text-info">{{ number_format($moduleCount) }}</div>
                <div class="text-muted">โมดูล</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('permissions.index') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="ค้นหา..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="module" class="form-select">
                        <option value="">ทุกโมดูล</option>
                        @foreach($modules as $module)
                        <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>{{ $module }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-search"></i> ค้นหา
                        </button>
                        <a href="{{ route('permissions.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Table (Desktop) -->
    <div class="permission-table">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>โมดูล</th>
                    <th>การกระทำ</th>
                    <th>คำอธิบาย</th>
                    <th>บทบาทที่มีสิทธิ์</th>
                    <th class="text-end">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($permissions as $permission)
                <tr>
                    <td>
                        <span class="module-badge">{{ $permission->module }}</span>
                    </td>
                    <td>
                        @php
                            $actionClass = match($permission->action) {
                                'view', 'index', 'show' => 'action-view',
                                'create', 'store' => 'action-create',
                                'edit', 'update' => 'action-update',
                                'delete', 'destroy' => 'action-delete',
                                default => 'action-other'
                            };
                        @endphp
                        <span class="action-badge {{ $actionClass }}">{{ $permission->action }}</span>
                    </td>
                    <td>
                        <small class="text-muted">{{ $permission->description ?? '-' }}</small>
                    </td>
                    <td>
                        <div class="role-badges">
                            @forelse($permission->roles as $role)
                            <span class="role-badge">{{ $role->name }}</span>
                            @empty
                            <span class="text-muted">-</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('permissions.edit', $permission) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <i class="bi bi-shield-x fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">ไม่พบข้อมูลสิทธิ์</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Cards (Mobile) -->
    @foreach($permissions as $permission)
    <div class="permission-card">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <span class="module-badge">{{ $permission->module }}</span>
            @php
                $actionClass = match($permission->action) {
                    'view', 'index', 'show' => 'action-view',
                    'create', 'store' => 'action-create',
                    'edit', 'update' => 'action-update',
                    'delete', 'destroy' => 'action-delete',
                    default => 'action-other'
                };
            @endphp
            <span class="action-badge {{ $actionClass }}">{{ $permission->action }}</span>
        </div>
        @if($permission->description)
        <p class="text-muted small mb-2">{{ $permission->description }}</p>
        @endif
        <div class="role-badges mb-2">
            @forelse($permission->roles as $role)
            <span class="role-badge">{{ $role->name }}</span>
            @empty
            <span class="text-muted small">ไม่มีบทบาทที่กำหนด</span>
            @endforelse
        </div>
        <div class="d-flex justify-content-end">
            <a href="{{ route('permissions.edit', $permission) }}" class="btn btn-sm btn-outline-primary">แก้ไข</a>
        </div>
    </div>
    @endforeach

    <!-- Pagination -->
    <div class="mt-4 d-flex justify-content-center">
        {{ $permissions->withQueryString()->links() }}
    </div>
</div>
@endsection
