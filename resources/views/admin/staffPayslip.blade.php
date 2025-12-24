@extends('layouts.admin')
@section('title', 'Staff Payslip')
@section('content')
<!-- Breadcrumbs -->
<div class="mb-6">
    {!! \App\Helpers\BreadcrumbHelper::render() !!}
</div>

<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">Staff Payslip</h1>
        <p class="text-gray-600">View and manage staff payslips for selected period</p>
    </div>

    <!-- Selection Controls -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="staffSelect" class="block text-sm font-semibold text-gray-700 mb-2">Select Staff</label>
                <select id="staffSelect" name="staff" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Select Staff Member --</option>
                    @foreach($staffList as $staff)
                        <option value="{{ $staff->user->id }}" data-department="{{ $staff->department }}">
                            {{ $staff->user->name }} ({{ $staff->department }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="monthSelect" class="block text-sm font-semibold text-gray-700 mb-2">Select Month</label>
                <select id="monthSelect" name="month" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Select Month --</option>
                    @php
                        for ($i = 0; $i < 12; $i++) {
                            $date = now()->subMonths($i);
                            $value = $date->format('Y-m');
                            $label = $date->format('F Y');
                    @endphp
                        <option value="{{ $value }}">{{ $label }}</option>
                    @php
                        }
                    @endphp
                </select>
            </div>
            <div class="flex items-end">
                <button id="viewPayslipBtn" 
                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150">
                    <i class="fas fa-search mr-2"></i> View Payslip
                </button>
            </div>
        </div>
    </div>

    <!-- Payslip Display Area -->
    <div id="payslipContainer" class="hidden">
        <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
            <!-- Action Buttons -->
            <div class="flex gap-3 mb-6 border-b pb-4">
                <button id="printBtn" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-print mr-2"></i> Print
                </button>
                <button id="exportPdfBtn" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-file-pdf mr-2"></i> Export PDF
                </button>
                <button id="emailBtn" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-envelope mr-2"></i> Email
                </button>
            </div>

            <!-- Payslip Content -->
            <div id="payslipContent" class="print-content">
                <!-- Content will be loaded here via AJAX -->
            </div>
        </div>
    </div>

    <!-- No Selection Message -->
    <div id="noSelectionMsg" class="bg-blue-50 border border-blue-200 rounded-lg p-8 text-center">
        <i class="fas fa-info-circle text-blue-600 text-4xl mb-4"></i>
        <p class="text-gray-700 text-lg">Select a staff member and month to view payslip</p>
    </div>
</div>

<style>
    @media print {
        body * { display: none; }
        #payslipContent, #payslipContent * { display: block !important; }
        #payslipContainer { display: block !important; }
        .print-content { page-break-after: avoid; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const staffSelect = document.getElementById('staffSelect');
    const monthSelect = document.getElementById('monthSelect');
    const viewBtn = document.getElementById('viewPayslipBtn');
    const printBtn = document.getElementById('printBtn');
    const exportPdfBtn = document.getElementById('exportPdfBtn');
    const emailBtn = document.getElementById('emailBtn');
    const payslipContainer = document.getElementById('payslipContainer');
    const noSelectionMsg = document.getElementById('noSelectionMsg');
    const payslipContent = document.getElementById('payslipContent');

    viewBtn.addEventListener('click', async function() {
        const staffId = staffSelect.value;
        const month = monthSelect.value;

        if (!staffId || !month) {
            alert('Please select both staff member and month');
            return;
        }

        try {
            const response = await fetch(`/admin/payslip/${staffId}/${month}`, {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            
            if (!response.ok || !data.success) {
                alert(data.message || 'Failed to load payslip');
                return;
            }

            // Display payslip
            payslipContent.innerHTML = data.html;
            payslipContainer.classList.remove('hidden');
            noSelectionMsg.classList.add('hidden');
        } catch (error) {
            console.error(error);
            alert('Error loading payslip');
        }
    });

    printBtn.addEventListener('click', function() {
        window.print();
    });

    exportPdfBtn.addEventListener('click', async function() {
        const staffId = staffSelect.value;
        const month = monthSelect.value;
        if (!staffId || !month) return;

        // For now, use print dialog with PDF printer
        // In production, use a library like mPDF or DomPDF
        window.location.href = `/admin/payslip/${staffId}/${month}/pdf`;
    });

    emailBtn.addEventListener('click', async function() {
        const staffId = staffSelect.value;
        const month = monthSelect.value;
        if (!staffId || !month) return;

        try {
            const response = await fetch(`/admin/payslip/${staffId}/${month}/email`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            const data = await response.json();
            alert(data.message || 'Email sent successfully');
        } catch (error) {
            console.error(error);
            alert('Error sending email');
        }
    });
});
</script>
@endsection
