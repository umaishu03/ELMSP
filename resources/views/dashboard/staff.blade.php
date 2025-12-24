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
$leaveTimetableUpdated = true; // Assume true for design demonstration
?>
<?php
use App\Models\Overtime;
use App\Models\Leave;
use App\Models\OTClaim;

// Approved overtime (summary and full list)
$staff = $user->staff;
$staffId = $staff ? $staff->id : null;
$approvedOT = $staffId ? Overtime::where('staff_id', $staffId)
    ->where('status', 'approved')
    ->orderBy('ot_date', 'desc')
    ->get() : collect();
$approvedCount = $approvedOT->count();

// Salary / OT claims (recent) - query through payroll relation to get claims for this staff
$staffId = $staff ? $staff->id : null;
$salaryClaims = collect();
if ($staffId) {
    $salaryClaims = OTClaim::whereHas('payroll', function($q) use ($staffId) {
            $q->whereHas('user.staff', function($sq) use ($staffId) {
                $sq->where('staff.id', $staffId);
            });
        })
        ->orWhereHas('overtime', function($q) use ($staffId) {
            $q->where('staff_id', $staffId);
        })
        ->orWhereHas('leave', function($q) use ($staffId) {
            $q->where('staff_id', $staffId);
        })
        ->orderBy('created_at', 'desc')
        ->take(2)
        ->get();
}

// Replacement leave schedule for this user
$staff = $user->staff;
$staffId = $staff ? $staff->id : null;
$replacementLeaves = $staffId ? Leave::where('staff_id', $staffId)
    ->whereHas('leaveType', function($q){ $q->where('type_name', 'replacement'); })
    ->orderBy('start_date', 'desc')
    ->get() : collect();
?>

<div class="mb-6">
    <h1 class="text-4xl font-extrabold text-purple-700">
        {{ $greeting }}, {{ $user->name }}!
    </h1>
    <p class="text-gray-600 mt-1">Welcome back to your Staff Dashboard. Here are your latest updates.</p>
