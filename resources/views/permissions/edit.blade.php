@extends('layouts.app')

@section('title', 'แก้ไขสิทธิ์ - GCMS')

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .page-header {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
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

    .role-checkbox {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 0.75rem;
        transition: all 0.2s;
        cursor: pointer;
    }

    .role-checkbox:hover {
        border-color: #7c3aed;
    }

    .role-checkbox.checked {
        border-color: #7c3aed;
        background: #f5f3ff;
    }

    .role-checkbox input {
        margin-right: 0.5rem;
    }

    .permission-display {
        font-family: 'Monaco', 'Consolas', monospace;
        background: #f1f5f9;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        display: inline-block;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2"><i class="bi bi-pencil me-2"></i>แก้ไขสิทธิ์</h2>
                <p class="mb-0 opacity-90">
                    <span class="permission-display">{{ $permission->module }}.{{ $permission->action }}</span>
                </p>
            </div>
            <a href="{{ route('permissions.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="form-card">
        <form method="POST" action="{{ route('permissions.update', $permission) }}">
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

            <!-- Permission Info -->
            <div class="section-title"><i class="bi bi-shield me-2"></i>ข้อมูลสิทธิ์</div>
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label">โมดูล <span class="text-danger">*</span></label>
                    <input type="text" name="module" class="form-control" value="{{ old('module', $permission->module) }}" list="moduleList" required>
                    <datalist id="moduleList">
                        @foreach($modules as $module)
                        <option value="{{ $module }}">
                        @endforeach
                    </datalist>
                </div>
                <div class="col-md-6">
                    <label class="form-label">การกระทำ <span class="text-danger">*</span></label>
                    <input type="text" name="action" class="form-control" value="{{ old('action', $permission->action) }}" list="actionList" required>
                    <datalist id="actionList">
                        <option value="view">
                        <option value="create">
                        <option value="update">
                        <option value="delete">
                        <option value="export">
                        <option value="import">
                        <option value="approve">
                        <option value="manage">
                    </datalist>
                </div>
                <div class="col-12">
                    <label class="form-label">คำอธิบาย</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $permission->description) }}</textarea>
                </div>
            </div>

            <!-- Role Assignment -->
            <div class="section-title"><i class="bi bi-people me-2"></i>กำหนดให้บทบาท</div>
            @php
                $assignedRoleIds = $permission->roles->pluck('id')->toArray();
            @endphp
            <div class="row g-3 mb-4">
                @foreach($roles as $role)
                <div class="col-md-4 col-6">
                    <label class="role-checkbox d-block {{ in_array($role->id, old('roles', $assignedRoleIds)) ? 'checked' : '' }}" id="role{{ $role->id }}">
                        <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                            {{ in_array($role->id, old('roles', $assignedRoleIds)) ? 'checked' : '' }}
                            onchange="toggleCheckbox('role{{ $role->id }}')">
                        <span class="fw-semibold">{{ $role->name }}</span>
                        @if($role->description)
                        <br><small class="text-muted">{{ $role->description }}</small>
                        @endif
                    </label>
                </div>
                @endforeach
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                    <i class="bi bi-trash me-1"></i> ลบ
                </button>
                <div class="d-flex gap-2">
                    <a href="{{ route('permissions.index') }}" class="btn btn-secondary">ยกเลิก</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> บันทึก
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ยืนยันการลบ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>คุณต้องการลบสิทธิ์ <strong>{{ $permission->module }}.{{ $permission->action }}</strong> หรือไม่?</p>
                <p class="text-danger mb-0"><small><i class="bi bi-exclamation-triangle me-1"></i>บทบาททั้งหมดที่มีสิทธิ์นี้จะสูญเสียสิทธิ์ไปด้วย</small></p>
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
function toggleCheckbox(id) {
    const label = document.getElementById(id);
    const checkbox = label.querySelector('input[type="checkbox"]');
    if (checkbox.checked) {
        label.classList.add('checked');
    } else {
        label.classList.remove('checked');
    }
}

function confirmDelete() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    fetch('{{ route("permissions.destroy", $permission) }}', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '{{ route("permissions.index") }}';
        } else {
            alert(data.message || 'เกิดข้อผิดพลาด');
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
        }
    })
    .catch(error => {
        alert('เกิดข้อผิดพลาด');
    });
});
</script>
@endpush
