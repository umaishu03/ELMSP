@extends('layouts.staff')

@section('title', 'Staff Dashboard')

@section('content')

<?php
// Placeholder for determining the time of day
$currentHour = date('H');
if ($currentHour < 12) {
    $greeting = 'Good Morning';
} elseif ($currentHour < 18) {
    $greeting = 'Good Afternoon';
} else {
    $greeting = 'Good Evening';
}

// Placeholder for notification status
$overtimeApproved = true; // Assume true for design demonstration
?>
<?php
use App\Models\Overtime;
use App\Models\OTClaim;
use App\Models\Leave;
use App\Models\LeaveBalance;
use Carbon\Carbon;

// Approved overtime (summary and full list)
$staff = $user->staff;
$staffId = $staff ? $staff->id : null;
$approvedOT = $staffId ? Overtime::where('staff_id', $staffId)
    ->where('status', 'approved')
    ->orderBy('ot_date', 'desc')
    ->get() : collect();
$approvedCount = $approvedOT->count();

// Salary / OT claims (recent) - query payroll claims for this staff
$salaryClaims = collect();
if ($staffId) {
    // Get all overtime IDs for this staff
    $overtimeIds = Overtime::where('staff_id', $staffId)->pluck('id')->toArray();
    
    if (!empty($overtimeIds)) {
        // Get all payroll claims and filter by checking if ot_ids contains any of this staff's overtime IDs
        $allPayrollClaims = OTClaim::where('claim_type', 'payroll')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Filter claims where ot_ids array contains any of this staff's overtime IDs
        $salaryClaims = $allPayrollClaims->filter(function($claim) use ($overtimeIds) {
            $claimOtIds = $claim->ot_ids ?? [];
            if (is_string($claimOtIds)) {
                $claimOtIds = json_decode($claimOtIds, true) ?? [];
            }
            return !empty(array_intersect($overtimeIds, $claimOtIds));
        })->values();
    }
}

// Replacement leave claims - query approved replacement leave claims for this staff
$replacementLeaveClaims = collect();
if ($staffId) {
    // Get all overtime IDs for this staff
    $overtimeIds = Overtime::where('staff_id', $staffId)->pluck('id')->toArray();
    
    if (!empty($overtimeIds)) {
        // Get all replacement leave claims and filter by checking if ot_ids contains any of this staff's overtime IDs
        $allReplacementClaims = OTClaim::where('claim_type', 'replacement_leave')
            ->where('status', 'approved')
            ->orderBy('updated_at', 'desc')
            ->get();
        
        // Filter claims where ot_ids array contains any of this staff's overtime IDs
        $replacementLeaveClaims = $allReplacementClaims->filter(function($claim) use ($overtimeIds) {
            $claimOtIds = $claim->ot_ids ?? [];
            if (is_string($claimOtIds)) {
                $claimOtIds = json_decode($claimOtIds, true) ?? [];
            }
            return !empty(array_intersect($overtimeIds, $claimOtIds));
        })->values();
    }
}

// ===== ANALYTICS DATA PREPARATION FOR CHARTS =====

