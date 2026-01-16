@extends('layouts.app')

@section('title', 'เพิ่มสิทธิ์ - GCMS')

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
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2"><i class="bi bi-shield-plus me-2"></i>เพิ่มสิทธิ์</h2>
                <p class="mb-0 opacity-90">สร้างสิทธิ์ใหม่ในระบบ</p>
            </div>
            <a href="{{ route('permissions.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="form-card">
        <form method="POST" action="{{ route('permissions.store') }}">
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

            <!-- Permission Info -->
            <div class="section-title"><i class="bi bi-shield me-2"></i>ข้อมูลสิทธิ์</div>
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label">โมดูล <span class="text-danger">*</span></label>
                    <input type="text" name="module" class="form-control" value="{{ old('module') }}" placeholder="เช่น patients, invoices, users" list="moduleList" required>
                    <datalist id="moduleList">
                        @foreach($modules as $module)
                        <option value="{{ $module }}">
                        @endforeach
                    </datalist>
                    <small class="text-muted">ชื่อโมดูลหรือฟีเจอร์ในระบบ</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">การกระทำ <span class="text-danger">*</span></label>
                    <input type="text" name="action" class="form-control" value="{{ old('action') }}" placeholder="เช่น view, create, update, delete" list="actionList" required>
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
                    <small class="text-muted">สิ่งที่สามารถทำได้กับโมดูลนี้</small>
                </div>
                <div class="col-12">
                    <label class="form-label">คำอธิบาย</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="อธิบายสิทธิ์นี้...">{{ old('description') }}</textarea>
                </div>
            </div>

            <!-- Role Assignment -->
            <div class="section-title"><i class="bi bi-people me-2"></i>กำหนดให้บทบาท</div>
            <div class="row g-3 mb-4">
                @foreach($roles as $role)
                <div class="col-md-4 col-6">
                    <label class="role-checkbox d-block" id="role{{ $role->id }}">
                        <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                            {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}
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

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('permissions.index') }}" class="btn btn-secondary">ยกเลิก</a>
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
function toggleCheckbox(id) {
    const label = document.getElementById(id);
    const checkbox = label.querySelector('input[type="checkbox"]');
    if (checkbox.checked) {
        label.classList.add('checked');
    } else {
        label.classList.remove('checked');
    }
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.role-checkbox input[type="checkbox"]:checked').forEach(cb => {
        cb.closest('.role-checkbox').classList.add('checked');
    });
});
</script>
@endpush
