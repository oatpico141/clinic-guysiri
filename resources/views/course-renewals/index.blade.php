@extends('layouts.app')

@section('title', 'ต่ออายุคอร์ส - GCMS')

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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

    .renewal-table {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .renewal-table thead th {
        background: #f8fafc;
        font-weight: 600;
        font-size: 0.85rem;
        color: #64748b;
        border-bottom: 2px solid #e2e8f0;
    }

    .renewal-table tbody tr:hover {
        background-color: #f8fafc;
    }

    .renewal-number {
        font-family: 'Monaco', 'Consolas', monospace;
        font-weight: 600;
    }

    .extension-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        background: #d1fae5;
        color: #065f46;
    }

    .renewal-card {
        display: none;
        background: white;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    @media (max-width: 768px) {
        .renewal-table { display: none !important; }
        .renewal-card { display: block; }
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
                <h2 class="mb-2"><i class="bi bi-arrow-repeat me-2"></i>ต่ออายุคอร์ส</h2>
                <p class="mb-0 opacity-90">จัดการการต่ออายุคอร์สของลูกค้า</p>
            </div>
            <a href="{{ route('course-renewals.create') }}" class="btn btn-light">
                <i class="bi bi-plus-lg me-1"></i> ต่ออายุ
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="fs-2 fw-bold text-primary">{{ number_format($totalRenewals) }}</div>
                <div class="text-muted">ต่ออายุทั้งหมด</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="fs-2 fw-bold text-success">{{ number_format($thisMonthRenewals) }}</div>
                <div class="text-muted">เดือนนี้</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="fs-2 fw-bold text-info">฿{{ number_format($totalRevenue) }}</div>
                <div class="text-muted">รายได้เดือนนี้</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="fs-2 fw-bold text-warning">{{ number_format($avgExtension, 0) }}</div>
                <div class="text-muted">วันเฉลี่ย</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('course-renewals.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="ค้นหา..." value="{{ request('search') }}">
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
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="จากวันที่">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="ถึงวันที่">
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-search"></i> ค้นหา
                        </button>
                        <a href="{{ route('course-renewals.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Table (Desktop) -->
    <div class="renewal-table">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>เลขที่</th>
                    <th>ลูกค้า</th>
                    <th>คอร์ส</th>
                    <th>วันที่ต่ออายุ</th>
                    <th>ต่ออายุ</th>
                    <th>ค่าธรรมเนียม</th>
                    <th class="text-end">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($renewals as $renewal)
                <tr>
                    <td>
                        <a href="{{ route('course-renewals.show', $renewal) }}" class="renewal-number text-decoration-none">
                            {{ $renewal->renewal_number }}
                        </a>
                    </td>
                    <td>{{ $renewal->patient->name ?? '-' }}</td>
                    <td>{{ $renewal->coursePurchase->coursePackage->name ?? '-' }}</td>
                    <td>{{ $renewal->renewal_date?->format('d/m/Y') ?? '-' }}</td>
                    <td>
                        <span class="extension-badge">
                            <i class="bi bi-plus me-1"></i>{{ $renewal->extension_days }} วัน
                        </span>
                    </td>
                    <td>
                        @if($renewal->renewal_fee > 0)
                        <span class="text-success fw-semibold">฿{{ number_format($renewal->renewal_fee, 2) }}</span>
                        @else
                        <span class="text-muted">ฟรี</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('course-renewals.show', $renewal) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-arrow-repeat fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">ไม่พบข้อมูลการต่ออายุ</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Cards (Mobile) -->
    @foreach($renewals as $renewal)
    <div class="renewal-card">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <a href="{{ route('course-renewals.show', $renewal) }}" class="renewal-number text-decoration-none">
                {{ $renewal->renewal_number }}
            </a>
            <span class="extension-badge">+{{ $renewal->extension_days }} วัน</span>
        </div>
        <div class="mb-2">
            <div class="fw-semibold">{{ $renewal->patient->name ?? '-' }}</div>
            <small class="text-muted">{{ $renewal->coursePurchase->coursePackage->name ?? '-' }}</small>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">
                <i class="bi bi-calendar me-1"></i>{{ $renewal->renewal_date?->format('d/m/Y') }}
            </small>
            @if($renewal->renewal_fee > 0)
            <span class="text-success fw-semibold">฿{{ number_format($renewal->renewal_fee) }}</span>
            @else
            <span class="text-muted">ฟรี</span>
            @endif
        </div>
    </div>
    @endforeach

    <!-- Pagination -->
    <div class="mt-4 d-flex justify-content-center">
        {{ $renewals->withQueryString()->links() }}
    </div>
</div>
@endsection
