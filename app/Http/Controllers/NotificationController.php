<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Notification::where('user_id', $user->id)->latest();

        // Filter by read status
        if ($request->filled('status')) {
            if ($request->status === 'unread') {
                $query->where('is_read', false);
            } elseif ($request->status === 'read') {
                $query->where('is_read', true);
            }
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('notification_type', $request->type);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $notifications = $query->paginate(20);

        // Stats
        $totalNotifications = Notification::where('user_id', $user->id)->count();
        $unreadCount = Notification::where('user_id', $user->id)->where('is_read', false)->count();
        $todayCount = Notification::where('user_id', $user->id)->whereDate('created_at', now())->count();
        $highPriorityCount = Notification::where('user_id', $user->id)->where('priority', 'high')->where('is_read', false)->count();

        // Get notification types for filter
        $types = Notification::where('user_id', $user->id)
            ->select('notification_type')
            ->distinct()
            ->whereNotNull('notification_type')
            ->pluck('notification_type');

        return view('notifications.index', compact(
            'notifications', 'types',
            'totalNotifications', 'unreadCount', 'todayCount', 'highPriorityCount'
        ));
    }

    public function show($id)
    {
        $notification = Notification::where('user_id', auth()->id())->findOrFail($id);

        // Mark as read
        if (!$notification->is_read) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'notification' => $notification
        ]);
    }

    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', auth()->id())->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json(['success' => true, 'message' => 'อ่านทั้งหมดแล้ว']);
    }

    public function destroy($id)
    {
        try {
            $notification = Notification::where('user_id', auth()->id())->findOrFail($id);
            $notification->delete();

            return response()->json(['success' => true, 'message' => 'ลบแจ้งเตือนแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteAll()
    {
        Notification::where('user_id', auth()->id())->delete();

        return response()->json(['success' => true, 'message' => 'ลบแจ้งเตือนทั้งหมดแล้ว']);
    }

    // API: Get unread count for header badge
    public function unreadCount()
    {
        $count = Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    // API: Get recent notifications for dropdown
    public function recent()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->latest()
            ->limit(10)
            ->get();

        return response()->json(['notifications' => $notifications]);
    }

    // Not used - notifications are created by system
    public function create() { abort(404); }
    public function store(Request $request) { abort(404); }
    public function edit($id) { abort(404); }
    public function update(Request $request, $id) { abort(404); }
}
