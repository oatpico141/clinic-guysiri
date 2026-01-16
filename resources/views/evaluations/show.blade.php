@extends('layouts.app')

@section('title', 'รายละเอียดการประเมิน - GCMS')

@push('styles')
<style>
    .eval-header {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border-radius: 20px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }

    .detail-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        margin-bottom: 1.5rem;
    }

    .detail-card .card-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 1rem 1.5rem;
        border-radius: 16px 16px 0 0;
    }

    .detail-card .card-body {
        padding: 1.5rem;
    }

    .info-label {
        font-size: 0.8rem;
        color: #64748b;
        margin-bottom: 0.25rem;
    }

    .info-value {
        font-weight: 500;
        color: #1e293b;
    }

    .badge-draft { background: #e5e7eb; color: #374151; }
    .badge-pending { background: #fef3c7; color: #92400e; }
    .badge-completed { background: #d1fae5; color: #065f46; }

    .score-circle {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }

    .score-excellent { background: linear-gradient(135deg, #10b981, #059669); color: white; }
    .score-good { background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; }
    .score-satisfactory { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; }
    .score-needs-improvement { background: linear-gradient(135deg, #f97316, #ea580c); color: white; }
    .score-unsatisfactory { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; }

    .rating-bar {
        height: 8px;
        border-radius: 4px;
        background: #e5e7eb;
        overflow: hidden;
    }

    .rating-bar-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.3s;
    }

    .rating-bar-fill.excellent { background: #10b981; }
    .rating-bar-fill.good { background: #3b82f6; }
    .rating-bar-fill.satisfactory { background: #f59e0b; }
    .rating-bar-fill.poor { background: #ef4444; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="eval-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <a href="{{ route('evaluations.index') }}" class="btn btn-sm btn-light mb-3">
                    <i class="bi bi-arrow-left me-1"></i> กลับ
                </a>
                <h2 class="mb-2"><i class="bi bi-clipboard-check me-2"></i>การประเมิน</h2>
                <p class="mb-0 opacity-90">
                    {{ $evaluation->staff->first_name ?? '' }} {{ $evaluation->staff->last_name ?? '' }} |
                    {{ $evaluation->evaluation_date?->format('d/m/Y') }}
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                @if($evaluation->status !== 'completed')
                <a href="{{ route('evaluations.edit', $evaluation) }}" class="btn btn-light">
                    <i class="bi bi-pencil me-1"></i> แก้ไข
                </a>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Basic Info -->
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>ข้อมูลการประเมิน</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-label">พนักงาน</div>
                            <div class="info-value">
                                @if($evaluation->staff)
                                <a href="{{ route('staff.show', $evaluation->staff) }}">
                                    {{ $evaluation->staff->first_name }} {{ $evaluation->staff->last_name }}
                                </a>
                                @else
                                -
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">สาขา</div>
                            <div class="info-value">{{ $evaluation->branch->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">ประเภทการประเมิน</div>
                            <div class="info-value">
                                @switch($evaluation->evaluation_type)
                                    @case('probation')
                                        <span class="badge bg-info">ทดลองงาน</span>
                                        @break
                                    @case('quarterly')
                                        <span class="badge bg-primary">รายไตรมาส</span>
                                        @break
                                    @case('annual')
                                        <span class="badge bg-success">ประจำปี</span>
                                        @break
                                    @case('improvement_plan')
                                        <span class="badge bg-warning">แผนพัฒนา</span>
                                        @break
                                @endswitch
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">สถานะ</div>
                            <div class="info-value">
                                @switch($evaluation->status)
                                    @case('draft')
                                        <span class="badge badge-draft">แบบร่าง</span>
                                        @break
                                    @case('pending')
                                        <span class="badge badge-pending">รอดำเนินการ</span>
                                        @break
                                    @case('completed')
                                        <span class="badge badge-completed">เสร็จสิ้น</span>
                                        @break
                                @endswitch
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">วันที่ประเมิน</div>
                            <div class="info-value">{{ $evaluation->evaluation_date?->format('d/m/Y') ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">ช่วงเวลาที่ประเมิน</div>
                            <div class="info-value">{{ $evaluation->evaluation_period ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">ผู้ประเมิน</div>
                            <div class="info-value">{{ $evaluation->evaluator->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">ประเมินครั้งถัดไป</div>
                            <div class="info-value">{{ $evaluation->next_evaluation_date?->format('d/m/Y') ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ratings -->
            @if($evaluation->ratings)
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>คะแนนรายด้าน</h6>
                </div>
                <div class="card-body">
                    @php
                        $ratingLabels = [
                            'quality' => 'คุณภาพงาน',
                            'efficiency' => 'ประสิทธิภาพการทำงาน',
                            'responsibility' => 'ความรับผิดชอบ',
                            'teamwork' => 'การทำงานเป็นทีม',
                            'initiative' => 'ความคิดริเริ่ม',
                            'communication' => 'การสื่อสาร',
                        ];
                    @endphp
                    @foreach($evaluation->ratings as $key => $value)
                    @if(isset($ratingLabels[$key]) && $value)
                    @php
                        $barClass = 'poor';
                        if ($value >= 90) $barClass = 'excellent';
                        elseif ($value >= 80) $barClass = 'good';
                        elseif ($value >= 70) $barClass = 'satisfactory';
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ $ratingLabels[$key] }}</span>
                            <span class="fw-bold">{{ $value }}</span>
                        </div>
                        <div class="rating-bar">
                            <div class="rating-bar-fill {{ $barClass }}" style="width: {{ $value }}%"></div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Comments -->
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>ความคิดเห็น</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        @if($evaluation->strengths)
                        <div class="col-md-6">
                            <div class="info-label"><i class="bi bi-check-circle text-success me-1"></i> จุดแข็ง</div>
                            <div class="info-value">{{ $evaluation->strengths }}</div>
                        </div>
                        @endif
                        @if($evaluation->areas_for_improvement)
                        <div class="col-md-6">
                            <div class="info-label"><i class="bi bi-arrow-up-circle text-warning me-1"></i> จุดที่ต้องพัฒนา</div>
                            <div class="info-value">{{ $evaluation->areas_for_improvement }}</div>
                        </div>
                        @endif
                        @if($evaluation->goals)
                        <div class="col-md-6">
                            <div class="info-label"><i class="bi bi-bullseye text-primary me-1"></i> เป้าหมาย</div>
                            <div class="info-value">{{ $evaluation->goals }}</div>
                        </div>
                        @endif
                        @if($evaluation->action_plan)
                        <div class="col-md-6">
                            <div class="info-label"><i class="bi bi-list-check text-info me-1"></i> แผนการพัฒนา</div>
                            <div class="info-value">{{ $evaluation->action_plan }}</div>
                        </div>
                        @endif
                        @if($evaluation->evaluator_comments)
                        <div class="col-12">
                            <div class="info-label"><i class="bi bi-person-badge me-1"></i> ความเห็นผู้ประเมิน</div>
                            <div class="info-value">{{ $evaluation->evaluator_comments }}</div>
                        </div>
                        @endif
                        @if($evaluation->staff_comments)
                        <div class="col-12">
                            <div class="info-label"><i class="bi bi-person me-1"></i> ความเห็นพนักงาน</div>
                            <div class="info-value">{{ $evaluation->staff_comments }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Score -->
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-award me-2"></i>ผลการประเมิน</h6>
                </div>
                <div class="card-body text-center">
                    @php
                        $scoreClass = 'score-satisfactory';
                        $ratingText = 'พอใช้';
                        if ($evaluation->overall_score >= 90) { $scoreClass = 'score-excellent'; $ratingText = 'ดีเยี่ยม'; }
                        elseif ($evaluation->overall_score >= 80) { $scoreClass = 'score-good'; $ratingText = 'ดี'; }
                        elseif ($evaluation->overall_score >= 70) { $scoreClass = 'score-satisfactory'; $ratingText = 'พอใช้'; }
                        elseif ($evaluation->overall_score >= 60) { $scoreClass = 'score-needs-improvement'; $ratingText = 'ต้องปรับปรุง'; }
                        else { $scoreClass = 'score-unsatisfactory'; $ratingText = 'ไม่ผ่าน'; }
                    @endphp
                    <div class="score-circle {{ $scoreClass }}">
                        <div class="fs-1 fw-bold">{{ number_format($evaluation->overall_score ?? 0, 0) }}</div>
                        <div class="small">คะแนน</div>
                    </div>
                    <div class="mt-3">
                        <span class="badge {{ $scoreClass }} fs-6 px-3 py-2">{{ $ratingText }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            @if($evaluation->status !== 'completed')
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>การดำเนินการ</h6>
                </div>
                <div class="card-body d-grid gap-2">
                    <button type="button" class="btn btn-success" onclick="completeEvaluation()">
                        <i class="bi bi-check-circle me-1"></i> บันทึกการประเมิน
                    </button>
                    <a href="{{ route('evaluations.edit', $evaluation) }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i> แก้ไข
                    </a>
                    <button type="button" class="btn btn-outline-danger" onclick="deleteEvaluation()">
                        <i class="bi bi-trash me-1"></i> ลบ
                    </button>
                </div>
            </div>
            @endif

            <!-- Metadata -->
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>ข้อมูลระบบ</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <div class="info-label">สร้างเมื่อ</div>
                        <div class="info-value small">{{ $evaluation->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="info-label">แก้ไขล่าสุด</div>
                        <div class="info-value small">{{ $evaluation->updated_at?->format('d/m/Y H:i') ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const evalId = '{{ $evaluation->id }}';

function completeEvaluation() {
    if (confirm('ยืนยันการบันทึกการประเมิน? หลังจากบันทึกแล้วจะไม่สามารถแก้ไขได้')) {
        fetch(`/evaluations/${evalId}/complete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
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

function deleteEvaluation() {
    if (confirm('ยืนยันการลบการประเมินนี้?')) {
        fetch(`/evaluations/${evalId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route('evaluations.index') }}';
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
