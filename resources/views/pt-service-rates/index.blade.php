@extends('layouts.app')

@section('title', 'อัตราค่าบริการ PT - GCMS')

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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
        font-size: 1.25rem;
    }

    .filter-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        margin-bottom: 1.5rem;
    }

    .rate-table {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        overflow: hidden;
    }

    .rate-table th {
        background: #f8fafc;
        font-weight: 600;
        color: #64748b;
        font-size: 0.85rem;
        text-transform: uppercase;
        padding: 1rem;
    }

    .rate-table td {
        padding: 1rem;
        vertical-align: middle;
    }

    .rate-table tbody tr:hover {
        background: #fefce8;
    }

    .pt-badge {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .service-tag {
        background: #dbeafe;
        color: #1e40af;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        font-size: 0.8rem;
    }

    .branch-tag {
        background: #f3e8ff;
        color: #7c3aed;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        font-size: 0.8rem;
    }

    .price-display {
        font-family: 'Monaco', 'Consolas', monospace;
        font-weight: 700;
        color: #059669;
    }

    .commission-display {
        font-size: 0.85rem;
        color: #6b7280;
    }

    .status-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-active { background: #d1fae5; color: #059669; }
    .status-inactive { background: #fee2e2; color: #dc2626; }

    /* Mobile card view */
    .rate-card {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        border-left: 4px solid #f59e0b;
    }

    @media (min-width: 768px) {
        .mobile-cards { display: none; }
    }

    @media (max-width: 767px) {
        .desktop-table { display: none; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="mb-2"><i class="bi bi-currency-exchange me-2"></i>อัตราค่าบริการ PT</h2>
                <p class="mb-0 opacity-90">จัดการราคาบริการและค่าคอมมิชชั่นสำหรับ PT</p>
            </div>
            <a href="{{ route('pt-service-rates.create') }}" class="btn btn-light btn-lg">
                <i class="bi bi-plus-lg me-2"></i>เพิ่มอัตราใหม่
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="bi bi-list-ul"></i>
                    </div>
                    <div>
                        <div class="text-muted small">ทั้งหมด</div>
                        <div class="fs-4 fw-bold">{{ number_format($totalRates) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <div class="text-muted small">ใช้งาน</div>
                        <div class="fs-4 fw-bold">{{ number_format($activeRates) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                        <i class="bi bi-person"></i>
                    </div>
                    <div>
                        <div class="text-muted small">PT</div>
                        <div class="fs-4 fw-bold">{{ number_format($ptCount) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                        <i class="bi bi-grid"></i>
                    </div>
                    <div>
                        <div class="text-muted small">บริการ</div>
                        <div class="fs-4 fw-bold">{{ number_format($serviceCount) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="filter-card">
        <form method="GET" action="{{ route('pt-service-rates.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">PT</label>
                    <select name="pt_id" class="form-select">
                        <option value="">-- ทั้งหมด --</option>
                        @foreach($pts as $pt)
                        <option value="{{ $pt->id }}" {{ request('pt_id') == $pt->id ? 'selected' : '' }}>{{ $pt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">บริการ</label>
                    <select name="service_id" class="form-select">
                        <option value="">-- ทั้งหมด --</option>
                        @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">สาขา</label>
                    <select name="branch_id" class="form-select">
                        <option value="">-- ทั้งหมด --</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">สถานะ</label>
                    <select name="status" class="form-select">
                        <option value="">-- ทั้งหมด --</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>ใช้งาน</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>ไม่ใช้งาน</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-search"></i>
                        </button>
                        <a href="{{ route('pt-service-rates.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Desktop Table -->
    <div class="rate-table desktop-table">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>PT</th>
                    <th>บริการ</th>
                    <th>สาขา</th>
                    <th class="text-end">ราคา</th>
                    <th class="text-center">Commission</th>
                    <th class="text-center">DF</th>
                    <th class="text-center">สถานะ</th>
                    <th width="100"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($rates as $rate)
                <tr>
                    <td>
                        <span class="pt-badge">{{ $rate->pt->name ?? '-' }}</span>
                    </td>
                    <td>
                        <span class="service-tag">{{ $rate->service->name ?? '-' }}</span>
                    </td>
                    <td>
                        <span class="branch-tag">{{ $rate->branch->name ?? '-' }}</span>
                    </td>
                    <td class="text-end">
                        <span class="price-display">฿{{ number_format($rate->price, 2) }}</span>
                    </td>
                    <td class="text-center">
                        @if($rate->commission_rate)
                        <span class="commission-display">{{ number_format($rate->commission_rate, 1) }}%</span>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($rate->df_rate)
                        <span class="commission-display">฿{{ number_format($rate->df_rate, 0) }}</span>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($rate->is_active)
                        <span class="status-badge status-active">ใช้งาน</span>
                        @else
                        <span class="status-badge status-inactive">ไม่ใช้งาน</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('pt-service-rates.show', $rate) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('pt-service-rates.edit', $rate) }}" class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <div class="text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            ไม่พบข้อมูลอัตราค่าบริการ
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Cards -->
    <div class="mobile-cards">
        @forelse($rates as $rate)
        <div class="rate-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="pt-badge">{{ $rate->pt->name ?? '-' }}</span>
                @if($rate->is_active)
                <span class="status-badge status-active">ใช้งาน</span>
                @else
                <span class="status-badge status-inactive">ไม่ใช้งาน</span>
                @endif
            </div>
            <div class="mb-2">
                <span class="service-tag me-1">{{ $rate->service->name ?? '-' }}</span>
                <span class="branch-tag">{{ $rate->branch->name ?? '-' }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="price-display fs-5">฿{{ number_format($rate->price, 2) }}</div>
                    <small class="text-muted">
                        @if($rate->commission_rate)
                        Com: {{ number_format($rate->commission_rate, 1) }}%
                        @endif
                        @if($rate->df_rate)
                        | DF: ฿{{ number_format($rate->df_rate, 0) }}
                        @endif
                    </small>
                </div>
                <div class="d-flex gap-1">
                    <a href="{{ route('pt-service-rates.show', $rate) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('pt-service-rates.edit', $rate) }}" class="btn btn-sm btn-outline-warning">
                        <i class="bi bi-pencil"></i>
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5">
            <div class="text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                ไม่พบข้อมูลอัตราค่าบริการ
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($rates->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $rates->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
