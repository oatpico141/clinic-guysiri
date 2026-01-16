@extends('layouts.app')

@section('title', 'การชำระเงิน - GCMS')

@push('styles')
<style>
    .payment-header {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
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

    .stat-icon.today { background: #d1fae5; color: #059669; }
    .stat-icon.week { background: #dbeafe; color: #2563eb; }
    .stat-icon.month { background: #f3e8ff; color: #7c3aed; }
    .stat-icon.count { background: #fef3c7; color: #d97706; }

    .filter-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        margin-bottom: 1.5rem;
    }

    .payment-table-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .payment-table thead th {
        background: #f8fafc;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        padding: 1rem;
        border: none;
    }

    .payment-table tbody td {
        padding: 1rem;
        vertical-align: middle;
    }

    .payment-table tbody tr:hover {
        background: #f8fafc;
    }

    .badge-completed { background: #d1fae5; color: #065f46; }
    .badge-pending { background: #fef3c7; color: #92400e; }
    .badge-cancelled { background: #fee2e2; color: #991b1b; }
    .badge-refunded { background: #e0e7ff; color: #3730a3; }

    .method-badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
    }

    .method-cash { background: #d1fae5; color: #065f46; }
    .method-transfer { background: #dbeafe; color: #1e40af; }
    .method-credit_card { background: #fce7f3; color: #be185d; }
    .method-qr_code { background: #f3e8ff; color: #7c3aed; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="payment-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-2"><i class="bi bi-credit-card me-2"></i>การชำระเงิน</h2>
                <p class="mb-0 opacity-90">จัดการรายการชำระเงินทั้งหมด</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="{{ route('payments.create') }}" class="btn btn-light">
                    <i class="bi bi-plus-lg me-1"></i> รับชำระเงิน
                </a>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon today me-3">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-success">฿{{ number_format($todayTotal, 0) }}</div>
                        <div class="text-muted small">วันนี้</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon week me-3">
                        <i class="bi bi-calendar-week"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-primary">฿{{ number_format($weekTotal, 0) }}</div>
                        <div class="text-muted small">สัปดาห์นี้</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon month me-3">
                        <i class="bi bi-calendar-month"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold" style="color: #7c3aed;">฿{{ number_format($monthTotal, 0) }}</div>
                        <div class="text-muted small">เดือนนี้</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon count me-3">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-warning">{{ number_format($todayCount) }}</div>
                        <div class="text-muted small">รายการวันนี้</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('payments.index') }}">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label small">ค้นหา</label>
                    <input type="text" name="search" class="form-control" placeholder="เลขที่/ชื่อลูกค้า" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">ช่องทาง</label>
                    <select name="payment_method" class="form-select">
                        <option value="">ทั้งหมด</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>เงินสด</option>
                        <option value="transfer" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>โอนเงิน</option>
                        <option value="credit_card" {{ request('payment_method') == 'credit_card' ? 'selected' : '' }}>บัตรเครดิต</option>
                        <option value="qr_code" {{ request('payment_method') == 'qr_code' ? 'selected' : '' }}>QR Code</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">สถานะ</label>
                    <select name="status" class="form-select">
                        <option value="">ทั้งหมด</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>สำเร็จ</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>รอดำเนินการ</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ยกเลิก</option>
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
                    <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Payment Table -->
    <div class="payment-table-card">
        <div class="table-responsive">
            <table class="table payment-table mb-0">
                <thead>
                    <tr>
                        <th>เลขที่</th>
                        <th>ลูกค้า</th>
                        <th>ใบเสร็จ</th>
                        <th>ช่องทาง</th>
                        <th class="text-end">จำนวนเงิน</th>
                        <th class="text-center">วันที่</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td><code>{{ $payment->payment_number ?? '-' }}</code></td>
                        <td>
                            @if($payment->patient)
                            <a href="{{ route('patients.show', $payment->patient) }}">
                                {{ $payment->patient->name }}
                            </a>
                            @else
                            -
                            @endif
                        </td>
                        <td>
                            @if($payment->invoice)
                            <a href="{{ route('invoices.show', $payment->invoice) }}">
                                {{ $payment->invoice->invoice_number }}
                            </a>
                            @else
                            -
                            @endif
                        </td>
                        <td>
                            @switch($payment->payment_method)
                                @case('cash')
                                    <span class="method-badge method-cash">เงินสด</span>
                                    @break
                                @case('transfer')
                                    <span class="method-badge method-transfer">โอนเงิน</span>
                                    @break
                                @case('credit_card')
                                    <span class="method-badge method-credit_card">บัตรเครดิต</span>
                                    @break
                                @case('qr_code')
                                    <span class="method-badge method-qr_code">QR Code</span>
                                    @break
                                @default
                                    <span class="method-badge">{{ $payment->payment_method }}</span>
                            @endswitch
                        </td>
                        <td class="text-end fw-bold">฿{{ number_format($payment->amount, 2) }}</td>
                        <td class="text-center">{{ $payment->payment_date?->format('d/m/Y') ?? '-' }}</td>
                        <td class="text-center">
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
                                @case('refunded')
                                    <span class="badge badge-refunded">คืนเงิน</span>
                                    @break
                            @endswitch
                        </td>
                        <td class="text-center">
                            <a href="{{ route('payments.show', $payment) }}" class="btn btn-sm btn-outline-info" title="ดูรายละเอียด">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            ไม่พบข้อมูลการชำระเงิน
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($payments->hasPages())
        <div class="p-3 border-top">
            {{ $payments->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
