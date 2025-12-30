@extends('layouts.staff')

@section('title', 'Payslip')

@section('content')
<!-- Breadcrumbs -->
<div class="mb-6">
    {!! \App\Helpers\BreadcrumbHelper::render() !!} 
</div>

<div class="container mx-auto px-4 py-8">
    @if(session('success'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-2">My Payslip</h1>
                <p class="text-gray-600 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    View your payslips for previous months
                </p>
            </div>
        </div>
    </div>

    {{-- Month Selection --}}
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <div class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="flex-1">
                <label for="monthSelect" class="block text-sm font-semibold text-gray-700 mb-2">Select Month</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <select id="monthSelect" name="month" 
                            class="block w-full pl-10 pr-10 py-3 text-base border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg bg-white transition duration-150 ease-in-out">
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
            </div>
            <div class="flex items-end">
                <button id="viewPayslipBtn" 
                        class="w-full sm:w-auto px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-150 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    View Payslip
                </button>
            </div>
        </div>
    </div>

    <!-- Error Message Area -->
    <div id="errorMessage" class="hidden mb-6">
        <div class="bg-red-50 border border-red-200 rounded-xl shadow-lg p-6">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1">
                    <h3 class="text-red-800 font-semibold mb-1 text-lg">Payslip Not Available</h3>
                    <p id="errorText" class="text-red-700"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Payslip Display Area -->
    <div id="payslipContainer" class="hidden">
        <div class="bg-white rounded-xl shadow-xl overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex items-center justify-between">
                <h2 class="text-xl font-bold text-white">Payslip Details</h2>
                <div class="flex items-center gap-2">
                    <button id="printBtn" 
                            class="px-4 py-2 bg-white text-gray-700 rounded-lg font-semibold hover:bg-gray-100 transition-colors duration-150 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print
                    </button>
                    <button id="exportPdfBtn" 
                            class="px-4 py-2 bg-red-500 text-white rounded-lg font-semibold hover:bg-red-600 transition-colors duration-150 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Download PDF
                    </button>
                </div>
            </div>

            <!-- Payslip Content -->
            <div id="payslipContent" class="p-8 print-content">
                <!-- Content will be loaded here via AJAX -->
            </div>
        </div>
    </div>

    <!-- No Selection Message -->
    <div id="noSelectionMsg" class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl shadow-md p-8 text-center">
        <div class="flex flex-col items-center">
            <svg class="w-16 h-16 text-blue-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="text-gray-700 text-lg font-semibold mb-2">Select a month to view your payslip</p>
            <p class="text-sm text-gray-600">Note: Payslips are only available for <span class="font-semibold text-blue-600">approved</span> or <span class="font-semibold text-blue-600">paid</span> payrolls</p>
        </div>
    </div>
</div>

<style>
    @media print {
        /* Hide everything by default */
        body * { 
            visibility: hidden;
        }
        
        /* Show only the payslip container and its contents */
        #payslipContainer,
        #payslipContainer * { 
            visibility: visible !important;
        }
        
        /* Ensure payslip content is visible and properly displayed */
        #payslipContent {
            display: block !important;
            visibility: visible !important;
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        
        /* Preserve original display types from payslip template */
        #payslipContent .payslip-container {
            display: block !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 20mm !important;
        }
        
        #payslipContent .header-top {
            display: flex !important;
        }
        
        #payslipContent .info-grid,
        #payslipContent .overtime-grid,
        #payslipContent .summary-grid,
        #payslipContent .payment-grid {
            display: table !important;
            width: 100% !important;
        }
        
        #payslipContent .info-row,
        #payslipContent .overtime-item,
        #payslipContent .summary-item,
        #payslipContent .payment-col {
            display: table-cell !important;
        }
        
        #payslipContent table {
            display: table !important;
        }
        
        #payslipContent tr {
            display: table-row !important;
        }
        
        #payslipContent td,
        #payslipContent th {
            display: table-cell !important;
        }
        
        /* Hide action buttons and header in print */
        #payslipContainer button,
        #payslipContainer > div > div:first-child {
            display: none !important;
            visibility: hidden !important;
        }
        
        /* Hide sidebar and other page elements */
        nav,
        aside,
        header:not(#payslipContent),
        .sidebar,
        .breadcrumbs {
            display: none !important;
            visibility: hidden !important;
        }
        
        /* Prevent page breaks inside content */
        .print-content,
        #payslipContent {
            page-break-after: avoid;
            page-break-inside: avoid;
        }
        
        /* Ensure body and html are visible for print */
        html,
        body {
            visibility: visible;
            background: white;
        }
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
    const errorMessage = document.getElementById('errorMessage');
    const errorText = document.getElementById('errorText');

    viewBtn.addEventListener('click', async function() {
        const month = monthSelect.value;

        if (!month) {
            errorText.textContent = 'Please select a month';
            errorMessage.classList.remove('hidden');
            return;
        }

        // Hide previous messages
        errorMessage.classList.add('hidden');
        payslipContainer.classList.add('hidden');
        noSelectionMsg.classList.add('hidden');

        try {
            const response = await fetch(`/staff/payslip/${month}`, {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            
            if (!response.ok || !data.success) {
                // Show error message
                errorText.textContent = data.message || 'Failed to load payslip';
                errorMessage.classList.remove('hidden');
                noSelectionMsg.classList.add('hidden');
                return;
            }

            // Display payslip
            payslipContent.innerHTML = data.html;
            payslipContainer.classList.remove('hidden');
            noSelectionMsg.classList.add('hidden');
            errorMessage.classList.add('hidden');
        } catch (error) {
            console.error(error);
            errorText.textContent = 'Error loading payslip. Please try again.';
            errorMessage.classList.remove('hidden');
            noSelectionMsg.classList.add('hidden');
        }
    });

    printBtn.addEventListener('click', function() {
        // Check if payslip is loaded and visible
        if (payslipContainer.classList.contains('hidden') || !payslipContent.innerHTML.trim()) {
            alert('Please load a payslip first by clicking "View Payslip"');
            return;
        }
        
        // Ensure container is visible for print
        payslipContainer.classList.remove('hidden');
        noSelectionMsg.classList.add('hidden');
        errorMessage.classList.add('hidden');
        
        // Force a reflow to ensure styles are applied
        payslipContainer.offsetHeight;
        
        // Small delay to ensure DOM is updated and styles are applied, then print
        setTimeout(function() {
            // Create a new window for printing to avoid layout issues
            const printWindow = window.open('', '_blank');
            const payslipHTML = payslipContent.innerHTML;
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Payslip - Print</title>
                    <style>
                        @media print {
                            @page {
                                margin: 0;
                                size: A4;
                            }
                            body {
                                margin: 0;
                                padding: 0;
                            }
                        }
                    </style>
                </head>
                <body>
                    ${payslipHTML}
                </body>
                </html>
            `);
            
            printWindow.document.close();
            
            // Wait for content to load, then print
            printWindow.onload = function() {
                setTimeout(function() {
                    printWindow.print();
                    // Close the window after printing (optional)
                    // printWindow.close();
                }, 250);
            };
        }, 100);
    });

    exportPdfBtn.addEventListener('click', async function() {
        const month = monthSelect.value;
        if (!month) {
            errorText.textContent = 'Please select a month';
            errorMessage.classList.remove('hidden');
            return;
        }

        try {
            // Check if payslip is available before exporting
            const response = await fetch(`/staff/payslip/${month}`, {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            
            if (!response.ok || !data.success) {
                errorText.textContent = data.message || 'Payslip is not available for export';
                errorMessage.classList.remove('hidden');
                return;
            }

            // If available, proceed with PDF export
            window.location.href = `/staff/payslip/${month}/pdf`;
        } catch (error) {
            console.error(error);
            errorText.textContent = 'Error exporting payslip. Please try again.';
            errorMessage.classList.remove('hidden');
        }
    });
});
</script>
@endsection
