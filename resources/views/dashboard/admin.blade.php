@extends('layouts.admin')

@section('title', 'Admin Dashboard')

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
?>

<?php
use App\Models\Overtime;
use App\Models\OTClaim;
use App\Models\Leave;
use App\Models\Payroll;
use Carbon\Carbon;

// ===== ANALYTICS DATA PREPARATION =====

// Leave Trend Data (by month - starting from October 2025 - based on leave start/end dates)
$leaveTrendData = [];
$monthLabels = [];
$startMonth = Carbon::create(2025, 10, 1);

for ($i = 0; $i < 12; $i++) {
    $currentMonth = $startMonth->copy()->addMonths($i);
    $monthStart = $currentMonth->copy()->startOfMonth();
    $monthEnd = $currentMonth->copy()->endOfMonth();
    
    // Count leaves that overlap with this month (based on start_date and end_date)
    $count = Leave::where('status', 'approved')
        ->where(function($query) use ($monthStart, $monthEnd) {
            $query->whereBetween('start_date', [$monthStart, $monthEnd])
                  ->orWhereBetween('end_date', [$monthStart, $monthEnd])
                  ->orWhere(function($q) use ($monthStart, $monthEnd) {
                      $q->where('start_date', '<=', $monthStart)
                        ->where('end_date', '>=', $monthEnd);
                  });
        })
        ->count();
    
    $leaveTrendData[] = $count;
    $monthLabels[] = $currentMonth->format('M Y');
}

// Leave Type Distribution
$leaveTypeDistribution = [];
$leaveTypeStats = Leave::with('leaveType')
    ->selectRaw('leave_type_id, COUNT(*) as count')
    ->groupBy('leave_type_id')
    ->get();

foreach ($leaveTypeStats as $stat) {
    $typeName = $stat->leaveType->type_name ?? 'Unknown';
    $leaveTypeDistribution[strtolower($typeName)] = $stat->count;
}

// Payroll Cost Breakdown (Overall - All months)
$allPayroll = Payroll::all();
$overtimeCost = $allPayroll->sum('fulltime_ot_pay') + $allPayroll->sum('public_holiday_ot_pay');
$allowances = $allPayroll->sum('marketing_bonus') ?? 0; // Marketing bonus
$deductions = $allPayroll->sum('total_deductions') ?? 0; // Total deductions (unpaid, etc)

$payrollBreakdown = [
    'labels' => ['Overtime', 'Allowances (Bonus)', 'Deductions (Unpaid)'],
    'values' => [$overtimeCost, $allowances, $deductions]
];

// Unpaid Leave Impact (Top 5 employees with highest deductions)
$unpaidLeaveData = Leave::where('status', 'approved')
    ->whereHas('leaveType', function($query) {
        $query->where('type_name', 'like', '%unpaid%');
    })
    ->with('staff.user')
    ->selectRaw('staff_id, COUNT(*) as leave_count')
    ->groupBy('staff_id')
    ->orderByDesc('leave_count')
    ->limit(5)
    ->get()
    ->map(function($leave) {
        $dailyRate = 100; // Default daily rate, adjust as needed
        return [
            'name' => $leave->staff->user->name ?? 'Unknown',
            'deduction' => $leave->leave_count * $dailyRate
        ];
    })
    ->toArray();

$pendingOvertimeCount = Overtime::where('status','pending')->count();
$pendingOvertimes = Overtime::where('status','pending')->with('staff.user')->orderBy('created_at','desc')->take(3)->get();
// Full pending list for the expandable overtime section
$allPending = Overtime::where('status','pending')->with('staff.user')->orderBy('created_at','desc')->get();

// Pending payroll (salary) claims
$pendingPayrollCount = OTClaim::query()->payroll()->pending()->count();
$pendingPayrolls = OTClaim::query()->payroll()->pending()->orderBy('created_at','desc')->take(3)->get();
// Full payroll list for the expandable section
$allPayrolls = OTClaim::query()->payroll()->pending()->orderBy('created_at','desc')->get();

// Pending replacement leave claims
$pendingReplacementCount = OTClaim::query()->replacementLeave()->pending()->count();
$pendingReplacements = OTClaim::query()->replacementLeave()->pending()->orderBy('created_at','desc')->take(3)->get();
// Full replacement list for the expandable section
$allReplacements = OTClaim::query()->replacementLeave()->pending()->orderBy('created_at','desc')->get();
?>