// 1. Personal Leave Balance Data (Used vs Remaining by type)
$leaveBalanceData = [];
$leaveBalanceLabels = [];
if ($staffId) {
    // Get all leave types available for this staff
    $leavesByType = Leave::where('staff_id', $staffId)
        ->where('status', 'approved')
        ->with('leaveType')
        ->get()
        ->groupBy('leave_type_id');
    
    // Get LeaveBalance for comparison
    $leaveBalances = \App\Models\LeaveBalance::where('staff_id', $staffId)
        ->with('leaveType')
        ->get()
        ->keyBy('leave_type_id');
    
    // Merge both sources - prefer LeaveBalance but validate against Leave records
    foreach ($leavesByType as $typeId => $leaves) {
        $firstLeave = $leaves->first();
        if ($firstLeave->leaveType) {
            $typeName = $firstLeave->leaveType->type_name ?? 'Unknown';
            $leaveType = $firstLeave->leaveType;
            
            // Calculate used days from Leave records using total_days (not date difference)
            $usedDays = $leaves->sum(function($leave) {
                return floatval($leave->total_days ?? 0);
            });
            
            // Get balance data if available
            $balance = $leaveBalances->get($typeId);
            
            if ($balance) {
                // Use LeaveBalance data when available
                $total = floatval($balance->total_days ?? 0);
                $storedRemaining = floatval($balance->remaining_days ?? 0);
                $storedUsed = floatval($balance->used_days ?? 0);
                
                // If total is 0 or seems wrong, recalculate from stored values
                if ($total == 0 || $total < $storedUsed + $storedRemaining) {
                    $total = $storedUsed + $storedRemaining;
                }
                
                $remaining = $storedRemaining > 0 ? $storedRemaining : ($total - $storedUsed);
                $used = $storedUsed;
            } else {
                // Fallback: use Leave record calculations and leave type max_days
                $used = $usedDays;
                $maxDays = $leaveType->max_days ?? (\App\Models\Leave::$maxLeaves[strtolower($typeName)] ?? 0);
                $total = floatval($maxDays);
                
                // Calculate remaining days (max - used, but not less than 0)
                $remaining = max(0, $total - $used);
            }
            
            $leaveBalanceLabels[] = ucfirst($typeName);
            $leaveBalanceData[] = [
                'used' => $used,
                'remaining' => $remaining,
                'total' => $total > 0 ? $total : ($used + $remaining)
            ];
        }
    }
    
    // Also add LeaveBalance entries that don't have Leave records yet
    foreach ($leaveBalances as $typeId => $balance) {
        if (!$leavesByType->has($typeId) && $balance->leaveType) {
            $typeName = $balance->leaveType->type_name ?? 'Unknown';
            $used = floatval($balance->used_days ?? 0);
            $total = floatval($balance->total_days ?? 0);
            $remaining = floatval($balance->remaining_days ?? 0);
            
            if ($remaining == 0 && $total > 0) {
                $remaining = $total - $used;
            }
            
            $leaveBalanceLabels[] = ucfirst($typeName);
            $leaveBalanceData[] = [
                'used' => $used,
                'remaining' => $remaining,
                'total' => $total > 0 ? $total : ($used + $remaining)
            ];
        }
    }
}

// 2. Monthly Overtime Trend (Last 6 months)
$monthlyOTData = [];
$monthLabels = [];
if ($staffId) {
    $now = Carbon::now();
    for ($i = 5; $i >= 0; $i--) {
        $month = $now->copy()->subMonths($i);
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();
        
        $totalHours = Overtime::where('staff_id', $staffId)
            ->whereBetween('ot_date', [$monthStart, $monthEnd])
            ->where('status', 'approved')
            ->sum('hours');
        
        $monthlyOTData[] = $totalHours ?? 0;
        $monthLabels[] = $month->format('M');
    }
}

// 3. Leave Application Status (Personal)
$leaveStatus = [];
if ($staffId) {
    $leaveStatus = [
        'approved' => Leave::where('staff_id', $staffId)->where('status', 'approved')->count() ?? 0,
        'rejected' => Leave::where('staff_id', $staffId)->where('status', 'rejected')->count() ?? 0
    ];
}

// 4. OT Claims Status
$otClaimsStatus = [
    'approved' => $salaryClaims->where('status', 'approved')->count() ?? 0,
    'pending' => $salaryClaims->where('status', 'pending')->count() ?? 0,
    'rejected' => $salaryClaims->where('status', 'rejected')->count() ?? 0
];
?>

<div class="mb-6">
    <h1 class="text-4xl font-extrabold text-purple-700">
        {{ $greeting }}, {{ explode(' ', $user->name)[0] }}!
    </h1>
    <p class="text-gray-600 mt-1">Welcome back to your Staff Dashboard. Here are your latest updates.</p>
