@extends('layouts.staff')
@section('title', 'Leave Status')
@section('content')
<!-- Success Toast Message -->
@if($message = Session::get('success'))
<div id="successToast" class="fixed top-0 left-0 right-0 sm:top-4 sm:left-auto sm:right-6 bg-green-500 text-white px-4 sm:px-6 py-3 sm:py-4 rounded-none sm:rounded-lg shadow-lg flex items-center gap-2 sm:gap-3 z-[9999] animate-fade-in-down max-w-full sm:max-w-lg">
    <div class="flex items-center gap-2 sm:gap-3 flex-1 min-w-0">
        <i class="fas fa-check-circle text-lg sm:text-xl flex-shrink-0"></i>
        <div class="min-w-0 flex-1">
            <p class="font-semibold text-sm sm:text-base">Leave Request Submitted!</p>
            <p class="text-xs sm:text-sm text-green-100 truncate">{{ $message }}</p>
        </div>
    </div>
    <button onclick="document.getElementById('successToast').remove()" class="ml-2 sm:ml-4 text-white hover:text-green-100 flex-shrink-0">
        <i class="fas fa-times text-lg sm:text-xl"></i>
    </button>
</div>

<script>
    // Auto-hide toast after 5 seconds
    setTimeout(function() {
        const toast = document.getElementById('successToast');
        if (toast) {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }
    }, 5000);
</script>
@endif

