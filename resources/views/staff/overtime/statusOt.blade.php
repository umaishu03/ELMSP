@extends('layouts.staff')
@section('title', 'Overtime Status')
@section('content')
<!-- Breadcrumbs -->
<div class="mb-6">
    {!! \App\Helpers\BreadcrumbHelper::render() !!}
</div>

<!-- Success Message Popup -->
@if(session('success'))
    <div id="successPopup" class="fixed top-20 right-4 z-[70] animate-slide-in-right">
        <div class="bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 min-w-[300px] max-w-md">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="flex-1">
                <p class="font-semibold">Success!</p>
                <p class="text-sm text-green-50">{{ session('success') }}</p>
            </div>
            <button onclick="document.getElementById('successPopup').remove()" class="flex-shrink-0 text-white hover:text-green-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
@endif

<!-- Error Message Popup -->
@if(session('error'))
    <div id="errorPopup" class="fixed top-20 right-4 z-[70] animate-slide-in-right">
        <div class="bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 min-w-[300px] max-w-md">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="flex-1">
                <p class="font-semibold">Error!</p>
                <p class="text-sm text-red-50">{{ session('error') }}</p>
            </div>
            <button onclick="document.getElementById('errorPopup').remove()" class="flex-shrink-0 text-white hover:text-red-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
@endif

<!-- Title -->
<div class="mb-8">
    <h1 class="text-4xl font-bold text-gray-800 mb-2">Overtime Application Status</h1>
    <p class="text-gray-600 flex items-center gap-2">
        <i class="fas fa-list-check text-blue-500"></i>
        Track your overtime applications and approval status
    </p>
</div>

<div class="space-y-6">
    <!-- ========== OVERTIME STATUS SECTION ========== -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">

        <!-- Status Cards Summary -->
        <div class="px-6 py-6 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-4">
                <i class="fas fa-chart-bar text-indigo-600"></i>
                <span>Application Statistics</span>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="stat-card border-amber-100 bg-amber-50/40">
                    <div class="stat-top">
                        <div class="stat-icon bg-amber-600">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="stat-label">Pending</p>
                            <p class="stat-value text-gray-900" id="pendingCount">{{ $overtimes->where('status','pending')->count() }}</p>
                        </div>
                    </div>
                    <div class="stat-bottom text-amber-700/80">
                        <i class="fas fa-hourglass-half"></i>
                        <span>Awaiting review</span>
                    </div>
                </div>

                <div class="stat-card border-green-100 bg-green-50/40">
                    <div class="stat-top">
                        <div class="stat-icon bg-green-600">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="stat-label">Approved</p>
                            <p class="stat-value text-gray-900" id="approvedCount">{{ $overtimes->where('status','approved')->count() }}</p>
                        </div>
                    </div>
                    <div class="stat-bottom text-green-700/80">
                        <i class="fas fa-thumbs-up"></i>
                        <span>Successfully approved</span>
                    </div>
                </div>

                <div class="stat-card border-red-100 bg-red-50/40">
                    <div class="stat-top">
                        <div class="stat-icon bg-red-600">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="stat-label">Rejected</p>
                            <p class="stat-value text-gray-900" id="rejectedCount">{{ $overtimes->where('status','rejected')->count() }}</p>
                        </div>
                    </div>
                    <div class="stat-bottom text-red-700/80">
                        <i class="fas fa-ban"></i>
                        <span>Not approved</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="px-6 py-6 bg-white border-b border-gray-100">
            <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center justify-between">
                <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                    <div class="relative flex-1 sm:flex-initial sm:w-auto min-w-0">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5 sm:hidden">Filter by Status</label>
                        <select id="statusFilter" class="appearance-none border border-gray-300 rounded-lg px-4 py-2.5 pr-10 w-full text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white text-gray-700 min-h-[44px]">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs sm:text-sm"></i>
                    </div>
                    <div class="relative flex-1 sm:flex-initial sm:w-auto min-w-0">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5 sm:hidden">Filter by Type</label>
                        <select id="typeFilter" class="appearance-none border border-gray-300 rounded-lg px-4 py-2.5 pr-10 w-full text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white text-gray-700 min-h-[44px]">
                            <option value="all">All Types</option>
                            <option value="fulltime">Fulltime</option>
                            <option value="public_holiday">Public Holiday</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs sm:text-sm"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Applications Table -->
        <div class="px-6 py-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-list-ul text-indigo-600"></i>
                    <span>Overtime Applications</span>
                </h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">OT Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Hours</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>

                    <tbody id="otStatusTableBody" class="divide-y divide-gray-200 bg-white">
                        @forelse($overtimes as $app)
                            @php
                                $type = $app->ot_type;
                            @endphp
                            <tr class="hover:bg-indigo-50 transition-colors" data-status="{{ $app->status }}" data-type="{{ $type }}">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">{{ $app->ot_date->format('M d, Y') }}</td>
                                <td class="px-6 py-4">
                                    @if($type === 'fulltime')
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200"><i class="fas fa-briefcase mr-1.5"></i>Fulltime</span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-purple-100 text-purple-800 border border-purple-200"><i class="fas fa-calendar-day mr-1.5"></i>Public Holiday</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900 whitespace-nowrap">{{ number_format($app->hours,1) }} hrs</td>
                                <td class="px-6 py-4">
                                    @if($app->status === 'pending')
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200"><i class="fas fa-clock mr-1.5"></i>Pending</span>
                                    @elseif($app->status === 'approved')
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-800 border border-green-200"><i class="fas fa-check-circle mr-1.5"></i>Approved</span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-800 border border-red-200"><i class="fas fa-times-circle mr-1.5"></i>Rejected</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-16">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                                        </div>
                                        <p class="text-gray-500 font-medium text-lg mb-2">No overtime applications found</p>
                                        <p class="text-gray-400 text-sm">You haven't submitted any overtime applications yet</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="hidden py-16 text-center">
                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-500 font-medium text-lg mb-2">No overtime applications found</p>
                    <p class="text-gray-400 text-sm">No applications match your current filters</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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