</div>

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
        <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);" class="px-4 md:px-6 py-3 md:py-4 flex flex-col md:flex-row md:justify-between md:items-center gap-2">
            <div class="flex items-center space-x-2">
                <svg class="w-4 md:w-5 h-4 md:h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h2 class="text-sm md:text-lg font-bold text-white">Overtime Approved</h2>
            </div>
            <span class="bg-white text-green-600 text-xs font-bold px-2.5 py-1 rounded-full w-fit" style="animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">
                 {{ $approvedCount }}
            </span>
        </div>
        <div class="p-3 md:p-4">
            <div class="space-y-2">
                @forelse($approvedOT as $ot)
                <div class="flex items-center space-x-2 p-2 bg-green-50 rounded hover:bg-green-100 transition cursor-pointer text-xs md:text-sm">
                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 truncate">{{ $ot->ot_date->format('M d') }} - {{ number_format($ot->hours,1) }}hrs Approved</p>
                        <p class="text-xs text-gray-500">Approved {{ $ot->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="text-xs text-gray-600">No approved overtime yet</div>
                @endforelse
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <button onclick="showOvertimeApprovedSection()" class="block w-full text-center text-xs font-semibold text-green-600 hover:text-green-700 transition">
                        View Status →
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Salary Claims Status Notification -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden min-w-0">
        <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);" class="px-4 md:px-6 py-3 md:py-4 flex flex-col md:flex-row md:justify-between md:items-center gap-2">
            <div class="flex items-center space-x-2">
                <svg class="w-4 md:w-5 h-4 md:h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h2 class="text-sm md:text-lg font-bold text-white">Salary Claims</h2>
            </div>
            <span class="bg-white text-orange-600 text-xs font-bold px-2.5 py-1 rounded-full w-fit" style="animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">
                {{ $salaryClaims->count() }}
            </span>
        </div>
        <div class="p-3 md:p-4">
            <div class="space-y-2">
                @forelse($salaryClaims as $claim)
                <div class="flex items-center space-x-2 p-2 bg-orange-50 rounded hover:bg-orange-100 transition cursor-pointer text-xs md:text-sm">
                    <div class="w-1.5 h-1.5 bg-orange-500 rounded-full flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 truncate">{{ $claim->month_label ?? ($claim->created_at->format('F Y')) }} - RM {{ number_format($claim->amount ?? ($claim->hours * ($claim->rate ?? 25)), 2) }}</p>
                        <p class="text-xs text-gray-500">{{ ucfirst($claim->status ?? 'pending') }}</p>
                    </div>
                </div>
                @empty
                <div class="text-xs text-gray-600">No salary/OT claims found</div>
                @endforelse
                <div class="h-3"></div>
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <button onclick="showSalaryClaimsSection()" class="block w-full text-center text-xs font-semibold text-orange-600 hover:text-orange-700 transition">
                        View Details →
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Replacement Leave Updates Notification -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden min-w-0">
        <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);" class="px-4 md:px-6 py-3 md:py-4 flex flex-col md:flex-row md:justify-between md:items-center gap-2">
            <div class="flex items-center space-x-2">
                <svg class="w-4 md:w-5 h-4 md:h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h2 class="text-sm md:text-lg font-bold text-white">Timetable Updates</h2>
            </div>
            <span class="bg-white text-purple-600 text-xs font-bold px-2.5 py-1 rounded-full w-fit" style="animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">
                {{ $replacementLeaves->count() }}
            </span>
        </div>
        <div class="p-3 md:p-4">
            <div class="space-y-2">
                @forelse($replacementLeaves->take(2) as $rl)
                <div class="flex items-center space-x-2 p-2 bg-purple-50 rounded hover:bg-purple-100 transition cursor-pointer text-xs md:text-sm">
                    <div class="w-1.5 h-1.5 bg-purple-500 rounded-full flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 truncate">{{ $rl->start_date->format('M d') }} - {{ ucfirst($rl->status) }}</p>
                        <p class="text-xs text-gray-500">{{ $rl->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="text-xs text-gray-600">No timetable updates</div>
                @endforelse
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <button onclick="showLeaveUpdatesSection()" class="block w-full text-center text-xs font-semibold text-purple-600 hover:text-purple-700 transition">
                        View Schedule →
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approved Overtime Section (Hidden by default) -->
<div class="mt-8 hidden" id="overtime-approved-section">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);" class="px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h2 class="text-xl font-bold text-white">Approved Overtime Requests</h2>
            </div>
            <button onclick="hideOvertimeApprovedSection()" class="text-white hover:text-green-100 transition">
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
                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-0.5 rounded">Approved</span>
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
        <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);" class="px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h2 class="text-xl font-bold text-white">Overtime Salary Claims Status</h2>
            </div>
            <button onclick="hideSalaryClaimsSection()" class="text-white hover:text-orange-100 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div class="divide-y divide-gray-200">
            @forelse($salaryClaims as $claim)
            <div class="p-6 bg-yellow-50 border-l-4 border-yellow-500">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            @php
                                $status = strtolower($claim->status ?? 'pending');
                                $statusLabel = $status === 'approved' ? 'Approved for Payment' : ($status === 'pending' ? 'Pending Review' : ucfirst($status));
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
                                <p class="text-lg font-bold text-{{ $status === 'approved' ? 'green' : ($status === 'pending' ? 'yellow' : 'red') }}-600">RM {{ number_format($totalPay, 2) }}</p>
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

<!-- Replacement Leave Schedule Section (Hidden by default) -->
<div class="mt-8 hidden" id="leave-updates-section">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);" class="px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h2 class="text-xl font-bold text-white">Replacement Leave Schedule</h2>
            </div>
            <button onclick="hideLeaveUpdatesSection()" class="text-white hover:text-purple-100 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Approved By</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">

                        @forelse($replacementLeaves as $r)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $r->start_date->format('Y-m-d') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ Illuminate\Support\Str::limit($r->reason, 80) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($r->status === 'approved')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                                @elseif($r->status === 'pending')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">{{ ucfirst($r->status) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $r->approved_by ?? ($r->approved_at ? 'Admin' : '-') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">No replacement leave records found</td>
                        </tr>
                        @endforelse

                    </tbody>
                </table>
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

    function showLeaveUpdatesSection() {
        document.getElementById('leave-updates-section').classList.remove('hidden');
        window.scrollTo({ top: document.getElementById('leave-updates-section').offsetTop, behavior: 'smooth' });
    }

    function hideLeaveUpdatesSection() {
        document.getElementById('leave-updates-section').classList.add('hidden');
    }
</script>

@endsection
