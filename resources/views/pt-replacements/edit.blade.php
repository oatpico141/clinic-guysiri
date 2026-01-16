@extends('layouts.app')

@section('title', 'แก้ไขการแทน PT - GCMS')

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
                <h2 class="mb-2"><i class="bi bi-pencil me-2"></i>แก้ไขการแทน PT</h2>
                <p class="mb-0 opacity-90">แก้ไขข้อมูลการแทน PT</p>
            </div>
            <a href="{{ route('pt-replacements.show', $replacement) }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="form-card">
        <form method="POST" action="{{ route('pt-replacements.update', $replacement) }}">
            @csrf
            @method('PUT')

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
                                <option value="{{ $pt->id }}" {{ old('original_pt_id', $replacement->original_pt_id) == $pt->id ? 'selected' : '' }}>
                                    {{ $pt->first_name }} {{ $pt->last_name }}
                                </option>
                                @endforeach
                            </select>
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
                                <option value="{{ $pt->id }}" {{ old('replacement_pt_id', $replacement->replacement_pt_id) == $pt->id ? 'selected' : '' }}>
                                    {{ $pt->first_name }} {{ $pt->last_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
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
                        <option value="{{ $branch->id }}" {{ old('branch_id', $replacement->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">วันที่แทน <span class="text-danger">*</span></label>
                    <input type="date" name="replacement_date" class="form-control" value="{{ old('replacement_date', $replacement->replacement_date ? \Carbon\Carbon::parse($replacement->replacement_date)->format('Y-m-d') : '') }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label">เหตุผลการแทน <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control" rows="2" required>{{ old('reason', $replacement->reason) }}</textarea>
                </div>
            </div>

            <!-- Commission Handling -->
            <div class="section-title"><i class="bi bi-cash me-2"></i>การจัดการค่าคอมมิชชัน</div>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="commission-option d-block" id="optionOriginal">
                        <input type="radio" name="commission_handling" value="original" {{ old('commission_handling', $replacement->commission_handling) == 'original' ? 'checked' : '' }}>
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
                        <input type="radio" name="commission_handling" value="replacement" {{ old('commission_handling', $replacement->commission_handling) == 'replacement' ? 'checked' : '' }}>
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
                        <input type="radio" name="commission_handling" value="split" {{ old('commission_handling', $replacement->commission_handling) == 'split' ? 'checked' : '' }}>
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
            <div class="row mb-4" id="splitPercentageRow" style="{{ old('commission_handling', $replacement->commission_handling) == 'split' ? '' : 'display: none;' }}">
                <div class="col-md-6">
                    <label class="form-label">สัดส่วนที่ PT แทนได้รับ (%)</label>
                    <div class="input-group">
                        <input type="number" name="commission_split_percentage" class="form-control" value="{{ old('commission_split_percentage', $replacement->commission_split_percentage ?? 50) }}" min="0" max="100">
                        <span class="input-group-text">%</span>
                    </div>
                    <small class="text-muted">PT เดิมจะได้รับส่วนที่เหลือ</small>
                </div>
            </div>

            <!-- Notes -->
            <div class="section-title"><i class="bi bi-sticky me-2"></i>หมายเหตุ</div>
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <textarea name="notes" class="form-control" rows="3" placeholder="หมายเหตุเพิ่มเติม (ถ้ามี)...">{{ old('notes', $replacement->notes) }}</textarea>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                    <i class="bi bi-trash me-1"></i> ลบ
                </button>
                <div class="d-flex gap-2">
                    <a href="{{ route('pt-replacements.show', $replacement) }}" class="btn btn-secondary">ยกเลิก</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> บันทึก
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ยืนยันการลบ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>คุณต้องการลบรายการแทน PT นี้หรือไม่?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">ลบ</button>
            </div>
        </div>
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
});

function confirmDelete() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    fetch('{{ route("pt-replacements.destroy", $replacement) }}', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '{{ route("pt-replacements.index") }}';
        } else {
            alert(data.message || 'เกิดข้อผิดพลาด');
        }
    })
    .catch(error => {
        alert('เกิดข้อผิดพลาด');
    });
});
</script>
@endpush
