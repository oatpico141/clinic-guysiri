@extends('layouts.app')

@section('title', 'รายละเอียดเอกสาร - GCMS')

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

    .doc-number-display {
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

    .type-receipt { background: #d1fae5; color: #065f46; }
    .type-invoice { background: #dbeafe; color: #1e40af; }
    .type-medical_certificate { background: #fef3c7; color: #92400e; }
    .type-consent_form { background: #e0e7ff; color: #3730a3; }
    .type-other { background: #f1f5f9; color: #475569; }

    .status-display {
        padding: 1.5rem;
        border-radius: 12px;
        text-align: center;
    }

    .status-display.active {
        background: #d1fae5;
        border-left: 4px solid #059669;
    }

    .status-display.archived {
        background: #f1f5f9;
        border-left: 4px solid #64748b;
    }

    .status-display.cancelled {
        background: #fee2e2;
        border-left: 4px solid #dc2626;
    }

    .file-preview {
        background: #f8fafc;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
    }

    .file-icon-large {
        font-size: 4rem;
        color: #64748b;
    }

    .related-doc-item {
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
                    <i class="bi bi-file-earmark-text me-2"></i>
                    <span class="doc-number-display">{{ $document->document_number }}</span>
                </h2>
                <p class="mb-0 opacity-90">
                    {{ $document->document_date?->format('d/m/Y') ?? '-' }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('documents.edit', $document) }}" class="btn btn-light">
                    <i class="bi bi-pencil me-1"></i> แก้ไข
                </a>
                <a href="{{ route('documents.index') }}" class="btn btn-outline-light">
                    <i class="bi bi-arrow-left me-1"></i> กลับ
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Document Details -->
            <div class="detail-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="section-title mb-0 border-0 pb-0"><i class="bi bi-info-circle me-2"></i>รายละเอียดเอกสาร</div>
                    @switch($document->document_type)
                        @case('receipt')
                            <span class="type-badge-lg type-receipt"><i class="bi bi-receipt me-1"></i> ใบเสร็จ</span>
                            @break
                        @case('invoice')
                            <span class="type-badge-lg type-invoice"><i class="bi bi-file-text me-1"></i> ใบแจ้งหนี้</span>
                            @break
                        @case('medical_certificate')
                            <span class="type-badge-lg type-medical_certificate"><i class="bi bi-file-medical me-1"></i> ใบรับรองแพทย์</span>
                            @break
                        @case('consent_form')
                            <span class="type-badge-lg type-consent_form"><i class="bi bi-file-earmark-check me-1"></i> ใบยินยอม</span>
                            @break
                        @default
                            <span class="type-badge-lg type-other"><i class="bi bi-file-earmark me-1"></i> อื่นๆ</span>
                    @endswitch
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">ผู้ป่วย</div>
                            <div class="info-value">
                                @if($document->patient)
                                <a href="{{ route('patients.show', $document->patient) }}">{{ $document->patient->name }}</a>
                                @else
                                -
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">สาขา</div>
                            <div class="info-value">{{ $document->branch->name ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">ใบแจ้งหนี้อ้างอิง</div>
                            <div class="info-value">
                                @if($document->invoice)
                                <a href="{{ route('invoices.show', $document->invoice) }}">{{ $document->invoice->invoice_number }}</a>
                                @else
                                -
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">การชำระเงิน</div>
                            <div class="info-value">
                                @if($document->payment)
                                <a href="{{ route('payments.show', $document->payment) }}">{{ $document->payment->payment_number }}</a>
                                @else
                                -
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if($document->notes)
                <div class="mt-4">
                    <h6 class="fw-semibold">หมายเหตุ</h6>
                    <p class="mb-0">{{ $document->notes }}</p>
                </div>
                @endif
            </div>

            <!-- File Preview -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-paperclip me-2"></i>ไฟล์แนบ</div>

                @if($document->file_path)
                <div class="file-preview">
                    @php
                        $extension = pathinfo($document->file_name, PATHINFO_EXTENSION);
                    @endphp

                    @if(in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']))
                        <img src="{{ Storage::url($document->file_path) }}" alt="{{ $document->file_name }}" class="img-fluid rounded mb-3" style="max-height: 400px;">
                    @else
                        @switch(strtolower($extension))
                            @case('pdf')
                                <i class="bi bi-file-earmark-pdf file-icon-large text-danger"></i>
                                @break
                            @case('doc')
                            @case('docx')
                                <i class="bi bi-file-earmark-word file-icon-large text-primary"></i>
                                @break
                            @case('xls')
                            @case('xlsx')
                                <i class="bi bi-file-earmark-excel file-icon-large text-success"></i>
                                @break
                            @default
                                <i class="bi bi-file-earmark file-icon-large"></i>
                        @endswitch
                    @endif

                    <div class="mt-3">
                        <h6>{{ $document->file_name }}</h6>
                        <small class="text-muted">
                            @if($document->file_size)
                            {{ number_format($document->file_size / 1024, 2) }} KB
                            @endif
                        </small>
                    </div>

                    <a href="{{ route('documents.download', $document) }}" class="btn btn-primary mt-3">
                        <i class="bi bi-download me-1"></i> ดาวน์โหลด
                    </a>
                </div>
                @else
                <div class="file-preview">
                    <i class="bi bi-file-earmark-x file-icon-large text-muted"></i>
                    <p class="text-muted mt-3 mb-0">ไม่มีไฟล์แนบ</p>
                </div>
                @endif
            </div>

            <!-- Related Documents -->
            @if($relatedDocuments->count() > 0)
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-files me-2"></i>เอกสารอื่นของผู้ป่วยนี้</div>
                @foreach($relatedDocuments as $relDoc)
                <a href="{{ route('documents.show', $relDoc) }}" class="related-doc-item d-block text-decoration-none">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-bold text-dark">{{ $relDoc->document_number }}</span>
                            <br>
                            <small class="text-muted">
                                {{ $relDoc->document_date?->format('d/m/Y') }} -
                                @switch($relDoc->document_type)
                                    @case('receipt') ใบเสร็จ @break
                                    @case('invoice') ใบแจ้งหนี้ @break
                                    @case('medical_certificate') ใบรับรองแพทย์ @break
                                    @case('consent_form') ใบยินยอม @break
                                    @default อื่นๆ
                                @endswitch
                            </small>
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

                @if($document->status == 'active')
                <div class="status-display active">
                    <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                    <h5 class="mb-1">ใช้งาน</h5>
                </div>
                @elseif($document->status == 'archived')
                <div class="status-display archived">
                    <i class="bi bi-archive fs-1 d-block mb-2 text-secondary"></i>
                    <h5 class="mb-1">เก็บถาวร</h5>
                </div>
                @else
                <div class="status-display cancelled">
                    <i class="bi bi-x-circle fs-1 d-block mb-2 text-danger"></i>
                    <h5 class="mb-1">ยกเลิก</h5>
                </div>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-lightning me-2"></i>การดำเนินการ</div>

                <div class="d-grid gap-2">
                    @if($document->file_path)
                    <a href="{{ route('documents.download', $document) }}" class="btn btn-outline-primary">
                        <i class="bi bi-download me-1"></i> ดาวน์โหลดไฟล์
                    </a>
                    @endif
                    <a href="{{ route('documents.edit', $document) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-pencil me-1"></i> แก้ไขข้อมูล
                    </a>
                </div>
            </div>

            <!-- Timestamps -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-clock-history me-2"></i>ประวัติ</div>

                <div class="info-item">
                    <div class="info-label">สร้างเมื่อ</div>
                    <div class="info-value">{{ $document->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">อัปเดตล่าสุด</div>
                    <div class="info-value">{{ $document->updated_at?->format('d/m/Y H:i') ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