</div>

{{-- Toast Notification Container --}}
<div id="toast-container" class="fixed top-20 right-4 z-[100] space-y-2" style="max-width: 400px;"></div>

<!-- Small responsive tweak: force single-column cards when viewport is narrow (split view) -->
<style>
@media (max-width: 1100px) {
    .notification-cards {
        grid-template-columns: 1fr !important;
    }
}
</style>

<!-- Notification Cards Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6 mb-6 notification-cards">
    <!-- User Information Card -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden min-w-0">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);" class="px-4 md:px-6 py-3 md:py-4">
            <h2 class="text-base md:text-lg font-bold text-white">User Information</h2>
        </div>
        <div class="p-3 md:p-4">
            <div class="space-y-1 md:space-y-2">
                <div>
                    <span class="text-xs text-gray-600">Username:</span>
                    <span class="font-semibold text-gray-800 text-xs md:text-sm block">{{ $user->name }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-600">Employee ID:</span>
                    <span class="font-semibold text-gray-800 text-xs md:text-sm block">{{ $user->staff->employee_id ?? 'Not assigned' }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-600">Status:</span>
                    <span class="font-semibold text-green-600 text-xs md:text-sm block">Active</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Overtime Request Approved Notification -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden min-w-0">
        <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);" class="px-4 md:px-6 py-3 md:py-4">
            <div class="flex items-center space-x-2">
                <svg class="w-4 md:w-5 h-4 md:h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h2 class="text-sm md:text-lg font-bold text-white flex items-center gap-2">
                    <span>Overtime Approved</span>
                    <span class="bg-white text-orange-600 text-xs font-bold px-2.5 py-1 rounded-full" style="animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">
                        {{ $approvedCount }}
                    </span>
                </h2>
            </div>
        </div>
        <div class="p-3 md:p-4">
            <div class="space-y-2">
                @forelse($approvedOT->take(2) as $ot)
                <div class="flex items-center space-x-2 p-2 bg-green-50 rounded hover:bg-green-100 transition cursor-pointer text-xs md:text-sm">
                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-semibold text-gray-800 truncate">{{ $ot->ot_date->format('M d') }} - {{ number_format($ot->hours,1) }}hrs</p>
                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-1.5 py-0.5 rounded">✓ Approved</span>
                        </div>
                        <p class="text-xs text-gray-500">Approved {{ $ot->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="text-xs text-gray-600">No approved overtime yet</div>
                @endforelse
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <button onclick="showOvertimeApprovedSection()" class="block w-full text-center text-xs font-semibold text-orange-600 hover:text-orange-700 transition">
                        View Status →
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Salary Claims Status Notification -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden min-w-0">
        <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);" class="px-4 md:px-6 py-3 md:py-4">
            <div class="flex items-center space-x-2">
                <svg class="w-4 md:w-5 h-4 md:h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h2 class="text-sm md:text-lg font-bold text-white flex items-center gap-2">
                    <span>Salary Claims</span>
                    <span class="bg-white text-green-600 text-xs font-bold px-2.5 py-1 rounded-full" style="animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">
                        {{ $salaryClaims->count() }}
                    </span>
                </h2>
            </div>
        </div>
        <div class="p-3 md:p-4">
            <div class="space-y-2">
                @forelse($salaryClaims->take(2) as $claim)
                @php
                    $amounts = $claim->calculatePayrollAmounts();
                    $totalPay = $amounts['total_pay'] ?? 0;
                    $status = strtolower($claim->status ?? 'pending');
                    $isApproved = $status === 'approved';
                    $isRejected = $status === 'rejected';
                @endphp
                <div class="flex items-center space-x-2 p-2 {{ $isApproved ? 'bg-green-50 border border-green-200' : ($isRejected ? 'bg-red-50 border border-red-200' : 'bg-yellow-50 border border-yellow-200') }} rounded {{ $isApproved ? 'hover:bg-green-100' : ($isRejected ? 'hover:bg-red-100' : 'hover:bg-yellow-100') }} transition cursor-pointer text-xs md:text-sm">
                    <div class="w-1.5 h-1.5 {{ $isApproved ? 'bg-green-500' : ($isRejected ? 'bg-red-500' : 'bg-yellow-500') }} rounded-full flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-semibold text-gray-800 truncate">{{ $claim->created_at->format('F Y') }} - RM {{ number_format($totalPay, 2) }}</p>
                            @if($isApproved)
                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-1.5 py-0.5 rounded">✓ Approved</span>
                            @elseif($isRejected)
                            <span class="bg-red-100 text-red-800 text-xs font-semibold px-1.5 py-0.5 rounded">✗ Rejected</span>
                            @endif
                        </div>
                        <p class="text-xs {{ $isRejected ? 'text-red-600' : 'text-gray-500' }}">{{ ucfirst($claim->status ?? 'pending') }} • {{ $claim->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="text-xs text-gray-600">No salary/OT claims found</div>
                @endforelse
                <div class="h-3"></div>
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <button onclick="showSalaryClaimsSection()" class="block w-full text-center text-xs font-semibold text-green-600 hover:text-green-700 transition">
                        View Details →
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Replacement Leave Claims Approved Notification -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden min-w-0">
        <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);" class="px-4 md:px-6 py-3 md:py-4">
            <div class="flex items-center space-x-2">
                <svg class="w-4 md:w-5 h-4 md:h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h2 class="text-sm md:text-lg font-bold text-white flex items-center gap-2">
                    <span>Replacement Leave</span>
                    <span class="bg-white text-purple-600 text-xs font-bold px-2.5 py-1 rounded-full" style="animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">
                        {{ $replacementLeaveClaims->count() }}
                    </span>
                </h2>
            </div>
        </div>
        <div class="p-3 md:p-4">
            <div class="space-y-2">
                @forelse($replacementLeaveClaims->take(2) as $claim)
                @php
                    $replacementDays = $claim->replacement_days ?? 0;
                    $isApproved = strtolower($claim->status ?? 'pending') === 'approved';
                @endphp
                <div class="flex items-center space-x-2 p-2 {{ $isApproved ? 'bg-green-50 border border-green-200' : 'bg-purple-50' }} rounded hover:{{ $isApproved ? 'bg-green-100' : 'bg-purple-100' }} transition cursor-pointer text-xs md:text-sm">
                    <div class="w-1.5 h-1.5 {{ $isApproved ? 'bg-green-500' : 'bg-purple-500' }} rounded-full flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-semibold text-gray-800 truncate">
                                {{ number_format($replacementDays, 1) }} days Replacement
                            </p>
                            @if($isApproved)
                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-1.5 py-0.5 rounded">✓ Approved</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500">Approved {{ $claim->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="text-xs text-gray-600">No replacement leave claims approved</div>
                @endforelse
                <div class="h-3"></div>
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <button onclick="showReplacementLeaveSection()" class="block w-full text-center text-xs font-semibold text-purple-600 hover:text-purple-700 transition">
                        View Details →
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approved Overtime Section (Hidden by default) -->
<div class="mt-8 hidden" id="overtime-approved-section">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);" class="px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h2 class="text-xl font-bold text-white">Approved Overtime Requests</h2>
            </div>
            <button onclick="hideOvertimeApprovedSection()" class="text-white hover:text-orange-100 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div class="divide-y divide-gray-200">
            @forelse($approvedOT as $otItem)
            <div class="p-6 bg-green-50 border-l-4 border-green-500">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-0.5 rounded">✓ Approved</span>
                            <span class="text-xs text-gray-500">{{ $otItem->updated_at->diffForHumans() }}</span>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-2">Overtime Request - {{ $otItem->ot_date->format('F d, Y') }}</h3>
                        <div class="grid grid-cols-2 gap-4 mb-3">
                            <div>
                                <p class="text-sm text-gray-600">Duration:</p>
                                <p class="text-sm font-semibold text-gray-800">{{ number_format($otItem->hours ?? 0, 1) }} hours</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Approved By:</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $otItem->approved_by ?? 'Admin' }}</p>
                            </div>
                        </div>
                        <div class="bg-white p-3 rounded border border-green-200 text-sm">
                            <p class="text-gray-700"><span class="font-semibold">Reason:</span> {{ $otItem->reason ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-6 text-sm text-gray-600">No approved overtime records to show.</div>
            @endforelse
        </div>
    </div>
</div>

<!-- Salary Claims Section (Hidden by default) -->
<div class="mt-8 hidden" id="salary-claims-section">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);" class="px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h2 class="text-xl font-bold text-white">Overtime Salary Claims Status</h2>
            </div>
            <button onclick="hideSalaryClaimsSection()" class="text-white hover:text-green-100 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div class="divide-y divide-gray-200">
            @forelse($salaryClaims as $claim)
            @php
                $status = strtolower($claim->status ?? 'pending');
                $isApproved = $status === 'approved';
                $isRejected = $status === 'rejected';
                $bgColor = $isApproved ? 'bg-green-50' : ($isRejected ? 'bg-red-50' : 'bg-yellow-50');
                $borderColor = $isApproved ? 'border-green-500' : ($isRejected ? 'border-red-500' : 'border-yellow-300');
            @endphp
            <div class="p-6 {{ $bgColor }} border-l-4 {{ $borderColor }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            @php
                                $status = strtolower($claim->status ?? 'pending');
                                $statusLabel = $status === 'approved' ? '✓ Approved' : ($status === 'pending' ? 'Pending Review' : ucfirst($status));
                                $statusClass = $status === 'approved' ? 'bg-green-100 text-green-800' : ($status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                                $payrollAmounts = $claim->calculatePayrollAmounts();
                                $totalHours = ($payrollAmounts['fulltime_hours'] ?? 0) + ($payrollAmounts['public_holiday_hours'] ?? 0);
                                $totalPay = $payrollAmounts['total_pay'] ?? 0;
                            @endphp
                            <span class="{{ $statusClass }} text-xs font-semibold px-2 py-0.5 rounded">{{ $statusLabel }}</span>
                            <span class="text-xs text-gray-500">{{ $claim->updated_at->diffForHumans() }}</span>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-2">{{ $claim->created_at->format('F Y') }} - Overtime Salary Claim</h3>
                        <div class="grid grid-cols-3 gap-4 mb-3">
                            <div>
                                <p class="text-sm text-gray-600">Total Hours:</p>
                                <p class="text-lg font-bold text-gray-800">{{ number_format($totalHours, 1) }} hours</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Breakdown:</p>
                                <p class="text-lg font-bold text-gray-800">
                                    @if($payrollAmounts['fulltime_hours'] > 0)
                                        {{ number_format($payrollAmounts['fulltime_hours'], 1) }}hrs @ RM12.26
                                    @endif
                                    @if($payrollAmounts['fulltime_hours'] > 0 && $payrollAmounts['public_holiday_hours'] > 0)
                                        <br/>
                                    @endif
                                    @if($payrollAmounts['public_holiday_hours'] > 0)
                                        {{ number_format($payrollAmounts['public_holiday_hours'], 1) }}hrs @ RM21.68
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Total Amount:</p>
                                <p class="text-lg font-bold text-{{ $status === 'approved' ? 'green' : ($status === 'pending' ? 'green' : 'red') }}-600">RM {{ number_format($totalPay, 2) }}</p>
                            </div>
                        </div>
                        <div class="bg-white p-3 rounded border border-gray-200 text-sm">
                            <p class="text-gray-700">
                                <span class="font-semibold">Fulltime OT:</span> {{ number_format($payrollAmounts['fulltime_hours'] ?? 0, 1) }} hrs × RM 12.26 = RM {{ number_format($payrollAmounts['fulltime_pay'] ?? 0, 2) }}
                            </p>
                            @if(($payrollAmounts['public_holiday_hours'] ?? 0) > 0)
                            <p class="text-gray-700 mt-2">
                                <span class="font-semibold">Public Holiday OT:</span> {{ number_format($payrollAmounts['public_holiday_hours'] ?? 0, 1) }} hrs × RM 21.68 = RM {{ number_format($payrollAmounts['public_holiday_pay'] ?? 0, 2) }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-6 text-sm text-gray-600">No salary/OT claims found.</div>
            @endforelse
        </div>
    </div>
</div>

<!-- Replacement Leave Claims Section (Hidden by default) -->
<div class="mt-8 hidden" id="replacement-leave-section">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);" class="px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h2 class="text-xl font-bold text-white">Approved Replacement Leave Claims</h2>
            </div>
            <button onclick="hideReplacementLeaveSection()" class="text-white hover:text-purple-100 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div class="divide-y divide-gray-200">
            @forelse($replacementLeaveClaims as $claim)
            @php
                $replacementDays = $claim->replacement_days ?? 0;
                $totalHours = ($claim->fulltime_hours ?? 0) + ($claim->public_holiday_hours ?? 0);
            @endphp
            <div class="p-6 bg-purple-50 border-l-4 border-purple-500">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-0.5 rounded">✓ Approved</span>
                            <span class="text-xs text-gray-500">{{ $claim->updated_at->diffForHumans() }}</span>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-2">
                            Replacement Leave Claim
                        </h3>
                        <div class="grid grid-cols-3 gap-4 mb-3">
                            <div>
                                <p class="text-sm text-gray-600">Replacement Days:</p>
                                <p class="text-lg font-bold text-gray-800">{{ number_format($replacementDays, 1) }} days</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Total OT Hours:</p>
                                <p class="text-lg font-bold text-gray-800">{{ number_format($totalHours, 1) }} hours</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Breakdown:</p>
                                <p class="text-sm font-bold text-gray-800">
                                    @if(($claim->fulltime_hours ?? 0) > 0)
                                        {{ number_format($claim->fulltime_hours, 1) }}hrs Fulltime
                                    @endif
                                    @if(($claim->fulltime_hours ?? 0) > 0 && ($claim->public_holiday_hours ?? 0) > 0)
                                        <br/>
                                    @endif
                                    @if(($claim->public_holiday_hours ?? 0) > 0)
                                        {{ number_format($claim->public_holiday_hours, 1) }}hrs Public Holiday
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-6 text-sm text-gray-600">No approved replacement leave claims found.</div>
            @endforelse
        </div>
    </div>
</div>

<!-- Analytics & Charts Section -->
<div class="mt-12">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Your Analytics</h2>
    
    <!-- Leave Analytics -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
        <div class="bg-gray-100 px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Leave Insights
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Leave Balance Chart -->
                <div class="bg-white rounded-lg p-6 border border-gray-200">
                    <h4 class="text-md font-bold text-gray-800 mb-4">Leave Balance by Type</h4>
                    <p class="text-sm text-gray-600 mb-3">Used vs Remaining leave days</p>
                    <div style="height: 280px;">
                        <canvas id="leaveBalanceChart"></canvas>
                    </div>
                </div>

                <!-- Leave Application Status -->
                <div class="bg-white rounded-lg p-6 border border-gray-200">
                    <h4 class="text-md font-bold text-gray-800 mb-4">Leave Request Status</h4>
                    <p class="text-sm text-gray-600 mb-3">Overview of your leave applications</p>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <p class="text-xs text-gray-600 mb-1">Approved</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $leaveStatus['approved'] ?? 0 }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <p class="text-xs text-gray-600 mb-1">Rejected</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $leaveStatus['rejected'] ?? 0 }}</p>
                        </div>
                    </div>
                    <div style="height: 200px;">
                        <canvas id="leaveStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overtime Analytics -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
        <div class="bg-gray-100 px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Overtime Insights
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Monthly OT Trend -->
                <div class="bg-white rounded-lg p-6 border border-gray-200">
                    <h4 class="text-md font-bold text-gray-800 mb-4">Monthly Overtime Trend</h4>
                    <p class="text-sm text-gray-600 mb-3">Your overtime hours over the last 6 months</p>
                    <div style="height: 280px;">
                        <canvas id="monthlyOTChart"></canvas>
                    </div>
                </div>

                <!-- OT Claims Status -->
                <div class="bg-white rounded-lg p-6 border border-gray-200">
                    <h4 class="text-md font-bold text-gray-800 mb-4">OT Claims Status</h4>
                    <p class="text-sm text-gray-600 mb-3">Your salary/OT claim statistics</p>
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <p class="text-xs text-gray-600 mb-1">Approved</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $otClaimsStatus['approved'] ?? 0 }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <p class="text-xs text-gray-600 mb-1">Pending</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $otClaimsStatus['pending'] ?? 0 }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <p class="text-xs text-gray-600 mb-1">Rejected</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $otClaimsStatus['rejected'] ?? 0 }}</p>
                        </div>
                    </div>
                    <div style="height: 200px;">
                        <canvas id="otClaimsStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript Toggle Functions -->
