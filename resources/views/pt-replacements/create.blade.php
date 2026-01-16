@extends('layouts.app')

@section('title', 'บันทึกการแทน PT - GCMS')

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .page-header {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
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

    .pt-swap-visual {
        background: #f8fafc;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .pt-select-box {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border: 2px dashed #e5e7eb;
        transition: all 0.3s;
    }

    .pt-select-box:hover {
        border-color: #f97316;
    }

    .swap-arrow {
        font-size: 2rem;
        color: #f97316;
    }

    .commission-option {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.3s;
    }

    .commission-option:hover {
        border-color: #f97316;
    }

    .commission-option.selected {
        border-color: #f97316;
        background: #fff7ed;
    }

    .commission-option input[type="radio"] {
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
                <h2 class="mb-2"><i class="bi bi-arrow-left-right me-2"></i>บันทึกการแทน PT</h2>
                <p class="mb-0 opacity-90">บันทึกข้อมูลเมื่อมีการแทน PT ในการให้บริการ</p>
            </div>
            <a href="{{ route('pt-replacements.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="form-card">
        <form method="POST" action="{{ route('pt-replacements.store') }}">
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

            <!-- PT Swap Visual -->
            <div class="section-title"><i class="bi bi-people me-2"></i>เลือก PT</div>
            <div class="pt-swap-visual">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <div class="pt-select-box">
                            <label class="form-label fw-semibold text-danger">
                                <i class="bi bi-person-x me-1"></i> PT ที่ถูกแทน <span class="text-danger">*</span>
                            </label>
                            <select name="original_pt_id" class="form-select form-select-lg" required>
                                <option value="">เลือก PT ที่ถูกแทน</option>
                                @foreach($pts as $pt)
                                <option value="{{ $pt->id }}" {{ old('original_pt_id') == $pt->id ? 'selected' : '' }}>
                                    {{ $pt->first_name }} {{ $pt->last_name }}
                                </option>
                                @endforeach
                            </select>
                            <small class="text-muted">PT ที่ไม่สามารถให้บริการได้</small>
                        </div>
                    </div>
                    <div class="col-md-2 text-center py-3">
                        <i class="bi bi-arrow-right swap-arrow"></i>
                    </div>
                    <div class="col-md-5">
                        <div class="pt-select-box">
                            <label class="form-label fw-semibold text-success">
                                <i class="bi bi-person-check me-1"></i> PT ที่แทน <span class="text-danger">*</span>
                            </label>
                            <select name="replacement_pt_id" class="form-select form-select-lg" required>
                                <option value="">เลือก PT ที่แทน</option>
                                @foreach($pts as $pt)
                                <option value="{{ $pt->id }}" {{ old('replacement_pt_id') == $pt->id ? 'selected' : '' }}>
                                    {{ $pt->first_name }} {{ $pt->last_name }}
                                </option>
                                @endforeach
                            </select>
                            <small class="text-muted">PT ที่มาแทนให้บริการ</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Details -->
            <div class="section-title"><i class="bi bi-info-circle me-2"></i>รายละเอียด</div>
            <div class="row g-4 mb-4">
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
                    <label class="form-label">วันที่แทน <span class="text-danger">*</span></label>
                    <input type="date" name="replacement_date" class="form-control" value="{{ old('replacement_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">เหตุผลการแทน <span class="text-danger">*</span></label>
                    <select name="reason_type" class="form-select" id="reasonType">
                        <option value="">เลือกเหตุผล</option>
                        <option value="sick" {{ old('reason_type') == 'sick' ? 'selected' : '' }}>ลาป่วย</option>
                        <option value="leave" {{ old('reason_type') == 'leave' ? 'selected' : '' }}>ลากิจ</option>
                        <option value="vacation" {{ old('reason_type') == 'vacation' ? 'selected' : '' }}>ลาพักร้อน</option>
                        <option value="training" {{ old('reason_type') == 'training' ? 'selected' : '' }}>อบรม</option>
                        <option value="emergency" {{ old('reason_type') == 'emergency' ? 'selected' : '' }}>เหตุฉุกเฉิน</option>
                        <option value="other" {{ old('reason_type') == 'other' ? 'selected' : '' }}>อื่นๆ</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">รายละเอียดเหตุผล <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control" rows="2" placeholder="ระบุเหตุผลในการแทน..." required>{{ old('reason') }}</textarea>
                </div>
            </div>

            <!-- Commission Handling -->
            <div class="section-title"><i class="bi bi-cash me-2"></i>การจัดการค่าคอมมิชชัน</div>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="commission-option d-block" id="optionOriginal">
                        <input type="radio" name="commission_handling" value="original" {{ old('commission_handling', 'original') == 'original' ? 'checked' : '' }}>
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-person-x fs-3 text-danger"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">PT เดิม</div>
                                <small class="text-muted">ค่าคอมมิชชันทั้งหมดไป PT ที่ถูกแทน</small>
                            </div>
                        </div>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="commission-option d-block" id="optionReplacement">
                        <input type="radio" name="commission_handling" value="replacement" {{ old('commission_handling') == 'replacement' ? 'checked' : '' }}>
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-person-check fs-3 text-success"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">PT แทน</div>
                                <small class="text-muted">ค่าคอมมิชชันทั้งหมดไป PT ที่มาแทน</small>
                            </div>
                        </div>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="commission-option d-block" id="optionSplit">
                        <input type="radio" name="commission_handling" value="split" {{ old('commission_handling') == 'split' ? 'checked' : '' }}>
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-pie-chart fs-3 text-warning"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">แบ่งกัน</div>
                                <small class="text-muted">แบ่งค่าคอมมิชชันตามสัดส่วน</small>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Split Percentage -->
            <div class="row mb-4" id="splitPercentageRow" style="display: none;">
                <div class="col-md-6">
                    <label class="form-label">สัดส่วนที่ PT แทนได้รับ (%)</label>
                    <div class="input-group">
                        <input type="number" name="commission_split_percentage" class="form-control" value="{{ old('commission_split_percentage', 50) }}" min="0" max="100">
                        <span class="input-group-text">%</span>
                    </div>
                    <small class="text-muted">PT เดิมจะได้รับส่วนที่เหลือ</small>
                </div>
            </div>

            <!-- Notes -->
            <div class="section-title"><i class="bi bi-sticky me-2"></i>หมายเหตุ</div>
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <textarea name="notes" class="form-control" rows="3" placeholder="หมายเหตุเพิ่มเติม (ถ้ามี)...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('pt-replacements.index') }}" class="btn btn-secondary">ยกเลิก</a>
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
    const commissionOptions = document.querySelectorAll('.commission-option');
    const splitPercentageRow = document.getElementById('splitPercentageRow');

    function updateCommissionUI() {
        commissionOptions.forEach(opt => {
            const radio = opt.querySelector('input[type="radio"]');
            if (radio.checked) {
                opt.classList.add('selected');
            } else {
                opt.classList.remove('selected');
            }
        });

        const splitRadio = document.querySelector('input[name="commission_handling"][value="split"]');
        splitPercentageRow.style.display = splitRadio.checked ? 'block' : 'none';
    }

    commissionOptions.forEach(opt => {
        opt.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
            updateCommissionUI();
        });
    });

    updateCommissionUI();

    // Auto-fill reason based on type
    const reasonType = document.getElementById('reasonType');
    const reasonText = document.querySelector('textarea[name="reason"]');
    const reasonMap = {
        'sick': 'PT ลาป่วย',
        'leave': 'PT ลากิจ',
        'vacation': 'PT ลาพักร้อน',
        'training': 'PT ไปอบรม',
        'emergency': 'มีเหตุฉุกเฉิน'
    };

    reasonType.addEventListener('change', function() {
        if (reasonMap[this.value] && !reasonText.value) {
            reasonText.value = reasonMap[this.value];
        }
    });
});
</script>
@endpush
