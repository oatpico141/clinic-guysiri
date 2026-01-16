@extends('layouts.app')

@section('title', 'ประเมินพนักงาน - GCMS')

@push('styles')
<style>
    .eval-header {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
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

    .stat-icon.total { background: #e0e7ff; color: #4f46e5; }
    .stat-icon.pending { background: #fef3c7; color: #d97706; }
    .stat-icon.completed { background: #d1fae5; color: #059669; }
    .stat-icon.score { background: #fce7f3; color: #db2777; }

    .filter-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        margin-bottom: 1.5rem;
    }

    .eval-table-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .eval-table thead th {
        background: #f8fafc;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        padding: 1rem;
        border: none;
    }

    .eval-table tbody td {
        padding: 1rem;
        vertical-align: middle;
    }

    .eval-table tbody tr:hover {
        background: #f8fafc;
    }

    .badge-draft { background: #e5e7eb; color: #374151; }
    .badge-pending { background: #fef3c7; color: #92400e; }
    .badge-completed { background: #d1fae5; color: #065f46; }

    .score-badge {
        display: inline-block;
        width: 40px;
        height: 40px;
        line-height: 40px;
        text-align: center;
        border-radius: 50%;
        font-weight: 700;
        font-size: 0.85rem;
    }

    .score-excellent { background: #d1fae5; color: #065f46; }
    .score-good { background: #dbeafe; color: #1e40af; }
    .score-satisfactory { background: #fef3c7; color: #92400e; }
    .score-needs-improvement { background: #fed7aa; color: #9a3412; }
    .score-unsatisfactory { background: #fee2e2; color: #991b1b; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="eval-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-2"><i class="bi bi-clipboard-check me-2"></i>ประเมินพนักงาน</h2>
                <p class="mb-0 opacity-90">จัดการการประเมินผลงานพนักงาน</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="{{ route('evaluations.create') }}" class="btn btn-light">
                    <i class="bi bi-plus-lg me-1"></i> สร้างการประเมิน
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
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">{{ number_format($totalEvaluations) }}</div>
                        <div class="text-muted small">การประเมินทั้งหมด</div>
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
                    <div class="stat-icon score me-3">
                        <i class="bi bi-star"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold" style="color: #db2777;">{{ number_format($avgScore, 1) }}</div>
                        <div class="text-muted small">คะแนนเฉลี่ย</div>
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
        <form method="GET" action="{{ route('evaluations.index') }}">
            <div class="row g-3">
                <div class="col-md-2">
                    <select name="staff_id" class="form-select">
                        <option value="">พนักงานทุกคน</option>
                        @foreach($staffs as $staff)
                        <option value="{{ $staff->id }}" {{ request('staff_id') == $staff->id ? 'selected' : '' }}>
                            {{ $staff->first_name }} {{ $staff->last_name }}
                        </option>
                        @endforeach
                    </select>
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
                    <select name="evaluation_type" class="form-select">
                        <option value="">ทุกประเภท</option>
                        <option value="probation" {{ request('evaluation_type') == 'probation' ? 'selected' : '' }}>ทดลองงาน</option>
                        <option value="quarterly" {{ request('evaluation_type') == 'quarterly' ? 'selected' : '' }}>รายไตรมาส</option>
                        <option value="annual" {{ request('evaluation_type') == 'annual' ? 'selected' : '' }}>ประจำปี</option>
                        <option value="improvement_plan" {{ request('evaluation_type') == 'improvement_plan' ? 'selected' : '' }}>แผนพัฒนา</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">ทุกสถานะ</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>แบบร่าง</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>รอดำเนินการ</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>เสร็จสิ้น</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search me-1"></i> ค้นหา
                    </button>
                    <a href="{{ route('evaluations.index') }}" class="btn btn-outline-secondary">รีเซ็ต</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="eval-table-card">
        <div class="table-responsive">
            <table class="table eval-table mb-0">
                <thead>
                    <tr>
                        <th>วันที่</th>
                        <th>พนักงาน</th>
                        <th>ประเภท</th>
                        <th class="text-center">คะแนน</th>
                        <th class="text-center">สถานะ</th>
                        <th>ผู้ประเมิน</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($evaluations as $eval)
                    <tr>
                        <td>{{ $eval->evaluation_date?->format('d/m/Y') ?? '-' }}</td>
                        <td>
                            <strong>{{ $eval->staff->first_name ?? '' }} {{ $eval->staff->last_name ?? '' }}</strong>
                            <br><small class="text-muted">{{ $eval->branch->name ?? '-' }}</small>
                        </td>
                        <td>
                            @switch($eval->evaluation_type)
                                @case('probation')
                                    <span class="badge bg-info">ทดลองงาน</span>
                                    @break
                                @case('quarterly')
                                    <span class="badge bg-primary">รายไตรมาส</span>
                                    @break
                                @case('annual')
                                    <span class="badge bg-success">ประจำปี</span>
                                    @break
                                @case('improvement_plan')
                                    <span class="badge bg-warning">แผนพัฒนา</span>
                                    @break
                            @endswitch
                        </td>
                        <td class="text-center">
                            @if($eval->overall_score)
                            @php
                                $scoreClass = 'score-satisfactory';
                                if ($eval->overall_score >= 90) $scoreClass = 'score-excellent';
                                elseif ($eval->overall_score >= 80) $scoreClass = 'score-good';
                                elseif ($eval->overall_score >= 70) $scoreClass = 'score-satisfactory';
                                elseif ($eval->overall_score >= 60) $scoreClass = 'score-needs-improvement';
                                else $scoreClass = 'score-unsatisfactory';
                            @endphp
                            <span class="score-badge {{ $scoreClass }}">{{ number_format($eval->overall_score, 0) }}</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @switch($eval->status)
                                @case('draft')
                                    <span class="badge badge-draft">แบบร่าง</span>
                                    @break
                                @case('pending')
                                    <span class="badge badge-pending">รอดำเนินการ</span>
                                    @break
                                @case('completed')
                                    <span class="badge badge-completed">เสร็จสิ้น</span>
                                    @break
                            @endswitch
                        </td>
                        <td>{{ $eval->evaluator->name ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('evaluations.show', $eval) }}" class="btn btn-sm btn-outline-info" title="ดูรายละเอียด">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($eval->status !== 'completed')
                            <a href="{{ route('evaluations.edit', $eval) }}" class="btn btn-sm btn-outline-secondary" title="แก้ไข">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            ไม่พบการประเมิน
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($evaluations->hasPages())
        <div class="p-3 border-top">
            {{ $evaluations->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
