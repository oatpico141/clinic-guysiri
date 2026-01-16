<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Staff;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    /**
     * Display schedule list (calendar view)
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfWeek()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfWeek()->format('Y-m-d'));

        $query = Schedule::with(['staff', 'branch'])
            ->whereBetween('schedule_date', [$startDate, $endDate]);

        // Filter by staff
        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $schedules = $query->orderBy('schedule_date')->orderBy('start_time')->get();
        $staffs = Staff::where('employment_status', 'active')->orderBy('first_name')->get();
        $branches = Branch::all();

        // Group schedules by date for calendar view
        $schedulesByDate = $schedules->groupBy(function($schedule) {
            return $schedule->schedule_date->format('Y-m-d');
        });

        // Generate week dates
        $weekDates = [];
        $current = Carbon::parse($startDate);
        while ($current->lte(Carbon::parse($endDate))) {
            $weekDates[] = $current->copy();
            $current->addDay();
        }

        // Stats
        $totalSchedules = $schedules->count();
        $activeSchedules = $schedules->where('status', 'scheduled')->count();
        $completedSchedules = $schedules->where('status', 'completed')->count();

        return view('schedules.index', compact(
            'schedules', 'schedulesByDate', 'weekDates',
            'staffs', 'branches', 'startDate', 'endDate',
            'totalSchedules', 'activeSchedules', 'completedSchedules'
        ));
    }

    /**
     * Show create schedule form
     */
    public function create()
    {
        $staffs = Staff::where('employment_status', 'active')->orderBy('first_name')->get();
        $branches = Branch::all();

        return view('schedules.create', compact('staffs', 'branches'));
    }

    /**
     * Store new schedule
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'branch_id' => 'required|exists:branches,id',
            'schedule_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'schedule_type' => 'nullable|in:regular,overtime,training,meeting',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i|after:break_start',
            'is_recurring' => 'boolean',
            'recurrence_pattern' => 'nullable|in:daily,weekly,monthly',
            'recurrence_end_date' => 'nullable|date|after:schedule_date',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $validated['status'] = 'scheduled';
            $validated['is_available'] = true;
            $validated['created_by'] = auth()->id();

            // Create main schedule
            $schedule = Schedule::create($validated);

            // Handle recurring schedules
            if ($request->is_recurring && $request->recurrence_pattern && $request->recurrence_end_date) {
                $this->createRecurringSchedules($validated);
            }

            DB::commit();

            return redirect()
                ->route('schedules.index', ['start_date' => $validated['schedule_date']])
                ->with('success', 'เพิ่มตารางงานสำเร็จ');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Create recurring schedules
     */
    private function createRecurringSchedules(array $data)
    {
        $startDate = Carbon::parse($data['schedule_date']);
        $endDate = Carbon::parse($data['recurrence_end_date']);
        $pattern = $data['recurrence_pattern'];

        $currentDate = $startDate->copy();

        switch ($pattern) {
            case 'daily':
                $currentDate->addDay();
                break;
            case 'weekly':
                $currentDate->addWeek();
                break;
            case 'monthly':
                $currentDate->addMonth();
                break;
        }

        while ($currentDate->lte($endDate)) {
            Schedule::create([
                'staff_id' => $data['staff_id'],
                'branch_id' => $data['branch_id'],
                'schedule_date' => $currentDate->format('Y-m-d'),
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'schedule_type' => $data['schedule_type'] ?? 'regular',
                'status' => 'scheduled',
                'is_available' => true,
                'break_start' => $data['break_start'] ?? null,
                'break_end' => $data['break_end'] ?? null,
                'is_recurring' => true,
                'recurrence_pattern' => $pattern,
                'notes' => $data['notes'] ?? null,
                'created_by' => $data['created_by'],
            ]);

            switch ($pattern) {
                case 'daily':
                    $currentDate->addDay();
                    break;
                case 'weekly':
                    $currentDate->addWeek();
                    break;
                case 'monthly':
                    $currentDate->addMonth();
                    break;
            }
        }
    }

    /**
     * Display schedule details
     */
    public function show($id)
    {
        $schedule = Schedule::with(['staff', 'branch'])->findOrFail($id);

        return view('schedules.show', compact('schedule'));
    }

    /**
     * Show edit schedule form
     */
    public function edit($id)
    {
        $schedule = Schedule::findOrFail($id);
        $staffs = Staff::where('employment_status', 'active')->orderBy('first_name')->get();
        $branches = Branch::all();

        return view('schedules.edit', compact('schedule', 'staffs', 'branches'));
    }

    /**
     * Update schedule
     */
    public function update(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);

        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'branch_id' => 'required|exists:branches,id',
            'schedule_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'schedule_type' => 'nullable|in:regular,overtime,training,meeting',
            'status' => 'required|in:scheduled,completed,cancelled',
            'is_available' => 'boolean',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i|after:break_start',
            'notes' => 'nullable|string',
        ]);

        try {
            $schedule->update($validated);

            return redirect()
                ->route('schedules.index', ['start_date' => $validated['schedule_date']])
                ->with('success', 'แก้ไขตารางงานสำเร็จ');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Delete schedule
     */
    public function destroy($id)
    {
        try {
            $schedule = Schedule::findOrFail($id);
            $schedule->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบตารางงานสำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get schedules for calendar
     */
    public function calendarEvents(Request $request)
    {
        $startDate = $request->input('start', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end', now()->endOfMonth()->format('Y-m-d'));

        $query = Schedule::with(['staff', 'branch'])
            ->whereBetween('schedule_date', [$startDate, $endDate]);

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $schedules = $query->get();

        $events = $schedules->map(function($schedule) {
            return [
                'id' => $schedule->id,
                'title' => $schedule->staff->first_name . ' ' . $schedule->staff->last_name,
                'start' => $schedule->schedule_date->format('Y-m-d') . 'T' . $schedule->start_time,
                'end' => $schedule->schedule_date->format('Y-m-d') . 'T' . $schedule->end_time,
                'color' => $this->getStatusColor($schedule->status),
                'extendedProps' => [
                    'staff_id' => $schedule->staff_id,
                    'branch' => $schedule->branch->name ?? '',
                    'status' => $schedule->status,
                    'type' => $schedule->schedule_type,
                ]
            ];
        });

        return response()->json($events);
    }

    /**
     * Get color by status
     */
    private function getStatusColor($status)
    {
        return match($status) {
            'scheduled' => '#3b82f6',
            'completed' => '#10b981',
            'cancelled' => '#ef4444',
            default => '#6b7280',
        };
    }

    /**
     * Quick add schedule (AJAX)
     */
    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'branch_id' => 'required|exists:branches,id',
            'schedule_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        try {
            $validated['status'] = 'scheduled';
            $validated['is_available'] = true;
            $validated['created_by'] = auth()->id();

            $schedule = Schedule::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'เพิ่มตารางงานสำเร็จ',
                'schedule' => $schedule->load(['staff', 'branch'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }
}
