@extends('layouts.app')

@section('title', 'รายละเอียดการแทน PT - GCMS')

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
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

    .pt-swap-display {
        background: #f8fafc;
        border-radius: 16px;
        padding: 2rem;
    }

    .pt-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .pt-avatar-lg {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 700;
        color: white;
        margin: 0 auto 1rem;
    }

    .pt-avatar-lg.original {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }

    .pt-avatar-lg.replacement {
        background: linear-gradient(135deg, #10b981, #059669);
    }

    .swap-arrow-lg {
        font-size: 3rem;
        color: #f97316;
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

    .commission-display {
        background: #fff7ed;
        border-radius: 12px;
        padding: 1.5rem;
        border-left: 4px solid #f97316;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2"><i class="bi bi-arrow-left-right me-2"></i>รายละเอียดการแทน PT</h2>
                <p class="mb-0 opacity-90">
                    วันที่ {{ $replacement->replacement_date ? \Carbon\Carbon::parse($replacement->replacement_date)->format('d/m/Y') : '-' }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('pt-replacements.edit', $replacement) }}" class="btn btn-light">
                    <i class="bi bi-pencil me-1"></i> แก้ไข
                </a>
                <a href="{{ route('pt-replacements.index') }}" class="btn btn-outline-light">
                    <i class="bi bi-arrow-left me-1"></i> กลับ
                </a>
            </div>
        </div>
    </div>

    <!-- PT Swap Display -->
    <div class="detail-card">
        <div class="section-title"><i class="bi bi-people me-2"></i>ข้อมูล PT</div>
        <div class="pt-swap-display">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <div class="pt-card">
                        <div class="pt-avatar-lg original">
                            {{ $replacement->originalPt ? substr($replacement->originalPt->first_name, 0, 1) : '?' }}
                        </div>
                        <h5 class="mb-1">{{ $replacement->originalPt->first_name ?? '-' }} {{ $replacement->originalPt->last_name ?? '' }}</h5>
                        <span class="badge bg-danger">PT ที่ถูกแทน</span>
                        @if($replacement->originalPt)
                        <div class="mt-3">
                            <small class="text-muted d-block">
                                <i class="bi bi-telephone me-1"></i>
                                {{ $replacement->originalPt->phone ?? '-' }}
                            </small>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-2 text-center py-3">
                    <i class="bi bi-arrow-right swap-arrow-lg"></i>
                </div>
                <div class="col-md-5">
                    <div class="pt-card">
                        <div class="pt-avatar-lg replacement">
                            {{ $replacement->replacementPt ? substr($replacement->replacementPt->first_name, 0, 1) : '?' }}
                        </div>
                        <h5 class="mb-1">{{ $replacement->replacementPt->first_name ?? '-' }} {{ $replacement->replacementPt->last_name ?? '' }}</h5>
                        <span class="badge bg-success">PT ที่แทน</span>
                        @if($replacement->replacementPt)
                        <div class="mt-3">
                            <small class="text-muted d-block">
                                <i class="bi bi-telephone me-1"></i>
                                {{ $replacement->replacementPt->phone ?? '-' }}
                            </small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <!-- Details -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-info-circle me-2"></i>รายละเอียด</div>

                <div class="info-item">
                    <div class="info-label">วันที่แทน</div>
                    <div class="info-value">
                        <i class="bi bi-calendar3 me-1 text-muted"></i>
                        {{ $replacement->replacement_date ? \Carbon\Carbon::parse($replacement->replacement_date)->format('d/m/Y') : '-' }}
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label">สาขา</div>
                    <div class="info-value">
                        <i class="bi bi-building me-1 text-muted"></i>
                        {{ $replacement->branch->name ?? '-' }}
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label">เหตุผลในการแทน</div>
                    <div class="info-value">{{ $replacement->reason ?? '-' }}</div>
                </div>

                @if($replacement->notes)
                <div class="info-item">
                    <div class="info-label">หมายเหตุ</div>
                    <div class="info-value">{{ $replacement->notes }}</div>
                </div>
                @endif

                <div class="info-item">
                    <div class="info-label">บันทึกเมื่อ</div>
                    <div class="info-value">{{ $replacement->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Commission Handling -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-cash me-2"></i>การจัดการค่าคอมมิชชัน</div>

                <div class="commission-display">
                    @if($replacement->commission_handling == 'original')
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-person-x fs-3 text-danger me-3"></i>
                            <div>
                                <div class="fw-semibold">ค่าคอมมิชชันไป PT เดิม</div>
                                <small class="text-muted">{{ $replacement->originalPt->first_name ?? '-' }} {{ $replacement->originalPt->last_name ?? '' }}</small>
                            </div>
                        </div>
                        <div class="text-muted small">
                            ค่าคอมมิชชันจากการให้บริการในวันนี้จะถูกบันทึกให้ PT เดิมทั้งหมด
                        </div>
                    @elseif($replacement->commission_handling == 'replacement')
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-person-check fs-3 text-success me-3"></i>
                            <div>
                                <div class="fw-semibold">ค่าคอมมิชชันไป PT ที่แทน</div>
                                <small class="text-muted">{{ $replacement->replacementPt->first_name ?? '-' }} {{ $replacement->replacementPt->last_name ?? '' }}</small>
                            </div>
                        </div>
                        <div class="text-muted small">
                            ค่าคอมมิชชันจากการให้บริการในวันนี้จะถูกบันทึกให้ PT ที่มาแทนทั้งหมด
                        </div>
                    @elseif($replacement->commission_handling == 'split')
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-pie-chart fs-3 text-warning me-3"></i>
                            <div>
                                <div class="fw-semibold">แบ่งค่าคอมมิชชัน</div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-6">
                                <div class="bg-white rounded p-2 text-center">
                                    <div class="text-muted small">PT ที่แทน</div>
                                    <div class="fs-4 fw-bold text-success">{{ $replacement->commission_split_percentage ?? 50 }}%</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-white rounded p-2 text-center">
                                    <div class="text-muted small">PT เดิม</div>
                                    <div class="fs-4 fw-bold text-danger">{{ 100 - ($replacement->commission_split_percentage ?? 50) }}%</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-muted text-center py-3">
                            <i class="bi bi-dash-circle fs-3 d-block mb-2"></i>
                            ไม่ได้ระบุการจัดการค่าคอมมิชชัน
                        </div>
                    @endif
                </div>
            </div>

            <!-- Related Info -->
            @if($replacement->appointment || $replacement->treatment)
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-link-45deg me-2"></i>ข้อมูลที่เกี่ยวข้อง</div>

                @if($replacement->appointment)
                <div class="info-item">
                    <div class="info-label">การนัดหมาย</div>
                    <div class="info-value">
                        <a href="{{ route('appointments.show', $replacement->appointment) }}">
                            ดูรายละเอียดการนัดหมาย <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                @endif

                @if($replacement->treatment)
                <div class="info-item">
                    <div class="info-label">การรักษา</div>
                    <div class="info-value">
                        <a href="{{ route('treatments.show', $replacement->treatment) }}">
                            ดูรายละเอียดการรักษา <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
