<div class="payslip-document bg-white">
    <!-- Payslip Header -->
    <div class="border-b-2 border-gray-400 pb-6 mb-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">PAYSLIP</h1>
                <p class="text-gray-600 text-sm mt-1">{{ \Carbon\Carbon::create($month)->format('F Y') }}</p>
            </div>
            <div class="text-right text-gray-700 text-sm">
                <p><span class="font-semibold">Employee ID:</span> {{ $user->employee_id }}</p>
                <p><span class="font-semibold">Payroll Period:</span> {{ $month }}</p>
            </div>
        </div>
    </div>

    <!-- Employee Information -->
    <div class="grid grid-cols-2 gap-8 mb-8 pb-6 border-b">
        <div>
            <h3 class="text-sm font-bold text-gray-600 uppercase mb-2">Employee Information</h3>
            <div class="space-y-2 text-sm text-gray-700">
                <div><span class="font-semibold">Name:</span> {{ $user->name }}</div>
                <div><span class="font-semibold">Email:</span> {{ $user->email }}</div>
                <div><span class="font-semibold">Phone:</span> {{ $user->phone ?? 'N/A' }}</div>
            </div>
        </div>
        <div>
            <h3 class="text-sm font-bold text-gray-600 uppercase mb-2">Employment Details</h3>
            <div class="space-y-2 text-sm text-gray-700">
                <div><span class="font-semibold">Department:</span> {{ $staff->department ?? 'N/A' }}</div>
                <div><span class="font-semibold">Hire Date:</span> {{ $staff->hire_date->format('d M Y') ?? 'N/A' }}</div>
                <div><span class="font-semibold">Role:</span> {{ ucfirst($user->role) }}</div>
            </div>
        </div>
    </div>

    <!-- Earnings Section -->
    <div class="mb-8">
        <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b-2 border-blue-500">Earnings</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-blue-50 p-4 rounded">
                <div class="flex justify-between items-center mb-3">
                    <span class="text-gray-700 font-semibold">Basic Salary</span>
                    <span class="text-gray-900 font-bold">RM {{ number_format($payroll->basic_salary, 2) }}</span>
                </div>
                <div class="flex justify-between items-center mb-3">
                    <span class="text-gray-700 font-semibold">Fixed Commission</span>
                    <span class="text-gray-900 font-bold">RM {{ number_format($payroll->fixed_commission, 2) }}</span>
                </div>
                    <div class="flex justify-between py-1">
                        <div class="text-sm text-gray-600">Marketing Bonus</div>
                        <div class="text-gray-900 font-bold">RM {{ number_format($payroll->marketing_bonus ?? 0, 2) }}</div>
                    </div>
                <div class="flex justify-between items-center pt-3 border-t border-blue-200">
                    <span class="text-gray-700 font-semibold">Base Total</span>
                    <span class="text-blue-700 font-bold">RM {{ number_format($payroll->basic_salary + $payroll->fixed_commission, 2) }}</span>
                </div>
            </div>

            <div class="bg-green-50 p-4 rounded">
                <div class="flex justify-between items-center mb-3">
                    <span class="text-gray-700 font-semibold">Overtime (Regular)</span>
                    <span class="text-gray-900 font-bold">RM {{ number_format($payroll->fulltime_ot_pay, 2) }}</span>
                </div>
                <div class="flex justify-between items-center mb-3">
                    <span class="text-gray-700 font-semibold">Public Holiday Pay</span>
                    <span class="text-gray-900 font-bold">RM {{ number_format($payroll->public_holiday_pay, 2) }}</span>
                </div>
                <div class="flex justify-between items-center pt-3 border-t border-green-200">
                    <span class="text-gray-700 font-semibold">Allowances Total</span>
                    <span class="text-green-700 font-bold">RM {{ number_format($payroll->fulltime_ot_pay + $payroll->public_holiday_pay, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Overtime Details -->
    <div class="mb-8">
        <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b-2 border-purple-500">Overtime Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-purple-50 p-4 rounded text-center">
                <div class="text-sm text-gray-600 mb-1">Fulltime OT Hours</div>
                <div class="text-2xl font-bold text-purple-700">{{ $payroll->fulltime_ot_hours }}</div>
                <div class="text-sm text-gray-600 mt-2">@ RM 12.26/hr</div>
            </div>
            <div class="bg-orange-50 p-4 rounded text-center">
                <div class="text-sm text-gray-600 mb-1">Public Holiday OT Hours</div>
                <div class="text-2xl font-bold text-orange-700">{{ $payroll->public_holiday_ot_hours }}</div>
                <div class="text-sm text-gray-600 mt-2">@ RM 21.68/hr</div>
            </div>
            <div class="bg-indigo-50 p-4 rounded text-center">
                <div class="text-sm text-gray-600 mb-1">Public Holiday Hours</div>
                <div class="text-2xl font-bold text-indigo-700">{{ $payroll->public_holiday_hours }}</div>
                <div class="text-sm text-gray-600 mt-2">@ RM 15.38/hr</div>
            </div>
        </div>
    </div>

    <!-- Summary Section -->
    <div class="mb-8 bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6 rounded-lg">
        <div class="grid grid-cols-3 gap-6 text-center">
            <div>
                <div class="text-sm opacity-90 mb-1">Gross Salary</div>
                <div class="text-2xl font-bold">RM {{ number_format($payroll->gross_salary, 2) }}</div>
            </div>
            <div class="border-l border-r border-white border-opacity-30">
                <div class="text-sm opacity-90 mb-1">Deductions</div>
                <div class="text-2xl font-bold">RM {{ number_format($payroll->total_deductions, 2) }}</div>
            </div>
            <div>
                <div class="text-sm opacity-90 mb-1">Net Salary</div>
                <div class="text-3xl font-bold">RM {{ number_format($payroll->net_salary, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Payment Details -->
    <div class="grid grid-cols-2 gap-8 pb-6 border-b mb-6">
        <div>
            <h3 class="text-sm font-bold text-gray-600 uppercase mb-3">Payment Status</h3>
            <div class="space-y-2 text-sm text-gray-700">
                <div>
                    <span class="font-semibold">Status:</span>
                    <span class="ml-2 px-3 py-1 rounded-full text-white text-xs font-semibold
                        @if($payroll->status === 'paid')
                            bg-green-600
                        @elseif($payroll->status === 'approved')
                            bg-blue-600
                        @else
                            bg-gray-600
                        @endif
                    ">
                        {{ ucfirst($payroll->status) }}
                    </span>
                </div>
                @if($payroll->payment_date)
                    <div><span class="font-semibold">Payment Date:</span> {{ $payroll->payment_date->format('d M Y') }}</div>
                @endif
                @if($payroll->remarks)
                    <div><span class="font-semibold">Remarks:</span> {{ $payroll->remarks }}</div>
                @endif
            </div>
        </div>
        <div>
            <h3 class="text-sm font-bold text-gray-600 uppercase mb-3">Calculation Period</h3>
            <div class="space-y-2 text-sm text-gray-700">
                <div><span class="font-semibold">Year:</span> {{ $payroll->year }}</div>
                <div><span class="font-semibold">Month:</span> {{ \Carbon\Carbon::createFromFormat('n', $payroll->month)->format('F') }}</div>
                <div><span class="font-semibold">Generated:</span> {{ $payroll->created_at->format('d M Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="pt-6 border-t text-center text-xs text-gray-600">
        <p>This is an electronically generated payslip. No signature is required.</p>
        <p class="mt-1">For inquiries, please contact HR Department</p>
    </div>
</div>
