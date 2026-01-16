@extends('layouts.app')

@section('title', 'สร้าง OPD ใหม่ - GCMS')

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .page-header {
        background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
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

    .patient-search-box {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1.5rem;
    }

    .temp-toggle {
        background: #fef3c7;
        border-radius: 12px;
        padding: 1rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2"><i class="bi bi-clipboard2-plus me-2"></i>สร้าง OPD ใหม่</h2>
                <p class="mb-0 opacity-90">เปิดบันทึกผู้ป่วยนอกใหม่</p>
            </div>
            <a href="{{ route('opd-records.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="form-card">
        <form method="POST" action="{{ route('opd-records.store') }}">
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

            <!-- Patient Selection -->
            <div class="section-title"><i class="bi bi-person me-2"></i>ข้อมูลผู้ป่วย</div>
            <div class="patient-search-box mb-4">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">ผู้ป่วย <span class="text-danger">*</span></label>
                        <select name="patient_id" class="form-select form-select-lg" required>
                            <option value="">เลือกผู้ป่วย</option>
                            @foreach($patients as $patient)
                            <option value="{{ $patient->id }}" {{ (old('patient_id') == $patient->id || $selectedPatientId == $patient->id) ? 'selected' : '' }}>
                                {{ $patient->name }} - {{ $patient->phone ?? 'ไม่มีเบอร์' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">สาขา <span class="text-danger">*</span></label>
                        <select name="branch_id" class="form-select form-select-lg" required>
                            <option value="">เลือกสาขา</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Chief Complaint -->
            <div class="section-title"><i class="bi bi-chat-left-text me-2"></i>อาการเบื้องต้น</div>
            <div class="mb-4">
                <label class="form-label">อาการสำคัญ / Chief Complaint</label>
                <textarea name="chief_complaint" class="form-control" rows="3" placeholder="ระบุอาการหรือสาเหตุที่มา...">{{ old('chief_complaint') }}</textarea>
            </div>

            <!-- Temporary Toggle -->
            <div class="temp-toggle mb-4">
                <div class="form-check">
                    <input type="checkbox" name="is_temporary" value="1" class="form-check-input" id="isTemporary" {{ old('is_temporary') ? 'checked' : '' }}>
                    <label class="form-check-label" for="isTemporary">
                        <strong><i class="bi bi-clock-history me-1"></i> OPD ชั่วคราว</strong>
                        <br><small class="text-muted">สำหรับผู้ป่วยที่ยังไม่ได้ลงทะเบียนถาวร หรือมาใช้บริการครั้งเดียว</small>
                    </label>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('opd-records.index') }}" class="btn btn-secondary">ยกเลิก</a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-lg me-1"></i> สร้าง OPD
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
