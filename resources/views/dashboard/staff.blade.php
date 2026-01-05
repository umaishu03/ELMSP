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

// Approved overtime (summary and full list)
$staff = $user->staff;
$staffId = $staff ? $staff->id : null;
$approvedOT = $staffId ? Overtime::where('staff_id', $staffId)
    ->where('status', 'approved')
    ->orderBy('ot_date', 'desc')
    ->get() : collect();
$approvedCount = $approvedOT->count();

// Salary / OT claims (recent) - query payroll claims for this staff
$staffId = $staff ? $staff->id : null;
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
        
        // Load leave relationship for claims that have it
        $replacementLeaveClaims->load('leave');
    }
}
?>

<div class="mb-6">
    <h1 class="text-4xl font-extrabold text-purple-700">
        {{ $greeting }}, {{ $user->name }}!
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
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 md:px-6 py-3 md:py-4">
            <h2 class="text-base md:text-lg font-bold text-white">👤 User Information</h2>
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
        <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);" class="px-4 md:px-6 py-3 md:py-4 flex flex-col md:flex-row md:justify-between md:items-center gap-2">
            <div class="flex items-center space-x-2">
                <svg class="w-4 md:w-5 h-4 md:h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h2 class="text-sm md:text-lg font-bold text-white">Overtime Approved</h2>
            </div>
            <span class="bg-white text-orange-600 text-xs font-bold px-2.5 py-1 rounded-full w-fit" style="animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">
                 {{ $approvedCount }}
            </span>
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
        <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);" class="px-4 md:px-6 py-3 md:py-4 flex flex-col md:flex-row md:justify-between md:items-center gap-2">
            <div class="flex items-center space-x-2">
                <svg class="w-4 md:w-5 h-4 md:h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h2 class="text-sm md:text-lg font-bold text-white">Salary Claims</h2>
            </div>
            <span class="bg-white text-green-600 text-xs font-bold px-2.5 py-1 rounded-full w-fit" style="animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">
                {{ $salaryClaims->count() }}
            </span>
        </div>
        <div class="p-3 md:p-4">
            <div class="space-y-2">
                @forelse($salaryClaims->take(2) as $claim)
                @php
                    $amounts = $claim->calculatePayrollAmounts();
                    $totalPay = $amounts['total_pay'] ?? 0;
                    $isApproved = strtolower($claim->status ?? 'pending') === 'approved';
                @endphp
                <div class="flex items-center space-x-2 p-2 {{ $isApproved ? 'bg-green-50 border border-green-200' : 'bg-green-50' }} rounded hover:bg-green-100 transition cursor-pointer text-xs md:text-sm">
                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-semibold text-gray-800 truncate">{{ $claim->created_at->format('F Y') }} - RM {{ number_format($totalPay, 2) }}</p>
                            @if($isApproved)
                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-1.5 py-0.5 rounded">✓ Approved</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500">{{ ucfirst($claim->status ?? 'pending') }} • {{ $claim->updated_at->diffForHumans() }}</p>
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
        <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);" class="px-4 md:px-6 py-3 md:py-4 flex flex-col md:flex-row md:justify-between md:items-center gap-2">
            <div class="flex items-center space-x-2">
                <svg class="w-4 md:w-5 h-4 md:h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h2 class="text-sm md:text-lg font-bold text-white">Replacement Leave</h2>
            </div>
            <span class="bg-white text-purple-600 text-xs font-bold px-2.5 py-1 rounded-full w-fit" style="animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">
                {{ $replacementLeaveClaims->count() }}
            </span>
        </div>
        <div class="p-3 md:p-4">
            <div class="space-y-2">
                @forelse($replacementLeaveClaims->take(2) as $claim)
                @php
                    $leave = $claim->leave;
                    $replacementDays = $claim->replacement_days ?? 0;
                    $isApproved = strtolower($claim->status ?? 'pending') === 'approved';
                @endphp
                <div class="flex items-center space-x-2 p-2 {{ $isApproved ? 'bg-green-50 border border-green-200' : 'bg-purple-50' }} rounded hover:{{ $isApproved ? 'bg-green-100' : 'bg-purple-100' }} transition cursor-pointer text-xs md:text-sm">
                    <div class="w-1.5 h-1.5 {{ $isApproved ? 'bg-green-500' : 'bg-purple-500' }} rounded-full flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-semibold text-gray-800 truncate">
                                @if($leave)
                                    {{ $leave->start_date->format('M d') }} - {{ number_format($replacementDays, 1) }} days
                                @else
                                    {{ number_format($replacementDays, 1) }} days Replacement
                                @endif
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
                $isApproved = strtolower($claim->status ?? 'pending') === 'approved';
                $bgColor = $isApproved ? 'bg-green-50' : 'bg-green-50';
                $borderColor = $isApproved ? 'border-green-500' : 'border-green-300';
            @endphp
            <div class="p-6 {{ $bgColor }} border-l-4 {{ $borderColor }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            @php
                                $status = strtolower($claim->status ?? 'pending');
                                $statusLabel = $status === 'approved' ? '✓ Approved' : ($status === 'pending' ? 'Pending Review' : ucfirst($status));
                                $statusClass = $status === 'approved' ? 'bg-green-100 text-green-800' : ($status === 'pending' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800');
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
                $leave = $claim->leave;
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
                            @if($leave)
                                Replacement Leave - {{ $leave->start_date->format('F d, Y') }}
                            @else
                                Replacement Leave Claim
                            @endif
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
                        @if($leave)
                        <div class="bg-white p-3 rounded border border-purple-200 text-sm">
                            <p class="text-gray-700">
                                <span class="font-semibold">Leave Period:</span> 
                                {{ $leave->start_date->format('M d, Y') }} 
                                @if($leave->end_date && $leave->end_date != $leave->start_date)
                                    to {{ $leave->end_date->format('M d, Y') }}
                                @endif
                            </p>
                            @if($leave->reason)
                            <p class="text-gray-700 mt-2">
                                <span class="font-semibold">Reason:</span> {{ Illuminate\Support\Str::limit($leave->reason, 100) }}
                            </p>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="p-6 text-sm text-gray-600">No approved replacement leave claims found.</div>
            @endforelse
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
});

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