/* Statistics cards styling (consistent with Leave Status page) */
.stat-card{
    border-radius: 1rem;
    border-width: 1px;
    padding: 1rem;
    background: rgba(255,255,255,0.9);
    transition: all .2s ease;
    box-shadow: 0 1px 2px rgba(0,0,0,.04);
}
.stat-card:hover{
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,.06);
}
.stat-top{
    display:flex;
    align-items:center;
    gap: .75rem;
}
.stat-icon{
    width: 44px;
    height: 44px;
    border-radius: .9rem;
    display:flex;
    align-items:center;
    justify-content:center;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 6px 14px rgba(0,0,0,.12);
}
.stat-label{
    font-size: .8rem;
    color: #6b7280;
    font-weight: 600;
    line-height: 1.1rem;
}
.stat-value{
    font-size: 1.75rem;
    font-weight: 800;
    line-height: 2rem;
    margin-top: .15rem;
}
.stat-bottom{
    margin-top: .9rem;
    padding-top: .75rem;
    border-top: 1px dashed rgba(0,0,0,.08);
    display:flex;
    align-items:center;
    gap: .5rem;
    font-size: .8rem;
    font-weight: 600;
}
.stat-bottom i{ opacity: .85; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('typeFilter');
    const tbody = document.getElementById('otStatusTableBody');

    function applyFilters() {
        const status = statusFilter.value;
        const type = typeFilter.value;

        Array.from(tbody.querySelectorAll('tr')).forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            const rowType = row.getAttribute('data-type');

            let show = true;
            if (status !== 'all' && rowStatus !== status) show = false;
            if (type !== 'all' && rowType !== type) show = false;

            row.style.display = show ? '' : 'none';
        });
    }

    statusFilter.addEventListener('change', applyFilters);
    typeFilter.addEventListener('change', applyFilters);

    // Auto-hide success/error popups after 5 seconds
    const successPopup = document.getElementById('successPopup');
    const errorPopup = document.getElementById('errorPopup');
    
    if (successPopup) {
        setTimeout(() => {
            successPopup.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
            successPopup.style.opacity = '0';
            successPopup.style.transform = 'translateX(100%)';
            setTimeout(() => successPopup.remove(), 300);
        }, 5000);
    }
    
    if (errorPopup) {
        setTimeout(() => {
            errorPopup.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
            errorPopup.style.opacity = '0';
            errorPopup.style.transform = 'translateX(100%)';
            setTimeout(() => errorPopup.remove(), 300);
        }, 5000);
    }
});
</script>

@endsection
