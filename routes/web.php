<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    BranchController, RoleController, PermissionController, UserController,
    PatientController, OpdRecordController, PatientNoteController,
    ServiceController, PtServiceRateController, CoursePackageController, CommissionRateController,
    AppointmentController, QueueController, ConfirmationListController,
    StaffController, ScheduleController, LeaveRequestController, EvaluationController, PtReplacementController,
    InvoiceController, PaymentController, DocumentController, RefundController,
    TreatmentController, TreatmentAuditLogController, PtRequestController, FollowUpListController,
    CoursePurchaseController, CourseUsageLogController, CourseSharingController, CourseRenewalController,
    CommissionController, DfPaymentController, CommissionSplitController,
    EquipmentController, MaintenanceLogController, StockItemController, StockTransactionController,
    LoyaltyPointController, LoyaltyTransactionController, LoyaltyRewardController,
    AuditLogController, NotificationController,
    ReportController, SuperAdminController, BillingController
};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});

// Setup route for initial database seeding (one-time use)
Route::get('/setup-database-seed-now', function () {
    // Check if already seeded
    if (\App\Models\User::count() > 0) {
        return 'Database already has data. No seeding needed.';
    }

    try {
        \Artisan::call('db:seed', ['--force' => true]);
        return 'Database seeded successfully! You can now login. <a href="/login">Go to Login</a>';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

// ========================================
// Public Routes (No Auth Required)
// ========================================

// Public Booking Form
Route::get('/booking', [\App\Http\Controllers\PublicBookingController::class, 'index'])->name('booking.index');
Route::post('/booking', [\App\Http\Controllers\PublicBookingController::class, 'store'])->name('booking.store');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Queue Display (Public for TV screens)
Route::get('/queue/display', [QueueController::class, 'display'])->name('queue.display');

// ========================================
// Authenticated Routes (Auth Required)
// ========================================
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    // Branch Selector & Switcher (Admin only)
    Route::get('/select-branch', [AuthController::class, 'showBranchSelector'])->name('branch.selector');
    Route::post('/switch-branch', [AuthController::class, 'switchBranch'])->name('branch.switch');

    // ========================================
    // Patient Management
    // ========================================
    Route::resource('patients', PatientController::class);
    Route::get('/api/patients/search', [PatientController::class, 'search'])->name('api.patients.search');
    Route::post('/patients/{id}/purchase-course-online', [PatientController::class, 'purchaseCourseOnline'])->name('patients.purchase-course-online');

    // Patient Notes
    Route::resource('patient-notes', PatientNoteController::class);
    Route::get('/api/patient-notes', [PatientNoteController::class, 'index'])->name('api.patient-notes.index');

    // OPD Records
    Route::resource('opd-records', OpdRecordController::class);
    Route::post('/opd-records/{id}/close', [OpdRecordController::class, 'close'])->name('opd-records.close');
    Route::post('/opd-records/{id}/reopen', [OpdRecordController::class, 'reopen'])->name('opd-records.reopen');

    // ========================================
    // Appointments & Queue
    // ========================================
    Route::resource('appointments', AppointmentController::class);
    Route::get('/appointments/feed', [AppointmentController::class, 'feed'])->name('appointments.feed');
    Route::get('/api/appointments/summary', [AppointmentController::class, 'summary'])->name('appointments.summary');
    Route::post('/appointments/quick-store', [AppointmentController::class, 'quickStore'])->name('appointments.quickStore');
    Route::patch('/appointments/{id}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.updateStatus');
    Route::post('/appointments/{id}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');

    // Confirmation Lists
    Route::get('/confirmation-lists', [ConfirmationListController::class, 'index'])->name('confirmation-lists.index');
    Route::post('/confirmation-lists/auto-generate', [ConfirmationListController::class, 'autoGenerate'])->name('confirmation-lists.autoGenerate');
    Route::post('/confirmation-lists/{id}/confirm', [ConfirmationListController::class, 'confirmAttendance'])->name('confirmation-lists.confirm');
    Route::post('/confirmation-lists/{id}/cancel', [ConfirmationListController::class, 'requestCancel'])->name('confirmation-lists.cancel');
    Route::post('/confirmation-lists/{id}/reschedule', [ConfirmationListController::class, 'requestReschedule'])->name('confirmation-lists.reschedule');
    Route::post('/confirmation-lists/{id}/no-answer', [ConfirmationListController::class, 'markNoAnswer'])->name('confirmation-lists.noAnswer');

    // Queue Management
    Route::get('/queue', [QueueController::class, 'index'])->name('queue.index');
    Route::post('/queue/{id}/start', [QueueController::class, 'startTreatment'])->name('queue.start');
    Route::post('/queue/{id}/end', [QueueController::class, 'endTreatment'])->name('queue.end');
    Route::post('/queue/complete-appointment/{id}', [QueueController::class, 'completeAppointment'])->name('queue.completeAppointment');
    Route::post('/queue/finish-treatment/{id}', [QueueController::class, 'finishTreatment'])->name('queue.finishTreatment');
    Route::post('/queue/process-payment/{id}', [QueueController::class, 'processPayment'])->name('queue.processPayment');
    Route::post('/queue/{id}/cancel-treatment', [QueueController::class, 'cancelTreatment'])->name('queue.cancelTreatment');
    Route::post('/queue/{id}/cancel-payment', [QueueController::class, 'cancelPayment'])->name('queue.cancelPayment');
    Route::post('/queue/{id}/revert-complete', [QueueController::class, 'revertComplete'])->name('queue.revertComplete');
    Route::post('/queue/{id}/cancel', [QueueController::class, 'cancelQueue'])->name('queue.cancel');
    Route::get('/queue/{id}/receipt', [QueueController::class, 'printReceipt'])->name('queue.receipt');

    // Queue API Routes
    Route::get('/api/patient-courses/{patientId}', [QueueController::class, 'getPatientCourses']);
    Route::get('/api/patient-last-treatment/{patientId}', [QueueController::class, 'getPatientLastTreatment']);
    Route::get('/api/treatment-detail/{appointmentId}', [QueueController::class, 'getTreatmentDetail']);
    Route::get('/api/invoice-detail/{invoiceId}', [QueueController::class, 'getInvoiceDetail']);
    Route::get('/api/appointment-invoice/{appointmentId}', [QueueController::class, 'getAppointmentInvoice']);
    Route::post('/api/update-payment/{appointmentId}', [QueueController::class, 'updatePayment']);

    // ========================================
    // Billing & Payments
    // ========================================
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/process-payment', [BillingController::class, 'processPayment'])->name('billing.processPayment');
    Route::post('/billing/cancel-course', [BillingController::class, 'storeCancellation'])->name('billing.cancel-course');

    // Invoices
    Route::resource('invoices', InvoiceController::class);

    // Payments
    Route::resource('payments', PaymentController::class);

    // Refunds
    Route::resource('refunds', RefundController::class);

    // Documents
    Route::resource('documents', DocumentController::class);
    Route::get('/documents/{id}/download', [DocumentController::class, 'download'])->name('documents.download');

    // ========================================
    // Course Management
    // ========================================
    Route::resource('course-purchases', CoursePurchaseController::class);
    Route::get('/course-purchases/{id}/usage-history', [CoursePurchaseController::class, 'usageHistory'])->name('course-purchases.usage-history');

    // Course Usage Logs
    Route::get('/course-usage-logs/{id}', [CourseUsageLogController::class, 'show'])->name('course-usage-logs.show');
    Route::get('/api/course-usage-logs/{id}', [CourseUsageLogController::class, 'getUsageHistory'])->name('api.course-usage-logs.show');
    Route::resource('course-usage-logs', CourseUsageLogController::class)->except(['show']);

    // Course Sharing
    Route::resource('course-sharings', CourseSharingController::class);
    Route::post('/course-shared-users', [CourseSharingController::class, 'store'])->name('course-shared-users.store');
    Route::delete('/course-shared-users/{id}', [CourseSharingController::class, 'destroy'])->name('course-shared-users.destroy');
    Route::get('/api/course-shared-users/search-patient', [CourseSharingController::class, 'searchPatient'])->name('api.course-shared-users.search-patient');

    // Course Renewals
    Route::resource('course-renewals', CourseRenewalController::class);

    // ========================================
    // Services & Packages
    // ========================================
    Route::resource('services', ServiceController::class);
    Route::resource('course-packages', CoursePackageController::class);
    Route::resource('pt-service-rates', PtServiceRateController::class);

    // ========================================
    // Commission & Compensation
    // ========================================
    Route::get('/commission-rates', [CommissionRateController::class, 'index'])->name('commission-rates.index');
    Route::get('/commission-rates/create', [CommissionRateController::class, 'create'])->name('commission-rates.create');
    Route::post('/commission-rates', [CommissionRateController::class, 'store'])->name('commission-rates.store');
    Route::post('/commission-rates/update-salary', [CommissionRateController::class, 'updateSalary'])->name('commission-rates.update-salary');
    Route::post('/api/df-payments/manual', [CommissionRateController::class, 'storeManualDf'])->name('df-payments.manual');
    Route::get('/commission-rates/{user}/detail', [CommissionRateController::class, 'staffDetail'])->name('commission-rates.staff-detail');
    Route::get('/commission-rates/{commission_rate}/edit', [CommissionRateController::class, 'edit'])->name('commission-rates.edit')->where('commission_rate', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
    Route::get('/commission-rates/{commission_rate}', [CommissionRateController::class, 'show'])->name('commission-rates.show')->where('commission_rate', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
    Route::put('/commission-rates/{commission_rate}', [CommissionRateController::class, 'update'])->name('commission-rates.update')->where('commission_rate', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
    Route::patch('/commission-rates/{commission_rate}', [CommissionRateController::class, 'update'])->where('commission_rate', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
    Route::delete('/commission-rates/{commission_rate}', [CommissionRateController::class, 'destroy'])->name('commission-rates.destroy')->where('commission_rate', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');

    Route::resource('commissions', CommissionController::class);
    Route::post('/commissions/{id}/pay', [CommissionController::class, 'markAsPaid'])->name('commissions.pay');
    Route::post('/commissions/bulk-pay', [CommissionController::class, 'bulkPay'])->name('commissions.bulkPay');
    Route::post('/commissions/{id}/clawback', [CommissionController::class, 'clawback'])->name('commissions.clawback');
    Route::get('/api/commissions/pt-summary', [CommissionController::class, 'ptSummary'])->name('api.commissions.ptSummary');

    Route::resource('df-payments', DfPaymentController::class);
    Route::post('/df-payments/{id}/pay', [DfPaymentController::class, 'markAsPaid'])->name('df-payments.pay');
    Route::post('/df-payments/bulk-pay', [DfPaymentController::class, 'bulkPay'])->name('df-payments.bulkPay');

    Route::resource('commission-splits', CommissionSplitController::class);

    // ========================================
    // Reports
    // ========================================
    Route::get('/reports/pnl', [ReportController::class, 'profitAndLoss'])->name('reports.pnl');
    Route::get('/reports/dashboard', [ReportController::class, 'dashboard'])->name('reports.dashboard');

    // ========================================
    // Stock & Equipment
    // ========================================
    Route::get('/stock', [\App\Http\Controllers\StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/create', [\App\Http\Controllers\StockController::class, 'create'])->name('stock.create');
    Route::post('/stock', [\App\Http\Controllers\StockController::class, 'store'])->name('stock.store');
    Route::get('/stock/{stock}/edit', [\App\Http\Controllers\StockController::class, 'edit'])->name('stock.edit');
    Route::put('/stock/{stock}', [\App\Http\Controllers\StockController::class, 'update'])->name('stock.update');
    Route::delete('/stock/{stock}', [\App\Http\Controllers\StockController::class, 'destroy'])->name('stock.destroy');
    Route::post('/stock/{stock}/adjust', [\App\Http\Controllers\StockController::class, 'adjust'])->name('stock.adjust');
    Route::get('/stock/transactions', [\App\Http\Controllers\StockController::class, 'transactions'])->name('stock.transactions');

    Route::resource('stock-items', StockItemController::class);
    Route::post('/stock-items/{id}/adjust', [StockItemController::class, 'adjust'])->name('stock-items.adjust');
    Route::resource('stock-transactions', StockTransactionController::class);

    Route::get('/equipment', [EquipmentController::class, 'index'])->name('equipment.index');
    Route::get('/equipment/create', [EquipmentController::class, 'create'])->name('equipment.create');
    Route::post('/equipment', [EquipmentController::class, 'store'])->name('equipment.store');
    Route::get('/equipment/{equipment}', [EquipmentController::class, 'show'])->name('equipment.show');
    Route::get('/equipment/{equipment}/edit', [EquipmentController::class, 'edit'])->name('equipment.edit');
    Route::put('/equipment/{equipment}', [EquipmentController::class, 'update'])->name('equipment.update');
    Route::delete('/equipment/{equipment}', [EquipmentController::class, 'destroy'])->name('equipment.destroy');
    Route::post('/equipment/{equipment}/maintenance', [EquipmentController::class, 'recordMaintenance'])->name('equipment.maintenance');

    Route::resource('maintenance-logs', MaintenanceLogController::class);

    // ========================================
    // CRM & Follow-up
    // ========================================
    Route::get('/crm', [\App\Http\Controllers\CrmController::class, 'index'])->name('crm.index');
    Route::post('/crm/call/{id}', [\App\Http\Controllers\CrmController::class, 'updateCall'])->name('crm.call.update');
    Route::post('/crm/refresh', [\App\Http\Controllers\CrmController::class, 'refreshCalls'])->name('crm.refresh');

    Route::resource('follow-up-lists', FollowUpListController::class);
    Route::post('/follow-up-lists/auto-generate', [FollowUpListController::class, 'autoGenerate'])->name('follow-up-lists.auto-generate');

    // ========================================
    // Expenses
    // ========================================
    Route::get('/expenses', [\App\Http\Controllers\ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [\App\Http\Controllers\ExpenseController::class, 'store'])->name('expenses.store');
    Route::put('/expenses/{id}', [\App\Http\Controllers\ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{id}', [\App\Http\Controllers\ExpenseController::class, 'destroy'])->name('expenses.destroy');

    // ========================================
    // Staff & HR
    // ========================================
    Route::resource('staff', StaffController::class);

    Route::resource('schedules', ScheduleController::class);
    Route::post('/schedules/quick-store', [ScheduleController::class, 'quickStore'])->name('schedules.quickStore');
    Route::get('/api/schedules/calendar', [ScheduleController::class, 'calendarEvents'])->name('api.schedules.calendar');

    Route::resource('leave-requests', LeaveRequestController::class);
    Route::post('/leave-requests/{id}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
    Route::post('/leave-requests/{id}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');
    Route::get('/api/leave-requests/staff/{staffId}/summary', [LeaveRequestController::class, 'staffSummary'])->name('api.leave-requests.staffSummary');
    Route::resource('evaluations', EvaluationController::class);
    Route::post('/evaluations/{id}/complete', [EvaluationController::class, 'complete'])->name('evaluations.complete');

    Route::resource('pt-replacements', PtReplacementController::class);
    Route::resource('pt-requests', PtRequestController::class);
    Route::post('/pt-requests/{id}/approve', [PtRequestController::class, 'approve'])->name('pt-requests.approve');
    Route::post('/pt-requests/{id}/reject', [PtRequestController::class, 'reject'])->name('pt-requests.reject');

    // ========================================
    // Treatment
    // ========================================
    Route::resource('treatments', TreatmentController::class);
    Route::resource('treatment-audit-logs', TreatmentAuditLogController::class);

    // ========================================
    // Marketing & Loyalty
    // ========================================
    Route::resource('loyalty-points', LoyaltyPointController::class);
    Route::resource('loyalty-transactions', LoyaltyTransactionController::class);
    Route::resource('loyalty-rewards', LoyaltyRewardController::class);

    // ========================================
    // System Administration
    // ========================================
    Route::resource('users', UserController::class);
    Route::resource('branches', BranchController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);

    // Super Admin
    Route::get('/super-admin', [SuperAdminController::class, 'index'])->name('super-admin.index');
    Route::get('/api/super-admin/users', [SuperAdminController::class, 'getUsers']);
    Route::get('/api/super-admin/users/{id}', [SuperAdminController::class, 'getUser']);
    Route::post('/api/super-admin/users', [SuperAdminController::class, 'storeUser']);
    Route::put('/api/super-admin/users/{id}', [SuperAdminController::class, 'updateUser']);
    Route::delete('/api/super-admin/users/{id}', [SuperAdminController::class, 'deleteUser']);

    // Audit Logs & Notifications
    Route::resource('audit-logs', AuditLogController::class);
    Route::resource('notifications', NotificationController::class);
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.markRead');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
    Route::get('/api/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('api.notifications.unreadCount');
    Route::get('/api/notifications/recent', [NotificationController::class, 'recent'])->name('api.notifications.recent');

    // API endpoints for roles and branches (for dropdowns)
    Route::get('/api/roles', function() {
        return response()->json(\App\Models\Role::all());
    });
    Route::get('/api/branches', function() {
        return response()->json(\App\Models\Branch::all());
    });
});
