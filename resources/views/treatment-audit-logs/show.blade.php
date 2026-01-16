@extends('layouts.app')

@section('title', 'รายละเอียด Audit Log - GCMS')

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
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

    .action-badge {
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-weight: 600;
    }

    .action-create { background: #d1fae5; color: #065f46; }
    .action-update { background: #fef3c7; color: #92400e; }
    .action-delete { background: #fee2e2; color: #991b1b; }

    .change-display {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1.5rem;
    }

    .old-value-box {
        background: #fee2e2;
        border-radius: 8px;
        padding: 1rem;
        border-left: 4px solid #dc2626;
    }

    .new-value-box {
        background: #d1fae5;
        border-radius: 8px;
        padding: 1rem;
        border-left: 4px solid #059669;
    }

    .field-badge {
        background: #e0e7ff;
        color: #3730a3;
        padding: 0.35rem 0.75rem;
        border-radius: 8px;
        font-family: 'Monaco', 'Consolas', monospace;
    }

    .related-log-item {
        background: #f8fafc;
        border-radius: 8px;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
    }

    .changes-json {
        background: #1e293b;
        color: #e2e8f0;
        border-radius: 8px;
        padding: 1rem;
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 0.85rem;
        max-height: 300px;
        overflow-y: auto;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2"><i class="bi bi-clock-history me-2"></i>รายละเอียด Audit Log</h2>
                <p class="mb-0 opacity-90">
                    {{ $auditLog->created_at?->format('d/m/Y H:i:s') ?? '-' }}
                </p>
            </div>
            <a href="{{ route('treatment-audit-logs.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Action & Field -->
            <div class="detail-card">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        @if($auditLog->action == 'create')
                            <span class="action-badge action-create"><i class="bi bi-plus-circle me-1"></i> สร้างใหม่</span>
                        @elseif($auditLog->action == 'update')
                            <span class="action-badge action-update"><i class="bi bi-pencil me-1"></i> แก้ไข</span>
                        @elseif($auditLog->action == 'delete')
                            <span class="action-badge action-delete"><i class="bi bi-trash me-1"></i> ลบ</span>
                        @else
                            <span class="action-badge bg-secondary text-white">{{ $auditLog->action }}</span>
                        @endif
                    </div>
                    @if($auditLog->field_name)
                    <span class="field-badge fs-6">{{ $auditLog->field_name }}</span>
                    @endif
                </div>

                <!-- Change Display -->
                @if($auditLog->old_value || $auditLog->new_value)
                <div class="section-title"><i class="bi bi-arrow-left-right me-2"></i>การเปลี่ยนแปลง</div>
                <div class="change-display">
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="text-muted small mb-2"><i class="bi bi-x-circle me-1"></i> ค่าเดิม</div>
                            <div class="old-value-box">
                                {{ $auditLog->old_value ?: '(ไม่มี)' }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small mb-2"><i class="bi bi-check-circle me-1"></i> ค่าใหม่</div>
                            <div class="new-value-box">
                                {{ $auditLog->new_value ?: '(ไม่มี)' }}
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Full Changes JSON -->
                @if($auditLog->changes && count($auditLog->changes) > 0)
                <div class="section-title mt-4"><i class="bi bi-code-slash me-2"></i>รายละเอียดการเปลี่ยนแปลงทั้งหมด</div>
                <div class="changes-json">
                    <pre class="mb-0">{{ json_encode($auditLog->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
                @endif

                <!-- Reason -->
                @if($auditLog->reason)
                <div class="section-title mt-4"><i class="bi bi-chat-left-text me-2"></i>เหตุผล</div>
                <p class="mb-0">{{ $auditLog->reason }}</p>
                @endif
            </div>

            <!-- Treatment Info -->
            @if($auditLog->treatment)
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-heart-pulse me-2"></i>ข้อมูล Treatment</div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">ผู้ป่วย</div>
                            <div class="info-value">{{ $auditLog->treatment->patient->name ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">บริการ</div>
                            <div class="info-value">{{ $auditLog->treatment->service->name ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">PT</div>
                            <div class="info-value">{{ $auditLog->treatment->pt->first_name ?? '-' }} {{ $auditLog->treatment->pt->last_name ?? '' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">วันที่รักษา</div>
                            <div class="info-value">{{ $auditLog->treatment->treatment_date?->format('d/m/Y') ?? '-' }}</div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('treatments.show', $auditLog->treatment) }}" class="btn btn-outline-primary">
                        ดู Treatment <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <!-- Meta Info -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-info-circle me-2"></i>ข้อมูลเพิ่มเติม</div>

                <div class="info-item">
                    <div class="info-label">วันที่/เวลา</div>
                    <div class="info-value">{{ $auditLog->created_at?->format('d/m/Y H:i:s') ?? '-' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">ผู้ดำเนินการ</div>
                    <div class="info-value">{{ $auditLog->performedBy->name ?? '-' }}</div>
                </div>

                @if($auditLog->ip_address)
                <div class="info-item">
                    <div class="info-label">IP Address</div>
                    <div class="info-value"><code>{{ $auditLog->ip_address }}</code></div>
                </div>
                @endif

                @if($auditLog->user_agent)
                <div class="info-item">
                    <div class="info-label">User Agent</div>
                    <div class="info-value">
                        <small class="text-muted">{{ \Illuminate\Support\Str::limit($auditLog->user_agent, 50) }}</small>
                    </div>
                </div>
                @endif
            </div>

            <!-- Related Logs -->
            @if($relatedLogs->count() > 0)
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-link-45deg me-2"></i>Log อื่นของ Treatment นี้</div>
                @foreach($relatedLogs as $log)
                <a href="{{ route('treatment-audit-logs.show', $log) }}" class="related-log-item d-block text-decoration-none">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">{{ $log->created_at?->format('d/m/Y H:i') }}</small>
                            <br><span class="text-dark">{{ $log->action }} {{ $log->field_name }}</span>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </div>
                </a>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