<script>
    function showOvertimeApprovedSection() {
        document.getElementById('overtime-approved-section').classList.remove('hidden');
        window.scrollTo({ top: document.getElementById('overtime-approved-section').offsetTop, behavior: 'smooth' });
    }

    function hideOvertimeApprovedSection() {
        document.getElementById('overtime-approved-section').classList.add('hidden');
    }

    function showSalaryClaimsSection() {
        document.getElementById('salary-claims-section').classList.remove('hidden');
        window.scrollTo({ top: document.getElementById('salary-claims-section').offsetTop, behavior: 'smooth' });
    }

    function hideSalaryClaimsSection() {
        document.getElementById('salary-claims-section').classList.add('hidden');
    }

    function showReplacementLeaveSection() {
        document.getElementById('replacement-leave-section').classList.remove('hidden');
        window.scrollTo({ top: document.getElementById('replacement-leave-section').offsetTop, behavior: 'smooth' });
    }

    function hideReplacementLeaveSection() {
        document.getElementById('replacement-leave-section').classList.add('hidden');
    }

// Toast Notification System
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
    const iconColor = type === 'success' ? 'text-green-500' : 'text-red-500';
    const icon = type === 'success' 
        ? '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>'
        : '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>';
    
    // Check if message starts with "Welcome" to show a simpler format
    const isWelcomeMessage = message.toLowerCase().startsWith('welcome');
    
    toast.className = `${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 animate-slide-in-right transform transition-all duration-300`;
    toast.innerHTML = `
        <div class="flex-shrink-0 ${iconColor} bg-white rounded-full p-1">
            ${icon}
        </div>
        <div class="flex-1">
            ${isWelcomeMessage ? '' : `<p class="font-semibold text-sm">${type === 'success' ? 'Success!' : 'Error!'}</p>`}
            <p class="text-sm ${isWelcomeMessage ? 'font-semibold' : 'opacity-90'}">${message}</p>
        </div>
        <button onclick="this.parentElement.remove()" class="flex-shrink-0 hover:opacity-75 transition">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </button>
    `;
    
    container.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

