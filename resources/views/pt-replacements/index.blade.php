@extends('layouts.app')

@section('title', 'การแทน PT - GCMS')

@push('styles')
<style>
    .pt-header {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
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

    .stat-icon.total { background: #ffedd5; color: #ea580c; }
    .stat-icon.month { background: #dbeafe; color: #2563eb; }

    .filter-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        margin-bottom: 1.5rem;
    }

    .table-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .data-table thead th {
        background: #f8fafc;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        padding: 1rem;
        border: none;
    }

    .data-table tbody td {
        padding: 1rem;
        vertical-align: middle;
    }

    .data-table tbody tr:hover {
        background: #f8fafc;
    }

    .pt-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        background: #f1f5f9;
    }

    .pt-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.75rem;
    }

    .arrow-icon {
        color: #9ca3af;
        font-size: 1.25rem;
    }

    .commission-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .commission-original { background: #dbeafe; color: #1e40af; }
    .commission-replacement { background: #d1fae5; color: #065f46; }
    .commission-split { background: #fef3c7; color: #92400e; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="pt-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-2"><i class="bi bi-arrow-left-right me-2"></i>การแทน PT</h2>
                <p class="mb-0 opacity-90">บันทึกและติดตามการแทน PT ในการให้บริการ</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="{{ route('pt-replacements.create') }}" class="btn btn-light">
                    <i class="bi bi-plus-lg me-1"></i> บันทึกการแทน
                </a>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon total me-3">
                        <i class="bi bi-arrow-left-right"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">{{ number_format($totalReplacements) }}</div>
                        <div class="text-muted small">การแทนทั้งหมด</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon month me-3">
                        <i class="bi bi-calendar-month"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-primary">{{ number_format($thisMonthCount) }}</div>
                        <div class="text-muted small">เดือนนี้</div>
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
        <form method="GET" action="{{ route('pt-replacements.index') }}">
            <div class="row g-3">
                <div class="col-md-2">
                    <select name="original_pt_id" class="form-select">
                        <option value="">PT ที่ถูกแทน</option>
                        @foreach($pts as $pt)
                        <option value="{{ $pt->id }}" {{ request('original_pt_id') == $pt->id ? 'selected' : '' }}>
                            {{ $pt->first_name }} {{ $pt->last_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="replacement_pt_id" class="form-select">
                        <option value="">PT ที่แทน</option>
                        @foreach($pts as $pt)
                        <option value="{{ $pt->id }}" {{ request('replacement_pt_id') == $pt->id ? 'selected' : '' }}>
                            {{ $pt->first_name }} {{ $pt->last_name }}
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
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" placeholder="จากวันที่">
                </div>
                <div class="col-md-2">
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" placeholder="ถึงวันที่">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search me-1"></i> ค้นหา
                    </button>
                    <a href="{{ route('pt-replacements.index') }}" class="btn btn-outline-secondary">รีเซ็ต</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="table data-table mb-0">
                <thead>
                    <tr>
                        <th>วันที่</th>
                        <th>PT ที่ถูกแทน</th>
                        <th></th>
                        <th>PT ที่แทน</th>
                        <th>สาขา</th>
                        <th>เหตุผล</th>
                        <th class="text-center">ค่าคอมมิชชัน</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($replacements as $replacement)
                    <tr>
                        <td>{{ $replacement->replacement_date ? \Carbon\Carbon::parse($replacement->replacement_date)->format('d/m/Y') : '-' }}</td>
                        <td>
                            <div class="pt-badge">
                                <div class="pt-avatar">
                                    {{ $replacement->originalPt ? substr($replacement->originalPt->first_name, 0, 1) : '?' }}
                                </div>
                                <span>{{ $replacement->originalPt->first_name ?? '-' }} {{ $replacement->originalPt->last_name ?? '' }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <i class="bi bi-arrow-right arrow-icon"></i>
                        </td>
                        <td>
                            <div class="pt-badge">
                                <div class="pt-avatar" style="background: linear-gradient(135deg, #10b981, #059669);">
                                    {{ $replacement->replacementPt ? substr($replacement->replacementPt->first_name, 0, 1) : '?' }}
                                </div>
                                <span>{{ $replacement->replacementPt->first_name ?? '-' }} {{ $replacement->replacementPt->last_name ?? '' }}</span>
                            </div>
                        </td>
                        <td>{{ $replacement->branch->name ?? '-' }}</td>
                        <td>
                            <span class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $replacement->reason }}">
                                {{ $replacement->reason ?? '-' }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($replacement->commission_handling == 'original')
                                <span class="commission-badge commission-original">PT เดิม</span>
                            @elseif($replacement->commission_handling == 'replacement')
                                <span class="commission-badge commission-replacement">PT แทน</span>
                            @elseif($replacement->commission_handling == 'split')
                                <span class="commission-badge commission-split">แบ่ง {{ $replacement->commission_split_percentage ?? 50 }}%</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('pt-replacements.show', $replacement) }}" class="btn btn-sm btn-outline-info" title="ดูรายละเอียด">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('pt-replacements.edit', $replacement) }}" class="btn btn-sm btn-outline-secondary" title="แก้ไข">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ $replacement->id }}')" title="ลบ">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            ไม่พบการแทน PT
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($replacements->hasPages())
        <div class="p-3 border-top">
            {{ $replacements->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ยืนยันการลบ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>คุณต้องการลบรายการแทน PT นี้หรือไม่?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">ลบ</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let deleteId = null;

function confirmDelete(id) {
    deleteId = id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteId) {
        fetch(`/pt-replacements/${deleteId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'เกิดข้อผิดพลาด');
            }
        })
        .catch(error => {
            alert('เกิดข้อผิดพลาด');
        });
    }
});
</script>
@endpush
