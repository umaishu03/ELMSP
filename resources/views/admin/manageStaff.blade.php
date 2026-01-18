@extends('layouts.admin')

@section('title', 'Staff Management')

@section('content')
<!-- Breadcrumbs -->
<div class="mb-6">
    {!! \App\Helpers\BreadcrumbHelper::render() !!}
</div>

<div class="mb-8">
    <h1 class="text-4xl font-bold text-gray-800 mb-2">Staff Management</h1>
</div>
<!-- Search and Action Buttons Section -->
<div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4">
    <!-- Search Bar -->
    <div class="relative flex-1 max-w-md">
        <input type="text" 
               id="staffSearchInput"
               placeholder="Search by name, email, ID, department, or role..." 
               class="w-full pl-4 pr-10 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
        </div>
    </div>
    
     <!-- Action Buttons -->
     <div class="flex flex-col gap-3">
         <!-- Download Staff List Button -->
         <a href="{{ route('admin.staff.download-template') }}" 
            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center">
             <i class="fas fa-download mr-2"></i>
             Download Staff List
         </a>
         
         <!-- Upload CSV Form -->
         <form action="{{ route('admin.register') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-3">
             @csrf
             <div class="flex items-center">
                 <input type="file" 
                        name="csv_file" 
                        id="csv_file" 
                        accept=".csv" 
                        required
                        class="hidden">
                 <label for="csv_file" 
                        class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center cursor-pointer">
                     Choose File
                 </label>
                 <span id="file-name" class="ml-3 text-gray-600">No file chosen</span>
             </div>
             <button type="submit" 
                     class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                 <i class="fas fa-upload mr-2"></i>
                 Upload CSV
             </button>
         </form>
     </div>
</div>


<!-- Success/Error Messages -->
@if(session('success'))
    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
        <div class="flex items-start">
            <i class="fas fa-check-circle mr-2 mt-1"></i>
            <div>{!! session('success') !!}</div>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        <div class="flex items-start">
            <i class="fas fa-exclamation-circle mr-2 mt-1"></i>
            <div>{!! session('error') !!}</div>
        </div>
    </div>
@endif



<!-- Section Title -->
<h1 class="text-2xl font-bold text-gray-800 mb-6">List Staff</h1>

<!-- Staff Table -->
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <!-- Table Header -->
            <thead class="bg-blue-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                        Staff Name
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                        Email
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                        ID
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                        Department
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                        Role
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                        Action
                    </th>
                </tr>
            </thead>
            
            <!-- Table Body -->
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $index => $user)
                <tr class="hover:bg-gray-50 staff-row {{ $index >= 5 ? 'hidden staff-row-all' : '' }}" 
                    data-search-name="{{ strtolower($user->name) }}"
                    data-search-email="{{ strtolower($user->email) }}"
                    data-search-id="{{ $user->staff ? strtolower($user->staff->employee_id) : 'n/a' }}"
                    data-search-department="{{ $user->staff ? strtolower($user->staff->department) : ($user->role === 'admin' ? 'admin' : 'n/a') }}"
                    data-search-role="{{ strtolower($user->role) }}">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $user->email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">
                            @if($user->staff)
                                {{ $user->staff->employee_id }}
                            @elseif($user->role === 'admin')
                                N/A
                            @else
                                N/A
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($user->staff)
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ \App\Http\Controllers\StaffController::getDepartmentColor($user->staff->department) }}">
                                {{ ucfirst($user->staff->department) }}
                            </span>
                        @elseif($user->role === 'admin')
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                Admin
                            </span>
                        @else
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                N/A
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $user->role === 'admin' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <button class="view-staff-btn text-blue-600 hover:text-blue-900" 
                                    title="View"
                                    data-user-id="{{ $user->id }}">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="edit-staff-btn text-yellow-600 hover:text-yellow-900" 
                                    title="Edit"
                                    data-user-id="{{ $user->id }}">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="text-red-600 hover:text-red-900 delete-staff-btn" 
                                    title="Delete"
                                    data-user-id="{{ $user->id }}"
                                    data-user-name="{{ $user->name }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        No staff members found. Upload a CSV file to add staff members.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Load More Indicator -->
    @if($users->count() > 5)
    <div class="px-6 py-4 bg-gray-50 text-center">
        <button id="toggleStaffList" class="text-gray-500 hover:text-gray-700 transition-transform duration-200">
            <i class="fas fa-chevron-down" id="chevronIcon"></i>
            <span class="ml-2 text-sm" id="toggleText">Show All Staff ({{ $users->count() - 5 }} more)</span>
        </button>
    </div>
    @endif
