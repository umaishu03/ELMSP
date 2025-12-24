@extends('layouts.admin')
@section('title', 'Staff Leave Status')
@section('content')
<!-- Breadcrumbs -->
<div class="mb-6">
    {!! \App\Helpers\BreadcrumbHelper::render() !!}
</div>

<!-- Search and Action Buttons Section -->
<div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4">
    <!-- Search Bar -->
    <div class="relative flex-1 max-w-md">
        <input type="text" 
               placeholder="Search by name, department, or leave type..." 
               class="w-full pl-4 pr-10 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white shadow-sm">
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="flex gap-2">
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
            <i class="fas fa-filter mr-2"></i>Filter
        </button>
        <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors shadow-sm">
            <i class="fas fa-file-excel mr-2"></i>Export
        </button>
    </div>
</div>

<!-- Staff Leave Status Table -->
<div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <!-- Table Header -->
            <thead class="bg-blue-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                        Staff Name
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                        Department
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                        Leave Type
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                        Start Date 
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                        End Date
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                        Total Days
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                        Document
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                        Leave Status
                    </th>
                </tr>
            </thead>

            <!-- Table Body -->
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($staffLeaves as $leave)
                <tr class="hover:bg-blue-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="text-sm font-semibold text-gray-900">{{ $leave->user->name ?? 'N/A' }}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-700 font-medium">{{ $leave->user->staff->department ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $leave->getLeaveTypeBadgeClass() }}">
                            {{ ucfirst(str_replace('_', ' ', $leave->leaveType?->type_name ?? 'N/A')) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-700">{{ $leave->start_date->format('d M Y') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-700">{{ $leave->end_date->format('d M Y') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-semibold text-gray-900">{{ $leave->total_days }} {{ $leave->total_days === 1 ? 'day' : 'days' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($leave->attachment)
                        <a href="{{ asset('storage/' . $leave->attachment) }}" class="text-blue-600 hover:text-blue-800 font-medium hover:underline">
                            <i class="fas fa-file-pdf mr-1"></i>View
                        </a>
                        @else
                        <span class="text-gray-400 text-sm">No document</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($leave->status === 'approved')
                            <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-300">
                                <i class="fas fa-check-circle mr-1"></i>Approved
                            </span>
                        @elseif($leave->status === 'pending')
                            <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                <i class="fas fa-clock mr-1"></i>Pending
                            </span>
                        @elseif($leave->status === 'rejected')
                            <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-300">
                                <i class="fas fa-times-circle mr-1"></i>Rejected
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                            <p class="text-lg font-medium">No leave records found</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<div class="mt-6 flex justify-between items-center">
    <div class="text-sm text-gray-600">
        Showing <span class="font-semibold">{{ $staffLeaves->firstItem() ?? 0 }}</span> to <span class="font-semibold">{{ $staffLeaves->lastItem() ?? 0 }}</span> of <span class="font-semibold">{{ $staffLeaves->total() }}</span> results
    </div>
    <div>
        {{ $staffLeaves->links() }}
    </div>
</div>

@endsection