<div class="mb-6">
    <h1 class="text-4xl font-extrabold text-blue-700">
        {{ $greeting }}, {{ explode(' ', $user->name)[0] }}!
    </h1>
    <p class="text-gray-600 mt-1">Welcome back to your Dashboard. Here are your latest updates.</p>
</div>

{{-- Toast Notification Container --}}
<div id="toast-container" class="fixed top-20 right-4 z-[100] space-y-2" style="max-width: 400px;"></div>

<!-- Small responsive tweak: force single-column cards when viewport is narrow (split view) -->
<style>
@media (max-width: 1100px) {
    .dashboard-cards {
        grid-template-columns: 1fr !important;
    }
}
</style>

<!-- Quick Actions Section -->
<div class="mt-6">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
        <div style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);" class="px-6 py-4">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <span>Quick Actions</span>
            </h2>
        </div>
        <div class="p-6 md:p-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Review All Requests Button -->
                <button onclick="showAllSections()" class="group relative bg-gradient-to-br from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white text-center font-semibold py-6 px-6 rounded-xl transition-all duration-300 shadow-md hover:shadow-xl transform hover:-translate-y-1 border border-blue-400/20">
                    <div class="flex flex-col items-center justify-center space-y-3">
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center group-hover:bg-white/30 transition-all duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <span class="text-base">Review All Requests</span>
                    </div>
                </button>
                
                <!-- Staff Management Button -->
                <a href="{{ route('admin.manage-staff') }}" class="group relative bg-gradient-to-br from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white text-center font-semibold py-6 px-6 rounded-xl transition-all duration-300 shadow-md hover:shadow-xl transform hover:-translate-y-1 border border-green-400/20">
                    <div class="flex flex-col items-center justify-center space-y-3">
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center group-hover:bg-white/30 transition-all duration-300">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <span class="text-base">Staff Management</span>
                    </div>
                </a>
                
                <!-- View Staff Schedule Button -->
                <a href="{{ route('admin.staff-timetable') }}" class="group relative bg-gradient-to-br from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white text-center font-semibold py-6 px-6 rounded-xl transition-all duration-300 shadow-md hover:shadow-xl transform hover:-translate-y-1 border border-indigo-400/20">
                    <div class="flex flex-col items-center justify-center space-y-3">
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center group-hover:bg-white/30 transition-all duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <span class="text-base">View Staff Schedule</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
<!-- End Quick Actions Section -->
<br>

