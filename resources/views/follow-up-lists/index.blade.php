@extends('layouts.app')

@section('title', 'รายการติดตามผล - GCMS')

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #8b5cf6, #a78bfa);
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        color: white;
        margin-bottom: 1.5rem;
    }
    .priority-high { border-left: 4px solid #ef4444; }
    .priority-normal { border-left: 4px solid #3b82f6; }
    .priority-low { border-left: 4px solid #6b7280; }
    .follow-up-card {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        transition: all 0.2s;
    }
    .follow-up-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-contacted { background: #dbeafe; color: #1e40af; }
    .status-completed { background: #dcfce7; color: #166534; }
    .stat-box {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        text-align: center;
        border: 1px solid #e2e8f0;
    }
    .stat-box .number {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
    }
    .stat-box .label {
        font-size: 0.75rem;
        color: #64748b;
        text-transform: uppercase;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1"><i class="bi bi-telephone-outbound me-2"></i>รายการติดตามผล</h4>
            <p class="mb-0 opacity-75">ติดตามอาการลูกค้าหลังรับบริการ</p>
        </div>
        <div>
            <button class="btn btn-light me-2" id="autoGenerateBtn">
                <i class="bi bi-arrow-repeat me-1"></i>สร้างรายการอัตโนมัติ
            </button>
            <a href="{{ route('crm.index') }}" class="btn btn-outline-light">
                <i class="bi bi-calendar-event me-1"></i>CRM Follow-up
            </a>
        </div>
    </div>

    <!-- Filters & Stats -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('follow-up-lists.index') }}" class="row g-2 align-items-center">
                        <div class="col-auto">
                            <label class="form-label small text-muted mb-0">วันที่</label>
                            <input type="date" name="date" class="form-control form-control-sm"
                                   value="{{ $date instanceof \Carbon\Carbon ? $date->format('Y-m-d') : $date }}">
                        </div>
                        <div class="col-auto">
                            <label class="form-label small text-muted mb-0">สาขา</label>
                            <select name="branch_id" class="form-select form-select-sm">
                                <option value="">ทุกสาขา</option>
                                @foreach(\App\Models\Branch::where('is_active', true)->get() as $branch)
                                <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <label class="form-label small text-muted mb-0">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="bi bi-search me-1"></i>ค้นหา
                                </button>
                                <a href="{{ route('follow-up-lists.index') }}" class="btn btn-sm btn-secondary">ล้าง</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="row g-2">
                @php
                    $pending = ($followUps ?? collect())->where('status', 'pending')->count();
                    $contacted = ($followUps ?? collect())->where('status', 'contacted')->count();
                    $completed = ($followUps ?? collect())->where('status', 'completed')->count();
                @endphp
                <div class="col-4">
                    <div class="stat-box">
                        <div class="number text-warning">{{ $pending }}</div>
                        <div class="label">รอติดตาม</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="stat-box">
                        <div class="number text-primary">{{ $contacted }}</div>
                        <div class="label">ติดต่อแล้ว</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="stat-box">
                        <div class="number text-success">{{ $completed }}</div>
                        <div class="label">เสร็จสิ้น</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Follow-up List -->
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-check me-2"></i>รายการวันที่ {{ $date instanceof \Carbon\Carbon ? $date->format('d/m/Y') : \Carbon\Carbon::parse($date)->format('d/m/Y') }}</span>
            <span class="badge bg-secondary">{{ ($followUps ?? collect())->count() }} รายการ</span>
        </div>
        <div class="card-body">
            @forelse($followUps ?? [] as $followUp)
            <div class="follow-up-card priority-{{ $followUp->priority ?? 'normal' }}" data-id="{{ $followUp->id }}">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="avatar bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                    <i class="bi bi-person text-primary"></i>
                                </div>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $followUp->patient->name ?? 'N/A' }}</div>
                                <div class="small text-muted">
                                    <i class="bi bi-telephone me-1"></i>{{ $followUp->patient->phone ?? '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">การรักษา</div>
                        <div>{{ $followUp->treatment->service->name ?? 'N/A' }}</div>
                        <div class="small text-muted">
                            PT: {{ $followUp->pt->name ?? '-' }}
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="small text-muted">ลำดับความสำคัญ</div>
                        @if($followUp->priority == 'high')
                        <span class="badge bg-danger">สูง</span>
                        @elseif($followUp->priority == 'low')
                        <span class="badge bg-secondary">ต่ำ</span>
                        @else
                        <span class="badge bg-primary">ปกติ</span>
                        @endif
                    </div>
                    <div class="col-md-2">
                        <div class="small text-muted">สถานะ</div>
                        <span class="badge status-{{ $followUp->status ?? 'pending' }}">
                            @if(($followUp->status ?? 'pending') == 'pending')
                            รอติดตาม
                            @elseif($followUp->status == 'contacted')
                            ติดต่อแล้ว
                            @else
                            เสร็จสิ้น
                            @endif
                        </span>
                    </div>
                    <div class="col-md-1 text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0)" onclick="updateStatus('{{ $followUp->id }}', 'contacted')">
                                        <i class="bi bi-telephone-fill me-2 text-primary"></i>ติดต่อแล้ว
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0)" onclick="updateStatus('{{ $followUp->id }}', 'completed')">
                                        <i class="bi bi-check-circle-fill me-2 text-success"></i>เสร็จสิ้น
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('patients.show', $followUp->patient_id) }}">
                                        <i class="bi bi-person me-2"></i>ดูข้อมูลลูกค้า
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                @if($followUp->notes || $followUp->contact_notes)
                <div class="mt-2 pt-2 border-top">
                    @if($followUp->notes)
                    <div class="small text-muted"><i class="bi bi-sticky me-1"></i>{{ $followUp->notes }}</div>
                    @endif
                    @if($followUp->contact_notes)
                    <div class="small text-info"><i class="bi bi-chat-dots me-1"></i>{{ $followUp->contact_notes }}</div>
                    @endif
                </div>
                @endif
            </div>
            @empty
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox display-4 d-block mb-3"></i>
                <p class="mb-0">ไม่มีรายการติดตามสำหรับวันที่เลือก</p>
                <button class="btn btn-primary mt-3" id="generateEmptyBtn">
                    <i class="bi bi-arrow-repeat me-1"></i>สร้างรายการอัตโนมัติ
                </button>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="updateStatusForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" id="newStatus">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>อัปเดตสถานะ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">หมายเหตุการติดต่อ</label>
                        <textarea name="contact_notes" class="form-control" rows="3" placeholder="บันทึกผลการติดต่อ..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const updateModal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
    let currentId = null;

    // Update status
    window.updateStatus = function(id, status) {
        currentId = id;
        document.getElementById('newStatus').value = status;
        updateModal.show();
    };

    // Submit update form
    document.getElementById('updateStatusForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch(`{{ url('/follow-up-lists') }}/${currentId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateModal.hide();
                location.reload();
            } else {
                alert('เกิดข้อผิดพลาด');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการบันทึก');
        });
    });

    // Auto generate
    const autoGenerateHandler = function() {
        if (!confirm('ต้องการสร้างรายการติดตามอัตโนมัติจากการรักษาเมื่อวานหรือไม่?')) {
            return;
        }

        fetch('{{ route('follow-up-lists.auto-generate') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('เกิดข้อผิดพลาด: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการสร้างรายการ');
        });
    };

    document.getElementById('autoGenerateBtn')?.addEventListener('click', autoGenerateHandler);
    document.getElementById('generateEmptyBtn')?.addEventListener('click', autoGenerateHandler);
});
</script>
@endpush
