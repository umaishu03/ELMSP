<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Payslip - {{ \Carbon\Carbon::create($month)->format('F Y') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }
        .email-title {
            font-size: 20px;
            color: #1f2937;
            margin-top: 10px;
        }
        .content {
            margin: 20px 0;
        }
        .greeting {
            font-size: 16px;
            color: #1f2937;
            margin-bottom: 15px;
        }
        .message {
            font-size: 14px;
            color: #4b5563;
            margin-bottom: 20px;
            line-height: 1.8;
        }
        .payslip-info {
            background-color: #eff6ff;
            border-left: 4px solid #2563eb;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .payslip-info-item {
            margin: 8px 0;
            font-size: 14px;
        }
        .payslip-info-label {
            font-weight: bold;
            color: #1e40af;
            display: inline-block;
            width: 120px;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #2563eb;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
        .note {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 12px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 13px;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div style="text-align: center; margin-bottom: 15px;">
                <img src="data:image/webp;base64,{{ base64_encode(file_get_contents(public_path('images/sheikh bistro logo.webp'))) }}" alt="Sheikh Bistro" style="max-height: 60px; width: auto;">
                <div style="font-size: 12px; color: #4b5563; margin-top: 10px; line-height: 1.6;">
                    <strong>Sheikh Bistro</strong><br>
                    Cawangan Bukit Tambun (Pearl City Mall)<br>
                    Bandar Tasek Mutiara, 14120 Simpang Ampat,<br>
                    Pulau Pinang
                </div>
            </div>
            <div class="email-title">Your Payslip for {{ \Carbon\Carbon::create($month)->format('F Y') }}</div>
        </div>

        <div class="content">
            <div class="greeting">
                Dear {{ $user->name }},
            </div>

            <div class="message">
                Your payslip for <strong>{{ \Carbon\Carbon::create($month)->format('F Y') }}</strong> is ready and attached to this email.
            </div>

            <div class="payslip-info">
                <div class="payslip-info-item">
                    <span class="payslip-info-label">Employee:</span>
                    <span>{{ $user->name }}</span>
                </div>
                <div class="payslip-info-item">
                    <span class="payslip-info-label">Department:</span>
                    <span>{{ $staff->department ?? 'N/A' }}</span>
                </div>
                <div class="payslip-info-item">
                    <span class="payslip-info-label">Period:</span>
                    <span>{{ \Carbon\Carbon::create($month)->format('F Y') }}</span>
                </div>
                <div class="payslip-info-item">
                    <span class="payslip-info-label">Net Salary:</span>
                    <span><strong>RM {{ number_format($payroll->net_salary, 2) }}</strong></span>
                </div>
                <div class="payslip-info-item">
                    <span class="payslip-info-label">Status:</span>
                    <span style="text-transform: capitalize;"><strong>{{ $payroll->status }}</strong></span>
                </div>
            </div>

            <div class="message">
                Please find your detailed payslip attached as a PDF document. The payslip includes:
            </div>

            <ul style="font-size: 14px; color: #4b5563; line-height: 2;">
                <li>Basic salary and commission details</li>
                <li>Overtime and allowances breakdown</li>
                <li>Deductions (if any)</li>
                <li>Net salary calculation</li>
            </ul>

            <div class="note">
                <strong>Note:</strong> This is an electronically generated payslip. No signature is required. 
                Please keep this document for your records.
            </div>

            <div class="message">
                If you have any questions or concerns about your payslip, please contact the HR Department.
            </div>
        </div>

        <div class="footer">
            <p><strong>Sheikh Bistro - Payroll System</strong></p>
            <p>This is an automated email. Please do not reply to this message.</p>
            <p style="margin-top: 10px; font-size: 11px;">
                Generated on {{ now()->format('d M Y, h:i A') }}
            </p>
        </div>
    </div>
</body>
</html>

