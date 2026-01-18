<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payslip - {{ $user->name }} - {{ \Carbon\Carbon::create($month)->format('F Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            height: auto;
        }
        
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            color: #1f2937;
            line-height: 1.4;
            background: #ffffff;
            height: auto;
            overflow: visible;
        }
        
        .payslip-container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 10mm;
            background: #ffffff;
        }
        
        /* Header Section */
        .header {
            border-bottom: 3px solid #2563eb;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }
        
        .company-info {
            flex: 1;
        }
        
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 3px;
        }
        
        .company-tagline {
            font-size: 10px;
            color: #6b7280;
        }
        
        .payslip-title {
            text-align: right;
        }
        
        .payslip-title h1 {
            font-size: 24px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 3px;
        }
        
        .payslip-period {
            font-size: 11px;
            color: #6b7280;
        }
        
        .header-details {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            font-size: 10px;
            color: #4b5563;
        }
        
        /* Employee Information Section */
        .info-section {
            margin-bottom: 12px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-col {
            display: table-cell;
            width: 50%;
            padding: 10px;
            vertical-align: top;
            border: 1px solid #e5e7eb;
        }
        
        .info-col:first-child {
            border-right: none;
        }
        
        .section-title {
            font-size: 9px;
            font-weight: bold;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 2px solid #2563eb;
        }
        
        .info-item {
            margin-bottom: 5px;
            font-size: 11px;
        }
        
        .info-label {
            font-weight: 600;
            color: #4b5563;
            display: inline-block;
            width: 100px;
        }
        
        .info-value {
            color: #1f2937;
        }
        
        /* Earnings Section */
        .earnings-section {
            margin-bottom: 12px;
        }
        
        .section-header {
            font-size: 12px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 2px solid #2563eb;
        }
        
        .earnings-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        
        .earnings-table th {
            background: #f3f4f6;
            padding: 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            color: #374151;
            text-transform: uppercase;
            border: 1px solid #d1d5db;
        }
        
        .earnings-table td {
            padding: 8px;
            border: 1px solid #e5e7eb;
            font-size: 11px;
        }
        
        .earnings-table tr:nth-child(even) {
            background: #f9fafb;
        }
        
        .earnings-table .item-name {
            color: #4b5563;
            font-weight: 500;
        }
        
        .earnings-table .item-amount {
            text-align: right;
            font-weight: bold;
            color: #1f2937;
        }
        
        .earnings-table .total-row {
            background: #eff6ff;
            font-weight: bold;
        }
        
        .earnings-table .total-row td {
            border-top: 2px solid #2563eb;
            padding-top: 10px;
            padding-bottom: 10px;
        }
        
        /* Overtime Details */
        .overtime-section {
            margin-bottom: 12px;
        }
        
        .overtime-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        
        .overtime-item {
            display: table-cell;
            width: 33.33%;
            padding: 12px;
            text-align: center;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
        }
        
        .overtime-item:not(:last-child) {
            border-right: none;
        }
        
        .overtime-label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        
        .overtime-hours {
            font-size: 20px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 3px;
        }
        
        .overtime-rate {
            font-size: 9px;
            color: #6b7280;
        }
        
        /* Summary Section */
        .summary-section {
            background: #1e40af;
            background-image: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
            color: #ffffff;
            padding: 15px;
            margin-bottom: 12px;
            border-radius: 6px;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
        }
        
        .summary-item {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 0 15px;
        }
        
        .summary-item:not(:last-child) {
            border-right: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .summary-label {
            font-size: 9px;
            opacity: 0.9;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .summary-amount {
            font-size: 16px;
            font-weight: bold;
        }
        
        .summary-net {
            font-size: 20px;
        }
        
        /* Payment Details */
        .payment-section {
            margin-bottom: 12px;
        }
        
        .payment-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        
        .payment-col {
            display: table-cell;
            width: 50%;
            padding: 10px;
            vertical-align: top;
            border: 1px solid #e5e7eb;
        }
        
        .payment-col:first-child {
            border-right: none;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            margin-left: 10px;
        }
        
        .status-paid {
            background: #10b981;
            color: #ffffff;
        }
        
        .status-approved {
            background: #3b82f6;
            color: #ffffff;
        }
        
        .status-draft {
            background: #6b7280;
            color: #ffffff;
        }
        
        /* Footer */
        .footer {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 8px;
            color: #6b7280;
        }
        
        .footer p {
            margin-bottom: 3px;
        }
        
        /* Print Styles */
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            @page {
                size: A4;
                margin: 5mm;
            }
            
            body {
                margin: 0;
                padding: 0;
                background: white;
            }
            
            .payslip-container {
                padding: 5mm;
                margin: 0;
                max-width: 100%;
                box-shadow: none;
                height: 100%;
                overflow: hidden;
            }
            
            .summary-section {
                page-break-inside: avoid;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            /* Ensure all display types are preserved */
            .header-top {
                display: flex !important;
            }
            
            .info-grid,
            .overtime-grid,
            .summary-grid,
            .payment-grid {
                display: table !important;
                width: 100% !important;
            }
            
            .info-row,
            .overtime-item,
            .summary-item,
            .payment-col {
                display: table-cell !important;
            }
            
            table {
                display: table !important;
            }
            
            tr {
                display: table-row !important;
            }
            
            td, th {
                display: table-cell !important;
            }
            
            /* Prevent page breaks in critical sections */
            .header,
            .summary-section {
                page-break-inside: avoid;
            }
            
            /* Prevent page breaks and ensure single page */
            .payslip-container > * {
                page-break-inside: avoid;
            }
            
            /* Reduce line height for compact display */
            body {
                line-height: 1.3;
            }
            
            /* Compact spacing */
            .info-item,
            .earnings-table td,
            .earnings-table th {
                padding: 6px 8px;
            }
            
            /* Ensure summary section is visible */
            .summary-section {
                display: block !important;
                visibility: visible !important;
                background: #1e40af !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .summary-grid {
                display: table !important;
                width: 100% !important;
            }
            
            .summary-item {
                display: table-cell !important;
            }
        }
    </style>
</head>
<body>
    <div class="payslip-container">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div class="company-info">
                    <img src="data:image/webp;base64,{{ base64_encode(file_get_contents(public_path('images/sheikh bistro logo.webp'))) }}" alt="Sheikh Bistro" style="max-height: 50px; width: auto;">
                    <div style="font-size: 11px; color: #4b5563; margin-top: 8px; line-height: 1.5;">
                        <strong>Sheikh Bistro</strong><br>
                        Cawangan Bukit Tambun (Pearl City Mall)<br>
                        Bandar Tasek Mutiara, 14120 Simpang Ampat,<br>
                        Pulau Pinang
                    </div>
                </div>
                <div class="payslip-title">
                    <h1>PAYSLIP</h1>
                    <div class="payslip-period">{{ \Carbon\Carbon::create($month)->format('F Y') }}</div>
                </div>
            </div>
            <div class="header-details">
                <div>
                    <strong>Employee ID:</strong> {{ $staff->employee_id ?? 'N/A' }}
                </div>
                <div>
                    <strong>Payroll Period:</strong> {{ $month }}
                </div>
            </div>
        </div>

        <!-- Employee Information -->
        <div class="info-section">
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-col">
                        <div class="section-title">Employee Information</div>
                        <div class="info-item">
                            <span class="info-label">Name:</span>
                            <span class="info-value">{{ $user->name }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-value">{{ $user->email }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Phone:</span>
                            <span class="info-value">{{ $user->phone ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="info-col">
                        <div class="section-title">Employment Details</div>
                        <div class="info-item">
                            <span class="info-label">Department:</span>
                            <span class="info-value">{{ $staff->department ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Hire Date:</span>
                            <span class="info-value">{{ $staff->hire_date ? $staff->hire_date->format('d M Y') : 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Role:</span>
                            <span class="info-value">{{ ucfirst($user->role) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Earnings Section -->
        <div class="earnings-section">
            <div class="section-header">Earnings & Allowances</div>
            <table class="earnings-table">
                <thead>
                    <tr>
                        <th style="width: 60%;">Description</th>
                        <th style="width: 40%; text-align: right;">Amount (RM)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="item-name">Basic Salary</td>
                        <td class="item-amount">{{ number_format($payroll->basic_salary, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="item-name">Fixed Commission</td>
                        <td class="item-amount">{{ number_format($payroll->fixed_commission, 2) }}</td>
                    </tr>
                    @if(($payroll->marketing_bonus ?? 0) > 0)
                    <tr>
                        <td class="item-name">Marketing Bonus</td>
                        <td class="item-amount">{{ number_format($payroll->marketing_bonus, 2) }}</td>
                    </tr>
                    @endif
                    @if(($payroll->extra_day_pay ?? 0) > 0)
                    <tr>
                        <td class="item-name">
                            Extra Day Pay
                            @if(($payroll->extra_days ?? 0) > 0)
                                ({{ $payroll->extra_days }} day{{ ($payroll->extra_days ?? 0) > 1 ? 's' : '' }})
                            @endif
                        </td>
                        <td class="item-amount">{{ number_format($payroll->extra_day_pay, 2) }}</td>
                    </tr>
                    @endif
                    @if(($payroll->fulltime_ot_pay ?? 0) > 0)
                    <tr>
                        <td class="item-name">Overtime (Regular)</td>
                        <td class="item-amount">{{ number_format($payroll->fulltime_ot_pay, 2) }}</td>
                    </tr>
                    @endif
                    @if(($payroll->public_holiday_pay ?? 0) > 0)
                    <tr>
                        <td class="item-name">Public Holiday Pay</td>
                        <td class="item-amount">{{ number_format($payroll->public_holiday_pay, 2) }}</td>
                    </tr>
                    @endif
                    @if(($payroll->public_holiday_ot_pay ?? 0) > 0)
                    <tr>
                        <td class="item-name">Public Holiday Overtime</td>
                        <td class="item-amount">{{ number_format($payroll->public_holiday_ot_pay, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="total-row">
                        <td class="item-name">Gross Salary</td>
                        <td class="item-amount">{{ number_format($payroll->gross_salary, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Overtime Details (if applicable) -->
        @if(($payroll->fulltime_ot_hours ?? 0) > 0 || ($payroll->public_holiday_ot_hours ?? 0) > 0 || ($payroll->public_holiday_hours ?? 0) > 0)
        <div class="overtime-section">
            <div class="section-header">Overtime Breakdown</div>
            <div class="overtime-grid">
                @if(($payroll->fulltime_ot_hours ?? 0) > 0)
                <div class="overtime-item">
                    <div class="overtime-label">Fulltime OT Hours</div>
                    <div class="overtime-hours">{{ number_format($payroll->fulltime_ot_hours, 1) }}</div>
                    <div class="overtime-rate">@ RM 12.26/hour</div>
                </div>
                @endif
                @if(($payroll->public_holiday_ot_hours ?? 0) > 0)
                <div class="overtime-item">
                    <div class="overtime-label">Public Holiday OT Hours</div>
                    <div class="overtime-hours">{{ number_format($payroll->public_holiday_ot_hours, 1) }}</div>
                    <div class="overtime-rate">@ RM 21.68/hour</div>
                </div>
                @endif
                @if(($payroll->public_holiday_hours ?? 0) > 0)
                <div class="overtime-item">
                    <div class="overtime-label">Public Holiday Hours</div>
                    <div class="overtime-hours">{{ number_format($payroll->public_holiday_hours, 1) }}</div>
                    <div class="overtime-rate">@ RM 15.38/hour</div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Summary Section -->
        <div class="summary-section">
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Gross Salary</div>
                    <div class="summary-amount">RM {{ number_format($payroll->gross_salary, 2) }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Deductions</div>
                    <div class="summary-amount">RM {{ number_format($payroll->total_deductions, 2) }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Net Salary</div>
                    <div class="summary-amount summary-net">RM {{ number_format($payroll->net_salary, 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Payment Details -->
        <div class="payment-section">
            <div class="payment-grid">
                <div class="payment-col">
                    <div class="section-title">Payment Status</div>
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        <span class="status-badge status-{{ $payroll->status }}">
                            {{ ucfirst($payroll->status) }}
                        </span>
                    </div>
                    @if($payroll->payment_date)
                    <div class="info-item">
                        <span class="info-label">Payment Date:</span>
                        <span class="info-value">{{ $payroll->payment_date->format('d M Y') }}</span>
                    </div>
                    @endif
                    @if($payroll->remarks)
                    <div class="info-item">
                        <span class="info-label">Remarks:</span>
                        <span class="info-value">{{ $payroll->remarks }}</span>
                    </div>
                    @endif
                </div>
                <div class="payment-col">
                    <div class="section-title">Calculation Period</div>
                    <div class="info-item">
                        <span class="info-label">Year:</span>
                        <span class="info-value">{{ $payroll->year }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Month:</span>
                        <span class="info-value">{{ \Carbon\Carbon::createFromFormat('n', $payroll->month)->format('F') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Generated:</span>
                        <span class="info-value">{{ $payroll->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>This is an electronically generated payslip. No signature is required.</strong></p>
            <p>For inquiries, please contact HR Department</p>
            <p style="margin-top: 10px; font-size: 9px;">Generated on {{ now()->format('d M Y, h:i A') }}</p>
        </div>
    </div>
</body>
</html>
