@extends('layouts.app')

@section('title', 'เอกสาร - GCMS')

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
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
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .filter-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        margin-bottom: 1.5rem;
    }

    .document-table {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .document-table thead th {
        background: #f8fafc;
        font-weight: 600;
        font-size: 0.85rem;
        color: #64748b;
        border-bottom: 2px solid #e2e8f0;
    }

    .document-table tbody tr:hover {
        background-color: #f8fafc;
    }

    .doc-number {
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .type-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .type-receipt { background: #d1fae5; color: #065f46; }
    .type-invoice { background: #dbeafe; color: #1e40af; }
    .type-medical_certificate { background: #fef3c7; color: #92400e; }
    .type-consent_form { background: #e0e7ff; color: #3730a3; }
    .type-other { background: #f1f5f9; color: #475569; }

    .status-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .status-active { background: #d1fae5; color: #065f46; }
    .status-archived { background: #f1f5f9; color: #475569; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }

    .file-icon {
        font-size: 1.25rem;
    }

    /* Mobile Cards */
    .document-card {
        display: none;
        background: white;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    @media (max-width: 768px) {
        .document-table { display: none !important; }
        .document-card { display: block; }
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
                <h2 class="mb-2"><i class="bi bi-file-earmark-text me-2"></i>เอกสาร</h2>
                <p class="mb-0 opacity-90">จัดการเอกสารทั้งหมดในระบบ</p>
            </div>
            <a href="{{ route('documents.create') }}" class="btn btn-light">
                <i class="bi bi-plus-lg me-1"></i> เพิ่มเอกสาร
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="fs-2 fw-bold text-primary">{{ number_format($totalDocuments) }}</div>
                <div class="text-muted">เอกสารทั้งหมด</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="fs-2 fw-bold text-success">{{ number_format($thisMonthDocuments) }}</div>
                <div class="text-muted">เดือนนี้</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="fs-2 fw-bold text-info">{{ number_format($receiptCount) }}</div>
                <div class="text-muted">ใบเสร็จ</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="fs-2 fw-bold text-warning">{{ number_format($invoiceCount) }}</div>
                <div class="text-muted">ใบแจ้งหนี้</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('documents.index') }}">
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
                    <select name="document_type" class="form-select">
                        <option value="">ทุกประเภท</option>
                        <option value="receipt" {{ request('document_type') == 'receipt' ? 'selected' : '' }}>ใบเสร็จ</option>
                        <option value="invoice" {{ request('document_type') == 'invoice' ? 'selected' : '' }}>ใบแจ้งหนี้</option>
                        <option value="medical_certificate" {{ request('document_type') == 'medical_certificate' ? 'selected' : '' }}>ใบรับรองแพทย์</option>
                        <option value="consent_form" {{ request('document_type') == 'consent_form' ? 'selected' : '' }}>ใบยินยอม</option>
                        <option value="other" {{ request('document_type') == 'other' ? 'selected' : '' }}>อื่นๆ</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">ทุกสถานะ</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>ใช้งาน</option>
                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>เก็บถาวร</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ยกเลิก</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-search"></i> ค้นหา
                        </button>
                        <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Table (Desktop) -->
    <div class="document-table">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>เลขที่เอกสาร</th>
                    <th>ประเภท</th>
                    <th>ผู้ป่วย</th>
                    <th>สาขา</th>
                    <th>วันที่</th>
                    <th>ไฟล์</th>
                    <th>สถานะ</th>
                    <th class="text-end">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $document)
                <tr>
                    <td>
                        <a href="{{ route('documents.show', $document) }}" class="doc-number text-decoration-none">
                            {{ $document->document_number }}
                        </a>
                    </td>
                    <td>
                        @switch($document->document_type)
                            @case('receipt')
                                <span class="type-badge type-receipt"><i class="bi bi-receipt me-1"></i>ใบเสร็จ</span>
                                @break
                            @case('invoice')
                                <span class="type-badge type-invoice"><i class="bi bi-file-text me-1"></i>ใบแจ้งหนี้</span>
                                @break
                            @case('medical_certificate')
                                <span class="type-badge type-medical_certificate"><i class="bi bi-file-medical me-1"></i>ใบรับรองแพทย์</span>
                                @break
                            @case('consent_form')
                                <span class="type-badge type-consent_form"><i class="bi bi-file-earmark-check me-1"></i>ใบยินยอม</span>
                                @break
                            @default
                                <span class="type-badge type-other"><i class="bi bi-file-earmark me-1"></i>อื่นๆ</span>
                        @endswitch
                    </td>
                    <td>{{ $document->patient->name ?? '-' }}</td>
                    <td>{{ $document->branch->name ?? '-' }}</td>
                    <td>{{ $document->document_date?->format('d/m/Y') ?? '-' }}</td>
                    <td>
                        @if($document->file_path)
                        <a href="{{ route('documents.download', $document) }}" class="text-primary" title="{{ $document->file_name }}">
                            <i class="bi bi-file-earmark-arrow-down file-icon"></i>
                        </a>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @switch($document->status)
                            @case('active')
                                <span class="status-badge status-active">ใช้งาน</span>
                                @break
                            @case('archived')
                                <span class="status-badge status-archived">เก็บถาวร</span>
                                @break
                            @case('cancelled')
                                <span class="status-badge status-cancelled">ยกเลิก</span>
                                @break
                        @endswitch
                    </td>
                    <td class="text-end">
                        <a href="{{ route('documents.show', $document) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="bi bi-file-earmark-x fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">ไม่พบข้อมูลเอกสาร</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Cards (Mobile) -->
    @foreach($documents as $document)
    <div class="document-card">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <a href="{{ route('documents.show', $document) }}" class="doc-number text-decoration-none">
                {{ $document->document_number }}
            </a>
            @switch($document->status)
                @case('active')
                    <span class="status-badge status-active">ใช้งาน</span>
                    @break
                @case('archived')
                    <span class="status-badge status-archived">เก็บถาวร</span>
                    @break
                @case('cancelled')
                    <span class="status-badge status-cancelled">ยกเลิก</span>
                    @break
            @endswitch
        </div>
        <div class="mb-2">
            @switch($document->document_type)
                @case('receipt')
                    <span class="type-badge type-receipt"><i class="bi bi-receipt me-1"></i>ใบเสร็จ</span>
                    @break
                @case('invoice')
                    <span class="type-badge type-invoice"><i class="bi bi-file-text me-1"></i>ใบแจ้งหนี้</span>
                    @break
                @case('medical_certificate')
                    <span class="type-badge type-medical_certificate"><i class="bi bi-file-medical me-1"></i>ใบรับรองแพทย์</span>
                    @break
                @case('consent_form')
                    <span class="type-badge type-consent_form"><i class="bi bi-file-earmark-check me-1"></i>ใบยินยอม</span>
                    @break
                @default
                    <span class="type-badge type-other"><i class="bi bi-file-earmark me-1"></i>อื่นๆ</span>
            @endswitch
        </div>
        <div class="text-muted small">
            <div><i class="bi bi-person me-1"></i>{{ $document->patient->name ?? '-' }}</div>
            <div><i class="bi bi-building me-1"></i>{{ $document->branch->name ?? '-' }}</div>
            <div><i class="bi bi-calendar me-1"></i>{{ $document->document_date?->format('d/m/Y') ?? '-' }}</div>
        </div>
        <div class="mt-2 d-flex gap-2">
            <a href="{{ route('documents.show', $document) }}" class="btn btn-sm btn-outline-primary flex-grow-1">ดูรายละเอียด</a>
            @if($document->file_path)
            <a href="{{ route('documents.download', $document) }}" class="btn btn-sm btn-outline-success">
                <i class="bi bi-download"></i>
            </a>
            @endif
        </div>
    </div>
    @endforeach

    <!-- Pagination -->
    <div class="mt-4 d-flex justify-content-center">
        {{ $documents->withQueryString()->links() }}
    </div>
</div>
@endsection
