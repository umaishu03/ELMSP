@extends('layouts.admin')

@section('title', 'Manage Payroll')

@section('content')
<!-- Breadcrumbs -->
<div class="mb-6">
    {!! \App\Helpers\BreadcrumbHelper::render() !!} 
</div>
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-2">Payroll Management</h1>
                <p class="text-gray-600 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Monthly payroll calculation for all staff, including OT and public holiday pay
                </p>
            </div>
        </div>
    </div>

    {{-- Alpine.js Data Scope for Filtering and State --}}
    <div x-data="{ 
        searchText: '', 
        selectedMonth: '{{ now()->format('Y-m') }}',
        /**
         * Filters staff members based on search text matching name, role, or department,
         * and by the selected payroll period (YYYY-MM).
         * @param {string} staffName
         * @param {string} staffRole
         * @param {string} staffDepartment
         * @param {string} staffPeriod (YYYY-MM)
         */
        filterStaff(staffName, staffRole, staffDepartment, staffPeriod) {
            const lowerSearch = this.searchText.toLowerCase();
            const staffInfo = `${staffName} ${staffRole} ${staffDepartment}`.toLowerCase();
            // Search matching
            const matchesSearch = staffInfo.includes(lowerSearch);
            // Period matching: if selectedMonth is empty show all, otherwise match YYYY-MM
            const matchesPeriod = !this.selectedMonth || String(staffPeriod) === String(this.selectedMonth);
            return matchesSearch && matchesPeriod;
        }
    }">

        {{-- Search and Filter Controls --}}
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-semibold text-gray-700 mb-2">Search Staff</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        {{-- Search Input: Binds to searchText --}}
                        <input type="text" id="search" name="search" 
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out"
                                placeholder="Search by name, role, or department..."
                                x-model="searchText">
                    </div>
                </div>

                <div>
                    <label for="month" class="block text-sm font-semibold text-gray-700 mb-2">Select Period</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <select id="month" name="month" 
                                class="block w-full pl-10 pr-10 py-3 text-base border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg bg-white transition duration-150 ease-in-out"
                                x-model="selectedMonth">
                            @php
                                $currentMonth = now()->format('Y-m');
                                $months = [];
                                for ($i = 0; $i < 12; $i++) {
                                    $date = now()->subMonths($i);
                                    $months[] = [
                                        'value' => $date->format('Y-m'),
                                        'label' => $date->format('F Y')
                                    ];
                                }
                            @endphp
                            @foreach($months as $month)
                                <option value="{{ $month['value'] }}">
                                    {{ $month['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="mt-4 flex flex-wrap gap-2">
                <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    All Departments
                </button>
                <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Export to Excel
                </button>
                <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print Payslips
                </button>
            </div>
        </div>

        {{-- Payroll Table --}}
        <div class="bg-white rounded-xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <h2 class="text-xl font-bold text-white">Staff Payroll Details</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Staff</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Department</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Basic Salary</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Commission</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">PH Pay</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Normal OT</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">PH OT</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($staffList as $staff)
                            @php
                                $monthsWorked = \Carbon\Carbon::parse($staff->start_date)->diffInMonths(now());
                                $fixedCommission = $monthsWorked >= 3 ? 200 : 0;
                                $publicHolidayHours = $staff->public_holiday_hours ?? 0;
                                $publicHolidayPay = 15.38 * $publicHolidayHours;
                                $normalOtHours = $staff->normal_ot_hours ?? 0;
                                $normalOtPay = 12.26 * $normalOtHours;
                                $phOtHours = $staff->ph_ot_hours ?? 0;
                                $phOtPay = 21.68 * $phOtHours;
                                $total = $staff->basic_salary + $fixedCommission + $publicHolidayPay + $normalOtPay + $phOtPay;
                            @endphp
                            {{-- Row: x-show filters the row based on the staff data and current search text --}}
                            <tr class="hover:bg-gray-50 transition-colors duration-150" 
                                x-show="filterStaff('{{ addslashes($staff->name) }}', '{{ addslashes($staff->role) }}', '{{ addslashes($staff->department) }}', '{{ $staff->pay_period ?? $currentMonth }}')"
                                data-pay-period="{{ $staff->pay_period ?? $currentMonth }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                                            {{ strtoupper(substr($staff->name, 0, 1)) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $staff->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ ucfirst($staff->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $staff->department }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                    RM {{ number_format($staff->basic_salary, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    @if($fixedCommission)
                                        <span class="text-green-600 font-semibold">RM {{ number_format($fixedCommission, 2) }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    <div class="font-medium text-gray-900">RM {{ number_format($publicHolidayPay, 2) }}</div>
                                    <div class="text-xs text-gray-500">({{ $publicHolidayHours }} hrs)</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    <div class="font-medium text-gray-900">RM {{ number_format($normalOtPay, 2) }}</div>
                                    <div class="text-xs text-gray-500">({{ $normalOtHours }} hrs)</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    <div class="font-medium text-gray-900">RM {{ number_format($phOtPay, 2) }}</div>
                                    <div class="text-xs text-gray-500">({{ $phOtHours }} hrs)</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    <span class="text-lg font-bold text-green-600">RM {{ number_format($total, 2) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Rules Summary --}}
        <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl shadow-md p-6 border border-blue-100">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-800 mb-3">Payroll Calculation Rules</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <h4 class="font-semibold text-blue-700 mb-2 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Basic Compensation
                            </h4>
                            <ul class="space-y-1 text-sm text-gray-700">
                                <li>• Basic salary as per contract</li>
                                <li>• **RM 200** fixed commission (after 3 months of service)</li>
                            </ul>
                        </div>
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <h4 class="font-semibold text-purple-700 mb-2 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                                Overtime Rates
                            </h4>
                            <ul class="space-y-1 text-sm text-gray-700">
                                <li>• Normal OT: <span class="font-semibold">RM 12.26/hour</span></li>
                                <li>• Public Holiday OT: <span class="font-semibold">RM 21.68/hour</span></li>
                            </ul>
                        </div>
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <h4 class="font-semibold text-orange-700 mb-2 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                            </svg>
                                Public Holiday
                            </h4>
                            <ul class="space-y-1 text-sm text-gray-700">
                                <li>• Public holiday pay: <span class="font-semibold">RM 15.38/hour</span></li>
                                <li>• Calculated for hours worked on designated public holidays</li>
                            </ul>
                        </div>
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <h4 class="font-semibold text-green-700 mb-2 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                    <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"></path>
                                </svg>
                                Standard Work Hours
                            </h4>
                            <ul class="space-y-1 text-sm text-gray-700">
                                <li>• Manager/Supervisor: 12 hours/day</li>
                                <li>• Other staff: 7.5 hours/day</li>
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
<script>
// Vanilla JS fallback filter: works even if Alpine is not present.
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const monthSelect = document.getElementById('month');
    const table = document.querySelector('table.min-w-full');
    if (!table || !searchInput || !monthSelect) return;

    function normalize(s) {
        return (s || '').toString().toLowerCase();
    }

    function applyFilter() {
        const q = normalize(searchInput.value);
        const month = monthSelect.value;
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(r => {
            const name = normalize(r.querySelector('td:nth-child(1) .text-sm') ? r.querySelector('td:nth-child(1) .text-sm').textContent : r.querySelector('td:nth-child(1)').textContent);
            const role = normalize(r.querySelector('td:nth-child(2)').textContent);
            const dept = normalize(r.querySelector('td:nth-child(3)').textContent);
            const period = r.getAttribute('data-pay-period') || '';

            const matchesSearch = (name + ' ' + role + ' ' + dept).includes(q);
            const matchesPeriod = !month || month === period;
            if (matchesSearch && matchesPeriod) {
                r.style.display = '';
            } else {
                r.style.display = 'none';
            }
        });
    }

    // Apply initially so server-rendered selected month filters immediately
    applyFilter();

    searchInput.addEventListener('input', applyFilter);
    monthSelect.addEventListener('change', applyFilter);
});
</script>
@endpush