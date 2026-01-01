<?php

namespace App\Services;

use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\Shift;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\OTClaim;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ChatbotService
{
    /**
     * Process user message and return response
     */
    public function processMessage(string $message, User $user): array
    {
        $message = strtolower(trim($message));
        
        // Check for greetings
        if ($this->matchesKeywords($message, ['hello', 'hi', 'hey', 'greetings', 'good morning', 'good afternoon', 'good evening'])) {
            return $this->getGreetingResponse($user);
        }

        // Check for help
        if ($this->matchesKeywords($message, ['help', 'what can you do', 'commands', 'options'])) {
            return $this->getHelpResponse($user);
        }

        // Admin-specific queries
        if ($user->isAdmin()) {
            // Check for pending requests
            if ($this->matchesKeywords($message, ['pending', 'requests', 'approve', 'review', 'pending requests', 'pending leave', 'pending overtime', 'pending payroll'])) {
                return $this->handleAdminPendingRequests($user);
            }
            
            // Check for staff management queries
            if ($this->matchesKeywords($message, ['staff', 'employee', 'staff management', 'staff information', 'all staff', 'staff list'])) {
                return $this->handleAdminStaffQuery($message, $user);
            }
        }

        // Leave-related queries
        if ($this->matchesKeywords($message, ['leave', 'vacation', 'holiday', 'time off', 'leave balance', 'leave status', 'leave application'])) {
            return $this->handleLeaveQuery($message, $user);
        }

        // Shift-related queries
        if ($this->matchesKeywords($message, ['shift', 'schedule', 'timetable', 'work hours', 'working hours', 'shift schedule'])) {
            return $this->handleShiftQuery($message, $user);
        }

        // Overtime-related queries
        if ($this->matchesKeywords($message, ['overtime', 'ot', 'overtime hours', 'overtime status', 'overtime claim'])) {
            return $this->handleOvertimeQuery($message, $user);
        }

        // Payroll-related queries
        if ($this->matchesKeywords($message, ['payroll', 'salary', 'payslip', 'payment', 'wage', 'income'])) {
            return $this->handlePayrollQuery($message, $user);
        }

        // System information
        if ($this->matchesKeywords($message, ['system', 'about', 'information', 'info', 'what is', 'tell me about'])) {
            return $this->handleSystemInfoQuery($message, $user);
        }

        // Default response
        $isAdmin = $user->isAdmin();
        if ($isAdmin) {
            return [
                'response' => "I'm not sure how to help with that. Try asking about:\n• Pending requests (leave, overtime, payroll)\n• Staff management and information\n• Staff leave applications\n• Staff schedules and timetables\n• Payroll and payslip management\n• System statistics\n\nType 'help' to see all available commands.",
                'type' => 'info'
            ];
        } else {
            return [
                'response' => "I'm not sure how to help with that. Try asking about:\n• Leave balance or status\n• Shift schedule\n• Overtime hours or status\n• Payroll or payslip\n• System information\n\nType 'help' to see all available commands.",
                'type' => 'info'
            ];
        }
    }

    /**
     * Check if message matches any keywords
     */
    private function matchesKeywords(string $message, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get greeting response
     */
    private function getGreetingResponse(User $user): array
    {
        $hour = (int) date('H');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
        $isAdmin = $user->isAdmin();
        
        if ($isAdmin) {
            return [
                'response' => "{$greeting}, {$user->name}! 👋\n\nI'm your ELMSP assistant. I can help you with:\n• Managing staff and their information\n• Reviewing and approving leave requests\n• Reviewing and approving overtime requests\n• Managing payroll and payslips\n• Viewing staff schedules and timetables\n• System statistics and information\n\nType 'help' to see all available commands.",
                'type' => 'greeting'
            ];
        } else {
            return [
                'response' => "{$greeting}, {$user->name}! 👋\n\nI'm your ELMSP assistant. I can help you with:\n• Leave information and applications\n• Shift schedules\n• Overtime status and claims\n• Payroll and payslip information\n• System information\n\nType 'help' to see all available commands.",
                'type' => 'greeting'
            ];
        }
    }

    /**
     * Get help response
     */
    private function getHelpResponse(User $user): array
    {
        $isAdmin = $user->isAdmin();
        
        if ($isAdmin) {
            $commands = "Here are the things I can help you with:\n\n";
            $commands .= "👥 **Staff Management**\n";
            $commands .= "• View staff information\n";
            $commands .= "• Check staff leave balances\n";
            $commands .= "• View staff schedules\n";
            $commands .= "• Staff payroll information\n\n";
            
            $commands .= "📋 **Pending Requests**\n";
            $commands .= "• Check pending leave requests\n";
            $commands .= "• Check pending overtime requests\n";
            $commands .= "• Check pending payroll claims\n";
            $commands .= "• Check pending replacement leave claims\n\n";
            
            $commands .= "📅 **Leave Management**\n";
            $commands .= "• View all staff leave applications\n";
            $commands .= "• Check leave status across staff\n";
            $commands .= "• Leave approval information\n\n";
            
            $commands .= "⏱️ **Overtime Management**\n";
            $commands .= "• View pending overtime requests\n";
            $commands .= "• Check overtime approvals\n";
            $commands .= "• Overtime claim reviews\n\n";
            
            $commands .= "💰 **Payroll Management**\n";
            $commands .= "• View staff payslips\n";
            $commands .= "• Generate payroll\n";
            $commands .= "• Payroll statistics\n\n";
            
            $commands .= "📊 **System Information**\n";
            $commands .= "• System statistics\n";
            $commands .= "• Staff overview\n";
            $commands .= "• Department information\n\n";
            
            $commands .= "💡 **Examples:**\n";
            $commands .= "• \"How many pending requests do I have?\"\n";
            $commands .= "• \"Show staff leave applications\"\n";
            $commands .= "• \"What are the pending overtime requests?\"\n";
            $commands .= "• \"View staff payroll information\"\n";
        } else {
            $commands = "Here are the things I can help you with:\n\n";
            $commands .= "📅 **Leave**\n";
            $commands .= "• Check leave balance\n";
            $commands .= "• View leave status\n";
            $commands .= "• Leave application information\n\n";
            
            $commands .= "⏰ **Shifts**\n";
            $commands .= "• View shift schedule\n";
            $commands .= "• Check working hours\n";
            $commands .= "• Timetable information\n\n";
            
            $commands .= "⏱️ **Overtime**\n";
            $commands .= "• Check overtime hours\n";
            $commands .= "• View overtime status\n";
            $commands .= "• Overtime claim information\n\n";
            
            $commands .= "💰 **Payroll**\n";
            $commands .= "• View payslip\n";
            $commands .= "• Check salary information\n";
            $commands .= "• Payment details\n\n";
            
            $commands .= "💡 **Examples:**\n";
            $commands .= "• \"What's my leave balance?\"\n";
            $commands .= "• \"Show my shifts this week\"\n";
            $commands .= "• \"How many overtime hours do I have?\"\n";
            $commands .= "• \"When is my next payslip?\"\n";
        }
        
        return [
            'response' => $commands,
            'type' => 'help'
        ];
    }

    /**
     * Handle leave-related queries
     */
    private function handleLeaveQuery(string $message, User $user): array
    {
        $staff = $user->staff;
        
        if (!$staff) {
            return [
                'response' => "I couldn't find your staff information. Please contact your administrator.",
                'type' => 'error'
            ];
        }

        // Check leave balance
        if ($this->matchesKeywords($message, ['balance', 'remaining', 'available', 'left'])) {
            return $this->getLeaveBalance($staff);
        }

        // Check leave status
        if ($this->matchesKeywords($message, ['status', 'application', 'pending', 'approved', 'rejected'])) {
            return $this->getLeaveStatus($staff);
        }

        // General leave information
        return [
            'response' => "I can help you with leave information. Try asking:\n• \"What's my leave balance?\"\n• \"What's my leave status?\"\n• \"Show my leave applications\"\n\nYou can also apply for leave through the Leave menu in the sidebar.",
            'type' => 'info'
        ];
    }

    /**
     * Get leave balance information
     */
    private function getLeaveBalance($staff): array
    {
        $balances = LeaveBalance::where('staff_id', $staff->id)
            ->with('leaveType')
            ->get();

        if ($balances->isEmpty()) {
            return [
                'response' => "You don't have any leave balances set up yet. Please contact your administrator.",
                'type' => 'info'
            ];
        }

        $response = "📅 **Your Leave Balance:**\n\n";
        foreach ($balances as $balance) {
            $typeName = $balance->leaveType->type_name ?? 'Unknown';
            $response .= "• **" . ucfirst($typeName) . " Leave:**\n";
            $response .= "  - Total: " . number_format($balance->total_days, 1) . " days\n";
            $response .= "  - Used: " . number_format($balance->used_days, 1) . " days\n";
            $response .= "  - Remaining: " . number_format($balance->remaining_days, 1) . " days\n\n";
        }

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Get leave status information
     */
    private function getLeaveStatus($staff): array
    {
        $leaves = Leave::where('staff_id', $staff->id)
            ->with('leaveType')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        if ($leaves->isEmpty()) {
            return [
                'response' => "You don't have any leave applications yet. You can apply for leave through the Leave menu in the sidebar.",
                'type' => 'info'
            ];
        }

        $response = "📋 **Recent Leave Applications:**\n\n";
        foreach ($leaves as $leave) {
            $typeName = $leave->leaveType->type_name ?? 'Unknown';
            $status = ucfirst($leave->status);
            $statusEmoji = $leave->status === 'approved' ? '✅' : ($leave->status === 'pending' ? '⏳' : '❌');
            
            $response .= "{$statusEmoji} **" . ucfirst($typeName) . " Leave**\n";
            $response .= "  - Period: {$leave->start_date->format('M d')} to {$leave->end_date->format('M d, Y')}\n";
            $response .= "  - Days: {$leave->total_days}\n";
            $response .= "  - Status: {$status}\n";
            if ($leave->reason) {
                $response .= "  - Reason: " . substr($leave->reason, 0, 50) . (strlen($leave->reason) > 50 ? '...' : '') . "\n";
            }
            $response .= "\n";
        }

        $response .= "View all leave applications in the Leave Status page.";

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Handle shift-related queries
     */
    private function handleShiftQuery(string $message, User $user): array
    {
        $staff = $user->staff;
        
        if (!$staff) {
            return [
                'response' => "I couldn't find your staff information. Please contact your administrator.",
                'type' => 'error'
            ];
        }

        // Check for specific time period
        $thisWeek = $this->matchesKeywords($message, ['this week', 'current week', 'week']);
        $today = $this->matchesKeywords($message, ['today', 'now', 'current']);
        $nextWeek = $this->matchesKeywords($message, ['next week', 'upcoming']);

        if ($today) {
            return $this->getTodayShifts($staff);
        } elseif ($thisWeek || $nextWeek) {
            return $this->getWeekShifts($staff, $nextWeek);
        }

        // General shift information
        return $this->getShiftSummary($staff);
    }

    /**
     * Get today's shifts
     */
    private function getTodayShifts($staff): array
    {
        $today = Carbon::today();
        $shift = Shift::where('staff_id', $staff->id)
            ->whereDate('date', $today)
            ->first();

        if (!$shift) {
            return [
                'response' => "You don't have a shift scheduled for today. Check your timetable for your schedule.",
                'type' => 'info'
            ];
        }

        if ($shift->rest_day) {
            return [
                'response' => "Today is your rest day. Enjoy your day off! 😊",
                'type' => 'info'
            ];
        }

        $response = "📅 **Today's Shift:**\n\n";
        $response .= "• Date: {$shift->date->format('l, F d, Y')}\n";
        $response .= "• Time: {$shift->start_time} - {$shift->end_time}\n";
        if ($shift->break_minutes) {
            $response .= "• Break: {$shift->break_minutes} minutes\n";
        }

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Get week shifts
     */
    private function getWeekShifts($staff, bool $nextWeek = false): array
    {
        $startDate = Carbon::now()->startOfWeek();
        if ($nextWeek) {
            $startDate->addWeek();
        }
        $endDate = $startDate->copy()->endOfWeek();

        $shifts = Shift::where('staff_id', $staff->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        if ($shifts->isEmpty()) {
            return [
                'response' => "You don't have any shifts scheduled for " . ($nextWeek ? 'next' : 'this') . " week. Check your timetable for your schedule.",
                'type' => 'info'
            ];
        }

        $response = "📅 **Shifts for " . ($nextWeek ? 'Next' : 'This') . " Week:**\n\n";
        foreach ($shifts as $shift) {
            if ($shift->rest_day) {
                $response .= "• {$shift->date->format('l, M d')}: Rest Day\n";
            } else {
                $response .= "• {$shift->date->format('l, M d')}: {$shift->start_time} - {$shift->end_time}\n";
            }
        }

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Get shift summary
     */
    private function getShiftSummary($staff): array
    {
        $upcomingShifts = Shift::where('staff_id', $staff->id)
            ->where('date', '>=', Carbon::today())
            ->orderBy('date')
            ->take(5)
            ->get();

        if ($upcomingShifts->isEmpty()) {
            return [
                'response' => "You don't have any upcoming shifts scheduled. Check your timetable for your schedule.",
                'type' => 'info'
            ];
        }

        $response = "📅 **Upcoming Shifts:**\n\n";
        foreach ($upcomingShifts as $shift) {
            if ($shift->rest_day) {
                $response .= "• {$shift->date->format('M d, Y (l)')}: Rest Day\n";
            } else {
                $response .= "• {$shift->date->format('M d, Y (l)')}: {$shift->start_time} - {$shift->end_time}\n";
            }
        }

        $response .= "\nView your full timetable in the My Timetable page.";

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Handle overtime-related queries
     */
    private function handleOvertimeQuery(string $message, User $user): array
    {
        $staff = $user->staff;
        
        if (!$staff) {
            return [
                'response' => "I couldn't find your staff information. Please contact your administrator.",
                'type' => 'error'
            ];
        }

        // Check overtime hours
        if ($this->matchesKeywords($message, ['hours', 'total', 'how many'])) {
            return $this->getOvertimeHours($staff);
        }

        // Check overtime status
        if ($this->matchesKeywords($message, ['status', 'pending', 'approved', 'rejected'])) {
            return $this->getOvertimeStatus($staff);
        }

        // General overtime information
        return [
            'response' => "I can help you with overtime information. Try asking:\n• \"How many overtime hours do I have?\"\n• \"What's my overtime status?\"\n• \"Show my overtime applications\"\n\nYou can apply for overtime or claim overtime through the Overtime menu in the sidebar.",
            'type' => 'info'
        ];
    }

    /**
     * Get overtime hours
     */
    private function getOvertimeHours($staff): array
    {
        $approvedOT = Overtime::where('staff_id', $staff->id)
            ->where('status', 'approved')
            ->get();

        $totalHours = $approvedOT->sum('hours');
        $claimedHours = $approvedOT->where('claimed', true)->sum('hours');
        $unclaimedHours = $totalHours - $claimedHours;

        $response = "⏱️ **Your Overtime Summary:**\n\n";
        $response .= "• Total Approved Hours: {$totalHours} hours\n";
        $response .= "• Claimed Hours: {$claimedHours} hours\n";
        $response .= "• Unclaimed Hours: {$unclaimedHours} hours\n\n";

        if ($unclaimedHours > 0) {
            $response .= "You can claim these hours for payroll or replacement leave through the Overtime Claim page.";
        } else {
            $response .= "All your overtime hours have been claimed.";
        }

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Get overtime status
     */
    private function getOvertimeStatus($staff): array
    {
        $overtimes = Overtime::where('staff_id', $staff->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        if ($overtimes->isEmpty()) {
            return [
                'response' => "You don't have any overtime applications yet. You can apply for overtime through the Overtime menu in the sidebar.",
                'type' => 'info'
            ];
        }

        $response = "📋 **Recent Overtime Applications:**\n\n";
        foreach ($overtimes as $ot) {
            $status = ucfirst($ot->status);
            $statusEmoji = $ot->status === 'approved' ? '✅' : ($ot->status === 'pending' ? '⏳' : '❌');
            
            $response .= "{$statusEmoji} **Overtime**\n";
            $response .= "  - Date: {$ot->ot_date->format('M d, Y')}\n";
            $response .= "  - Hours: {$ot->hours} hours\n";
            $response .= "  - Type: " . ucfirst(str_replace('_', ' ', $ot->ot_type)) . "\n";
            $response .= "  - Status: {$status}\n";
            if ($ot->claimed) {
                $response .= "  - Claimed: Yes\n";
            }
            $response .= "\n";
        }

        $response .= "View all overtime applications in the Overtime Status page.";

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Handle payroll-related queries
     */
    private function handlePayrollQuery(string $message, User $user): array
    {
        $staff = $user->staff;
        
        if (!$staff) {
            return [
                'response' => "I couldn't find your staff information. Please contact your administrator.",
                'type' => 'error'
            ];
        }

        // Check for payslip
        if ($this->matchesKeywords($message, ['payslip', 'payslip', 'salary slip'])) {
            return $this->getPayslipInfo($user);
        }

        // Check salary information
        if ($this->matchesKeywords($message, ['salary', 'wage', 'payment', 'income'])) {
            return $this->getSalaryInfo($staff);
        }

        // General payroll information
        return [
            'response' => "I can help you with payroll information. Try asking:\n• \"Show my payslip\"\n• \"What's my salary?\"\n• \"When is my next payment?\"\n\nYou can view your payslips in the Payslip page.",
            'type' => 'info'
        ];
    }

    /**
     * Get payslip information
     */
    private function getPayslipInfo(User $user): array
    {
        $payrolls = Payroll::where('user_id', $user->id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(3)
            ->get();

        if ($payrolls->isEmpty()) {
            return [
                'response' => "You don't have any payslips available yet. Payslips are generated monthly by your administrator.",
                'type' => 'info'
            ];
        }

        $response = "💰 **Recent Payslips:**\n\n";
        foreach ($payrolls as $payroll) {
            $monthName = Carbon::create()->month($payroll->month)->format('F');
            $response .= "• **{$monthName} {$payroll->year}**\n";
            $response .= "  - Gross Salary: RM " . number_format($payroll->gross_salary, 2) . "\n";
            $response .= "  - Net Salary: RM " . number_format($payroll->net_salary, 2) . "\n";
            $response .= "  - Status: " . ucfirst($payroll->status) . "\n\n";
        }

        $response .= "View and download your payslips in the Payslip page.";

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Get salary information
     */
    private function getSalaryInfo($staff): array
    {
        $response = "💰 **Salary Information:**\n\n";
        $response .= "• Basic Salary: RM " . number_format($staff->salary, 2) . "\n";
        $response .= "• Department: " . ucfirst($staff->department) . "\n";
        $response .= "• Employee ID: {$staff->employee_id}\n\n";
        $response .= "For detailed payslip information, check the Payslip page.";

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Handle system information queries
     */
    private function handleSystemInfoQuery(string $message, User $user): array
    {
        $isAdmin = $user->isAdmin();
        
        $response = "ℹ️ **ELMSP System Information:**\n\n";
        $response .= "ELMSP (Employee Leave Management System & Payroll) is a comprehensive system for managing:\n\n";
        $response .= "📅 **Leave Management**\n";
        $response .= "• Apply for various leave types\n";
        $response .= "• Track leave balances\n";
        $response .= "• View leave status\n\n";
        
        $response .= "⏰ **Shift Management**\n";
        $response .= "• View your work schedule\n";
        $response .= "• Check shift timings\n";
        $response .= "• Manage rest days\n\n";
        
        $response .= "⏱️ **Overtime Management**\n";
        $response .= "• Apply for overtime\n";
        $response .= "• Claim overtime for payroll or replacement leave\n";
        $response .= "• Track overtime hours\n\n";
        
        $response .= "💰 **Payroll System**\n";
        $response .= "• View payslips\n";
        $response .= "• Check salary information\n";
        $response .= "• Download payslip PDFs\n\n";

        if ($isAdmin) {
            $response .= "👥 **Admin Features**\n";
            $response .= "• Manage staff\n";
            $response .= "• Approve/reject leave and overtime\n";
            $response .= "• Generate payroll\n";
            $response .= "• View system statistics\n\n";
        }

        $response .= "For more information, use the navigation menu or ask me specific questions!";

        return [
            'response' => $response,
            'type' => 'info'
        ];
    }

    /**
     * Handle admin pending requests query
     */
    private function handleAdminPendingRequests(User $user): array
    {
        $pendingOvertime = Overtime::where('status', 'pending')->count();
        $pendingPayroll = OTClaim::query()->payroll()->pending()->count();
        $pendingReplacement = OTClaim::query()->replacementLeave()->pending()->count();
        
        // Get pending leaves (need to check leaves table)
        $pendingLeaves = Leave::where('status', 'pending')->count();
        
        $totalPending = $pendingOvertime + $pendingPayroll + $pendingReplacement + $pendingLeaves;
        
        $response = "📋 **Pending Requests Summary:**\n\n";
        $response .= "• **Overtime Requests:** {$pendingOvertime} pending\n";
        $response .= "• **Payroll Claims:** {$pendingPayroll} pending\n";
        $response .= "• **Replacement Leave Claims:** {$pendingReplacement} pending\n";
        $response .= "• **Leave Applications:** {$pendingLeaves} pending\n\n";
        $response .= "**Total:** {$totalPending} pending requests\n\n";
        $response .= "You can review and approve these requests from your dashboard.";

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Handle admin staff management queries
     */
    private function handleAdminStaffQuery(string $message, User $user): array
    {
        $totalStaff = \App\Models\Staff::where('status', 'active')->count();
        $totalDepartments = \App\Models\Staff::distinct('department')->count('department');
        
        $response = "👥 **Staff Management Overview:**\n\n";
        $response .= "• **Total Active Staff:** {$totalStaff}\n";
        $response .= "• **Total Departments:** {$totalDepartments}\n\n";
        $response .= "You can manage staff through:\n";
        $response .= "• Staff Management page - View and edit staff information\n";
        $response .= "• Staff Timetable - Manage staff schedules\n";
        $response .= "• Staff Leave Status - Review all leave applications\n";
        $response .= "• Payroll page - Manage staff payroll and payslips\n\n";
        $response .= "Type 'pending requests' to see what needs your attention.";

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }
}

