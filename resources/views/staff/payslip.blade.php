@extends('layouts.staff')
@section('title', 'Payslip')
@section('content')
<!-- Breadcrumbs -->
<div class="mb-6">
    {!! \App\Helpers\BreadcrumbHelper::render() !!}
</div>
<div class="mb-8">
    <h1 class="text-4xl font-bold text-gray-800 mb-2">My Payslip</h1>
    <p class="text-gray-600">View your payslips for previous months</p>
</div>
<div class="container mx-auto px-4 py-8">
    <!-- Month Selection -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <div class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="flex-1">
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
            <button id="viewPayslipBtn" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150">
                <i class="fas fa-search mr-2"></i> View Payslip
            </button>
        </div>
    </div>

    <!-- Payslip Display Area -->
    <div id="payslipContainer" class="hidden">
        <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
            <!-- Action Buttons -->
            <div class="flex gap-3 mb-6 border-b pb-4 flex-wrap">
                <button id="printBtn" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-print mr-2"></i> Print
                </button>
                <button id="exportPdfBtn" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-file-pdf mr-2"></i> Download PDF
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
        <p class="text-gray-700 text-lg">Select a month to view your payslip</p>
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
    const monthSelect = document.getElementById('monthSelect');
    const viewBtn = document.getElementById('viewPayslipBtn');
    const printBtn = document.getElementById('printBtn');
    const exportPdfBtn = document.getElementById('exportPdfBtn');
    const payslipContainer = document.getElementById('payslipContainer');
    const noSelectionMsg = document.getElementById('noSelectionMsg');
    const payslipContent = document.getElementById('payslipContent');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    viewBtn.addEventListener('click', async function() {
        const month = monthSelect.value;

        if (!month) {
            alert('Please select a month');
            return;
        }

        try {
            const response = await fetch(`/payslip/${month}`, {
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
        const month = monthSelect.value;
        if (!month) return;
        window.location.href = `/payslip/${month}/pdf`;
    });
});
</script>
@endsection