</div>

<!-- Department Legend -->
<div class="mt-6 bg-white rounded-lg shadow-lg p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Department Categories</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="flex items-center">
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 mr-2">Manager</span>
            <span class="text-sm text-gray-600">Management</span>
        </div>
        <div class="flex items-center">
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 mr-2">Supervisor</span>
            <span class="text-sm text-gray-600">Supervision</span>
        </div>
        <div class="flex items-center">
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 mr-2">Cashier</span>
            <span class="text-sm text-gray-600">Payment</span>
        </div>
        <div class="flex items-center">
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800 mr-2">Barista</span>
            <span class="text-sm text-gray-600">Beverages</span>
        </div>
        <div class="flex items-center">
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 mr-2">Waiter</span>
            <span class="text-sm text-gray-600">Service</span>
        </div>
        <div class="flex items-center">
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 mr-2">Kitchen</span>
            <span class="text-sm text-gray-600">Food Prep</span>
        </div>
        <div class="flex items-center">
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-cyan-100 text-cyan-800 mr-2">Joki</span>
            <span class="text-sm text-gray-600">Runner</span>
        </div>
    </div>
</div>

<!-- View Staff Modal -->
<div id="viewStaffModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center" onclick="if(event.target === this) closeViewModal()">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4 flex justify-between items-center">
            <h2 class="text-xl font-bold text-white">Staff Details</h2>
            <button onclick="closeViewModal()" class="text-white hover:text-gray-200">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <div class="p-6" id="viewStaffContent">
            <div class="flex justify-center items-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Staff Modal -->