<!-- Breadcrumbs -->
<div class="mb-6">
    {!! \App\Helpers\BreadcrumbHelper::render() !!}
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 sm:mt-6 lg:mt-8 mb-8 sm:mb-12">
    <!-- Header Card -->
    <div class="bg-white rounded-xl sm:rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-4 sm:mb-6">
        <div class="bg-gradient-to-r from-purple-600 to-purple-800 px-4 sm:px-6 md:px-8 py-3 sm:py-4 md:py-6">
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-white flex items-center gap-2 sm:gap-3">
                <i class="fas fa-clipboard-list text-lg sm:text-xl md:text-2xl"></i>
                <span class="break-words">Leave Status</span>
            </h1>
            <p class="text-purple-100 mt-1 sm:mt-2 text-xs sm:text-sm md:text-base">Track and manage all your leave applications</p>
        </div>
    

    <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4 md:p-6 mb-4 sm:mb-6">
    <!-- Leave Balance Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-3 sm:gap-4 mb-4 sm:mb-6">
        <!-- Annual Leave -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4 md:p-5 hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-2 sm:mb-3">
                <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Annual Leave</h3>
                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 ml-1">
                    <i class="fas fa-calendar-alt text-blue-600 text-xs sm:text-sm"></i>
                </div>
            </div>
            <div class="space-y-1">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-600 text-xs">Balance</span>
                    <span class="font-bold text-blue-600 text-xs sm:text-sm truncate ml-1" id="annualBalance">{{ isset($leaveBalance['annual']['balance']) ? $leaveBalance['annual']['balance'] . ' days' : '0 days' }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 text-xs">Max</span>
                    <span class="text-gray-700 text-xs">14 days</span>
                </div>
            </div>
        </div>

        <!-- Medical Leave -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4 md:p-5 hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-2 sm:mb-3">
                <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Medical Leave</h3>
                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0 ml-1">
                    <i class="fas fa-clinic-medical text-red-600 text-xs sm:text-sm"></i>
                </div>
            </div>
            <div class="space-y-1">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-600 text-xs">Balance</span>
                    <span class="font-bold text-red-600 text-xs sm:text-sm truncate ml-1" id="medicalBalance">{{ isset($leaveBalance['medical']['balance']) ? $leaveBalance['medical']['balance'] . ' days' : '0 days' }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 text-xs">Max</span>
                    <span class="text-gray-700 text-xs">14 days</span>
                </div>
            </div>
        </div>

        <!-- Hospitalization Leave -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4 md:p-5 hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-2 sm:mb-3">
                <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Hospitalization</h3>
                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-pink-100 rounded-full flex items-center justify-center flex-shrink-0 ml-1">
                    <i class="fas fa-hospital text-pink-600 text-xs sm:text-sm"></i>
                </div>
            </div>
            <div class="space-y-1">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-600 text-xs">Balance</span>
                    <span class="font-bold text-pink-600 text-xs sm:text-sm truncate ml-1" id="hospitalizationBalance">{{ isset($leaveBalance['hospitalization']['balance']) ? $leaveBalance['hospitalization']['balance'] . ' days' : '0 days' }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 text-xs">Max</span>
                    <span class="text-gray-700 text-xs">30 days</span>
                </div>
            </div>
        </div>

        <!-- Emergency Leave -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4 md:p-5 hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-2 sm:mb-3">
                <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Emergency</h3>
                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 ml-1">
                    <i class="fas fa-exclamation-triangle text-orange-600 text-xs sm:text-sm"></i>
                </div>
            </div>
            <div class="space-y-1">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-600 text-xs">Balance</span>
                    <span class="font-bold text-orange-600 text-xs sm:text-sm truncate ml-1" id="emergencyBalance">{{ isset($leaveBalance['emergency']['balance']) ? $leaveBalance['emergency']['balance'] . ' days' : '0 days' }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 text-xs">Max</span>
                    <span class="text-gray-700 text-xs">7 days</span>
                </div>
            </div>
        </div>

        <!-- Replacement Leave -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4 md:p-5 hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-2 sm:mb-3">
                <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Replacement</h3>
                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-teal-100 rounded-full flex items-center justify-center flex-shrink-0 ml-1">
                    <i class="fas fa-exchange-alt text-teal-600 text-xs sm:text-sm"></i>
                </div>
            </div>
            <div class="space-y-1">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-600 text-xs">Balance</span>
                    <span class="font-bold text-teal-600 text-xs sm:text-sm truncate ml-1" id="replacementBalance">{{ isset($leaveBalance['replacement']['balance']) ? $leaveBalance['replacement']['balance'] . ' days' : '0 days' }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 text-xs">OT Hours</span>
                    <span class="text-gray-700 text-xs truncate" id="otHours">{{ isset($leaveBalance['replacement']['balance']) && $leaveBalance['replacement']['balance'] > 0 ? ($leaveBalance['replacement']['balance'] * 8) . 'h' : '0h' }}</span>
                </div>
            </div>
        </div>

        <!-- Marriage Leave -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4 md:p-5 hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-2 sm:mb-3">
                <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Marriage</h3>
                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0 ml-1">
                    <i class="fas fa-heart text-purple-600 text-xs sm:text-sm"></i>
                </div>
            </div>
            <div class="space-y-1">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-600 text-xs">Balance</span>
                    <span class="font-bold text-purple-600 text-xs sm:text-sm truncate ml-1" id="marriageBalance">{{ isset($leaveBalance['marriage']['balance']) ? ($leaveBalance['marriage']['balance'] === 0 ? 'Used' : 'Available') : 'Available' }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 text-xs">Max</span>
                    <span class="text-gray-700 text-xs">One-time</span>
                </div>
            </div>
        </div>

        <!-- Unpaid Leave -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4 md:p-5 hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-2 sm:mb-3">
                <h3 class="text-xs sm:text-sm font-semibold text-gray-700 truncate">Unpaid</h3>
                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0 ml-1">
                    <i class="fas fa-minus-circle text-gray-600 text-xs sm:text-sm"></i>
                </div>
            </div>
            <div class="space-y-1">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-600 text-xs">Balance</span>
                    <span class="font-bold text-gray-600 text-xs sm:text-sm truncate ml-1" id="unpaidBalance">{{ isset($leaveBalance['unpaid']['balance']) ? $leaveBalance['unpaid']['balance'] . ' days' : '10 days' }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 text-xs">Max</span>
                    <span class="text-gray-700 text-xs">10 days</span>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 md:gap-6 mb-4 sm:mb-6">
        <!-- Total Applications -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5 md:p-6 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-600 mb-1 truncate">Total Applications</p>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900" id="totalApplications">0</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0 ml-2">
                    <i class="fas fa-file-alt text-purple-600 text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Pending -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5 md:p-6 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-600 mb-1 truncate">Pending</p>
                    <p class="text-2xl sm:text-3xl font-bold text-amber-600" id="pendingCount">0</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0 ml-2">
                    <i class="fas fa-clock text-amber-600 text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Approved -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5 md:p-6 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-600 mb-1 truncate">Approved</p>
                    <p class="text-2xl sm:text-3xl font-bold text-green-600" id="approvedCount">0</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 ml-2">
                    <i class="fas fa-check-circle text-green-600 text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Rejected -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5 md:p-6 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-600 mb-1 truncate">Rejected</p>
                    <p class="text-2xl sm:text-3xl font-bold text-red-600" id="rejectedCount">0</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0 ml-2">
                    <i class="fas fa-times-circle text-red-600 text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter and Action Bar -->
    <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-100 p-3 sm:p-4 md:p-6 mb-4 sm:mb-6">
        <div class="flex flex-col lg:flex-row gap-3 sm:gap-4 items-stretch lg:items-center justify-between">
            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full lg:w-auto">
                <!-- Status Filter -->
                <div class="relative flex-1 sm:flex-initial sm:w-auto min-w-0">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5 sm:hidden">Filter by Status</label>
                    <select id="statusFilter" class="appearance-none border border-gray-300 rounded-lg px-3 sm:px-4 py-2.5 sm:py-2.5 pr-10 w-full text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white text-gray-700 min-h-[44px]">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs sm:text-sm"></i>
                </div>

                <!-- Leave Type Filter -->
                <div class="relative flex-1 sm:flex-initial sm:w-auto min-w-0">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5 sm:hidden">Filter by Leave Type</label>
                    <select id="leaveTypeFilter" class="appearance-none border border-gray-300 rounded-lg px-3 sm:px-4 py-2.5 sm:py-2.5 pr-10 w-full text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white text-gray-700 min-h-[44px]">
                        <option value="">All Leave Types</option>
                        <option value="annual">Annual Leave</option>
                        <option value="hospitalization">Hospitalization Leave</option>
                        <option value="medical">Medical Leave</option>
                        <option value="emergency">Emergency Leave</option>
                        <option value="marriage">Marriage Leave</option>
                        <option value="replacement">Replacement Leave</option>
                        <option value="unpaid">Unpaid Leave</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs sm:text-sm"></i>
                </div>

                <!-- Search -->
                <div class="relative flex-1 sm:flex-initial sm:w-48 md:w-64 min-w-0">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5 sm:hidden">Search</label>
                    <input type="text" 
                           id="searchInput" 
                           placeholder="Search applications..." 
                           class="border border-gray-300 rounded-lg pl-10 pr-4 py-2.5 sm:py-2.5 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent w-full min-h-[44px]">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs sm:text-sm"></i>
                </div>
            </div>

            <!-- New Application Button -->
            <a href="{{ route('staff.leave-application') }}" 
               class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-purple-600 to-purple-800 hover:from-purple-700 hover:to-purple-900 text-white font-semibold px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg shadow-md hover:shadow-lg transition-all duration-200 w-full lg:w-auto text-sm sm:text-base min-h-[44px]">
                <i class="fas fa-plus text-sm sm:text-base"></i>
                <span>New Application</span>
            </a>
        </div>
    </div>

    <!-- Leave Applications List -->
    <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-base sm:text-lg font-semibold text-gray-800">Leave Applications</h2>
        </div>

        <!-- Applications Container -->
        <div id="applicationsContainer" class="divide-y divide-gray-200">
            <!-- Application items will be inserted here by JavaScript -->
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden p-6 sm:p-8 md:p-12 text-center">
            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                <i class="fas fa-inbox text-gray-400 text-2xl sm:text-3xl"></i>
            </div>
            <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2">No leave applications found</h3>
            <p class="text-sm sm:text-base text-gray-500 mb-4 sm:mb-6">You haven't submitted any leave applications yet</p>
            <a href="{{ route('staff.leave-application') }}" 
               class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg transition-all text-sm sm:text-base">
                <i class="fas fa-plus"></i>
                <span>Create New Application</span>
            </a>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div id="detailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-xl sm:rounded-2xl shadow-2xl max-w-2xl w-full max-h-[95vh] sm:max-h-[90vh] overflow-hidden mx-auto">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-800 px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between">
            <h3 class="text-lg sm:text-xl font-bold text-white truncate pr-2">Application Details</h3>
            <button onclick="closeModal()" class="text-white hover:text-gray-200 transition-colors flex-shrink-0 min-w-[44px] min-h-[44px] flex items-center justify-center">
                <i class="fas fa-times text-lg sm:text-xl"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-4 sm:p-6 overflow-y-auto max-h-[calc(95vh-100px)] sm:max-h-[calc(90vh-120px)]" id="modalContent">
            <!-- Content will be inserted by JavaScript -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Real leave balance data from controller
    const leaveBalance = {!! json_encode($leaveBalance ?? []) !!};
    const leaveApplications = {!! json_encode($leaveApplications->map(function($leave) {
        $typeName = $leave->leaveType?->type_name ?? null;
        return [
            'id' => $leave->id,
            'leaveType' => $typeName,
            'leaveTypeName' => $typeName ? ucfirst(str_replace('_', ' ', $typeName)) : null,
            'startDate' => $leave->start_date,
            'endDate' => $leave->end_date,
            'days' => $leave->total_days,
            'reason' => $leave->reason,
            'status' => $leave->status,
            'appliedDate' => $leave->created_at->format('Y-m-d'),
            'autoApproved' => $leave->auto_approved ?? false,
            'approvedDate' => $leave->approved_date ? $leave->approved_date->format('Y-m-d') : null,
            'remarks' => $leave->remarks,
            'attachment' => $leave->attachment ? route('staff.leave.attachment', $leave->id) : null,
            'attachmentName' => $leave->attachment ? basename($leave->attachment) : null
        ];
    })->toArray()) !!};

    const applicationsContainer = document.getElementById('applicationsContainer');
    const emptyState = document.getElementById('emptyState');
    const statusFilter = document.getElementById('statusFilter');
    const leaveTypeFilter = document.getElementById('leaveTypeFilter');
    const searchInput = document.getElementById('searchInput');

    // Update leave balance display
    function updateBalanceDisplay() {
        document.getElementById('annualBalance').textContent = `${leaveBalance.annual?.balance || 0} days`;
        document.getElementById('medicalBalance').textContent = `${leaveBalance.medical?.balance || 0} days`;
        document.getElementById('hospitalizationBalance').textContent = `${leaveBalance.hospitalization?.balance || 0} days`;
        document.getElementById('emergencyBalance').textContent = `${leaveBalance.emergency?.balance || 0} days`;
        const replacementBalance = leaveBalance.replacement?.balance || 0;
        document.getElementById('replacementBalance').textContent = `${replacementBalance} days`;
        // OT hours should be 0 if balance is 0, otherwise show balance * 8
        const otHours = replacementBalance > 0 ? (replacementBalance * 8) : 0;
        document.getElementById('otHours').textContent = `${otHours.toFixed(0)}h`;
        document.getElementById('marriageBalance').textContent = leaveBalance.marriage?.balance === 0 ? 'Used' : 'Available';
        document.getElementById('unpaidBalance').textContent = `${leaveBalance.unpaid?.balance || 10} days`;
    }

    // Update stats
    function updateStats(applications) {
        document.getElementById('totalApplications').textContent = applications.length;
        document.getElementById('pendingCount').textContent = applications.filter(a => a.status === 'pending').length;
        document.getElementById('approvedCount').textContent = applications.filter(a => a.status === 'approved').length;
        document.getElementById('rejectedCount').textContent = applications.filter(a => a.status === 'rejected').length;
    }

    // Get status badge HTML
    function getStatusBadge(status) {
        const badges = {
            pending: '<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700"><i class="fas fa-clock"></i> Pending</span>',
            approved: '<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700"><i class="fas fa-check-circle"></i> Approved</span>',
            rejected: '<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700"><i class="fas fa-times-circle"></i> Rejected</span>'
        };
        return badges[status] || '';
    }

    // Get leave type color
    function getLeaveTypeColor(type) {
        const colors = {
            annual: 'bg-blue-100 text-blue-700',
            medical: 'bg-red-100 text-red-700',
            hospitalization: 'bg-pink-100 text-pink-700',
            emergency: 'bg-orange-100 text-orange-700',
            marriage: 'bg-purple-100 text-purple-700',
            replacement: 'bg-teal-100 text-teal-700',
            unpaid: 'bg-gray-100 text-gray-700',
        };
        return colors[type] || 'bg-gray-100 text-gray-700';
    }

    // Format date
    function formatDate(dateString) {
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('en-US', options);
    }

    // Render applications
    function renderApplications(applications) {
        if (applications.length === 0) {
            applicationsContainer.innerHTML = '';
            emptyState.classList.remove('hidden');
            return;
        }

        emptyState.classList.add('hidden');
        applicationsContainer.innerHTML = applications.map(app => `
            <div class="p-4 sm:p-5 md:p-6 hover:bg-gray-50 transition-all cursor-pointer" onclick="showDetails(${app.id})">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 sm:gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-2">
                            <span class="px-2 sm:px-3 py-1 rounded-lg text-xs font-semibold ${getLeaveTypeColor(app.leaveType)}">
                                ${app.leaveTypeName}
                            </span>
                            ${getStatusBadge(app.status)}
                            ${app.autoApproved && app.status === 'approved' ? '<span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium bg-green-50 text-green-700 border border-green-200"><i class="fas fa-bolt text-xs"></i> Auto</span>' : ''}
                        </div>
                        <div class="flex flex-wrap items-center gap-1 sm:gap-2 text-gray-600 mb-2">
                            <i class="fas fa-calendar text-purple-600 text-xs sm:text-sm"></i>
                            <span class="font-medium text-xs sm:text-sm">${formatDate(app.startDate)} - ${formatDate(app.endDate)}</span>
                            <span class="text-xs text-gray-500">(${app.days} ${app.days === 1 ? 'day' : 'days'})</span>
                        </div>
                        <p class="text-xs sm:text-sm text-gray-600 line-clamp-2 mb-2">${app.reason}</p>
                        <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-xs text-gray-500">
                            <span><i class="fas fa-clock mr-1"></i>Applied: ${formatDate(app.appliedDate)}</span>
                            ${app.attachment ? '<span><i class="fas fa-paperclip mr-1"></i>Attachment</span>' : ''}
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0 mt-2 md:mt-0">
                        <button onclick="event.stopPropagation(); showDetails(${app.id})" 
                                class="px-3 sm:px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs sm:text-sm font-medium transition-all min-h-[44px] whitespace-nowrap">
                            View Details
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    }

    // Filter applications
    function filterApplications() {
        const statusValue = statusFilter.value.toLowerCase();
        const leaveTypeValue = leaveTypeFilter.value.toLowerCase();
        const searchValue = searchInput.value.toLowerCase();

        const filtered = leaveApplications.filter(app => {
            const matchesStatus = !statusValue || app.status === statusValue;
            const matchesLeaveType = !leaveTypeValue || app.leaveType === leaveTypeValue;
            const matchesSearch = !searchValue || 
                app.leaveTypeName.toLowerCase().includes(searchValue) ||
                app.reason.toLowerCase().includes(searchValue);
            
            return matchesStatus && matchesLeaveType && matchesSearch;
        });

        renderApplications(filtered);
    }

    // Show details modal
    window.showDetails = function(id) {
        const app = leaveApplications.find(a => a.id === id);
        if (!app) return;

        const modalContent = document.getElementById('modalContent');
        modalContent.innerHTML = `
            <div class="space-y-4 sm:space-y-6">
                <!-- Status and Leave Type -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 sm:pb-4 border-b border-gray-200">
                    <div class="min-w-0">
                        <span class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-semibold ${getLeaveTypeColor(app.leaveType)}">
                            ${app.leaveTypeName}
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        ${getStatusBadge(app.status)}
                        ${app.autoApproved && app.status === 'approved' ? '<span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium bg-green-50 text-green-700 border border-green-200"><i class="fas fa-bolt text-xs"></i> Auto-Approved</span>' : ''}
                    </div>
                </div>

                <!-- Application Details -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">Start Date</label>
                        <p class="text-sm sm:text-base text-gray-900 font-semibold mt-1">${formatDate(app.startDate)}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">End Date</label>
                        <p class="text-sm sm:text-base text-gray-900 font-semibold mt-1">${formatDate(app.endDate)}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">Duration</label>
                        <p class="text-sm sm:text-base text-gray-900 font-semibold mt-1">${app.days} ${app.days === 1 ? 'day' : 'days'}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">Applied Date</label>
                        <p class="text-sm sm:text-base text-gray-900 font-semibold mt-1">${formatDate(app.appliedDate)}</p>
                    </div>
                </div>

                <!-- Reason -->
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase">Reason</label>
                    <p class="text-sm sm:text-base text-gray-900 mt-2 p-3 sm:p-4 bg-gray-50 rounded-lg break-words">${app.reason}</p>
                </div>

                ${app.attachment ? `
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">Attachment</label>
                        <div class="mt-2 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-2 p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                <i class="fas fa-file-pdf text-red-500 text-lg sm:text-xl flex-shrink-0"></i>
                                <span class="text-xs sm:text-sm text-gray-700 truncate">${app.attachmentName || 'Document'}</span>
                            </div>
                            <div class="flex gap-2 sm:ml-auto">
                                <a href="${app.attachment}" target="_blank" rel="noopener noreferrer" class="text-purple-600 hover:text-purple-700 text-xs sm:text-sm font-medium px-3 py-2 rounded min-h-[44px] flex items-center justify-center">
                                    <i class="fas fa-eye mr-1"></i>View
                                </a>
                                <a href="${app.attachment}" download class="text-purple-600 hover:text-purple-700 text-xs sm:text-sm font-medium px-3 py-2 rounded min-h-[44px] flex items-center justify-center">
                                    <i class="fas fa-download mr-1"></i>Download
                                </a>
                            </div>
                        </div>
                    </div>
                ` : ''}

                ${app.status !== 'pending' ? `
                    <div class="border-t border-gray-200 pt-3 sm:pt-4">
                        ${app.autoApproved ? `
                            <div class="bg-green-50 border border-green-200 rounded-lg p-3 sm:p-4 mb-3 sm:mb-4">
                                <div class="flex items-start gap-2 sm:gap-3">
                                    <i class="fas fa-check-circle text-green-600 text-lg sm:text-xl mt-0.5 flex-shrink-0"></i>
                                    <div class="min-w-0">
                                        <p class="text-sm sm:text-base font-semibold text-green-900 mb-1">Auto-Approved</p>
                                        <p class="text-xs sm:text-sm text-green-800">This leave type is automatically approved upon submission if sufficient balance is available.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase">Approved Date</label>
                                    <p class="text-sm sm:text-base text-gray-900 font-semibold mt-1">${app.approvedDate ? formatDate(app.approvedDate) : 'N/A'}</p>
                                </div>
                            </div>
                        ` : `
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-3">
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase">Processed</label>
                                    <p class="text-sm sm:text-base text-gray-900 font-semibold mt-1">By Manager</p>
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase">Processed Date</label>
                                    <p class="text-sm sm:text-base text-gray-900 font-semibold mt-1">${app.approvedDate ? formatDate(app.approvedDate) : 'Pending'}</p>
                                </div>
                            </div>
                            ${app.remarks ? `
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase">Manager Remarks</label>
                                    <p class="text-sm sm:text-base text-gray-900 mt-2 p-3 sm:p-4 bg-${app.status === 'approved' ? 'green' : 'red'}-50 rounded-lg border border-${app.status === 'approved' ? 'green' : 'red'}-200 break-words">${app.remarks}</p>
                                </div>
                            ` : ''}
                        `}
                    </div>
                ` : ''}
            </div>
        `;

        document.getElementById('detailsModal').classList.remove('hidden');
    };

    // Close modal
    window.closeModal = function() {
        document.getElementById('detailsModal').classList.add('hidden');
    };

    // Event listeners
    statusFilter.addEventListener('change', filterApplications);
    leaveTypeFilter.addEventListener('change', filterApplications);
    searchInput.addEventListener('input', filterApplications);

    // Close modal on outside click
    document.getElementById('detailsModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });

    // Initialize
    updateBalanceDisplay();
    updateStats(leaveApplications);
    renderApplications(leaveApplications);
});
</script>

<style>
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-fade-in-down {
        animation: fadeInDown 0.3s ease-out;
    }

    /* Responsive adjustments for very small screens */
    @media (max-width: 640px) {
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    }

    /* Ensure touch-friendly buttons on mobile */
    @media (max-width: 768px) {
        button, a {
            min-height: 44px;
            min-width: 44px;
        }
    }
</style>
@endsection