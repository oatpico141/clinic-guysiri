@extends('layouts.app')

@section('title', 'คอมมิชชั่น - GCMS')

@push('styles')
<style>
    .commission-header {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
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
    .stat-icon.clawback { background: #fee2e2; color: #dc2626; }

    .filter-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        margin-bottom: 1.5rem;
    }

    .commission-table-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .commission-table thead th {
        background: #f8fafc;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        padding: 1rem;
        border: none;
    }

    .commission-table tbody td {
        padding: 1rem;
        vertical-align: middle;
    }

    .commission-table tbody tr:hover {
        background: #f8fafc;
    }

    .badge-pending { background: #fef3c7; color: #92400e; }
    .badge-approved { background: #dbeafe; color: #1e40af; }
    .badge-paid { background: #d1fae5; color: #065f46; }
    .badge-clawed_back { background: #fee2e2; color: #991b1b; }

    .type-badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
    }

    .type-service { background: #e0f2fe; color: #0369a1; }
    .type-package_sale { background: #fce7f3; color: #be185d; }
    .type-package_usage { background: #f3e8ff; color: #7c3aed; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="commission-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-2"><i class="bi bi-cash-coin me-2"></i>คอมมิชชั่น</h2>
                <p class="mb-0 opacity-90">จัดการค่าคอมมิชชั่นของ PT</p>
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
        <div class="col-md-3">
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
        <div class="col-md-3">
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
        <div class="col-md-3">
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
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon clawback me-3">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-danger">฿{{ number_format($totalClawedBack, 0) }}</div>
                        <div class="text-muted small">Clawback</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('commissions.index') }}">
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
                        <option value="clawed_back" {{ request('status') == 'clawed_back' ? 'selected' : '' }}>Clawback</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">ประเภท</label>
                    <select name="commission_type" class="form-select">
                        <option value="">ทั้งหมด</option>
                        <option value="service" {{ request('commission_type') == 'service' ? 'selected' : '' }}>บริการ</option>
                        <option value="package_sale" {{ request('commission_type') == 'package_sale' ? 'selected' : '' }}>ขายแพ็คเกจ</option>
                        <option value="package_usage" {{ request('commission_type') == 'package_usage' ? 'selected' : '' }}>ใช้แพ็คเกจ</option>
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
                    <a href="{{ route('commissions.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Commission Table -->
    <div class="commission-table-card">
        <div class="table-responsive">
            <table class="table commission-table mb-0">
                <thead>
                    <tr>
                        <th width="40">
                            <input type="checkbox" class="form-check-input" id="selectAll">
                        </th>
                        <th>เลขที่</th>
                        <th>PT</th>
                        <th>ลูกค้า</th>
                        <th>ประเภท</th>
                        <th class="text-end">ยอดบริการ</th>
                        <th class="text-center">อัตรา</th>
                        <th class="text-end">คอมมิชชั่น</th>
                        <th class="text-center">วันที่</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commissions as $commission)
                    <tr>
                        <td>
                            @if(in_array($commission->status, ['pending', 'approved']))
                            <input type="checkbox" class="form-check-input commission-checkbox" value="{{ $commission->id }}">
                            @endif
                        </td>
                        <td><code>{{ $commission->commission_number ?? '-' }}</code></td>
                        <td>
                            <strong>{{ $commission->pt->name ?? '-' }}</strong>
                        </td>
                        <td>
                            @if($commission->invoice && $commission->invoice->patient)
                                {{ $commission->invoice->patient->name }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @switch($commission->commission_type)
                                @case('service')
                                    <span class="type-badge type-service">บริการ</span>
                                    @break
                                @case('package_sale')
                                    <span class="type-badge type-package_sale">ขายแพ็คเกจ</span>
                                    @break
                                @case('package_usage')
                                    <span class="type-badge type-package_usage">ใช้แพ็คเกจ</span>
                                    @break
                                @default
                                    <span class="type-badge">{{ $commission->commission_type }}</span>
                            @endswitch
                        </td>
                        <td class="text-end">฿{{ number_format($commission->base_amount, 0) }}</td>
                        <td class="text-center">{{ number_format($commission->commission_rate, 0) }}%</td>
                        <td class="text-end fw-bold">฿{{ number_format($commission->commission_amount, 0) }}</td>
                        <td class="text-center">{{ $commission->commission_date?->format('d/m/Y') ?? '-' }}</td>
                        <td class="text-center">
                            @switch($commission->status)
                                @case('pending')
                                    <span class="badge badge-pending">รอดำเนินการ</span>
                                    @break
                                @case('approved')
                                    <span class="badge badge-approved">อนุมัติแล้ว</span>
                                    @break
                                @case('paid')
                                    <span class="badge badge-paid">จ่ายแล้ว</span>
                                    @break
                                @case('clawed_back')
                                    <span class="badge badge-clawed_back">Clawback</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ $commission->status }}</span>
                            @endswitch
                        </td>
                        <td class="text-center">
                            <a href="{{ route('commissions.show', $commission) }}" class="btn btn-sm btn-outline-info" title="ดูรายละเอียด">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if(in_array($commission->status, ['pending', 'approved']))
                            <button type="button" class="btn btn-sm btn-outline-success pay-btn" data-id="{{ $commission->id }}" title="จ่าย">
                                <i class="bi bi-cash"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            ไม่พบข้อมูลคอมมิชชั่น
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($commissions->hasPages())
        <div class="p-3 border-top">
            {{ $commissions->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Pay Modal -->
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cash-stack me-2"></i>จ่ายคอมมิชชั่น</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
    const checkboxes = document.querySelectorAll('.commission-checkbox');
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
            document.getElementById('paymentSummary').innerHTML = '<p>จ่ายคอมมิชชั่น 1 รายการ</p>';
            payModal.show();
        });
    });

    // Bulk pay button
    bulkPayBtn?.addEventListener('click', function() {
        document.getElementById('paymentSummary').innerHTML = `<p>จ่ายคอมมิชชั่น ${selectedIds.length} รายการ</p>`;
        payModal.show();
    });

    // Confirm pay
    document.getElementById('confirmPayBtn')?.addEventListener('click', function() {
        const reference = document.getElementById('paymentReference').value;

        fetch('{{ route("commissions.bulkPay") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                commission_ids: selectedIds,
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