<div id="editStaffModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center" onclick="if(event.target === this) closeEditModal()">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 px-6 py-4 flex justify-between items-center">
            <h2 class="text-xl font-bold text-white">Edit Staff</h2>
            <button onclick="closeEditModal()" class="text-white hover:text-gray-200">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <div class="p-6" id="editStaffContent">
            <div class="flex justify-center items-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-yellow-600"></div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteStaffModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center backdrop-blur-sm transition-opacity duration-300" onclick="if(event.target === this) closeDeleteModal()">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-4 transform transition-all scale-95" onclick="event.stopPropagation()">
        <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-5 rounded-t-xl">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-white bg-opacity-25 rounded-full p-2.5 mr-3">
                    <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                </div>
                <h2 class="text-xl font-bold text-white">Confirm Deletion</h2>
            </div>
        </div>
        <div class="p-6">
            <div class="flex items-start mb-6">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-14 w-14 rounded-full bg-red-100 ring-4 ring-red-50">
                        <i class="fas fa-trash-alt text-red-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4 flex-1 pt-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete Staff Member?</h3>
                    <p class="text-sm text-gray-600 mb-2 leading-relaxed">
                        Are you sure you want to delete <span id="deleteStaffName" class="font-semibold text-gray-900"></span>?
                    </p>
                    <div class="flex items-center text-sm text-red-600 font-medium bg-red-50 px-3 py-2 rounded-md">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span>This action cannot be undone.</span>
                    </div>
                </div>
            </div>
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <button type="button" onclick="closeDeleteModal()" 
                        class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all duration-200 font-medium border border-gray-300">
                    <i class="fas fa-times mr-2"></i>Cancel
                </button>
                <button type="button" id="confirmDeleteBtn" onclick="confirmDelete()"
                        class="px-6 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all duration-200 font-medium shadow-md hover:shadow-lg transform hover:scale-105 active:scale-95">
                    <i class="fas fa-trash mr-2"></i>Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('csv_file');
    const fileNameSpan = document.getElementById('file-name');
    
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const fileName = this.files[0].name;
            fileNameSpan.textContent = fileName;
            fileNameSpan.classList.remove('text-gray-600');
            fileNameSpan.classList.add('text-gray-800', 'font-medium');
        } else {
            fileNameSpan.textContent = 'No file chosen';
            fileNameSpan.classList.remove('text-gray-800', 'font-medium');
            fileNameSpan.classList.add('text-gray-600');
        }
    });
    
    // Search functionality
    const searchInput = document.getElementById('staffSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const allRows = document.querySelectorAll('.staff-row');
            let visibleCount = 0;
            
            allRows.forEach((row, index) => {
                const name = row.getAttribute('data-search-name') || '';
                const email = row.getAttribute('data-search-email') || '';
                const id = row.getAttribute('data-search-id') || '';
                const department = row.getAttribute('data-search-department') || '';
                const role = row.getAttribute('data-search-role') || '';
                
                // Check if search term matches any field
                const matches = !searchTerm || 
                    name.includes(searchTerm) || 
                    email.includes(searchTerm) || 
                    id.includes(searchTerm) || 
                    department.includes(searchTerm) || 
                    role.includes(searchTerm);
                
                if (searchTerm) {
                    // When searching, show/hide based on match
                    if (matches) {
                        row.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        row.classList.add('hidden');
                    }
                } else {
                    // When not searching, restore original visibility
                    if (index < 5) {
                        row.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        // Rows beyond index 5 should be hidden unless showAllState is true
                        if (showAllState) {
                            row.classList.remove('hidden');
                            visibleCount++;
                        } else {
                            row.classList.add('hidden');
                        }
                    }
                }
            });
            
            // Update "Show All Staff" button visibility and text
            const toggleButton = document.getElementById('toggleStaffList');
            if (toggleButton) {
                if (searchTerm) {
                    // When searching, hide toggle button
                    toggleButton.style.display = 'none';
                } else {
                    // When not searching, show toggle button if there are more than 5 rows
                    const totalRows = allRows.length;
                    if (totalRows > 5) {
                        toggleButton.style.display = 'block';
                        const toggleText = document.getElementById('toggleText');
                        const chevronIcon = document.getElementById('chevronIcon');
                        if (toggleText) {
                            toggleText.textContent = `Show All Staff (${totalRows - 5} more)`;
                        }
                        if (chevronIcon && !showAllState) {
                            chevronIcon.style.transform = 'rotate(0deg)';
                        }
                    } else {
                        toggleButton.style.display = 'none';
                    }
                }
            }
            
            // Show "No results" message if no matches
            const tbody = document.querySelector('tbody');
            let noResultsRow = tbody.querySelector('.no-results-row');
            
            if (visibleCount === 0 && searchTerm) {
                if (!noResultsRow) {
                    noResultsRow = document.createElement('tr');
                    noResultsRow.className = 'no-results-row';
                    noResultsRow.innerHTML = '<td colspan="6" class="px-6 py-4 text-center text-gray-500">No staff members found matching your search.</td>';
                    tbody.appendChild(noResultsRow);
                }
            } else if (noResultsRow) {
                noResultsRow.remove();
            }
        });
        
    }
    
    // Close modals on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeViewModal();
            closeEditModal();
            closeDeleteModal();
        }
    });
    
    // Toggle staff list functionality - declare showAllState globally
    let showAllState = false;
    const toggleStaffListBtn = document.getElementById('toggleStaffList');
    if (toggleStaffListBtn) {
        toggleStaffListBtn.addEventListener('click', function() {
            // Don't toggle if search is active
            const searchInput = document.getElementById('staffSearchInput');
            if (searchInput && searchInput.value.trim()) {
                return;
            }
            
            const allRows = document.querySelectorAll('.staff-row-all');
            const chevronIcon = document.getElementById('chevronIcon');
            const toggleText = document.getElementById('toggleText');
            
            showAllState = !showAllState;
            
            if (showAllState) {
                // Show all rows
                allRows.forEach(row => {
                    row.classList.remove('hidden');
                });
                chevronIcon.style.transform = 'rotate(180deg)';
                toggleText.textContent = 'Show Less';
            } else {
                // Hide rows beyond first 5
                allRows.forEach(row => {
                    row.classList.add('hidden');
                });
                chevronIcon.style.transform = 'rotate(0deg)';
                toggleText.textContent = `Show All Staff (${allRows.length} more)`;
            }
        });
    }

    // View staff functionality
    const viewButtons = document.querySelectorAll('.view-staff-btn');
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            openViewModal(userId);
        });
    });

    // Edit staff functionality
    const editButtons = document.querySelectorAll('.edit-staff-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            openEditModal(userId);
        });
    });

    // Delete staff functionality
    const deleteButtons = document.querySelectorAll('.delete-staff-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const userName = this.getAttribute('data-user-name');
            openDeleteModal(userId, userName);
        });
    });
});

