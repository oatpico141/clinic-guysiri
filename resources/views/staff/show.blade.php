@extends('layouts.app')

@section('title', 'รายละเอียดพนักงาน - GCMS')

@push('styles')
<style>
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

    .page-header {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        border-radius: 20px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
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

    .badge-active { background: #d1fae5; color: #065f46; }
    .badge-on_leave { background: #fef3c7; color: #92400e; }
    .badge-terminated { background: #fee2e2; color: #991b1b; }

    .schedule-item {
        border-left: 3px solid #8b5cf6;
        padding-left: 1rem;
        margin-bottom: 1rem;
    }

    .leave-item {
        border-left: 3px solid #f59e0b;
        padding-left: 1rem;
        margin-bottom: 1rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <a href="{{ route('staff.index') }}" class="btn btn-sm btn-light mb-3">
                    <i class="bi bi-arrow-left me-1"></i> กลับ
                </a>
                <h2 class="mb-2"><i class="bi bi-person me-2"></i>{{ $staff->first_name }} {{ $staff->last_name }}</h2>
                <p class="mb-0 opacity-90">
                    รหัส: {{ $staff->employee_id }} |
                    @switch($staff->position)
                        @case('pt') PT @break
                        @case('receptionist') พนักงานต้อนรับ @break
                        @case('manager') ผู้จัดการ @break
                        @case('admin') Admin @break
                        @case('nurse') พยาบาล @break
                        @case('assistant') ผู้ช่วย @break
                        @default {{ $staff->position }}
                    @endswitch
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="{{ route('staff.edit', $staff) }}" class="btn btn-light">
                    <i class="bi bi-pencil me-1"></i> แก้ไข
                </a>
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
            <!-- Personal Info -->
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-person me-2"></i>ข้อมูลส่วนตัว</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="info-label">รหัสพนักงาน</div>
                            <div class="info-value"><code>{{ $staff->employee_id }}</code></div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">ชื่อ-นามสกุล</div>
                            <div class="info-value">{{ $staff->first_name }} {{ $staff->last_name }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">เพศ</div>
                            <div class="info-value">
                                @switch($staff->gender)
                                    @case('male') ชาย @break
                                    @case('female') หญิง @break
                                    @case('other') อื่นๆ @break
                                    @default -
                                @endswitch
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">วันเกิด</div>
                            <div class="info-value">{{ $staff->date_of_birth?->format('d/m/Y') ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">เบอร์โทรศัพท์</div>
                            <div class="info-value">{{ $staff->phone ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">อีเมล</div>
                            <div class="info-value">{{ $staff->email ?? '-' }}</div>
                        </div>
                        @if($staff->address)
                        <div class="col-12">
                            <div class="info-label">ที่อยู่</div>
                            <div class="info-value">{{ $staff->address }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Employment Info -->
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-briefcase me-2"></i>ข้อมูลการจ้างงาน</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="info-label">สาขา</div>
                            <div class="info-value">{{ $staff->branch->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">ตำแหน่ง</div>
                            <div class="info-value">
                                @switch($staff->position)
                                    @case('pt')
                                        <span class="badge bg-primary">PT</span>
                                        @break
                                    @case('receptionist')
                                        <span class="badge bg-info">พนักงานต้อนรับ</span>
                                        @break
                                    @case('manager')
                                        <span class="badge" style="background: #7c3aed; color: white;">ผู้จัดการ</span>
                                        @break
                                    @case('admin')
                                        <span class="badge bg-dark">Admin</span>
                                        @break
                                    @case('nurse')
                                        <span class="badge bg-success">พยาบาล</span>
                                        @break
                                    @case('assistant')
                                        <span class="badge bg-secondary">ผู้ช่วย</span>
                                        @break
                                @endswitch
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">แผนก</div>
                            <div class="info-value">{{ $staff->department ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">วันที่เริ่มงาน</div>
                            <div class="info-value">{{ $staff->hire_date?->format('d/m/Y') ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">ประเภทการจ้างงาน</div>
                            <div class="info-value">
                                @switch($staff->employment_type)
                                    @case('full_time') พนักงานประจำ @break
                                    @case('part_time') พาร์ทไทม์ @break
                                    @case('contract') สัญญาจ้าง @break
                                    @default -
                                @endswitch
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">สถานะการจ้างงาน</div>
                            <div class="info-value">
                                @switch($staff->employment_status)
                                    @case('active')
                                        <span class="badge badge-active">ทำงานอยู่</span>
                                        @break
                                    @case('on_leave')
                                        <span class="badge badge-on_leave">ลางาน</span>
                                        @break
                                    @case('terminated')
                                        <span class="badge badge-terminated">พ้นสภาพ</span>
                                        @break
                                @endswitch
                            </div>
                        </div>
                        @if($staff->termination_date)
                        <div class="col-md-4">
                            <div class="info-label">วันที่พ้นสภาพ</div>
                            <div class="info-value text-danger">{{ $staff->termination_date?->format('d/m/Y') }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- License Info (for PT) -->
            @if($staff->position === 'pt' || $staff->license_number)
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-file-earmark-medical me-2"></i>ข้อมูลใบอนุญาต</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-label">เลขที่ใบอนุญาต</div>
                            <div class="info-value">{{ $staff->license_number ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">วันหมดอายุ</div>
                            <div class="info-value">
                                @if($staff->license_expiry)
                                    {{ $staff->license_expiry->format('d/m/Y') }}
                                    @if($staff->license_expiry < now())
                                        <span class="badge bg-danger">หมดอายุแล้ว</span>
                                    @elseif($staff->license_expiry <= now()->addDays(30))
                                        <span class="badge bg-warning">ใกล้หมดอายุ</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Salary Info -->
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-cash me-2"></i>ข้อมูลเงินเดือน</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-label">ประเภทเงินเดือน</div>
                            <div class="info-value">
                                @switch($staff->salary_type)
                                    @case('monthly') รายเดือน @break
                                    @case('hourly') รายชั่วโมง @break
                                    @case('commission_only') คอมมิชชั่นอย่างเดียว @break
                                    @default -
                                @endswitch
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">เงินเดือนพื้นฐาน</div>
                            <div class="info-value">{{ $staff->base_salary ? '฿' . number_format($staff->base_salary, 2) : '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($staff->notes)
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-sticky me-2"></i>หมายเหตุ</h6>
                </div>
                <div class="card-body">
                    {{ $staff->notes }}
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- User Account -->
            @if($staff->user)
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-person-circle me-2"></i>บัญชีผู้ใช้</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <div class="info-label">ชื่อผู้ใช้</div>
                        <div class="info-value">{{ $staff->user->name }}</div>
                    </div>
                    <div>
                        <div class="info-label">อีเมล</div>
                        <div class="info-value">{{ $staff->user->email }}</div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Recent Schedules -->
            @if($staff->schedules && $staff->schedules->count() > 0)
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-calendar3 me-2"></i>ตารางงานล่าสุด</h6>
                </div>
                <div class="card-body">
                    @foreach($staff->schedules->take(5) as $schedule)
                    <div class="schedule-item">
                        <div class="fw-bold">{{ $schedule->schedule_date?->format('d/m/Y') }}</div>
                        <div class="small text-muted">
                            {{ $schedule->start_time }} - {{ $schedule->end_time }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Recent Leave Requests -->
            @if($staff->leaveRequests && $staff->leaveRequests->count() > 0)
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-calendar-x me-2"></i>การลาล่าสุด</h6>
                </div>
                <div class="card-body">
                    @foreach($staff->leaveRequests->take(5) as $leave)
                    <div class="leave-item">
                        <div class="fw-bold">{{ $leave->start_date?->format('d/m/Y') }} - {{ $leave->end_date?->format('d/m/Y') }}</div>
                        <div class="small text-muted">{{ $leave->leave_type }}</div>
                        <div class="small">
                            @switch($leave->status)
                                @case('approved')
                                    <span class="badge bg-success">อนุมัติ</span>
                                    @break
                                @case('pending')
                                    <span class="badge bg-warning">รออนุมัติ</span>
                                    @break
                                @case('rejected')
                                    <span class="badge bg-danger">ไม่อนุมัติ</span>
                                    @break
                            @endswitch
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Recent Evaluations -->
            @if($staff->evaluations && $staff->evaluations->count() > 0)
            <div class="detail-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-star me-2"></i>การประเมินล่าสุด</h6>
                </div>
                <div class="card-body">
                    @foreach($staff->evaluations->take(3) as $evaluation)
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="fw-bold">{{ $evaluation->evaluation_date?->format('d/m/Y') }}</div>
                        <div class="small text-muted">{{ $evaluation->evaluation_type }}</div>
                        @if($evaluation->overall_score)
                        <div class="small">
                            คะแนน: <span class="fw-bold">{{ $evaluation->overall_score }}/100</span>
                        </div>
                        @endif
                    </div>
                    @endforeach
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
                        <div class="info-value small">{{ $staff->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="info-label">แก้ไขล่าสุด</div>
                        <div class="info-value small">{{ $staff->updated_at?->format('d/m/Y H:i') ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
