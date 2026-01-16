@extends('layouts.app')

@section('title', 'แก้ไขตารางงาน - GCMS')

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
                <h2 class="mb-2"><i class="bi bi-pencil me-2"></i>แก้ไขตารางงาน</h2>
                <p class="mb-0 opacity-90">
                    {{ $schedule->staff->first_name ?? '' }} {{ $schedule->staff->last_name ?? '' }} |
                    {{ $schedule->schedule_date?->format('d/m/Y') }}
                </p>
            </div>
            <a href="{{ route('schedules.index', ['start_date' => $schedule->schedule_date?->startOfWeek()->format('Y-m-d')]) }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="form-card">
        <form method="POST" action="{{ route('schedules.update', $schedule) }}">
            @csrf
            @method('PUT')

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
                        <option value="{{ $staff->id }}" {{ old('staff_id', $schedule->staff_id) == $staff->id ? 'selected' : '' }}>
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
                        <option value="{{ $branch->id }}" {{ old('branch_id', $schedule->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">ประเภทตารางงาน</label>
                    <select name="schedule_type" class="form-select">
                        <option value="regular" {{ old('schedule_type', $schedule->schedule_type) == 'regular' ? 'selected' : '' }}>งานปกติ</option>
                        <option value="overtime" {{ old('schedule_type', $schedule->schedule_type) == 'overtime' ? 'selected' : '' }}>OT</option>
                        <option value="training" {{ old('schedule_type', $schedule->schedule_type) == 'training' ? 'selected' : '' }}>อบรม</option>
                        <option value="meeting" {{ old('schedule_type', $schedule->schedule_type) == 'meeting' ? 'selected' : '' }}>ประชุม</option>
                    </select>
                </div>
            </div>

            <!-- Time Info -->
            <div class="section-title"><i class="bi bi-clock me-2"></i>วันและเวลา</div>
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <label class="form-label">วันที่ <span class="text-danger">*</span></label>
                    <input type="date" name="schedule_date" class="form-control" value="{{ old('schedule_date', $schedule->schedule_date?->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">เวลาเริ่ม <span class="text-danger">*</span></label>
                    <input type="time" name="start_time" class="form-control" value="{{ old('start_time', substr($schedule->start_time, 0, 5)) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">เวลาสิ้นสุด <span class="text-danger">*</span></label>
                    <input type="time" name="end_time" class="form-control" value="{{ old('end_time', substr($schedule->end_time, 0, 5)) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">สถานะ <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="scheduled" {{ old('status', $schedule->status) == 'scheduled' ? 'selected' : '' }}>กำหนดการ</option>
                        <option value="completed" {{ old('status', $schedule->status) == 'completed' ? 'selected' : '' }}>เสร็จสิ้น</option>
                        <option value="cancelled" {{ old('status', $schedule->status) == 'cancelled' ? 'selected' : '' }}>ยกเลิก</option>
                    </select>
                </div>
            </div>

            <!-- Break Time -->
            <div class="section-title"><i class="bi bi-cup-hot me-2"></i>เวลาพัก (ไม่บังคับ)</div>
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="form-label">เริ่มพัก</label>
                    <input type="time" name="break_start" class="form-control" value="{{ old('break_start', $schedule->break_start ? substr($schedule->break_start, 0, 5) : '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">สิ้นสุดพัก</label>
                    <input type="time" name="break_end" class="form-control" value="{{ old('break_end', $schedule->break_end ? substr($schedule->break_end, 0, 5) : '') }}">
                </div>
                <div class="col-md-4">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="is_available" value="1" id="isAvailable" {{ old('is_available', $schedule->is_available) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isAvailable">
                            พร้อมรับงาน
                        </label>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <label class="form-label">หมายเหตุ</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $schedule->notes) }}</textarea>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-danger" onclick="deleteSchedule()">
                    <i class="bi bi-trash me-1"></i> ลบตารางงาน
                </button>
                <div class="d-flex gap-2">
                    <a href="{{ route('schedules.index', ['start_date' => $schedule->schedule_date?->startOfWeek()->format('Y-m-d')]) }}" class="btn btn-secondary">ยกเลิก</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> บันทึก
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteSchedule() {
    if (confirm('ยืนยันการลบตารางงานนี้?')) {
        fetch('{{ route('schedules.destroy', $schedule) }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route('schedules.index') }}';
            } else {
                alert(data.message);
            }
        })
        .catch(err => {
            alert('เกิดข้อผิดพลาด');
            console.error(err);
        });
    }
}
</script>
@endpush
