@extends('layouts.app')

@section('title', 'รายละเอียดรายได้ - ' . $user->name)

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #0284c7, #0ea5e9);
        border-radius: 12px;
        padding: 1.5rem;
        color: white;
        margin-bottom: 1.5rem;
    }

    .summary-card {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        border: 1px solid #e2e8f0;
        text-align: center;
    }

    .summary-card .amount {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .summary-card .label {
        font-size: 0.75rem;
        color: #64748b;
    }

    .detail-table {
        font-size: 0.85rem;
    }

    .detail-table th {
        background: #f8fafc;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
    }

    .badge-pattern {
        font-size: 0.7rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">{{ $user->name }}</h4>
                <p class="mb-0 opacity-75">
                    {{ $user->role->name ?? '-' }} | {{ $user->branch->name ?? '-' }}
                </p>
            </div>
            <a href="{{ route('commission-rates.index') }}" class="btn btn-light btn-sm">
                <i class="bi bi-arrow-left me-1"></i>กลับ
            </a>
        </div>
    </div>

    <!-- Filter -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label small">ช่วงเวลา</label>
                    <select name="date_range" class="form-select form-select-sm" onchange="toggleCustomDate(this)">
                        <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>วันนี้</option>
                        <option value="week" {{ $dateRange == 'week' ? 'selected' : '' }}>สัปดาห์นี้</option>
                        <option value="month" {{ $dateRange == 'month' ? 'selected' : '' }}>เดือนนี้</option>
                        <option value="custom" {{ $dateRange == 'custom' ? 'selected' : '' }}>กำหนดเอง</option>
                    </select>
                </div>
                <div class="col-auto" id="monthSelect" style="{{ $dateRange == 'month' ? '' : 'display:none' }}">
                    <label class="form-label small">เดือน</label>
                    <input type="month" name="month" class="form-control form-control-sm" value="{{ $startDate->format('Y-m') }}">
                </div>
                <div class="col-auto" id="customDate" style="{{ $dateRange == 'custom' ? '' : 'display:none' }}">
                    <label class="form-label small">วันที่เริ่ม</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate->format('Y-m-d') }}">
                </div>
                <div class="col-auto" id="customDateEnd" style="{{ $dateRange == 'custom' ? '' : 'display:none' }}">
                    <label class="form-label small">วันที่สิ้นสุด</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate->format('Y-m-d') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-search me-1"></i>ค้นหา
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="summary-card">
                <div class="amount text-secondary">฿{{ number_format($salary) }}</div>
                <div class="label">เงินเดือน</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="summary-card">
                <div class="amount text-primary">฿{{ number_format($totalDF) }}</div>
                <div class="label">ค่ามือ ({{ $treatments->count() }} เคส)</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="summary-card">
                <div class="amount text-warning">฿{{ number_format($totalCommission) }}</div>
                <div class="label">ค่าคอม ({{ $courseSales->count() }} คอร์ส)</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="summary-card">
                <div class="amount text-success">฿{{ number_format($totalPayout) }}</div>
                <div class="label">รวมทั้งหมด</div>
            </div>
        </div>
    </div>

    <!-- Period Info -->
    <div class="alert alert-info py-2 mb-3">
        <i class="bi bi-calendar3 me-2"></i>
        ข้อมูลวันที่ {{ $startDate->locale('th')->isoFormat('D MMM YYYY') }} - {{ $endDate->locale('th')->isoFormat('D MMM YYYY') }}
    </div>

    <!-- Tabs with Add Button -->
    <div class="d-flex justify-content-between align-items-center mb-0">
        <ul class="nav nav-tabs mb-0" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#dfTab">
                    <i class="bi bi-clipboard2-pulse me-1"></i>ค่ามือ ({{ $treatments->count() }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#commissionTab">
                    <i class="bi bi-box-seam me-1"></i>ค่าคอม ({{ $courseSales->count() }})
                </a>
            </li>
        </ul>
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addManualDfModal">
            <i class="bi bi-plus-circle me-1"></i>เพิ่มค่ามือพิเศษ
        </button>
    </div>

    <div class="tab-content">
        <!-- DF Tab -->
        <div class="tab-pane fade show active" id="dfTab">
            <div class="card border-top-0 rounded-top-0">
                <div class="card-body p-0">
                    @if($treatments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover detail-table mb-0">
                            <thead>
                                <tr>
                                    <th>วันที่</th>
                                    <th>ลูกค้า</th>
                                    <th>หัตการ</th>
                                    <th>สาขา</th>
                                    <th class="text-end">ค่ามือ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($treatments as $t)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($t['date'])->locale('th')->isoFormat('D MMM YY HH:mm') }}</td>
                                    <td>{{ $t['patient_name'] }}</td>
                                    <td>{{ $t['service_name'] }}</td>
                                    <td>{{ $t['branch_name'] }}</td>
                                    <td class="text-end fw-bold text-primary">฿{{ number_format($t['df_amount']) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="4" class="text-end fw-bold">รวมค่ามือ</td>
                                    <td class="text-end fw-bold text-primary">฿{{ number_format($totalDF) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-clipboard-x" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">ไม่มีข้อมูลค่ามือในช่วงเวลานี้</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Commission Tab -->
        <div class="tab-pane fade" id="commissionTab">
            <div class="card border-top-0 rounded-top-0">
                <div class="card-body p-0">
                    @if($courseSales->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover detail-table mb-0">
                            <thead>
                                <tr>
                                    <th>วันที่</th>
                                    <th>เลขคอร์ส</th>
                                    <th>ลูกค้า</th>
                                    <th>คอร์ส</th>
                                    <th>รูปแบบ</th>
                                    <th class="text-end">ราคา</th>
                                    <th class="text-center">คนขาย</th>
                                    <th class="text-end">คอมได้รับ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($courseSales as $c)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($c['date'])->locale('th')->isoFormat('D MMM YY') }}</td>
                                    <td><small class="text-muted">{{ $c['course_number'] }}</small></td>
                                    <td>{{ $c['patient_name'] }}</td>
                                    <td>{{ $c['package_name'] }}</td>
                                    <td>
                                        @switch($c['purchase_pattern'])
                                            @case('buy_and_use')
                                                <span class="badge bg-success badge-pattern">ซื้อ+ใช้</span>
                                                @break
                                            @case('buy_for_later')
                                                <span class="badge bg-primary badge-pattern">ซื้อเก็บ</span>
                                                @break
                                            @case('retroactive')
                                                <span class="badge bg-warning text-dark badge-pattern">ต่อคอร์ส</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary badge-pattern">{{ $c['purchase_pattern'] }}</span>
                                        @endswitch
                                    </td>
                                    <td class="text-end">฿{{ number_format($c['price']) }}</td>
                                    <td class="text-center">
                                        @if($c['purchase_pattern'] == 'retroactive')
                                            <span class="text-muted">หารทุก PT</span>
                                        @else
                                            {{ $c['seller_count'] }} คน
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-warning">฿{{ number_format($c['staff_commission']) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="7" class="text-end fw-bold">รวมค่าคอม</td>
                                    <td class="text-end fw-bold text-warning">฿{{ number_format($totalCommission) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-box" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">ไม่มีข้อมูลค่าคอมในช่วงเวลานี้</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: เพิ่มค่ามือพิเศษ -->
<div class="modal fade" id="addManualDfModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>เพิ่มค่ามือพิเศษ
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addManualDfForm">
                <div class="modal-body">
                    <input type="hidden" name="pt_id" value="{{ $user->id }}">

                    <div class="mb-3">
                        <label class="form-label">วันที่ <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">จำนวนเงิน (บาท) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0" required placeholder="เช่น 500">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">รายละเอียด / หมายเหตุ</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="เช่น ค่ามือย้อนหลัง, OT พิเศษ"></textarea>
                    </div>

                    <div class="alert alert-warning py-2 mb-0">
                        <small><i class="bi bi-info-circle me-1"></i>ค่ามือนี้จะถูกเพิ่มเข้าระบบทันทีและไม่สามารถลบได้</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleCustomDate(select) {
    const value = select.value;
    document.getElementById('monthSelect').style.display = value === 'month' ? '' : 'none';
    document.getElementById('customDate').style.display = value === 'custom' ? '' : 'none';
    document.getElementById('customDateEnd').style.display = value === 'custom' ? '' : 'none';
}

// Handle manual DF form submission
document.getElementById('addManualDfForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>กำลังบันทึก...';

    try {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        const response = await fetch('{{ url('/api/df-payments/manual') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            alert('บันทึกค่ามือพิเศษสำเร็จ');
            window.location.reload();
        } else {
            alert('เกิดข้อผิดพลาด: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        alert('เกิดข้อผิดพลาด: ' + error.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});
</script>
@endsection
