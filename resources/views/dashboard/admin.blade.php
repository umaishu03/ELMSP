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
$pendingOvertimeCount = Overtime::where('status','pending')->count();
$pendingOvertimes = Overtime::where('status','pending')->with('staff.user')->orderBy('created_at','desc')->take(3)->get();
// Full pending list for the expandable overtime section
$allPending = Overtime::where('status','pending')->with('staff.user')->orderBy('created_at','desc')->get();

// Pending payroll (salary) claims
$pendingPayrollCount = OTClaim::query()->payroll()->pending()->count();
$pendingPayrolls = OTClaim::query()->payroll()->pending()->with('payroll.user')->orderBy('created_at','desc')->take(3)->get();
// Full payroll list for the expandable section
$allPayrolls = OTClaim::query()->payroll()->pending()->with('payroll.user')->orderBy('created_at','desc')->get();

// Pending replacement leave claims
$pendingReplacementCount = OTClaim::query()->replacementLeave()->pending()->count();
$pendingReplacements = OTClaim::query()->replacementLeave()->pending()->with('leave.staff.user')->orderBy('created_at','desc')->take(3)->get();
// Full replacement list for the expandable section
$allReplacements = OTClaim::query()->replacementLeave()->pending()->with('leave.staff.user')->orderBy('created_at','desc')->get();
?><div class="mb-6">
    <h1 class="text-4xl font-extrabold text-blue-700">
        {{ $greeting }}, {{ $user->name }}!
    </h1>
    <p class="text-gray-600 mt-1">Welcome back to your Dashboard. Here are your latest updates.</p>
</div>

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
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);" class="px-6 py-4">
            <h2 class="text-lg font-bold text-white">Quick Actions</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <button onclick="showAllSections()" class="block w-full bg-blue-500 hover:bg-blue-600 text-white text-center font-semibold py-3 px-4 rounded-lg transition duration-200 shadow-sm">
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span>Review All Requests</span>
                    </div>
                </button>
                <a href="{{ route('admin.manage-staff') }}" class="block w-full bg-green-500 hover:bg-green-600 text-white text-center font-semibold py-3 px-4 rounded-lg transition duration-200 shadow-sm">
                    <div class="flex items-center justify-center space-x-2">
                        <i class="fas fa-list mr-3"></i>
                        <span>Staff Management</span>
                    </div>
                </a>
                <a href="{{ route('admin.staff-timetable') }}" class="block w-full bg-indigo-500 hover:bg-indigo-600 text-white text-center font-semibold py-3 px-4 rounded-lg transition duration-200 shadow-sm">
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>View Staff Schedule</span>
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
        <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);" class="px-4 md:px-6 py-3 md:py-4 flex flex-col md:flex-row md:justify-between md:items-center gap-2">
            <div class="flex items-center space-x-2">
                <svg class="w-4 md:w-5 h-4 md:h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h2 class="text-sm md:text-lg font-bold text-white">Overtime Request</h2>
            </div>
            <span class="bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full w-fit" style="animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">
                {{ $pendingOvertimeCount }}
            </span>
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
        <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);" class="px-4 md:px-6 py-3 md:py-4 flex flex-col md:flex-row md:justify-between md:items-center gap-2">
            <div class="flex items-center space-x-2">
                <svg class="w-4 md:w-5 h-4 md:h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h2 class="text-sm md:text-lg font-bold text-white">Salary Claims</h2>
            </div>
            <span class="bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full w-fit" style="animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">
                {{ $pendingPayrollCount }}
            </span>
        </div>
        <div class="p-3 md:p-4">
            <div class="space-y-2">
                @forelse($pendingPayrolls as $claim)
                <div class="flex items-center space-x-2 p-2 bg-green-50 rounded hover:bg-green-100 transition cursor-pointer text-xs md:text-sm">
                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        @php $amounts = $claim->calculatePayrollAmounts(); @endphp
                        <p class="font-semibold text-gray-800 truncate">{{ $claim->payroll->user->name ?? 'Unknown' }} - RM {{ number_format($amounts['total_pay'] ?? 0, 2) }}</p>
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
        <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);" class="px-4 md:px-6 py-3 md:py-4 flex flex-col md:flex-row md:justify-between md:items-center gap-2">
            <div class="flex items-center space-x-2">
                <svg class="w-4 md:w-5 h-4 md:h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h2 class="text-sm md:text-lg font-bold text-white">Replacement Leave</h2>
            </div>
            <span class="bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full w-fit" style="animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">
                {{ $pendingReplacementCount }}
            </span>
        </div>
        <div class="p-3 md:p-4">
            <div class="space-y-2">
                @forelse($pendingReplacements as $claim)
                <div class="flex items-center space-x-2 p-2 bg-purple-50 rounded hover:bg-purple-100 transition cursor-pointer text-xs md:text-sm">
                    <div class="w-1.5 h-1.5 bg-purple-500 rounded-full flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 truncate">{{ $claim->leave->staff->user->name ?? 'Unknown' }} - {{ intval($claim->replacement_days) }} day{{ intval($claim->replacement_days) > 1 ? 's' : '' }}</p>
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
                <div class="p-6 {{ $claim->status === 'pending' ? 'bg-green-50 hover:bg-green-100' : 'hover:bg-gray-50' }} transition cursor-pointer border-l-4 {{ $claim->status === 'pending' ? 'border-green-500' : 'border-transparent' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4 flex-1">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-md">
                                    {{ strtoupper(substr($claim->user->name ?? 'U', 0, 2)) }}
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <h3 class="font-bold text-gray-900">{{ $claim->user->name ?? 'Unknown' }}</h3>
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
                                        @if($amounts['fulltime_hours'] > 0)
                                        <li>• Fulltime OT: {{ number_format($amounts['fulltime_hours'], 1) }} hours @ RM 12.26/hr = RM {{ number_format($amounts['fulltime_pay'], 2) }}</li>
                                        @endif
                                        @if($amounts['public_holiday_hours'] > 0)
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
                <div class="p-6 {{ $claim->status === 'pending' ? 'bg-purple-50 hover:bg-purple-100' : 'hover:bg-gray-50' }} transition cursor-pointer border-l-4 {{ $claim->status === 'pending' ? 'border-purple-500' : 'border-transparent' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4 flex-1">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-md">
                                    {{ strtoupper(substr($claim->user->name ?? 'U', 0, 2)) }}
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <h3 class="font-bold text-gray-900">{{ $claim->user->name ?? 'Unknown' }}</h3>
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

