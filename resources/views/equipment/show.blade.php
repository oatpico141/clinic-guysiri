@extends('layouts.app')

@section('title', 'รายละเอียดอุปกรณ์ - GCMS')

@push('styles')
<style>
    .detail-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        margin-bottom: 1.5rem;
    }

    .page-header {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        border-radius: 20px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }

    .info-row {
        padding: 0.75rem 0;
        border-bottom: 1px solid #e5e7eb;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: #6b7280;
        font-size: 0.875rem;
    }

    .maintenance-log {
        border-left: 3px solid #0ea5e9;
        padding-left: 1rem;
        margin-bottom: 1rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2"><i class="bi bi-tools me-2"></i>{{ $equipment->name }}</h2>
                <p class="mb-0 opacity-90">รหัส: {{ $equipment->equipment_code }}</p>
            </div>
            <div>
                <a href="{{ route('equipment.edit', $equipment) }}" class="btn btn-light me-2">
                    <i class="bi bi-pencil me-1"></i> แก้ไข
                </a>
                <a href="{{ route('equipment.index') }}" class="btn btn-outline-light">
                    <i class="bi bi-arrow-left me-1"></i> กลับ
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Equipment Details -->
        <div class="col-md-8">
            <div class="detail-card">
                <h5 class="mb-4"><i class="bi bi-info-circle me-2"></i>ข้อมูลอุปกรณ์</h5>

                <div class="row">
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">รหัสอุปกรณ์</div>
                            <div><code>{{ $equipment->equipment_code }}</code></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">ชื่ออุปกรณ์</div>
                            <div>{{ $equipment->name }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">หมวดหมู่</div>
                            <div>{{ $equipment->category }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">สาขา</div>
                            <div>{{ $equipment->branch->name ?? '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">สถานะ</div>
                            <div>
                                @switch($equipment->status)
                                    @case('available')
                                        <span class="badge bg-success">พร้อมใช้งาน</span>
                                        @break
                                    @case('in_use')
                                        <span class="badge bg-primary">กำลังใช้งาน</span>
                                        @break
                                    @case('maintenance')
                                        <span class="badge bg-warning">ซ่อมบำรุง</span>
                                        @break
                                    @case('retired')
                                        <span class="badge bg-secondary">ปลดระวาง</span>
                                        @break
                                @endswitch
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Serial Number</div>
                            <div>{{ $equipment->serial_number ?? '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">วันที่ซื้อ</div>
                            <div>{{ $equipment->purchase_date ? $equipment->purchase_date->format('d/m/Y') : '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">ราคาซื้อ</div>
                            <div>{{ $equipment->purchase_price ? '฿' . number_format($equipment->purchase_price, 2) : '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">มูลค่าปัจจุบัน</div>
                            <div>{{ $equipment->current_value ? '฿' . number_format($equipment->current_value, 2) : '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">ผู้จัดจำหน่าย</div>
                            <div>{{ $equipment->supplier ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                @if($equipment->description)
                <div class="mt-3">
                    <div class="info-label">รายละเอียด</div>
                    <div>{{ $equipment->description }}</div>
                </div>
                @endif

                @if($equipment->notes)
                <div class="mt-3">
                    <div class="info-label">หมายเหตุ</div>
                    <div>{{ $equipment->notes }}</div>
                </div>
                @endif
            </div>

            <!-- Warranty Info -->
            <div class="detail-card">
                <h5 class="mb-4"><i class="bi bi-shield-check me-2"></i>ข้อมูลรับประกัน</h5>

                <div class="row">
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">เลขที่รับประกัน</div>
                            <div>{{ $equipment->warranty_number ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">วันหมดประกัน</div>
                            <div>
                                @if($equipment->warranty_expiry)
                                    {{ $equipment->warranty_expiry->format('d/m/Y') }}
                                    @if($equipment->warranty_expiry < now())
                                        <span class="badge bg-danger">หมดอายุแล้ว</span>
                                    @elseif($equipment->warranty_expiry <= now()->addDays(30))
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
        </div>

        <!-- Maintenance Info -->
        <div class="col-md-4">
            <div class="detail-card">
                <h5 class="mb-4"><i class="bi bi-wrench me-2"></i>ข้อมูลซ่อมบำรุง</h5>

                <div class="info-row">
                    <div class="info-label">รอบซ่อมบำรุง</div>
                    <div>{{ $equipment->maintenance_interval_days ? $equipment->maintenance_interval_days . ' วัน' : '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">ซ่อมบำรุงล่าสุด</div>
                    <div>{{ $equipment->last_maintenance_date ? $equipment->last_maintenance_date->format('d/m/Y') : '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">ซ่อมบำรุงครั้งถัดไป</div>
                    <div>
                        @if($equipment->next_maintenance_date)
                            {{ $equipment->next_maintenance_date->format('d/m/Y') }}
                            @if($equipment->next_maintenance_date <= now())
                                <span class="badge bg-danger">เลยกำหนด</span>
                            @elseif($equipment->next_maintenance_date <= now()->addDays(7))
                                <span class="badge bg-warning">ใกล้ถึงกำหนด</span>
                            @endif
                        @else
                            -
                        @endif
                    </div>
                </div>

                <hr>

                <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#maintenanceModal">
                    <i class="bi bi-plus me-1"></i> บันทึกการซ่อมบำรุง
                </button>
            </div>

            <!-- Maintenance Logs -->
            @if($equipment->maintenanceLogs && $equipment->maintenanceLogs->count() > 0)
            <div class="detail-card">
                <h5 class="mb-4"><i class="bi bi-clock-history me-2"></i>ประวัติซ่อมบำรุง</h5>

                @foreach($equipment->maintenanceLogs->sortByDesc('maintenance_date')->take(5) as $log)
                <div class="maintenance-log">
                    <div class="fw-bold">{{ $log->maintenance_date->format('d/m/Y') }}</div>
                    <div class="small text-muted">{{ $log->maintenance_type }}</div>
                    @if($log->description)
                        <div class="small">{{ Str::limit($log->description, 50) }}</div>
                    @endif
                    @if($log->cost)
                        <div class="small">฿{{ number_format($log->cost, 2) }}</div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Maintenance Modal -->
<div class="modal fade" id="maintenanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-wrench me-2"></i>บันทึกการซ่อมบำรุง</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="maintenanceForm" method="POST" action="{{ route('equipment.maintenance', $equipment) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ประเภทการซ่อมบำรุง</label>
                        <select name="maintenance_type" class="form-select" required>
                            <option value="preventive">ซ่อมบำรุงตามกำหนด</option>
                            <option value="corrective">ซ่อมแซมแก้ไข</option>
                            <option value="emergency">ซ่อมฉุกเฉิน</option>
                            <option value="inspection">ตรวจสอบ</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">วันที่ซ่อมบำรุง</label>
                        <input type="date" name="maintenance_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รายละเอียด</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">งานที่ทำ</label>
                        <textarea name="work_performed" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ผู้ดำเนินการ</label>
                        <input type="text" name="performed_by" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ค่าใช้จ่าย</label>
                        <div class="input-group">
                            <span class="input-group-text">฿</span>
                            <input type="number" name="cost" class="form-control" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">หมายเหตุ</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
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
document.getElementById('maintenanceForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('บันทึกการซ่อมบำรุงสำเร็จ');
            location.reload();
        } else {
            alert('เกิดข้อผิดพลาด: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('เกิดข้อผิดพลาดในการบันทึก');
    });
});
</script>
@endpush
