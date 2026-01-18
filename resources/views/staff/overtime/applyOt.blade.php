@extends('layouts.staff')
@section('title', 'Apply Overtime')
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
    <h1 class="text-4xl font-bold text-gray-800 mb-2">Apply Overtime</h1>
    <p class="text-gray-600 flex items-center gap-2">
        <i class="fas fa-clock text-blue-500"></i>
        Request overtime work for upcoming shifts
    </p>
</div>

<div class="space-y-6">
    <!-- ========== APPLY OVERTIME SECTION ========== -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
        <form id="applyOvertimeForm" method="POST" action="{{ route('staff.applyOt.store') }}">
            @csrf
            <!-- Weekly Limit Warning -->
            <div id="weeklyLimitWarning" class="hidden p-4 mb-4 bg-yellow-50 border-l-4 border-yellow-400 rounded">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                    <div>
                        <p class="font-semibold text-yellow-800">Weekly OT Limit Warning</p>
                        <p class="text-yellow-700 text-sm" id="weeklyLimitMessage"></p>
                    </div>
                </div>
            </div>
            <!-- Employee Information Section -->
            <div class="px-8 py-6 border-b border-gray-100 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-user-circle text-gray-600"></i>
                    Employee Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Full Name</label>
                        <input type="text" 
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 bg-gray-100 text-gray-700" 
                               value="{{ Auth::user()->name }}" 
                               readonly />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Employee ID</label>
                        <input type="text" 
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 bg-gray-100 text-gray-700" 
                               value="{{ Auth::user()->staff->employee_id ?? 'Not assigned' }}" 
                               readonly />
                    </div>
                </div>
            </div>

            <!-- OT Request Details -->
            <div class="px-8 py-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-calendar-alt text-gray-600"></i>
                    Overtime Request Details
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Type of Overtime <span class="text-red-500">*</span>
                        </label>
                        <select name="ot_type" 
                                id="otType"
                                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent" 
                                required>
                            <option value="">Select Type</option>
                            <option value="public_holiday">Public Holiday</option>
                            <option value="fulltime">Fulltime</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               name="ot_date"
                               id="otDate"
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent" 
                               required />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hours <span class="text-red-500">*</span></label>
                        <input type="number" step="0.25" min="0.5" name="hours" id="hoursInput" value="" class="w-full border border-gray-300 rounded-lg px-4 py-3" readonly />
                        <p class="text-xs text-gray-500 mt-1">Auto-set based on your department</p>
                    </div>
                </div>

                <!-- Public Holidays List (Hidden by default) -->
                <div id="publicHolidaysSection" class="hidden mt-6">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-calendar-day text-blue-600"></i>
                            Restaurant Public Holidays (2026)
                        </h3>
                        
                        <div class="bg-white rounded-lg overflow-hidden shadow-sm">
                            <div class="max-h-96 overflow-y-auto">
                                <table class="w-full">
                                    <thead class="bg-gray-50 sticky top-0">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Holiday</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Date</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Day</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="holidaysTableBody" class="divide-y divide-gray-200">
                                        <!-- Holiday rows will be inserted here by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-4 bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <div class="flex items-start gap-2 text-sm">
                                <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                                <div>
                                    <p class="font-semibold text-blue-900">Next Public Holiday</p>
                                    <p class="text-blue-800" id="nextHolidayInfo">The date has been automatically set to the next upcoming public holiday.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Reason / Notes
                    </label>
                    <textarea name="ot_reason" 
                              rows="3" 
                              class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                              placeholder="Please provide reason for overtime request..."></textarea>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="px-8 py-6 border-t border-gray-100 bg-gray-50">
                <div class="flex flex-col md:flex-row gap-4 justify-end">
                    <button type="reset" class="px-6 py-3 bg-white hover:bg-gray-50 text-gray-700 font-semibold rounded-xl border-2 border-gray-300 transition-all duration-200">
                        Reset
                    </button>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane"></i>
                        <span>Submit</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// --- Custom Alert Modal (replaces browser alert) ---
function showCustomAlert(message, type = 'error') {
    return new Promise((resolve) => {
        // Create modal if it doesn't exist
        let modal = document.getElementById('customAlertModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'customAlertModal';
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden';
            document.body.appendChild(modal);
        }
        
        // Create modal content
        const icon = type === 'error' ? '⚠️' : (type === 'warning' ? '⚠️' : (type === 'info' ? 'ℹ️' : '✓'));
        const iconColor = type === 'error' ? 'text-red-600' : (type === 'warning' ? 'text-yellow-600' : (type === 'info' ? 'text-blue-600' : 'text-green-600'));
        
        modal.innerHTML = `
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                <div class="p-6">
                    <div class="text-4xl mb-4 text-center ${iconColor}">${icon}</div>
                    <div class="text-gray-800 text-center mb-6 whitespace-pre-line">${message}</div>
                    <button class="customAlertOk w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                        OK
                    </button>
                </div>
            </div>
        `;
        
        // Close on OK button click
        modal.querySelector('.customAlertOk').addEventListener('click', function() {
            modal.classList.add('hidden');
            resolve();
        });
        
        // Close on outside click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
                resolve();
            }
        });
        
        // Show modal
        modal.classList.remove('hidden');
    });
}

