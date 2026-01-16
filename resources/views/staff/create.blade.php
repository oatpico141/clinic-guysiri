@extends('layouts.app')

@section('title', 'เพิ่มพนักงานใหม่ - GCMS')

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .page-header {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
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
                <h2 class="mb-2"><i class="bi bi-person-plus me-2"></i>เพิ่มพนักงานใหม่</h2>
                <p class="mb-0 opacity-90">ลงทะเบียนพนักงานใหม่เข้าระบบ</p>
            </div>
            <a href="{{ route('staff.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="form-card">
        <form method="POST" action="{{ route('staff.store') }}">
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

            <!-- Personal Info -->
            <div class="section-title"><i class="bi bi-person me-2"></i>ข้อมูลส่วนตัว</div>
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="form-label">รหัสพนักงาน <span class="text-danger">*</span></label>
                    <input type="text" name="employee_id" class="form-control" value="{{ old('employee_id') }}" required placeholder="เช่น EMP-001">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ชื่อ <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required placeholder="ชื่อจริง">
                </div>
                <div class="col-md-4">
                    <label class="form-label">นามสกุล <span class="text-danger">*</span></label>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required placeholder="นามสกุล">
                </div>
                <div class="col-md-4">
                    <label class="form-label">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required placeholder="08x-xxx-xxxx">
                </div>
                <div class="col-md-4">
                    <label class="form-label">อีเมล</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="email@example.com">
                </div>
                <div class="col-md-4">
                    <label class="form-label">เพศ</label>
                    <select name="gender" class="form-select">
                        <option value="">ไม่ระบุ</option>
                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>ชาย</option>
                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>หญิง</option>
                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>อื่นๆ</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">วันเกิด</label>
                    <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label">ที่อยู่</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="ที่อยู่...">{{ old('address') }}</textarea>
                </div>
            </div>

            <!-- Employment Info -->
            <div class="section-title"><i class="bi bi-briefcase me-2"></i>ข้อมูลการจ้างงาน</div>
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <label class="form-label">สาขา <span class="text-danger">*</span></label>
                    <select name="branch_id" class="form-select" required>
                        <option value="">เลือกสาขา</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">ตำแหน่ง <span class="text-danger">*</span></label>
                    <select name="position" class="form-select" required>
                        <option value="">เลือกตำแหน่ง</option>
                        <option value="pt" {{ old('position') == 'pt' ? 'selected' : '' }}>PT (Physical Therapist)</option>
                        <option value="receptionist" {{ old('position') == 'receptionist' ? 'selected' : '' }}>พนักงานต้อนรับ</option>
                        <option value="manager" {{ old('position') == 'manager' ? 'selected' : '' }}>ผู้จัดการ</option>
                        <option value="admin" {{ old('position') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="nurse" {{ old('position') == 'nurse' ? 'selected' : '' }}>พยาบาล</option>
                        <option value="assistant" {{ old('position') == 'assistant' ? 'selected' : '' }}>ผู้ช่วย</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">แผนก</label>
                    <input type="text" name="department" class="form-control" value="{{ old('department') }}" placeholder="ชื่อแผนก">
                </div>
                <div class="col-md-3">
                    <label class="form-label">วันที่เริ่มงาน <span class="text-danger">*</span></label>
                    <input type="date" name="hire_date" class="form-control" value="{{ old('hire_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">สถานะการจ้างงาน <span class="text-danger">*</span></label>
                    <select name="employment_status" class="form-select" required>
                        <option value="active" {{ old('employment_status', 'active') == 'active' ? 'selected' : '' }}>ทำงานอยู่</option>
                        <option value="on_leave" {{ old('employment_status') == 'on_leave' ? 'selected' : '' }}>ลางาน</option>
                        <option value="terminated" {{ old('employment_status') == 'terminated' ? 'selected' : '' }}>พ้นสภาพ</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">ประเภทการจ้างงาน <span class="text-danger">*</span></label>
                    <select name="employment_type" class="form-select" required>
                        <option value="full_time" {{ old('employment_type', 'full_time') == 'full_time' ? 'selected' : '' }}>พนักงานประจำ</option>
                        <option value="part_time" {{ old('employment_type') == 'part_time' ? 'selected' : '' }}>พนักงานพาร์ทไทม์</option>
                        <option value="contract" {{ old('employment_type') == 'contract' ? 'selected' : '' }}>สัญญาจ้าง</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">เชื่อมโยง User Account</label>
                    <select name="user_id" class="form-select">
                        <option value="">ไม่เชื่อมโยง</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    <small class="text-muted">สำหรับ login เข้าระบบ</small>
                </div>
            </div>

            <!-- License Info (for PT) -->
            <div class="section-title"><i class="bi bi-file-earmark-medical me-2"></i>ข้อมูลใบอนุญาต (สำหรับ PT)</div>
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="form-label">เลขที่ใบอนุญาต</label>
                    <input type="text" name="license_number" class="form-control" value="{{ old('license_number') }}" placeholder="เลขที่ใบประกอบวิชาชีพ">
                </div>
                <div class="col-md-4">
                    <label class="form-label">วันหมดอายุใบอนุญาต</label>
                    <input type="date" name="license_expiry" class="form-control" value="{{ old('license_expiry') }}">
                </div>
            </div>

            <!-- Salary Info -->
            <div class="section-title"><i class="bi bi-cash me-2"></i>ข้อมูลเงินเดือน</div>
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="form-label">ประเภทเงินเดือน</label>
                    <select name="salary_type" class="form-select">
                        <option value="">ไม่ระบุ</option>
                        <option value="monthly" {{ old('salary_type') == 'monthly' ? 'selected' : '' }}>รายเดือน</option>
                        <option value="hourly" {{ old('salary_type') == 'hourly' ? 'selected' : '' }}>รายชั่วโมง</option>
                        <option value="commission_only" {{ old('salary_type') == 'commission_only' ? 'selected' : '' }}>คอมมิชชั่นอย่างเดียว</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">เงินเดือนพื้นฐาน</label>
                    <div class="input-group">
                        <span class="input-group-text">฿</span>
                        <input type="number" name="base_salary" class="form-control" value="{{ old('base_salary') }}" min="0" step="0.01" placeholder="0.00">
                    </div>
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
                <a href="{{ route('staff.index') }}" class="btn btn-secondary">ยกเลิก</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> บันทึก
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
