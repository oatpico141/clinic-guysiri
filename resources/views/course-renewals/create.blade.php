@extends('layouts.app')

@section('title', 'ต่ออายุคอร์ส - GCMS')

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .page-header {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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

    .course-select-card {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1.5rem;
        border: 2px dashed #e5e7eb;
        transition: all 0.3s;
    }

    .course-select-card:hover {
        border-color: #10b981;
    }

    .course-select-card.selected {
        border-color: #10b981;
        background: #f0fdf4;
    }

    .expiring-course-item {
        background: #fffbeb;
        border-radius: 8px;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        cursor: pointer;
        transition: all 0.2s;
        border-left: 3px solid #f59e0b;
    }

    .expiring-course-item:hover {
        background: #fef3c7;
    }

    .expiring-course-item.expired {
        background: #fef2f2;
        border-left-color: #dc2626;
    }

    .extension-option {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 1rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
    }

    .extension-option:hover {
        border-color: #10b981;
    }

    .extension-option.selected {
        border-color: #10b981;
        background: #f0fdf4;
    }

    .extension-option input[type="radio"] {
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
                <h2 class="mb-2"><i class="bi bi-arrow-repeat me-2"></i>ต่ออายุคอร์ส</h2>
                <p class="mb-0 opacity-90">ขยายระยะเวลาการใช้งานคอร์ส</p>
            </div>
            <a href="{{ route('course-renewals.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Form -->
            <div class="form-card">
                <form method="POST" action="{{ route('course-renewals.store') }}">
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

                    <!-- Course Selection -->
                    <div class="section-title"><i class="bi bi-box me-2"></i>เลือกคอร์สที่ต้องการต่ออายุ</div>
                    <div class="course-select-card mb-4 {{ $selectedPurchase ? 'selected' : '' }}" id="courseSelectCard">
                        @if($selectedPurchase)
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #10b981, #059669); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                                    <i class="bi bi-box"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1">{{ $selectedPurchase->coursePackage->name ?? '-' }}</h5>
                                <small class="text-muted">{{ $selectedPurchase->patient->name ?? '-' }}</small>
                                <br>
                                <small class="text-danger">
                                    <i class="bi bi-calendar-x me-1"></i>
                                    หมดอายุ: {{ $selectedPurchase->expiry_date?->format('d/m/Y') ?? '-' }}
                                </small>
                            </div>
                        </div>
                        <input type="hidden" name="course_purchase_id" value="{{ $selectedPurchase->id }}">
                        @else
                        <select name="course_purchase_id" class="form-select form-select-lg" id="coursePurchaseSelect" required>
                            <option value="">เลือกคอร์ส...</option>
                        </select>
                        <small class="text-muted d-block mt-2">เลือกผู้ป่วยก่อนเพื่อดูรายการคอร์ส</small>
                        @endif
                    </div>

                    @if(!$selectedPurchase)
                    <div class="mb-4">
                        <label class="form-label">ค้นหาผู้ป่วย</label>
                        <select id="patientSelect" class="form-select">
                            <option value="">เลือกผู้ป่วย</option>
                            @foreach($patients as $patient)
                            <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Extension Days -->
                    <div class="section-title"><i class="bi bi-calendar-plus me-2"></i>จำนวนวันที่ต้องการต่ออายุ</div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-6">
                            <label class="extension-option d-block">
                                <input type="radio" name="extension_days" value="30" {{ old('extension_days') == '30' ? 'checked' : '' }}>
                                <div class="fs-4 fw-bold text-success">30</div>
                                <div class="text-muted">วัน</div>
                            </label>
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="extension-option d-block">
                                <input type="radio" name="extension_days" value="60" {{ old('extension_days') == '60' ? 'checked' : '' }}>
                                <div class="fs-4 fw-bold text-success">60</div>
                                <div class="text-muted">วัน</div>
                            </label>
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="extension-option d-block">
                                <input type="radio" name="extension_days" value="90" {{ old('extension_days', '90') == '90' ? 'checked' : '' }}>
                                <div class="fs-4 fw-bold text-success">90</div>
                                <div class="text-muted">วัน</div>
                            </label>
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="extension-option d-block">
                                <input type="radio" name="extension_days" value="custom" id="customRadio">
                                <div class="fs-6 fw-bold text-success">กำหนดเอง</div>
                                <input type="number" id="customDays" class="form-control form-control-sm mt-2" min="1" max="365" placeholder="วัน" style="display: none;">
                            </label>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="section-title"><i class="bi bi-info-circle me-2"></i>รายละเอียด</div>
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">สาขา <span class="text-danger">*</span></label>
                            <select name="branch_id" class="form-select" required>
                                <option value="">เลือกสาขา</option>
                                @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">วันที่ต่ออายุ <span class="text-danger">*</span></label>
                            <input type="date" name="renewal_date" class="form-control" value="{{ old('renewal_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ค่าธรรมเนียม (บาท)</label>
                            <div class="input-group">
                                <span class="input-group-text">฿</span>
                                <input type="number" name="renewal_fee" class="form-control" value="{{ old('renewal_fee', 0) }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">เหตุผลในการต่ออายุ</label>
                            <textarea name="renewal_reason" class="form-control" rows="2" placeholder="เช่น ลูกค้าขอต่ออายุ, ไม่สามารถมาใช้บริการได้ตามกำหนด...">{{ old('renewal_reason') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">หมายเหตุ</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('course-renewals.index') }}" class="btn btn-secondary">ยกเลิก</a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg me-1"></i> ต่ออายุ
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Expiring Courses -->
            @if($expiringCourses->count() > 0)
            <div class="form-card">
                <div class="section-title"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>คอร์สใกล้หมดอายุ</div>
                @foreach($expiringCourses as $course)
                <div class="expiring-course-item {{ $course->expiry_date < now() ? 'expired' : '' }}" onclick="selectCourse('{{ $course->id }}')">
                    <div class="fw-semibold">{{ $course->coursePackage->name ?? '-' }}</div>
                    <small class="text-muted">{{ $course->patient->name ?? '-' }}</small>
                    <div class="mt-1">
                        @if($course->expiry_date < now())
                        <span class="badge bg-danger">หมดอายุแล้ว {{ $course->expiry_date?->diffForHumans() }}</span>
                        @else
                        <span class="badge bg-warning text-dark">เหลือ {{ $course->expiry_date?->diffForHumans() }}</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Extension option selection
    const extensionOptions = document.querySelectorAll('.extension-option');
    const customRadio = document.getElementById('customRadio');
    const customDays = document.getElementById('customDays');

    function updateExtensionUI() {
        extensionOptions.forEach(opt => {
            const radio = opt.querySelector('input[type="radio"]');
            if (radio.checked) {
                opt.classList.add('selected');
            } else {
                opt.classList.remove('selected');
            }
        });

        // Show/hide custom input
        if (customRadio && customRadio.checked) {
            customDays.style.display = 'block';
            customDays.name = 'extension_days';
            customRadio.name = '';
        } else if (customDays) {
            customDays.style.display = 'none';
            customDays.name = '';
            if (customRadio) customRadio.name = 'extension_days';
        }
    }

    extensionOptions.forEach(opt => {
        opt.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
            updateExtensionUI();
        });
    });

    updateExtensionUI();

    // Patient selection - load courses
    const patientSelect = document.getElementById('patientSelect');
    const coursePurchaseSelect = document.getElementById('coursePurchaseSelect');

    if (patientSelect && coursePurchaseSelect) {
        patientSelect.addEventListener('change', function() {
            const patientId = this.value;
            if (!patientId) {
                coursePurchaseSelect.innerHTML = '<option value="">เลือกคอร์ส...</option>';
                return;
            }

            // Fetch patient courses
            fetch(`/api/patient-courses/${patientId}`)
                .then(response => response.json())
                .then(data => {
                    let options = '<option value="">เลือกคอร์ส...</option>';
                    if (data.courses) {
                        data.courses.forEach(course => {
                            const expiry = course.expiry_date ? new Date(course.expiry_date).toLocaleDateString('th-TH') : '-';
                            options += `<option value="${course.id}">${course.course_package?.name || '-'} (หมดอายุ: ${expiry})</option>`;
                        });
                    }
                    coursePurchaseSelect.innerHTML = options;
                });
        });
    }
});

function selectCourse(id) {
    window.location.href = '{{ route("course-renewals.create") }}?course_purchase_id=' + id;
}
</script>
@endpush
