@extends('layouts.staff')
@section('title', 'Overtime Status')
@section('content')
<!-- Breadcrumbs -->
<div class="mb-6">
    {!! \App\Helpers\BreadcrumbHelper::render() !!}
</div>

<div class="max-w-7xl mx-auto mt-8 mb-12 space-y-6">
    <!-- ========== OVERTIME STATUS SECTION ========== -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 px-4 sm:px-6 md:px-8 py-4 sm:py-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-2 sm:gap-3">
                <i class="fas fa-list-check"></i>
                <span class="break-words">Overtime Application Status</span>
            </h1>
            <p class="text-indigo-100 mt-2 text-sm sm:text-base">Track your overtime applications and approval status</p>
        </div>

        <!-- Filter Section -->
        <div class="px-4 sm:px-6 md:px-8 py-4 bg-gray-50 border-b border-gray-100">
            <div class="flex flex-col sm:flex-row gap-4 items-stretch sm:items-end">
                <div class="flex-1 w-full sm:w-auto">
                    <label class="block text-xs font-medium text-gray-600 mb-2">Filter by Status</label>
                    <select id="statusFilter" class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="flex-1 w-full sm:w-auto">
                    <label class="block text-xs font-medium text-gray-600 mb-2">Filter by Type</label>
                    <select id="typeFilter" class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
                        <option value="all">All Types</option>
                        <option value="fulltime">Fulltime</option>
                        <option value="public_holiday">Public Holiday</option>
                    </select>
                </div>
                <button type="button" id="refreshStatus" class="w-full sm:w-auto px-4 sm:px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-all flex items-center justify-center gap-2 text-sm sm:text-base">
                    <i class="fas fa-sync-alt"></i>
                    <span>Refresh</span>
                </button>
            </div>
        </div>

        <!-- Status Cards Summary -->
        <div class="px-4 sm:px-6 md:px-8 py-4 sm:py-6 bg-gradient-to-br from-gray-50 to-gray-100">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl p-5 border-l-4 border-yellow-500 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-600 mb-1">Pending</p>
                            <p class="text-3xl font-bold text-yellow-600" id="pendingCount">{{ $overtimes->where('status','pending')->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 border-l-4 border-green-500 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-600 mb-1">Approved</p>
                            <p class="text-3xl font-bold text-green-600" id="approvedCount">{{ $overtimes->where('status','approved')->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 border-l-4 border-red-500 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-600 mb-1">Rejected</p>
                            <p class="text-3xl font-bold text-red-600" id="rejectedCount">{{ $overtimes->where('status','rejected')->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-times-circle text-red-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Applications Table -->
        <div class="px-4 sm:px-6 md:px-8 py-4 sm:py-6">
            <div class="bg-white rounded-lg overflow-hidden border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Date Applied</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">OT Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Hours</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                            </tr>
                        </thead>

                        <tbody id="otStatusTableBody" class="divide-y divide-gray-200">
                            @forelse($overtimes as $app)
                                @php
                                    $type = $app->ot_type;
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors" data-status="{{ $app->status }}" data-type="{{ $type }}">
                                    <td class="px-4 py-4 text-sm text-gray-700">{{ $app->created_at->format('Y-m-d') }}</td>
                                    <td class="px-4 py-4 text-sm font-medium text-gray-900">{{ $app->ot_date->format('Y-m-d') }}</td>
                                    <td class="px-4 py-4">
                                        @if($type === 'fulltime')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"><i class="fas fa-briefcase mr-1"></i>Fulltime</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800"><i class="fas fa-calendar-day mr-1"></i>Public Holiday</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-sm font-semibold text-gray-900">{{ number_format($app->hours,1) }} hrs</td>
                                    <td class="px-4 py-4">
                                        @if($app->status === 'pending')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800"><i class="fas fa-clock mr-1"></i>Pending</span>
                                        @elseif($app->status === 'approved')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i>Approved</span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800"><i class="fas fa-times-circle mr-1"></i>Rejected</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-12">
                                        <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                                        <p class="text-gray-500 font-medium">No overtime applications found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div id="emptyState" class="hidden py-12 text-center">
                    <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                    <p class="text-gray-500 font-medium">No overtime applications found</p>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex items-center justify-between">
                <p class="text-sm text-gray-600">
                    Showing <span class="font-semibold">1-5</span> of <span class="font-semibold">8</span> applications
                </p>
                <div class="flex gap-2">
                    <button class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        <i class="fas fa-chevron-left mr-1"></i> Previous
                    </button>
                    <button class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Next <i class="fas fa-chevron-right ml-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

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
    document.getElementById('refreshStatus').addEventListener('click', applyFilters);
});
</script>

@endsection