<!-- Dashboard Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6 dashboard-cards">
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
                    <span class="text-xs text-gray-600">Email:</span>
                    <span class="font-semibold text-gray-800 text-xs md:text-sm block truncate">{{ $user->email }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-600">Role:</span>
                    <span class="font-semibold text-gray-800 text-xs md:text-sm block">{{ ucfirst($user->role) }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-600">Status:</span>
                    <span class="font-semibold text-green-600 text-xs md:text-sm block">Active</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Overtime Notifications Card -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden min-w-0">
        <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);" class="px-4 md:px-6 py-3 md:py-4">
            <div class="flex items-center space-x-2">
                <svg class="w-4 md:w-5 h-4 md:h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h2 class="text-sm md:text-lg font-bold text-white flex items-center gap-2">
                    <span>Overtime Request</span>
                    <span class="bg-white text-orange-600 text-xs font-bold px-2.5 py-1 rounded-full" style="animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">
                        {{ $pendingOvertimeCount }}
                    </span>
                </h2>
            </div>
        </div>
        <div class="p-3 md:p-4">
            <div class="space-y-2">
                @forelse($pendingOvertimes as $ot)
                <div class="flex items-center space-x-2 p-2 bg-orange-50 rounded hover:bg-orange-100 transition cursor-pointer text-xs md:text-sm">
                    <div class="w-1.5 h-1.5 bg-orange-500 rounded-full flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 truncate">{{ $ot->user->name ?? 'Unknown' }} - {{ number_format($ot->hours,1) }}hrs</p>
                        <p class="text-xs text-gray-500">{{ $ot->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="text-xs text-gray-600">No pending overtime requests</div>
                @endforelse
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <button type="button" onclick="showOvertimeSection()" class="block w-full text-center text-xs font-semibold text-orange-600 hover:text-orange-700 transition">
                        View All →
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Salary Claims Notifications Card -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden min-w-0">
        <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);" class="px-4 md:px-6 py-3 md:py-4">
            <div class="flex items-center space-x-2">
                <svg class="w-4 md:w-5 h-4 md:h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h2 class="text-sm md:text-lg font-bold text-white flex items-center gap-2">
                    <span>Salary Claims</span>
                    <span class="bg-white text-green-600 text-xs font-bold px-2.5 py-1 rounded-full" style="animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">
                        {{ $pendingPayrollCount }}
                    </span>
                </h2>
            </div>
        </div>
        <div class="p-3 md:p-4">
            <div class="space-y-2">
                @forelse($pendingPayrolls as $claim)
                @php
                    $claimUser = null;
                    if ($claim->ot_ids && is_array($claim->ot_ids) && !empty($claim->ot_ids)) {
                        $firstOtId = $claim->ot_ids[0];
                        $overtime = \App\Models\Overtime::with('staff.user')->find($firstOtId);
                        if ($overtime && $overtime->staff && $overtime->staff->user) {
                            $claimUser = $overtime->staff->user;
                        }
                    }
                    $amounts = $claim->calculatePayrollAmounts();
                @endphp
                <div class="flex items-center space-x-2 p-2 bg-green-50 rounded hover:bg-green-100 transition cursor-pointer text-xs md:text-sm">
                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 truncate">{{ $claimUser->name ?? 'Unknown' }} - RM {{ number_format($amounts['total_pay'] ?? 0, 2) }}</p>
                        <p class="text-xs text-gray-500">{{ $claim->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="text-xs text-gray-600">No pending payroll claims</div>
                @endforelse
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <button type="button" onclick="showSalarySection()" class="block w-full text-center text-xs font-semibold text-green-600 hover:text-green-700 transition">
                        View All →
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Replacement Leave Notifications Card -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden min-w-0">
        <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);" class="px-4 md:px-6 py-3 md:py-4">
            <div class="flex items-center space-x-2">
                <svg class="w-4 md:w-5 h-4 md:h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h2 class="text-sm md:text-lg font-bold text-white flex items-center gap-2">
                    <span>Replacement Leave</span>
                    <span class="bg-white text-purple-600 text-xs font-bold px-2.5 py-1 rounded-full" style="animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">
                        {{ $pendingReplacementCount }}
                    </span>
                </h2>
            </div>
        </div>
        <div class="p-3 md:p-4">
            <div class="space-y-2">
                @forelse($pendingReplacements as $claim)
                @php
                    $claimUser = null;
                    if ($claim->ot_ids && is_array($claim->ot_ids) && !empty($claim->ot_ids)) {
                        $firstOtId = $claim->ot_ids[0];
                        $overtime = \App\Models\Overtime::with('staff.user')->find($firstOtId);
                        if ($overtime && $overtime->staff && $overtime->staff->user) {
                            $claimUser = $overtime->staff->user;
                        }
                    }
                    if (!$claimUser && $claim->user) {
                        $claimUser = $claim->user;
                    }
                @endphp
                <div class="flex items-center space-x-2 p-2 bg-purple-50 rounded hover:bg-purple-100 transition cursor-pointer text-xs md:text-sm">
                    <div class="w-1.5 h-1.5 bg-purple-500 rounded-full flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 truncate">{{ $claimUser->name ?? 'Unknown' }} - {{ intval($claim->replacement_days) }} day{{ intval($claim->replacement_days) > 1 ? 's' : '' }}</p>
                        <p class="text-xs text-gray-500">{{ $claim->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="text-xs text-gray-600">No pending replacement leave claims</div>
                @endforelse
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <button type="button" onclick="showLeaveSection()" class="block w-full text-center text-xs font-semibold text-purple-600 hover:text-purple-700 transition">
                        View All →
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Overtime Requests Section (Hidden by default) -->
<div class="mt-8 hidden" id="overtime-section">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);" class="px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h2 class="text-xl font-bold text-white">Overtime Requests</h2>
            </div>
            <div class="flex items-center space-x-3">
                <span class="bg-white text-orange-600 text-sm font-bold px-3 py-1 rounded-full">
                    {{ $pendingOvertimeCount }} Pending
                </span>
                <button onclick="hideOvertimeSection()" class="text-white hover:text-orange-100 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="divide-y divide-gray-200">
            @if($allPending->isEmpty())
                <div class="p-6 text-gray-600">No pending overtime requests.</div>
            @else
                @foreach($allPending as $p)
                <div class="p-6 bg-orange-50 hover:bg-orange-100 transition cursor-pointer border-l-4 border-orange-500">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4 flex-1">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-md">
                                    {{ strtoupper(substr($p->user->name ?? 'U', 0, 2)) }}
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <h3 class="font-bold text-gray-900">{{ $p->user->name ?? 'Unknown' }}</h3>
                                    <span class="bg-orange-100 text-orange-800 text-xs font-semibold px-2 py-0.5 rounded">New</span>
                                </div>
                                <p class="text-sm text-gray-600 mb-2">Overtime Request - {{ $p->ot_date->format('F d, Y') }}</p>
                                <p class="text-sm text-gray-700 mb-2">
                                    <span class="font-semibold">Duration:</span> {{ number_format($p->hours,1) }} hours |
                                    <span class="font-semibold ml-2">Date:</span> {{ $p->ot_date->format('M d, Y') }} |
                                    <span class="font-semibold ml-2">Type:</span> {{ ucfirst($p->ot_type) }}
                                </p>
                                <p class="text-sm text-gray-600">{{ $p->remarks }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end space-y-2 ml-4">
                            <span class="text-xs text-gray-500">{{ $p->created_at->diffForHumans() }}</span>
                            <div class="flex space-x-2">
                                <form method="post" action="{{ route('admin.overtimes.approve', $p) }}" class="inline">
                                    @csrf
                                    <button class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow-sm">Approve</button>
                                </form>
                                <form method="post" action="{{ route('admin.overtimes.reject', $p) }}" class="inline">
                                    @csrf
                                    <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow-sm">Reject</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            @endif

            <!-- Pagination -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Showing <span class="font-semibold">1-3</span> of <span class="font-semibold">3</span> requests
                    </div>
                    <div class="flex space-x-2">
                        <button class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-100 transition disabled:opacity-50" disabled>
                            Previous
                        </button>
                        <button class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-100 transition disabled:opacity-50" disabled>
                            Next
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Salary Claims Section (Hidden by default) -->
<div class="mt-8 hidden" id="salary-section">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);" class="px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h2 class="text-xl font-bold text-white">Salary Claims (Overtime Payment)</h2>
            </div>
            <div class="flex items-center space-x-3">
                <span class="bg-white text-green-600 text-sm font-bold px-3 py-1 rounded-full">
                    {{ $pendingPayrollCount }} Pending
                </span>
                <button onclick="hideSalarySection()" class="text-white hover:text-green-100 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="divide-y divide-gray-200">
            @if($allPayrolls->isEmpty())
                <div class="p-6 text-gray-600">No payroll claims available.</div>
            @else
                @foreach($allPayrolls as $claim)
                @php
                    $claimUser = null;
                    if ($claim->ot_ids && is_array($claim->ot_ids) && !empty($claim->ot_ids)) {
                        $firstOtId = $claim->ot_ids[0];
                        $overtime = \App\Models\Overtime::with('staff.user')->find($firstOtId);
                        if ($overtime && $overtime->staff && $overtime->staff->user) {
                            $claimUser = $overtime->staff->user;
                        }
                    }
                @endphp
                <div class="p-6 {{ $claim->status === 'pending' ? 'bg-green-50 hover:bg-green-100' : 'hover:bg-gray-50' }} transition cursor-pointer border-l-4 {{ $claim->status === 'pending' ? 'border-green-500' : 'border-transparent' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4 flex-1">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-md">
                                    {{ strtoupper(substr($claimUser->name ?? 'U', 0, 2)) }}
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <h3 class="font-bold text-gray-900">{{ $claimUser->name ?? 'Unknown' }}</h3>
                                    <span class="{{ $claim->status === 'pending' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }} text-xs font-semibold px-2 py-0.5 rounded">{{ ucfirst($claim->status) }}</span>
                                </div>
                                @php $amounts = $claim->calculatePayrollAmounts(); @endphp
                                <p class="text-sm text-gray-600 mb-2">Payroll Claim - {{ $claim->created_at->format('F Y') }}</p>
                                <p class="text-sm text-gray-700 mb-2">
                                    <span class="font-semibold">Amount:</span> RM {{ number_format($amounts['total_pay'] ?? 0, 2) }} |
                                    <span class="font-semibold ml-2">Total Hours:</span> {{ number_format($amounts['total_hours'] ?? 0, 1) }} hours |
                                    <span class="font-semibold ml-2">Period:</span> {{ $claim->created_at->format('M Y') }}
                                </p>
                                <div class="bg-white p-3 rounded border border-green-200 text-xs">
                                    <p class="text-gray-700 mb-1"><span class="font-semibold">Breakdown:</span></p>
                                    <ul class="space-y-1 text-gray-600">
                                        @if(($amounts['fulltime_hours'] ?? 0) > 0)
                                            <li>• Fulltime OT: {{ number_format($amounts['fulltime_hours'], 1) }} hours @ RM 12.26/hr = RM {{ number_format($amounts['fulltime_pay'], 2) }}</li>
                                        @endif
                                        @if(($amounts['public_holiday_hours'] ?? 0) > 0)
                                            <li>• Public Holiday OT: {{ number_format($amounts['public_holiday_hours'], 1) }} hours @ RM 21.68/hr = RM {{ number_format($amounts['public_holiday_pay'], 2) }}</li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col items-end space-y-2 ml-4">
                            <span class="text-xs text-gray-500">{{ $claim->created_at->diffForHumans() }}</span>
                            @if($claim->status === 'pending')
                            <div class="flex flex-col space-y-2">
                                <form method="post" action="{{ route('admin.otclaims.approve', $claim) }}" class="inline">
                                    @csrf
                                    <button class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow-sm whitespace-nowrap">Approve</button>
                                </form>
                                <form method="post" action="{{ route('admin.otclaims.reject', $claim) }}" class="inline">
                                    @csrf
                                    <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow-sm">Reject</button>
                                </form>
                            </div>
                            @else
                            <span class="text-xs text-green-600 font-semibold">✓ {{ ucfirst($claim->status) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Showing <span class="font-semibold">1-4</span> of <span class="font-semibold">4</span> claims
                </div>
                <div class="flex space-x-2">
                    <button class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-100 transition disabled:opacity-50" disabled>
                        Previous
                    </button>
                    <button class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-100 transition disabled:opacity-50" disabled>
                        Next
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Replacement Leave Section (Hidden by default) -->
<div class="mt-8 hidden" id="leave-section">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);" class="px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h2 class="text-xl font-bold text-white">Replacement Leave Claims</h2>
            </div>
            <div class="flex items-center space-x-3">
                <span class="bg-white text-purple-600 text-sm font-bold px-3 py-1 rounded-full">
                    {{ $pendingReplacementCount }} Pending
                </span>
                <button onclick="hideLeaveSection()" class="text-white hover:text-purple-100 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="divide-y divide-gray-200">
            @if($allReplacements->isEmpty())
                <div class="p-6 text-gray-600">No replacement leave claims available.</div>
            @else
                @foreach($allReplacements as $claim)
                @php
                    $claimUser = null;
                    if ($claim->ot_ids && is_array($claim->ot_ids) && !empty($claim->ot_ids)) {
                        $firstOtId = $claim->ot_ids[0];
                        $overtime = \App\Models\Overtime::with('staff.user')->find($firstOtId);
                        if ($overtime && $overtime->staff && $overtime->staff->user) {
                            $claimUser = $overtime->staff->user;
                        }
                    }
                    if (!$claimUser && $claim->user) {
                        $claimUser = $claim->user;
                    }
                @endphp
                <div class="p-6 {{ $claim->status === 'pending' ? 'bg-purple-50 hover:bg-purple-100' : 'hover:bg-gray-50' }} transition cursor-pointer border-l-4 {{ $claim->status === 'pending' ? 'border-purple-500' : 'border-transparent' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4 flex-1">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-md">
                                    {{ strtoupper(substr($claimUser->name ?? 'U', 0, 2)) }}
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <h3 class="font-bold text-gray-900">{{ $claimUser->name ?? 'Unknown' }}</h3>
                                    <span class="{{ $claim->status === 'pending' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }} text-xs font-semibold px-2 py-0.5 rounded">{{ ucfirst($claim->status) }}</span>
                                </div>
                                <p class="text-sm text-gray-600 mb-2">Replacement Leave Claim - {{ $claim->created_at->format('F Y') }}</p>
                                <p class="text-sm text-gray-700 mb-2">
                                    <span class="font-semibold">Days Claimed:</span> {{ intval($claim->replacement_days) }} day{{ intval($claim->replacement_days) > 1 ? 's' : '' }} |
                                    <span class="font-semibold ml-2">Total Hours:</span> {{ number_format(($claim->fulltime_hours ?? 0) + ($claim->public_holiday_hours ?? 0), 1) }} hours |
                                    <span class="font-semibold ml-2">Date:</span> {{ $claim->created_at->format('M d, Y') }}
                                </p>
                                <div class="bg-white p-3 rounded border border-purple-200 text-xs">
                                    <p class="text-gray-700 mb-1"><span class="font-semibold">Details:</span></p>
                                    <ul class="space-y-1 text-gray-600">
                                        <li>• Submitted: {{ $claim->created_at->format('M d, Y H:i') }}</li>
                                        <li>• Fulltime Hours: {{ number_format($claim->fulltime_hours ?? 0, 1) }}</li>
                                        <li>• Public Holiday Hours: {{ number_format($claim->public_holiday_hours ?? 0, 1) }}</li>
                                        <li>• Status: {{ ucfirst($claim->status) }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col items-end space-y-2 ml-4">
                            <span class="text-xs text-gray-500">{{ $claim->created_at->diffForHumans() }}</span>
                            @if($claim->status === 'pending')
                            <div class="flex flex-col space-y-2">
                                <form method="post" action="{{ route('admin.otclaims.approve', $claim) }}" class="inline">
                                    @csrf
                                    <button class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow-sm whitespace-nowrap">Approve</button>
                                </form>
                                <form method="post" action="{{ route('admin.otclaims.reject', $claim) }}" class="inline">
                                    @csrf
                                    <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow-sm">Reject</button>
                                </form>
                            </div>
                            @else
                            <span class="text-xs text-green-600 font-semibold">✓ {{ ucfirst($claim->status) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Showing <span class="font-semibold">1-3</span> of <span class="font-semibold">3</span> claims
                </div>
                <div class="flex space-x-2">
                    <button class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-100 transition disabled:opacity-50" disabled>
                        Previous
                    </button>
                    <button class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-100 transition disabled:opacity-50" disabled>
                        Next
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Dashboard Summary Analytics Section -->
<div class="mt-12">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Analytics & Monitoring</h2>
    
    <!-- Leave Analytics -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
        <div style="background: #f3f4f6;" class="px-6 py-4">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Leave Analytics & Insights
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Leave Trend by Month -->
                <div class="bg-white rounded-lg p-6 border border-gray-200">
                    <h4 class="text-md font-bold text-gray-800 mb-4">Leave Trend by Month</h4>
                    <p class="text-sm text-gray-600 mb-3">Identify seasonal leave patterns throughout the year</p>
                    <div style="height: 250px;">
                        <canvas id="leaveTrendChart"></canvas>
                    </div>
                    <div class="mt-3 text-xs text-gray-600">
                        <p><strong>Purpose:</strong> Monitor leave application trends across months</p>
                    </div>
                </div>

                <!-- Leave Type Distribution -->
                <div class="bg-white rounded-lg p-6 border border-gray-200">
                    <h4 class="text-md font-bold text-gray-800 mb-4">Leave Type Distribution</h4>
                    <p class="text-sm text-gray-600 mb-3">Understand leave consumption behavior by type</p>
                    <div style="height: 250px;">
                        <canvas id="leaveTypeChart"></canvas>
                    </div>
                    <div class="mt-3 text-xs text-gray-600">
                        <p><strong>Types:</strong> Annual, Medical, Unpaid, Emergency, Replacement</p>
                    </div>
                </div>

                <!-- Leave Summary Stats -->
                <div class="bg-white rounded-lg p-6 border border-gray-200 lg:col-span-2">
                    <h4 class="text-md font-bold text-gray-800 mb-4">Leave Summary Statistics</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-white p-4 rounded-lg border border-blue-100">
                            <p class="text-xs text-gray-600 mb-1">Total Leave Apps</p>
                            <p class="text-2xl font-bold text-blue-600">
                                {{ \App\Models\Leave::count() }}
                            </p>
                        </div>
                        <div class="bg-white p-4 rounded-lg border border-green-100">
                            <p class="text-xs text-gray-600 mb-1">Approved</p>
                            <p class="text-2xl font-bold text-green-600">
                                {{ \App\Models\Leave::where('status', 'approved')->count() }}
                            </p>
                        </div>
                        <div class="bg-white p-4 rounded-lg border border-yellow-100">
                            <p class="text-xs text-gray-600 mb-1">Pending</p>
                            <p class="text-2xl font-bold text-yellow-600">
                                {{ \App\Models\Leave::where('status', 'pending')->count() }}
                            </p>
                        </div>
                        <div class="bg-white p-4 rounded-lg border border-red-100">
                            <p class="text-xs text-gray-600 mb-1">Rejected</p>
                            <p class="text-2xl font-bold text-red-600">
                                {{ \App\Models\Leave::where('status', 'rejected')->count() }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payroll Analytics -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
        <div style="background: #f3f4f6;" class="px-6 py-4">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Payroll Analytics & Cost Breakdown
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Payroll Cost Breakdown -->
                <div class="bg-white rounded-lg p-6 border border-gray-200">
                    <h4 class="text-md font-bold text-gray-800 mb-4">Payroll Cost Breakdown</h4>
                    <p class="text-sm text-gray-600 mb-3">Overall cost composition across all months</p>
                    <div style="height: 250px;">
                        <canvas id="payrollBreakdownChart"></canvas>
                    </div>
                </div>

                <!-- Unpaid Leave Impact -->
                <div class="bg-white rounded-lg p-6 border border-gray-200">
                    <h4 class="text-md font-bold text-gray-800 mb-4">Unpaid Leave Impact</h4>
                    <p class="text-sm text-gray-600 mb-3">Payroll loss due to unpaid leave by employee</p>
                    <div style="height: 250px;">
                        <canvas id="unpaidLeaveChart"></canvas>
                    </div>
                    <div class="mt-3 text-xs text-gray-600">
                        <p><strong>Shows:</strong> Salary deductions per employee due to unpaid leave</p>
                    </div>
                </div>

                <!-- Payroll Summary Stats -->
                <div class="bg-white rounded-lg p-6 border border-gray-200 lg:col-span-2">
                    <h4 class="text-md font-bold text-gray-800 mb-4">Payroll Summary Statistics (Overall)</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div class="bg-white p-4 rounded-lg border border-green-100">
                            <p class="text-xs text-gray-600 mb-1">Total Payroll</p>
                            <p class="text-lg font-bold text-green-600">
                                RM {{ number_format(\App\Models\Payroll::sum('net_salary'), 2) }}
                            </p>
                        </div>
                        <div class="bg-white p-4 rounded-lg border border-blue-100">
                            <p class="text-xs text-gray-600 mb-1">Overtime Cost</p>
                            <p class="text-lg font-bold text-blue-600">
                                RM {{ number_format(\App\Models\Payroll::sum('fulltime_ot_hours') * 12.26 + \App\Models\Payroll::sum('public_holiday_ot_hours') * 21.68, 2) }}
                            </p>
                        </div>
                        <div class="bg-white p-4 rounded-lg border border-yellow-100">
                            <p class="text-xs text-gray-600 mb-1">Staff Count</p>
                            <p class="text-lg font-bold text-yellow-600">
                                {{ \App\Models\Staff::where('status', 'active')->count() }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

<!-- JavaScript Toggle Functions -->
<script>
/**
 * Helper: Show a section (remove hidden) and scroll to it safely
 */
function _showAndScroll(sectionId) {
    const section = document.getElementById(sectionId);
    if (!section) return;

    section.classList.remove('hidden');

    // Ensure it's rendered before scrolling
    setTimeout(() => {
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 80);
}

// Make functions global so inline onclick ALWAYS finds them
window.showOvertimeSection = function () { _showAndScroll('overtime-section'); }
window.hideOvertimeSection = function () {
    const section = document.getElementById('overtime-section');
    if (section) section.classList.add('hidden');
}

window.showSalarySection = function () { _showAndScroll('salary-section'); }
window.hideSalarySection = function () {
    const section = document.getElementById('salary-section');
    if (section) section.classList.add('hidden');
}

window.showLeaveSection = function () { _showAndScroll('leave-section'); }
window.hideLeaveSection = function () {
    const section = document.getElementById('leave-section');
    if (section) section.classList.add('hidden');
}

window.showAllSections = function () {
    const overtimeSection = document.getElementById('overtime-section');
    const salarySection = document.getElementById('salary-section');
    const leaveSection = document.getElementById('leave-section');

    if (overtimeSection) overtimeSection.classList.remove('hidden');
    if (salarySection) salarySection.classList.remove('hidden');
    if (leaveSection) leaveSection.classList.remove('hidden');

    setTimeout(() => {
        if (overtimeSection) overtimeSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 80);
}

// Toast Notification System
window.showToast = function (message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
    const iconColor = type === 'success' ? 'text-green-500' : 'text-red-500';
    const icon = type === 'success'
        ? '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>'
        : '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>';

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

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
};

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
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    .animate-slide-in-right { animation: slide-in-right 0.3s ease-out; }
`;
document.head.appendChild(style);

// ==================== CHART.JS INITIALIZATION ====================

// Initialize Leave Trend Chart (Line Chart)
function initLeaveTrendChart() {
    const ctx = document.getElementById('leaveTrendChart');
    if (!ctx) return;
    
    const monthLabels = @json($monthLabels ?? []);
    
    // Get data from database
    const leaveData = @json($leaveTrendData ?? array_fill(0, 12, 0));
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthLabels.length > 0 ? monthLabels : ['Oct 2025', 'Nov 2025', 'Dec 2025', 'Jan 2026', 'Feb 2026', 'Mar 2026', 'Apr 2026', 'May 2026', 'Jun 2026', 'Jul 2026', 'Aug 2026', 'Sep 2026'],
            datasets: [{
                label: 'Leave Applications',
                data: leaveData,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#6366f1',
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

// Initialize Leave Type Distribution Chart (Bar Chart)
function initLeaveTypeChart() {
    const ctx = document.getElementById('leaveTypeChart');
    if (!ctx) return;
    
    // Get data from database
    const leaveTypeDistribution = @json($leaveTypeDistribution ?? []);
    const leaveTypes = Object.keys(leaveTypeDistribution);
    const leaveTypeCounts = Object.values(leaveTypeDistribution);
    const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: leaveTypes.length > 0 ? leaveTypes.map(t => t.charAt(0).toUpperCase() + t.slice(1)) : ['No Data'],
            datasets: [{
                label: 'Number of Applications',
                data: leaveTypeCounts.length > 0 ? leaveTypeCounts : [0],
                backgroundColor: leaveTypes.length > 0 ? colors.slice(0, leaveTypes.length) : ['#d1d5db'],
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    labels: { font: { size: 12 }, padding: 15 }
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

// Initialize Payroll Cost Breakdown Chart (Doughnut/Pie Chart)
function initPayrollBreakdownChart() {
    const ctx = document.getElementById('payrollBreakdownChart');
    if (!ctx) return;
    
    // Get data from database
    const payrollBreakdown = @json($payrollBreakdown ?? []);
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: payrollBreakdown.labels || ['Basic Salary', 'Overtime', 'Allowances', 'Deductions'],
            datasets: [{
                data: payrollBreakdown.values || [60, 15, 15, 10],
                backgroundColor: [
                    '#10b981',
                    '#f59e0b',
                    '#3b82f6',
                    '#ef4444'
                ],
                borderColor: '#fff',
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: { font: { size: 11 }, padding: 15 }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': RM ' + context.parsed;
                        }
                    }
                }
            }
        }
    });
}

// Initialize Unpaid Leave Impact Chart (Horizontal Bar Chart)
function initUnpaidLeaveChart() {
    const ctx = document.getElementById('unpaidLeaveChart');
    if (!ctx) return;
    
    // Get data from database
    const unpaidLeaveData = @json($unpaidLeaveData ?? []);
    const employees = unpaidLeaveData.map(d => d.name) || ['No Data'];
    const deductions = unpaidLeaveData.map(d => d.deduction) || [0];
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: employees.length > 0 ? employees : ['No Data'],
            datasets: [{
                label: 'Salary Deduction (RM)',
                data: deductions.length > 0 ? deductions : [0],
                backgroundColor: '#f59e0b',
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    labels: { font: { size: 12 }, padding: 15 }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                },
                y: {
                    grid: { display: false }
                }
            }
        }
    });
}

// Initialize all charts on page load
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        initLeaveTrendChart();
        initLeaveTypeChart();
        initPayrollBreakdownChart();
        initUnpaidLeaveChart();
    }, 300);
});
</script>
@endsection