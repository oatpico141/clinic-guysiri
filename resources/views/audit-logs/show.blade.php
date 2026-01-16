@extends('layouts.app')

@section('title', 'รายละเอียด Audit Log - GCMS')

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
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

    .action-badge-lg {
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-weight: 600;
    }

    .action-create { background: #d1fae5; color: #065f46; }
    .action-update { background: #dbeafe; color: #1e40af; }
    .action-delete { background: #fee2e2; color: #991b1b; }
    .action-login { background: #e0e7ff; color: #3730a3; }
    .action-logout { background: #f1f5f9; color: #475569; }

    .values-box {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1rem;
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 0.85rem;
        max-height: 400px;
        overflow-y: auto;
    }

    .old-values-box {
        background: #fef2f2;
        border-left: 4px solid #dc2626;
    }

    .new-values-box {
        background: #f0fdf4;
        border-left: 4px solid #16a34a;
    }

    .user-avatar-lg {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1e293b, #0f172a);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .ip-badge {
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 0.85rem;
        background: #f1f5f9;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
    }

    .related-log-item {
        background: #f8fafc;
        border-radius: 8px;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
    }

    .json-key { color: #9333ea; }
    .json-string { color: #059669; }
    .json-number { color: #d97706; }
    .json-null { color: #6b7280; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2">
                    <i class="bi bi-clock-history me-2"></i>
                    รายละเอียด Audit Log
                </h2>
                <p class="mb-0 opacity-90">
                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                </p>
            </div>
            <a href="{{ route('audit-logs.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Activity Details -->
            <div class="detail-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="section-title mb-0 border-0 pb-0"><i class="bi bi-activity me-2"></i>กิจกรรม</div>
                    @switch($log->action)
                        @case('create')
                            <span class="action-badge-lg action-create"><i class="bi bi-plus-circle me-1"></i> สร้าง</span>
                            @break
                        @case('update')
                            <span class="action-badge-lg action-update"><i class="bi bi-pencil me-1"></i> แก้ไข</span>
                            @break
                        @case('delete')
                            <span class="action-badge-lg action-delete"><i class="bi bi-trash me-1"></i> ลบ</span>
                            @break
                        @case('login')
                            <span class="action-badge-lg action-login"><i class="bi bi-box-arrow-in-right me-1"></i> เข้าสู่ระบบ</span>
                            @break
                        @case('logout')
                            <span class="action-badge-lg action-logout"><i class="bi bi-box-arrow-right me-1"></i> ออกจากระบบ</span>
                            @break
                        @default
                            <span class="action-badge-lg" style="background: #f1f5f9; color: #475569;">{{ $log->action }}</span>
                    @endswitch
                </div>

                @if($log->description)
                <h6 class="fw-semibold">คำอธิบาย</h6>
                <p>{{ $log->description }}</p>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">โมดูล</div>
                            <div class="info-value">{{ $log->module ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">ประเภท Model</div>
                            <div class="info-value">{{ $log->model_type ? class_basename($log->model_type) : '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">Model ID</div>
                            <div class="info-value">
                                <code>{{ $log->model_id ?? '-' }}</code>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">HTTP Method</div>
                            <div class="info-value">{{ $log->method ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                @if($log->url)
                <div class="info-item">
                    <div class="info-label">URL</div>
                    <div class="info-value">
                        <code style="word-break: break-all;">{{ $log->url }}</code>
                    </div>
                </div>
                @endif
            </div>

            <!-- Data Changes -->
            @if($log->old_values || $log->new_values)
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-database-diff me-2"></i>การเปลี่ยนแปลงข้อมูล</div>

                <div class="row">
                    @if($log->old_values)
                    <div class="col-md-6 mb-3">
                        <h6 class="text-danger mb-2"><i class="bi bi-dash-circle me-1"></i>ค่าเดิม</h6>
                        <div class="values-box old-values-box">
                            <pre class="mb-0">{!! formatJsonHighlight($log->old_values) !!}</pre>
                        </div>
                    </div>
                    @endif

                    @if($log->new_values)
                    <div class="col-md-6 mb-3">
                        <h6 class="text-success mb-2"><i class="bi bi-plus-circle me-1"></i>ค่าใหม่</h6>
                        <div class="values-box new-values-box">
                            <pre class="mb-0">{!! formatJsonHighlight($log->new_values) !!}</pre>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Related Logs -->
            @if($relatedLogs->count() > 0)
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-list-ul me-2"></i>Log อื่นของข้อมูลนี้</div>
                @foreach($relatedLogs as $related)
                <a href="{{ route('audit-logs.show', $related) }}" class="related-log-item d-block text-decoration-none">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            @switch($related->action)
                                @case('create')
                                    <span class="badge bg-success">สร้าง</span>
                                    @break
                                @case('update')
                                    <span class="badge bg-primary">แก้ไข</span>
                                    @break
                                @case('delete')
                                    <span class="badge bg-danger">ลบ</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ $related->action }}</span>
                            @endswitch
                            <span class="text-dark ms-2">{{ $related->user->name ?? 'ระบบ' }}</span>
                            <br>
                            <small class="text-muted">{{ $related->created_at->format('d/m/Y H:i:s') }}</small>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </div>
                </a>
                @endforeach
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <!-- User Info -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-person me-2"></i>ผู้ดำเนินการ</div>

                <div class="text-center mb-3">
                    <div class="user-avatar-lg mx-auto mb-3">
                        {{ $log->user ? substr($log->user->name, 0, 1) : '?' }}
                    </div>
                    <h5 class="mb-1">{{ $log->user->name ?? 'ระบบ' }}</h5>
                    @if($log->user)
                    <small class="text-muted">{{ $log->user->email ?? '-' }}</small>
                    @endif
                </div>

                @if($log->branch)
                <div class="info-item">
                    <div class="info-label">สาขา</div>
                    <div class="info-value">{{ $log->branch->name }}</div>
                </div>
                @endif
            </div>

            <!-- Technical Info -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-globe me-2"></i>ข้อมูลทางเทคนิค</div>

                <div class="info-item">
                    <div class="info-label">IP Address</div>
                    <div class="info-value">
                        <span class="ip-badge">{{ $log->ip_address ?? '-' }}</span>
                    </div>
                </div>

                @if($log->user_agent)
                <div class="info-item">
                    <div class="info-label">User Agent</div>
                    <div class="info-value" style="font-size: 0.8rem; word-break: break-word;">
                        {{ $log->user_agent }}
                    </div>
                </div>
                @endif
            </div>

            <!-- Timestamps -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-calendar me-2"></i>เวลา</div>

                <div class="info-item">
                    <div class="info-label">บันทึกเมื่อ</div>
                    <div class="info-value">{{ $log->created_at->format('d/m/Y H:i:s') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">เมื่อ</div>
                    <div class="info-value">{{ $log->created_at->diffForHumans() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

@php
function formatJsonHighlight($data) {
    if (is_array($data)) {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        $json = $data;
    }

    // Simple syntax highlighting
    $json = htmlspecialchars($json);
    $json = preg_replace('/"([^"]+)":/m', '<span class="json-key">"$1"</span>:', $json);
    $json = preg_replace('/: "([^"]*)"/', ': <span class="json-string">"$1"</span>', $json);
    $json = preg_replace('/: (\d+)/', ': <span class="json-number">$1</span>', $json);
    $json = preg_replace('/: (null)/', ': <span class="json-null">$1</span>', $json);

    return $json;
}
@endphp
@endsection
