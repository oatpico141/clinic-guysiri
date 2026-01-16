@extends('layouts.app')

@section('title', 'ตารางงาน PT - GCMS')

@push('styles')
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, var(--calm-blue-500, #3b82f6) 0%, var(--calm-blue-600, #2563eb) 100%);
    }
    .stat-card {
        border-left: 4px solid;
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
    }
    .stat-card.blue { border-left-color: var(--calm-blue-500, #3b82f6); }
    .stat-card.green { border-left-color: #10b981; }
    .stat-card.orange { border-left-color: #f59e0b; }
    .btn-primary {
        background-color: var(--calm-blue-500, #3b82f6);
        border-color: var(--calm-blue-500, #3b82f6);
    }
    .btn-primary:hover {
        background-color: var(--calm-blue-600, #2563eb);
        border-color: var(--calm-blue-600, #2563eb);
    }
    .text-primary {
        color: var(--calm-blue-500, #3b82f6) !important;
    }
    .modal-header.bg-gradient-primary {
        background: linear-gradient(135deg, var(--calm-blue-500, #3b82f6) 0%, var(--calm-blue-600, #2563eb) 100%);
    }

    /* Calendar Styles */
    .calendar-container {
        overflow-x: auto;
    }
    .calendar-table {
        min-width: 800px;
    }
    .calendar-table th {
        background: #f8f9fa;
        font-weight: 600;
        text-align: center;
        padding: 12px 8px;
        border: 1px solid #dee2e6;
    }
    .calendar-table th.today {
        background: var(--calm-blue-100, #dbeafe);
        color: var(--calm-blue-700, #1d4ed8);
    }
    .calendar-table td {
        vertical-align: top;
        padding: 8px;
        border: 1px solid #dee2e6;
        min-height: 100px;
        width: 14.28%;
    }
    .calendar-table td.today {
        background: var(--calm-blue-50, #eff6ff);
    }
    .schedule-item {
        background: var(--calm-blue-100, #dbeafe);
        border-left: 3px solid var(--calm-blue-500, #3b82f6);
        border-radius: 4px;
        padding: 6px 8px;
        margin-bottom: 6px;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .schedule-item:hover {
        background: var(--calm-blue-200, #bfdbfe);
        transform: translateX(2px);
    }
    .schedule-item.unavailable {
        background: #fee2e2;
        border-left-color: #ef4444;
    }
    .schedule-item .time {
        font-weight: 600;
        color: var(--calm-blue-700, #1d4ed8);
    }
    .schedule-item.unavailable .time {
        color: #dc2626;
    }
    .schedule-item .pt-name {
        font-size: 0.8rem;
        color: #6b7280;
    }
    .day-number {
        font-size: 0.9rem;
        color: #6b7280;
        margin-bottom: 8px;
    }
    .day-number.today {
        background: var(--calm-blue-500, #3b82f6);
        color: white;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .add-schedule-btn {
        opacity: 0;
        transition: opacity 0.2s;
    }
    .calendar-table td:hover .add-schedule-btn {
        opacity: 1;
    }

    /* Mobile Cards */
    @media (max-width: 768px) {
        .calendar-container {
            display: none;
        }
        .mobile-schedule-list {
            display: block !important;
        }
    }
    .mobile-schedule-list {
        display: none;
    }
    .mobile-schedule-card {
        border-left: 4px solid var(--calm-blue-500, #3b82f6);
    }
    .mobile-schedule-card.unavailable {
        border-left-color: #ef4444;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-gradient-primary text-white rounded-3 p-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h2 class="mb-2"><i class="bi bi-calendar-week me-2"></i>ตารางงาน PT</h2>
                        <p class="mb-0 opacity-90">จัดการตารางการทำงานของนักกายภาพบำบัด</p>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#scheduleModal" onclick="openCreateModal()">
                            <i class="bi bi-plus-circle me-2"></i>เพิ่มตารางงาน
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card stat-card blue h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">ตารางงานสัปดาห์นี้</h6>
                            <h3 class="mb-0 text-primary">{{ $schedules->count() }}</h3>
                        </div>
                        <div class="text-primary opacity-25">
                            <i class="bi bi-calendar-check fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card stat-card green h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">ว่าง/พร้อมรับงาน</h6>
                            <h3 class="mb-0 text-success">{{ $schedules->where('is_available', true)->count() }}</h3>
                        </div>
                        <div class="text-success opacity-25">
                            <i class="bi bi-check-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card stat-card orange h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">PT ที่ทำงาน</h6>
                            <h3 class="mb-0 text-warning">{{ $pts->count() }}</h3>
                        </div>
                        <div class="text-warning opacity-25">
                            <i class="bi bi-person-badge fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">วันที่เริ่มต้น</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">วันที่สิ้นสุด</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">PT</label>
                    <select name="pt_id" class="form-select">
                        <option value="">-- ทั้งหมด --</option>
                        @foreach($pts as $pt)
                        <option value="{{ $pt->id }}" {{ request('pt_id') == $pt->id ? 'selected' : '' }}>
                            {{ $pt->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-2"></i>ค้นหา
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Calendar View (Desktop) -->
    <div class="card shadow-sm calendar-container">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-calendar3 me-2 text-primary"></i>ตารางสัปดาห์</h5>
                <div>
                    @php
                        $prevStart = \Carbon\Carbon::parse($startDate)->subWeek()->toDateString();
                        $prevEnd = \Carbon\Carbon::parse($endDate)->subWeek()->toDateString();
                        $nextStart = \Carbon\Carbon::parse($startDate)->addWeek()->toDateString();
                        $nextEnd = \Carbon\Carbon::parse($endDate)->addWeek()->toDateString();
                    @endphp
                    <a href="{{ route('schedules.index', ['start_date' => $prevStart, 'end_date' => $prevEnd]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-chevron-left"></i> สัปดาห์ก่อน
                    </a>
                    <a href="{{ route('schedules.index') }}" class="btn btn-sm btn-outline-secondary mx-1">
                        สัปดาห์นี้
                    </a>
                    <a href="{{ route('schedules.index', ['start_date' => $nextStart, 'end_date' => $nextEnd]) }}" class="btn btn-sm btn-outline-primary">
                        สัปดาห์หน้า <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered calendar-table mb-0">
                <thead>
                    <tr>
                        @foreach($dates as $date)
                        @php
                            $isToday = $date->isToday();
                            $dayNames = ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'];
                        @endphp
                        <th class="{{ $isToday ? 'today' : '' }}">
                            <div>{{ $dayNames[$date->dayOfWeek] }}</div>
                            <div class="fw-normal">{{ $date->format('d/m') }}</div>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach($dates as $date)
                        @php
                            $dateKey = $date->format('Y-m-d');
                            $daySchedules = $schedulesByDate->get($dateKey, collect());
                            $isToday = $date->isToday();
                        @endphp
                        <td class="{{ $isToday ? 'today' : '' }}" style="min-height: 150px;">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="day-number {{ $isToday ? 'today' : '' }}">{{ $date->day }}</span>
                                <button class="btn btn-sm btn-outline-primary add-schedule-btn"
                                        onclick="openCreateModal('{{ $dateKey }}')" title="เพิ่มตาราง">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                            @foreach($daySchedules as $schedule)
                            <div class="schedule-item {{ !$schedule->is_available ? 'unavailable' : '' }}"
                                 onclick="openViewModal('{{ $schedule->id }}')"
                                 title="{{ $schedule->pt?->name ?? 'ไม่ระบุ PT' }}">
                                <div class="time">
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                </div>
                                <div class="pt-name">
                                    <i class="bi bi-person"></i> {{ $schedule->pt?->name ?? 'ไม่ระบุ' }}
                                </div>
                                @if($schedule->schedule_type)
                                <small class="text-muted">{{ $schedule->schedule_type }}</small>
                                @endif
                            </div>
                            @endforeach
                            @if($daySchedules->isEmpty())
                            <div class="text-center text-muted small py-3">
                                <i class="bi bi-calendar-x"></i><br>ไม่มีตาราง
                            </div>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Schedule List -->
    <div class="mobile-schedule-list">
        @foreach($dates as $date)
        @php
            $dateKey = $date->format('Y-m-d');
            $daySchedules = $schedulesByDate->get($dateKey, collect());
            $isToday = $date->isToday();
            $dayNames = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];
        @endphp
        <div class="card mb-3 {{ $isToday ? 'border-primary' : '' }}">
            <div class="card-header {{ $isToday ? 'bg-primary text-white' : 'bg-light' }}">
                <div class="d-flex justify-content-between align-items-center">
                    <span>
                        <strong>{{ $dayNames[$date->dayOfWeek] }}</strong>
                        {{ $date->format('d/m/Y') }}
                    </span>
                    <button class="btn btn-sm {{ $isToday ? 'btn-light' : 'btn-outline-primary' }}"
                            onclick="openCreateModal('{{ $dateKey }}')">
                        <i class="bi bi-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                @forelse($daySchedules as $schedule)
                <div class="card mobile-schedule-card {{ !$schedule->is_available ? 'unavailable' : '' }} mb-2"
                     onclick="openViewModal('{{ $schedule->id }}')">
                    <div class="card-body py-2 px-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="{{ $schedule->is_available ? 'text-primary' : 'text-danger' }}">
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                </strong>
                                <div class="small text-muted">
                                    <i class="bi bi-person"></i> {{ $schedule->pt?->name ?? 'ไม่ระบุ' }}
                                </div>
                            </div>
                            <span class="badge {{ $schedule->is_available ? 'bg-success' : 'bg-danger' }}">
                                {{ $schedule->is_available ? 'ว่าง' : 'ไม่ว่าง' }}
                            </span>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center mb-0">
                    <i class="bi bi-calendar-x"></i> ไม่มีตาราง
                </p>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Schedule Modal (Create/Edit) -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="scheduleForm" autocomplete="off">
                @csrf
                <input type="hidden" id="scheduleId" name="schedule_id">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title" id="modalTitle"><i class="bi bi-plus-circle me-2"></i>เพิ่มตารางงาน</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">PT <span class="text-danger">*</span></label>
                            <select class="form-select" id="staff_id" name="staff_id" required>
                                <option value="">-- เลือก PT --</option>
                                @foreach($pts as $pt)
                                <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">วันที่ <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="schedule_date" name="schedule_date" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">เวลาเริ่ม <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="start_time" name="start_time" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">เวลาสิ้นสุด <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="end_time" name="end_time" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">ประเภทงาน</label>
                            <select class="form-select" id="schedule_type" name="schedule_type">
                                <option value="">-- เลือกประเภท --</option>
                                <option value="regular">ทำงานปกติ</option>
                                <option value="overtime">ทำงานล่วงเวลา</option>
                                <option value="on_call">อยู่เวร</option>
                                <option value="training">อบรม</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">หมายเหตุ</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_available" name="is_available" checked>
                                <label class="form-check-label" for="is_available">ว่าง/พร้อมรับงาน</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Schedule Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title"><i class="bi bi-calendar-event me-2"></i>รายละเอียดตาราง</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="30%">PT:</th>
                        <td id="viewPtName"></td>
                    </tr>
                    <tr>
                        <th>วันที่:</th>
                        <td id="viewDate"></td>
                    </tr>
                    <tr>
                        <th>เวลา:</th>
                        <td id="viewTime"></td>
                    </tr>
                    <tr>
                        <th>ประเภท:</th>
                        <td id="viewType"></td>
                    </tr>
                    <tr>
                        <th>สถานะ:</th>
                        <td id="viewStatus"></td>
                    </tr>
                    <tr>
                        <th>หมายเหตุ:</th>
                        <td id="viewNotes"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-outline-primary" id="editFromViewBtn">
                    <i class="bi bi-pencil me-2"></i>แก้ไข
                </button>
                <button type="button" class="btn btn-outline-danger" id="deleteFromViewBtn">
                    <i class="bi bi-trash me-2"></i>ลบ
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>ยืนยันการลบ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>คุณต้องการลบตารางงานนี้ใช่หรือไม่?</p>
                <p class="text-muted mb-0">การดำเนินการนี้ไม่สามารถย้อนกลับได้</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash me-2"></i>ลบ
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const scheduleModal = new bootstrap.Modal(document.getElementById('scheduleModal'));
    const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    let currentScheduleId = null;

    // Open create modal
    window.openCreateModal = function(date = null) {
        document.getElementById('scheduleForm').reset();
        document.getElementById('scheduleId').value = '';
        document.getElementById('modalTitle').innerHTML = '<i class="bi bi-plus-circle me-2"></i>เพิ่มตารางงาน';
        document.getElementById('is_available').checked = true;

        if (date) {
            document.getElementById('schedule_date').value = date;
        } else {
            document.getElementById('schedule_date').value = '{{ now()->toDateString() }}';
        }

        // Default times
        document.getElementById('start_time').value = '09:00';
        document.getElementById('end_time').value = '18:00';

        scheduleModal.show();
    };

    // Open view modal
    window.openViewModal = function(id) {
        currentScheduleId = id;

        fetch(`{{ url('/schedules') }}/${id}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('viewPtName').textContent = data.pt?.name || 'ไม่ระบุ';
            document.getElementById('viewDate').textContent = new Date(data.schedule_date).toLocaleDateString('th-TH', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            });
            document.getElementById('viewTime').textContent = `${data.start_time.substring(0,5)} - ${data.end_time.substring(0,5)}`;
            document.getElementById('viewType').textContent = data.schedule_type || '-';
            document.getElementById('viewStatus').innerHTML = data.is_available
                ? '<span class="badge bg-success">ว่าง/พร้อมรับงาน</span>'
                : '<span class="badge bg-danger">ไม่ว่าง</span>';
            document.getElementById('viewNotes').textContent = data.notes || '-';

            viewModal.show();
        })
        .catch(error => {
            showAlert('เกิดข้อผิดพลาดในการโหลดข้อมูล', 'danger');
        });
    };

    // Edit from view modal
    document.getElementById('editFromViewBtn').addEventListener('click', function() {
        viewModal.hide();

        fetch(`{{ url('/schedules') }}/${currentScheduleId}/edit`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('scheduleId').value = data.id;
            document.getElementById('staff_id').value = data.staff_id;
            document.getElementById('schedule_date').value = data.schedule_date.substring(0, 10);
            document.getElementById('start_time').value = data.start_time.substring(0, 5);
            document.getElementById('end_time').value = data.end_time.substring(0, 5);
            document.getElementById('schedule_type').value = data.schedule_type || '';
            document.getElementById('notes').value = data.notes || '';
            document.getElementById('is_available').checked = data.is_available;

            document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>แก้ไขตารางงาน';

            scheduleModal.show();
        })
        .catch(error => {
            showAlert('เกิดข้อผิดพลาดในการโหลดข้อมูล', 'danger');
        });
    });

    // Delete from view modal
    document.getElementById('deleteFromViewBtn').addEventListener('click', function() {
        viewModal.hide();
        deleteModal.show();
    });

    // Form submit
    document.getElementById('scheduleForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const scheduleId = document.getElementById('scheduleId').value;
        const formData = new FormData(this);

        let url = '{{ url('/schedules') }}';
        let method = 'POST';

        if (scheduleId) {
            url = `{{ url('/schedules') }}/${scheduleId}`;
            formData.append('_method', 'PUT');
        }

        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>กำลังบันทึก...';

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => Promise.reject(err));
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                scheduleModal.hide();
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                let errorMsg = 'เกิดข้อผิดพลาด';
                if (data.errors) {
                    errorMsg = Object.values(data.errors).flat().join('<br>');
                } else if (data.message) {
                    errorMsg = data.message;
                }
                showAlert(errorMsg, 'danger');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>บันทึก';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            let errorMsg = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
            if (error.errors) {
                errorMsg = Object.values(error.errors).flat().join('<br>');
            } else if (error.message) {
                errorMsg = error.message;
            }
            showAlert(errorMsg, 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>บันทึก';
        });
    });

    // Confirm delete
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (!currentScheduleId) return;

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>กำลังลบ...';

        const formData = new FormData();
        formData.append('_method', 'DELETE');
        formData.append('_token', '{{ csrf_token() }}');

        fetch(`{{ url('/schedules') }}/${currentScheduleId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                deleteModal.hide();
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert(data.message || 'เกิดข้อผิดพลาดในการลบ', 'danger');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-trash me-2"></i>ลบ';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('เกิดข้อผิดพลาดในการลบข้อมูล', 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-trash me-2"></i>ลบ';
        });
    });

    // Show alert
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
        alertDiv.style.zIndex = '9999';
        alertDiv.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);

        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    }
});
</script>
@endpush
@endsection