<!-- JavaScript Toggle Functions -->
<script>
// Overtime Section
function showOvertimeSection() {
    const section = document.getElementById('overtime-section');
    if (section) {
        section.classList.remove('hidden');
        // Use setTimeout to ensure the section is rendered before scrolling
        setTimeout(() => {
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    }
}

function hideOvertimeSection() {
    const section = document.getElementById('overtime-section');
    if (section) {
        section.classList.add('hidden');
    }
}

// Salary Section
function showSalarySection() {
    const section = document.getElementById('salary-section');
    if (section) {
        section.classList.remove('hidden');
        // Use setTimeout to ensure the section is rendered before scrolling
        setTimeout(() => {
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    }
}

function hideSalarySection() {
    const section = document.getElementById('salary-section');
    if (section) {
        section.classList.add('hidden');
    }
}

// Replacement Leave Section
function showLeaveSection() {
    const section = document.getElementById('leave-section');
    if (section) {
        section.classList.remove('hidden');
        // Use setTimeout to ensure the section is rendered before scrolling
        setTimeout(() => {
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    }
}

function hideLeaveSection() {
    const section = document.getElementById('leave-section');
    if (section) {
        section.classList.add('hidden');
    }
}

// Show All Sections
function showAllSections() {
    const overtimeSection = document.getElementById('overtime-section');
    const salarySection = document.getElementById('salary-section');
    const leaveSection = document.getElementById('leave-section');
    
    // Show all sections
    if (overtimeSection) overtimeSection.classList.remove('hidden');
    if (salarySection) salarySection.classList.remove('hidden');
    if (leaveSection) leaveSection.classList.remove('hidden');
    
    // Scroll to the first section after rendering
    setTimeout(() => {
        if (overtimeSection) {
            overtimeSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }, 100);
}
</script>
@endsection