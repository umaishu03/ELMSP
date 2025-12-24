@extends('layouts.staff')
@section('title', 'Leave Status')
@section('content')
<!-- Success Toast Message -->
@if($message = Session::get('success'))
<div id="successToast" class="fixed top-6 right-6 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 z-50 animate-fade-in-down">
    <div class="flex items-center gap-3">
        <i class="fas fa-check-circle text-xl"></i>
        <div>
            <p class="font-semibold">Leave Request Submitted!</p>
            <p class="text-sm text-green-100">{{ $message }}</p>
        </div>
    </div>
    <button onclick="document.getElementById('successToast').remove()" class="ml-4 text-white hover:text-green-100">
        <i class="fas fa-times"></i>
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

<div class="max-w-7xl mx-auto mt-8 mb-12">
    <!-- Header Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-purple-600 to-purple-800 px-8 py-6">
            <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                <i class="fas fa-clipboard-list"></i>
                Leave Status
            </h1>
            <p class="text-purple-100 mt-2">Track and manage all your leave applications</p>
        </div>
    

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
    <!-- Leave Balance Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
        <!-- Annual Leave -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-700">Annual Leave</h3>
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-blue-600 text-sm"></i>
                </div>
            </div>
            <div class="space-y-1">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-600">Balance</span>
                    <span class="font-bold text-blue-600" id="annualBalance">{{ isset($leaveBalance['annual']['balance']) ? $leaveBalance['annual']['balance'] . ' days' : '0 days' }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Max</span>
                    <span class="text-gray-700">14 days</span>
                </div>
            </div>
        </div>

        <!-- Medical Leave -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-700">Medical Leave</h3>
                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-clinic-medical text-red-600 text-sm"></i>
                </div>
            </div>
            <div class="space-y-1">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-600">Balance</span>
                    <span class="font-bold text-red-600" id="medicalBalance">{{ isset($leaveBalance['medical']['balance']) ? $leaveBalance['medical']['balance'] . ' days' : '0 days' }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Max</span>
                    <span class="text-gray-700">14 days</span>
                </div>
            </div>
        </div>

        <!-- Hospitalization Leave -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-700">Hospitalization</h3>
                <div class="w-8 h-8 bg-pink-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-hospital text-pink-600 text-sm"></i>
                </div>
            </div>
            <div class="space-y-1">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-600">Balance</span>
                    <span class="font-bold text-pink-600" id="hospitalizationBalance">{{ isset($leaveBalance['hospitalization']['balance']) ? $leaveBalance['hospitalization']['balance'] . ' days' : '0 days' }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Max</span>
                    <span class="text-gray-700">30 days</span>
                </div>
            </div>
        </div>

        <!-- Emergency Leave -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-700">Emergency</h3>
                <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-orange-600 text-sm"></i>
                </div>
            </div>
            <div class="space-y-1">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-600">Balance</span>
                    <span class="font-bold text-orange-600" id="emergencyBalance">{{ isset($leaveBalance['emergency']['balance']) ? $leaveBalance['emergency']['balance'] . ' days' : '0 days' }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Max</span>
                    <span class="text-gray-700">7 days</span>
                </div>
            </div>
        </div>

        <!-- Replacement Leave -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-700">Replacement</h3>
                <div class="w-8 h-8 bg-teal-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-exchange-alt text-teal-600 text-sm"></i>
                </div>
            </div>
            <div class="space-y-1">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-600">Balance</span>
                    <span class="font-bold text-teal-600" id="replacementBalance">{{ isset($leaveBalance['replacement']['balance']) ? $leaveBalance['replacement']['balance'] . ' days' : '0 days' }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">OT Hours</span>
                    <span class="text-gray-700" id="otHours">{{ isset($leaveBalance['replacement']['ot_hours']) ? $leaveBalance['replacement']['ot_hours'] . 'h' : (isset($leaveBalance['replacement']['max']) ? ($leaveBalance['replacement']['max']*8) . 'h' : '0h') }}</span>
                </div>
            </div>
        </div>

        <!-- Marriage Leave -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-700">Marriage</h3>
                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-heart text-purple-600 text-sm"></i>
                </div>
            </div>
            <div class="space-y-1">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-600">Balance</span>
                    <span class="font-bold text-purple-600" id="marriageBalance">{{ isset($leaveBalance['marriage']['balance']) ? ($leaveBalance['marriage']['balance'] === 0 ? 'Used' : 'Available') : 'Available' }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Max</span>
                    <span class="text-gray-700">One-time</span>
                </div>
            </div>
        </div>

        <!-- Unpaid Leave -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-700">Unpaid</h3>
                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-minus-circle text-gray-600 text-sm"></i>
                </div>
            </div>
            <div class="space-y-1">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-600">Available</span>
                    <span class="font-bold text-gray-600">Unlimited</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Status</span>
                    <span class="text-gray-700">No limit</span>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <!-- Total Applications -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Applications</p>
                    <p class="text-3xl font-bold text-gray-900" id="totalApplications">0</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-file-alt text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Pending -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Pending</p>
                    <p class="text-3xl font-bold text-amber-600" id="pendingCount">0</p>
                </div>
                <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-clock text-amber-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Approved -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Approved</p>
                    <p class="text-3xl font-bold text-green-600" id="approvedCount">0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Rejected -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Rejected</p>
                    <p class="text-3xl font-bold text-red-600" id="rejectedCount">0</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter and Action Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
            <!-- Filters -->
            <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                <!-- Status Filter -->
                <div class="relative">
                    <select id="statusFilter" class="appearance-none border border-gray-300 rounded-lg px-4 py-2.5 pr-10 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white text-gray-700">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-sm"></i>
                </div>

                <!-- Leave Type Filter -->
                <div class="relative">
                    <select id="leaveTypeFilter" class="appearance-none border border-gray-300 rounded-lg px-4 py-2.5 pr-10 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white text-gray-700">
                        <option value="">All Leave Types</option>
                        <option value="annual">Annual Leave</option>
                        <option value="hospitalization">Hospitalization Leave</option>
                        <option value="medical">Medical Leave</option>
                        <option value="emergency">Emergency Leave</option>
                        <option value="marriage">Marriage Leave</option>
                        <option value="replacement">Replacement Leave</option>
                        <option value="unpaid">Unpaid Leave</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-sm"></i>
                </div>

                <!-- Search -->
                <div class="relative">
                    <input type="text" 
                           id="searchInput" 
                           placeholder="Search applications..." 
                           class="border border-gray-300 rounded-lg pl-10 pr-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent w-full md:w-64">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>

            <!-- New Application Button -->
            <a href="{{ route('staff.leave-application') }}" 
               class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-600 to-purple-800 hover:from-purple-700 hover:to-purple-900 text-white font-semibold px-6 py-2.5 rounded-lg shadow-md hover:shadow-lg transition-all duration-200 w-full md:w-auto justify-center">
                <i class="fas fa-plus"></i>
                <span>New Application</span>
            </a>
        </div>
    </div>

    <!-- Leave Applications List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-800">Leave Applications</h2>
        </div>

        <!-- Applications Container -->
        <div id="applicationsContainer" class="divide-y divide-gray-200">
            <!-- Application items will be inserted here by JavaScript -->
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden p-12 text-center">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-inbox text-gray-400 text-3xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No leave applications found</h3>
            <p class="text-gray-500 mb-6">You haven't submitted any leave applications yet</p>
            <a href="{{ route('staff.leave-application') }}" 
               class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-lg transition-all">
                <i class="fas fa-plus"></i>
                <span>Create New Application</span>
            </a>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div id="detailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-800 px-6 py-4 flex items-center justify-between">
            <h3 class="text-xl font-bold text-white">Application Details</h3>
            <button onclick="closeModal()" class="text-white hover:text-gray-200 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]" id="modalContent">
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
            'attachment' => $leave->attachment
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
        document.getElementById('replacementBalance').textContent = `${leaveBalance.replacement?.balance || 0} days`;
        document.getElementById('otHours').textContent = `${(leaveBalance.replacement?.ot_hours || 0).toFixed(0)}h`;
        document.getElementById('marriageBalance').textContent = leaveBalance.marriage?.balance === 0 ? 'Used' : 'Available';
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
            <div class="p-6 hover:bg-gray-50 transition-all cursor-pointer" onclick="showDetails(${app.id})">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="px-3 py-1 rounded-lg text-xs font-semibold ${getLeaveTypeColor(app.leaveType)}">
                                ${app.leaveTypeName}
                            </span>
                            ${getStatusBadge(app.status)}
                            ${app.autoApproved && app.status === 'approved' ? '<span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium bg-green-50 text-green-700 border border-green-200"><i class="fas fa-bolt text-xs"></i> Auto</span>' : ''}
                        </div>
                        <div class="flex items-center gap-2 text-gray-600 mb-2">
                            <i class="fas fa-calendar text-purple-600"></i>
                            <span class="font-medium">${formatDate(app.startDate)} - ${formatDate(app.endDate)}</span>
                            <span class="text-sm text-gray-500">(${app.days} ${app.days === 1 ? 'day' : 'days'})</span>
                        </div>
                        <p class="text-sm text-gray-600 line-clamp-2">${app.reason}</p>
                        <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                            <span><i class="fas fa-clock mr-1"></i>Applied: ${formatDate(app.appliedDate)}</span>
                            ${app.attachment ? '<span><i class="fas fa-paperclip mr-1"></i>Attachment</span>' : ''}
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="event.stopPropagation(); showDetails(${app.id})" 
                                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-medium transition-all">
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
            <div class="space-y-6">
                <!-- Status and Leave Type -->
                <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                    <div>
                        <span class="px-4 py-2 rounded-lg text-sm font-semibold ${getLeaveTypeColor(app.leaveType)}">
                            ${app.leaveTypeName}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        ${getStatusBadge(app.status)}
                        ${app.autoApproved && app.status === 'approved' ? '<span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium bg-green-50 text-green-700 border border-green-200"><i class="fas fa-bolt text-xs"></i> Auto-Approved</span>' : ''}
                    </div>
                </div>

                <!-- Application Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">Start Date</label>
                        <p class="text-gray-900 font-semibold mt-1">${formatDate(app.startDate)}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">End Date</label>
                        <p class="text-gray-900 font-semibold mt-1">${formatDate(app.endDate)}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">Duration</label>
                        <p class="text-gray-900 font-semibold mt-1">${app.days} ${app.days === 1 ? 'day' : 'days'}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">Applied Date</label>
                        <p class="text-gray-900 font-semibold mt-1">${formatDate(app.appliedDate)}</p>
                    </div>
                </div>

                <!-- Reason -->
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase">Reason</label>
                    <p class="text-gray-900 mt-2 p-4 bg-gray-50 rounded-lg">${app.reason}</p>
                </div>

                ${app.attachment ? `
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">Attachment</label>
                        <div class="mt-2 flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
                            <i class="fas fa-file-pdf text-red-500 text-xl"></i>
                            <span class="text-sm text-gray-700">${app.attachment}</span>
                            <a href="${app.attachment}" download class="ml-auto text-purple-600 hover:text-purple-700 text-sm font-medium">
                                <i class="fas fa-download mr-1"></i>Download
                            </a>
                        </div>
                    </div>
                ` : ''}

                ${app.status !== 'pending' ? `
                    <div class="border-t border-gray-200 pt-4">
                        ${app.autoApproved ? `
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-check-circle text-green-600 text-xl mt-0.5"></i>
                                    <div>
                                        <p class="font-semibold text-green-900 mb-1">Auto-Approved</p>
                                        <p class="text-sm text-green-800">This leave type is automatically approved upon submission. All leave types except Replacement Leave are auto-approved if sufficient balance is available.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase">Approved Date</label>
                                    <p class="text-gray-900 font-semibold mt-1">${app.approvedDate ? formatDate(app.approvedDate) : 'N/A'}</p>
                                </div>
                            </div>
                        ` : `
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase">Processed</label>
                                    <p class="text-gray-900 font-semibold mt-1">By Manager</p>
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase">Processed Date</label>
                                    <p class="text-gray-900 font-semibold mt-1">${app.approvedDate ? formatDate(app.approvedDate) : 'Pending'}</p>
                                </div>
                            </div>
                            ${app.remarks ? `
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase">Manager Remarks</label>
                                    <p class="text-gray-900 mt-2 p-4 bg-${app.status === 'approved' ? 'green' : 'red'}-50 rounded-lg border border-${app.status === 'approved' ? 'green' : 'red'}-200">${app.remarks}</p>
                                </div>
                            ` : ''}
                        `}
                    </div>
                ` : `
                    ${app.leaveType === 'replacement' ? `
                        <div class="border-t border-gray-200 pt-4">
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-clock text-amber-600 text-xl mt-0.5"></i>
                                    <div>
                                        <p class="font-semibold text-amber-900 mb-1">Pending Manager Approval</p>
                                        <p class="text-sm text-amber-800">Replacement leave requires manager verification of overtime hours before approval. This is the only leave type that requires manual approval.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ` : ''}
                `}
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
</style>
@endsection