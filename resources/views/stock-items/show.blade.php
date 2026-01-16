@extends('layouts.app')

@section('title', 'รายละเอียดสินค้า - GCMS')

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
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

    .item-code-display {
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 1.25rem;
        font-weight: 700;
        color: #0891b2;
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

    .stock-display {
        background: #f8fafc;
        border-radius: 16px;
        padding: 2rem;
        text-align: center;
    }

    .stock-number {
        font-size: 3rem;
        font-weight: 700;
    }

    .stock-ok { color: #059669; }
    .stock-low { color: #dc2626; }

    .transaction-item {
        background: #f8fafc;
        border-radius: 8px;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
    }

    .trans-in { border-left: 4px solid #059669; }
    .trans-out { border-left: 4px solid #dc2626; }
    .trans-adjust { border-left: 4px solid #d97706; }

    .quick-actions {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1.5rem;
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
                    <i class="bi bi-box-seam me-2"></i>
                    {{ $stockItem->name }}
                </h2>
                <p class="mb-0 opacity-90">
                    <span class="item-code-display">{{ $stockItem->item_code }}</span>
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('stock-items.edit', $stockItem) }}" class="btn btn-light">
                    <i class="bi bi-pencil me-1"></i> แก้ไข
                </a>
                <a href="{{ route('stock-items.index') }}" class="btn btn-outline-light">
                    <i class="bi bi-arrow-left me-1"></i> กลับ
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Stock Display -->
            <div class="detail-card">
                <div class="stock-display">
                    <div class="stock-number {{ $stockItem->isLowStock() ? 'stock-low' : 'stock-ok' }}">
                        {{ number_format($stockItem->quantity_on_hand) }}
                    </div>
                    <div class="text-muted">{{ $stockItem->unit }}</div>
                    @if($stockItem->isLowStock())
                    <div class="alert alert-danger mt-3 mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        สต็อกต่ำกว่าขั้นต่ำ ({{ $stockItem->minimum_quantity }})
                    </div>
                    @endif
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions mt-4">
                    <h6 class="mb-3">ปรับจำนวน</h6>
                    <div class="row g-2">
                        <div class="col-4">
                            <button type="button" class="btn btn-success w-100" onclick="showAdjustModal('in')">
                                <i class="bi bi-plus-lg me-1"></i> รับเข้า
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-danger w-100" onclick="showAdjustModal('out')">
                                <i class="bi bi-dash-lg me-1"></i> เบิกออก
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-warning w-100" onclick="showAdjustModal('adjust')">
                                <i class="bi bi-arrow-repeat me-1"></i> ปรับยอด
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-arrow-left-right me-2"></i>ประวัติเคลื่อนไหว</div>
                @if($transactions->count() > 0)
                    @foreach($transactions as $trans)
                    <div class="transaction-item {{ $trans->transaction_type == 'in' ? 'trans-in' : ($trans->transaction_type == 'out' ? 'trans-out' : 'trans-adjust') }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold">
                                    @if($trans->transaction_type == 'in')
                                        <span class="text-success">+{{ number_format(abs($trans->quantity)) }}</span>
                                    @elseif($trans->transaction_type == 'out')
                                        <span class="text-danger">-{{ number_format(abs($trans->quantity)) }}</span>
                                    @else
                                        <span class="text-warning">ปรับยอด: {{ number_format($trans->quantity) }}</span>
                                    @endif
                                </span>
                                <br><small class="text-muted">{{ $trans->reason ?? '-' }}</small>
                            </div>
                            <div class="text-end">
                                <small class="text-muted">
                                    {{ $trans->created_at?->format('d/m/Y H:i') }}<br>
                                    {{ $trans->performedBy->name ?? '-' }}
                                </small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <p class="text-muted text-center py-3 mb-0">
                        <i class="bi bi-inbox d-block fs-3 mb-2"></i>
                        ยังไม่มีประวัติ
                    </p>
                @endif
            </div>
        </div>

        <div class="col-md-4">
            <!-- Details -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-info-circle me-2"></i>ข้อมูลสินค้า</div>

                <div class="info-item">
                    <div class="info-label">รหัสสินค้า</div>
                    <div class="info-value">{{ $stockItem->item_code }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">หมวดหมู่</div>
                    <div class="info-value">{{ $stockItem->category ?? '-' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">หน่วย</div>
                    <div class="info-value">{{ $stockItem->unit }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">สาขา</div>
                    <div class="info-value">{{ $stockItem->branch->name ?? '-' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">สถานะ</div>
                    <div class="info-value">
                        @if($stockItem->is_active)
                            <span class="badge bg-success">ใช้งาน</span>
                        @else
                            <span class="badge bg-secondary">ไม่ใช้งาน</span>
                        @endif
                    </div>
                </div>

                @if($stockItem->description)
                <div class="info-item">
                    <div class="info-label">รายละเอียด</div>
                    <div class="info-value">{{ $stockItem->description }}</div>
                </div>
                @endif
            </div>

            <!-- Stock Levels -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-sliders me-2"></i>ระดับสต็อก</div>

                <div class="info-item">
                    <div class="info-label">จำนวนขั้นต่ำ</div>
                    <div class="info-value">{{ number_format($stockItem->minimum_quantity ?? 0) }} {{ $stockItem->unit }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">จำนวนสูงสุด</div>
                    <div class="info-value">{{ $stockItem->maximum_quantity ? number_format($stockItem->maximum_quantity) . ' ' . $stockItem->unit : '-' }}</div>
                </div>
            </div>

            <!-- Pricing -->
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-currency-exchange me-2"></i>ราคา</div>

                <div class="info-item">
                    <div class="info-label">ราคาต้นทุน</div>
                    <div class="info-value">฿{{ number_format($stockItem->unit_cost ?? 0, 2) }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">ราคาขาย</div>
                    <div class="info-value">฿{{ number_format($stockItem->unit_price ?? 0, 2) }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">มูลค่าคงเหลือ</div>
                    <div class="info-value fw-bold text-primary">
                        ฿{{ number_format(($stockItem->quantity_on_hand ?? 0) * ($stockItem->unit_cost ?? 0), 2) }}
                    </div>
                </div>
            </div>

            <!-- Supplier -->
            @if($stockItem->supplier)
            <div class="detail-card">
                <div class="section-title"><i class="bi bi-truck me-2"></i>ผู้จำหน่าย</div>

                <div class="info-item">
                    <div class="info-label">ชื่อผู้จำหน่าย</div>
                    <div class="info-value">{{ $stockItem->supplier }}</div>
                </div>

                @if($stockItem->supplier_item_code)
                <div class="info-item">
                    <div class="info-label">รหัสสินค้าผู้จำหน่าย</div>
                    <div class="info-value">{{ $stockItem->supplier_item_code }}</div>
                </div>
                @endif
            </div>
            @endif
        </div>
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
                <p class="mb-3"><strong>{{ $stockItem->name }}</strong></p>
                <p class="text-muted mb-3">คงเหลือปัจจุบัน: <span class="fw-bold">{{ number_format($stockItem->quantity_on_hand) }}</span> {{ $stockItem->unit }}</p>

                <div class="mb-3">
                    <label class="form-label">ประเภท</label>
                    <select id="adjustType" class="form-select">
                        <option value="in">รับเข้า (+)</option>
                        <option value="out">เบิกออก (-)</option>
                        <option value="adjust">ปรับยอด</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">จำนวน</label>
                    <input type="number" id="adjustQty" class="form-control" min="1" value="1">
                </div>
                <div class="mb-3">
                    <label class="form-label">เหตุผล <span class="text-danger">*</span></label>
                    <textarea id="adjustReason" class="form-control" rows="2"></textarea>
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
function showAdjustModal(type) {
    document.getElementById('adjustType').value = type;
    document.getElementById('adjustQty').value = 1;
    document.getElementById('adjustReason').value = '';
    new bootstrap.Modal(document.getElementById('adjustModal')).show();
}

function submitAdjust() {
    const type = document.getElementById('adjustType').value;
    const quantity = document.getElementById('adjustQty').value;
    const reason = document.getElementById('adjustReason').value;

    if (!reason.trim()) {
        alert('กรุณาระบุเหตุผล');
        return;
    }

    fetch('{{ route("stock-items.show", $stockItem) }}/adjust', {
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
