@extends('layouts.app')

@section('title', 'เพิ่มตารางงาน - GCMS')

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .page-header {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border-radius: 20px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }

    .section-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e5e7eb;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2"><i class="bi bi-plus-circle me-2"></i>เพิ่มตารางงาน</h2>
                <p class="mb-0 opacity-90">สร้างตารางงานพนักงานใหม่</p>
            </div>
            <a href="{{ route('schedules.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="form-card">
        <form method="POST" action="{{ route('schedules.store') }}">
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

            <!-- Basic Info -->
            <div class="section-title"><i class="bi bi-calendar3 me-2"></i>ข้อมูลตารางงาน</div>
            <div class="row g-4 mb-4">
                <div class="col-md-4">
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
                <div class="col-md-4">
                    <label class="form-label">สาขา <span class="text-danger">*</span></label>
                    <select name="branch_id" class="form-select" required>
                        <option value="">เลือกสาขา</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">ประเภทตารางงาน</label>
                    <select name="schedule_type" class="form-select">
                        <option value="regular" {{ old('schedule_type') == 'regular' ? 'selected' : '' }}>งานปกติ</option>
                        <option value="overtime" {{ old('schedule_type') == 'overtime' ? 'selected' : '' }}>OT</option>
                        <option value="training" {{ old('schedule_type') == 'training' ? 'selected' : '' }}>อบรม</option>
                        <option value="meeting" {{ old('schedule_type') == 'meeting' ? 'selected' : '' }}>ประชุม</option>
                    </select>
                </div>
            </div>

            <!-- Time Info -->
            <div class="section-title"><i class="bi bi-clock me-2"></i>วันและเวลา</div>
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="form-label">วันที่ <span class="text-danger">*</span></label>
                    <input type="date" name="schedule_date" class="form-control" value="{{ old('schedule_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">เวลาเริ่ม <span class="text-danger">*</span></label>
                    <input type="time" name="start_time" class="form-control" value="{{ old('start_time', '09:00') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">เวลาสิ้นสุด <span class="text-danger">*</span></label>
                    <input type="time" name="end_time" class="form-control" value="{{ old('end_time', '18:00') }}" required>
                </div>
            </div>

            <!-- Break Time -->
            <div class="section-title"><i class="bi bi-cup-hot me-2"></i>เวลาพัก (ไม่บังคับ)</div>
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="form-label">เริ่มพัก</label>
                    <input type="time" name="break_start" class="form-control" value="{{ old('break_start') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">สิ้นสุดพัก</label>
                    <input type="time" name="break_end" class="form-control" value="{{ old('break_end') }}">
                </div>
            </div>

            <!-- Recurring -->
            <div class="section-title"><i class="bi bi-arrow-repeat me-2"></i>ตารางงานซ้ำ</div>
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_recurring" value="1" id="isRecurring" {{ old('is_recurring') ? 'checked' : '' }}>
                        <label class="form-check-label" for="isRecurring">
                            สร้างตารางงานซ้ำ
                        </label>
                    </div>
                </div>
                <div class="col-md-4" id="recurrencePattern" style="display: none;">
                    <label class="form-label">รูปแบบการซ้ำ</label>
                    <select name="recurrence_pattern" class="form-select">
                        <option value="daily" {{ old('recurrence_pattern') == 'daily' ? 'selected' : '' }}>ทุกวัน</option>
                        <option value="weekly" {{ old('recurrence_pattern') == 'weekly' ? 'selected' : '' }}>ทุกสัปดาห์</option>
                        <option value="monthly" {{ old('recurrence_pattern') == 'monthly' ? 'selected' : '' }}>ทุกเดือน</option>
                    </select>
                </div>
                <div class="col-md-4" id="recurrenceEnd" style="display: none;">
                    <label class="form-label">สิ้นสุดการซ้ำ</label>
                    <input type="date" name="recurrence_end_date" class="form-control" value="{{ old('recurrence_end_date') }}">
                </div>
            </div>

            <!-- Notes -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <label class="form-label">หมายเหตุ</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="หมายเหตุเพิ่มเติม...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('schedules.index') }}" class="btn btn-secondary">ยกเลิก</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> บันทึก
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isRecurring = document.getElementById('isRecurring');
    const recurrencePattern = document.getElementById('recurrencePattern');
    const recurrenceEnd = document.getElementById('recurrenceEnd');

    function toggleRecurrence() {
        const show = isRecurring.checked;
        recurrencePattern.style.display = show ? 'block' : 'none';
        recurrenceEnd.style.display = show ? 'block' : 'none';
    }

    isRecurring.addEventListener('change', toggleRecurrence);
    toggleRecurrence();
});
</script>
@endpush
