@extends('layouts.app')

@section('title', 'จัดการอุปกรณ์ - GCMS')

@push('styles')
<style>
    .equipment-header {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
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

    .stat-icon.items { background: #e0f2fe; color: #0284c7; }
    .stat-icon.active { background: #d1fae5; color: #059669; }
    .stat-icon.maintenance { background: #fef3c7; color: #d97706; }
    .stat-icon.value { background: #ede9fe; color: #7c3aed; }

    .filter-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        margin-bottom: 1.5rem;
    }

    .equipment-table-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .equipment-table thead th {
        background: #f8fafc;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        padding: 1rem;
        border: none;
    }

    .equipment-table tbody td {
        padding: 1rem;
        vertical-align: middle;
    }

    .equipment-table tbody tr:hover {
        background: #f8fafc;
    }

    .modal-content {
        border-radius: 16px;
        border: none;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="equipment-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-2"><i class="bi bi-tools me-2"></i>จัดการอุปกรณ์</h2>
                <p class="mb-0 opacity-90">จัดการอุปกรณ์และซ่อมบำรุง</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="{{ route('equipment.create') }}" class="btn btn-light">
                    <i class="bi bi-plus-lg me-1"></i> เพิ่มอุปกรณ์
                </a>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon items me-3">
                        <i class="bi bi-tools"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">{{ number_format($totalEquipment) }}</div>
                        <div class="text-muted small">อุปกรณ์ทั้งหมด</div>
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
                        <div class="text-muted small">พร้อมใช้งาน</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon maintenance me-3">
                        <i class="bi bi-wrench"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-warning">{{ number_format($maintenanceDue) }}</div>
                        <div class="text-muted small">ถึงกำหนดซ่อมบำรุง</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon value me-3">
                        <i class="bi bi-currency-exchange"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">฿{{ number_format($totalValue, 0) }}</div>
                        <div class="text-muted small">มูลค่ารวม</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if($maintenanceDue > 0)
    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-wrench me-2"></i>
        <strong>แจ้งเตือน!</strong> มีอุปกรณ์ {{ $maintenanceDue }} รายการถึงกำหนดซ่อมบำรุงใน 7 วัน
        <a href="{{ route('equipment.index', ['maintenance_due' => 1]) }}" class="alert-link">ดูรายการ</a>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('equipment.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="ค้นหาชื่อ/รหัส..." value="{{ request('search') }}">
                    <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">ทุกสถานะ</option>
                        <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>พร้อมใช้งาน</option>
                        <option value="in_use" {{ request('status') == 'in_use' ? 'selected' : '' }}>กำลังใช้งาน</option>
                        <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>ซ่อมบำรุง</option>
                        <option value="retired" {{ request('status') == 'retired' ? 'selected' : '' }}>ปลดระวาง</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="maintenance_due" value="1" id="maintenanceDue" {{ request('maintenance_due') ? 'checked' : '' }}>
                        <label class="form-check-label" for="maintenanceDue">ถึงกำหนดซ่อม</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search me-1"></i> ค้นหา
                    </button>
                    <a href="{{ route('equipment.index') }}" class="btn btn-outline-secondary">รีเซ็ต</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Equipment Table -->
    <div class="equipment-table-card">
        <div class="table-responsive">
            <table class="table equipment-table mb-0">
                <thead>
                    <tr>
                        <th>รหัส</th>
                        <th>ชื่ออุปกรณ์</th>
                        <th>หมวด</th>
                        <th>สาขา</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-end">มูลค่า</th>
                        <th class="text-center">ซ่อมบำรุงครั้งถัดไป</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($equipment as $item)
                    <tr>
                        <td><code>{{ $item->equipment_code }}</code></td>
                        <td>
                            <strong>{{ $item->name }}</strong>
                            @if($item->serial_number)
                                <br><small class="text-muted">S/N: {{ $item->serial_number }}</small>
                            @endif
                        </td>
                        <td>{{ $item->category }}</td>
                        <td>{{ $item->branch->name ?? '-' }}</td>
                        <td class="text-center">
                            @switch($item->status)
                                @case('available')
                                    <span class="badge bg-success">พร้อมใช้</span>
                                    @break
                                @case('in_use')
                                    <span class="badge bg-primary">กำลังใช้</span>
                                    @break
                                @case('maintenance')
                                    <span class="badge bg-warning">ซ่อมบำรุง</span>
                                    @break
                                @case('retired')
                                    <span class="badge bg-secondary">ปลดระวาง</span>
                                    @break
                                @default
                                    <span class="badge bg-info">{{ $item->status }}</span>
                            @endswitch
                        </td>
                        <td class="text-end">฿{{ number_format($item->current_value, 0) }}</td>
                        <td class="text-center">
                            @if($item->next_maintenance_date)
                                @if($item->next_maintenance_date <= now()->addDays(7))
                                    <span class="text-danger">{{ $item->next_maintenance_date->format('d/m/Y') }}</span>
                                @else
                                    {{ $item->next_maintenance_date->format('d/m/Y') }}
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('equipment.show', $item) }}" class="btn btn-sm btn-outline-info me-1" title="ดูรายละเอียด">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('equipment.edit', $item) }}" class="btn btn-sm btn-outline-secondary" title="แก้ไข">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            ไม่พบข้อมูลอุปกรณ์
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($equipment->hasPages())
        <div class="p-3 border-top">
            {{ $equipment->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
