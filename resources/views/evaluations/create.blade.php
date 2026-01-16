@extends('layouts.app')

@section('title', 'สร้างการประเมิน - GCMS')

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .page-header {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
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

    .rating-item {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.75rem;
    }

    .rating-slider {
        width: 100%;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2"><i class="bi bi-clipboard-plus me-2"></i>สร้างการประเมิน</h2>
                <p class="mb-0 opacity-90">สร้างการประเมินพนักงานใหม่</p>
            </div>
            <a href="{{ route('evaluations.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="form-card">
        <form method="POST" action="{{ route('evaluations.store') }}">
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
            <div class="section-title"><i class="bi bi-info-circle me-2"></i>ข้อมูลทั่วไป</div>
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
                    <label class="form-label">ประเภทการประเมิน <span class="text-danger">*</span></label>
                    <select name="evaluation_type" class="form-select" required>
                        <option value="">เลือกประเภท</option>
                        <option value="probation" {{ old('evaluation_type') == 'probation' ? 'selected' : '' }}>ทดลองงาน</option>
                        <option value="quarterly" {{ old('evaluation_type') == 'quarterly' ? 'selected' : '' }}>รายไตรมาส</option>
                        <option value="annual" {{ old('evaluation_type') == 'annual' ? 'selected' : '' }}>ประจำปี</option>
                        <option value="improvement_plan" {{ old('evaluation_type') == 'improvement_plan' ? 'selected' : '' }}>แผนพัฒนา</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">วันที่ประเมิน <span class="text-danger">*</span></label>
                    <input type="date" name="evaluation_date" class="form-control" value="{{ old('evaluation_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">ช่วงเวลาที่ประเมิน</label>
                    <input type="text" name="evaluation_period" class="form-control" value="{{ old('evaluation_period') }}" placeholder="เช่น Q1/2024, ม.ค.-มี.ค. 2567">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ประเมินครั้งถัดไป</label>
                    <input type="date" name="next_evaluation_date" class="form-control" value="{{ old('next_evaluation_date') }}">
                </div>
            </div>

            <!-- Ratings -->
            <div class="section-title"><i class="bi bi-star me-2"></i>คะแนนประเมิน (0-100)</div>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="rating-item">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">คุณภาพงาน</label>
                            <span id="qualityValue" class="badge bg-primary">0</span>
                        </div>
                        <input type="range" name="ratings[quality]" class="form-range rating-slider" min="0" max="100" value="{{ old('ratings.quality', 0) }}" oninput="document.getElementById('qualityValue').textContent = this.value">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="rating-item">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">ประสิทธิภาพการทำงาน</label>
                            <span id="efficiencyValue" class="badge bg-primary">0</span>
                        </div>
                        <input type="range" name="ratings[efficiency]" class="form-range rating-slider" min="0" max="100" value="{{ old('ratings.efficiency', 0) }}" oninput="document.getElementById('efficiencyValue').textContent = this.value">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="rating-item">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">ความรับผิดชอบ</label>
                            <span id="responsibilityValue" class="badge bg-primary">0</span>
                        </div>
                        <input type="range" name="ratings[responsibility]" class="form-range rating-slider" min="0" max="100" value="{{ old('ratings.responsibility', 0) }}" oninput="document.getElementById('responsibilityValue').textContent = this.value">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="rating-item">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">การทำงานเป็นทีม</label>
                            <span id="teamworkValue" class="badge bg-primary">0</span>
                        </div>
                        <input type="range" name="ratings[teamwork]" class="form-range rating-slider" min="0" max="100" value="{{ old('ratings.teamwork', 0) }}" oninput="document.getElementById('teamworkValue').textContent = this.value">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="rating-item">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">ความคิดริเริ่ม</label>
                            <span id="initiativeValue" class="badge bg-primary">0</span>
                        </div>
                        <input type="range" name="ratings[initiative]" class="form-range rating-slider" min="0" max="100" value="{{ old('ratings.initiative', 0) }}" oninput="document.getElementById('initiativeValue').textContent = this.value">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="rating-item">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">การสื่อสาร</label>
                            <span id="communicationValue" class="badge bg-primary">0</span>
                        </div>
                        <input type="range" name="ratings[communication]" class="form-range rating-slider" min="0" max="100" value="{{ old('ratings.communication', 0) }}" oninput="document.getElementById('communicationValue').textContent = this.value">
                    </div>
                </div>
            </div>

            <!-- Overall -->
            <div class="section-title"><i class="bi bi-award me-2"></i>สรุปผล</div>
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label">คะแนนรวม</label>
                    <div class="input-group">
                        <input type="number" name="overall_score" id="overallScore" class="form-control" value="{{ old('overall_score') }}" min="0" max="100" step="0.1" readonly>
                        <span class="input-group-text">/100</span>
                    </div>
                    <small class="text-muted">คำนวณอัตโนมัติจากคะแนนด้านบน</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">ผลการประเมิน</label>
                    <select name="overall_rating" class="form-select">
                        <option value="">เลือกผล</option>
                        <option value="excellent" {{ old('overall_rating') == 'excellent' ? 'selected' : '' }}>ดีเยี่ยม (90-100)</option>
                        <option value="good" {{ old('overall_rating') == 'good' ? 'selected' : '' }}>ดี (80-89)</option>
                        <option value="satisfactory" {{ old('overall_rating') == 'satisfactory' ? 'selected' : '' }}>พอใช้ (70-79)</option>
                        <option value="needs_improvement" {{ old('overall_rating') == 'needs_improvement' ? 'selected' : '' }}>ต้องปรับปรุง (60-69)</option>
                        <option value="unsatisfactory" {{ old('overall_rating') == 'unsatisfactory' ? 'selected' : '' }}>ไม่ผ่าน (<60)</option>
                    </select>
                </div>
            </div>

            <!-- Comments -->
            <div class="section-title"><i class="bi bi-chat-left-text me-2"></i>ความคิดเห็น</div>
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label">จุดแข็ง</label>
                    <textarea name="strengths" class="form-control" rows="3" placeholder="ระบุจุดแข็งของพนักงาน...">{{ old('strengths') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">จุดที่ต้องพัฒนา</label>
                    <textarea name="areas_for_improvement" class="form-control" rows="3" placeholder="ระบุจุดที่ต้องพัฒนา...">{{ old('areas_for_improvement') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">เป้าหมาย</label>
                    <textarea name="goals" class="form-control" rows="3" placeholder="ระบุเป้าหมาย...">{{ old('goals') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">แผนการพัฒนา</label>
                    <textarea name="action_plan" class="form-control" rows="3" placeholder="ระบุแผนการพัฒนา...">{{ old('action_plan') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">ความเห็นผู้ประเมิน</label>
                    <textarea name="evaluator_comments" class="form-control" rows="3" placeholder="ความเห็นเพิ่มเติม...">{{ old('evaluator_comments') }}</textarea>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('evaluations.index') }}" class="btn btn-secondary">ยกเลิก</a>
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
    const sliders = document.querySelectorAll('.rating-slider');
    const overallScore = document.getElementById('overallScore');
    const overallRating = document.querySelector('select[name="overall_rating"]');

    function calculateOverall() {
        let total = 0;
        let count = 0;

        sliders.forEach(slider => {
            const value = parseInt(slider.value);
            if (value > 0) {
                total += value;
                count++;
            }
        });

        const avg = count > 0 ? (total / count).toFixed(1) : 0;
        overallScore.value = avg;

        // Auto-select rating based on score
        if (avg >= 90) overallRating.value = 'excellent';
        else if (avg >= 80) overallRating.value = 'good';
        else if (avg >= 70) overallRating.value = 'satisfactory';
        else if (avg >= 60) overallRating.value = 'needs_improvement';
        else if (avg > 0) overallRating.value = 'unsatisfactory';
    }

    sliders.forEach(slider => {
        slider.addEventListener('input', calculateOverall);
    });

    calculateOverall();
});
</script>
@endpush
