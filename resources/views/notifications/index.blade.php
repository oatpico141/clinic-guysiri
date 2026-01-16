@extends('layouts.app')

@section('title', 'การแจ้งเตือน - GCMS')

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border-radius: 20px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .filter-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        margin-bottom: 1.5rem;
    }

    .notification-list {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .notification-item {
        padding: 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        transition: background-color 0.2s;
        cursor: pointer;
    }

    .notification-item:hover {
        background-color: #f8fafc;
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-item.unread {
        background: #fffbeb;
        border-left: 4px solid #f59e0b;
    }

    .notification-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .icon-info { background: #dbeafe; color: #2563eb; }
    .icon-success { background: #d1fae5; color: #059669; }
    .icon-warning { background: #fef3c7; color: #d97706; }
    .icon-error { background: #fee2e2; color: #dc2626; }
    .icon-system { background: #f1f5f9; color: #64748b; }

    .priority-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .priority-high { background: #fee2e2; color: #991b1b; }
    .priority-normal { background: #f1f5f9; color: #64748b; }
    .priority-low { background: #d1fae5; color: #065f46; }

    .notification-title {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }

    .notification-message {
        color: #6b7280;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .notification-time {
        font-size: 0.8rem;
        color: #9ca3af;
    }

    .notification-actions {
        opacity: 0;
        transition: opacity 0.2s;
    }

    .notification-item:hover .notification-actions {
        opacity: 1;
    }

    .empty-state {
        padding: 4rem 2rem;
        text-align: center;
    }

    .empty-state i {
        font-size: 4rem;
        color: #d1d5db;
    }

    /* Modal styling */
    .notification-detail-modal .modal-header {
        border-bottom: none;
        padding-bottom: 0;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="mb-2"><i class="bi bi-bell me-2"></i>การแจ้งเตือน</h2>
                <p class="mb-0 opacity-90">ศูนย์รวมการแจ้งเตือนทั้งหมดของคุณ</p>
            </div>
            <div class="d-flex gap-2">
                @if($unreadCount > 0)
                <button class="btn btn-light" onclick="markAllAsRead()">
                    <i class="bi bi-check2-all me-1"></i> อ่านทั้งหมด
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="fs-2 fw-bold text-primary">{{ number_format($totalNotifications) }}</div>
                <div class="text-muted">ทั้งหมด</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="fs-2 fw-bold text-warning">{{ number_format($unreadCount) }}</div>
                <div class="text-muted">ยังไม่อ่าน</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="fs-2 fw-bold text-info">{{ number_format($todayCount) }}</div>
                <div class="text-muted">วันนี้</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="fs-2 fw-bold text-danger">{{ number_format($highPriorityCount) }}</div>
                <div class="text-muted">สำคัญ</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('notifications.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">สถานะ</label>
                    <select name="status" class="form-select">
                        <option value="">ทั้งหมด</option>
                        <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>ยังไม่อ่าน</option>
                        <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>อ่านแล้ว</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">ประเภท</label>
                    <select name="type" class="form-select">
                        <option value="">ทุกประเภท</option>
                        @foreach($types as $type)
                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">ความสำคัญ</label>
                    <select name="priority" class="form-select">
                        <option value="">ทั้งหมด</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>สูง</option>
                        <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>ปกติ</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>ต่ำ</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-funnel"></i> กรอง
                        </button>
                        <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Notification List -->
    <div class="notification-list">
        @forelse($notifications as $notification)
        <div class="notification-item {{ !$notification->is_read ? 'unread' : '' }}" onclick="viewNotification('{{ $notification->id }}')">
            <div class="d-flex align-items-start gap-3">
                <div class="notification-icon
                    @switch($notification->notification_type)
                        @case('success') icon-success @break
                        @case('warning') icon-warning @break
                        @case('error') icon-error @break
                        @case('info') icon-info @break
                        @default icon-system
                    @endswitch
                ">
                    @switch($notification->notification_type)
                        @case('success')
                            <i class="bi bi-check-circle"></i>
                            @break
                        @case('warning')
                            <i class="bi bi-exclamation-triangle"></i>
                            @break
                        @case('error')
                            <i class="bi bi-x-circle"></i>
                            @break
                        @case('info')
                            <i class="bi bi-info-circle"></i>
                            @break
                        @default
                            <i class="bi bi-bell"></i>
                    @endswitch
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="notification-title">{{ $notification->title }}</div>
                            <div class="notification-message">{{ $notification->message }}</div>
                            <div class="notification-time">
                                <i class="bi bi-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if($notification->priority == 'high')
                            <span class="priority-badge priority-high">สำคัญ</span>
                            @elseif($notification->priority == 'low')
                            <span class="priority-badge priority-low">ต่ำ</span>
                            @endif
                            <div class="notification-actions">
                                @if(!$notification->is_read)
                                <button class="btn btn-sm btn-outline-primary me-1" onclick="event.stopPropagation(); markAsRead('{{ $notification->id }}')" title="ทำเครื่องหมายอ่านแล้ว">
                                    <i class="bi bi-check"></i>
                                </button>
                                @endif
                                <button class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); deleteNotification('{{ $notification->id }}')" title="ลบ">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <i class="bi bi-bell-slash mb-3"></i>
            <h5 class="text-muted">ไม่มีการแจ้งเตือน</h5>
            <p class="text-muted mb-0">เมื่อมีการแจ้งเตือนใหม่ จะแสดงที่นี่</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-4 d-flex justify-content-center">
        {{ $notifications->withQueryString()->links() }}
    </div>
</div>

<!-- Notification Detail Modal -->
<div class="modal fade notification-detail-modal" id="notificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="notificationMessage"></p>
                <div class="text-muted small" id="notificationTime"></div>
                <div id="notificationAction" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function viewNotification(id) {
    fetch(`{{ url('notifications') }}/${id}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const n = data.notification;
            document.getElementById('notificationTitle').textContent = n.title;
            document.getElementById('notificationMessage').textContent = n.message;
            document.getElementById('notificationTime').textContent = new Date(n.created_at).toLocaleString('th-TH');

            const actionDiv = document.getElementById('notificationAction');
            if (n.action_url) {
                actionDiv.innerHTML = `<a href="${n.action_url}" class="btn btn-primary">${n.action_text || 'ดูเพิ่มเติม'}</a>`;
            } else {
                actionDiv.innerHTML = '';
            }

            new bootstrap.Modal(document.getElementById('notificationModal')).show();

            // Update UI to mark as read
            const item = document.querySelector(`.notification-item[onclick*="${id}"]`);
            if (item) item.classList.remove('unread');
        }
    });
}

function markAsRead(id) {
    fetch(`{{ url('notifications') }}/${id}/mark-read`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = document.querySelector(`.notification-item[onclick*="${id}"]`);
            if (item) item.classList.remove('unread');
        }
    });
}

function markAllAsRead() {
    fetch('{{ route("notifications.markAllRead") }}', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
            });
            location.reload();
        }
    });
}

function deleteNotification(id) {
    if (!confirm('ต้องการลบการแจ้งเตือนนี้?')) return;

    fetch(`{{ url('notifications') }}/${id}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = document.querySelector(`.notification-item[onclick*="${id}"]`);
            if (item) item.remove();
        }
    });
}
</script>
@endpush
