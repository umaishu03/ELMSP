<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your ELMSP Account Credentials</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
        }
        .email-wrapper {
            padding: 40px 20px;
            min-height: 100vh;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: #ffffff;
        }
        .system-name {
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        .header-subtitle {
            font-size: 14px;
            opacity: 0.9;
            font-weight: 300;
        }
        .content {
            padding: 40px 30px;
        }
        .welcome-title {
            font-size: 28px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 12px;
            text-align: center;
        }
        .greeting {
            font-size: 18px;
            color: #4a5568;
            margin-bottom: 24px;
            text-align: center;
        }
        .message {
            font-size: 16px;
            color: #4a5568;
            margin-bottom: 30px;
            text-align: center;
            line-height: 1.8;
        }
        .credentials-box {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
        }
        .credential-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        .credential-item:last-child {
            border-bottom: none;
        }
        .credential-label {
            font-weight: 600;
            color: #4a5568;
            font-size: 15px;
            display: flex;
            align-items: center;
        }
        .credential-label::before {
            content: "✓";
            display: inline-block;
            width: 24px;
            height: 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 24px;
            font-size: 12px;
            margin-right: 12px;
            font-weight: bold;
        }
        .credential-value {
            font-weight: 700;
            color: #1a202c;
            font-size: 15px;
            background: white;
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            font-family: 'Courier New', monospace;
        }
        .instruction-box {
            background: #fff5e6;
            border-left: 4px solid #ffa726;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .instruction {
            font-size: 15px;
            color: #856404;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        .instruction::before {
            content: "⚠";
            font-size: 20px;
            margin-right: 10px;
        }
        .button-container {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            padding: 16px 40px;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
        }
        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }
        .signature-section {
            border-top: 1px solid #e2e8f0;
            padding-top: 30px;
            margin-top: 30px;
        }
        .signature {
            font-size: 16px;
            color: #4a5568;
            margin-bottom: 8px;
        }
        .signature-name {
            font-weight: 700;
            color: #1a202c;
            font-size: 18px;
        }
        .footer {
            background: #f7fafc;
            padding: 24px 30px;
            text-align: center;
            color: #718096;
            font-size: 13px;
            border-top: 1px solid #e2e8f0;
        }
        .footer-text {
            margin-bottom: 8px;
        }
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                padding: 20px 10px;
            }
            .content {
                padding: 30px 20px;
            }
            .header {
                padding: 30px 20px;
            }
            .system-name {
                font-size: 28px;
            }
            .welcome-title {
                font-size: 24px;
            }
            .credential-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            .credential-value {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="header">
                <div class="system-name">ELMSP</div>
                <div class="header-subtitle">Employee Leave Management System</div>
            </div>
            
            <div class="content">
                <div class="welcome-title">Welcome to ELMSP! 🎉</div>
                <div class="greeting">Dear {{ $user->name }},</div>
                
                <div class="message">
                    Your account has been successfully created in the ELMSP System. Below are your login credentials to get started:
                </div>
                
                <div class="credentials-box">
                    <div class="credential-item">
                        <span class="credential-label">Email Address</span>
                        <span class="credential-value">{{ $user->email }}</span>
                    </div>
                    <div class="credential-item">
                        <span class="credential-label">Temporary Password</span>
                        <span class="credential-value">{{ $temporaryPassword }}</span>
                    </div>
                    <div class="credential-item">
                        <span class="credential-label">Role</span>
                        <span class="credential-value">{{ ucfirst($user->role) }}</span>
                    </div>
                    @if($user->staff && $user->staff->employee_id)
                    <div class="credential-item">
                        <span class="credential-label">Employee ID</span>
                        <span class="credential-value">{{ $user->staff->employee_id }}</span>
                    </div>
                    @endif
                </div>
                
                <div class="instruction-box">
                    <div class="instruction">
                        For security purposes, please change your password immediately after your first login.
                    </div>
                </div>
                
                <div class="button-container">
                    <a href="{{ route('login') }}" class="login-button">Login to System</a>
                </div>
                
                <div class="signature-section">
                    <div class="signature">Best regards,</div>
                    <div class="signature signature-name">ELMSP Team</div>
                </div>
            </div>
            
            <div class="footer">
                <div class="footer-text">© {{ date('Y') }} ELMSP. All rights reserved.</div>
                <div class="footer-text">This is an automated email. Please do not reply.</div>
            </div>
        </div>
    </div>
</body>
</html>