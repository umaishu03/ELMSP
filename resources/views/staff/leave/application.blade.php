@extends('layouts.staff')
@section('title', 'Leave Application')
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
                <i class="fas fa-file-alt"></i>
                Leave Application
            </h1>
            <p class="text-purple-100 mt-2">Submit your leave request with all required details</p>
        </div>
        
        <!-- Important Note -->
        <div class="px-8 py-6 bg-amber-50 border-l-4 border-amber-400">
            <div class="flex items-start gap-3">
                <i class="fas fa-info-circle text-amber-600 text-xl mt-0.5"></i>
                <div>
                    <p class="font-semibold text-amber-900 mb-1">Important Reminder</p>
                    <p class="text-amber-800">The replacement leave only can apply if the OT hours sufficient.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Button -->
    <div class="mb-6 flex justify-end">
        <a href="{{ route('staff.claimOt') }}" 
           class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-purple-600 font-semibold px-6 py-3 rounded-xl border-2 border-purple-600 shadow-sm hover:shadow-md transition-all duration-200">
            <i class="fas fa-exchange-alt"></i>
            <span>Claim Overtime</span>
        </a>
    </div>

    <!-- Main Form Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-8 py-8">
            <form class="space-y-6" id="leaveApplicationForm" method="POST" action="{{ route('staff.leave-application.store') }}" enctype="multipart/form-data">
                @csrf
                <!-- Leave Type -->
                <div class="form-group">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Leave Type <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select id="leaveType" name="leave_type_id" class="w-full appearance-none border border-gray-300 rounded-xl px-4 py-3.5 pr-10 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all bg-white text-gray-700 hover:border-gray-400 @error('leave_type_id') border-red-500 @enderror">
                            <option value="">Select leave type</option>
                            @foreach($leaveTypes as $lt)
                                <option value="{{ $lt->id }}" data-type="{{ $lt->type_name }}">{{ ucfirst(str_replace('_', ' ', $lt->type_name)) }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    </div>
                    @error('leave_type_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Leave Entitlement Card (Hidden by default) -->
                <div id="leaveEntitlementCard" class="hidden">
                    <div class="bg-gradient-to-r from-purple-500 to-purple-700 rounded-2xl p-6 shadow-lg">
                        <h3 class="text-white font-semibold text-lg mb-4" id="leaveTypeName">Leave Category</h3>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <!-- Entitlement -->
                            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 text-center border border-white/20">
                                <div class="text-white/80 text-xs font-medium mb-2">Entitlement</div>
                                <div class="text-white text-2xl font-bold" id="entitlement">0.00</div>
                                <div class="text-white/90 text-xs mt-1">days</div>
                            </div>
                            
                            <!-- Available -->
                            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 text-center border border-white/20">
                                <div class="text-white/80 text-xs font-medium mb-2 flex items-center justify-center gap-1">
                                    Available
                                    <i class="fas fa-plus-circle text-xs"></i>
                                </div>
                                <div class="text-white text-2xl font-bold" id="available">0.00</div>
                                <div class="text-white/90 text-xs mt-1">days</div>
                            </div>
                            
                            <!-- Taken -->
                            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 text-center border border-white/20">
                                <div class="text-white/80 text-xs font-medium mb-2">Taken</div>
                                <div class="text-white text-2xl font-bold" id="taken">0.00</div>
                                <div class="text-white/90 text-xs mt-1">days</div>
                            </div>
                            
                            <!-- Balance -->
                            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 text-center border border-white/20">
                                <div class="text-white/80 text-xs font-medium mb-2">Balance</div>
                                <div class="text-white text-2xl font-bold" id="balance">0.00</div>
                                <div class="text-white/90 text-xs mt-1">days</div>
                            </div>
                        </div>

                        <!-- Special Note for Replacement Leave -->
                        <div id="replacementNote" class="hidden mt-4 bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                            <div class="flex items-start gap-2 text-white">
                                <i class="fas fa-info-circle mt-0.5"></i>
                                <p class="text-sm">Balance is based on your claimed overtime hours converted to replacement leave.</p>
                            </div>
                        </div>

                        <!-- Special Note for Unpaid Leave -->
                        <div id="unpaidNote" class="hidden mt-4 bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                            <div class="flex items-start gap-2 text-white">
                                <i class="fas fa-info-circle mt-0.5"></i>
                                <p class="text-sm">Unpaid leave has a maximum limit of 10 days per year.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Date Selection Card -->
                <div class="form-group">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        Leave Duration <span class="text-red-500">*</span>
                    </label>
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-100">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-2">Start Date</label>
                                <input type="date" 
                                       id="startDate"
                                       name="start_date"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white @error('start_date') border-red-500 @enderror"
                                       value="{{ old('start_date') }}">
                                @error('start_date')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-2">End Date</label>
                                <input type="date" 
                                       id="endDate"
                                       name="end_date"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white @error('end_date') border-red-500 @enderror"
                                       value="{{ old('end_date') }}">
                                @error('end_date')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Days Counter -->
                        <div class="bg-white rounded-lg px-5 py-4 flex items-center justify-between border border-gray-200 shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-calendar-check text-purple-600"></i>
                                </div>
                                <span class="font-medium text-gray-700">Total Leave Days</span>
                            </div>
                            <div class="bg-purple-600 text-white px-4 py-2 rounded-lg font-bold text-lg" id="totalDays">
                                0 days
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reason -->
                <div class="form-group">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Reason <span class="text-red-500">*</span>
                    </label>
                    <textarea name="reason" class="w-full border border-gray-300 rounded-xl px-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none hover:border-gray-400 transition-all @error('reason') border-red-500 @enderror" 
                              rows="4" 
                              placeholder="Please provide a detailed reason for your leave application...">{{ old('reason') }}</textarea>
                    <p class="text-xs text-gray-500 mt-2">Describe your reason for leave</p>
                    @error('reason')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Attachment -->
                <div class="form-group">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Attachment (Optional)
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 hover:border-purple-400 transition-all bg-gray-50 @error('attachment') border-red-500 @enderror">
                        <div class="flex flex-col items-center gap-2">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400"></i>
                            <p class="text-sm font-medium text-gray-600">Upload supporting documents</p>
                            <p class="text-xs text-gray-500">PDF, JPG, PNG up to 10MB</p>
                            <input type="file" 
                                   name="attachment"
                                   class="mt-3 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
                        </div>
                    </div>
                    @error('attachment')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('staff.dashboard') }}"
                            class="flex-1 bg-white hover:bg-gray-50 text-gray-700 font-semibold py-3.5 rounded-xl border-2 border-gray-300 transition-all duration-200 flex items-center justify-center">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="flex-1 bg-gradient-to-r from-purple-600 to-purple-800 hover:from-purple-700 hover:to-purple-900 text-white font-semibold py-3.5 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane"></i>
                        <span>Submit Application</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const leaveTypeSelect = document.getElementById('leaveType');
    const entitlementCard = document.getElementById('leaveEntitlementCard');
    const leaveTypeName = document.getElementById('leaveTypeName');
    const entitlementEl = document.getElementById('entitlement');
    const availableEl = document.getElementById('available');
    const takenEl = document.getElementById('taken');
    const balanceEl = document.getElementById('balance');
    const replacementNote = document.getElementById('replacementNote');
    const unpaidNote = document.getElementById('unpaidNote');
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const totalDaysEl = document.getElementById('totalDays');

    // Real leave balance data from controller
    const leaveBalance = {!! json_encode($leaveBalance ?? []) !!};
    const totalOTHours = {!! json_encode($totalOTHours ?? 0) !!};

    // Define leave entitlements and data
    const leaveData = {
        annual: {
            name: 'ANNUAL LEAVE',
            unlimited: false
        },
        hospitalization: {
            name: 'HOSPITALIZATION LEAVE',
            unlimited: false
        },
        medical: {
            name: 'MEDICAL LEAVE',
            unlimited: false
        },
        emergency: {
            name: 'EMERGENCY LEAVE',
            unlimited: false
        },
        replacement: {
            name: 'REPLACEMENT LEAVE',
            unlimited: false,
            isReplacement: true
        },
        marriage: {
            name: 'MARRIAGE LEAVE',
            unlimited: false
        },
        unpaid: {
            name: 'UNPAID LEAVE',
            unlimited: false
        }
    };

    // Handle leave type change
    leaveTypeSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const selectedType = selectedOption ? selectedOption.dataset.type : null;

        if (selectedType) {
            const data = leaveData[selectedType];
            const balance = leaveBalance[selectedType] || {};
            
            // Show the entitlement card
            entitlementCard.classList.remove('hidden');
            
            // Update leave type name
            leaveTypeName.textContent = data.name;
            
            // Calculate values
            if (data.unlimited) {
                // Unpaid leave
                entitlementEl.textContent = '∞';
                availableEl.textContent = '∞';
                takenEl.textContent = (balance.taken || 0).toFixed(2);
                balanceEl.textContent = '∞';
                unpaidNote.classList.remove('hidden');
                replacementNote.classList.add('hidden');
            } else if (data.isReplacement) {
                // Replacement leave - entitlement based on OT (8 hours = 1 day)
                const replacementEntitlement = Math.floor(totalOTHours / 8);
                const takenRep = (balance.taken || 0);
                const remainingRep = Math.max(0, replacementEntitlement - takenRep);
                entitlementEl.textContent = replacementEntitlement.toFixed(2);
                availableEl.textContent = remainingRep.toFixed(2);
                takenEl.textContent = takenRep.toFixed(2);
                balanceEl.textContent = remainingRep.toFixed(2);
                replacementNote.classList.remove('hidden');
                unpaidNote.classList.add('hidden');
            } else {
                // Regular leave types
                const max = balance.max || 0;
                const taken = balance.taken || 0;
                const balanceValue = (balance.balance !== undefined) ? balance.balance : (max - taken);
                
                entitlementEl.textContent = max.toFixed(2);
                availableEl.textContent = (typeof balanceValue !== 'undefined' ? Number(balanceValue).toFixed(2) : (max - taken).toFixed(2));
                takenEl.textContent = taken.toFixed(2);
                balanceEl.textContent = Number(balanceValue).toFixed(2);
                replacementNote.classList.add('hidden');
                unpaidNote.classList.add('hidden');
            }
        } else {
            // Hide the entitlement card if no leave type selected
            entitlementCard.classList.add('hidden');
        }
    });

    // Calculate days between dates
    function calculateDays() {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;

        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            
            if (end >= start) {
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; // +1 to include both start and end date
                totalDaysEl.textContent = diffDays + ' ' + (diffDays === 1 ? 'day' : 'days');
            } else {
                totalDaysEl.textContent = '0 days';
            }
        } else {
            totalDaysEl.textContent = '0 days';
        }
    }

    // Add event listeners for date inputs
    startDateInput.addEventListener('change', calculateDays);
    endDateInput.addEventListener('change', calculateDays);
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