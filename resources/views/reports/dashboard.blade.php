@extends('layouts.app')

@section('title', 'Dashboard - GCMS')

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #0284c7, #0ea5e9);
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        color: white;
        margin-bottom: 1.5rem;
    }
    .kpi-card {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        border: 1px solid #e2e8f0;
        height: 100%;
        transition: all 0.2s;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .kpi-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .kpi-icon.sales { background: #dcfce7; color: #16a34a; }
    .kpi-icon.patients { background: #dbeafe; color: #2563eb; }
    .kpi-icon.utilization { background: #fef3c7; color: #d97706; }
    .kpi-icon.invoices { background: #e9d5ff; color: #7c3aed; }
    .kpi-icon.revenue { background: #cffafe; color: #0891b2; }
    .kpi-icon.queue { background: #fce7f3; color: #db2777; }
    .kpi-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
    }
    .kpi-label {
        font-size: 0.8rem;
        color: #64748b;
        font-weight: 500;
    }
    .kpi-sub {
        font-size: 0.75rem;
        color: #94a3b8;
    }
    .period-btn {
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        background: white;
        color: #64748b;
        font-size: 0.875rem;
        transition: all 0.2s;
    }
    .period-btn:hover, .period-btn.active {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }
    .section-card {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        border: 1px solid #e2e8f0;
        height: 100%;
    }
    .section-title {
        font-size: 1rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .filter-card {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        border: 1px solid #e2e8f0;
        margin-bottom: 1.25rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h4>
            <p class="mb-0 opacity-75">ภาพรวมการดำเนินงาน</p>
        </div>
        <a href="{{ route('reports.pnl') }}" class="btn btn-light">
            <i class="bi bi-bar-chart-line me-1"></i>รายงานทั้งหมด
        </a>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('reports.dashboard') }}">
            <div class="row g-3 align-items-end">
                <div class="col-auto">
                    <label class="form-label small text-muted mb-1">ช่วงเวลา</label>
                    <div class="btn-group">
                        <button type="submit" name="period" value="today" class="period-btn {{ $period == 'today' ? 'active' : '' }}">วันนี้</button>
                        <button type="submit" name="period" value="week" class="period-btn {{ $period == 'week' ? 'active' : '' }}">สัปดาห์นี้</button>
                        <button type="submit" name="period" value="month" class="period-btn {{ $period == 'month' ? 'active' : '' }}">เดือนนี้</button>
                    </div>
                </div>
                <div class="col-auto">
                    <label class="form-label small text-muted mb-1">สาขา</label>
                    <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">ทุกสาขา</option>
                        @foreach($branches ?? [] as $branch)
                        <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <span class="text-muted small">
                        <i class="bi bi-calendar3 me-1"></i>
                        {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                    </span>
                </div>
            </div>
        </form>
    </div>

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-4 col-xl-2">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="kpi-icon sales">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                </div>
                <div class="kpi-value">{{ number_format($totalSales ?? 0, 0) }}</div>
                <div class="kpi-label">ยอดขาย (บาท)</div>
            </div>
        </div>

        <div class="col-6 col-lg-4 col-xl-2">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="kpi-icon patients">
                        <i class="bi bi-person-plus"></i>
                    </div>
                </div>
                <div class="kpi-value">{{ number_format($newPatients ?? 0) }}</div>
                <div class="kpi-label">ลูกค้าใหม่</div>
            </div>
        </div>

        <div class="col-6 col-lg-4 col-xl-2">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="kpi-icon utilization">
                        <i class="bi bi-speedometer"></i>
                    </div>
                </div>
                <div class="kpi-value">{{ number_format($utilizationRate ?? 0, 1) }}%</div>
                <div class="kpi-label">Utilization Rate</div>
            </div>
        </div>

        <div class="col-6 col-lg-4 col-xl-2">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="kpi-icon invoices">
                        <i class="bi bi-receipt"></i>
                    </div>
                </div>
                <div class="kpi-value">{{ number_format($totalInvoices ?? 0) }}</div>
                <div class="kpi-label">ใบเสร็จ</div>
            </div>
        </div>

        <div class="col-6 col-lg-4 col-xl-2">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="kpi-icon queue">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
                <div class="kpi-value">{{ number_format($totalPatients ?? 0) }}</div>
                <div class="kpi-label">ลูกค้าทั้งหมด</div>
            </div>
        </div>

        <div class="col-6 col-lg-4 col-xl-2">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="kpi-icon revenue">
                        <i class="bi bi-graph-up"></i>
                    </div>
                </div>
                <div class="kpi-value">{{ number_format($avgRevenuePerPatient ?? 0, 0) }}</div>
                <div class="kpi-label">รายได้/คน (บาท)</div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="section-card">
                <div class="section-title">
                    <i class="bi bi-lightning-fill text-warning"></i>
                    ลิงก์ด่วน
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <a href="{{ route('queue.index') }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-people me-2"></i>จัดการคิว
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('appointments.index') }}" class="btn btn-outline-success w-100">
                            <i class="bi bi-calendar-check me-2"></i>นัดหมาย
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('billing.index') }}" class="btn btn-outline-warning w-100">
                            <i class="bi bi-cash me-2"></i>เก็บเงิน
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('patients.create') }}" class="btn btn-outline-info w-100">
                            <i class="bi bi-person-plus me-2"></i>ลูกค้าใหม่
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="section-card">
                <div class="section-title">
                    <i class="bi bi-bar-chart-fill text-primary"></i>
                    รายงานยอดนิยม
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('reports.pnl') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-graph-up-arrow me-2 text-success"></i>รายงานกำไรขาดทุน</span>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                    <a href="{{ route('invoices.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-receipt me-2 text-primary"></i>รายงานใบเสร็จ</span>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                    <a href="{{ route('commissions.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-currency-dollar me-2 text-warning"></i>รายงานค่าคอมมิชชัน</span>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                    <a href="{{ route('stock.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-box-seam me-2 text-info"></i>รายงานสต็อก</span>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
