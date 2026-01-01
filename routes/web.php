<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffRegisterController;
use App\Http\Controllers\OvertimeController;

// Test route to check if the application is working
Route::get('/test', function () {
    return 'Application is working!';
});

// Redirect root to login (only if not authenticated)
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isStaff()) {
            return redirect()->route('staff.dashboard');
        }
    }
    return redirect()->route('login');
});

// Simple login route without middleware for testing
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Password reset routes
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])->name('password.email');
Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Protected Routes (only for authenticated users)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Role-based dashboard routes
    Route::get('/admin/dashboard', [AuthController::class, 'adminDashboard'])
        ->middleware('role:admin')
        ->name('admin.dashboard');
    Route::get('/staff/dashboard', [AuthController::class, 'staffDashboard'])
        ->middleware('role:staff')
        ->name('staff.dashboard');

    // Staff leave routes
    Route::middleware(['role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/leave-application', [\App\Http\Controllers\LeaveController::class, 'application'])->name('leave-application');
    Route::post('/leave-application', [\App\Http\Controllers\LeaveController::class, 'store'])->name('leave-application.store');
    Route::get('/leave-status', [\App\Http\Controllers\LeaveController::class, 'status'])->name('leave-status');
    Route::get('/leave/{leave}/attachment', [\App\Http\Controllers\LeaveController::class, 'downloadAttachment'])->name('leave.attachment');
    // Staff overtime routes
    Route::get('/overtime-applyOt', [\App\Http\Controllers\OvertimeController::class, 'apply'])->name('applyOt');
    Route::get('/overtime-statusOt', [\App\Http\Controllers\OvertimeController::class, 'status'])->name('statusOt');
    Route::post('/overtime-applyOt', [\App\Http\Controllers\OvertimeController::class, 'store'])->name('applyOt.store');
    Route::post('/overtime-check-limit', [\App\Http\Controllers\OvertimeController::class, 'checkWeeklyLimit'])->name('applyOt.checkLimit');
    Route::get('/overtime-claimOt', [\App\Http\Controllers\OvertimeController::class, 'claim'])->name('claimOt');
    Route::post('/overtime-claimOt', [\App\Http\Controllers\OvertimeController::class, 'claimStore'])->name('claimOt.store');
    // Staff-facing personal timetable (read-only view of assigned shifts)
    Route::get('/timetable', [\App\Http\Controllers\StaffTimetableController::class, 'staffIndex'])->name('timetable');
    // Staff payslip route
    Route::get('/payslip', [\App\Http\Controllers\PayrollController::class, 'payslip'])->name('payslip');
    Route::get('/payslip/{month}', [\App\Http\Controllers\PayrollController::class, 'getPayslip'])->name('payslip.get');
    Route::get('/payslip/{month}/pdf', [\App\Http\Controllers\PayrollController::class, 'exportPayslipPdf'])->name('payslip.pdf');
    });
    
    // Admin group with auth + admin role middleware
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Staff management routes
    Route::get('/payslip', [\App\Http\Controllers\PayrollController::class, 'staffPayroll'])->name('payroll');
    Route::post('/payslip/update-bonus', [\App\Http\Controllers\PayrollController::class, 'updateBonus'])->name('payroll.update-bonus');
    Route::post('/payslip/publish', [\App\Http\Controllers\PayrollController::class, 'publishPayroll'])->name('payroll.publish');
    Route::post('/payslip/sync', [\App\Http\Controllers\PayrollController::class, 'syncPayroll'])->name('payroll.sync');
    Route::get('/staffPayslip', [\App\Http\Controllers\PayrollController::class, 'staffPayslip'])->name('payslip');
    Route::get('/payslip/{userId}/{month}', [\App\Http\Controllers\PayrollController::class, 'getStaffPayslip'])->name('payslip.get');
    Route::get('/payslip/{userId}/{month}/pdf', [\App\Http\Controllers\PayrollController::class, 'exportStaffPayslipPdf'])->name('payslip.pdf');
    Route::post('/payslip/{userId}/{month}/email', [\App\Http\Controllers\PayrollController::class, 'emailStaffPayslip'])->name('payslip.email');
    Route::get('staff-leave-status', [App\Http\Controllers\LeaveController::class, 'staffLeaveStatus'])->name('staff-leave-status');
    Route::get('/leave/{leave}/attachment', [App\Http\Controllers\LeaveController::class, 'downloadAttachment'])->name('leave.attachment');
    Route::get('/manage-staff', [StaffController::class, 'index'])->name('manage-staff');
    Route::get('/staff-timetable', [App\Http\Controllers\StaffTimetableController::class, 'index'])->name('staff-timetable');
    Route::post('/shifts', [App\Http\Controllers\StaffTimetableController::class, 'store'])->name('shifts.store');
    Route::post('/shifts/bulk', [App\Http\Controllers\StaffTimetableController::class, 'bulkStore'])->name('shifts.bulk');
    Route::put('/shifts/{shift}', [App\Http\Controllers\StaffTimetableController::class, 'update'])->name('shifts.update');
    Route::delete('/shifts/{shift}', [App\Http\Controllers\StaffTimetableController::class, 'destroy'])->name('shifts.destroy');
    Route::get('/staff/download-template', [StaffController::class, 'downloadTemplate'])->name('staff.download-template');
    Route::get('/staff/{user}', [StaffController::class, 'show'])->name('staff.show');
    Route::put('/staff/{user}', [StaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{user}', [StaffController::class, 'destroy'])->name('staff.destroy');
        
    // Staff registration route
    Route::post('/register', [StaffRegisterController::class, 'store'])->name('register');
    });
    
    // Overtime approval routes for admin (named with admin. prefix)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/overtimes/pending', [\App\Http\Controllers\Admin\OvertimeApprovalController::class, 'index'])->name('admin.overtimes.pending');
        Route::post('/overtimes/{overtime}/approve', [\App\Http\Controllers\Admin\OvertimeApprovalController::class, 'approve'])->name('admin.overtimes.approve');
        Route::post('/overtimes/{overtime}/reject', [\App\Http\Controllers\Admin\OvertimeApprovalController::class, 'reject'])->name('admin.overtimes.reject');

        // OT Claim approval routes for admin
        Route::post('/otclaims/{otClaim}/approve', [\App\Http\Controllers\Admin\OTClaimApprovalController::class, 'approve'])->name('admin.otclaims.approve');
        Route::post('/otclaims/{otClaim}/reject', [\App\Http\Controllers\Admin\OTClaimApprovalController::class, 'reject'])->name('admin.otclaims.reject');
    });

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
