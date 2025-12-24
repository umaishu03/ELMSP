<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\OTClaim;

class PayrollController extends Controller
{
    public function staffPayroll()
    {
        // Get all staff with user info
        $staffList = Staff::with('user')
            ->where('status', 'active')
            ->get()
            ->map(function ($staff) {
                $user = $staff->user;
                $monthsWorked = $staff->hire_date ? $staff->hire_date->diffInMonths(now()) : 0;
                $fixedCommission = $monthsWorked >= 3 ? 200 : 0;
                $basic_salary = $staff->salary ?? 0;
                $department = $staff->department;
                $role = $user->role ?? '';
                $name = $user->name ?? '';
                $start_date = $staff->hire_date;

                // Calculate OT claims for payroll (approved, this month)
                $otClaims = OTClaim::whereHas('payroll', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    })
                    ->approved()
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->get();

                $normal_ot_hours = $otClaims->sum('fulltime_hours');
                $ph_ot_hours = $otClaims->sum('public_holiday_hours');

                // Public holiday pay (worked on PH, not OT):
                // You may need to adjust this if you track PH work separately
                $public_holiday_hours = $staff->public_holiday_hours ?? 0;
                $public_holiday_pay = 15.38 * $public_holiday_hours;

                return (object) [
                    'name' => $name,
                    'role' => $role,
                    'department' => $department,
                    'basic_salary' => $basic_salary,
                    'fixed_commission' => $fixedCommission,
                    'public_holiday_hours' => $public_holiday_hours,
                    'normal_ot_hours' => $normal_ot_hours,
                    'ph_ot_hours' => $ph_ot_hours,
                    'start_date' => $start_date,
                ];
            });

        return view('admin.managePayroll', compact('staffList'));
    }
    public function staffPayslip()
    {
        // Get all staff for selection
        $staffList = Staff::with('user')
            ->where('status', 'active')
            ->get();
        
        return view('admin.staffPayslip', compact('staffList'));
    }

    /**
     * Get payslip for a specific staff and month (JSON response)
     */
    public function getStaffPayslip($userId, $month)
    {
        // Validate month format YYYY-MM
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            return response()->json(['success' => false, 'message' => 'Invalid month format'], 400);
        }

        list($year, $monthNum) = explode('-', $month);

        // Get payroll record
        $payroll = \App\Models\Payroll::where('user_id', $userId)
            ->where('year', $year)
            ->where('month', $monthNum)
            ->first();

        if (!$payroll) {
            return response()->json(['success' => false, 'message' => 'Payslip not found for selected period'], 404);
        }

        // Get user and staff details
        $user = \App\Models\User::find($userId);
        $staff = $user->staff;

        // Render payslip HTML
        $html = view('partials.payslip-template', [
            'payroll' => $payroll,
            'user' => $user,
            'staff' => $staff,
            'month' => $month
        ])->render();

        return response()->json(['success' => true, 'html' => $html]);
    }

    /**
     * Export payslip as PDF (admin)
     */
    public function exportStaffPayslipPdf($userId, $month)
    {
        // Validate month format
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            abort(400, 'Invalid month format');
        }

        list($year, $monthNum) = explode('-', $month);

        // Get payroll record
        $payroll = \App\Models\Payroll::where('user_id', $userId)
            ->where('year', $year)
            ->where('month', $monthNum)
            ->firstOrFail();

        $user = \App\Models\User::find($userId);
        $staff = $user->staff;

        // Generate PDF using a library like mPDF or DomPDF
        // For now, return a simple download response
        $filename = 'payslip_' . $user->name . '_' . $month . '.pdf';
        
        // TODO: Implement PDF generation
        // Option 1: Use barryvdh/laravel-dompdf
        // Option 2: Use mpdf/mpdf
        
        return response()->download(storage_path('payslips/' . $filename), $filename);
    }

    /**
     * Send payslip via email (admin)
     */
    public function emailStaffPayslip(Request $request, $userId, $month)
    {
        // Validate month format
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            return response()->json(['success' => false, 'message' => 'Invalid month format'], 400);
        }

        list($year, $monthNum) = explode('-', $month);

        // Get payroll and user
        $payroll = \App\Models\Payroll::where('user_id', $userId)
            ->where('year', $year)
            ->where('month', $monthNum)
            ->firstOrFail();

        $user = \App\Models\User::find($userId);

        // Send email with payslip
        // TODO: Create mailable and send
        \Mail::to($user->email)->send(new \App\Mail\PayslipMail($payroll, $user));

        return response()->json(['success' => true, 'message' => 'Payslip sent to ' . $user->email]);
    }
    public function payslip()
    {
        return view('staff.payslip');
    }

    /**
     * Get current staff's payslip for a specific month (JSON response)
     */
    public function getPayslip($month)
    {
        // Validate month format YYYY-MM
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            return response()->json(['success' => false, 'message' => 'Invalid month format'], 400);
        }

        list($year, $monthNum) = explode('-', $month);

        // Get current user
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Get payroll record
        $payroll = \App\Models\Payroll::where('user_id', $user->id)
            ->where('year', $year)
            ->where('month', $monthNum)
            ->first();

        if (!$payroll) {
            return response()->json(['success' => false, 'message' => 'Payslip not found for selected period'], 404);
        }

        $staff = $user->staff;

        // Render payslip HTML
        $html = view('partials.payslip-template', [
            'payroll' => $payroll,
            'user' => $user,
            'staff' => $staff,
            'month' => $month
        ])->render();

        return response()->json(['success' => true, 'html' => $html]);
    }

    /**
     * Export current staff's payslip as PDF
     */
    public function exportPayslipPdf($month)
    {
        // Validate month format
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            abort(400, 'Invalid month format');
        }

        list($year, $monthNum) = explode('-', $month);

        // Get current user
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Unauthorized');
        }

        // Get payroll record
        $payroll = \App\Models\Payroll::where('user_id', $user->id)
            ->where('year', $year)
            ->where('month', $monthNum)
            ->firstOrFail();

        $staff = $user->staff;

        // TODO: Generate PDF
        $filename = 'payslip_' . $user->name . '_' . $month . '.pdf';
        
        return response()->download(storage_path('payslips/' . $filename), $filename);
    }
}
