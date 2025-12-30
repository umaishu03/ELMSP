@extends('layouts.admin')
@section('title', 'Staff Leave Status')
@section('content')
<!-- Breadcrumbs -->
<div class="mb-6">
    {!! \App\Helpers\BreadcrumbHelper::render() !!}
</div>

<div class="mb-8">
    <h1 class="text-4xl font-bold text-gray-800 mb-2">Staff Leave Status</h1>
</div>
<!-- Search Section -->
<div class="mb-6">
    <div class="relative max-w-md">
        <input type="text" 
               id="leaveSearchInput"
               placeholder="Search by name, department, or leave type..." 
               class="w-full pl-4 pr-10 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white shadow-sm">
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
        </div>
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
            <tbody id="leaveTableBody" class="bg-white divide-y divide-gray-200">
                @forelse($staffLeaves as $leave)
                <tr class="leave-row hover:bg-blue-50 transition-colors" 
                    data-name="{{ strtolower($leave->user->name ?? 'N/A') }}"
                    data-department="{{ strtolower($leave->user->staff->department ?? 'N/A') }}"
                    data-leave-type="{{ strtolower(str_replace('_', ' ', $leave->leaveType?->type_name ?? 'N/A')) }}">
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
                        <a href="{{ route('admin.leave.attachment', $leave->id) }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 font-medium hover:underline">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('leaveSearchInput');
    const tableBody = document.getElementById('leaveTableBody');
    
    if (!searchInput || !tableBody) return;

    const rows = tableBody.querySelectorAll('.leave-row');
    const emptyStateRow = tableBody.querySelector('tr:not(.leave-row)');
    
    // Create a custom empty state for search results if it doesn't exist
    let searchEmptyState = null;
    if (rows.length > 0) {
        searchEmptyState = document.createElement('tr');
        searchEmptyState.id = 'searchEmptyState';
        searchEmptyState.style.display = 'none';
        searchEmptyState.innerHTML = `
            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                <div class="flex flex-col items-center justify-center">
                    <i class="fas fa-search text-4xl mb-3 text-gray-300"></i>
                    <p class="text-lg font-medium">No leave records found matching your search</p>
                </div>
            </td>
        `;
        tableBody.appendChild(searchEmptyState);
    }

    function filterTable() {
        const searchTerm = searchInput.value.trim().toLowerCase();
        let visibleCount = 0;

        rows.forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const department = row.getAttribute('data-department') || '';
            const leaveType = row.getAttribute('data-leave-type') || '';
            
            const matches = searchTerm === '' || 
                name.includes(searchTerm) || 
                department.includes(searchTerm) || 
                leaveType.includes(searchTerm);

            if (matches) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Show/hide empty state for search results
        if (searchEmptyState) {
            if (visibleCount === 0 && searchTerm !== '') {
                searchEmptyState.style.display = '';
            } else {
                searchEmptyState.style.display = 'none';
            }
        }

        // Hide original empty state when searching
        if (emptyStateRow) {
            if (searchTerm !== '') {
                emptyStateRow.style.display = 'none';
            } else {
                emptyStateRow.style.display = '';
            }
        }
    }

    // Add event listener for search input
    searchInput.addEventListener('input', filterTable);
    searchInput.addEventListener('keyup', filterTable);
});
</script>

@endsection