@extends('layouts.app')

@section('title', 'ส่งคำขอเปลี่ยน PT - GCMS')

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

    .patient-search-box {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .patient-selected {
        background: #f0fdf4;
        border: 2px solid #22c55e;
        border-radius: 12px;
        padding: 1rem;
        display: none;
    }

    .patient-selected.show {
        display: block;
    }

    .pt-select-card {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1.5rem;
        border: 2px dashed #e5e7eb;
        transition: all 0.3s;
    }

    .pt-select-card:hover {
        border-color: #8b5cf6;
    }

    .pt-arrow {
        font-size: 2rem;
        color: #8b5cf6;
    }

    .reason-card {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.3s;
    }

    .reason-card:hover {
        border-color: #8b5cf6;
    }

    .reason-card.selected {
        border-color: #8b5cf6;
        background: #f5f3ff;
    }

    .reason-card input[type="radio"] {
        display: none;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2"><i class="bi bi-person-lines-fill me-2"></i>ส่งคำขอเปลี่ยน PT</h2>
                <p class="mb-0 opacity-90">ส่งคำขอเปลี่ยน PT ประจำให้ผู้ป่วย</p>
            </div>
            <a href="{{ route('pt-requests.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="form-card">
        <form method="POST" action="{{ route('pt-requests.store') }}">
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
            <div class="section-title"><i class="bi bi-person me-2"></i>เลือกผู้ป่วย</div>
            <div class="patient-search-box">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">ค้นหาผู้ป่วย <span class="text-danger">*</span></label>
                        <select name="patient_id" class="form-select" id="patientSelect" required>
                            <option value="">เลือกผู้ป่วย</option>
                            @foreach($patients as $patient)
                            <option value="{{ $patient->id }}"
                                    data-pt="{{ $patient->preferred_pt_id ?? '' }}"
                                    {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                {{ $patient->name }} - {{ $patient->phone ?? 'ไม่มีเบอร์' }}
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
                </div>
            </div>

            <!-- PT Selection -->
            <div class="section-title"><i class="bi bi-people me-2"></i>เลือก PT</div>
            <div class="row align-items-center mb-4">
                <div class="col-md-5">
                    <div class="pt-select-card">
                        <label class="form-label fw-semibold text-danger">
                            <i class="bi bi-person-x me-1"></i> PT เดิม
                        </label>
                        <select name="original_pt_id" class="form-select" id="originalPtSelect">
                            <option value="">ไม่มี PT เดิม</option>
                            @foreach($pts as $pt)
                            <option value="{{ $pt->id }}" {{ old('original_pt_id') == $pt->id ? 'selected' : '' }}>
                                {{ $pt->first_name }} {{ $pt->last_name }}
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted">PT ประจำปัจจุบัน (ถ้ามี)</small>
                    </div>
                </div>
                <div class="col-md-2 text-center py-3">
                    <i class="bi bi-arrow-right pt-arrow"></i>
                </div>
                <div class="col-md-5">
                    <div class="pt-select-card">
                        <label class="form-label fw-semibold text-success">
                            <i class="bi bi-person-check me-1"></i> PT ที่ต้องการ <span class="text-danger">*</span>
                        </label>
                        <select name="requested_pt_id" class="form-select" required>
                            <option value="">เลือก PT ที่ต้องการ</option>
                            @foreach($pts as $pt)
                            <option value="{{ $pt->id }}" {{ old('requested_pt_id') == $pt->id ? 'selected' : '' }}>
                                {{ $pt->first_name }} {{ $pt->last_name }}
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted">PT ที่ผู้ป่วยต้องการเปลี่ยนไปใช้</small>
                    </div>
                </div>
            </div>

            <!-- Reason -->
            <div class="section-title"><i class="bi bi-chat-left-text me-2"></i>เหตุผลในการขอเปลี่ยน</div>
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="reason-card d-block" id="reason1">
                        <input type="radio" name="reason_type" value="schedule">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calendar-x fs-4 me-2 text-primary"></i>
                            <div>
                                <div class="fw-semibold small">ตารางไม่ตรง</div>
                                <small class="text-muted">เวลาว่างไม่ตรงกัน</small>
                            </div>
                        </div>
                    </label>
                </div>
                <div class="col-md-3">
                    <label class="reason-card d-block" id="reason2">
                        <input type="radio" name="reason_type" value="satisfaction">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-emoji-frown fs-4 me-2 text-warning"></i>
                            <div>
                                <div class="fw-semibold small">ไม่พอใจบริการ</div>
                                <small class="text-muted">ต้องการเปลี่ยน PT</small>
                            </div>
                        </div>
                    </label>
                </div>
                <div class="col-md-3">
                    <label class="reason-card d-block" id="reason3">
                        <input type="radio" name="reason_type" value="recommendation">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-hand-thumbs-up fs-4 me-2 text-success"></i>
                            <div>
                                <div class="fw-semibold small">ได้รับแนะนำ</div>
                                <small class="text-muted">มีคนแนะนำ PT ใหม่</small>
                            </div>
                        </div>
                    </label>
                </div>
                <div class="col-md-3">
                    <label class="reason-card d-block" id="reason4">
                        <input type="radio" name="reason_type" value="other">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-three-dots fs-4 me-2 text-secondary"></i>
                            <div>
                                <div class="fw-semibold small">อื่นๆ</div>
                                <small class="text-muted">เหตุผลอื่น</small>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">รายละเอียดเหตุผล <span class="text-danger">*</span></label>
                <textarea name="reason" class="form-control" rows="3" placeholder="ระบุรายละเอียดเหตุผลในการขอเปลี่ยน PT..." required>{{ old('reason') }}</textarea>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('pt-requests.index') }}" class="btn btn-secondary">ยกเลิก</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send me-1"></i> ส่งคำขอ
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const reasonCards = document.querySelectorAll('.reason-card');
    const reasonText = document.querySelector('textarea[name="reason"]');
    const patientSelect = document.getElementById('patientSelect');
    const originalPtSelect = document.getElementById('originalPtSelect');

    const reasonMap = {
        'schedule': 'ตารางเวลาไม่ตรงกับ PT เดิม',
        'satisfaction': 'ต้องการเปลี่ยน PT เนื่องจากไม่พอใจบริการ',
        'recommendation': 'ได้รับคำแนะนำให้ใช้บริการกับ PT ที่ต้องการ'
    };

    // Reason card selection
    reasonCards.forEach(card => {
        card.addEventListener('click', function() {
            reasonCards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');

            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;

            if (reasonMap[radio.value] && !reasonText.value) {
                reasonText.value = reasonMap[radio.value];
            }
        });
    });

    // Auto-select original PT based on patient
    patientSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const preferredPtId = selectedOption.getAttribute('data-pt');

        if (preferredPtId) {
            originalPtSelect.value = preferredPtId;
        } else {
            originalPtSelect.value = '';
        }
    });
});
</script>
@endpush