// Check for flash messages on page load
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        showToast('{{ session('success') }}', 'success');
    @endif
    
    @if(session('error'))
        showToast('{{ session('error') }}', 'error');
    @endif

    // Initialize charts
    initLeaveBalanceChart();
    initLeaveStatusChart();
    initMonthlyOTChart();
    initOTClaimsStatusChart();
});

// ==================== CHART.JS INITIALIZATION ====================

// Color Palette - Minimal, professional colors
const chartColors = {
    primary: '#3b82f6',
    success: '#10b981',
    warning: '#f59e0b',
    danger: '#ef4444',
    info: '#06b6d4',
    secondary: '#8b5cf6',
    light: '#f3f4f6'
};

// Initialize Leave Balance Chart (Stacked Bar)
function initLeaveBalanceChart() {
    const ctx = document.getElementById('leaveBalanceChart');
    if (!ctx) return;
    
    const leaveBalanceData = @json($leaveBalanceData ?? []);
    const leaveBalanceLabels = @json($leaveBalanceLabels ?? []);
    
    console.log('Leave Balance Chart Data:', leaveBalanceData);
    console.log('Leave Balance Chart Labels:', leaveBalanceLabels);
    
    if (leaveBalanceData.length === 0) {
        ctx.parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500"><p>No leave balance data available</p></div>';
        return;
    }
    
    const usedData = leaveBalanceData.map(d => d.used);
    const remainingData = leaveBalanceData.map(d => d.remaining);
    
    console.log('Used Data:', usedData);
    console.log('Remaining Data:', remainingData);
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: leaveBalanceLabels,
            datasets: [
                {
                    label: 'Used Days',
                    data: usedData,
                    backgroundColor: chartColors.danger,
                    borderRadius: 6,
                    borderSkipped: false
                },
                {
                    label: 'Remaining Days',
                    data: remainingData,
                    backgroundColor: chartColors.success,
                    borderRadius: 6,
                    borderSkipped: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: true,
                    labels: { font: { size: 12 }, padding: 15 }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.x + ' days';
                        }
                    }
                }
            },
            scales: {
                x: {
                    stacked: true,
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                },
                y: {
                    stacked: true,
                    grid: { display: false }
                }
            }
        }
    });
}

