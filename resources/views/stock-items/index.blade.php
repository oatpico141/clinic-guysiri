@extends('layouts.app')

@section('title', 'วัสดุสิ้นเปลือง - GCMS')

@push('styles')
<style>
    .stock-header {
        background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
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

    .stat-icon.total { background: #cffafe; color: #0e7490; }
    .stat-icon.active { background: #d1fae5; color: #059669; }
    .stat-icon.low { background: #fee2e2; color: #dc2626; }
    .stat-icon.value { background: #fef3c7; color: #d97706; }

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

    .item-code {
        font-family: 'Monaco', 'Consolas', monospace;
        color: #0891b2;
        font-weight: 600;
    }

    .quantity-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-weight: 600;
    }

    .quantity-ok { background: #d1fae5; color: #065f46; }
    .quantity-low { background: #fee2e2; color: #991b1b; }

    .status-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 10px;
        font-size: 0.7rem;
    }

    .status-active { background: #d1fae5; color: #065f46; }
    .status-inactive { background: #e5e7eb; color: #374151; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="stock-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-2"><i class="bi bi-box-seam me-2"></i>วัสดุสิ้นเปลือง</h2>
                <p class="mb-0 opacity-90">จัดการวัสดุสิ้นเปลืองและสต็อก</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="{{ route('stock-items.create') }}" class="btn btn-light">
                    <i class="bi bi-plus-lg me-1"></i> เพิ่มสินค้า
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
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">{{ number_format($totalItems) }}</div>
                        <div class="text-muted small">สินค้าทั้งหมด</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon active me-3">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-success">{{ number_format($activeCount) }}</div>
                        <div class="text-muted small">ใช้งานอยู่</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon low me-3">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-danger">{{ number_format($lowStockCount) }}</div>
                        <div class="text-muted small">สต็อกต่ำ</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon value me-3">
                        <i class="bi bi-currency-exchange"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-warning">฿{{ number_format($totalValue, 0) }}</div>
                        <div class="text-muted small">มูลค่ารวม</div>
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
        <form method="GET" action="{{ route('stock-items.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="ค้นหารหัส/ชื่อ/ผู้จำหน่าย..." value="{{ request('search') }}">
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
                    <select name="category" class="form-select">
                        <option value="">ทุกหมวด</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">ทุกสถานะ</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>ใช้งาน</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>ไม่ใช้งาน</option>
                        <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>สต็อกต่ำ</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search me-1"></i> ค้นหา
                    </button>
                    <a href="{{ route('stock-items.index') }}" class="btn btn-outline-secondary">รีเซ็ต</a>
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
                        <th>รหัส</th>
                        <th>ชื่อสินค้า</th>
                        <th>หมวด</th>
                        <th class="text-center">คงเหลือ</th>
                        <th class="text-end">ต้นทุน</th>
                        <th>สาขา</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockItems as $item)
                    <tr>
                        <td><span class="item-code">{{ $item->item_code }}</span></td>
                        <td>
                            <strong>{{ $item->name }}</strong>
                            @if($item->supplier)
                            <br><small class="text-muted">{{ $item->supplier }}</small>
                            @endif
                        </td>
                        <td>{{ $item->category ?? '-' }}</td>
                        <td class="text-center">
                            @if($item->isLowStock())
                            <span class="quantity-badge quantity-low">
                                {{ number_format($item->quantity_on_hand) }} {{ $item->unit }}
                            </span>
                            @else
                            <span class="quantity-badge quantity-ok">
                                {{ number_format($item->quantity_on_hand) }} {{ $item->unit }}
                            </span>
                            @endif
                        </td>
                        <td class="text-end">฿{{ number_format($item->unit_cost, 2) }}</td>
                        <td>{{ $item->branch->name ?? '-' }}</td>
                        <td class="text-center">
                            @if($item->is_active)
                                <span class="status-badge status-active">ใช้งาน</span>
                            @else
                                <span class="status-badge status-inactive">ไม่ใช้งาน</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('stock-items.show', $item) }}" class="btn btn-sm btn-outline-info" title="ดูรายละเอียด">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('stock-items.edit', $item) }}" class="btn btn-sm btn-outline-secondary" title="แก้ไข">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="showAdjustModal('{{ $item->id }}', '{{ $item->name }}', {{ $item->quantity_on_hand }})" title="ปรับจำนวน">
                                <i class="bi bi-plus-slash-minus"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            ไม่พบสินค้า
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($stockItems->hasPages())
        <div class="p-3 border-top">
            {{ $stockItems->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Adjust Modal -->
<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ปรับจำนวนสต็อก</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="adjustItemId">
                <p class="mb-3"><strong id="adjustItemName"></strong></p>
                <p class="text-muted mb-3">คงเหลือปัจจุบัน: <span id="adjustCurrentQty" class="fw-bold"></span></p>

                <div class="mb-3">
                    <label class="form-label">ประเภท</label>
                    <select id="adjustType" class="form-select">
                        <option value="in">รับเข้า (+)</option>
                        <option value="out">เบิกออก (-)</option>
                        <option value="adjust">ปรับยอด (กำหนดจำนวนใหม่)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">จำนวน</label>
                    <input type="number" id="adjustQty" class="form-control" min="1" value="1">
                </div>
                <div class="mb-3">
                    <label class="form-label">เหตุผล <span class="text-danger">*</span></label>
                    <textarea id="adjustReason" class="form-control" rows="2" placeholder="ระบุเหตุผล..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" onclick="submitAdjust()">บันทึก</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showAdjustModal(id, name, currentQty) {
    document.getElementById('adjustItemId').value = id;
    document.getElementById('adjustItemName').textContent = name;
    document.getElementById('adjustCurrentQty').textContent = currentQty;
    document.getElementById('adjustType').value = 'in';
    document.getElementById('adjustQty').value = 1;
    document.getElementById('adjustReason').value = '';
    new bootstrap.Modal(document.getElementById('adjustModal')).show();
}

function submitAdjust() {
    const id = document.getElementById('adjustItemId').value;
    const type = document.getElementById('adjustType').value;
    const quantity = document.getElementById('adjustQty').value;
    const reason = document.getElementById('adjustReason').value;

    if (!reason.trim()) {
        alert('กรุณาระบุเหตุผล');
        return;
    }

    fetch(`/stock-items/${id}/adjust`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            adjustment_type: type,
            quantity: parseInt(quantity),
            reason: reason
        })
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
</script>
@endpush