// View Modal Functions
function openViewModal(userId) {
    const modal = document.getElementById('viewStaffModal');
    const content = document.getElementById('viewStaffContent');
    
    modal.classList.remove('hidden');
    content.innerHTML = '<div class="flex justify-center items-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>';
    
    // Fetch staff data
    fetch(`/admin/staff/${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                content.innerHTML = `<div class="text-red-600 text-center py-8">${data.error}</div>`;
                return;
            }
            
            const user = data.user;
            const staff = user.staff || user.admin;
            const isAdmin = user.role === 'admin';
            
            let html = `
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <div class="text-gray-900 font-semibold">${user.name || 'N/A'}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <div class="text-gray-900">${user.email || 'N/A'}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Employee ID</label>
                                <div class="text-gray-900">${staff && staff.employee_id ? staff.employee_id : 'N/A'}</div>
                            </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${user.role === 'admin' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'}">
                                ${user.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : 'N/A'}
                            </span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getDepartmentColorClass(staff && staff.department ? staff.department : '')}">
                                ${staff && staff.department ? staff.department.charAt(0).toUpperCase() + staff.department.slice(1) : 'N/A'}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${staff && staff.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                ${staff && staff.status ? staff.status.charAt(0).toUpperCase() + staff.status.slice(1) : 'N/A'}
                            </span>
                        </div>
                    </div>
                    ${isAdmin ? `
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Admin Level</label>
                        <div class="text-gray-900">${staff && staff.admin_level ? staff.admin_level.replace('_', ' ').replace(/\\b\\w/g, l => l.toUpperCase()) : 'N/A'}</div>
                    </div>
                    ` : ''}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">${isAdmin ? 'Appointment Date' : 'Hire Date'}</label>
                            <div class="text-gray-900">${getDateDisplay(staff, isAdmin)}</div>
                        </div>
                        ${!isAdmin ? `
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Salary</label>
                            <div class="text-gray-900">${staff && staff.salary ? 'RM ' + parseFloat(staff.salary).toFixed(2) : 'N/A'}</div>
                        </div>
                        ` : ''}
                    </div>
                    ${user.phone ? `
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <div class="text-gray-900">${user.phone}</div>
                    </div>
                    ` : ''}
                    ${user.address ? `
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <div class="text-gray-900">${user.address}</div>
                    </div>
                    ` : ''}
                </div>
            `;
            
            content.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<div class="text-red-600 text-center py-8">Error loading staff details. Please try again.</div>';
        });
}

function closeViewModal() {
    document.getElementById('viewStaffModal').classList.add('hidden');
}

// Edit Modal Functions
function openEditModal(userId) {
    const modal = document.getElementById('editStaffModal');
    const content = document.getElementById('editStaffContent');
    
    modal.classList.remove('hidden');
    content.innerHTML = '<div class="flex justify-center items-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-yellow-600"></div></div>';
    
    // Fetch staff data
    fetch(`/admin/staff/${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                content.innerHTML = `<div class="text-red-600 text-center py-8">${data.error}</div>`;
                return;
            }
            
            const user = data.user;
            const staff = user.staff || user.admin;
            const isAdmin = user.role === 'admin';
            
            const departments = ['manager', 'supervisor', 'cashier', 'barista', 'waiter', 'kitchen', 'joki'];
            const statuses = ['active', 'inactive'];
            const adminLevels = ['super_admin', 'admin', 'manager'];
            
            let html = `
                <form id="editStaffForm" onsubmit="submitEditForm(event, ${userId})">
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" value="${user.name || ''}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                                <input type="email" name="email" value="${user.email || ''}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Employee ID <span class="text-red-500">*</span></label>
                                <input type="text" name="employee_id" value="${staff && staff.employee_id ? staff.employee_id : ''}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Department <span class="text-red-500">*</span></label>
                                <select name="department" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                    <option value="">Select Department</option>
                                    ${departments.map(dept => `
                                        <option value="${dept}" ${staff && staff.department === dept ? 'selected' : ''}>
                                            ${dept.charAt(0).toUpperCase() + dept.slice(1)}
                                        </option>
                                    `).join('')}
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                                <select name="status" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                    ${statuses.map(status => `
                                        <option value="${status}" ${staff && staff.status === status ? 'selected' : ''}>
                                            ${status.charAt(0).toUpperCase() + status.slice(1)}
                                        </option>
                                    `).join('')}
                                </select>
                            </div>
                            ${isAdmin ? `
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Admin Level</label>
                                <select name="admin_level"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                    ${adminLevels.map(level => `
                                        <option value="${level}" ${staff && staff.admin_level === level ? 'selected' : ''}>
                                            ${level.replace('_', ' ').replace(/\\b\\w/g, l => l.toUpperCase())}
                                        </option>
                                    `).join('')}
                                </select>
                            </div>
                            ` : ''}
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">${isAdmin ? 'Appointment Date' : 'Hire Date'}</label>
                                <input type="date" name="${isAdmin ? 'appointment_date' : 'hire_date'}" 
                                       value="${getDateInputValue(staff, isAdmin)}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            </div>
                            ${!isAdmin ? `
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Salary</label>
                                <input type="number" name="salary" step="0.01" value="${staff && staff.salary ? staff.salary : ''}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            </div>
                            ` : ''}
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input type="text" name="phone" value="${user.phone || ''}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                <select name="role" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                    <option value="staff" ${user.role === 'staff' ? 'selected' : ''}>Staff</option>
                                    <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Admin</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <textarea name="address" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">${user.address || ''}</textarea>
                        </div>
                        <div class="flex justify-end space-x-3 pt-4 border-t">
                            <button type="button" onclick="closeEditModal()" 
                                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition">
                                Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            `;
            
            content.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<div class="text-red-600 text-center py-8">Error loading staff details. Please try again.</div>';
        });
}

