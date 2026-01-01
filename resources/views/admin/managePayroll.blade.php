@extends('layouts.admin')

@section('title', 'Manage Payroll')

@section('content')
<!-- Breadcrumbs -->
<div class="mb-6">
    {!! \App\Helpers\BreadcrumbHelper::render() !!} 
</div>

@php
    $statusColors = [
        'draft' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
        'approved' => 'bg-green-100 text-green-800 border-green-300',
        'paid' => 'bg-blue-100 text-blue-800 border-blue-300'
    ];
    $statusLabels = [
        'draft' => 'Draft',
        'approved' => 'Approved',
        'paid' => 'Paid'
    ];
    $currentStatus = $overallStatus ?? 'draft';
@endphp

<div class="container mx-auto px-2 sm:px-4 py-4 sm:py-8">
    @if(session('success'))
        <div class="mb-4 sm:mb-6 bg-green-100 border border-green-400 text-green-700 px-3 sm:px-4 py-2 sm:py-3 rounded-lg flex items-center gap-2 text-sm sm:text-base">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <span class="break-words">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 sm:mb-6 bg-red-100 border border-red-400 text-red-700 px-3 sm:px-4 py-2 sm:py-3 rounded-lg flex items-center gap-2 text-sm sm:text-base">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <span class="break-words">{{ session('error') }}</span>
        </div>
    @endif
    <div class="mb-4 sm:mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-800 mb-2">Payroll Management</h1>
                <p class="text-sm sm:text-base text-gray-600 flex items-center gap-2">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="break-words">Monthly payroll calculation based on shifts assigned in timetable</span>
                </p>
            </div>
        </div>
    </div>

    {{-- Alpine.js Data Scope for Filtering and State --}}
    <div x-data="payrollData()" x-init="init()">

        {{-- Filter Controls --}}
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 mb-4 sm:mb-8">
            <form method="GET" action="{{ route('admin.payroll') }}">
                <div>
                    <label for="month" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">Select Period</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-2 sm:pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <select id="month" name="month" 
                                class="block w-full pl-8 sm:pl-10 pr-8 sm:pr-10 py-2 sm:py-3 text-sm sm:text-base border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 rounded-lg bg-white transition duration-150 ease-in-out"
                                x-model="selectedMonth"
                                onchange="this.form.submit()">
                            @php
                                $currentMonth = $selectedMonth ?? now()->format('Y-m');
                                $months = [];
                                $seenMonths = [];
                                
                                // Generate all months from January 2025 to current month
                                $startDate = \Carbon\Carbon::create(2025, 1, 1)->startOfMonth();
                                $endDate = now()->endOfMonth();
                                
                                // Generate all months from start date to current month
                                $current = $endDate->copy();
                                while ($current->gte($startDate)) {
                                    $value = $current->format('Y-m');
                                    $label = $current->format('F Y');
                                    
                                    if (!in_array($value, $seenMonths)) {
                                        $months[] = [
                                            'value' => $value,
                                            'label' => $label
                                        ];
                                        $seenMonths[] = $value;
                                    }
                                    
                                    $current->subMonth();
                                }
                                
                                // Sort months by value (newest first)
                                usort($months, function($a, $b) {
                                    return strcmp($b['value'], $a['value']);
                                });
                            @endphp
                            @foreach($months as $month)
                                <option value="{{ $month['value'] }}" {{ $month['value'] == $currentMonth ? 'selected' : '' }}>
                                    {{ $month['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>

        {{-- Payroll Table --}}
        <form method="POST" action="{{ route('admin.payroll.update-bonus') }}" id="bonusForm">
            @csrf
            <input type="hidden" name="month" value="{{ $selectedMonth ?? now()->format('Y-m') }}">
            <div class="bg-white rounded-xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-3 sm:px-6 py-3 sm:py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 sm:gap-4">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4">
                        <h2 class="text-base sm:text-lg lg:text-xl font-bold text-white break-words">Staff Payroll Details - {{ \Carbon\Carbon::parse($selectedMonth ?? now()->format('Y-m'))->format('F Y') }}</h2>
                        <span class="px-2 sm:px-3 py-1 text-xs font-semibold rounded-full border {{ $statusColors[$currentStatus] ?? $statusColors['draft'] }}">
                            {{ $statusLabels[$currentStatus] ?? 'Draft' }}
                        </span>
                    </div>
            </div>
            
            <div class="overflow-x-auto -mx-2 sm:mx-0">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-left text-[10px] sm:text-xs font-bold text-gray-700 uppercase tracking-wider whitespace-nowrap">Staff</th>
                            <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-left text-[10px] sm:text-xs font-bold text-gray-700 uppercase tracking-wider whitespace-nowrap">Role</th>
                            <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-left text-[10px] sm:text-xs font-bold text-gray-700 uppercase tracking-wider whitespace-nowrap">Department</th>
                            <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-center text-[10px] sm:text-xs font-bold text-gray-700 uppercase tracking-wider whitespace-nowrap">Shifts</th>
                            <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-right text-[10px] sm:text-xs font-bold text-gray-700 uppercase tracking-wider whitespace-nowrap">Basic</th>
                            <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-right text-[10px] sm:text-xs font-bold text-gray-700 uppercase tracking-wider whitespace-nowrap">Commission</th>
                            <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-right text-[10px] sm:text-xs font-bold text-gray-700 uppercase tracking-wider whitespace-nowrap">Bonus</th>
                            <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-right text-[10px] sm:text-xs font-bold text-gray-700 uppercase tracking-wider whitespace-nowrap">PH Pay</th>
                            <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-right text-[10px] sm:text-xs font-bold text-gray-700 uppercase tracking-wider whitespace-nowrap">Normal OT</th>
                            <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-right text-[10px] sm:text-xs font-bold text-gray-700 uppercase tracking-wider whitespace-nowrap">PH OT</th>
                            <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-right text-[10px] sm:text-xs font-bold text-gray-700 uppercase tracking-wider whitespace-nowrap">Deductions</th>
                            <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-right text-[10px] sm:text-xs font-bold text-gray-700 uppercase tracking-wider whitespace-nowrap">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($staffList as $index => $staff)
                            @php
                                $publicHolidayPay = 15.38 * ($staff->public_holiday_hours ?? 0);
                                $normalOtPay = 12.26 * ($staff->normal_ot_hours ?? 0);
                                $phOtPay = 21.68 * ($staff->ph_ot_hours ?? 0);
                                $marketingBonus = $staff->marketing_bonus ?? 0;
                                $totalDeductions = $staff->total_deductions ?? 0;
                                $baseTotal = $staff->basic_salary + $staff->fixed_commission + $publicHolidayPay + $normalOtPay + $phOtPay;
                                $grossTotal = $baseTotal + $marketingBonus;
                                $total = $grossTotal - $totalDeductions;
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors duration-150 staff-row" 
                                data-name="{{ strtolower($staff->name) }}"
                                data-role="{{ strtolower($staff->role) }}"
                                data-department="{{ strtolower($staff->department) }}"
                                x-data="{
                                    marketingBonus: {{ $marketingBonus }},
                                    basicSalary: {{ $staff->basic_salary }},
                                    fixedCommission: {{ $staff->fixed_commission }},
                                    publicHolidayPay: {{ $publicHolidayPay }},
                                    normalOtPay: {{ $normalOtPay }},
                                    phOtPay: {{ $phOtPay }},
                                    totalDeductions: {{ $totalDeductions }},
                                    get total() {
                                        return this.basicSalary + this.fixedCommission + this.marketingBonus + this.publicHolidayPay + this.normalOtPay + this.phOtPay - this.totalDeductions;
                                    }
                                }">
                                <td class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4">
                                    <div class="flex items-center">
                                        <div class="sm:ml-4">
                                            <div class="text-xs sm:text-sm font-semibold text-gray-900 break-words">{{ $staff->name }}</div>
                                            @if($staff->hire_date)
                                                <div class="text-[10px] sm:text-xs text-gray-500 hidden sm:block">Hired: {{ $staff->hire_date->format('d M Y') }}</div>
                                            @endif
                                            @php
                                                $staffStatus = $staff->payroll_status ?? 'draft';
                                                $staffStatusColors = [
                                                    'draft' => 'bg-yellow-100 text-yellow-700',
                                                    'approved' => 'bg-green-100 text-green-700',
                                                    'paid' => 'bg-blue-100 text-blue-700'
                                                ];
                                            @endphp
                                            <span class="text-[10px] sm:text-xs px-1.5 sm:px-2 py-0.5 rounded-full {{ $staffStatusColors[$staffStatus] ?? $staffStatusColors['draft'] }} font-medium mt-1 inline-block">
                                                {{ ucfirst($staffStatus) }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 whitespace-nowrap">
                                    <span class="px-2 sm:px-3 py-1 inline-flex text-[10px] sm:text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ ucfirst($staff->role) }}
                                    </span>
                                </td>
                                <td class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-700">{{ $staff->department }}</td>
                                <td class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 whitespace-nowrap text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-xs sm:text-sm font-semibold text-gray-900">
                                            {{ $staff->working_days }}/{{ $staff->total_working_days }}
                                        </span>
                                        <span class="text-[10px] sm:text-xs text-gray-500 hidden sm:block">shifts</span>
                                        @if($staff->is_full_month)
                                            <span class="text-[10px] sm:text-xs text-green-600 font-medium mt-1 hidden sm:block">Full Month</span>
                                        @else
                                            <span class="text-[10px] sm:text-xs text-orange-600 font-medium mt-1 hidden sm:block">Pro-rated</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 whitespace-nowrap text-xs sm:text-sm text-right">
                                    <div class="font-medium text-gray-900">RM {{ number_format($staff->basic_salary, 2) }}</div>
                                    @if(!$staff->is_full_month)
                                        <div class="text-[10px] sm:text-xs text-gray-500 hidden lg:block">(Full: RM {{ number_format($staff->full_basic_salary, 2) }})</div>
                                    @endif
                                </td>
                                <td class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 whitespace-nowrap text-xs sm:text-sm text-right">
                                    @if($staff->fixed_commission)
                                        <span class="text-green-600 font-semibold">RM {{ number_format($staff->fixed_commission, 2) }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 whitespace-nowrap text-xs sm:text-sm text-right">
                                    <div class="flex items-center justify-end gap-1 sm:gap-2">
                                        <span class="text-gray-500 text-[10px] sm:text-xs mr-0.5 sm:mr-1 hidden sm:inline">RM</span>
                                        <input type="number" 
                                               step="0.01" 
                                               min="0"
                                               value="{{ $marketingBonus }}"
                                               x-model.number="marketingBonus"
                                               name="bonus[{{ $staff->user_id }}]"
                                               class="w-16 sm:w-20 lg:w-24 px-1 sm:px-2 py-0.5 sm:py-1 text-[10px] sm:text-xs lg:text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-right"
                                               placeholder="0.00">
                                    </div>
                                </td>
                                <td class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 whitespace-nowrap text-xs sm:text-sm text-right">
                                    @if($staff->public_holiday_hours > 0)
                                    <div class="font-medium text-gray-900">RM {{ number_format($publicHolidayPay, 2) }}</div>
                                        <div class="text-[10px] sm:text-xs text-gray-500">({{ number_format($staff->public_holiday_hours, 2) }} hrs)</div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 whitespace-nowrap text-xs sm:text-sm text-right">
                                    @if($staff->normal_ot_hours > 0)
                                    <div class="font-medium text-gray-900">RM {{ number_format($normalOtPay, 2) }}</div>
                                        <div class="text-[10px] sm:text-xs text-gray-500">({{ number_format($staff->normal_ot_hours, 2) }} hrs)</div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 whitespace-nowrap text-xs sm:text-sm text-right">
                                    @if($staff->ph_ot_hours > 0)
                                    <div class="font-medium text-gray-900">RM {{ number_format($phOtPay, 2) }}</div>
                                        <div class="text-[10px] sm:text-xs text-gray-500">({{ number_format($staff->ph_ot_hours, 2) }} hrs)</div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 whitespace-nowrap text-xs sm:text-sm text-right">
                                    @if($totalDeductions > 0)
                                        <div class="font-medium text-red-600">RM {{ number_format($totalDeductions, 2) }}</div>
                                        <div class="text-[10px] sm:text-xs text-gray-500">(Unpaid Leave)</div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 whitespace-nowrap text-xs sm:text-sm lg:text-base text-right">
                                    <span class="text-sm sm:text-base lg:text-lg font-bold text-green-600" x-text="'RM ' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')">RM {{ number_format($total, 2) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-2 sm:px-6 py-4 sm:py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                        <p class="text-lg font-medium">No staff found</p>
                                        <p class="text-sm">Try adjusting your search or filter criteria</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </form>

        {{-- Status Actions Below Table --}}
        <div class="mt-4 bg-white rounded-xl shadow-lg p-4 sm:p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-800">Payroll Status Actions</h3>
                    <p class="text-xs sm:text-sm text-gray-600 mt-1 break-words">Actions will apply to all payrolls for {{ \Carbon\Carbon::parse($selectedMonth ?? now()->format('Y-m'))->format('F Y') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2.5 sm:gap-3">
                    {{-- Save Marketing Bonuses Button --}}
                    <button type="submit" 
                            form="bonusForm"
                            class="px-5 py-2.5 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2 text-sm sm:text-base min-w-[140px]">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="whitespace-nowrap">Save Marketing Bonuses</span>
                    </button>

                    {{-- Sync Payroll Button --}}
                    <form method="POST" action="{{ route('admin.payroll.sync') }}" class="inline">
                        @csrf
                        <input type="hidden" name="month" value="{{ $selectedMonth ?? now()->format('Y-m') }}">
                        <button type="submit" 
                                onclick="return confirm('This will recalculate and update all payroll records for this month based on current shifts and OT claims. Continue?')"
                                class="px-5 py-2.5 bg-purple-500 hover:bg-purple-600 text-white rounded-lg font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2 text-sm sm:text-base min-w-[140px]">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <span class="whitespace-nowrap">Sync Payroll Data</span>
                        </button>
                    </form>

                    {{-- Publish Button (Draft → Approved) --}}
                    @if($currentStatus === 'draft')
                        <form method="POST" action="{{ route('admin.payroll.publish') }}" class="inline">
                            @csrf
                            <input type="hidden" name="month" value="{{ $selectedMonth ?? now()->format('Y-m') }}">
                            <input type="hidden" name="publish_all" value="1">
                            <input type="hidden" name="status" value="approved">
                            <button type="submit" 
                                    onclick="return confirm('Are you sure you want to publish ALL draft payrolls for this month?')"
                                    class="px-5 py-2.5 bg-green-500 hover:bg-green-600 text-white rounded-lg font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2 text-sm sm:text-base min-w-[140px]">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <span class="whitespace-nowrap">Publish All Draft Payrolls</span>
                            </button>
                        </form>
                    @endif

                    {{-- Mark as Paid Button (Approved → Paid) --}}
                    @if($currentStatus === 'approved')
                        <form method="POST" action="{{ route('admin.payroll.publish') }}" class="inline">
                            @csrf
                            <input type="hidden" name="month" value="{{ $selectedMonth ?? now()->format('Y-m') }}">
                            <input type="hidden" name="publish_all" value="1">
                            <input type="hidden" name="status" value="paid">
                            <button type="submit" 
                                    onclick="return confirm('Are you sure you want to mark ALL approved payrolls as paid for this month?')"
                                    class="px-5 py-2.5 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2 text-sm sm:text-base min-w-[140px]">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="whitespace-nowrap">Mark All as Paid</span>
                            </button>
                        </form>
                    @endif

                    {{-- Revert to Draft Button (Approved → Draft or Paid → Draft) --}}
                    @if($currentStatus === 'approved' || $currentStatus === 'paid')
                        <form method="POST" action="{{ route('admin.payroll.publish') }}" class="inline">
                            @csrf
                            <input type="hidden" name="month" value="{{ $selectedMonth ?? now()->format('Y-m') }}">
                            <input type="hidden" name="publish_all" value="1">
                            <input type="hidden" name="status" value="draft">
                            <button type="submit" 
                                    onclick="return confirm('Are you sure you want to revert ALL payrolls to draft for this month?')"
                                    class="px-5 py-2.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2 text-sm sm:text-base min-w-[140px]">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                <span class="whitespace-nowrap">Revert All to Draft</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Calculation Rules Summary --}}
        <div class="mt-4 sm:mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl shadow-md p-4 sm:p-6 border border-blue-100">
            <div class="flex items-start gap-2 sm:gap-3">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-2 sm:mb-3">Payroll Calculation Rules</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
                        <div class="bg-white rounded-lg p-3 sm:p-4 shadow-sm">
                            <h4 class="font-semibold text-blue-700 mb-2 flex items-center gap-2 text-sm sm:text-base">
                                <svg class="w-3 h-3 sm:w-4 sm:h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Working Days Calculation
                            </h4>
                            <ul class="space-y-1 text-xs sm:text-sm text-gray-700">
                                <li>• Working days are based on <strong>shifts assigned in the timetable</strong></li>
                                <li>• Only counts shifts with working hours (excludes rest days and leave days)</li>
                                <li>• <strong>Industry Standard:</strong> Salary is always calculated based on <strong>full month (27 days)</strong>, regardless of hire date</li>
                                <li>• <strong>Full month staff:</strong> Receives full basic salary if worked all 27 shifts</li>
                                <li>• <strong>Mid-month join:</strong> Only counts shifts from hire date onwards, but salary is still pro-rated against full month (27 days)</li>
                                <li>• <strong>Formula:</strong> (Full Basic Salary ÷ 27) × Actual Shifts Worked</li>
                                <li>• Example: Staff hired Dec 28, worked 2 shifts → (RM 1,500 ÷ 27) × 2 = RM 111.11</li>
                            </ul>
                        </div>
                        <div class="bg-white rounded-lg p-3 sm:p-4 shadow-sm">
                            <h4 class="font-semibold text-purple-700 mb-2 flex items-center gap-2 text-sm sm:text-base">
                                <svg class="w-3 h-3 sm:w-4 sm:h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                                Basic Compensation
                            </h4>
                            <ul class="space-y-1 text-xs sm:text-sm text-gray-700">
                                <li>• Basic salary as per contract</li>
                                <li>• <strong>RM 200</strong> fixed commission (after 3 months of service)</li>
                                <li>• Commission is not pro-rated (full amount if eligible)</li>
                                <li>• <strong>Marketing bonus</strong> can be entered by admin for each staff</li>
                                <li>• Marketing bonus is added to the total salary</li>
                            </ul>
                        </div>
                        <div class="bg-white rounded-lg p-3 sm:p-4 shadow-sm">
                            <h4 class="font-semibold text-orange-700 mb-2 flex items-center gap-2 text-sm sm:text-base">
                                <svg class="w-3 h-3 sm:w-4 sm:h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                            </svg>
                                Overtime Rates (for Claims)
                            </h4>
                            <ul class="space-y-1 text-xs sm:text-sm text-gray-700">
                                <li>• <strong>Normal OT (Fulltime):</strong> <span class="font-semibold text-orange-600">RM 12.26/hour</span></li>
                                <li>• <strong>Public Holiday OT:</strong> <span class="font-semibold text-orange-600">RM 21.68/hour</span></li>
                                <li>• Applied to approved OT claims for payroll calculation</li>
                            </ul>
                        </div>
                        <div class="bg-white rounded-lg p-3 sm:p-4 shadow-sm">
                            <h4 class="font-semibold text-green-700 mb-2 flex items-center gap-2 text-sm sm:text-base">
                                <svg class="w-3 h-3 sm:w-4 sm:h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                    <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"></path>
                                </svg>
                                Public Holiday Pay
                            </h4>
                            <ul class="space-y-1 text-xs sm:text-sm text-gray-700">
                                <li>• <strong>Public Holiday Work Pay:</strong> <span class="font-semibold text-green-600">RM 15.38/hour</span></li>
                                <li>• Regular work hours on designated public holidays (not OT)</li>
                                <li>• Different from Public Holiday OT rate (RM 21.68/hour)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
// Alpine.js data function
function payrollData() {
    return {
        selectedMonth: '{{ $selectedMonth ?? now()->format('Y-m') }}'
    };
}
</script>
@endpush
