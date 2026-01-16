@extends('layouts.app')

@section('title', 'รับชำระเงิน - GCMS')

@push('styles')
<style>
    .payment-header {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        border-radius: 20px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }

    .form-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        margin-bottom: 1.5rem;
    }

    .form-card .card-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 1rem 1.5rem;
        border-radius: 16px 16px 0 0;
    }

    .form-card .card-body {
        padding: 1.5rem;
    }

    .invoice-summary {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1rem;
    }

    .payment-method-btn {
        padding: 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s;
        text-align: center;
    }

    .payment-method-btn:hover {
        border-color: #0ea5e9;
        background: #f0f9ff;
    }

    .payment-method-btn.active {
        border-color: #0ea5e9;
        background: #e0f2fe;
    }

    .payment-method-btn i {
        font-size: 1.5rem;
        display: block;
        margin-bottom: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="payment-header">
        <a href="{{ route('payments.index') }}" class="btn btn-sm btn-light mb-3">
            <i class="bi bi-arrow-left me-1"></i> กลับ
        </a>
        <h2 class="mb-2"><i class="bi bi-credit-card me-2"></i>รับชำระเงิน</h2>
        <p class="mb-0 opacity-90">บันทึกการรับชำระเงินจากลูกค้า</p>
    </div>

    <form method="POST" action="{{ route('payments.store') }}">
        @csrf

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Invoice Selection -->
                <div class="form-card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>เลือกใบเสร็จ</h6>
                    </div>
                    <div class="card-body">
                        @if($invoice)
                            <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                            <div class="invoice-summary">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="small text-muted">เลขที่ใบเสร็จ</div>
                                        <div class="fw-bold">{{ $invoice->invoice_number }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="small text-muted">ลูกค้า</div>
                                        <div class="fw-bold">{{ $invoice->patient->name ?? '-' }}</div>
                                    </div>
                                    <div class="col-md-4 mt-3">
                                        <div class="small text-muted">ยอดรวม</div>
                                        <div class="fw-bold">฿{{ number_format($invoice->total_amount, 2) }}</div>
                                    </div>
                                    <div class="col-md-4 mt-3">
                                        <div class="small text-muted">ชำระแล้ว</div>
                                        <div class="fw-bold text-success">฿{{ number_format($invoice->paid_amount, 2) }}</div>
                                    </div>
                                    <div class="col-md-4 mt-3">
                                        <div class="small text-muted">ค้างชำระ</div>
                                        <div class="fw-bold text-danger">฿{{ number_format($invoice->outstanding_amount, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="mb-3">
                                <label class="form-label">เลือกใบเสร็จ <span class="text-danger">*</span></label>
                                <select name="invoice_id" class="form-select @error('invoice_id') is-invalid @enderror" required>
                                    <option value="">-- เลือกใบเสร็จ --</option>
                                    @foreach($pendingInvoices as $inv)
                                        <option value="{{ $inv->id }}" {{ old('invoice_id') == $inv->id ? 'selected' : '' }}>
                                            {{ $inv->invoice_number }} - {{ $inv->patient->name ?? '-' }} (ค้าง ฿{{ number_format($inv->outstanding_amount, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('invoice_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="form-card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-wallet2 me-2"></i>ช่องทางชำระเงิน</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-2 col-4">
                                <label class="payment-method-btn w-100" data-method="cash">
                                    <input type="radio" name="payment_method" value="cash" class="d-none" {{ old('payment_method', 'cash') == 'cash' ? 'checked' : '' }}>
                                    <i class="bi bi-cash text-success"></i>
                                    <span class="small">เงินสด</span>
                                </label>
                            </div>
                            <div class="col-md-2 col-4">
                                <label class="payment-method-btn w-100" data-method="transfer">
                                    <input type="radio" name="payment_method" value="transfer" class="d-none" {{ old('payment_method') == 'transfer' ? 'checked' : '' }}>
                                    <i class="bi bi-bank text-primary"></i>
                                    <span class="small">โอนเงิน</span>
                                </label>
                            </div>
                            <div class="col-md-2 col-4">
                                <label class="payment-method-btn w-100" data-method="credit_card">
                                    <input type="radio" name="payment_method" value="credit_card" class="d-none" {{ old('payment_method') == 'credit_card' ? 'checked' : '' }}>
                                    <i class="bi bi-credit-card text-danger"></i>
                                    <span class="small">บัตรเครดิต</span>
                                </label>
                            </div>
                            <div class="col-md-2 col-4">
                                <label class="payment-method-btn w-100" data-method="debit_card">
                                    <input type="radio" name="payment_method" value="debit_card" class="d-none" {{ old('payment_method') == 'debit_card' ? 'checked' : '' }}>
                                    <i class="bi bi-credit-card-2-front text-info"></i>
                                    <span class="small">บัตรเดบิต</span>
                                </label>
                            </div>
                            <div class="col-md-2 col-4">
                                <label class="payment-method-btn w-100" data-method="qr_code">
                                    <input type="radio" name="payment_method" value="qr_code" class="d-none" {{ old('payment_method') == 'qr_code' ? 'checked' : '' }}>
                                    <i class="bi bi-qr-code" style="color: #7c3aed;"></i>
                                    <span class="small">QR Code</span>
                                </label>
                            </div>
                            <div class="col-md-2 col-4">
                                <label class="payment-method-btn w-100" data-method="installment">
                                    <input type="radio" name="payment_method" value="installment" class="d-none" {{ old('payment_method') == 'installment' ? 'checked' : '' }}>
                                    <i class="bi bi-calendar3 text-warning"></i>
                                    <span class="small">ผ่อนชำระ</span>
                                </label>
                            </div>
                        </div>

                        <!-- Additional fields based on payment method -->
                        <div id="cardFields" class="row g-3" style="display: none;">
                            <div class="col-md-6">
                                <label class="form-label">ประเภทบัตร</label>
                                <select name="card_type" class="form-select">
                                    <option value="">-- เลือก --</option>
                                    <option value="visa">VISA</option>
                                    <option value="mastercard">MasterCard</option>
                                    <option value="jcb">JCB</option>
                                    <option value="amex">American Express</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">4 หลักสุดท้าย</label>
                                <input type="text" name="card_last_4" class="form-control" maxlength="4" placeholder="1234">
                            </div>
                        </div>

                        <div id="installmentFields" class="row g-3" style="display: none;">
                            <div class="col-md-6">
                                <label class="form-label">งวดที่</label>
                                <input type="number" name="installment_number" class="form-control" min="1" value="{{ old('installment_number', 1) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">จำนวนงวดทั้งหมด</label>
                                <input type="number" name="total_installments" class="form-control" min="1" value="{{ old('total_installments') }}">
                            </div>
                        </div>

                        <div id="referenceField" class="mt-3">
                            <label class="form-label">เลขอ้างอิง</label>
                            <input type="text" name="reference_number" class="form-control" value="{{ old('reference_number') }}" placeholder="เลขที่โอน/อ้างอิง">
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="form-card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-sticky me-2"></i>หมายเหตุ</h6>
                    </div>
                    <div class="card-body">
                        <textarea name="notes" class="form-control" rows="3" placeholder="หมายเหตุเพิ่มเติม (ถ้ามี)">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <div class="sticky-top" style="top: 100px;">
                    <!-- Amount -->
                    <div class="form-card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-cash-coin me-2"></i>จำนวนเงิน</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">จำนวนเงินที่ชำระ <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">฿</span>
                                    <input type="number" name="amount" class="form-control form-control-lg @error('amount') is-invalid @enderror"
                                           value="{{ old('amount', $invoice->outstanding_amount ?? '') }}"
                                           step="0.01" min="0.01" required>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">วันที่ชำระ <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror"
                                       value="{{ old('payment_date', now()->format('Y-m-d')) }}" required>
                                @error('payment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="form-card">
                        <div class="card-body d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle me-2"></i>บันทึกการชำระเงิน
                            </button>
                            <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">
                                ยกเลิก
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const methodBtns = document.querySelectorAll('.payment-method-btn');
    const cardFields = document.getElementById('cardFields');
    const installmentFields = document.getElementById('installmentFields');

    // Set initial state
    updateMethodUI();

    methodBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active from all
            methodBtns.forEach(b => b.classList.remove('active'));
            // Add active to clicked
            this.classList.add('active');
            // Update fields
            updateMethodUI();
        });
    });

    function updateMethodUI() {
        const selected = document.querySelector('input[name="payment_method"]:checked');
        if (!selected) return;

        const method = selected.value;

        // Set active class
        methodBtns.forEach(btn => {
            if (btn.querySelector('input').value === method) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        // Show/hide fields
        cardFields.style.display = ['credit_card', 'debit_card'].includes(method) ? 'flex' : 'none';
        installmentFields.style.display = method === 'installment' ? 'flex' : 'none';
    }
});
</script>
@endpush