// Initialize Leave Status Chart (Doughnut)
function initLeaveStatusChart() {
    const ctx = document.getElementById('leaveStatusChart');
    if (!ctx) return;
    
    const leaveStatus = @json($leaveStatus ?? ['approved' => 0, 'rejected' => 0]);
    const total = leaveStatus.approved + leaveStatus.rejected;
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Approved', 'Rejected'],
            datasets: [{
                data: [leaveStatus.approved, leaveStatus.rejected],
                backgroundColor: [
                    chartColors.success,
                    chartColors.danger
                ],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: { font: { size: 11 }, padding: 12 }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed;
                        }
                    }
                }
            }
        }
    });
}

// Initialize Monthly OT Trend Chart (Line)
function initMonthlyOTChart() {
    const ctx = document.getElementById('monthlyOTChart');
    if (!ctx) return;
    
    const monthlyOTData = @json($monthlyOTData ?? []);
    const monthLabels = @json($monthLabels ?? []);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'Overtime Hours',
                data: monthlyOTData,
                borderColor: chartColors.warning,
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: chartColors.warning,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    labels: { font: { size: 12 }, padding: 15 }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y + ' hrs';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
}

// Initialize OT Claims Status Chart (Doughnut)
function initOTClaimsStatusChart() {
    const ctx = document.getElementById('otClaimsStatusChart');
    if (!ctx) return;
    
    const otClaimsStatus = @json($otClaimsStatus ?? ['approved' => 0, 'pending' => 0, 'rejected' => 0]);
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Approved', 'Pending', 'Rejected'],
            datasets: [{
                data: [otClaimsStatus.approved, otClaimsStatus.pending, otClaimsStatus.rejected],
                backgroundColor: [
                    chartColors.success,
                    chartColors.warning,
                    chartColors.danger
                ],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: { font: { size: 11 }, padding: 12 }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed;
                        }
                    }
                }
            }
        }
    });
}

// Add CSS animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slide-in-right {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    .animate-slide-in-right {
        animation: slide-in-right 0.3s ease-out;
    }
`;
document.head.appendChild(style);
</script>

@endsection
