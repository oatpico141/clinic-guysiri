@extends('layouts.app')

@section('title', 'รายละเอียดการชำระเงิน - GCMS')

@push('styles')
<style>
    .payment-header {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        border-radius: 20px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }

    .detail-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        margin-bottom: 1.5rem;
    }

    .detail-card .card-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 1rem 1.5rem;
        border-radius: 16px 16px 0 0;
    }

    .detail-card .card-body {
        padding: 1.5rem;
    }

    .info-label {
        font-size: 0.8rem;
        color: #64748b;
        margin-bottom: 0.25rem;
    }

    .info-value {
        font-weight: 500;
        color: #1e293b;
    }

    .badge-completed { background: #d1fae5; color: #065f46; }
    .badge-pending { background: #fef3c7; color: #92400e; }
    .badge-cancelled { background: #fee2e2; color: #991b1b; }

    .amount-box {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        border-radius: 12px;
        padding: 1.5rem;
        color: white;
        text-align: center;
    }

    .amount-box .amount {
        font-size: 2rem;
        font-weight: 700;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="payment-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <a href="{{ route('payments.index') }}" class="btn btn-sm btn-light mb-3">
                    <i class="bi bi-arrow-left me-1"></i> กลับ
                </a>
                <h2 class="mb-2"><i class="bi bi-credit-card me-2"></i>{{ $payment->payment_number ?? 'การชำระเงิน' }}</h2>
                <p class="mb-0 opacity-90">
                    ลูกค้า: {{ $payment->patient->name ?? '-' }} |
                    วันที่: {{ $payment->payment_date?->format('d/m/Y') ?? '-' }}
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                @if($payment->status === 'completed')
                <button type="button" class="btn btn-light" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> พิมพ์
                </button>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Payment Details -->
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>รายละเอียดการชำระเงิน</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-label">เลขที่การชำระ</div>
                            <div class="info-value"><code>{{ $payment->payment_number ?? '-' }}</code></div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">สถานะ</div>
                            <div class="info-value">
                                @switch($payment->status)
                                    @case('completed')
                                        <span class="badge badge-completed">สำเร็จ</span>
                                        @break
                                    @case('pending')
                                        <span class="badge badge-pending">รอดำเนินการ</span>
                                        @break
                                    @case('cancelled')
                                        <span class="badge badge-cancelled">ยกเลิก</span>
                                        @break
                                @endswitch
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">ช่องทางชำระเงิน</div>
                            <div class="info-value">
                                @switch($payment->payment_method)
                                    @case('cash')
                                        <i class="bi bi-cash text-success me-1"></i> เงินสด
                                        @break
                                    @case('transfer')
                                        <i class="bi bi-bank text-primary me-1"></i> โอนเงิน
                                        @break
                                    @case('credit_card')
                                        <i class="bi bi-credit-card text-danger me-1"></i> บัตรเครดิต
                                        @if($payment->card_type) ({{ strtoupper($payment->card_type) }}) @endif
                                        @if($payment->card_last_4) **** {{ $payment->card_last_4 }} @endif
                                        @break
                                    @case('debit_card')
                                        <i class="bi bi-credit-card-2-front text-info me-1"></i> บัตรเดบิต
                                        @break
                                    @case('qr_code')
                                        <i class="bi bi-qr-code me-1" style="color: #7c3aed;"></i> QR Code
                                        @break
                                    @case('installment')
                                        <i class="bi bi-calendar3 text-warning me-1"></i> ผ่อนชำระ
                                        @if($payment->installment_number && $payment->total_installments)
                                            (งวดที่ {{ $payment->installment_number }}/{{ $payment->total_installments }})
                                        @endif
                                        @break
                                    @default
                                        {{ $payment->payment_method }}
                                @endswitch
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">วันที่ชำระ</div>
                            <div class="info-value">{{ $payment->payment_date?->format('d/m/Y') ?? '-' }}</div>
                        </div>
                        @if($payment->reference_number)
                        <div class="col-md-6">
                            <div class="info-label">เลขอ้างอิง</div>
                            <div class="info-value">{{ $payment->reference_number }}</div>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <div class="info-label">สาขา</div>
                            <div class="info-value">{{ $payment->branch->name ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Info -->
            @if($payment->patient)
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-person me-2"></i>ข้อมูลลูกค้า</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-label">ชื่อลูกค้า</div>
                            <div class="info-value">
                                <a href="{{ route('patients.show', $payment->patient) }}">
                                    {{ $payment->patient->name }}
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">เบอร์โทรศัพท์</div>
                            <div class="info-value">{{ $payment->patient->phone ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Invoice Info -->
            @if($payment->invoice)
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>ข้อมูลใบเสร็จ</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-label">เลขที่ใบเสร็จ</div>
                            <div class="info-value">
                                <a href="{{ route('invoices.show', $payment->invoice) }}">
                                    {{ $payment->invoice->invoice_number }}
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">วันที่ใบเสร็จ</div>
                            <div class="info-value">{{ $payment->invoice->invoice_date?->format('d/m/Y') ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">ยอดรวม</div>
                            <div class="info-value">฿{{ number_format($payment->invoice->total_amount, 2) }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">ชำระแล้ว</div>
                            <div class="info-value text-success">฿{{ number_format($payment->invoice->paid_amount, 2) }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">ค้างชำระ</div>
                            <div class="info-value text-danger">฿{{ number_format($payment->invoice->outstanding_amount, 2) }}</div>
                        </div>
                    </div>

                    @if($payment->invoice->items && $payment->invoice->items->count() > 0)
                    <hr>
                    <h6 class="mb-3">รายการในใบเสร็จ</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>รายการ</th>
                                <th class="text-center">จำนวน</th>
                                <th class="text-end">ราคา</th>
                                <th class="text-end">รวม</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payment->invoice->items as $item)
                            <tr>
                                <td>{{ $item->description }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">฿{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">฿{{ number_format($item->total_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
            @endif

            <!-- Notes -->
            @if($payment->notes)
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-sticky me-2"></i>หมายเหตุ</h6>
                </div>
                <div class="card-body">
                    {{ $payment->notes }}
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Amount Box -->
            <div class="amount-box mb-4">
                <div class="small opacity-75">จำนวนเงินที่ชำระ</div>
                <div class="amount">฿{{ number_format($payment->amount, 2) }}</div>
            </div>

            <!-- Actions -->
            @if($payment->status === 'completed')
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>การดำเนินการ</h6>
                </div>
                <div class="card-body d-grid gap-2">
                    <button type="button" class="btn btn-outline-danger" id="cancelPaymentBtn">
                        <i class="bi bi-x-circle me-1"></i> ยกเลิกการชำระเงิน
                    </button>
                </div>
            </div>
            @endif

            <!-- Metadata -->
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>ข้อมูลระบบ</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <div class="info-label">สร้างเมื่อ</div>
                        <div class="info-value small">{{ $payment->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="info-label">แก้ไขล่าสุด</div>
                        <div class="info-value small">{{ $payment->updated_at?->format('d/m/Y H:i') ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>ยกเลิกการชำระเงิน</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    การยกเลิกจะทำให้ยอดค้างชำระในใบเสร็จเพิ่มขึ้นตามจำนวนเงินที่ยกเลิก
                </div>
                <p>คุณต้องการยกเลิกการชำระเงิน <strong>฿{{ number_format($payment->amount, 2) }}</strong> หรือไม่?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-danger" id="confirmCancelBtn">
                    <i class="bi bi-check-lg me-1"></i> ยืนยันการยกเลิก
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentId = '{{ $payment->id }}';
    const cancelModal = document.getElementById('cancelModal') ? new bootstrap.Modal(document.getElementById('cancelModal')) : null;

    // Cancel payment button
    document.getElementById('cancelPaymentBtn')?.addEventListener('click', function() {
        if (cancelModal) cancelModal.show();
    });

    // Confirm cancel
    document.getElementById('confirmCancelBtn')?.addEventListener('click', function() {
        fetch(`/payments/${paymentId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route("payments.index") }}';
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
