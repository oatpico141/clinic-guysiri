@extends('layouts.app')

@section('title', 'รายละเอียดการซ่อมบำรุง - GCMS')

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        border-radius: 20px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }

    .detail-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        margin-bottom: 1.5rem;
    }

    .section-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e5e7eb;
    }

    .maint-number-display {
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 1.25rem;
        font-weight: 700;
    }

    .info-item {
        padding: 0.75rem 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-label {
        font-size: 0.85rem;
        color: #6b7280;
        margin-bottom: 0.25rem;
    }

    .info-value {
        font-weight: 500;
        color: #1f2937;
    }

    .type-badge-lg {
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-weight: 600;
    }

    .type-preventive { background: #dbeafe; color: #1e40af; }
    .type-corrective { background: #fef3c7; color: #92400e; }
    .type-emergency { background: #fee2e2; color: #991b1b; }
    .type-inspection { background: #d1fae5; color: #065f46; }

    .status-display {
        padding: 1.5rem;
        border-radius: 12px;
        text-align: center;
    }

    .status-display.pending {
        background: #fef3c7;
        border-left: 4px solid #d97706;
    }

    .status-display.in_progress {
        background: #dbeafe;
        border-left: 4px solid #2563eb;
    }

    .status-display.completed {
        background: #d1fae5;
        border-left: 4px solid #059669;
    }

    .equipment-card {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1.5rem;
    }

    .related-log-item {
        background: #f8fafc;
        border-radius: 8px;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2">
                    <i class="bi bi-wrench-adjustable me-2"></i>
                    <span class="maint-number-display">{{ $maintenanceLog->maintenance_number }}</span>
                </h2>
                <p class="mb-0 opacity-90">
                    {{ $maintenanceLog->maintenance_date?->format('d/m/Y') ?? '-' }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('maintenance-logs.edit', $maintenanceLog) }}" class="btn btn-light">
                    <i class="bi bi-pencil me-1"></i> แก้ไข
                </a>
                <a href="{{ route('maintenance-logs.index') }}" class="btn btn-outline-light">
                    <i class="bi bi-arrow-left me-1"></i> กลับ
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Equipment Info -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-gear me-2"></i>อุปกรณ์</div>
                <div class="equipment-card">
                    <div class="d-flex align-items-center">
                        <div class="me-4">
                            <div style="width: 60px; height: 60px; border-radius: 12px; background: linear-gradient(135deg, #7c3aed, #6d28d9); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                <i class="bi bi-gear"></i>
                            </div>
                        </div>
                        <div>
                            <h5 class="mb-1">{{ $maintenanceLog->equipment->name ?? '-' }}</h5>
                            <p class="text-muted mb-0">
                                <i class="bi bi-building me-1"></i>{{ $maintenanceLog->branch->name ?? '-' }}
                            </p>
                        </div>
                        @if($maintenanceLog->equipment)
                        <div class="ms-auto">
                            <a href="{{ route('equipment.show', $maintenanceLog->equipment) }}" class="btn btn-outline-primary">
                                ดูอุปกรณ์ <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Maintenance Details -->
            <div class="detail-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="section-title mb-0 border-0 pb-0"><i class="bi bi-info-circle me-2"></i>รายละเอียดการซ่อมบำรุง</div>
                    @switch($maintenanceLog->maintenance_type)
                        @case('preventive')
                            <span class="type-badge-lg type-preventive"><i class="bi bi-shield-check me-1"></i> ป้องกัน</span>
                            @break
                        @case('corrective')
                            <span class="type-badge-lg type-corrective"><i class="bi bi-tools me-1"></i> แก้ไข</span>
                            @break
                        @case('emergency')
                            <span class="type-badge-lg type-emergency"><i class="bi bi-exclamation-triangle me-1"></i> ฉุกเฉิน</span>
                            @break
                        @case('inspection')
                            <span class="type-badge-lg type-inspection"><i class="bi bi-search me-1"></i> ตรวจสอบ</span>
                            @break
                    @endswitch
                </div>

                <h6 class="fw-semibold">รายละเอียด</h6>
                <p>{{ $maintenanceLog->description ?? '-' }}</p>

                @if($maintenanceLog->work_performed)
                <h6 class="fw-semibold mt-4">งานที่ดำเนินการ</h6>
                <p>{{ $maintenanceLog->work_performed }}</p>
                @endif

                @if($maintenanceLog->notes)
                <h6 class="fw-semibold mt-4">หมายเหตุ</h6>
                <p class="mb-0">{{ $maintenanceLog->notes }}</p>
                @endif
            </div>

            <!-- Related Logs -->
            @if($relatedLogs->count() > 0)
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-clock-history me-2"></i>ประวัติซ่อมบำรุงอื่นของอุปกรณ์นี้</div>
                @foreach($relatedLogs as $log)
                <a href="{{ route('maintenance-logs.show', $log) }}" class="related-log-item d-block text-decoration-none">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-bold text-dark">{{ $log->maintenance_number }}</span>
                            <br><small class="text-muted">{{ $log->maintenance_date?->format('d/m/Y') }} - {{ $log->description ? \Illuminate\Support\Str::limit($log->description, 50) : '-' }}</small>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </div>
                </a>
                @endforeach
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <!-- Status -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-flag me-2"></i>สถานะ</div>

                @if($maintenanceLog->status == 'pending')
                <div class="status-display pending">
                    <i class="bi bi-clock fs-1 d-block mb-2 text-warning"></i>
                    <h5 class="mb-1">รอดำเนินการ</h5>
                </div>
                @elseif($maintenanceLog->status == 'in_progress')
                <div class="status-display in_progress">
                    <i class="bi bi-gear-wide-connected fs-1 d-block mb-2 text-primary"></i>
                    <h5 class="mb-1">กำลังดำเนินการ</h5>
                </div>
                @elseif($maintenanceLog->status == 'completed')
                <div class="status-display completed">
                    <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                    <h5 class="mb-1">เสร็จสิ้น</h5>
                </div>
                @else
                <div class="status-display" style="background: #f3f4f6; border-left: 4px solid #6b7280;">
                    <i class="bi bi-x-circle fs-1 d-block mb-2 text-secondary"></i>
                    <h5 class="mb-1">ยกเลิก</h5>
                </div>
                @endif
            </div>

            <!-- Cost & Service -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-cash me-2"></i>ค่าใช้จ่าย</div>

                <div class="text-center py-3">
                    <div class="fs-2 fw-bold text-danger">
                        ฿{{ number_format($maintenanceLog->cost ?? 0, 2) }}
                    </div>
                </div>

                @if($maintenanceLog->service_provider || $maintenanceLog->performed_by)
                <hr>
                @if($maintenanceLog->service_provider)
                <div class="info-item">
                    <div class="info-label">ผู้ให้บริการ</div>
                    <div class="info-value">{{ $maintenanceLog->service_provider }}</div>
                </div>
                @endif

                @if($maintenanceLog->performed_by)
                <div class="info-item">
                    <div class="info-label">ผู้ดำเนินการ</div>
                    <div class="info-value">{{ $maintenanceLog->performed_by }}</div>
                </div>
                @endif
                @endif
            </div>

            <!-- Schedule -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-calendar me-2"></i>กำหนดการ</div>

                <div class="info-item">
                    <div class="info-label">วันที่ดำเนินการ</div>
                    <div class="info-value">{{ $maintenanceLog->maintenance_date?->format('d/m/Y') ?? '-' }}</div>
                </div>

                @if($maintenanceLog->next_maintenance_date)
                <div class="info-item">
                    <div class="info-label">ซ่อมบำรุงครั้งถัดไป</div>
                    <div class="info-value text-primary">
                        <i class="bi bi-calendar-event me-1"></i>
                        {{ $maintenanceLog->next_maintenance_date->format('d/m/Y') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
