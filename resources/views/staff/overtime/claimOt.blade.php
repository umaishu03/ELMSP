@extends('layouts.staff')
@section('title', 'Claim Overtime')
@section('content')
<!-- Breadcrumbs -->
<div class="mb-6">
    {!! \App\Helpers\BreadcrumbHelper::render() !!}
</div>

<!-- Title -->
<div class="mb-8">
    <h1 class="text-4xl font-bold text-gray-800 mb-2">Claim Overtime</h1>
    <p class="text-gray-600 flex items-center gap-2">
        <i class="fas fa-exchange-alt text-blue-500"></i>
        Convert your overtime hours into replacement leave or payroll
    </p>
</div>

<div class="space-y-6">
    <!-- ========== CLAIM OVERTIME SECTION ========== -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
        <!-- Info Banner -->
        <div class="px-8 py-4 bg-purple-50 border-l-4 border-purple-400">
            <div class="flex items-start gap-3">
                <i class="fas fa-info-circle text-purple-600 text-lg mt-0.5"></i>
                <div class="text-sm">
                    <p class="font-semibold text-purple-900">Conversion Rate</p>
                    <p class="text-purple-800">8 hours of overtime = 1 day of replacement leave</p>
                </div>
            </div>
        </div>

        <form id="claimOvertimeForm" method="POST" action="{{ route('staff.claimOt.store') }}">
            @csrf
            @if(session('success'))
                <!-- Success Modal -->
                <div id="claimSuccessModal" class="fixed z-50 flex items-center justify-center" aria-modal="true" role="dialog" style="top: 4rem; left: 0; right: 0; bottom: 0; pointer-events: none;">
                    <div class="absolute bg-black opacity-40 z-40" style="pointer-events: auto; top: 0; left: 0; right: 0; bottom: 0;"></div>
                    <div class="bg-white rounded-lg shadow-lg p-6 z-50 relative w-full max-w-md mx-4" style="pointer-events: auto;">
                        <div class="flex items-start justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Success</h3>
                            <button id="closeClaimSuccessX" class="text-gray-400 hover:text-gray-600 text-2xl leading-none cursor-pointer">&times;</button>
                        </div>
                        <div class="mt-3 text-sm text-gray-700">{{ session('success') }}</div>
                        <div class="mt-6 text-right">
                            <button id="closeClaimSuccess" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold cursor-pointer">OK</button>
                        </div>
                    </div>
                </div>
            @endif
            <!-- Select Month Section -->
            <div class="px-8 py-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-calendar-check text-gray-600"></i>
                    Select Month to Claim
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Month <span class="text-red-500">*</span>
                        </label>
                        <input type="month" 
                               name="claim_month"
                               id="claimMonth"
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" 
                               required />
                    </div>
                    <div class="md:col-span-2 flex items-end">
                        <button type="button" 
                                id="viewOTButton"
                                class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-all duration-200 flex items-center gap-2">
                            <i class="fas fa-search"></i>
                            <span>View OT Summary</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- OT Summary Display (Hidden by default) -->
            <div id="otSummarySection" class="hidden">
                <!-- Future OT Info (if any) -->
                @if(isset($futureHours) && $futureHours > 0)
                <div class="px-8 py-4 bg-blue-50 border-l-4 border-blue-400">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle text-blue-600 text-lg mt-0.5"></i>
                        <div class="flex-1">
                            <p class="font-semibold text-blue-900 mb-1">Future Overtime Not Available</p>
                            <p class="text-sm text-blue-800 mb-2">
                                <strong>{{ number_format($futureHours, 1) }} hours</strong> of approved overtime for future dates are not available for claim. Overtime can only be claimed for dates that have already passed.
                            </p>
                            @if(isset($futureOT) && $futureOT->count() > 0)
                            <details class="mt-2">
                                <summary class="text-sm text-blue-700 cursor-pointer hover:text-blue-900 font-medium">
                                    View future overtime details
                                </summary>
                                <div class="mt-2 pl-4 border-l-2 border-blue-300">
                                    <ul class="text-sm text-blue-800 space-y-1">
                                        @foreach($futureOT as $ot)
                                        <li>
                                            • {{ $ot->ot_date->format('M d, Y') }} - {{ number_format($ot->hours, 1) }} hours ({{ ucfirst(str_replace('_', ' ', $ot->ot_type)) }})
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </details>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Excluded OT Warning (if any) -->
                @if(isset($excludedHours) && $excludedHours > 0)
                <div class="px-8 py-4 bg-amber-50 border-l-4 border-amber-400">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-triangle text-amber-600 text-lg mt-0.5"></i>
                        <div class="flex-1">
                            <p class="font-semibold text-amber-900 mb-1">Overtime Hours Excluded</p>
                            <p class="text-sm text-amber-800 mb-2">
                                <strong>{{ number_format($excludedHours, 1) }} hours</strong> of approved overtime have been automatically excluded because you have approved leave on those dates.
                            </p>
                            @if(isset($excludedOT) && $excludedOT->count() > 0)
                            <details class="mt-2">
                                <summary class="text-sm text-amber-700 cursor-pointer hover:text-amber-900 font-medium">
                                    View excluded overtime details
                                </summary>
                                <div class="mt-2 pl-4 border-l-2 border-amber-300">
                                    <ul class="text-sm text-amber-800 space-y-1">
                                        @foreach($excludedOT as $ot)
                                        <li>
                                            • {{ $ot->ot_date->format('M d, Y') }} - {{ number_format($ot->hours, 1) }} hours ({{ ucfirst(str_replace('_', ' ', $ot->ot_type)) }})
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </details>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Summary Card -->
                <div class="px-8 py-6 border-b border-gray-100 bg-gradient-to-br from-purple-50 to-purple-100">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-chart-bar text-gray-600"></i>
                        Overtime Summary
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @php
                            $totalHours = isset($availableOT) ? $availableOT->sum('hours') : 0;
                            $fulltimeAvailable = isset($availableOT) ? $availableOT->where('ot_type','fulltime')->sum('hours') : 0;
                            $publicHolidayAvailable = isset($availableOT) ? $availableOT->where('ot_type','public_holiday')->sum('hours') : 0;
                            $replacementDays = floor($totalHours / 8);
                        @endphp
                        <div class="bg-white rounded-xl p-6 shadow-sm border border-purple-100">
                            <p class="text-xs font-medium text-gray-600 mb-2">Total OT Hours Available</p>
                            <p class="text-3xl font-bold text-purple-600">{{ number_format($totalHours,1) }} hrs</p>
                            @if(isset($excludedHours) && $excludedHours > 0)
                            <p class="text-xs text-amber-600 mt-1">
                                <i class="fas fa-info-circle"></i> Excludes {{ number_format($excludedHours, 1) }} hrs on leave dates
                            </p>
                            @endif
                        </div>
                        <div class="bg-white rounded-xl p-6 shadow-sm border border-purple-100">
                            <p class="text-xs font-medium text-gray-600 mb-2">Days Entitled</p>
                            <p class="text-3xl font-bold text-purple-700">{{ $replacementDays }} days</p>
                        </div>
                        <div class="bg-white rounded-xl p-6 shadow-sm border border-purple-100">
                            <p class="text-xs font-medium text-gray-600 mb-2">Status</p>
                            @php $serverAvailable = ($totalHours >= 1); @endphp
                            <span id="availabilityBadge" data-total-available-hours="{{ $totalHours }}" class="inline-flex items-center gap-2 px-4 py-2 {{ $serverAvailable ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }} text-sm font-semibold rounded-full">
                                <i id="availabilityIcon" class="{{ $serverAvailable ? 'fas fa-check-circle' : 'fas fa-times-circle' }}"></i>
                                <span id="availabilityText">{{ $serverAvailable ? 'Available for claim' : 'Not available for claim' }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Claim Options -->
                <div class="px-8 py-6 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-gift text-gray-600"></i>
                        Claim Options
                    </h2>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Claim OT For <span class="text-red-500">*</span>
                        </label>
                        <select name="claim_type" 
                                id="claimType"
                                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" 
                                required>
                            <option value="">Select Option</option>
                            <option value="replacement_leave">Replacement Leave</option>
                            <option value="payroll">Payroll</option>
                        </select>
                    </div>

                    <!-- Days to Claim Input (Hidden by default) -->
                    <div id="replacementLeaveSection" class="hidden mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Days to Claim <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                            name="days_to_claim"
                            id="daysToClaimInput"
                            min="1"
                            max="{{ $replacementDays }}"
                            placeholder="Enter number of days to claim"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" />
                        <p class="text-xs text-gray-500 mt-2">
                            <i class="fas fa-info-circle"></i> You have <strong>{{ $replacementDays }}</strong> days available for replacement leave.
                        </p>
                    </div>
                    <!-- Available OT Records to include in claim -->
                    <div class="mt-4">
                        <p class="text-sm font-medium mb-2">Overtime records included in this claim</p>
                        <div class="text-sm text-gray-700 mb-2">
                            @if(isset($availableOT) && $availableOT->count())
                                All approved overtime for the selected month will be included automatically in this claim.
                            @else
                                <span class="text-gray-600">No approved overtime available for claim.</span>
                            @endif
                        </div>
                        {{-- Hidden inputs: include all available OT ids so backend receives them without visible checkboxes --}}
                        @if(isset($availableOT) && $availableOT->count())
                            @foreach($availableOT as $ot)
                                <input type="hidden" name="selected_overtimes[]" value="{{ $ot->id }}" />
                            @endforeach
                        @endif
                    </div>

                    <!-- Payroll Claim Section (Hidden by default) -->
                    <div id="payrollSection" class="hidden mt-6">
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                                <i class="fas fa-money-bill-wave text-green-600"></i>
                                Payroll Claim Details
                            </h3>

                            <!-- OT Rates Info -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div class="bg-white rounded-lg p-4 border border-green-200">
                                    <p class="text-xs font-medium text-gray-600 mb-1">Fulltime OT Rate</p>
                                    <p class="text-2xl font-bold text-green-600">RM 12.26<span class="text-sm font-normal text-gray-600">/hour</span></p>
                                </div>
                                <div class="bg-white rounded-lg p-4 border border-green-200">
                                    <p class="text-xs font-medium text-gray-600 mb-1">Public Holiday OT Rate</p>
                                    <p class="text-2xl font-bold text-green-700">RM 21.68<span class="text-sm font-normal text-gray-600">/hour</span></p>
                                </div>
                            </div>

                            <!-- Available OT Hours -->
                            <div class="bg-white rounded-lg p-5 mb-6 border border-green-200">
                                <h4 class="font-semibold text-gray-800 mb-3">Available OT Hours</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                        <span class="text-sm text-gray-700">Fulltime OT:</span>
                                        <span class="font-bold text-gray-900" id="availableFulltimeHours">{{ number_format($fulltimeAvailable,1) }} hrs</span>
                                    </div>
                                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                        <span class="text-sm text-gray-700">Public Holiday OT:</span>
                                        <span class="font-bold text-gray-900" id="availablePublicHolidayHours">{{ number_format($publicHolidayAvailable,1) }} hrs</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Claim Hours Input -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Fulltime OT Hours to Claim
                                    </label>
                                    <div class="flex gap-3">
                                             <input type="number" 
                                                 name="fulltime_hours"
                                                 id="fulltimeHoursClaim"
                                                 min="0"
                                                 max="{{ $fulltimeAvailable }}"
                                                 step="0.5"
                                                 value="0"
                                                 class="flex-1 border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                                 placeholder="0.0" />
                                        <button type="button" 
                                                id="claimAllFulltime"
                                                class="px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-all">
                                            Claim All
                                        </button>
                                    </div>
                                    <p class="text-xs text-gray-600 mt-1">
                                        Amount: <span class="font-semibold text-green-700" id="fulltimeAmount">RM 0.00</span>
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Public Holiday OT Hours to Claim
                                    </label>
                                    <div class="flex gap-3">
                                             <input type="number" 
                                                 name="public_holiday_hours"
                                                 id="publicHolidayHoursClaim"
                                                 min="0"
                                                 max="{{ $publicHolidayAvailable }}"
                                                 step="0.5"
                                                 value="0"
                                                 class="flex-1 border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                                 placeholder="0.0" />
                                        <button type="button" 
                                                id="claimAllPublicHoliday"
                                                class="px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-all">
                                            Claim All
                                        </button>
                                    </div>
                                    <p class="text-xs text-gray-600 mt-1">
                                        Amount: <span class="font-semibold text-green-700" id="publicHolidayAmount">RM 0.00</span>
                                    </p>
                                </div>
                            </div>

                            <!-- Total Calculation -->
                            <div class="mt-6 bg-gradient-to-r from-green-600 to-green-700 rounded-lg p-5 text-white">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm text-green-100 mb-1">Total Payroll Claim</p>
                                        <p class="text-xs text-green-200">
                                            <span id="totalHoursClaimed">0.0</span> hours claimed
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-3xl font-bold" id="totalPayrollAmount">RM 0.00</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Confirmation -->
                <div class="px-8 py-6 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-question-circle text-gray-600"></i>
                        Confirmation
                    </h2>
                    
                    <div class="bg-white rounded-xl p-6 border-2 border-gray-200">
                        <p class="text-lg font-semibold text-gray-800 mb-4">Do you want to claim this overtime?</p>
                        
                        <div class="flex flex-col sm:flex-row gap-4">
                            <label class="flex items-center gap-3 p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="radio" 
                                       name="confirm_claim" 
                                       value="yes" 
                                       class="w-5 h-5 text-purple-600 focus:ring-purple-500" 
                                       required />
                                <span class="text-gray-700 font-medium">Yes, I want to claim</span>
                            </label>
                            
                            <label class="flex items-center gap-3 p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="radio" 
                                       name="confirm_claim" 
                                       value="no" 
                                       class="w-5 h-5 text-gray-600 focus:ring-gray-500" />
                                <span class="text-gray-700 font-medium">No, not now</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="px-8 py-6 border-t border-gray-100 bg-white">
                    <div class="flex flex-col md:flex-row gap-4 justify-end">
                        <button type="button" 
                                id="cancelClaimButton"
                                class="px-6 py-3 bg-white hover:bg-gray-50 text-gray-700 font-semibold rounded-xl border-2 border-gray-300 transition-all duration-200">
                            Cancel
                        </button>
                        <button type="submit" class="px-8 py-3 bg-gradient-to-r from-purple-600 to-purple-800 hover:from-purple-700 hover:to-purple-900 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
                            <i class="fas fa-check-circle"></i>
                            <span>Submit</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Show/Hide OT Summary Section
document.getElementById('viewOTButton').addEventListener('click', function() {
    const month = document.getElementById('claimMonth').value;
    if (month) {
        document.getElementById('otSummarySection').classList.remove('hidden');
    } else {
        alert('Please select a month first');
    }
});

// Show/Hide Replacement Leave Date based on Claim Type
document.getElementById('claimType').addEventListener('change', function() {
    const replacementSection = document.getElementById('replacementLeaveSection');
    const label = replacementSection.querySelector('label');
    const input = replacementSection.querySelector('input');
    const info = replacementSection.querySelector('p');
    const payrollSection = document.getElementById('payrollSection');
    const REPLACEMENT_DAYS = {{ $replacementDays }};
    
    if (this.value === 'replacement_leave') {
        replacementSection.classList.remove('hidden');
        label.textContent = "Days to Claim *";
        input.type = "number";
        input.name = "days_to_claim";
        input.placeholder = "Enter number of days to claim";
        info.innerHTML = '<i class="fas fa-info-circle"></i> You have <strong>' + REPLACEMENT_DAYS + '</strong> days available for replacement leave.';
    } else {
        replacementSection.classList.add('hidden');
        input.value = "";
    }
    
    if (this.value === 'replacement_leave') {
        replacementSection.classList.remove('hidden');
        payrollSection.classList.add('hidden');
    } else if (this.value === 'payroll') {
        payrollSection.classList.remove('hidden');
        replacementSection.classList.add('hidden');
    } else {
        replacementSection.classList.add('hidden');
        payrollSection.classList.add('hidden');
    }
});

// Cancel button - hide OT summary section
document.getElementById('cancelClaimButton').addEventListener('click', function() {
    document.getElementById('otSummarySection').classList.add('hidden');
    document.getElementById('claimOvertimeForm').reset();
});

// Payroll Calculation
const FULLTIME_RATE = 12.26;
const PUBLIC_HOLIDAY_RATE = 21.68;

// These values come from backend (computed above)
const AVAILABLE_FULLTIME_HOURS = {{ number_format($fulltimeAvailable,1,'.','') }};
const AVAILABLE_PUBLIC_HOLIDAY_HOURS = {{ number_format($publicHolidayAvailable,1,'.','') }};

// Calculate payroll amounts
function calculatePayrollAmounts() {
    const fulltimeHours = parseFloat(document.getElementById('fulltimeHoursClaim').value) || 0;
    const publicHolidayHours = parseFloat(document.getElementById('publicHolidayHoursClaim').value) || 0;
    
    const fulltimeAmount = fulltimeHours * FULLTIME_RATE;
    const publicHolidayAmount = publicHolidayHours * PUBLIC_HOLIDAY_RATE;
    const totalAmount = fulltimeAmount + publicHolidayAmount;
    const totalHours = fulltimeHours + publicHolidayHours;
    
    document.getElementById('fulltimeAmount').textContent = `RM ${fulltimeAmount.toFixed(2)}`;
    document.getElementById('publicHolidayAmount').textContent = `RM ${publicHolidayAmount.toFixed(2)}`;
    document.getElementById('totalPayrollAmount').textContent = `RM ${totalAmount.toFixed(2)}`;
    document.getElementById('totalHoursClaimed').textContent = totalHours.toFixed(1);
}

// Event listeners for payroll hour inputs
document.getElementById('fulltimeHoursClaim').addEventListener('input', calculatePayrollAmounts);
document.getElementById('publicHolidayHoursClaim').addEventListener('input', calculatePayrollAmounts);

// Claim all fulltime hours
document.getElementById('claimAllFulltime').addEventListener('click', function() {
    document.getElementById('fulltimeHoursClaim').value = AVAILABLE_FULLTIME_HOURS;
    calculatePayrollAmounts();
});

// Claim all public holiday hours
document.getElementById('claimAllPublicHoliday').addEventListener('click', function() {
    document.getElementById('publicHolidayHoursClaim').value = AVAILABLE_PUBLIC_HOLIDAY_HOURS;
    calculatePayrollAmounts();
});

// Utility: compute selected OT hours from checked checkboxes
function computeSelectedHours() {
    const checkboxes = document.querySelectorAll('input[name="selected_overtimes[]"]');
    let sum = 0;
    checkboxes.forEach(cb => {
        if (cb.checked) {
            const hrs = parseFloat(cb.dataset.hours) || 0;
            sum += hrs;
        }
    });
    return sum;
}

// Update availability badge and submit button based on thresholds
function updateAvailability() {
    const badge = document.getElementById('availabilityBadge');
    const text = document.getElementById('availabilityText');
    const icon = document.getElementById('availabilityIcon');
    const claimForm = document.getElementById('claimOvertimeForm');
    const submitBtn = claimForm ? claimForm.querySelector('button[type="submit"]') : null;
    const serverTotal = parseFloat(badge.dataset.totalAvailableHours) || 0;
    const selectedTotal = computeSelectedHours();
    const claimType = document.getElementById('claimType').value;

    let available = false;
    // For replacement leave we require at least 8 hours selected (or available)
    if (claimType === 'replacement_leave') {
        available = (selectedTotal >= 8) || (selectedTotal === 0 && serverTotal >= 8);
    }
    // For payroll we require at least 1 hour available in total (selected or server)
    else if (claimType === 'payroll') {
        available = (selectedTotal >= 1) || (serverTotal >= 1);
    }
    // If no claim type selected, show available only if server has >=1 hour (payroll minimum)
    else {
        available = serverTotal >= 1;
    }

    if (available) {
        badge.className = 'inline-flex items-center gap-2 px-4 py-2 bg-green-100 text-green-800 text-sm font-semibold rounded-full';
        icon.className = 'fas fa-check-circle';
        text.textContent = 'Available for claim';
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-60', 'cursor-not-allowed');
        }
    } else {
        badge.className = 'inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-600 text-sm font-semibold rounded-full';
        icon.className = 'fas fa-times-circle';
        text.textContent = 'Not available for claim';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
        }
    }
}

