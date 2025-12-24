@extends('layouts.admin')

@section('title', 'Staff Management')

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
               placeholder="Search" 
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
                @forelse($users as $user)
                <tr class="hover:bg-gray-50">
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
                            @elseif($user->admin)
                                {{ $user->admin->employee_id }}
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
                        @elseif($user->admin)
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ \App\Http\Controllers\StaffController::getDepartmentColor($user->admin->department) }}">
                                {{ ucfirst($user->admin->department) }}
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
                            <button class="text-blue-600 hover:text-blue-900" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="text-yellow-600 hover:text-yellow-900" title="Edit">
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
    <div class="px-6 py-4 bg-gray-50 text-center">
        <button class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-chevron-down"></i>
        </button>
    </div>
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

    // Delete staff functionality
    const deleteButtons = document.querySelectorAll('.delete-staff-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const userName = this.getAttribute('data-user-name');
            
            // Show confirmation dialog
            if (confirm(`Are you sure you want to delete "${userName}"? This action cannot be undone.`)) {
                // Create and submit delete form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/staff/${userId}`;
                
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
        });
    });
});
</script>
@endsection