// --- Custom Confirm Modal (replaces browser confirm) ---
function showCustomConfirm(message, type = 'warning') {
    return new Promise((resolve) => {
        // Create modal if it doesn't exist
        let modal = document.getElementById('customConfirmModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'customConfirmModal';
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden';
            document.body.appendChild(modal);
        }
        
        // Create modal content
        const icon = type === 'error' ? '⚠️' : (type === 'warning' ? '⚠️' : 'ℹ️');
        const iconColor = type === 'error' ? 'text-red-600' : (type === 'warning' ? 'text-yellow-600' : 'text-blue-600');
        
        modal.innerHTML = `
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                <div class="p-6">
                    <div class="text-4xl mb-4 text-center ${iconColor}">${icon}</div>
                    <div class="text-gray-800 text-center mb-6 whitespace-pre-line">${message}</div>
                    <div class="flex gap-2">
                        <button class="customConfirmCancel w-full bg-gray-300 text-gray-800 py-2 rounded-lg hover:bg-gray-400 transition">
                            Cancel
                        </button>
                        <button class="customConfirmOk w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                            OK
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Handle button clicks
        modal.querySelector('.customConfirmOk').addEventListener('click', function() {
            modal.classList.add('hidden');
            resolve(true);
        });
        
        modal.querySelector('.customConfirmCancel').addEventListener('click', function() {
            modal.classList.add('hidden');
            resolve(false);
        });
        
        // Close on outside click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
                resolve(false);
            }
        });
        
        // Show modal
        modal.classList.remove('hidden');
    });
}

// ========== PUBLIC HOLIDAYS SECTION ==========

// Set default hours based on department
document.addEventListener('DOMContentLoaded', function() {
    const department = "{{ $department ?? '' }}";
    const hoursInput = document.getElementById('hoursInput');
    
    // Department OT hour limits
    const hourLimits = {
        'manager': 2,
        'supervisor': 2,
        'waiter': 4,
        'cashier': 4,
        'kitchen': 4,
        'joki': 4,
        'barista': 4
    };
    
    const hours = hourLimits[department] || 4;
    hoursInput.value = hours;
});

// ========== PUBLIC HOLIDAYS SECTION ==========
const publicHolidays = [
    { name: "New Year's Day", date: "2026-01-01", day: "Thursday" },
    { name: "Chinese New Year", date: "2026-02-17", day: "Tuesday" },
    { name: "Chinese New Year (2nd Day)", date: "2026-02-18", day: "Wednesday" },
    { name: "Federal Territory Day", date: "2026-02-01", day: "Sunday" },
    { name: "Thaipusam", date: "2026-01-31", day: "Saturday" },
    { name: "Hari Raya Aidilfitri", date: "2026-03-20", day: "Friday" },
    { name: "Hari Raya Aidilfitri (2nd Day)", date: "2026-03-21", day: "Saturday" },
    { name: "Labour Day", date: "2026-05-01", day: "Friday" },
    { name: "Vesak Day", date: "2026-05-01", day: "Friday" },
    { name: "Agong's Birthday", date: "2026-06-06", day: "Saturday" },
    { name: "Hari Raya Aidiladha", date: "2026-05-27", day: "Wednesday" },
    { name: "Awal Muharram", date: "2026-07-16", day: "Thursday" },
    { name: "Merdeka Day", date: "2026-08-31", day: "Monday" },
    { name: "Malaysia Day", date: "2026-09-16", day: "Wednesday" },
    { name: "Prophet Muhammad's Birthday", date: "2026-08-25", day: "Tuesday" },
    { name: "Deepavali", date: "2026-11-08", day: "Sunday" },
    { name: "Christmas Day", date: "2026-12-25", day: "Friday" }
];

// Get next public holiday
function getNextPublicHoliday() {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    for (let holiday of publicHolidays) {
        const holidayDate = new Date(holiday.date);
        if (holidayDate >= today) {
            return holiday;
        }
    }
    return null;
}

// Format date to display
function formatDate(dateStr) {
    const date = new Date(dateStr);
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('en-MY', options);
}

// Populate holidays table
function populateHolidaysTable() {
    const tbody = document.getElementById('holidaysTableBody');
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    tbody.innerHTML = '';
    
    publicHolidays.forEach(holiday => {
        const holidayDate = new Date(holiday.date);
        const isPast = holidayDate < today;
        const isUpcoming = holidayDate >= today;
        
        const row = document.createElement('tr');
        row.className = isUpcoming ? 'bg-blue-50 hover:bg-blue-100' : 'hover:bg-gray-50';
        
        row.innerHTML = `
            <td class="px-4 py-3 text-sm font-medium text-gray-900">${holiday.name}</td>
            <td class="px-4 py-3 text-sm text-gray-700">${formatDate(holiday.date)}</td>
            <td class="px-4 py-3 text-sm text-gray-600">${holiday.day}</td>
            <td class="px-4 py-3">
                ${isPast 
                    ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600"><i class="fas fa-check mr-1"></i>Past</span>'
                    : '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700"><i class="fas fa-calendar-check mr-1"></i>Upcoming</span>'
                }
            </td>
        `;
        
        tbody.appendChild(row);
    });
}

// Handle OT Type change
document.getElementById('otType').addEventListener('change', function() {
    const publicHolidaysSection = document.getElementById('publicHolidaysSection');
    const otDateInput = document.getElementById('otDate');
    const nextHolidayInfo = document.getElementById('nextHolidayInfo');
    
    if (this.value === 'public_holiday') {
        // Show public holidays section
        publicHolidaysSection.classList.remove('hidden');
        populateHolidaysTable();
        
        // Set date to next public holiday
        const nextHoliday = getNextPublicHoliday();
        if (nextHoliday) {
            otDateInput.value = nextHoliday.date;
            nextHolidayInfo.textContent = `Next public holiday: ${nextHoliday.name} (${formatDate(nextHoliday.date)})`;
        } else {
            nextHolidayInfo.textContent = 'No upcoming public holidays found for this year.';
        }
    } else {
        // Hide public holidays section and clear date
        publicHolidaysSection.classList.add('hidden');
        otDateInput.value = '';
    }
});

// Check weekly OT limit
async function checkWeeklyLimit(otDate) {
    if (!otDate) {
        document.getElementById('weeklyLimitWarning').classList.add('hidden');
        return { valid: true };
    }

    try {
        const response = await fetch('{{ route("staff.applyOt.checkLimit") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ ot_date: otDate })
        });

        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error checking weekly limit:', error);
        return { valid: true }; // Allow submission if check fails
    }
}

// Check limit when date changes
document.getElementById('otDate').addEventListener('change', async function() {
    const otDate = this.value;
    if (!otDate) {
        document.getElementById('weeklyLimitWarning').classList.add('hidden');
        return;
    }

    const limitCheck = await checkWeeklyLimit(otDate);
    const warningDiv = document.getElementById('weeklyLimitWarning');
    const messageDiv = document.getElementById('weeklyLimitMessage');

    if (!limitCheck.valid) {
        warningDiv.classList.remove('hidden');
        messageDiv.textContent = limitCheck.message;
        warningDiv.className = 'p-4 mb-4 bg-red-50 border-l-4 border-red-400 rounded';
    } else {
        // Check if they have 1 application (warning)
        const otDateObj = new Date(otDate);
        const weekStart = new Date(otDateObj);
        weekStart.setDate(otDateObj.getDate() - otDateObj.getDay() + 1); // Monday
        weekStart.setHours(0, 0, 0, 0);
        const weekEnd = new Date(weekStart);
        weekEnd.setDate(weekStart.getDate() + 6); // Sunday
        weekEnd.setHours(23, 59, 59, 999);

        // This is just a visual warning, the actual check happens on submit
        warningDiv.classList.add('hidden');
    }
});

// Form submission: check limit before submitting
document.getElementById('applyOvertimeForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const otDate = document.getElementById('otDate').value;
    if (!otDate) {
        await showCustomAlert('Please select a date for the overtime application.', 'warning');
        return false;
    }

    // Check weekly limit
    const limitCheck = await checkWeeklyLimit(otDate);
    
    if (!limitCheck.valid) {
        // Show popup alert
        await showCustomAlert('⚠️ OT Application Limit Exceeded!\n\n' + limitCheck.message + '\n\nYou cannot submit more than 2 OT applications per week.', 'error');
        return false;
    }

    // Confirm submission
    const confirmed = await showCustomConfirm('Submit overtime application?', 'info');
    if (!confirmed) {
        return false;
    }

    // Allow normal form submission
    this.submit();
});

// Auto-hide success/error popups after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
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
</style>

@endsection