// Wire up events: checkboxes, claim type change, payroll inputs
document.querySelectorAll('input[name="selected_overtimes[]"]').forEach(cb => {
    cb.addEventListener('change', updateAvailability);
});
document.getElementById('claimType').addEventListener('change', function(e) {
    // existing handler above will toggle sections; also update availability
    // small timeout to allow DOM changes to settle
    setTimeout(updateAvailability, 50);
});
document.getElementById('fulltimeHoursClaim').addEventListener('input', updateAvailability);
document.getElementById('publicHolidayHoursClaim').addEventListener('input', updateAvailability);

// Initialize availability on load
document.addEventListener('DOMContentLoaded', function() {
    updateAvailability();
    // If success modal exists, wire close handlers
    const modal = document.getElementById('claimSuccessModal');
    if (modal) {
        const backdrop = modal.querySelector('.absolute.inset-0');
        const modalContent = modal.querySelector('.bg-white');
        const closeBtn = document.getElementById('closeClaimSuccess');
        const closeX = document.getElementById('closeClaimSuccessX');
        
        function closeModal() { 
            modal.style.display = 'none';
            modal.remove();
        }
        
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (closeX) closeX.addEventListener('click', closeModal);
        
        // Close on backdrop click (but not on modal content)
        if (backdrop) {
            backdrop.addEventListener('click', closeModal);
        }
        
        // Prevent clicks inside modal from closing it
        if (modalContent) {
            modalContent.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
        
        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal && modal.style.display !== 'none') {
                closeModal();
            }
        });
    }
});

// Form submission: allow normal submit (server will handle) but ensure availability and confirmation
document.getElementById('claimOvertimeForm').addEventListener('submit', function(e) {
    const confirmClaim = document.querySelector('input[name="confirm_claim"]:checked');
    if (!confirmClaim || confirmClaim.value !== 'yes') {
        e.preventDefault();
        alert('Please confirm that you want to claim the selected overtime.');
        return false;
    }

    // Ensure availability check passes (defense-in-depth)
    const badge = document.getElementById('availabilityBadge');
    const serverTotal = parseFloat(badge.dataset.totalAvailableHours) || 0;
    const selectedTotal = computeSelectedHours();
    const claimType = document.getElementById('claimType').value;
    let available = false;
    if (claimType === 'replacement_leave') {
        available = (selectedTotal >= 8) || (selectedTotal === 0 && serverTotal >= 8);
    } else if (claimType === 'payroll') {
        available = (selectedTotal >= 1) || (serverTotal >= 1);
    } else {
        available = serverTotal >= 1;
    }

    if (!available) {
        e.preventDefault();
        alert('You do not have enough overtime hours selected/available to make this type of claim.');
        return false;
    }

    // otherwise allow form to submit
});
</script>

@endsection