function closeEditModal() {
    document.getElementById('editStaffModal').classList.add('hidden');
}

// Delete Modal Functions
let deleteUserId = null;

function openDeleteModal(userId, userName) {
    deleteUserId = userId;
    const modal = document.getElementById('deleteStaffModal');
    const modalContent = modal.querySelector('.transform');
    const nameSpan = document.getElementById('deleteStaffName');
    
    nameSpan.textContent = `"${userName}"`;
    modal.classList.remove('hidden');
    
    // Add animation
    setTimeout(() => {
        modalContent.classList.remove('scale-95');
        modalContent.classList.add('scale-100');
    }, 10);
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteStaffModal');
    modal.classList.add('hidden');
    deleteUserId = null;
}

function confirmDelete() {
    if (!deleteUserId) return;
    
    // Create and submit delete form
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/staff/${deleteUserId}`;
    
    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);
    
    // Add method override for DELETE
    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'DELETE';
    form.appendChild(methodField);
    
    // Submit form
    document.body.appendChild(form);
    form.submit();
}

function submitEditForm(event, userId) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    formData.append('_method', 'PUT');
    formData.append('_token', '{{ csrf_token() }}');
    
    fetch(`/admin/staff/${userId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Staff updated successfully!');
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to update staff'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating staff. Please try again.');
    });
}

function getDepartmentColorClass(department) {
    const colors = {
        'manager': 'bg-purple-100 text-purple-800',
        'supervisor': 'bg-blue-100 text-blue-800',
        'cashier': 'bg-yellow-100 text-yellow-800',
        'barista': 'bg-orange-100 text-orange-800',
        'joki': 'bg-cyan-100 text-cyan-800',
        'waiter': 'bg-green-100 text-green-800',
        'kitchen': 'bg-red-100 text-red-800',
    };
    return colors[(department || '').toLowerCase()] || 'bg-gray-100 text-gray-800';
}

function getDateDisplay(staff, isAdmin) {
    if (!staff) return 'N/A';
    const dateField = isAdmin ? staff.appointment_date : staff.hire_date;
    if (!dateField) return 'N/A';
    try {
        return new Date(dateField).toLocaleDateString();
    } catch (e) {
        return dateField;
    }
}

function getDateInputValue(staff, isAdmin) {
    if (!staff) return '';
    const dateField = isAdmin ? staff.appointment_date : staff.hire_date;
    if (!dateField) return '';
    try {
        return new Date(dateField).toISOString().split('T')[0];
    } catch (e) {
        return '';
    }
}
</script>
@endsection
