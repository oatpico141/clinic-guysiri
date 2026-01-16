@extends('layouts.app')

@section('title', 'ยื่นคำขอลา - GCMS')

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .page-header {
        background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
        border-radius: 20px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }

    .leave-type-card {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .leave-type-card:hover {
        border-color: #ec4899;
    }

    .leave-type-card.selected {
        border-color: #ec4899;
        background: #fdf2f8;
    }

    .leave-type-card input[type="radio"] {
        display: none;
    }

    .leave-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2"><i class="bi bi-calendar-plus me-2"></i>ยื่นคำขอลา</h2>
                <p class="mb-0 opacity-90">สร้างคำขอลาใหม่</p>
            </div>
            <a href="{{ route('leave-requests.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="form-card">
        <form method="POST" action="{{ route('leave-requests.store') }}">
            @csrf

            @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Staff & Branch -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label">พนักงาน <span class="text-danger">*</span></label>
                    <select name="staff_id" class="form-select" required>
                        <option value="">เลือกพนักงาน</option>
                        @foreach($staffs as $staff)
                        <option value="{{ $staff->id }}" {{ old('staff_id') == $staff->id ? 'selected' : '' }}>
                            {{ $staff->first_name }} {{ $staff->last_name }}
                            @if($staff->position) ({{ $staff->position }}) @endif
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">สาขา <span class="text-danger">*</span></label>
                    <select name="branch_id" class="form-select" required>
                        <option value="">เลือกสาขา</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Leave Type -->
            <div class="mb-4">
                <label class="form-label">ประเภทการลา <span class="text-danger">*</span></label>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="leave-type-card d-block text-center {{ old('leave_type') == 'annual' ? 'selected' : '' }}">
                            <input type="radio" name="leave_type" value="annual" {{ old('leave_type') == 'annual' ? 'checked' : '' }} required>
                            <div class="leave-icon text-info"><i class="bi bi-sun"></i></div>
                            <div class="fw-bold">ลาพักร้อน</div>
                            <small class="text-muted">Annual Leave</small>
                        </label>
                    </div>
                    <div class="col-md-4">
                        <label class="leave-type-card d-block text-center {{ old('leave_type') == 'sick' ? 'selected' : '' }}">
                            <input type="radio" name="leave_type" value="sick" {{ old('leave_type') == 'sick' ? 'checked' : '' }}>
                            <div class="leave-icon text-danger"><i class="bi bi-thermometer-half"></i></div>
                            <div class="fw-bold">ลาป่วย</div>
                            <small class="text-muted">Sick Leave</small>
                        </label>
                    </div>
                    <div class="col-md-4">
                        <label class="leave-type-card d-block text-center {{ old('leave_type') == 'personal' ? 'selected' : '' }}">
                            <input type="radio" name="leave_type" value="personal" {{ old('leave_type') == 'personal' ? 'checked' : '' }}>
                            <div class="leave-icon text-secondary"><i class="bi bi-person"></i></div>
                            <div class="fw-bold">ลากิจ</div>
                            <small class="text-muted">Personal Leave</small>
                        </label>
                    </div>
                    <div class="col-md-4">
                        <label class="leave-type-card d-block text-center {{ old('leave_type') == 'maternity' ? 'selected' : '' }}">
                            <input type="radio" name="leave_type" value="maternity" {{ old('leave_type') == 'maternity' ? 'checked' : '' }}>
                            <div class="leave-icon" style="color: #ec4899;"><i class="bi bi-heart"></i></div>
                            <div class="fw-bold">ลาคลอด</div>
                            <small class="text-muted">Maternity Leave</small>
                        </label>
                    </div>
                    <div class="col-md-4">
                        <label class="leave-type-card d-block text-center {{ old('leave_type') == 'unpaid' ? 'selected' : '' }}">
                            <input type="radio" name="leave_type" value="unpaid" {{ old('leave_type') == 'unpaid' ? 'checked' : '' }}>
                            <div class="leave-icon text-dark"><i class="bi bi-cash-stack"></i></div>
                            <div class="fw-bold">ลาไม่รับเงิน</div>
                            <small class="text-muted">Unpaid Leave</small>
                        </label>
                    </div>
                    <div class="col-md-4">
                        <label class="leave-type-card d-block text-center {{ old('leave_type') == 'other' ? 'selected' : '' }}">
                            <input type="radio" name="leave_type" value="other" {{ old('leave_type') == 'other' ? 'checked' : '' }}>
                            <div class="leave-icon text-muted"><i class="bi bi-three-dots"></i></div>
                            <div class="fw-bold">อื่นๆ</div>
                            <small class="text-muted">Other</small>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Dates -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="form-label">วันที่เริ่มลา <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">วันที่สิ้นสุด <span class="text-danger">*</span></label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">จำนวนวัน</label>
                    <input type="text" id="totalDays" class="form-control" readonly value="0 วัน">
                </div>
            </div>

            <!-- Reason -->
            <div class="mb-4">
                <label class="form-label">เหตุผลการลา <span class="text-danger">*</span></label>
                <textarea name="reason" class="form-control" rows="3" required placeholder="ระบุเหตุผลการลา...">{{ old('reason') }}</textarea>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('leave-requests.index') }}" class="btn btn-secondary">ยกเลิก</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send me-1"></i> ส่งคำขอลา
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.querySelector('input[name="start_date"]');
    const endDate = document.querySelector('input[name="end_date"]');
    const totalDays = document.getElementById('totalDays');
    const leaveTypeCards = document.querySelectorAll('.leave-type-card');

    function calculateDays() {
        if (startDate.value && endDate.value) {
            const start = new Date(startDate.value);
            const end = new Date(endDate.value);
            const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
            totalDays.value = diff > 0 ? diff + ' วัน' : '0 วัน';
        }
    }

    startDate.addEventListener('change', calculateDays);
    endDate.addEventListener('change', calculateDays);

    // Leave type card selection
    leaveTypeCards.forEach(card => {
        card.addEventListener('click', function() {
            leaveTypeCards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
        });
    });

    calculateDays();
});
</script>
@endpush
