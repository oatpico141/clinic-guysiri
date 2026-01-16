@extends('layouts.app')

@section('title', 'ค่ามือ PT (DF) - GCMS')

@push('styles')
<style>
    .df-header {
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

    .stat-icon.pending { background: #fef3c7; color: #d97706; }
    .stat-icon.approved { background: #dbeafe; color: #2563eb; }
    .stat-icon.paid { background: #d1fae5; color: #059669; }

    .filter-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        margin-bottom: 1.5rem;
    }

    .df-table-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .df-table thead th {
        background: #f8fafc;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        padding: 1rem;
        border: none;
    }

    .df-table tbody td {
        padding: 1rem;
        vertical-align: middle;
    }

    .df-table tbody tr:hover {
        background: #f8fafc;
    }

    .badge-pending { background: #fef3c7; color: #92400e; }
    .badge-approved { background: #dbeafe; color: #1e40af; }
    .badge-paid { background: #d1fae5; color: #065f46; }

    .source-badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
    }

    .source-course_usage { background: #f3e8ff; color: #7c3aed; }
    .source-per_session { background: #e0f2fe; color: #0369a1; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="df-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-2"><i class="bi bi-hand-index-thumb me-2"></i>ค่ามือ PT (DF)</h2>
                <p class="mb-0 opacity-90">จัดการค่ามือของนักกายภาพบำบัด</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <button type="button" class="btn btn-light" id="bulkPayBtn" disabled>
                    <i class="bi bi-cash-stack me-1"></i> จ่ายที่เลือก
                </button>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon pending me-3">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-warning">฿{{ number_format($totalPending, 0) }}</div>
                        <div class="text-muted small">รอดำเนินการ ({{ $pendingCount }})</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon approved me-3">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-primary">฿{{ number_format($totalApproved, 0) }}</div>
                        <div class="text-muted small">อนุมัติแล้ว ({{ $approvedCount }})</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon paid me-3">
                        <i class="bi bi-cash"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-success">฿{{ number_format($totalPaid, 0) }}</div>
                        <div class="text-muted small">จ่ายแล้ว</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('df-payments.index') }}">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label small">PT</label>
                    <select name="pt_id" class="form-select">
                        <option value="">ทั้งหมด</option>
                        @foreach($pts as $pt)
                            <option value="{{ $pt->id }}" {{ request('pt_id') == $pt->id ? 'selected' : '' }}>
                                {{ $pt->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">สถานะ</label>
                    <select name="status" class="form-select">
                        <option value="">ทั้งหมด</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>รอดำเนินการ</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>อนุมัติแล้ว</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>จ่ายแล้ว</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">ประเภท</label>
                    <select name="source_type" class="form-select">
                        <option value="">ทั้งหมด</option>
                        <option value="course_usage" {{ request('source_type') == 'course_usage' ? 'selected' : '' }}>ใช้คอร์ส</option>
                        <option value="per_session" {{ request('source_type') == 'per_session' ? 'selected' : '' }}>รายครั้ง</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">จากวันที่</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">ถึงวันที่</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i>
                    </button>
                    <a href="{{ route('df-payments.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- DF Table -->
    <div class="df-table-card">
        <div class="table-responsive">
            <table class="table df-table mb-0">
                <thead>
                    <tr>
                        <th width="40">
                            <input type="checkbox" class="form-check-input" id="selectAll">
                        </th>
                        <th>เลขที่</th>
                        <th>PT</th>
                        <th>ลูกค้า</th>
                        <th>บริการ</th>
                        <th>ประเภท</th>
                        <th class="text-end">ยอดบริการ</th>
                        <th class="text-center">อัตรา</th>
                        <th class="text-end">ค่ามือ</th>
                        <th class="text-center">วันที่</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dfPayments as $df)
                    <tr>
                        <td>
                            @if(in_array($df->status, ['pending', 'approved']))
                            <input type="checkbox" class="form-check-input df-checkbox" value="{{ $df->id }}">
                            @endif
                        </td>
                        <td><code>{{ $df->df_number ?? '-' }}</code></td>
                        <td>
                            <strong>{{ $df->pt->name ?? '-' }}</strong>
                        </td>
                        <td>
                            @if($df->treatment && $df->treatment->patient)
                                {{ $df->treatment->patient->name }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($df->service)
                                {{ $df->service->name }}
                            @elseif($df->treatment && $df->treatment->service)
                                {{ $df->treatment->service->name }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @switch($df->source_type)
                                @case('course_usage')
                                    <span class="source-badge source-course_usage">ใช้คอร์ส</span>
                                    @break
                                @case('per_session')
                                    <span class="source-badge source-per_session">รายครั้ง</span>
                                    @break
                                @default
                                    <span class="source-badge">{{ $df->source_type ?? '-' }}</span>
                            @endswitch
                        </td>
                        <td class="text-end">฿{{ number_format($df->base_amount, 0) }}</td>
                        <td class="text-center">{{ number_format($df->df_rate, 0) }}%</td>
                        <td class="text-end fw-bold text-success">฿{{ number_format($df->df_amount, 0) }}</td>
                        <td class="text-center">{{ $df->df_date?->format('d/m/Y') ?? '-' }}</td>
                        <td class="text-center">
                            @switch($df->status)
                                @case('pending')
                                    <span class="badge badge-pending">รอดำเนินการ</span>
                                    @break
                                @case('approved')
                                    <span class="badge badge-approved">อนุมัติแล้ว</span>
                                    @break
                                @case('paid')
                                    <span class="badge badge-paid">จ่ายแล้ว</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ $df->status }}</span>
                            @endswitch
                        </td>
                        <td class="text-center">
                            <a href="{{ route('df-payments.show', $df) }}" class="btn btn-sm btn-outline-info" title="ดูรายละเอียด">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if(in_array($df->status, ['pending', 'approved']))
                            <button type="button" class="btn btn-sm btn-outline-success pay-btn" data-id="{{ $df->id }}" title="จ่าย">
                                <i class="bi bi-cash"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            ไม่พบข้อมูลค่ามือ
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($dfPayments->hasPages())
        <div class="p-3 border-top">
            {{ $dfPayments->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Pay Modal -->
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-cash-stack me-2"></i>จ่ายค่ามือ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">เลขอ้างอิงการจ่าย</label>
                    <input type="text" class="form-control" id="paymentReference" placeholder="เช่น เลขที่โอน, เลขเช็ค">
                </div>
                <div id="paymentSummary"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-success" id="confirmPayBtn">
                    <i class="bi bi-check-lg me-1"></i> ยืนยันการจ่าย
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.df-checkbox');
    const bulkPayBtn = document.getElementById('bulkPayBtn');
    const payModal = new bootstrap.Modal(document.getElementById('payModal'));
    let selectedIds = [];

    // Select all
    selectAll?.addEventListener('change', function() {
        checkboxes.forEach(cb => {
            cb.checked = this.checked;
        });
        updateBulkBtn();
    });

    // Individual checkbox
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkBtn);
    });

    function updateBulkBtn() {
        selectedIds = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
        bulkPayBtn.disabled = selectedIds.length === 0;
    }

    // Single pay button
    document.querySelectorAll('.pay-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            selectedIds = [this.dataset.id];
            document.getElementById('paymentSummary').innerHTML = '<p>จ่ายค่ามือ 1 รายการ</p>';
            payModal.show();
        });
    });

    // Bulk pay button
    bulkPayBtn?.addEventListener('click', function() {
        document.getElementById('paymentSummary').innerHTML = `<p>จ่ายค่ามือ ${selectedIds.length} รายการ</p>`;
        payModal.show();
    });

    // Confirm pay
    document.getElementById('confirmPayBtn')?.addEventListener('click', function() {
        const reference = document.getElementById('paymentReference').value;

        fetch('{{ route("df-payments.bulkPay") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                df_payment_ids: selectedIds,
                payment_reference: reference
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                payModal.hide();
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(err => {
            alert('เกิดข้อผิดพลาด');
            console.error(err);
        });
    });
});
</script>
@endpush
