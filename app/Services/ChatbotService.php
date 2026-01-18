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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    /**
     * Process user message and return response
     */
    public function processMessage(string $message, User $user): array
    {
        $originalMessage = trim($message);
        $message = strtolower(trim($message));
        
        // Use rule-based system for specific queries, Groq API for general questions
        
        // Check for greetings
        if ($this->matchesKeywords($message, ['hello', 'hi', 'hey', 'greetings', 'good morning', 'good afternoon', 'good evening'])) {
            return $this->getGreetingResponse($user);
        }

        // Check for help
        if ($this->matchesKeywords($message, ['help', 'what can you do', 'commands', 'options'])) {
            return $this->getHelpResponse($user);
        }

        // Check for general system questions
        $generalResponse = $this->detectGeneralQuestion($message);
        if ($generalResponse !== null) {
            return $generalResponse;
        }

        // Check for leave eligibility questions (can I apply leave, etc.)
        // Check for questions asking if they can apply for leave
        if ($this->isLeaveEligibilityQuestion($message)) {
            return $this->checkLeaveEligibility($message, $user);
        }

        // Admin-specific queries (check FIRST before general queries and data retrieval)
        if ($user->isAdmin()) {
            // Check for "how to" questions about timetable/schedule (process questions, not data queries)
            // These should provide instructions, not schedule data
            $isHowToTimetable = $this->isHowToQuestion($message) && $this->matchesKeywords($message, [
                'timetable', 'timetables', 'schedule', 'schedules', 'shift', 'shifts', 'staff schedule', 'staff timetable'
            ]);
            
            // Only return schedule data if it's NOT a "how to" question
            if (!$isHowToTimetable && $this->matchesKeywords($message, [
                'staff schedule', 'staff schedules', 'view staff schedule', 'view staff schedules', 
                'show staff schedule', 'show the staff schedule', 'show staff schedules', 'show the staff schedules',
                'display staff schedule', 'display staff schedules', 'list staff schedule', 'list staff schedules',
                'staff timetable', 'manage staff schedule', 'manage schedule staff', 'manage staff schedules',
                'manage schedules staff', 'staff schedule management', 'manage timetable', 'manage timetables',
                'see staff schedule', 'see staff schedules', 'get staff schedule', 'get staff schedules'
            ])) {
                return $this->handleAdminStaffSchedules($user);
            }
            // Check for approved overtime requests FIRST (must be before pending/approve keywords)
            if ($this->matchesKeywords($message, ['show approved overtime', 'approved overtime requests', 'list approved overtime', 'approved staff', 'staff that already approve', 'show list staff that already approve', 'staff already approved', 'list staff approved'])) {
                return $this->handleAdminApprovedOvertimeRequests($user);
            }
            
            // Check for specific pending request types (must be checked before general pending requests)
            if ($this->matchesKeywords($message, ['check pending overtime requests', 'pending overtime requests', 'pending overtime'])) {
                return $this->handleAdminPendingOvertimeRequests($user);
            }
            
            if ($this->matchesKeywords($message, ['check pending payroll claims', 'pending payroll claims', 'pending payroll'])) {
                return $this->handleAdminPendingPayrollClaims($user);
            }
            
            if ($this->matchesKeywords($message, ['check pending replacement leave claims', 'pending replacement leave claims', 'pending replacement leave', 'pending replacement'])) {
                return $this->handleAdminPendingReplacementClaims($user);
            }
            
            // Check for general pending requests (including "how many pending requests do I have")
            // Note: "approve" and "review" keywords are here, but approved requests are checked above
            if ($this->matchesKeywords($message, ['how many pending requests', 'pending requests', 'pending', 'requests', 'review'])) {
                return $this->handleAdminPendingRequests($user);
            }
            
            // Check for staff leave balances
            if ($this->matchesKeywords($message, ['staff leave balance', 'staff leave balances', 'check staff leave'])) {
                return $this->handleAdminStaffLeaveBalances($user);
            }
            
            // Check for staff payroll information
            if ($this->matchesKeywords($message, ['staff payroll', 'staff payroll information', 'staff payslip', 'staff payslips'])) {
                return $this->handleAdminStaffPayroll($user);
            }
            
            // Check for all staff leave applications (including "show staff leave applications")
            if ($this->matchesKeywords($message, ['show staff leave applications', 'all staff leave', 'staff leave applications', 'view all staff leave', 'staff leave status'])) {
                return $this->handleAdminAllStaffLeaves($user);
            }
            
            // Check for leave status across staff
            if ($this->matchesKeywords($message, ['leave status across', 'check leave status across', 'staff leave status'])) {
                return $this->handleAdminLeaveStatusAcrossStaff($user);
            }
            
            // Check for leave approval information
            if ($this->matchesKeywords($message, ['leave approval', 'leave approval information', 'approve leave'])) {
                return $this->handleAdminLeaveApprovalInfo($user);
            }
            
            // Check for overtime approvals (general status, not pending list)
            if ($this->matchesKeywords($message, ['overtime approval', 'overtime approvals', 'check overtime approval', 'overtime approval status'])) {
                return $this->handleAdminOvertimeApprovals($user);
            }
            
            // Check for overtime claim reviews
            if ($this->matchesKeywords($message, ['overtime claim review', 'overtime claim reviews', 'review overtime claim'])) {
                return $this->handleAdminOvertimeClaimReviews($user);
            }
            
            // Check for payroll statistics
            if ($this->matchesKeywords($message, ['payroll statistics', 'payroll stats', 'payroll overview'])) {
                return $this->handleAdminPayrollStatistics($user);
            }
            
            // Check for generate payroll
            if ($this->matchesKeywords($message, ['generate payroll', 'create payroll', 'make payroll'])) {
                return [
                    'response' => "💰 **Generate Payroll:**\n\nTo generate payroll:\n\n1️⃣ **Navigate to Payroll Management**\n   • Go to the Payroll menu in the sidebar\n   • Click on 'Calculation'\n\n2️⃣ **Select Month and Year**\n   • Choose the payroll period\n\n3️⃣ **Review and Publish**\n   • Review all staff payroll data\n   • Set marketing bonuses (if applicable)\n   • Click 'Publish' to finalize payroll\n\n💡 **Note:**\n• Payroll includes basic salary, commission, bonuses, OT pay, and deductions\n• All payrolls start as 'Draft' status\n• Once published, payrolls are marked as 'Paid'\n\nView and manage payrolls in the Payroll Management page.",
                    'type' => 'info'
                ];
            }
            
            // Check for system statistics
            if ($this->matchesKeywords($message, ['system statistics', 'system stats', 'system overview'])) {
                return $this->handleAdminSystemStatistics($user);
            }
            
            // Check for staff overview
            if ($this->matchesKeywords($message, ['staff overview', 'staff summary'])) {
                return $this->handleAdminStaffOverview($user);
            }
            
            // Check for department information
            if ($this->matchesKeywords($message, ['department information', 'department info', 'departments'])) {
                return $this->handleAdminDepartmentInfo($user);
            }
            
            // Check for staff management queries
            if ($this->matchesKeywords($message, ['staff', 'employee', 'staff management', 'staff information', 'all staff', 'staff list', 'view staff information'])) {
                return $this->handleAdminStaffQuery($message, $user);
            }
        }

        // Leave rules queries (check FIRST before general leave queries to avoid false matches)
        if ($this->matchesKeywords($message, ['leave rules', 'leave policy', 'leave requirements', 'how to apply leave', 'leave approval', 'leave guidelines', 'leave restrictions', 'leave application rules'])) {
            return $this->getLeaveApplicationRules($user);
        }

        // Check for leave types query
        if ($this->matchesKeywords($message, ['leave types', 'leave type', 'all leave types', 'show leave types', 'list leave types'])) {
            return $this->getAllLeaveTypes($user);
        }
        
        // Leave-related queries (including "what's my leave balance")
        if ($this->matchesKeywords($message, ['what\'s my leave balance', 'what is my leave balance', 'leave', 'vacation', 'holiday', 'time off', 'leave balance', 'leave status', 'leave application'])) {
            return $this->handleLeaveQuery($message, $user);
        }
        
        // Check for leave application information
        if ($this->matchesKeywords($message, ['leave application information', 'how to apply leave', 'apply for leave', 'application information'])) {
            return $this->getLeaveApplicationInfo($user);
        }

        // Check for personal shift schedule queries (exclude staff schedules which are handled in admin section)
        if (stripos($message, 'staff schedule') === false && 
            stripos($message, 'staff schedules') === false &&
            stripos($message, 'staff timetable') === false) {
            if ($this->matchesKeywords($message, ['view shift schedule', 'view schedule', 'shift schedule', 'show my shifts this week', 'shifts this week', 'check working hours', 'check shift timings', 'shift timings', 'break times', 'break time', 'timetable information', 'view shift', 'my shift', 'my schedule', 'show shift', 'show schedule', 'shift', 'schedule', 'timetable', 'work hours', 'working hours'])) {
                return $this->handleShiftQuery($message, $user);
            }
        }

        // Check for overtime claim information (check this FIRST before general overtime queries)
        if ($this->matchesKeywords($message, ['overtime claim', 'claim information', 'claim ot', 'claim overtime', 'overtime claim information'])) {
            return $this->getOvertimeClaimInfo($user);
        }
        
        // Overtime-related queries (including "how many overtime hours do I have")
        if ($this->matchesKeywords($message, ['how many overtime hours do i have', 'how many overtime hours', 'overtime', 'ot', 'overtime hours', 'overtime status'])) {
            return $this->handleOvertimeQuery($message, $user);
        }

        // Payroll-related queries (including "show my payslip", "check salary information")
        if ($this->matchesKeywords($message, ['next payslip', 'show my payslip', 'check salary information', 'payroll', 'salary', 'payslip', 'payment', 'wage', 'income'])) {
            return $this->handlePayrollQuery($message, $user);
        }
        
        // Check payment details
        if ($this->matchesKeywords($message, ['payment details', 'payment information', 'when is payment', 'next payment'])) {
            return $this->getPaymentDetails($user);
        }

        // System information
        if ($this->matchesKeywords($message, ['system', 'about', 'information', 'info', 'what is', 'tell me about'])) {
            return $this->handleSystemInfoQuery($message, $user);
        }

        // Check if this is a "how to" question (process question, not data query)
        // Skip data retrieval for "how to" questions - they should go to Groq with flow information
        $isHowToQuestion = $this->isHowToQuestion($message);
        
        // Check if this is a data-specific query that needs database retrieval (for staff personal queries only)
        // Skip if it's an admin staff schedule query (already handled above) OR a "how to" question
        $isAdminStaffScheduleQuery = $user->isAdmin() && $this->matchesKeywords($message, [
            'staff schedule', 'staff schedules', 'view staff schedule', 'view staff schedules', 
            'show staff schedule', 'show the staff schedule', 'show staff schedules', 'show the staff schedules',
            'display staff schedule', 'display staff schedules', 'list staff schedule', 'list staff schedules',
            'staff timetable', 'manage staff schedule', 'manage schedule staff', 'manage staff schedules',
            'manage schedules staff', 'staff schedule management', 'manage timetable', 'manage timetables',
            'see staff schedule', 'see staff schedules', 'get staff schedule', 'get staff schedules'
        ]);
        
        if (!$isAdminStaffScheduleQuery && !$isHowToQuestion) {
            $dataResult = $this->retrieveDataIfNeeded($message, $user);
            
            // If data was retrieved, use Groq to format it naturally, or return formatted response
            if ($dataResult !== null) {
                // Try Groq to format the data response naturally
                $groqResponse = $this->getGroqResponseWithData($originalMessage, $user, $dataResult);
                if ($groqResponse !== null) {
                    return $groqResponse;
                }
                // If Groq fails, return pre-formatted response
                return $dataResult;
            }
        }
        
        // For questions not matched by rule-based system, try Groq API
        // Check if it's a flow/process question (including "how to" questions)
        $isFlowQuestion = $this->isFlowQuestion($message) || $isHowToQuestion;
        
        // Try Groq API for general conversational questions
        $groqResponse = $this->getGroqResponse($originalMessage, $user, $isFlowQuestion);
        if ($groqResponse !== null) {
            return $groqResponse;
        }
        
        // Default response if Groq also fails
        $isAdmin = $user->isAdmin();
        if ($isAdmin) {
            return [
                'response' => "I'm not sure how to help with that. Try asking about:\n• Pending requests (overtime, payroll, replacement leave)\n• Staff management and information\n• Staff leave applications\n• Staff schedules and timetables\n• Payroll and payslip management\n• System statistics\n\nType 'help' to see all available commands.",
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
     * Prioritizes longer/more specific phrases
     */
    private function matchesKeywords(string $message, array $keywords): bool
    {
        // Sort keywords by length (longest first) to match more specific phrases first
        $sortedKeywords = collect($keywords)->sortByDesc(function($keyword) {
            return strlen($keyword);
        })->toArray();
        
        foreach ($sortedKeywords as $keyword) {
            // Use case-insensitive matching (message is already lowercased)
            if (stripos($message, $keyword) !== false) {
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
            $commands .= "• Check pending overtime requests\n";
            $commands .= "• Check pending payroll claims\n";
            $commands .= "• Check pending replacement leave claims\n\n";
            $commands .= "✅ **Approved Requests**\n";
            $commands .= "• Show approved overtime requests\n\n";
            
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
            $commands .= "• View all leave types\n";
            $commands .= "• View leave status\n";
            $commands .= "• Leave application information\n";
            $commands .= "• Leave application rules and guidelines\n\n";
            
            $commands .= "⏰ **Shifts**\n";
            $commands .= "• View shift schedule\n";
            $commands .= "• Check working hours\n";
            $commands .= "• Timetable information\n\n";
            
            $commands .= "⏱️ **Overtime**\n";
            $commands .= "• Check overtime hours\n";
            $commands .= "• View overtime status\n";
            $commands .= "• Overtime rules and guidelines\n";
            $commands .= "• Overtime claim information\n\n";
            
            $commands .= "💰 **Payroll**\n";
            $commands .= "• View payslip\n";
            $commands .= "• Check salary information\n";
            $commands .= "• Payment details\n\n";
            
            $commands .= "💡 **Examples:**\n";
            $commands .= "• \"What's my leave balance?\"\n";
            $commands .= "• \"What are the leave rules?\"\n";
            $commands .= "• \"Show my shifts this week\"\n";
            $commands .= "• \"How many overtime hours do I have?\"\n";
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
            // Check if user specified a leave type
            $leaveTypeName = $this->extractLeaveType($message);
            return $this->getLeaveBalance($staff, $leaveTypeName);
        }

        // Check for rules query FIRST (before status to avoid false matches)
        if ($this->matchesKeywords($message, ['rules', 'policy', 'requirements', 'guidelines', 'restrictions', 'what can be approved', 'how to apply', 'application rules'])) {
            return $this->getLeaveApplicationRules($user);
        }

        // Check leave status (but not if it's about rules)
        if ($this->matchesKeywords($message, ['status', 'pending', 'approved', 'rejected']) && 
            !$this->matchesKeywords($message, ['rules', 'policy', 'requirements', 'guidelines'])) {
            return $this->getLeaveStatus($staff);
        }
        
        // Check for "application" but only if it's about status, not rules
        if ($this->matchesKeywords($message, ['application']) && 
            !$this->matchesKeywords($message, ['rules', 'policy', 'requirements', 'guidelines', 'application rules', 'application information'])) {
            return $this->getLeaveStatus($staff);
        }

        // Default to showing leave balance for general leave queries
        return $this->getLeaveBalance($staff);
    }

    /**
     * Extract leave type from message
     */
    private function extractLeaveType(string $message): ?string
    {
        $leaveTypes = [
            'annual' => ['annual', 'annual leave'],
            'medical' => ['medical', 'medical leave', 'sick', 'sick leave'],
            'hospitalization' => ['hospitalization', 'hospitalization leave', 'hospital'],
            'emergency' => ['emergency', 'emergency leave'],
            'marriage' => ['marriage', 'marriage leave', 'wedding'],
            'replacement' => ['replacement', 'replacement leave'],
            'unpaid' => ['unpaid', 'unpaid leave'],
        ];

        $messageLower = strtolower(trim($message));
        
        // Check for exact matches first (longer phrases first to avoid partial matches)
        foreach ($leaveTypes as $type => $keywords) {
            // Sort keywords by length (longest first) to match "annual leave" before "annual"
            $sortedKeywords = collect($keywords)->sortByDesc(function($keyword) {
                return strlen($keyword);
            })->toArray();
            
            foreach ($sortedKeywords as $keyword) {
                // Use word boundaries to avoid partial matches (e.g., "medical" in "medicalization")
                if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/i', $messageLower)) {
                    return $type;
                }
            }
        }

        return null;
    }

    /**
     * Get leave balance information
     */
    private function getLeaveBalance($staff, ?string $leaveTypeName = null): array
    {
        $query = LeaveBalance::where('staff_id', $staff->id)
            ->with('leaveType');

        // Filter by specific leave type if requested
        if ($leaveTypeName) {
            $query->whereHas('leaveType', function($q) use ($leaveTypeName) {
                $q->whereRaw('LOWER(type_name) = ?', [strtolower($leaveTypeName)]);
            });
        }

        $balances = $query->get();

        if ($balances->isEmpty()) {
            if ($leaveTypeName) {
                return [
                    'response' => "You don't have a leave balance set up for " . ucfirst(str_replace('_', ' ', $leaveTypeName)) . " leave yet. Please contact your administrator.",
                    'type' => 'info'
                ];
            }
            return [
                'response' => "You don't have any leave balances set up yet. Please contact your administrator.",
                'type' => 'info'
            ];
        }

        if ($leaveTypeName) {
            $response = "📅 **Your " . ucfirst(str_replace('_', ' ', $leaveTypeName)) . " Leave Balance:**\n\n";
        } else {
            $response = "📅 **Your Leave Balance:**\n\n";
        }

        foreach ($balances as $balance) {
            $typeName = $balance->leaveType->type_name ?? 'Unknown';
            $response .= "• **" . ucfirst(str_replace('_', ' ', $typeName)) . " Leave:**\n";
            $response .= "  - Available: " . number_format($balance->remaining_days, 1) . " days\n";
            $response .= "  - Taken: " . number_format($balance->used_days, 1) . " days\n";
            $response .= "  - Total: " . number_format($balance->total_days, 1) . " days\n\n";
        }

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Get all leave types with available and taken balances
     */
    private function getAllLeaveTypes(User $user): array
    {
        $staff = $user->staff;
        
        if (!$staff) {
            return [
                'response' => "I couldn't find your staff information. Please contact your administrator.",
                'type' => 'error'
            ];
        }

        // Get ALL leave types in the system
        $allLeaveTypes = \App\Models\LeaveType::orderBy('type_name')->get();
        
        if ($allLeaveTypes->isEmpty()) {
            return [
                'response' => "No leave types are configured in the system yet.",
                'type' => 'info'
            ];
        }

        // Get staff's leave balances as a map for quick lookup
        $staffBalances = LeaveBalance::where('staff_id', $staff->id)
            ->with('leaveType')
            ->get()
            ->keyBy('leave_type_id');

        $response = "📅 **All Leave Types - Your Balance:**\n\n";
        
        foreach ($allLeaveTypes as $leaveType) {
            $typeName = $leaveType->type_name ?? 'Unknown';
            $balance = $staffBalances->get($leaveType->id);
            
            // Calculate taken days from approved leaves (always use actual data)
            $takenDays = Leave::where('staff_id', $staff->id)
                ->where('leave_type_id', $leaveType->id)
                ->where('status', 'approved')
                ->sum('total_days');
            $taken = (float) $takenDays;
            
            // Determine total and available based on leave type
            if (strtolower($typeName) === 'replacement') {
                // Replacement leave is calculated from OT hours (8 hours = 1 day)
                $totalOTHours = \App\Models\Overtime::where('staff_id', $staff->id)
                    ->where('status', 'approved')
                    ->sum('hours');
                $total = (float) floor($totalOTHours / 8);
                $available = max(0, $total - $taken);
            } elseif (strtolower($typeName) === 'unpaid') {
                // Unpaid leave has a fixed max of 10 days
                $total = 10.0;
                $available = max(0, $total - $taken);
            } else {
                // For other leave types, use balance if exists, otherwise use max_days or default
                if ($balance) {
                    $total = (float) $balance->total_days;
                    $available = (float) $balance->remaining_days;
                } else {
                    // Use max_days from leave type or default from Leave model
                    $total = (float) ($leaveType->max_days ?? (\App\Models\Leave::$maxLeaves[strtolower($typeName)] ?? 0));
                    $available = max(0, $total - $taken);
                }
            }
            
            $response .= "• **" . ucfirst(str_replace('_', ' ', $typeName)) . " Leave:**\n";
            $response .= "  ✓ Available: " . number_format($available, 1) . " days\n";
            $response .= "  ✗ Taken: " . number_format($taken, 1) . " days\n";
            $response .= "  📊 Total: " . number_format($total, 1) . " days\n\n";
        }

        $response .= "💡 **Note:** Available = days you can still use, Taken = days you've already used.";

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
            $startDate = $leave->start_date instanceof Carbon ? $leave->start_date : Carbon::parse($leave->start_date);
            $endDate = $leave->end_date instanceof Carbon ? $leave->end_date : Carbon::parse($leave->end_date);
            $response .= "  - Period: {$startDate->format('M d')} to {$endDate->format('M d, Y')}\n";
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

        // Check for specific query types first
        if ($this->matchesKeywords($message, ['check shift timings', 'shift timings', 'break times', 'break time', 'check shift timings and break times'])) {
            return $this->getShiftTimingsAndBreaks($staff);
        }
        
        if ($this->matchesKeywords($message, ['check working hours', 'working hours', 'work hours'])) {
            return $this->getWorkingHours($staff);
        }
        
        if ($this->matchesKeywords($message, ['timetable information', 'timetable info', 'timetable'])) {
            return $this->getTimetableInformation($staff);
        }
        
        // "view shift schedule" or "view schedule" should show this week's shifts
        if ($this->matchesKeywords($message, ['view shift schedule', 'view schedule', 'shift schedule'])) {
            return $this->getWeekShifts($staff, false);
        }

        // Check for specific time period
        $today = $this->matchesKeywords($message, ['today', 'now', 'current']);
        $nextWeek = $this->matchesKeywords($message, ['next week', 'upcoming']);
        $thisWeek = $this->matchesKeywords($message, ['this week', 'current week']);

        if ($today) {
            return $this->getTodayShifts($staff);
        } elseif ($nextWeek) {
            return $this->getWeekShifts($staff, true);
        } elseif ($thisWeek) {
            return $this->getWeekShifts($staff, false);
        }

        // For general shift queries without time specification, show this week's shifts
        return $this->getWeekShifts($staff, false);
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
        $shiftDate = $shift->date instanceof Carbon ? $shift->date : Carbon::parse($shift->date);
        $response .= "• Date: {$shiftDate->format('l, F d, Y')}\n";
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

        $weekLabel = $nextWeek ? 'Next' : 'This';
        $response = "📅 **Your Shifts for {$weekLabel} Week:**\n\n";
        
        foreach ($shifts as $shift) {
            $shiftDate = $shift->date instanceof Carbon ? $shift->date : Carbon::parse($shift->date);
            if ($shift->rest_day) {
                $response .= "• **{$shiftDate->format('l, M d')}**: Rest Day\n";
            } else {
                $breakInfo = $shift->break_minutes ? " (Break: {$shift->break_minutes} min)" : "";
                $response .= "• **{$shiftDate->format('l, M d')}**: {$shift->start_time} - {$shift->end_time}{$breakInfo}\n";
            }
        }
        
        $response .= "\nView your full timetable in the My Timetable page.";

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
            $shiftDate = $shift->date instanceof Carbon ? $shift->date : Carbon::parse($shift->date);
            if ($shift->rest_day) {
                $response .= "• {$shiftDate->format('M d, Y (l)')}: Rest Day\n";
            } else {
                $response .= "• {$shiftDate->format('M d, Y (l)')}: {$shift->start_time} - {$shift->end_time}\n";
            }
        }

        $response .= "\nView your full timetable in the My Timetable page.";

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Get working hours information
     */
    private function getWorkingHours($staff): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        
        // Get shifts for this week
        $shifts = Shift::where('staff_id', $staff->id)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->where('rest_day', false)
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->get();

        $totalHours = 0;
        $totalMinutes = 0;
        
        foreach ($shifts as $shift) {
            $startTime = Carbon::parse($shift->start_time);
            $endTime = Carbon::parse($shift->end_time);
            
            // Handle overnight shifts
            if ($endTime <= $startTime) {
                $endTime->addDay();
            }
            
            $shiftMinutes = $startTime->diffInMinutes($endTime);
            $breakMinutes = $shift->break_minutes ?? 0;
            $workedMinutes = $shiftMinutes - $breakMinutes;
            
            $totalMinutes += $workedMinutes;
        }
        
        $totalHours = floor($totalMinutes / 60);
        $remainingMinutes = $totalMinutes % 60;
        
        $response = "⏰ **Your Working Hours (This Week):**\n\n";
        $response .= "• **Total Hours:** {$totalHours} hours";
        if ($remainingMinutes > 0) {
            $response .= " {$remainingMinutes} minutes";
        }
        $response .= "\n";
        $response .= "• **Shifts Worked:** {$shifts->count()} shift(s)\n";
        $response .= "• **Period:** " . $startOfWeek->format('M d') . " - " . $endOfWeek->format('M d, Y') . "\n\n";
        
        if ($shifts->count() > 0) {
            $response .= "**This Week's Shifts:**\n";
            foreach ($shifts as $shift) {
                $shiftDate = $shift->date instanceof Carbon ? $shift->date : Carbon::parse($shift->date);
                $startTime = Carbon::parse($shift->start_time);
                $endTime = Carbon::parse($shift->end_time);
                if ($endTime <= $startTime) {
                    $endTime->addDay();
                }
                $shiftMinutes = $startTime->diffInMinutes($endTime);
                $breakMinutes = $shift->break_minutes ?? 0;
                $workedMinutes = $shiftMinutes - $breakMinutes;
                $workedHours = floor($workedMinutes / 60);
                $workedMins = $workedMinutes % 60;
                
                $response .= "• {$shiftDate->format('l, M d')}: {$shift->start_time} - {$shift->end_time} ({$workedHours}h";
                if ($workedMins > 0) {
                    $response .= " {$workedMins}m";
                }
                $response .= ")\n";
            }
        }
        
        $response .= "\nView detailed schedule in the My Timetable page.";

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Get timetable information
     */
    private function getTimetableInformation($staff): array
    {
        $response = "📋 **Timetable Information:**\n\n";
        $response .= "Your timetable shows all your scheduled shifts, rest days, and working hours.\n\n";
        
        $response .= "**What You Can Do:**\n";
        $response .= "• View your weekly shift schedule\n";
        $response .= "• See your rest days\n";
        $response .= "• Check shift timings and break times\n";
        $response .= "• View shifts for upcoming weeks\n\n";
        
        $response .= "**How to Access:**\n";
        $response .= "• Go to **My Timetable** in the sidebar\n";
        $response .= "• Select the week you want to view\n";
        $response .= "• Your shifts will be displayed with dates and times\n\n";
        
        // Get next few shifts
        $upcomingShifts = Shift::where('staff_id', $staff->id)
            ->where('date', '>=', Carbon::today())
            ->orderBy('date')
            ->take(3)
            ->get();
        
        if ($upcomingShifts->count() > 0) {
            $response .= "**Your Next Shifts:**\n";
            foreach ($upcomingShifts as $shift) {
                $shiftDate = $shift->date instanceof Carbon ? $shift->date : Carbon::parse($shift->date);
                if ($shift->rest_day) {
                    $response .= "• {$shiftDate->format('M d, Y (l)')}: Rest Day\n";
                } else {
                    $response .= "• {$shiftDate->format('M d, Y (l)')}: {$shift->start_time} - {$shift->end_time}\n";
                }
            }
            $response .= "\n";
        }
        
        $response .= "💡 **Tip:** Ask me 'View shift schedule' to see all your shifts for this week!";

        return [
            'response' => $response,
            'type' => 'info'
        ];
    }

    /**
     * Get shift timings and break times
     */
    private function getShiftTimingsAndBreaks($staff): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        
        // Get shifts for this week
        $shifts = Shift::where('staff_id', $staff->id)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->orderBy('date')
            ->get();

        if ($shifts->isEmpty()) {
            return [
                'response' => "You don't have any shifts scheduled for this week. Check your timetable for your schedule.",
                'type' => 'info'
            ];
        }

        $response = "⏰ **Shift Timings & Break Times (This Week):**\n\n";
        
        foreach ($shifts as $shift) {
            $shiftDate = $shift->date instanceof Carbon ? $shift->date : Carbon::parse($shift->date);
            
            if ($shift->rest_day) {
                $response .= "• **{$shiftDate->format('l, M d')}**: Rest Day\n";
            } else {
                $response .= "• **{$shiftDate->format('l, M d')}**\n";
                $response .= "  - Shift Time: {$shift->start_time} - {$shift->end_time}\n";
                
                if ($shift->break_minutes && $shift->break_minutes > 0) {
                    $breakHours = floor($shift->break_minutes / 60);
                    $breakMins = $shift->break_minutes % 60;
                    $breakDisplay = $breakHours > 0 ? "{$breakHours}h {$breakMins}min" : "{$breakMins}min";
                    $response .= "  - Break Time: {$breakDisplay}\n";
                } else {
                    $response .= "  - Break Time: No break scheduled\n";
                }
                
                // Calculate total working hours
                if ($shift->start_time && $shift->end_time) {
                    $startTime = Carbon::parse($shift->start_time);
                    $endTime = Carbon::parse($shift->end_time);
                    
                    // Handle overnight shifts
                    if ($endTime <= $startTime) {
                        $endTime->addDay();
                    }
                    
                    $totalMinutes = $startTime->diffInMinutes($endTime);
                    $breakMinutes = $shift->break_minutes ?? 0;
                    $workedMinutes = $totalMinutes - $breakMinutes;
                    $workedHours = floor($workedMinutes / 60);
                    $workedMins = $workedMinutes % 60;
                    
                    $response .= "  - Working Hours: {$workedHours}h";
                    if ($workedMins > 0) {
                        $response .= " {$workedMins}min";
                    }
                    $response .= "\n";
                }
            }
            $response .= "\n";
        }
        
        $response .= "View your full timetable in the My Timetable page.";

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

        // Check overtime hours (including "how many overtime hours do I have")
        if ($this->matchesKeywords($message, ['how many overtime hours do i have', 'how many overtime hours', 'hours', 'total', 'how many'])) {
            return $this->getOvertimeHours($staff);
        }

        // Check overtime rules
        if ($this->matchesKeywords($message, ['overtime rules', 'ot rules', 'overtime guidelines', 'ot guidelines', 'overtime policy', 'ot policy', 'what are the overtime rules', 'overtime information'])) {
            return $this->getOvertimeRules($staff);
        }

        // Check overtime status
        if ($this->matchesKeywords($message, ['status', 'pending', 'approved', 'rejected'])) {
            return $this->getOvertimeStatus($staff);
        }

        // Default to showing overtime hours for general overtime queries
        return $this->getOvertimeHours($staff);
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
            $otDate = $ot->ot_date instanceof Carbon ? $ot->ot_date : Carbon::parse($ot->ot_date);
            $response .= "  - Date: {$otDate->format('M d, Y')}\n";
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
     * Get overtime rules and guidelines
     */
    private function getOvertimeRules($staff): array
    {
        $department = $staff->department ?? 'other';
        $isManagerOrSupervisor = in_array($department, ['manager', 'supervisor']);
        
        $response = "⏱️ **Overtime Rules & Guidelines:**\n\n";
        
        // Overtime Rates
        $response .= "💰 **Overtime Rates:**\n";
        $response .= "• Fulltime OT: RM 12.26/hour\n";
        $response .= "• Public Holiday OT: RM 21.68/hour\n";
        $response .= "• Public Holiday Work: RM 15.38/hour\n\n";
        
        // Maximum OT Hours per Day
        $maxHoursPerDay = $isManagerOrSupervisor ? 2 : 4;
        $workdayHours = $isManagerOrSupervisor ? 12 : 7.5;
        $response .= "⏰ **Maximum OT Hours per Day:**\n";
        $response .= "• Your Department ({$department}): {$maxHoursPerDay} hours/day\n";
        $response .= "• Workday: {$workdayHours} hours\n\n";
        
        // Maximum OT Applications per Week
        $response .= "📅 **Maximum OT Applications per Week:**\n";
        $response .= "• Per Person: 2 applications per week\n";
        
        // Department Weekly Limits
        $departmentLimits = [
            'manager' => 1,
            'supervisor' => 1,
            'cashier' => 2,
            'barista' => 2,
            'joki' => 2,
            'waiter' => 3,
            'kitchen' => 3,
        ];
        $deptLimit = $departmentLimits[$department] ?? 0;
        if ($deptLimit > 0) {
            $response .= "• Department Limit ({$department}): {$deptLimit} application(s) per week\n";
        }
        $response .= "\n";
        
        // Approval Process
        $response .= "✅ **Approval Process:**\n";
        $response .= "• All OT applications require admin approval\n";
        $response .= "• Applications are validated against department limits\n";
        $response .= "• Status: Pending → Approved/Rejected\n\n";
        
        // Claim Options
        $response .= "💼 **Claim Options:**\n";
        if ($isManagerOrSupervisor) {
            $response .= "• **Manager/Supervisor:** Can ONLY claim for replacement leave (not payroll)\n";
        } else {
            $response .= "• **Other Staff:** Can choose replacement leave OR payroll\n";
        }
        $response .= "• Conversion Rate: 8 OT hours = 1 replacement leave day\n\n";
        
        // Payroll Calculation (if applicable)
        if (!$isManagerOrSupervisor) {
            $response .= "💰 **Payroll Calculation (when claiming for payroll):**\n";
            $response .= "• Fulltime OT: hours × RM 12.26\n";
            $response .= "• Public Holiday OT: hours × RM 21.68\n";
            $response .= "• Total: Sum of both types\n\n";
        }
        
        // How to Apply
        $response .= "📝 **How to Apply:**\n";
        $response .= "• Go to **Overtime** menu in the sidebar\n";
        $response .= "• Click **Apply Overtime**\n";
        $response .= "• Select OT type, date, and hours\n";
        $response .= "• Submit for admin approval\n\n";
        
        $response .= "💡 **Note:** Make sure you don't exceed the weekly limits before applying!";
        
        return [
            'response' => $response,
            'type' => 'info'
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

        // Check for payslip (including "show my payslip")
        if ($this->matchesKeywords($message, ['show my payslip', 'next payslip', 'payslip', 'salary slip'])) {
            return $this->getPayslipInfo($user);
        }

        // Check salary information (including "check salary information")
        if ($this->matchesKeywords($message, ['check salary information', 'salary', 'wage', 'income'])) {
            return $this->getSalaryInfo($staff);
        }

        // Default to payment details for general payroll queries
        return $this->getPaymentDetails($user);
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
        
        $totalPending = $pendingOvertime + $pendingPayroll + $pendingReplacement;
        
        $response = "📋 **Pending Requests Summary:**\n\n";
        $response .= "• **Overtime Requests:** {$pendingOvertime} pending\n";
        $response .= "• **Payroll Claims:** {$pendingPayroll} pending\n";
        $response .= "• **Replacement Leave Claims:** {$pendingReplacement} pending\n";
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

    /**
     * Get leave application rules
     */
    private function getLeaveApplicationRules(User $user): array
    {
        $response = "📋 **Leave Application Rules & Guidelines:**\n\n";
        
        $response .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        $response .= "1️⃣ **Weekly Leave Entitlement**\n";
        $response .= "   • Maximum 2 leave days per calendar week (excluding rest days)\n";
        $response .= "   • This limit does NOT apply to:\n";
        $response .= "     ✓ Emergency Leave\n";
        $response .= "     ✓ Medical (Sick) Leave\n";
        $response .= "     ✓ Hospitalization Leave\n\n";
        
        $response .= "2️⃣ **Advance Leave Application Requirement**\n";
        $response .= "   • Leave applications must be submitted at least 3 days in advance\n";
        $response .= "   • This requirement is waived for:\n";
        $response .= "     ✓ Emergency Leave\n";
        $response .= "     ✓ Medical (Sick) Leave\n";
        $response .= "     ✓ Hospitalization Leave\n\n";
        
        $response .= "3️⃣ **Department-Based Leave Quota**\n";
        $response .= "   **Per-Day Limit (Maximum Staff on Leave Per Day):**\n";
        $response .= "   • Supervisor: 1 person\n";
        $response .= "   • Cashier: 2 people\n";
        $response .= "   • Barista: 1 person\n";
        $response .= "   • Joki: 1 person\n";
        $response .= "   • Waiter: 3 people\n";
        $response .= "   • Kitchen: 2 people\n\n";
        $response .= "   **Per-Week Limit (Maximum Staff on Leave Per Week):**\n";
        $response .= "   • Supervisor: 2 people\n";
        $response .= "   • Cashier: 4 people\n";
        $response .= "   • Barista: 2 people\n";
        $response .= "   • Joki: 2 people\n";
        $response .= "   • Waiter: 6 people\n";
        $response .= "   • Kitchen: 4 people\n\n";
        $response .= "   ⚠️ Once the daily/weekly quota is reached, ELMSP will automatically reject further leave applications for that department.\n";
        $response .= "   ⚠️ Weekly limits include all approved leave types, except emergency and medical-related leave.\n\n";
        
        $response .= "4️⃣ **Weekend Leave Restriction**\n";
        $response .= "   • Normal leave applications are NOT permitted on Saturdays and Sundays\n";
        $response .= "   • The following leave types are exempted and allowed:\n";
        $response .= "     ✓ Emergency Leave\n";
        $response .= "     ✓ Medical (Sick) Leave (Medical Certificate required)\n";
        $response .= "     ✓ Hospitalization Leave\n\n";
        
        $response .= "5️⃣ **Medical & Hospitalization Leave Requirement**\n";
        $response .= "   • Medical and Hospitalization leave MUST be supported by a valid Medical Certificate (MC)\n";
        $response .= "   • MC must be issued by a registered medical practitioner\n";
        $response .= "   • Upon MC submission, the leave will be automatically approved by ELMSP\n\n";
        
        $response .= "6️⃣ **Overtime Conflict Restriction**\n";
        $response .= "   • Leave applications cannot be approved on the same date(s) as approved overtime\n";
        $response .= "   • If you have approved overtime on a date, you cannot apply for leave on that same date\n";
        $response .= "   • This restriction does NOT apply to:\n";
        $response .= "     ✓ Emergency Leave\n";
        $response .= "     ✓ Medical (Sick) Leave\n";
        $response .= "     ✓ Hospitalization Leave\n\n";
        
        $response .= "7️⃣ **ELMSP Auto-Approval Conditions**\n";
        $response .= "   A leave request will be automatically approved if ALL conditions are met:\n";
        $response .= "   ✓ Staff has sufficient leave balance\n";
        $response .= "   ✓ Request complies with weekly leave limit\n";
        $response .= "   ✓ Request complies with department per-day and per-week quotas\n";
        $response .= "   ✓ Request complies with weekend restriction rules\n";
        $response .= "   ✓ No conflict with approved overtime on the same date(s)\n";
        $response .= "   ✓ Required supporting documents (if any) are submitted\n\n";
        $response .= "   ⚠️ If any rule is violated, the system will automatically reject the request and display a clear reason.\n\n";
        
        $response .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        $response .= "💡 **Tips for Successful Leave Application:**\n";
            $response .= "• Apply at least 3 days in advance (except for emergency/medical)\n";
        $response .= "• Check your leave balance before applying\n";
        $response .= "• For medical/hospitalization leave, always attach your Medical Certificate\n";
        $response .= "• Avoid applying for leave on weekends unless it's emergency/medical\n";
        $response .= "• Check your department's quota availability before applying\n";
        $response .= "• Do not apply for leave on dates where you have approved overtime\n\n";
        
        $response .= "📝 You can apply for leave through the Leave Application page in the sidebar.";

        return [
            'response' => $response,
            'type' => 'info'
        ];
    }

    /**
     * Get leave application information
     */
    private function getLeaveApplicationInfo(User $user): array
    {
        $response = "📝 **Leave Application Information:**\n\n";
        $response .= "To apply for leave:\n\n";
        $response .= "1️⃣ **Navigate to Leave Application**\n";
        $response .= "   • Go to the Leave menu in the sidebar\n";
        $response .= "   • Click on 'Leave Application'\n\n";
        $response .= "2️⃣ **Fill in the Application Form**\n";
        $response .= "   • Select your leave type\n";
        $response .= "   • Choose start and end dates\n";
        $response .= "   • Provide a reason\n";
        $response .= "   • Attach supporting documents (if required)\n\n";
        $response .= "3️⃣ **Submit Your Application**\n";
        $response .= "   • Review your details\n";
        $response .= "   • Click 'Submit Application'\n";
        $response .= "   • The system will auto-approve if all rules are met\n\n";
        $response .= "💡 **Important:**\n";
        $response .= "• Medical and Hospitalization leave require a Medical Certificate\n";
            $response .= "• Apply at least 3 days in advance (except emergency/medical)\n";
        $response .= "• Check your leave balance before applying\n\n";
        $response .= "Ask me 'What are the leave rules?' for detailed guidelines.";

        return [
            'response' => $response,
            'type' => 'info'
        ];
    }

    /**
     * Get overtime claim information
     */
    private function getOvertimeClaimInfo(User $user): array
    {
        $staff = $user->staff;
        
        if (!$staff) {
            return [
                'response' => "I couldn't find your staff information. Please contact your administrator.",
                'type' => 'error'
            ];
        }

        $response = "⏱️ **Overtime Claim Information:**\n\n";
        $response .= "You can claim your approved overtime hours in two ways:\n\n";
        $response .= "1️⃣ **Replacement Leave**\n";
        $response .= "   • 8 hours of overtime = 1 day of replacement leave\n";
        $response .= "   • Can be used for leave applications\n";
        $response .= "   • Requires admin approval\n\n";
        $response .= "2️⃣ **Payroll**\n";
        $response .= "   • Fulltime OT: RM 12.26 per hour\n";
        $response .= "   • Public Holiday OT: RM 21.68 per hour\n";
        $response .= "   • Added to your monthly payroll\n";
        $response .= "   • Requires admin approval\n\n";
        $response .= "📋 **How to Claim:**\n";
        $response .= "• Go to 'Claim Overtime' in the Overtime menu\n";
        $response .= "• Select the month with approved overtime\n";
        $response .= "• Choose 'Replacement Leave' or 'Payroll'\n";
        $response .= "• Submit your claim\n\n";
        $response .= "💡 **Note:**\n";
        $response .= "• Only past dates can be claimed (future dates are excluded)\n";
        $response .= "• Overtime on leave dates is automatically excluded\n";
        $response .= "• Check your overtime status to see available hours";

        return [
            'response' => $response,
            'type' => 'info'
        ];
    }

    /**
     * Get payment details
     */
    private function getPaymentDetails(User $user): array
    {
        $staff = $user->staff;
        
        if (!$staff) {
            return [
                'response' => "I couldn't find your staff information. Please contact your administrator.",
                'type' => 'error'
            ];
        }

        $latestPayroll = Payroll::where('user_id', $user->id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->first();

        $response = "💰 **Payment Details:**\n\n";
        
        if ($latestPayroll) {
            $monthName = Carbon::create()->month($latestPayroll->month)->format('F');
            $response .= "**Latest Payslip:**\n";
            $response .= "• Period: {$monthName} {$latestPayroll->year}\n";
            $response .= "• Net Salary: RM " . number_format($latestPayroll->net_salary, 2) . "\n";
            $response .= "• Status: " . ucfirst($latestPayroll->status) . "\n\n";
        }

        $response .= "**Basic Information:**\n";
        $response .= "• Basic Salary: RM " . number_format($staff->salary, 2) . "\n";
        $response .= "• Department: " . ucfirst($staff->department) . "\n\n";
        $response .= "**Payroll Components:**\n";
        $response .= "• Basic Salary\n";
        $response .= "• Fixed Commission (after 3 months)\n";
        $response .= "• Marketing Bonus\n";
        $response .= "• Public Holiday Pay\n";
        $response .= "• Overtime Pay (if claimed)\n";
        $response .= "• Deductions (if any)\n\n";
        $response .= "View detailed payslips in the Payslip page.";

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Handle admin staff leave balances query
     */
    private function handleAdminStaffLeaveBalances(User $user): array
    {
        $staffList = \App\Models\Staff::with('user')
            ->where('status', 'active')
            ->get();

        if ($staffList->isEmpty()) {
            return [
                'response' => "No active staff found.",
                'type' => 'info'
            ];
        }

        $response = "📅 **Staff Leave Balances:**\n\n";
        $count = 0;
        foreach ($staffList->take(10) as $staff) {
            $balances = LeaveBalance::where('staff_id', $staff->id)
                ->with('leaveType')
                ->get();

            if ($balances->count() > 0) {
                $staffName = $staff->user->name ?? "Staff #{$staff->id}";
                $response .= "**{$staffName}** ({$staff->department}):\n";
                foreach ($balances as $balance) {
                    $typeName = $balance->leaveType->type_name ?? 'Unknown';
                    $response .= "  • " . ucfirst(str_replace('_', ' ', $typeName)) . ": ";
                    $response .= number_format($balance->remaining_days, 1) . " days remaining\n";
                }
                $response .= "\n";
                $count++;
            }
        }

        if ($count === 0) {
            $response = "No leave balances found for staff members.";
        } else {
            $response .= "View detailed leave balances in the Staff Management page.";
        }

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Handle admin staff schedules query
     */
    private function handleAdminStaffSchedules(User $user): array
    {
        $thisWeek = Carbon::now()->startOfWeek();
        $endWeek = $thisWeek->copy()->endOfWeek();

        $shifts = Shift::whereBetween('date', [$thisWeek, $endWeek])
            ->with('staff.user')
            ->get()
            ->groupBy('staff_id');

        $response = "📅 **Staff Schedules (This Week):**\n\n";
        
        if ($shifts->isEmpty()) {
            $response = "No shifts scheduled for this week.";
        } else {
            foreach ($shifts->take(5) as $staffId => $staffShifts) {
                $staff = $staffShifts->first()->staff;
                $staffName = $staff && $staff->user ? $staff->user->name : "Staff #{$staffId}";
                $dept = $staff->department ?? 'N/A';
                $response .= "**{$staffName}** ({$dept}):\n";
                
                foreach ($staffShifts->sortBy('date') as $shift) {
                    $shiftDate = $shift->date instanceof Carbon ? $shift->date : Carbon::parse($shift->date);
                    if ($shift->rest_day) {
                        $response .= "  • {$shiftDate->format('M d (l)')}: Rest Day\n";
                    } else {
                        $response .= "  • {$shiftDate->format('M d (l)')}: {$shift->start_time} - {$shift->end_time}\n";
                    }
                }
                $response .= "\n";
            }
            $response .= "View full schedules in the Staff Timetable page.";
        }

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Handle admin staff payroll query
     */
    private function handleAdminStaffPayroll(User $user): array
    {
        $totalStaff = \App\Models\Staff::where('status', 'active')->count();
        $recentPayrolls = Payroll::orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(5)
            ->with('user')
            ->get();

        $response = "💰 **Staff Payroll Information:**\n\n";
        $response .= "• **Total Active Staff:** {$totalStaff}\n\n";
        
        if ($recentPayrolls->count() > 0) {
            $response .= "**Recent Payrolls:**\n";
            foreach ($recentPayrolls as $payroll) {
                $monthName = Carbon::create()->month($payroll->month)->format('F');
                $staffName = $payroll->user->name ?? "User #{$payroll->user_id}";
                $response .= "• {$staffName} - {$monthName} {$payroll->year}: ";
                $response .= "RM " . number_format($payroll->net_salary, 2) . " ({$payroll->status})\n";
            }
            $response .= "\n";
        }

        $response .= "Manage payroll in the Payroll Management page.";

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Handle admin all staff leaves query
     */
    private function handleAdminAllStaffLeaves(User $user): array
    {
        $recentLeaves = Leave::with('staff.user', 'leaveType')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        if ($recentLeaves->isEmpty()) {
            return [
                'response' => "No leave applications found.",
                'type' => 'info'
            ];
        }

        $response = "📋 **Recent Staff Leave Applications:**\n\n";
        foreach ($recentLeaves as $leave) {
            $staffName = $leave->staff && $leave->staff->user ? $leave->staff->user->name : "Staff #{$leave->staff_id}";
            $typeName = $leave->leaveType->type_name ?? 'Unknown';
            $status = ucfirst($leave->status);
            $statusEmoji = $leave->status === 'approved' ? '✅' : ($leave->status === 'pending' ? '⏳' : '❌');
            
            $response .= "{$statusEmoji} **{$staffName}**\n";
            $response .= "  • Type: " . ucfirst(str_replace('_', ' ', $typeName)) . "\n";
            $startDate = $leave->start_date instanceof Carbon ? $leave->start_date : Carbon::parse($leave->start_date);
            $endDate = $leave->end_date instanceof Carbon ? $leave->end_date : Carbon::parse($leave->end_date);
            $response .= "  • Period: {$startDate->format('M d')} to {$endDate->format('M d, Y')}\n";
            $response .= "  • Days: {$leave->total_days}\n";
            $response .= "  • Status: {$status}\n\n";
        }

        $response .= "View all leave applications in the Staff Leave Status page.";

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Handle admin leave status across staff
     */
    private function handleAdminLeaveStatusAcrossStaff(User $user): array
    {
        $pendingCount = Leave::where('status', 'pending')->count();
        $approvedCount = Leave::where('status', 'approved')->count();
        $rejectedCount = Leave::where('status', 'rejected')->count();
        $totalCount = Leave::count();

        $response = "📊 **Leave Status Across All Staff:**\n\n";
        $response .= "• **Total Applications:** {$totalCount}\n";
        $response .= "• **Pending:** {$pendingCount}\n";
        $response .= "• **Approved:** {$approvedCount}\n";
        $response .= "• **Rejected:** {$rejectedCount}\n\n";

        // Get leaves by department
        $leavesByDept = Leave::where('status', 'approved')
            ->with('staff')
            ->get()
            ->groupBy(function($leave) {
                return $leave->staff->department ?? 'Unknown';
            });

        if ($leavesByDept->count() > 0) {
            $response .= "**Approved Leaves by Department:**\n";
            foreach ($leavesByDept as $dept => $leaves) {
                $response .= "• " . ucfirst($dept) . ": {$leaves->count()} approved leave(s)\n";
            }
        }

        $response .= "\nView detailed leave status in the Staff Leave Status page.";

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Handle admin leave approval information
     */
    private function handleAdminLeaveApprovalInfo(User $user): array
    {
        $pendingLeaves = Leave::where('status', 'pending')->count();
        $autoApproved = Leave::where('auto_approved', true)->count();
        $manualApproved = Leave::where('status', 'approved')->where('auto_approved', false)->count();

        $response = "✅ **Leave Approval Information:**\n\n";
        $response .= "**Current Status:**\n";
        $response .= "• Pending Approvals: {$pendingLeaves}\n";
        $response .= "• Auto-Approved: {$autoApproved}\n";
        $response .= "• Manually Approved: {$manualApproved}\n\n";
        
        $response .= "**Auto-Approval System:**\n";
        $response .= "Leaves are automatically approved if:\n";
        $response .= "✓ Staff has sufficient leave balance\n";
        $response .= "✓ Request complies with weekly leave limit\n";
        $response .= "✓ Request complies with department quotas\n";
        $response .= "✓ Request complies with weekend restrictions\n";
        $response .= "✓ No conflict with approved overtime\n";
        $response .= "✓ Required documents are submitted\n\n";
        
        $response .= "**Manual Approval Required:**\n";
        $response .= "• Replacement Leave (always requires approval)\n";
        $response .= "• Any leave that violates rules\n\n";
        
        $response .= "Review pending leaves in the Staff Leave Status page.";

        return [
            'response' => $response,
            'type' => 'info'
        ];
    }

    /**
     * Handle admin overtime approvals query
     */
    private function handleAdminOvertimeApprovals(User $user): array
    {
        $pendingOT = Overtime::where('status', 'pending')->count();
        $approvedOT = Overtime::where('status', 'approved')->count();
        $rejectedOT = Overtime::where('status', 'rejected')->count();

        $response = "⏱️ **Overtime Approval Status:**\n\n";
        $response .= "• **Pending:** {$pendingOT} request(s)\n";
        $response .= "• **Approved:** {$approvedOT} request(s)\n";
        $response .= "• **Rejected:** {$rejectedOT} request(s)\n\n";

        if ($pendingOT > 0) {
            $recentPending = Overtime::where('status', 'pending')
                ->with('staff.user')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $response .= "**Recent Pending Requests:**\n";
            foreach ($recentPending as $ot) {
                $staffName = $ot->staff && $ot->staff->user ? $ot->staff->user->name : "Staff #{$ot->staff_id}";
                $otDate = $ot->ot_date instanceof Carbon ? $ot->ot_date : Carbon::parse($ot->ot_date);
                $response .= "• {$staffName} - {$otDate->format('M d, Y')}: {$ot->hours} hours\n";
            }
            $response .= "\n";
        }

        $response .= "Review and approve overtime requests in the Dashboard page.";

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Handle admin overtime claim reviews query
     */
    private function handleAdminOvertimeClaimReviews(User $user): array
    {
        $pendingPayroll = OTClaim::where('claim_type', 'payroll')
            ->where('status', 'pending')
            ->count();
        $pendingReplacement = OTClaim::where('claim_type', 'replacement_leave')
            ->where('status', 'pending')
            ->count();

        $response = "📋 **Overtime Claim Reviews:**\n\n";
        $response .= "**Pending Claims:**\n";
        $response .= "• Payroll Claims: {$pendingPayroll}\n";
        $response .= "• Replacement Leave Claims: {$pendingReplacement}\n";
        $response .= "• Total: " . ($pendingPayroll + $pendingReplacement) . "\n\n";

        if ($pendingPayroll > 0 || $pendingReplacement > 0) {
            $recentClaims = OTClaim::where('status', 'pending')
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $response .= "**Recent Pending Claims:**\n";
            foreach ($recentClaims as $claim) {
                $userName = $claim->user->name ?? "User #{$claim->user_id}";
                $claimType = $claim->claim_type === 'payroll' ? 'Payroll' : 'Replacement Leave';
                $response .= "• {$userName} - {$claimType}\n";
                if ($claim->claim_type === 'payroll') {
                    $response .= "  Amount: RM " . number_format(
                        ($claim->fulltime_hours * 12.26) + ($claim->public_holiday_hours * 21.68),
                        2
                    ) . "\n";
                } else {
                    $response .= "  Days: {$claim->replacement_days}\n";
                }
            }
        }

        $response .= "\nReview claims in the Dashboard page.";

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Handle admin pending overtime requests - shows actual pending requests
     */
    private function handleAdminPendingOvertimeRequests(User $user): array
    {
        $pendingOT = Overtime::where('status', 'pending')
            ->with('staff.user')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($pendingOT->isEmpty()) {
            return [
                'response' => "✅ **No Pending Overtime Requests**\n\nThere are currently no pending overtime requests to review.",
                'type' => 'success'
            ];
        }

        $response = "⏱️ **Pending Overtime Requests** ({$pendingOT->count()}):\n\n";
        
        foreach ($pendingOT as $ot) {
            $staffName = $ot->staff && $ot->staff->user ? $ot->staff->user->name : "Staff #{$ot->staff_id}";
            $otDate = $ot->ot_date instanceof Carbon ? $ot->ot_date : Carbon::parse($ot->ot_date);
            $createdAt = $ot->created_at instanceof Carbon ? $ot->created_at : Carbon::parse($ot->created_at);
            $response .= "• **{$staffName}**\n";
            $response .= "  Date: {$otDate->format('M d, Y')}\n";
            $response .= "  Hours: {$ot->hours} hours\n";
            $response .= "  Submitted: {$createdAt->diffForHumans()}\n\n";
        }

        $response .= "Review and approve these requests in the Dashboard page.";

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Handle admin pending payroll claims - shows actual pending claims
     */
    private function handleAdminPendingPayrollClaims(User $user): array
    {
        $pendingPayroll = OTClaim::query()
            ->payroll()
            ->pending()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($pendingPayroll->isEmpty()) {
            return [
                'response' => "✅ **No Pending Payroll Claims**\n\nThere are currently no pending payroll claims to review.",
                'type' => 'success'
            ];
        }

        $response = "💰 **Pending Payroll Claims** ({$pendingPayroll->count()}):\n\n";
        
        foreach ($pendingPayroll as $claim) {
            $userName = $claim->user->name ?? "User #{$claim->user_id}";
            $createdAt = $claim->created_at instanceof Carbon ? $claim->created_at : Carbon::parse($claim->created_at);
            $amount = ($claim->fulltime_hours * 12.26) + ($claim->public_holiday_hours * 21.68);
            
            $response .= "• **{$userName}**\n";
            $response .= "  Fulltime Hours: {$claim->fulltime_hours} hours\n";
            $response .= "  Public Holiday Hours: {$claim->public_holiday_hours} hours\n";
            $response .= "  Amount: RM " . number_format($amount, 2) . "\n";
            $response .= "  Submitted: {$createdAt->diffForHumans()}\n\n";
        }

        $response .= "Review and approve these claims in the Dashboard page.";

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Handle admin pending replacement leave claims - shows actual pending claims
     */
    private function handleAdminPendingReplacementClaims(User $user): array
    {
        $pendingReplacement = OTClaim::query()
            ->replacementLeave()
            ->pending()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($pendingReplacement->isEmpty()) {
            return [
                'response' => "✅ **No Pending Replacement Leave Claims**\n\nThere are currently no pending replacement leave claims to review.",
                'type' => 'success'
            ];
        }

        $response = "🔄 **Pending Replacement Leave Claims** ({$pendingReplacement->count()}):\n\n";
        
        foreach ($pendingReplacement as $claim) {
            // Try to get user from overtime records
            $claimUser = null;
            if ($claim->ot_ids && is_array($claim->ot_ids) && !empty($claim->ot_ids)) {
                $firstOtId = $claim->ot_ids[0];
                $overtime = Overtime::with('staff.user')->find($firstOtId);
                if ($overtime && $overtime->staff && $overtime->staff->user) {
                    $claimUser = $overtime->staff->user;
                }
            }
            // Fallback to direct user relationship
            if (!$claimUser && $claim->user) {
                $claimUser = $claim->user;
            }
            
            $userName = $claimUser ? $claimUser->name : "User #{$claim->user_id}";
            $createdAt = $claim->created_at instanceof Carbon ? $claim->created_at : Carbon::parse($claim->created_at);
            
            $response .= "• **{$userName}**\n";
            $response .= "  Replacement Days: {$claim->replacement_days} day" . ($claim->replacement_days > 1 ? 's' : '') . "\n";
            $response .= "  Submitted: {$createdAt->diffForHumans()}\n\n";
        }

        $response .= "Review and approve these claims in the Dashboard page.";

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Handle admin approved overtime requests - shows list of staff with approved overtime
     */
    private function handleAdminApprovedOvertimeRequests(User $user): array
    {
        $approvedOT = Overtime::where('status', 'approved')
            ->with('staff.user')
            ->orderBy('updated_at', 'desc')
            ->get();

        if ($approvedOT->isEmpty()) {
            return [
                'response' => "✅ **No Approved Overtime Requests**\n\nThere are currently no approved overtime requests.",
                'type' => 'success'
            ];
        }

        // Group by staff to show unique staff list
        $staffList = [];
        foreach ($approvedOT as $ot) {
            $staffId = $ot->staff_id;
            $staffName = $ot->staff && $ot->staff->user ? $ot->staff->user->name : "Staff #{$staffId}";
            
            if (!isset($staffList[$staffId])) {
                $staffList[$staffId] = [
                    'name' => $staffName,
                    'total_hours' => 0,
                    'total_requests' => 0,
                    'latest_approval' => null,
                    'requests' => []
                ];
            }
            
            $otDate = $ot->ot_date instanceof Carbon ? $ot->ot_date : Carbon::parse($ot->ot_date);
            $approvedAt = $ot->updated_at instanceof Carbon ? $ot->updated_at : Carbon::parse($ot->updated_at);
            
            $staffList[$staffId]['total_hours'] += $ot->hours;
            $staffList[$staffId]['total_requests']++;
            $staffList[$staffId]['requests'][] = [
                'date' => $otDate,
                'hours' => $ot->hours,
                'approved_at' => $approvedAt
            ];
            
            // Track latest approval
            if (!$staffList[$staffId]['latest_approval'] || $approvedAt->gt($staffList[$staffId]['latest_approval'])) {
                $staffList[$staffId]['latest_approval'] = $approvedAt;
            }
        }

        // Sort by latest approval date (most recent first)
        uasort($staffList, function($a, $b) {
            return $b['latest_approval'] <=> $a['latest_approval'];
        });

        $response = "✅ **Approved Overtime Requests** (" . count($staffList) . " staff):\n\n";
        
        foreach ($staffList as $staff) {
            $response .= "• **{$staff['name']}**\n";
            $response .= "  Total Requests: {$staff['total_requests']}\n";
            $response .= "  Total Hours: {$staff['total_hours']} hours\n";
            $response .= "  Latest Approval: {$staff['latest_approval']->diffForHumans()}\n";
            
            // Show recent requests (last 3)
            $recentRequests = array_slice($staff['requests'], 0, 3);
            if (count($recentRequests) > 0) {
                $response .= "  Recent Approvals:\n";
                foreach ($recentRequests as $req) {
                    $response .= "    - {$req['date']->format('M d, Y')}: {$req['hours']} hours\n";
                }
            }
            $response .= "\n";
        }

        $response .= "View all approved requests in the Dashboard page.";

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Handle admin payroll statistics query
     */
    private function handleAdminPayrollStatistics(User $user): array
    {
        $totalPayrolls = Payroll::count();
        $paidPayrolls = Payroll::where('status', 'paid')->count();
        $draftPayrolls = Payroll::where('status', 'draft')->count();
        
        $totalGross = Payroll::sum('gross_salary');
        $totalNet = Payroll::sum('net_salary');

        $response = "💰 **Payroll Statistics:**\n\n";
        $response .= "**Overview:**\n";
        $response .= "• Total Payrolls: {$totalPayrolls}\n";
        $response .= "• Paid: {$paidPayrolls}\n";
        $response .= "• Draft: {$draftPayrolls}\n\n";

        if ($totalPayrolls > 0) {
            $response .= "**Financial Summary:**\n";
            $response .= "• Total Gross Salary: RM " . number_format($totalGross, 2) . "\n";
            $response .= "• Total Net Salary: RM " . number_format($totalNet, 2) . "\n";
            $response .= "• Average Gross: RM " . number_format($totalGross / $totalPayrolls, 2) . "\n";
            $response .= "• Average Net: RM " . number_format($totalNet / $totalPayrolls, 2) . "\n\n";
        }

        // Recent payrolls by month
        $recentMonths = Payroll::selectRaw('year, month, COUNT(*) as count, SUM(gross_salary) as total_gross, SUM(net_salary) as total_net')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(3)
            ->get();

        if ($recentMonths->count() > 0) {
            $response .= "**Recent Months:**\n";
            foreach ($recentMonths as $month) {
                $monthName = Carbon::create()->month($month->month)->format('F');
                $response .= "• {$monthName} {$month->year}: ";
                $response .= "{$month->count} payroll(s), ";
                $response .= "RM " . number_format($month->total_net, 2) . " total\n";
            }
        }

        $response .= "\nManage payroll in the Payroll Management page.";

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Handle admin system statistics query
     */
    private function handleAdminSystemStatistics(User $user): array
    {
        $totalStaff = \App\Models\Staff::where('status', 'active')->count();
        $totalLeaves = Leave::count();
        $totalOvertimes = Overtime::count();
        $totalPayrolls = Payroll::count();
        $totalOTClaims = OTClaim::count();

        $response = "📊 **System Statistics:**\n\n";
        $response .= "**Overview:**\n";
        $response .= "• Active Staff: {$totalStaff}\n";
        $response .= "• Total Leave Applications: {$totalLeaves}\n";
        $response .= "• Total Overtime Applications: {$totalOvertimes}\n";
        $response .= "• Total Payrolls: {$totalPayrolls}\n";
        $response .= "• Total OT Claims: {$totalOTClaims}\n\n";

        // Department breakdown
        $staffByDept = \App\Models\Staff::where('status', 'active')
            ->selectRaw('department, COUNT(*) as count')
            ->groupBy('department')
            ->get();

        if ($staffByDept->count() > 0) {
            $response .= "**Staff by Department:**\n";
            foreach ($staffByDept as $dept) {
                $response .= "• " . ucfirst($dept->department) . ": {$dept->count}\n";
            }
        }

        // Leave status breakdown
        $leavesByStatus = Leave::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        if ($leavesByStatus->count() > 0) {
            $response .= "\n**Leave Applications by Status:**\n";
            foreach ($leavesByStatus as $status) {
                $response .= "• " . ucfirst($status->status) . ": {$status->count}\n";
            }
        }

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Handle admin staff overview query
     */
    private function handleAdminStaffOverview(User $user): array
    {
        $totalStaff = \App\Models\Staff::where('status', 'active')->count();
        $totalDepartments = \App\Models\Staff::distinct('department')->count('department');
        
        $staffByDept = \App\Models\Staff::where('status', 'active')
            ->selectRaw('department, COUNT(*) as count')
            ->groupBy('department')
            ->orderBy('count', 'desc')
            ->get();

        $response = "👥 **Staff Overview:**\n\n";
        $response .= "• **Total Active Staff:** {$totalStaff}\n";
        $response .= "• **Total Departments:** {$totalDepartments}\n\n";

        if ($staffByDept->count() > 0) {
            $response .= "**Staff Distribution:**\n";
            foreach ($staffByDept as $dept) {
                $percentage = $totalStaff > 0 ? round(($dept->count / $totalStaff) * 100, 1) : 0;
                $response .= "• " . ucfirst($dept->department) . ": {$dept->count} staff ({$percentage}%)\n";
            }
        }

        // Recent hires
        $recentHires = \App\Models\Staff::where('status', 'active')
            ->with('user')
            ->orderBy('hire_date', 'desc')
            ->take(5)
            ->get();

        if ($recentHires->count() > 0) {
            $response .= "\n**Recent Hires:**\n";
            foreach ($recentHires as $staff) {
                $staffName = $staff->user->name ?? "Staff #{$staff->id}";
                $hireDate = $staff->hire_date instanceof Carbon ? $staff->hire_date : Carbon::parse($staff->hire_date);
                $response .= "• {$staffName} - Hired: {$hireDate->format('M d, Y')}\n";
            }
        }

        $response .= "\nManage staff in the Staff Management page.";

        return [
            'response' => $response,
            'type' => 'success'
        ];
    }

    /**
     * Handle admin department information query
     */
    private function handleAdminDepartmentInfo(User $user): array
    {
        $departments = ['manager', 'supervisor', 'cashier', 'barista', 'joki', 'waiter', 'kitchen'];
        
        $response = "🏢 **Department Information:**\n\n";
        
        foreach ($departments as $dept) {
            $staffCount = \App\Models\Staff::where('department', $dept)
                ->where('status', 'active')
                ->count();
            
            $limit = \App\Models\Staff::$departmentLimits[$dept] ?? 'N/A';
            
            $response .= "**" . ucfirst($dept) . ":**\n";
            $response .= "  • Active Staff: {$staffCount}\n";
            $response .= "  • Department Limit: {$limit}\n";
            
            // Add OT limits
            $otWeeklyLimit = \App\Models\Overtime::$departmentWeeklyConstraints[$dept] ?? 'N/A';
            if ($otWeeklyLimit !== 'N/A') {
                $response .= "  • OT Weekly Limit: {$otWeeklyLimit} application(s)\n";
            }
            
            // Add leave limits
            $leaveDailyLimit = \App\Models\Leave::$departmentConstraints[$dept] ?? 'N/A';
            $leaveWeeklyLimit = \App\Models\Leave::$departmentWeeklyConstraints[$dept] ?? 'N/A';
            if ($leaveDailyLimit !== 'N/A') {
                $response .= "  • Leave Daily Limit: {$leaveDailyLimit} person(s)\n";
            }
            if ($leaveWeeklyLimit !== 'N/A') {
                $response .= "  • Leave Weekly Limit: {$leaveWeeklyLimit} person(s)\n";
            }
            $response .= "\n";
        }

        return [
            'response' => $response,
            'type' => 'info'
        ];
    }

    /**
     * Check if message is a data-specific query that needs rule-based system
     */
    private function isDataSpecificQuery(string $message): bool
    {
        $dataKeywords = [
            'leave balance', 'leave status', 'leave application', 'leave types',
            'shift schedule', 'shifts', 'timetable', 'working hours', 'break time',
            'overtime hours', 'overtime status', 'overtime claim',
            'payslip', 'salary', 'payroll', 'payment',
            'pending requests', 'pending overtime', 'pending payroll', 'pending replacement',
            'staff', 'employee', 'department',
            'statistics', 'stats', 'overview'
        ];
        
        foreach ($dataKeywords as $keyword) {
            if (stripos($message, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if message is asking about system flow/process
     */
    private function isFlowQuestion(string $message): bool
    {
        $flowKeywords = [
            'how to', 'how do i', 'how can i', 'what is the process', 'what is the flow',
            'steps to', 'procedure', 'guide', 'tutorial', 'instructions',
            'how does', 'how should', 'way to', 'method to', 'how do',
            'apply for', 'apply', 'claim', 'submit', 'request', 'process'
        ];
        
        // Also check for action words combined with system features
        $actionWords = ['apply', 'claim', 'submit', 'request', 'access', 'view', 'check', 'get'];
        $featureWords = ['leave', 'overtime', 'ot', 'payslip', 'payroll', 'shift', 'schedule'];
        
        foreach ($flowKeywords as $keyword) {
            if (stripos($message, $keyword) !== false) {
                return true;
            }
        }
        
        // Check for action + feature combinations (e.g., "apply leave", "claim overtime")
        foreach ($actionWords as $action) {
            foreach ($featureWords as $feature) {
                if (stripos($message, $action) !== false && stripos($message, $feature) !== false) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Retrieve data from database if the query needs it
     * 
     * References the 'shifts' table with columns:
     * - id (primary key)
     * - staff_id (foreign key to staff table)
     * - date (YYYY-MM-DD format)
     * - day_of_week (abbreviated day name: wed, thu, fri, sat, sun, etc.)
     * - start_time (HH:MM format, e.g., "10:00", "07:00")
     * - end_time (HH:MM format, e.g., "23:00")
     * - break_minutes (integer, nullable)
     * - rest_day (boolean, indicates if it's a rest day)
     */
    private function retrieveDataIfNeeded(string $message, User $user): ?array
    {
        try {
            $message = strtolower(trim($message));
        
        // Check for shift queries (today, this week, etc.)
        // Queries the 'shifts' table using staff_id and date columns
        // Skip if it's a "how to" question (process question, not data query)
        if ($this->isHowToQuestion($message)) {
            return null; // Skip data retrieval for "how to" questions
        }
        
        if ($this->matchesKeywords($message, ['shift today', 'my shift today', 'today shift', 'shift schedule today', 'what is my shift today', 'my shift', 'shift schedule', 'view shift schedule', 'view schedule', 'show shift', 'show schedule', 'timetable', 'schedule', 'list timetable', 'list my timetable', 'list schedule'])) {
            $staff = $user->staff;
            if (!$staff) {
                return [
                    'response' => "I couldn't find your staff information. Please contact your administrator.",
                    'type' => 'error'
                ];
            }
            
            // Check if asking for today specifically
            $isToday = $this->matchesKeywords($message, ['today', 'now', 'current']);
            $isLastWeek = $this->matchesKeywords($message, ['last week', 'previous week', 'past week']);
            $isNextWeek = $this->matchesKeywords($message, ['next week', 'upcoming week']);
            $isThisWeek = $this->matchesKeywords($message, ['this week', 'current week']);
            
            if ($isToday) {
                $today = Carbon::today();
                $shift = Shift::where('staff_id', $staff->id)
                    ->whereDate('date', $today)
                    ->first();
                
                if (!$shift) {
                    return [
                        'response' => "You don't have a shift scheduled for today.",
                        'type' => 'info',
                        'data' => ['has_shift' => false, 'date' => $today->format('Y-m-d')]
                    ];
                }
                
                if ($shift->rest_day) {
                    return [
                        'response' => "Today is your rest day.",
                        'type' => 'info',
                        'data' => ['has_shift' => false, 'rest_day' => true, 'date' => $today->format('Y-m-d')]
                    ];
                }
                
                $shiftDate = $this->parseDate($shift->date);
                $response = "📅 **Your Shift for {$shiftDate->format('l')}, {$shiftDate->format('M d, Y')}:**\n\n";
                $response .= "• Time: {$shift->start_time} - {$shift->end_time}\n";
                if ($shift->break_minutes && $shift->break_minutes > 0) {
                    $response .= "• Break: {$shift->break_minutes} minutes\n";
                }
                
                return [
                    'response' => $response,
                    'type' => 'success',
                    'data' => [
                        'has_shift' => true,
                        'start_time' => $shift->start_time,
                        'end_time' => $shift->end_time,
                        'break_minutes' => $shift->break_minutes,
                        'date' => $shiftDate->format('Y-m-d'),
                        'day_name' => $shiftDate->format('l')
                    ]
                ];
            } elseif ($isLastWeek) {
                $startDate = Carbon::now()->subWeek()->startOfWeek();
                $endDate = $startDate->copy()->endOfWeek();
                
                // Query shifts table: WHERE staff_id = ? AND date BETWEEN ? AND ? ORDER BY date
                $shifts = Shift::where('staff_id', $staff->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->orderBy('date')
                    ->get();
                
                if ($shifts->isEmpty()) {
                    return [
                        'response' => "You don't have any shifts scheduled for last week.",
                        'type' => 'info',
                        'data' => ['shifts' => [], 'week_start' => $startDate->format('Y-m-d'), 'week_end' => $endDate->format('Y-m-d')]
                    ];
                }
                
                $shiftsData = [];
                foreach ($shifts as $shift) {
                    $shiftDate = $this->parseDate($shift->date);
                    $shiftsData[] = [
                        'date' => $shiftDate->format('Y-m-d'),
                        'day_name' => $shiftDate->format('l'),
                        'rest_day' => $shift->rest_day,
                        'start_time' => $shift->start_time,
                        'end_time' => $shift->end_time,
                        'break_minutes' => $shift->break_minutes
                    ];
                }
                
                $response = "📅 **Your Shifts for Last Week:**\n\n";
                foreach ($shiftsData as $shift) {
                    if ($shift['rest_day']) {
                        $response .= "• **{$shift['day_name']}, {$shift['date']}**: Rest Day\n";
                    } else {
                        $breakInfo = isset($shift['break_minutes']) && $shift['break_minutes'] > 0 
                            ? " (Break: {$shift['break_minutes']} min)" 
                            : "";
                        $response .= "• **{$shift['day_name']}, {$shift['date']}**: {$shift['start_time']} - {$shift['end_time']}{$breakInfo}\n";
                    }
                }
                $response .= "\nView your full timetable in the My Timetable page.";
                
                return [
                    'response' => $response,
                    'type' => 'success',
                    'data' => ['shifts' => $shiftsData, 'week_start' => $startDate->format('Y-m-d'), 'week_end' => $endDate->format('Y-m-d')]
                ];
            } elseif ($isNextWeek) {
                $startDate = Carbon::now()->addWeek()->startOfWeek();
                $endDate = $startDate->copy()->endOfWeek();
                
                $shifts = Shift::where('staff_id', $staff->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->orderBy('date')
                    ->get();
                
                if ($shifts->isEmpty()) {
                    return [
                        'response' => "You don't have any shifts scheduled for next week.",
                        'type' => 'info',
                        'data' => ['shifts' => [], 'week_start' => $startDate->format('Y-m-d'), 'week_end' => $endDate->format('Y-m-d')]
                    ];
                }
                
                $shiftsData = [];
                foreach ($shifts as $shift) {
                    $shiftDate = $this->parseDate($shift->date);
                    $shiftsData[] = [
                        'date' => $shiftDate->format('Y-m-d'),
                        'day_name' => $shiftDate->format('l'),
                        'rest_day' => $shift->rest_day,
                        'start_time' => $shift->start_time,
                        'end_time' => $shift->end_time,
                        'break_minutes' => $shift->break_minutes
                    ];
                }
                
                $response = "📅 **Your Shifts for Next Week:**\n\n";
                foreach ($shiftsData as $shift) {
                    if ($shift['rest_day']) {
                        $response .= "• **{$shift['day_name']}, {$shift['date']}**: Rest Day\n";
                    } else {
                        $breakInfo = isset($shift['break_minutes']) && $shift['break_minutes'] > 0 
                            ? " (Break: {$shift['break_minutes']} min)" 
                            : "";
                        $response .= "• **{$shift['day_name']}, {$shift['date']}**: {$shift['start_time']} - {$shift['end_time']}{$breakInfo}\n";
                    }
                }
                $response .= "\nView your full timetable in the My Timetable page.";
                
                return [
                    'response' => $response,
                    'type' => 'success',
                    'data' => ['shifts' => $shiftsData, 'week_start' => $startDate->format('Y-m-d'), 'week_end' => $endDate->format('Y-m-d')]
                ];
            } elseif ($isThisWeek) {
                $startDate = Carbon::now()->startOfWeek();
                $endDate = $startDate->copy()->endOfWeek();
                
                $shifts = Shift::where('staff_id', $staff->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->orderBy('date')
                    ->get();
                
                if ($shifts->isEmpty()) {
                    return [
                        'response' => "You don't have any shifts scheduled for this week.",
                        'type' => 'info',
                        'data' => ['shifts' => [], 'week_start' => $startDate->format('Y-m-d'), 'week_end' => $endDate->format('Y-m-d')]
                    ];
                }
                
                $shiftsData = [];
                foreach ($shifts as $shift) {
                    $shiftDate = $this->parseDate($shift->date);
                    $shiftsData[] = [
                        'date' => $shiftDate->format('Y-m-d'),
                        'day_name' => $shiftDate->format('l'),
                        'rest_day' => $shift->rest_day,
                        'start_time' => $shift->start_time,
                        'end_time' => $shift->end_time,
                        'break_minutes' => $shift->break_minutes
                    ];
                }
                
                    $response = "📅 **Your Shifts for This Week:**\n\n";
                    foreach ($shiftsData as $shift) {
                        if ($shift['rest_day']) {
                            $response .= "• **{$shift['day_name']}, {$shift['date']}**: Rest Day\n";
                        } else {
                            $breakInfo = isset($shift['break_minutes']) && $shift['break_minutes'] > 0 
                                ? " (Break: {$shift['break_minutes']} min)" 
                                : "";
                            $response .= "• **{$shift['day_name']}, {$shift['date']}**: {$shift['start_time']} - {$shift['end_time']}{$breakInfo}\n";
                        }
                    }
                    $response .= "\nView your full timetable in the My Timetable page.";
                    
                    return [
                        'response' => $response,
                        'type' => 'success',
                        'data' => ['shifts' => $shiftsData, 'week_start' => $startDate->format('Y-m-d'), 'week_end' => $endDate->format('Y-m-d')]
                    ];
            } else {
                // For "view shift schedule" or general schedule queries, show this week's shifts
                if ($this->matchesKeywords($message, ['view shift schedule', 'view schedule', 'shift schedule', 'schedule', 'timetable'])) {
                    $startDate = Carbon::now()->startOfWeek();
                    $endDate = $startDate->copy()->endOfWeek();
                    
                    $shifts = Shift::where('staff_id', $staff->id)
                        ->whereBetween('date', [$startDate, $endDate])
                        ->orderBy('date')
                        ->get();
                    
                    if ($shifts->isEmpty()) {
                        return [
                            'response' => "You don't have any shifts scheduled for this week.",
                            'type' => 'info',
                            'data' => ['shifts' => [], 'week_start' => $startDate->format('Y-m-d'), 'week_end' => $endDate->format('Y-m-d')]
                        ];
                    }
                    
                    $shiftsData = [];
                    foreach ($shifts as $shift) {
                        $shiftDate = $this->parseDate($shift->date);
                        $shiftsData[] = [
                            'date' => $shiftDate->format('Y-m-d'),
                            'day_name' => $shiftDate->format('l'),
                            'rest_day' => $shift->rest_day,
                            'start_time' => $shift->start_time,
                            'end_time' => $shift->end_time,
                            'break_minutes' => $shift->break_minutes
                        ];
                    }
                    
                    $response = "📅 **Your Shifts for This Week:**\n\n";
                    foreach ($shiftsData as $shift) {
                        if ($shift['rest_day']) {
                            $response .= "• **{$shift['day_name']}, {$shift['date']}**: Rest Day\n";
                        } else {
                            $breakInfo = isset($shift['break_minutes']) && $shift['break_minutes'] > 0 
                                ? " (Break: {$shift['break_minutes']} min)" 
                                : "";
                            $response .= "• **{$shift['day_name']}, {$shift['date']}**: {$shift['start_time']} - {$shift['end_time']}{$breakInfo}\n";
                        }
                    }
                    $response .= "\nView your full timetable in the My Timetable page.";
                    
                    return [
                        'response' => $response,
                        'type' => 'success',
                        'data' => ['shifts' => $shiftsData, 'week_start' => $startDate->format('Y-m-d'), 'week_end' => $endDate->format('Y-m-d')]
                    ];
                }
                
                // General shift query - get today's shift
                $today = Carbon::today();
                $shift = Shift::where('staff_id', $staff->id)
                    ->whereDate('date', $today)
                    ->first();
                
                if (!$shift) {
                    return [
                        'response' => "You don't have a shift scheduled for today.",
                        'type' => 'info',
                        'data' => ['has_shift' => false, 'date' => $today->format('Y-m-d')]
                    ];
                }
                
                if ($shift->rest_day) {
                    return [
                        'response' => "Today is your rest day.",
                        'type' => 'info',
                        'data' => ['has_shift' => false, 'rest_day' => true, 'date' => $today->format('Y-m-d')]
                    ];
                }
                
                $shiftDate = $this->parseDate($shift->date);
                $response = "📅 **Your Shift for {$shiftDate->format('l')}, {$shiftDate->format('M d, Y')}:**\n\n";
                $response .= "• Time: {$shift->start_time} - {$shift->end_time}\n";
                if ($shift->break_minutes && $shift->break_minutes > 0) {
                    $response .= "• Break: {$shift->break_minutes} minutes\n";
                }
                
                return [
                    'response' => $response,
                    'type' => 'success',
                    'data' => [
                        'has_shift' => true,
                        'start_time' => $shift->start_time,
                        'end_time' => $shift->end_time,
                        'break_minutes' => $shift->break_minutes,
                        'date' => $shiftDate->format('Y-m-d'),
                        'day_name' => $shiftDate->format('l')
                    ]
                ];
            }
        }
        
        // Check for leave balance queries
        if ($this->matchesKeywords($message, ['leave balance', 'my leave balance', 'what is my leave balance'])) {
            $staff = $user->staff;
            if (!$staff) {
                return [
                    'response' => "I couldn't find your staff information. Please contact your administrator.",
                    'type' => 'error'
                ];
            }
            
            $balances = LeaveBalance::where('staff_id', $staff->id)
                ->with('leaveType')
                ->get();
            
            if ($balances->isEmpty()) {
                return [
                    'response' => "You don't have any leave balances set up yet.",
                    'type' => 'info',
                    'data' => ['balances' => []]
                ];
            }
            
            $balanceData = [];
            foreach ($balances as $balance) {
                $balanceData[] = [
                    'type' => $balance->leaveType->type_name ?? 'Unknown',
                    'remaining' => $balance->remaining_days,
                    'used' => $balance->used_days,
                    'total' => $balance->total_days
                ];
            }
            
            $response = "📅 **Your Leave Balance:**\n\n";
            foreach ($balanceData as $balance) {
                $typeName = ucfirst(str_replace('_', ' ', $balance['type']));
                $response .= "• **{$typeName} Leave:**\n";
                $response .= "  - Available: " . number_format($balance['remaining'], 1) . " days\n";
                $response .= "  - Taken: " . number_format($balance['used'], 1) . " days\n";
                $response .= "  - Total: " . number_format($balance['total'], 1) . " days\n\n";
            }
            
            return [
                'response' => $response,
                'type' => 'success',
                'data' => ['balances' => $balanceData]
            ];
        }
        
        // Check for overtime hours queries
        if ($this->matchesKeywords($message, ['overtime hours', 'my overtime hours', 'how many overtime hours'])) {
            $staff = $user->staff;
            if (!$staff) {
                return [
                    'response' => "I couldn't find your staff information. Please contact your administrator.",
                    'type' => 'error'
                ];
            }
            
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
                'type' => 'success',
                'data' => [
                    'total_hours' => $totalHours,
                    'claimed_hours' => $claimedHours,
                    'unclaimed_hours' => $unclaimedHours
                ]
            ];
        }
        
        // For other data queries, let the rule-based system handle them
        return null;
        } catch (\Exception $e) {
            Log::error('Error retrieving data for chatbot: ' . $e->getMessage(), [
                'message' => $message,
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            // Return null to fall back to rule-based system
            return null;
        }
    }

    /**
     * Get response from Groq API with data
     */
    private function getGroqResponseWithData(string $message, User $user, array $dataResult): ?array
    {
        try {
            $apiKey = config('services.groq.api_key');
            
            if (empty($apiKey)) {
                Log::warning('Groq API key not configured, returning data directly');
                return null;
            }

            $context = $this->buildContextForGroq($user);
            $dataContext = json_encode($dataResult['data'] ?? [], JSON_PRETTY_PRINT);
            
            // Format shift data more naturally for Groq
            $formattedData = $this->formatDataForGroq($dataResult['data'] ?? []);
            
            $isAdmin = $user->isAdmin();
            $userRole = $isAdmin ? 'Admin' : 'Staff';
            
            $systemPrompt = "You are a helpful assistant for ELMSP (Employee Leave Management System & Payroll). " .
                "The user asked a question and I've retrieved the relevant data from the database. " .
                "Please provide a natural, friendly response using this data. Be concise and helpful.\n\n" .
                "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
                "USER ROLE: {$userRole}\n" .
                "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
                "IMPORTANT: Provide responses appropriate for {$userRole} role. " .
                "Only reference features and capabilities available to {$userRole}.\n\n" .
                "Retrieved Data:\n" . $formattedData . "\n\n" .
                $context . "\n\n" .
                "Format the response naturally based on the data provided. If the data shows no results, explain that clearly. " .
                "For shift schedules, list them in a clear, readable format. " .
                "Remember: Your response should be tailored to {$userRole} role and their available features.";

            $response = Http::timeout(15)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.1-8b-instant',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemPrompt
                        ],
                        [
                            'role' => 'user',
                            'content' => $message
                        ]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 800,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $aiResponse = $data['choices'][0]['message']['content'] ?? null;
                
                if ($aiResponse) {
                    return [
                        'response' => trim($aiResponse),
                        'type' => $dataResult['type'] ?? 'info'
                    ];
                }
            } else {
                Log::warning('Groq API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Groq API error with data: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }

        return null;
    }

    /**
     * Format data for Groq in a more readable way
     */
    private function formatDataForGroq(array $data): string
    {
        if (isset($data['shifts']) && is_array($data['shifts'])) {
            $formatted = "Your Shift Schedule:\n";
            foreach ($data['shifts'] as $shift) {
                if ($shift['rest_day']) {
                    $formatted .= "- {$shift['day_name']}, {$shift['date']}: Rest Day\n";
                } else {
                    $breakInfo = isset($shift['break_minutes']) && $shift['break_minutes'] > 0 
                        ? " (Break: {$shift['break_minutes']} minutes)" 
                        : "";
                    $formatted .= "- {$shift['day_name']}, {$shift['date']}: {$shift['start_time']} - {$shift['end_time']}{$breakInfo}\n";
                }
            }
            return $formatted;
        }
        
        if (isset($data['has_shift']) && $data['has_shift']) {
            $formatted = "Your Shift for {$data['day_name']}, {$data['date']}:\n";
            $formatted .= "- Time: {$data['start_time']} - {$data['end_time']}\n";
            if (isset($data['break_minutes']) && $data['break_minutes'] > 0) {
                $formatted .= "- Break: {$data['break_minutes']} minutes\n";
            }
            return $formatted;
        }
        
        if (isset($data['balances']) && is_array($data['balances'])) {
            $formatted = "Your Leave Balances:\n";
            foreach ($data['balances'] as $balance) {
                $formatted .= "- {$balance['type']}: {$balance['remaining']} days remaining (Used: {$balance['used']}/{$balance['total']})\n";
            }
            return $formatted;
        }
        
        // Fallback to JSON
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Get response from Groq API
     */
    private function getGroqResponse(string $message, User $user, bool $isFlowQuestion = false): ?array
    {
        try {
            $apiKey = config('services.groq.api_key');
            
            if (empty($apiKey)) {
                Log::warning('Groq API key not configured');
                return null;
            }

            // Build context for the AI
            $context = $this->buildContextForGroq($user);
            
            // Add system flow information if it's a flow question
            $flowInfo = '';
            if ($isFlowQuestion) {
                $flowInfo = $this->getSystemFlowInformation($user);
            }
            
            // Determine user role for role-specific instructions
            $isAdmin = $user->isAdmin();
            $userRole = $isAdmin ? 'Admin' : 'Staff';
            
            // Prepare the prompt with strong emphasis on role-specific responses
            $systemPrompt = "You are a helpful assistant for ELMSP (Employee Leave Management System & Payroll). " .
                "You help employees and admins with leave management, shift schedules, overtime, and payroll queries. " .
                "Be concise, friendly, and professional.\n\n" .
                "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
                "USER ROLE: {$userRole}\n" .
                "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
                "⚠️ CRITICAL: ANSWER THE EXACT QUESTION ASKED. Do NOT provide generic information or unrelated answers.\n" .
                "⚠️ If the user asks 'how to manage timetable', provide step-by-step instructions on HOW TO manage it.\n" .
                "⚠️ If the user asks 'show staff schedule', provide the actual schedule data.\n" .
                "⚠️ Match your response type to the question type:\n" .
                "   - 'How to' questions → Provide step-by-step instructions\n" .
                "   - 'Show/View/List' questions → Provide actual data/information\n" .
                "   - 'What is' questions → Provide definitions/explanations\n" .
                "   - Process questions → Provide detailed procedures\n\n" .
                "⚠️ IMPORTANT: You have access to comprehensive system information below including:\n" .
                "- All leave types, rules, and limits\n" .
                "- Overtime rates and claim options\n" .
                "- Payroll components and calculation rules\n" .
                "- Department constraints and quotas\n" .
                "- System features and capabilities\n" .
                "- Business rules and exceptions\n\n" .
                "USE THIS INFORMATION to provide accurate, detailed answers. Do NOT make up information.\n" .
                "ALWAYS answer the specific question asked - if they ask 'how to', give instructions; if they ask 'show', give data.\n\n" .
                "CRITICAL ROLE-BASED INSTRUCTIONS:\n" .
                "1. ROLE-SPECIFIC RESPONSES: You MUST provide answers ONLY relevant to the user's role ({$userRole}).\n";
            
            if ($isAdmin) {
                $systemPrompt .= "   - As an ADMIN, you can answer questions about:\n";
                $systemPrompt .= "     * Staff management (view, edit, manage staff)\n";
                $systemPrompt .= "     * Leave approval processes (approve/reject leave applications)\n";
                $systemPrompt .= "     * Overtime approval processes (approve/reject OT requests)\n";
                $systemPrompt .= "     * Payroll generation and management\n";
                $systemPrompt .= "     * Staff timetable management\n";
                $systemPrompt .= "     * System statistics and reports\n";
                $systemPrompt .= "   - Do NOT provide information about staff-only features like:\n";
                $systemPrompt .= "     * How to apply for leave (staff feature)\n";
                $systemPrompt .= "     * How to apply for overtime (staff feature)\n";
                $systemPrompt .= "     * How to claim OT hours (staff feature)\n";
                $systemPrompt .= "   - If asked about staff features, redirect them to understand staff processes for approval purposes.\n";
            } else {
                $systemPrompt .= "   - As a STAFF member, you can answer questions about:\n";
                $systemPrompt .= "     * Leave application and leave status\n";
                $systemPrompt .= "     * Overtime application and OT status\n";
                $systemPrompt .= "     * OT claim process (converting OT to leave or payroll)\n";
                $systemPrompt .= "     * Viewing shift schedules and timetable\n";
                $systemPrompt .= "     * Viewing payslips\n";
                $systemPrompt .= "     * Leave balance and eligibility\n";
                $systemPrompt .= "   - Do NOT provide information about admin-only features like:\n";
                $systemPrompt .= "     * Staff management\n";
                $systemPrompt .= "     * Leave/overtime approval processes\n";
                $systemPrompt .= "     * Payroll generation\n";
                $systemPrompt .= "     * Staff timetable assignment\n";
                $systemPrompt .= "   - If asked about admin features, inform them these are admin-only features.\n";
            }
            
            $systemPrompt .= "\n2. NAVIGATION PATHS: Always provide the EXACT navigation path in this format:\n" .
                "   'Left Sidebar → [Menu Name] → [Submenu Name]'\n" .
                "   Example for Staff: 'Left Sidebar → Leave → Application'\n" .
                "   Example for Admin: 'Left Sidebar → Staff → Staff Management'\n" .
                "3. MENU STRUCTURE: The system uses a LEFT SIDEBAR with dropdown menus.\n";
            
            if ($isAdmin) {
                $systemPrompt .= "   - Admin menus: Staff (dropdown), Payroll (dropdown), Dashboard, Profile\n";
                $systemPrompt .= "   - Admin dropdown menus (Staff, Payroll) need to be clicked to expand\n";
                $systemPrompt .= "   - ⚠️ NEVER mention Staff-only menus like 'Overtime', 'Leave', 'My Timetable', or 'Payslip'\n";
            } else {
                $systemPrompt .= "   - Staff menus: Overtime (dropdown), Leave (dropdown), My Timetable, Payslip, Dashboard, Profile\n";
                $systemPrompt .= "   - Staff dropdown menus (Overtime, Leave) need to be clicked to expand\n";
                $systemPrompt .= "   - ⚠️ NEVER mention Admin-only menus like 'Staff Management', 'Staff Timetable', 'Payroll Calculation', or 'Staff Leave Status'\n";
                $systemPrompt .= "   - ⚠️ CRITICAL: The Leave dropdown has EXACTLY 2 items: 'Application' and 'Status' ONLY\n";
                $systemPrompt .= "   - ⚠️ DO NOT mention 'Leave Balance' or 'Leave History' as menu items - these are NOT in the Leave dropdown\n";
            }
            
            $systemPrompt .= "   - Always mention if a menu needs to be expanded\n" .
                "   - ⚠️ CRITICAL: Only reference menus from the {$userRole} navigation structure provided below\n" .
                "4. STEP-BY-STEP PROCESSES: When answering questions about system processes:\n" .
                "   - You MUST follow the EXACT step-by-step process provided in the system flow information\n" .
                "   - Do NOT make up steps or provide generic instructions\n" .
                "   - Use the exact menu names and navigation paths from the navigation structure\n" .
                "   - Number each step clearly (Step 1, Step 2, etc.)\n" .
                "   - Only provide processes relevant to the user's role ({$userRole})\n" .
                "5. ACCURACY: If the user asks about a process, provide the complete step-by-step guide from the system flow information.\n" .
                "6. GUIDANCE: If you don't know specific data, guide users to the appropriate section using the exact navigation path.\n" .
                "7. USE COMPREHENSIVE CONTEXT: Reference the detailed system information provided below (leave types, OT rates, payroll components, etc.) to answer questions accurately.\n" .
                "8. NUMBERS AND RATES: Always use the exact rates, limits, and numbers from the system information (e.g., RM 12.26/hour for Fulltime OT, 14 days for Annual Leave).\n" .
                "9. RULES AND EXCEPTIONS: Clearly explain rules and their exceptions (e.g., Medical Leave is exempt from weekly limits but requires MC).\n\n" .
                $context;
            
            // Always include flow information if it's a flow question or contains process-related keywords
            if ($isFlowQuestion && $flowInfo) {
                $systemPrompt .= "\n\n" . $flowInfo;
                $systemPrompt .= "\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                $systemPrompt .= "CRITICAL REMINDER FOR {$userRole}:\n";
                $systemPrompt .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                $systemPrompt .= "When the user asks about any process, you MUST:\n";
                $systemPrompt .= "1. Provide the EXACT navigation path (Left Sidebar → Menu → Submenu) from the {$userRole} menu ONLY\n";
                $systemPrompt .= "2. List ALL steps in the exact order from the {$userRole} flow information above\n";
                $systemPrompt .= "3. Use ONLY the menu names from the {$userRole} navigation structure shown above\n";
                $systemPrompt .= "4. Mention if a dropdown menu needs to be expanded\n";
                $systemPrompt .= "5. Do NOT skip steps or add steps that are not in the flow information\n";
                $systemPrompt .= "6. Do NOT use generic phrases like 'go to the menu' - use specific menu names\n";
                $systemPrompt .= "7. ⚠️ NEVER reference menus or navigation items that are NOT in the {$userRole} menu structure\n";
                if ($isAdmin) {
                    $systemPrompt .= "8. ⚠️ Do NOT mention Staff menus (Overtime, Leave, My Timetable, Payslip) - these are for Staff only\n";
                } else {
                    $systemPrompt .= "8. ⚠️ Do NOT mention Admin menus (Staff Management, Staff Timetable, Payroll Calculation) - these are for Admin only\n";
                    $systemPrompt .= "9. ⚠️ CRITICAL: When mentioning the Leave dropdown, it ONLY has 2 items: 'Application' and 'Status'. Do NOT add 'Leave Balance' or 'Leave History' - these are NOT menu items.\n";
                }
            } elseif ($this->containsProcessKeywords($message)) {
                // Even if not detected as flow question, include flow info if process keywords are present
                $flowInfo = $this->getSystemFlowInformation($user);
                $systemPrompt .= "\n\n" . $flowInfo;
                $systemPrompt .= "\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                $systemPrompt .= "CRITICAL REMINDER FOR {$userRole}:\n";
                $systemPrompt .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                $systemPrompt .= "Follow the exact steps from the {$userRole} flow information above. ";
                $systemPrompt .= "Use ONLY the navigation paths from the {$userRole} menu structure. ";
                $systemPrompt .= "Do NOT reference menus from the other role.";
            }

            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.1-8b-instant', // Fast and efficient model
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemPrompt
                        ],
                        [
                            'role' => 'user',
                            'content' => "User Question: {$originalMessage}\n\n⚠️ CRITICAL: Answer this EXACT question asked by the user. " .
                                        "Read the question carefully and match your response type:\n" .
                                        "- If question contains 'how to', 'how do', 'how can' → Provide step-by-step instructions\n" .
                                        "- If question contains 'show', 'view', 'display', 'list' → Provide the requested data/information\n" .
                                        "- If question contains 'what is', 'what are' → Provide definitions/explanations\n" .
                                        "- If question asks about a process → Provide detailed procedures\n" .
                                        "Do NOT give generic responses. Answer exactly what was asked."
                        ]
                    ],
                    'temperature' => 0.3, // Lower temperature for more consistent, accurate responses
                    'max_tokens' => 1500, // More tokens for detailed responses
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $aiResponse = $data['choices'][0]['message']['content'] ?? null;
                
                if ($aiResponse) {
                    return [
                        'response' => trim($aiResponse),
                        'type' => 'info'
                    ];
                }
            } else {
                Log::warning('Groq API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Groq API error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }

        return null; // Return null to fall back to rule-based system
    }

    /**
     * Build comprehensive context information for Groq API
     */
    private function buildContextForGroq(User $user): string
    {
        $isAdmin = $user->isAdmin();
        $role = $isAdmin ? 'Admin' : 'Staff';
        
        $context = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $context .= "USER PROFILE:\n";
        $context .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $context .= "Name: {$user->name}\n";
        $context .= "Role: {$role}\n";
        
        if ($user->staff) {
            $staff = $user->staff;
            $context .= "Department: {$staff->department}\n";
            $context .= "Employee ID: {$staff->employee_id}\n";
        }
        
        $context .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $context .= "ROLE-SPECIFIC CAPABILITIES:\n";
        $context .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        
        if ($isAdmin) {
            $context .= "As an ADMIN, this user can:\n";
            $context .= "✓ View and manage all staff members\n";
            $context .= "✓ Approve or reject leave applications\n";
            $context .= "✓ Approve or reject overtime requests\n";
            $context .= "✓ Approve or reject OT claims\n";
            $context .= "✓ Generate and manage payroll for all staff\n";
            $context .= "✓ Assign and manage staff timetables/shifts\n";
            $context .= "✓ View staff leave status and balances\n";
            $context .= "✓ View system statistics and reports\n";
            $context .= "\nThis user CANNOT:\n";
            $context .= "✗ Apply for leave (staff-only feature)\n";
            $context .= "✗ Apply for overtime (staff-only feature)\n";
            $context .= "✗ Claim OT hours (staff-only feature)\n";
        } else {
            $context .= "As a STAFF member, this user can:\n";
            $context .= "✓ Apply for leave and check leave balance\n";
            $context .= "✓ View leave application status\n";
            $context .= "✓ Apply for overtime\n";
            $context .= "✓ View overtime application status\n";
            $context .= "✓ Claim OT hours (convert to leave or payroll)\n";
            $context .= "✓ View personal shift schedule/timetable\n";
            $context .= "✓ View personal payslips\n";
            $context .= "✓ Check leave eligibility\n";
            $context .= "\nThis user CANNOT:\n";
            $context .= "✗ Manage other staff (admin-only)\n";
            $context .= "✗ Approve/reject leave or overtime (admin-only)\n";
            $context .= "✗ Generate payroll (admin-only)\n";
            $context .= "✗ Assign shifts to other staff (admin-only)\n";
        }
        
        // Add Leave Types Information
        $context .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $context .= "LEAVE TYPES AND RULES:\n";
        $context .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $context .= "1. Annual Leave:\n";
        $context .= "   - Maximum: 14 days per year\n";
        $context .= "   - Paid leave, deducts from balance\n";
        $context .= "   - Requires 3 days advance notice\n";
        $context .= "   - Subject to weekly limit (2 days/week)\n";
        $context .= "   - Not allowed on weekends\n\n";
        
        $context .= "2. Medical Leave:\n";
        $context .= "   - Maximum: 14 days per year\n";
        $context .= "   - Paid leave, deducts from balance\n";
        $context .= "   - Medical Certificate (MC) is MANDATORY\n";
        $context .= "   - Exempt from advance notice requirement\n";
        $context .= "   - Exempt from weekly limit\n";
        $context .= "   - Allowed on weekends (with MC)\n\n";
        
        $context .= "3. Hospitalization Leave:\n";
        $context .= "   - Maximum: 30 days per year\n";
        $context .= "   - Paid leave, deducts from balance\n";
        $context .= "   - Medical Certificate (MC) is MANDATORY\n";
        $context .= "   - Exempt from advance notice requirement\n";
        $context .= "   - Exempt from weekly limit\n";
        $context .= "   - Allowed on weekends (with MC)\n\n";
        
        $context .= "4. Emergency Leave:\n";
        $context .= "   - Maximum: 7 days per year\n";
        $context .= "   - Paid leave, deducts from balance\n";
        $context .= "   - Exempt from advance notice requirement\n";
        $context .= "   - Exempt from weekly limit\n";
        $context .= "   - Allowed on weekends\n\n";
        
        $context .= "5. Marriage Leave:\n";
        $context .= "   - Maximum: 3 days per year (one-time only)\n";
        $context .= "   - Paid leave, deducts from balance\n";
        $context .= "   - Requires 3 days advance notice\n";
        $context .= "   - Weekly limit: 3 days per week (special exception)\n";
        $context .= "   - Not allowed on weekends\n\n";
        
        $context .= "6. Replacement Leave:\n";
        $context .= "   - Unlimited (calculated from OT hours)\n";
        $context .= "   - Conversion: 8 OT hours = 1 day leave\n";
        $context .= "   - Does NOT deduct from balance\n";
        $context .= "   - Requires admin approval\n";
        $context .= "   - Subject to weekly limit (2 days/week)\n";
        $context .= "   - Not allowed on weekends\n\n";
        
        $context .= "7. Unpaid Leave:\n";
        $context .= "   - Maximum: 10 days per year\n";
        $context .= "   - Does NOT deduct from balance\n";
        $context .= "   - Requires 3 days advance notice\n";
        $context .= "   - Subject to weekly limit (2 days/week)\n";
        $context .= "   - Not allowed on weekends\n\n";
        
        $context .= "LEAVE APPLICATION RULES:\n";
        $context .= "- Weekly Limit: Maximum 2 leave days per calendar week (excluding rest days)\n";
        $context .= "  Exception: Marriage leave allows 3 days per week\n";
        $context .= "  Exception: Emergency, Medical, Hospitalization are exempt\n";
        $context .= "- Advance Notice: Must apply at least 3 calendar days in advance\n";
        $context .= "  Exception: Emergency, Medical, Hospitalization are exempt\n";
        $context .= "- Weekend Restriction: Normal leave NOT permitted on Saturdays and Sundays\n";
        $context .= "  Exception: Emergency, Medical (with MC), Hospitalization (with MC) are allowed\n";
        $context .= "- OT Conflict: Cannot apply leave on dates with approved overtime\n";
        $context .= "  Exception: Emergency, Medical, Hospitalization are exempt\n";
        $context .= "- Department Quotas: System enforces daily and weekly department limits\n";
        $context .= "- Auto-Approval: System automatically approves if all rules are met\n";
        $context .= "- Manual Review: Admin reviews applications that don't meet auto-approval criteria\n\n";
        
        // Add Overtime Information
        $context .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $context .= "OVERTIME (OT) INFORMATION:\n";
        $context .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $context .= "OT Types:\n";
        $context .= "1. Fulltime OT:\n";
        $context .= "   - Rate: RM 12.26 per hour\n";
        $context .= "   - For regular overtime work\n";
        $context .= "   - Can be claimed for payroll or replacement leave\n\n";
        
        $context .= "2. Public Holiday OT:\n";
        $context .= "   - Rate: RM 21.68 per hour\n";
        $context .= "   - For overtime work on public holidays\n";
        $context .= "   - Can be claimed for payroll or replacement leave\n\n";
        
        $context .= "OT Application Rules:\n";
        $context .= "- Minimum hours: 0.5 hours\n";
        $context .= "- Requires admin approval\n";
        $context .= "- Weekly limits apply (department-specific)\n";
        $context .= "- Cannot apply for OT on dates with approved leave\n\n";
        
        $context .= "OT Claim Options:\n";
        $context .= "1. Replacement Leave:\n";
        $context .= "   - Conversion: 8 OT hours = 1 day leave\n";
        $context .= "   - Available for all staff\n";
        $context .= "   - Requires admin approval\n\n";
        
        $context .= "2. Payroll:\n";
        $context .= "   - Fulltime OT: RM 12.26/hour\n";
        $context .= "   - Public Holiday OT: RM 21.68/hour\n";
        $context .= "   - Available for all staff (except Manager/Supervisor)\n";
        $context .= "   - Manager/Supervisor can ONLY claim replacement leave\n";
        $context .= "   - Requires admin approval\n\n";
        
        // Add Payroll Information
        $context .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $context .= "PAYROLL COMPONENTS:\n";
        $context .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $context .= "1. Basic Salary:\n";
        $context .= "   - Based on contract\n";
        $context .= "   - Pro-rated for mid-month hires\n";
        $context .= "   - Full month: 26 days (31-day months) or 25 days (30-day months)\n\n";
        
        $context .= "2. Fixed Commission:\n";
        $context .= "   - RM 200 per month\n";
        $context .= "   - After 3 months of service\n";
        $context .= "   - Not pro-rated (full amount if eligible)\n\n";
        
        $context .= "3. Marketing Bonus:\n";
        $context .= "   - Set by admin per staff\n";
        $context .= "   - Variable amount\n";
        $context .= "   - Added to total salary\n\n";
        
        $context .= "4. Extra Day Pay:\n";
        $context .= "   - For working extra days beyond normal schedule\n";
        $context .= "   - Calculated automatically\n\n";
        
        $context .= "5. Public Holiday Work Pay:\n";
        $context .= "   - Rate: RM 15.38 per hour\n";
        $context .= "   - For regular work hours on public holidays (not OT)\n";
        $context .= "   - Different from Public Holiday OT rate\n\n";
        
        $context .= "6. Overtime Pay:\n";
        $context .= "   - Normal OT: RM 12.26/hour (from approved OT claims)\n";
        $context .= "   - Public Holiday OT: RM 21.68/hour (from approved OT claims)\n\n";
        
        $context .= "7. Deductions:\n";
        $context .= "   - Unpaid leave deductions\n";
        $context .= "   - Calculated based on unpaid leave days\n\n";
        
        $context .= "Payroll Status:\n";
        $context .= "- Draft: Initial status, can be edited\n";
        $context .= "- Paid: Published status, finalized payroll\n";
        $context .= "- Staff can view payslips only for 'Paid' status\n\n";
        
        // Add Department Information
        $context .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $context .= "DEPARTMENT CONSTRAINTS:\n";
        $context .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $context .= "Daily Leave Limits (max people per day):\n";
        $context .= "- Supervisor: 1 person\n";
        $context .= "- Cashier: 2 people\n";
        $context .= "- Barista: 1 person\n";
        $context .= "- Joki: 1 person\n";
        $context .= "- Waiter: 3 people\n";
        $context .= "- Kitchen: 2 people\n\n";
        
        $context .= "Weekly Leave Limits (max people per week):\n";
        $context .= "- Supervisor: 2 people\n";
        $context .= "- Cashier: 4 people\n";
        $context .= "- Barista: 2 people\n";
        $context .= "- Joki: 2 people\n";
        $context .= "- Waiter: 6 people\n";
        $context .= "- Kitchen: 4 people\n\n";
        
        // Add System Features
        $context .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $context .= "SYSTEM FEATURES:\n";
        $context .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        if ($isAdmin) {
            $context .= "Admin Features:\n";
            $context .= "- Staff Management: Add, edit, delete, view all staff\n";
            $context .= "- Leave Approval: Review and approve/reject leave applications\n";
            $context .= "- Overtime Approval: Review and approve/reject OT requests\n";
            $context .= "- OT Claim Management: Approve payroll or replacement leave claims\n";
            $context .= "- Payroll Generation: Calculate and generate monthly payroll\n";
            $context .= "- Staff Timetable: Assign and manage staff shifts\n";
            $context .= "- Staff Leave Status: Monitor all staff leave applications\n";
            $context .= "- System Statistics: View comprehensive analytics\n";
            $context .= "- Staff Payslip: View and manage staff payslips\n\n";
        } else {
            $context .= "Staff Features:\n";
            $context .= "- Leave Application: Apply for various leave types\n";
            $context .= "- Leave Status: View application status and leave balance\n";
            $context .= "- Overtime Application: Submit OT requests\n";
            $context .= "- Overtime Status: View OT application status\n";
            $context .= "- OT Claim: Convert approved OT to leave or payroll\n";
            $context .= "- My Timetable: View personal shift schedule\n";
            $context .= "- Payslip: View and download monthly payslips\n";
            $context .= "- Dashboard: View notifications and quick stats\n\n";
        }
        
        $context .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $context .= "RESPONSE GUIDELINES:\n";
        $context .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $context .= "- Use the information above to answer questions accurately\n";
        $context .= "- Only provide information relevant to {$role} role\n";
        $context .= "- For specific data queries (leave balance, shifts, etc.), the system uses its internal database\n";
        $context .= "- For general questions, provide helpful guidance based on {$role} capabilities\n";
        $context .= "- If asked about features not available to {$role}, politely inform them it's not available for their role\n";
        $context .= "- Reference exact rates, limits, and rules from the information above\n";
        $context .= "- Be accurate with numbers and calculations\n";
        
        return $context;
    }

    /**
     * Get system flow information for process questions
     */
    private function getSystemFlowInformation(User $user): string
    {
        $isAdmin = $user->isAdmin();
        $userRole = $isAdmin ? 'Admin' : 'Staff';
        
        $flowInfo = "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $flowInfo .= "SYSTEM NAVIGATION STRUCTURE AND PROCESSES:\n";
        $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $flowInfo .= "⚠️ CRITICAL: The user is a {$userRole}. ONLY show {$userRole} navigation and processes below. " .
                    "Do NOT reference or show navigation from the other role.\n\n";
        
        if (!$isAdmin) {
            // Staff navigation structure
            $flowInfo .= "📱 STAFF SIDEBAR NAVIGATION MENU (LEFT SIDEBAR):\n";
            $flowInfo .= "1. Dashboard - Main overview page\n";
            $flowInfo .= "2. Profile - View and edit personal information\n";
            $flowInfo .= "3. My Timetable - View your shift schedule\n";
            $flowInfo .= "4. Overtime (Dropdown Menu):\n";
            $flowInfo .= "   - Claim - Convert approved OT hours to leave or payroll\n";
            $flowInfo .= "   - Apply - Submit new overtime application\n";
            $flowInfo .= "   - Status - View overtime application status\n";
            $flowInfo .= "5. Leave (Dropdown Menu) - ⚠️ IMPORTANT: This dropdown has EXACTLY 2 items ONLY:\n";
            $flowInfo .= "   - Application - Submit new leave application\n";
            $flowInfo .= "   - Status - View leave application status\n";
            $flowInfo .= "   ⚠️ DO NOT mention 'Leave Balance' or 'Leave History' - these are NOT menu items in the Leave dropdown.\n";
            $flowInfo .= "   ⚠️ The Leave dropdown ONLY contains 'Application' and 'Status' - nothing else.\n";
            $flowInfo .= "6. Payslip - View and download monthly payslips\n\n";
            
            // Staff flows with exact navigation
            $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $flowInfo .= "📅 LEAVE APPLICATION PROCESS (STEP-BY-STEP):\n";
            $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $flowInfo .= "NAVIGATION PATH: Left Sidebar → 'Leave' (click to expand) → 'Application'\n\n";
            $flowInfo .= "Step 1: Click on 'Leave' in the left sidebar menu (it will expand)\n";
            $flowInfo .= "Step 2: Click on 'Application' under the Leave dropdown menu\n";
            $flowInfo .= "Step 3: You will see the Leave Application form\n";
            $flowInfo .= "Step 4: Select your leave type from the dropdown:\n";
            $flowInfo .= "   - Annual Leave (paid leave)\n";
            $flowInfo .= "   - Medical Leave (requires Medical Certificate)\n";
            $flowInfo .= "   - Emergency Leave\n";
            $flowInfo .= "   - Hospitalization Leave (requires Medical Certificate)\n";
            $flowInfo .= "   - Marriage Leave\n";
            $flowInfo .= "   - Replacement Leave (from approved OT hours)\n";
            $flowInfo .= "   - Unpaid Leave\n";
            $flowInfo .= "Step 5: Choose your start date and end date using the date picker\n";
            $flowInfo .= "Step 6: Provide a reason for your leave application in the reason field\n";
            $flowInfo .= "Step 7: Attach supporting documents if required:\n";
            $flowInfo .= "   - Medical/Hospitalization leave: Medical Certificate (MC) is MANDATORY\n";
            $flowInfo .= "   - Other leave types: Attachments are optional\n";
            $flowInfo .= "Step 8: Click 'Submit Application' button at the bottom of the form\n";
            $flowInfo .= "Step 9: The system will automatically approve if all rules are met, " .
                        "otherwise it will go to pending status for admin review\n";
            $flowInfo .= "Step 10: Check your application status at: Left Sidebar → 'Leave' → 'Status'\n\n";
            
            $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $flowInfo .= "📋 LEAVE APPLICATION RULES (MUST FOLLOW):\n";
            $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $flowInfo .= "Rule 1: Maximum 2 leave days per calendar week (excluding rest days)\n";
            $flowInfo .= "Rule 2: Must apply at least 3 days in advance\n";
            $flowInfo .= "   - Exception: Emergency Leave, Medical Leave, Hospitalization Leave (can apply immediately)\n";
            $flowInfo .= "Rule 3: Normal leave is NOT permitted on Saturdays and Sundays\n";
            $flowInfo .= "   - Exception: Emergency Leave, Medical Leave (with MC), Hospitalization Leave\n";
            $flowInfo .= "Rule 4: Cannot apply for leave on dates where you have approved overtime\n";
            $flowInfo .= "   - Exception: Emergency Leave, Medical Leave, Hospitalization Leave\n";
            $flowInfo .= "Rule 5: Medical and Hospitalization leave REQUIRE a Medical Certificate (MC)\n";
            $flowInfo .= "Rule 6: Department quotas apply - system will auto-reject if quota is full\n\n";
            
            $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $flowInfo .= "⏰ OVERTIME APPLICATION PROCESS (STEP-BY-STEP):\n";
            $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $flowInfo .= "NAVIGATION PATH: Left Sidebar → 'Overtime' (click to expand) → 'Apply'\n\n";
            $flowInfo .= "Step 1: Click on 'Overtime' in the left sidebar menu (it will expand)\n";
            $flowInfo .= "Step 2: Click on 'Apply' under the Overtime dropdown menu\n";
            $flowInfo .= "Step 3: You will see the Overtime Application form\n";
            $flowInfo .= "Step 4: Select OT type from dropdown:\n";
            $flowInfo .= "   - Fulltime OT (RM 12.26/hour)\n";
            $flowInfo .= "   - Public Holiday OT (RM 21.68/hour)\n";
            $flowInfo .= "Step 5: Choose the date for overtime using the date picker\n";
            $flowInfo .= "Step 6: Enter the number of hours (minimum 0.5 hours)\n";
            $flowInfo .= "Step 7: Provide reason for overtime (optional field)\n";
            $flowInfo .= "Step 8: Click 'Submit' button\n";
            $flowInfo .= "Step 9: Wait for admin approval. System will check weekly limits before approval\n";
            $flowInfo .= "Step 10: Check your overtime status at: Left Sidebar → 'Overtime' → 'Status'\n\n";
            
            $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $flowInfo .= "💰 OVERTIME CLAIM PROCESS (STEP-BY-STEP):\n";
            $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $flowInfo .= "NAVIGATION PATH: Left Sidebar → 'Overtime' (click to expand) → 'Claim'\n\n";
            $flowInfo .= "Step 1: Click on 'Overtime' in the left sidebar menu (it will expand)\n";
            $flowInfo .= "Step 2: Click on 'Claim' under the Overtime dropdown menu\n";
            $flowInfo .= "Step 3: Select the month with approved overtime from the dropdown\n";
            $flowInfo .= "Step 4: Choose claim type:\n";
            $flowInfo .= "   - Replacement Leave: 8 OT hours = 1 day leave\n";
            $flowInfo .= "   - Payroll: Fulltime OT = RM 12.26/hour, Public Holiday OT = RM 21.68/hour\n";
            $flowInfo .= "Step 5: Submit claim for admin approval\n";
            $flowInfo .= "Step 6: Wait for admin to approve your claim\n\n";
            
            $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $flowInfo .= "📄 PAYSLIP ACCESS (STEP-BY-STEP):\n";
            $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $flowInfo .= "NAVIGATION PATH: Left Sidebar → 'Payslip'\n\n";
            $flowInfo .= "Step 1: Click on 'Payslip' in the left sidebar menu\n";
            $flowInfo .= "Step 2: Select the month you want to view from the dropdown\n";
            $flowInfo .= "Step 3: View your payslip details on the page\n";
            $flowInfo .= "Step 4: Click 'Download PDF' button to download payslip as PDF\n";
            $flowInfo .= "Step 5: Payslips are generated monthly by admin\n\n";
            
            $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $flowInfo .= "📅 VIEW SHIFT SCHEDULE (STEP-BY-STEP):\n";
            $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $flowInfo .= "NAVIGATION PATH: Left Sidebar → 'My Timetable'\n\n";
            $flowInfo .= "Step 1: Click on 'My Timetable' in the left sidebar menu\n";
            $flowInfo .= "Step 2: View your assigned shifts in calendar format\n";
            $flowInfo .= "Step 3: See shift details including date, time, and location\n\n";
        } else {
            // Admin navigation structure
            $flowInfo .= "📱 ADMIN SIDEBAR NAVIGATION MENU (LEFT SIDEBAR):\n";
            $flowInfo .= "1. Dashboard - Main overview with pending requests\n";
            $flowInfo .= "2. Profile - View and edit admin information\n";
            $flowInfo .= "3. Staff (Dropdown Menu):\n";
            $flowInfo .= "   - Staff Management - View, edit, and manage all staff\n";
            $flowInfo .= "   - Staff Timetable - Assign and manage staff shifts\n";
            $flowInfo .= "   - Staff Leave Status - View and approve/reject leave applications\n";
            $flowInfo .= "4. Payroll (Dropdown Menu):\n";
            $flowInfo .= "   - Calculation - Calculate and generate payroll\n";
            $flowInfo .= "   - Staff Payslip - View and manage staff payslips\n\n";
            
            // Admin flows with exact navigation
            $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $flowInfo .= "👥 STAFF MANAGEMENT (STEP-BY-STEP):\n";
            $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $flowInfo .= "NAVIGATION PATH: Left Sidebar → 'Staff' (click to expand) → 'Staff Management'\n\n";
            $flowInfo .= "Step 1: Click on 'Staff' in the left sidebar menu (it will expand)\n";
            $flowInfo .= "Step 2: Click on 'Staff Management' under the Staff dropdown\n";
            $flowInfo .= "Step 3: View all staff in a table/list format\n";
            $flowInfo .= "Step 4: Click on a staff member to view details\n";
            $flowInfo .= "Step 5: Edit staff details, view leave balances, or delete staff\n";
            $flowInfo .= "Step 6: To manage schedules: Left Sidebar → 'Staff' → 'Staff Timetable'\n\n";
            
            $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $flowInfo .= "✅ LEAVE APPROVAL PROCESS (STEP-BY-STEP):\n";
            $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $flowInfo .= "NAVIGATION PATH: Left Sidebar → 'Staff' (click to expand) → 'Staff Leave Status'\n\n";
            $flowInfo .= "Step 1: Click on 'Staff' in the left sidebar menu (it will expand)\n";
            $flowInfo .= "Step 2: Click on 'Staff Leave Status' under the Staff dropdown\n";
            $flowInfo .= "Step 3: View all leave applications (pending, approved, rejected)\n";
            $flowInfo .= "Step 4: System auto-approves if all rules are met\n";
            $flowInfo .= "Step 5: Review applications that need manual approval\n";
            $flowInfo .= "Step 6: Click 'Approve' or 'Reject' button with reason\n";
            $flowInfo .= "Step 7: Staff will be notified of the decision\n\n";
            
            $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $flowInfo .= "⏱️ OVERTIME APPROVAL PROCESS (STEP-BY-STEP):\n";
            $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $flowInfo .= "NAVIGATION PATH: Left Sidebar → 'Dashboard'\n\n";
            $flowInfo .= "Step 1: Click on 'Dashboard' in the left sidebar menu\n";
            $flowInfo .= "Step 2: View pending overtime requests in the dashboard\n";
            $flowInfo .= "Step 3: Check department limits and weekly constraints\n";
            $flowInfo .= "Step 4: Click 'Approve' or 'Reject' button\n";
            $flowInfo .= "Step 5: Staff will be notified of the decision\n\n";
            
            $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $flowInfo .= "💰 PAYROLL GENERATION PROCESS (STEP-BY-STEP):\n";
            $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $flowInfo .= "NAVIGATION PATH: Left Sidebar → 'Payroll' (click to expand) → 'Calculation'\n\n";
            $flowInfo .= "Step 1: Click on 'Payroll' in the left sidebar menu (it will expand)\n";
            $flowInfo .= "Step 2: Click on 'Calculation' under the Payroll dropdown\n";
            $flowInfo .= "Step 3: Select month and year from the dropdown\n";
            $flowInfo .= "Step 4: System automatically calculates:\n";
            $flowInfo .= "   - Basic salary\n";
            $flowInfo .= "   - Commission (after 3 months)\n";
            $flowInfo .= "   - Marketing bonus\n";
            $flowInfo .= "   - Overtime pay (if claimed)\n";
            $flowInfo .= "   - Deductions\n";
            $flowInfo .= "Step 5: Review calculated amounts for each staff\n";
            $flowInfo .= "Step 6: Set bonuses if needed\n";
            $flowInfo .= "Step 7: Click 'Publish Payroll' button (marks as 'Paid')\n";
            $flowInfo .= "Step 8: Staff can view and download payslips at: Left Sidebar → 'Payroll' → 'Staff Payslip'\n\n";
            
            $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $flowInfo .= "📅 STAFF TIMETABLE MANAGEMENT (STEP-BY-STEP):\n";
            $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $flowInfo .= "NAVIGATION PATH: Left Sidebar → 'Staff' (click to expand) → 'Staff Timetable'\n\n";
            $flowInfo .= "Step 1: Click on 'Staff' in the left sidebar menu (it will expand)\n";
            $flowInfo .= "Step 2: Click on 'Staff Timetable' under the Staff dropdown\n";
            $flowInfo .= "Step 3: View calendar with all staff shifts\n";
            $flowInfo .= "Step 4: Add new shift: Click on a date, select staff, set time and location\n";
            $flowInfo .= "Step 5: Edit shift: Click on existing shift and modify details\n";
            $flowInfo .= "Step 6: Delete shift: Click on shift and delete\n";
            $flowInfo .= "Step 7: Use bulk upload for multiple shifts\n\n";
        }
        
        $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $flowInfo .= "IMPORTANT NAVIGATION NOTES FOR {$userRole}:\n";
        $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $flowInfo .= "- All navigation is done through the LEFT SIDEBAR MENU\n";
        
        if (!$isAdmin) {
            $flowInfo .= "- Dropdown menus (Overtime, Leave) need to be clicked to expand\n";
        } else {
            $flowInfo .= "- Dropdown menus (Staff, Payroll) need to be clicked to expand\n";
        }
        
        $flowInfo .= "- The active menu item is highlighted\n";
        $flowInfo .= "- Always provide the exact navigation path when answering questions\n";
        $flowInfo .= "- Use the format: 'Left Sidebar → [Menu] → [Submenu]' when describing navigation\n";
        $flowInfo .= "- ⚠️ ONLY reference the {$userRole} navigation menu shown above. Do NOT mention menus from the other role.\n\n";
        
        $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $flowInfo .= "When answering process questions, you MUST:\n";
        $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $flowInfo .= "1. Provide the exact navigation path (Left Sidebar → Menu → Submenu) from the {$userRole} menu above\n";
        $flowInfo .= "2. List all steps in order\n";
        $flowInfo .= "3. Reference ONLY the menu names shown in the {$userRole} navigation structure above\n";
        $flowInfo .= "4. Be specific about dropdown menus that need to be expanded\n";
        $flowInfo .= "5. Include all relevant rules and exceptions\n";
        $flowInfo .= "6. ⚠️ NEVER mention or reference navigation items that are NOT in the {$userRole} menu structure above\n";
        $flowInfo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        
        return $flowInfo;
    }

    /**
     * Check if message contains process-related keywords
     */
    private function containsProcessKeywords(string $message): bool
    {
        $processKeywords = [
            'apply', 'claim', 'submit', 'request', 'process', 'procedure',
            'leave', 'overtime', 'payslip', 'payroll', 'how', 'steps',
            'how to', 'how do', 'how can', 'instructions', 'guide', 'tutorial'
        ];
        
        foreach ($processKeywords as $keyword) {
            if (stripos($message, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if message is asking "how to" do something (process question, not data query)
     */
    private function isHowToQuestion(string $message): bool
    {
        // Normalize message to lowercase for consistent matching
        $message = strtolower(trim($message));
        
        $howToPatterns = [
            'how to', 'how do', 'how can', 'how should', 'how would',
            'what are the steps', 'what is the process', 'what is the procedure',
            'tell me how', 'show me how', 'explain how', 'guide me',
            'instructions', 'tutorial', 'walkthrough', 'step by step',
            'how do i', 'how can i', 'how should i', 'how would i'
        ];
        
        foreach ($howToPatterns as $pattern) {
            if (stripos($message, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Safely parse date - handles both string and Carbon instances
     */
    private function parseDate($date)
    {
        if ($date instanceof Carbon) {
            return $date;
        }
        if (is_string($date)) {
            return Carbon::parse($date);
        }
        return Carbon::now();
    }

    /**
     * Enhanced keyword detection for general system questions
     */
    private function detectGeneralQuestion(string $message): ?array
    {
        $message = strtolower(trim($message));
        
        // System information questions
        if ($this->matchesKeywords($message, ['what is elmsp', 'what is this system', 'tell me about elmsp', 'about elmsp', 'system information', 'system info'])) {
            return $this->handleSystemInfoQuery($message, Auth::user());
        }
        
        // Feature questions
        if ($this->matchesKeywords($message, ['what can i do', 'what features', 'available features', 'system features', 'what does this system do'])) {
            return $this->getSystemFeatures(Auth::user());
        }
        
        // Sidebar/Navigation questions
        if ($this->matchesKeywords($message, ['sidebar', 'navigation', 'menu', 'menus', 'available menu', 'what menus', 'list menu', 'show menu', 'navigation menu', 'left sidebar'])) {
            return $this->getNavigationMenu(Auth::user());
        }
        
        // Department questions
        if ($this->matchesKeywords($message, ['departments', 'what departments', 'list departments', 'department list'])) {
            return $this->getDepartmentsList();
        }
        
        // Leave type questions
        if ($this->matchesKeywords($message, ['leave types', 'types of leave', 'what leave types', 'available leave types'])) {
            return $this->getAllLeaveTypes(Auth::user());
        }
        
        // Overtime rate questions
        if ($this->matchesKeywords($message, ['overtime rate', 'ot rate', 'overtime pay', 'ot pay', 'overtime salary', 'how much overtime'])) {
            return $this->getOvertimeRates();
        }
        
        // Payroll component questions
        if ($this->matchesKeywords($message, ['payroll components', 'salary components', 'what is in payroll', 'payroll includes'])) {
            return $this->getPayrollComponents();
        }
        
        return null;
    }

    /**
     * Get system features
     */
    private function getSystemFeatures(User $user): array
    {
        $isAdmin = $user->isAdmin();
        $response = "🚀 **ELMSP System Features:**\n\n";
        
        if ($isAdmin) {
            $response .= "**Admin Features:**\n";
            $response .= "• Staff Management - Add, edit, and manage staff information\n";
            $response .= "• Leave Approval - Review and approve/reject leave applications\n";
            $response .= "• Overtime Approval - Review and approve overtime requests\n";
            $response .= "• OT Claim Management - Approve payroll or replacement leave claims\n";
            $response .= "• Payroll Generation - Create and manage monthly payroll\n";
            $response .= "• Staff Timetable - Assign and manage staff schedules\n";
            $response .= "• System Statistics - View comprehensive system analytics\n";
            $response .= "• Staff Leave Status - Monitor all staff leave applications\n\n";
        } else {
            $response .= "**Staff Features:**\n";
            $response .= "• Leave Application - Apply for various types of leave\n";
            $response .= "• Leave Balance - Check available leave days\n";
            $response .= "• Shift Schedule - View your weekly timetable\n";
            $response .= "• Overtime Application - Apply for overtime work\n";
            $response .= "• Overtime Claim - Claim OT hours for payroll or replacement leave\n";
            $response .= "• Payslip View - Access and download monthly payslips\n";
            $response .= "• Profile Management - Update your personal information\n\n";
        }
        
        $response .= "💡 Ask me specific questions to get detailed information!";
        
        return [
            'response' => $response,
            'type' => 'info'
        ];
    }

    /**
     * Get departments list
     */
    private function getDepartmentsList(): array
    {
        $departments = ['Manager', 'Supervisor', 'Cashier', 'Barista', 'Joki', 'Waiter', 'Kitchen'];
        
        $response = "🏢 **Departments in ELMSP:**\n\n";
        foreach ($departments as $dept) {
            $count = \App\Models\Staff::where('department', strtolower($dept))
                ->where('status', 'active')
                ->count();
            $response .= "• **{$dept}**: {$count} active staff\n";
        }
        
        return [
            'response' => $response,
            'type' => 'info'
        ];
    }

    /**
     * Get overtime rates
     */
    private function getOvertimeRates(): array
    {
        $response = "💰 **Overtime Rates:**\n\n";
        $response .= "• **Fulltime OT**: RM 12.26 per hour\n";
        $response .= "• **Public Holiday OT**: RM 21.68 per hour\n";
        $response .= "• **Public Holiday Work**: RM 15.38 per hour\n\n";
        $response .= "💡 These rates are used when you claim overtime for payroll.";
        
        return [
            'response' => $response,
            'type' => 'info'
        ];
    }

    /**
     * Get payroll components
     */
    private function getPayrollComponents(): array
    {
        $response = "💰 **Payroll Components:**\n\n";
        $response .= "**Earnings:**\n";
        $response .= "• Basic Salary - Your fixed monthly salary\n";
        $response .= "• Fixed Commission - After 3 months of service\n";
        $response .= "• Marketing Bonus - Set by admin\n";
        $response .= "• Public Holiday Pay - For working on public holidays\n";
        $response .= "• Overtime Pay - If you claimed OT for payroll\n\n";
        $response .= "**Deductions:**\n";
        $response .= "• Any applicable deductions (if any)\n\n";
        $response .= "**Net Salary** = Total Earnings - Deductions";
        
        return [
            'response' => $response,
            'type' => 'info'
        ];
    }

    /**
     * Get navigation menu structure
     */
    private function getNavigationMenu(User $user): array
    {
        $isAdmin = $user->isAdmin();
        
        if ($isAdmin) {
            $response = "📱 **Admin Navigation Menu (Left Sidebar):**\n\n";
            $response .= "1. **Dashboard** - Main overview with pending requests\n";
            $response .= "2. **Profile** - View and edit admin information\n";
            $response .= "3. **Staff** (Dropdown Menu - click to expand):\n";
            $response .= "   - Staff Management - View, edit, and manage all staff\n";
            $response .= "   - Staff Timetable - Assign and manage staff shifts\n";
            $response .= "   - Staff Leave Status - View and approve/reject leave applications\n";
            $response .= "4. **Payroll** (Dropdown Menu - click to expand):\n";
            $response .= "   - Calculation - Calculate and generate payroll\n";
            $response .= "   - Staff Payslip - View and manage staff payslips\n";
        } else {
            $response = "📱 **Staff Navigation Menu (Left Sidebar):**\n\n";
            $response .= "1. **Dashboard** - Main overview page\n";
            $response .= "2. **Profile** - View and edit personal information\n";
            $response .= "3. **My Timetable** - View your shift schedule\n";
            $response .= "4. **Overtime** (Dropdown Menu - click to expand):\n";
            $response .= "   - Claim - Convert approved OT hours to leave or payroll\n";
            $response .= "   - Apply - Submit new overtime application\n";
            $response .= "   - Status - View overtime application status\n";
            $response .= "5. **Leave** (Dropdown Menu - click to expand):\n";
            $response .= "   - Application - Submit new leave application\n";
            $response .= "   - Status - View leave application status\n";
            $response .= "6. **Payslip** - View and download monthly payslips\n";
        }
        
        $response .= "\n💡 Click on dropdown menus to expand and see submenu options.";
        
        return [
            'response' => $response,
            'type' => 'info'
        ];
    }

    /**
     * Check if message is asking about leave eligibility
     */
    private function isLeaveEligibilityQuestion(string $message): bool
    {
        // Check for question patterns about applying for leave
        $questionPatterns = [
            'can i apply',
            'can i take',
            'can i get',
            'can i request',
            'may i apply',
            'am i allowed',
            'is it possible',
            'is it allowed',
            'can i have leave',
            'can i use leave'
        ];
        
        // Check if message contains both a question pattern and "leave"
        $hasQuestionPattern = false;
        foreach ($questionPatterns as $pattern) {
            if (stripos($message, $pattern) !== false) {
                $hasQuestionPattern = true;
                break;
            }
        }
        
        // Must have both question pattern and "leave" keyword
        // Also check for variations like "apply for leave", "take leave", etc.
        if ($hasQuestionPattern) {
            $leaveKeywords = ['leave', 'vacation', 'time off', 'day off'];
            foreach ($leaveKeywords as $keyword) {
                if (stripos($message, $keyword) !== false) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Check leave eligibility based on user's question
     * Returns yes/no with specific reasons
     */
    private function checkLeaveEligibility(string $message, User $user): array
    {
        $staff = $user->staff;
        if (!$staff) {
            return [
                'response' => "❌ **No, you cannot apply for leave.**\n\nReason: Staff record not found. Please contact your administrator.",
                'type' => 'error'
            ];
        }

        $message = strtolower(trim($message));
        
        // Extract leave type from message if mentioned
        $leaveTypeName = $this->extractLeaveType($message);
        
        // Extract time period from message
        $isThisWeek = $this->matchesKeywords($message, ['this week', 'current week']);
        $isNextWeek = $this->matchesKeywords($message, ['next week', 'upcoming week']);
        $isToday = $this->matchesKeywords($message, ['today', 'now']);
        
        // If no specific dates mentioned, check general eligibility
        if (!$isThisWeek && !$isNextWeek && !$isToday) {
            return $this->getGeneralLeaveEligibility($staff, $leaveTypeName);
        }
        
        // Check specific time period
        if ($isThisWeek) {
            return $this->checkLeaveEligibilityForWeek($staff, Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek(), $leaveTypeName);
        } elseif ($isNextWeek) {
            return $this->checkLeaveEligibilityForWeek($staff, Carbon::now()->addWeek()->startOfWeek(), Carbon::now()->addWeek()->endOfWeek(), $leaveTypeName);
        } elseif ($isToday) {
            return $this->checkLeaveEligibilityForDate($staff, Carbon::today(), $leaveTypeName);
        }
        
        return $this->getGeneralLeaveEligibility($staff, $leaveTypeName);
    }

    /**
     * Get general leave eligibility without specific dates
     */
    private function getGeneralLeaveEligibility($staff, ?string $leaveTypeName): array
    {
        $reasons = [];
        $canApply = true;
        
        // Check leave balance
        if ($leaveTypeName) {
            $balance = LeaveBalance::where('staff_id', $staff->id)
                ->whereHas('leaveType', function($q) use ($leaveTypeName) {
                    $q->whereRaw('LOWER(type_name) = ?', [strtolower($leaveTypeName)]);
                })
                ->first();
            
            if (!$balance || $balance->remaining_days <= 0) {
                $canApply = false;
                $reasons[] = "Insufficient leave balance for " . ucfirst(str_replace('_', ' ', $leaveTypeName)) . " leave";
            }
        } else {
            // Check if has any leave balance
            $hasBalance = LeaveBalance::where('staff_id', $staff->id)
                ->where('remaining_days', '>', 0)
                ->exists();
            
            if (!$hasBalance) {
                $canApply = false;
                $reasons[] = "No available leave balance";
            }
        }
        
        // Check weekly leave entitlement
        $thisWeekStart = Carbon::now()->startOfWeek();
        $thisWeekEnd = Carbon::now()->endOfWeek();
        
        $existingLeaves = Leave::where('staff_id', $staff->id)
            ->where('status', 'approved')
            ->where(function($q) use ($thisWeekStart, $thisWeekEnd) {
                $q->whereBetween('start_date', [$thisWeekStart, $thisWeekEnd])
                  ->orWhereBetween('end_date', [$thisWeekStart, $thisWeekEnd])
                  ->orWhere(function($q2) use ($thisWeekStart, $thisWeekEnd) {
                      $q2->where('start_date', '<=', $thisWeekStart)
                         ->where('end_date', '>=', $thisWeekEnd);
                  });
            })
            ->get();
        
        $leaveDaysThisWeek = 0;
        foreach ($existingLeaves as $leave) {
            $leaveType = strtolower($leave->leaveType->type_name ?? '');
            if (in_array($leaveType, ['emergency', 'medical', 'hospitalization'])) {
                continue; // Exempt from weekly limit
            }
            
            for ($date = max($leave->start_date->copy(), $thisWeekStart); $date->lte(min($leave->end_date, $thisWeekEnd)); $date->addDay()) {
                $shift = Shift::where('staff_id', $staff->id)
                    ->whereDate('date', $date)
                    ->first();
                
                if (!$shift || !$shift->rest_day) {
                    $leaveDaysThisWeek++;
                }
            }
        }
        
        if ($leaveDaysThisWeek >= 2) {
            $canApply = false;
            $reasons[] = "You have already used {$leaveDaysThisWeek} leave day(s) this week (maximum 2 days per week allowed)";
        }
        
        // Check department constraints
        $department = $staff->department;
        $deptDailyLimit = Leave::$departmentConstraints[$department] ?? null;
        $deptWeeklyLimit = Leave::$departmentWeeklyConstraints[$department] ?? null;
        
        if ($deptDailyLimit) {
            // Check today's department quota
            $today = Carbon::today();
            $leavesToday = Leave::whereHas('staff', function($q) use ($department) {
                    $q->where('department', $department);
                })
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->get();
            
            $countToday = 0;
            foreach ($leavesToday as $leave) {
                $shift = Shift::where('staff_id', $leave->staff_id)
                    ->whereDate('date', $today)
                    ->first();
                if (!$shift || !$shift->rest_day) {
                    $countToday++;
                }
            }
            
            if ($countToday >= $deptDailyLimit) {
                $canApply = false;
                $reasons[] = "Department daily quota reached ({$deptDailyLimit} staff already on leave today)";
            }
        }
        
        if ($canApply) {
            $response = "✅ **Yes, you can apply for leave.**\n\n";
            if ($leaveDaysThisWeek > 0) {
                $response .= "⚠️ Note: You have {$leaveDaysThisWeek} leave day(s) already approved this week.\n";
                $response .= "You can still apply for up to " . (2 - $leaveDaysThisWeek) . " more day(s) this week.\n\n";
            }
            $response .= "**Requirements:**\n";
            $response .= "• Apply at least 3 days in advance (except Emergency, Medical, Hospitalization)\n";
            $response .= "• Maximum 2 leave days per week\n";
            $response .= "• Medical/Hospitalization leave requires Medical Certificate\n";
            $response .= "• Cannot apply on weekends (except Emergency, Medical with MC, Hospitalization)";
        } else {
            $response = "❌ **No, you cannot apply for leave at this time.**\n\n";
            $response .= "**Reasons:**\n";
            foreach ($reasons as $reason) {
                $response .= "• {$reason}\n";
            }
        }
        
        return [
            'response' => $response,
            'type' => $canApply ? 'success' : 'error'
        ];
    }

    /**
     * Check leave eligibility for a specific week
     */
    private function checkLeaveEligibilityForWeek($staff, Carbon $weekStart, Carbon $weekEnd, ?string $leaveTypeName): array
    {
        $reasons = [];
        $canApply = true;
        
        // Check weekly leave entitlement
        $existingLeaves = Leave::where('staff_id', $staff->id)
            ->where('status', 'approved')
            ->where(function($q) use ($weekStart, $weekEnd) {
                $q->whereBetween('start_date', [$weekStart, $weekEnd])
                  ->orWhereBetween('end_date', [$weekStart, $weekEnd])
                  ->orWhere(function($q2) use ($weekStart, $weekEnd) {
                      $q2->where('start_date', '<=', $weekStart)
                         ->where('end_date', '>=', $weekEnd);
                  });
            })
            ->get();
        
        $leaveDaysThisWeek = 0;
        foreach ($existingLeaves as $leave) {
            $leaveType = strtolower($leave->leaveType->type_name ?? '');
            if (in_array($leaveType, ['emergency', 'medical', 'hospitalization'])) {
                continue;
            }
            
            for ($date = max($leave->start_date->copy(), $weekStart); $date->lte(min($leave->end_date, $weekEnd)); $date->addDay()) {
                $shift = Shift::where('staff_id', $staff->id)
                    ->whereDate('date', $date)
                    ->first();
                
                if (!$shift || !$shift->rest_day) {
                    $leaveDaysThisWeek++;
                }
            }
        }
        
        if ($leaveDaysThisWeek >= 2) {
            $canApply = false;
            $reasons[] = "You have already used {$leaveDaysThisWeek} leave day(s) this week (maximum 2 days per week allowed)";
        }
        
        // Check department weekly constraints
        $department = $staff->department;
        $deptWeeklyLimit = Leave::$departmentWeeklyConstraints[$department] ?? null;
        
        if ($deptWeeklyLimit) {
            $leavesThisWeek = Leave::whereHas('staff', function($q) use ($department) {
                    $q->where('department', $department);
                })
                ->where('status', 'approved')
                ->where(function($q) use ($weekStart, $weekEnd) {
                    $q->whereBetween('start_date', [$weekStart, $weekEnd])
                      ->orWhereBetween('end_date', [$weekStart, $weekEnd])
                      ->orWhere(function($q2) use ($weekStart, $weekEnd) {
                          $q2->where('start_date', '<=', $weekStart)
                             ->where('end_date', '>=', $weekEnd);
                      });
                })
                ->get();
            
            $countThisWeek = 0;
            foreach ($leavesThisWeek as $leave) {
                $leaveType = strtolower($leave->leaveType->type_name ?? '');
                if (in_array($leaveType, ['emergency', 'medical', 'hospitalization'])) {
                    continue;
                }
                $countThisWeek++;
            }
            
            if ($countThisWeek >= $deptWeeklyLimit) {
                $canApply = false;
                $reasons[] = "Department weekly quota reached ({$deptWeeklyLimit} staff already on leave this week)";
            }
        }
        
        // Check leave balance
        if ($leaveTypeName) {
            $balance = LeaveBalance::where('staff_id', $staff->id)
                ->whereHas('leaveType', function($q) use ($leaveTypeName) {
                    $q->whereRaw('LOWER(type_name) = ?', [strtolower($leaveTypeName)]);
                })
                ->first();
            
            if (!$balance || $balance->remaining_days <= 0) {
                $canApply = false;
                $reasons[] = "Insufficient leave balance for " . ucfirst(str_replace('_', ' ', $leaveTypeName)) . " leave";
            }
        }
        
        $weekLabel = $weekStart->isCurrentWeek() ? 'this week' : 'next week';
        
        if ($canApply) {
            $response = "✅ **Yes, you can apply for leave for {$weekLabel}.**\n\n";
            if ($leaveDaysThisWeek > 0) {
                $response .= "⚠️ Note: You have {$leaveDaysThisWeek} leave day(s) already approved this week.\n";
                $response .= "You can still apply for up to " . (2 - $leaveDaysThisWeek) . " more day(s).\n\n";
            }
            $response .= "**Requirements:**\n";
            $response .= "• Apply at least 3 days in advance (except Emergency, Medical, Hospitalization)\n";
            $response .= "• Maximum 2 leave days per week\n";
            $response .= "• Medical/Hospitalization leave requires Medical Certificate";
        } else {
            $response = "❌ **No, you cannot apply for leave for {$weekLabel}.**\n\n";
            $response .= "**Reasons:**\n";
            foreach ($reasons as $reason) {
                $response .= "• {$reason}\n";
            }
        }
        
        return [
            'response' => $response,
            'type' => $canApply ? 'success' : 'error'
        ];
    }

    /**
     * Check leave eligibility for a specific date
     */
    private function checkLeaveEligibilityForDate($staff, Carbon $date, ?string $leaveTypeName): array
    {
        $reasons = [];
        $canApply = true;
        
        // Check if it's a weekend
        if ($date->isWeekend()) {
            $leaveType = $leaveTypeName ? strtolower($leaveTypeName) : '';
            if (!in_array($leaveType, ['emergency', 'medical', 'hospitalization'])) {
                $canApply = false;
                $reasons[] = "Normal leave is not permitted on weekends (Saturday/Sunday)";
            }
        }
        
        // Check if there's approved overtime on this date
        $hasOvertime = Overtime::where('staff_id', $staff->id)
            ->where('status', 'approved')
            ->whereDate('ot_date', $date)
            ->exists();
        
        if ($hasOvertime) {
            $leaveType = $leaveTypeName ? strtolower($leaveTypeName) : '';
            if (!in_array($leaveType, ['emergency', 'medical', 'hospitalization'])) {
                $canApply = false;
                $reasons[] = "You have approved overtime on {$date->format('M d, Y')} - cannot apply for leave on the same date";
            }
        }
        
        // Check department daily quota
        $department = $staff->department;
        $deptDailyLimit = Leave::$departmentConstraints[$department] ?? null;
        
        if ($deptDailyLimit) {
            $leavesToday = Leave::whereHas('staff', function($q) use ($department) {
                    $q->where('department', $department);
                })
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->get();
            
            $countToday = 0;
            foreach ($leavesToday as $leave) {
                $shift = Shift::where('staff_id', $leave->staff_id)
                    ->whereDate('date', $date)
                    ->first();
                if (!$shift || !$shift->rest_day) {
                    $countToday++;
                }
            }
            
            if ($countToday >= $deptDailyLimit) {
                $canApply = false;
                $reasons[] = "Department daily quota reached ({$deptDailyLimit} staff already on leave on {$date->format('M d, Y')})";
            }
        }
        
        // Check leave balance
        if ($leaveTypeName) {
            $balance = LeaveBalance::where('staff_id', $staff->id)
                ->whereHas('leaveType', function($q) use ($leaveTypeName) {
                    $q->whereRaw('LOWER(type_name) = ?', [strtolower($leaveTypeName)]);
                })
                ->first();
            
            if (!$balance || $balance->remaining_days <= 0) {
                $canApply = false;
                $reasons[] = "Insufficient leave balance for " . ucfirst(str_replace('_', ' ', $leaveTypeName)) . " leave";
            }
        }
        
        if ($canApply) {
            $response = "✅ **Yes, you can apply for leave on {$date->format('l, M d, Y')}.**\n\n";
            $response .= "**Requirements:**\n";
            $response .= "• Apply at least 3 days in advance (except Emergency, Medical, Hospitalization)\n";
            $response .= "• Medical/Hospitalization leave requires Medical Certificate";
        } else {
            $response = "❌ **No, you cannot apply for leave on {$date->format('l, M d, Y')}.**\n\n";
            $response .= "**Reasons:**\n";
            foreach ($reasons as $reason) {
                $response .= "• {$reason}\n";
            }
        }
        
        return [
            'response' => $response,
            'type' => $canApply ? 'success' : 'error'
        ];
    }
}

