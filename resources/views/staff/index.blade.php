@extends('layouts.app')

@section('title', 'จัดการพนักงาน - GCMS')

@push('styles')
<style>
    .staff-header {
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
    .stat-icon.active { background: #d1fae5; color: #059669; }
    .stat-icon.pt { background: #dbeafe; color: #2563eb; }
    .stat-icon.leave { background: #fef3c7; color: #d97706; }

    .filter-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        margin-bottom: 1.5rem;
    }

    .staff-table-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .staff-table thead th {
        background: #f8fafc;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        padding: 1rem;
        border: none;
    }

    .staff-table tbody td {
        padding: 1rem;
        vertical-align: middle;
    }

    .staff-table tbody tr:hover {
        background: #f8fafc;
    }

    .badge-active { background: #d1fae5; color: #065f46; }
    .badge-on_leave { background: #fef3c7; color: #92400e; }
    .badge-terminated { background: #fee2e2; color: #991b1b; }

    .position-badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="staff-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-2"><i class="bi bi-people me-2"></i>จัดการพนักงาน</h2>
                <p class="mb-0 opacity-90">จัดการข้อมูลพนักงานและบุคลากร</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="{{ route('staff.create') }}" class="btn btn-light">
                    <i class="bi bi-plus-lg me-1"></i> เพิ่มพนักงาน
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
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">{{ number_format($totalStaff) }}</div>
                        <div class="text-muted small">พนักงานทั้งหมด</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon active me-3">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-success">{{ number_format($activeCount) }}</div>
                        <div class="text-muted small">ทำงานอยู่</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon pt me-3">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-primary">{{ number_format($ptCount) }}</div>
                        <div class="text-muted small">PT ปฏิบัติงาน</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon leave me-3">
                        <i class="bi bi-calendar-x"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-warning">{{ number_format($onLeaveCount) }}</div>
                        <div class="text-muted small">ลางาน</div>
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

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('staff.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="ค้นหาชื่อ/รหัส/โทร..." value="{{ request('search') }}">
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
                    <select name="position" class="form-select">
                        <option value="">ทุกตำแหน่ง</option>
                        <option value="pt" {{ request('position') == 'pt' ? 'selected' : '' }}>PT</option>
                        <option value="receptionist" {{ request('position') == 'receptionist' ? 'selected' : '' }}>พนักงานต้อนรับ</option>
                        <option value="manager" {{ request('position') == 'manager' ? 'selected' : '' }}>ผู้จัดการ</option>
                        <option value="admin" {{ request('position') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="nurse" {{ request('position') == 'nurse' ? 'selected' : '' }}>พยาบาล</option>
                        <option value="assistant" {{ request('position') == 'assistant' ? 'selected' : '' }}>ผู้ช่วย</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="employment_status" class="form-select">
                        <option value="">ทุกสถานะ</option>
                        <option value="active" {{ request('employment_status') == 'active' ? 'selected' : '' }}>ทำงานอยู่</option>
                        <option value="on_leave" {{ request('employment_status') == 'on_leave' ? 'selected' : '' }}>ลางาน</option>
                        <option value="terminated" {{ request('employment_status') == 'terminated' ? 'selected' : '' }}>พ้นสภาพ</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search me-1"></i> ค้นหา
                    </button>
                    <a href="{{ route('staff.index') }}" class="btn btn-outline-secondary">รีเซ็ต</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Staff Table -->
    <div class="staff-table-card">
        <div class="table-responsive">
            <table class="table staff-table mb-0">
                <thead>
                    <tr>
                        <th>รหัส</th>
                        <th>ชื่อพนักงาน</th>
                        <th>ตำแหน่ง</th>
                        <th>สาขา</th>
                        <th>เบอร์โทร</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center">วันที่เริ่มงาน</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staffs as $staff)
                    <tr>
                        <td><code>{{ $staff->employee_id }}</code></td>
                        <td>
                            <strong>{{ $staff->first_name }} {{ $staff->last_name }}</strong>
                            @if($staff->user)
                                <br><small class="text-muted"><i class="bi bi-person-circle"></i> {{ $staff->user->email }}</small>
                            @endif
                        </td>
                        <td>
                            @switch($staff->position)
                                @case('pt')
                                    <span class="badge bg-primary position-badge">PT</span>
                                    @break
                                @case('receptionist')
                                    <span class="badge bg-info position-badge">พนักงานต้อนรับ</span>
                                    @break
                                @case('manager')
                                    <span class="badge bg-purple position-badge" style="background: #7c3aed;">ผู้จัดการ</span>
                                    @break
                                @case('admin')
                                    <span class="badge bg-dark position-badge">Admin</span>
                                    @break
                                @case('nurse')
                                    <span class="badge bg-success position-badge">พยาบาล</span>
                                    @break
                                @case('assistant')
                                    <span class="badge bg-secondary position-badge">ผู้ช่วย</span>
                                    @break
                                @default
                                    <span class="badge bg-light text-dark position-badge">{{ $staff->position }}</span>
                            @endswitch
                        </td>
                        <td>{{ $staff->branch->name ?? '-' }}</td>
                        <td>{{ $staff->phone ?? '-' }}</td>
                        <td class="text-center">
                            @switch($staff->employment_status)
                                @case('active')
                                    <span class="badge badge-active">ทำงานอยู่</span>
                                    @break
                                @case('on_leave')
                                    <span class="badge badge-on_leave">ลางาน</span>
                                    @break
                                @case('terminated')
                                    <span class="badge badge-terminated">พ้นสภาพ</span>
                                    @break
                            @endswitch
                        </td>
                        <td class="text-center">{{ $staff->hire_date?->format('d/m/Y') ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('staff.show', $staff) }}" class="btn btn-sm btn-outline-info me-1" title="ดูรายละเอียด">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('staff.edit', $staff) }}" class="btn btn-sm btn-outline-secondary" title="แก้ไข">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            ไม่พบข้อมูลพนักงาน
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($staffs->hasPages())
        <div class="p-3 border-top">
            {{ $staffs->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
