<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your ELMSP Account Credentials</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 40px;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .system-name {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }
        .welcome-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .greeting {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }
        .message {
            font-size: 16px;
            color: #333;
            margin-bottom: 25px;
        }
        .credentials {
            margin-bottom: 25px;
        }
        .credential-line {
            margin-bottom: 10px;
            font-size: 16px;
        }
        .credential-label {
            font-weight: bold;
            color: #333;
        }
        .credential-value {
            font-weight: bold;
            color: #333;
        }
        .instruction {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
        }
        .login-button {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
        }
        .login-button:hover {
            background-color: #1d4ed8;
        }
        .signature {
            font-size: 16px;
            color: #333;
            margin-bottom: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #999;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="system-name">ELMSP</div>
        </div>
        
        <div class="welcome-title">Welcome to ELMSP</div>
        <div class="greeting">Dear {{ $user->name }},</div>
        
        <div class="message">
            Your account has been created in the ELMSP System. Here are your login credentials:
        </div>
        
        <div class="credentials">
            <div class="credential-line">
                <span class="credential-label">Email:</span> 
                <span class="credential-value">{{ $user->email }}</span>
            </div>
            <div class="credential-line">
                <span class="credential-label">Temporary Password:</span> 
                <span class="credential-value">{{ $temporaryPassword }}</span>
            </div>
            <div class="credential-line">
                <span class="credential-label">Role:</span> 
                <span class="credential-value">{{ ucfirst($user->role) }}</span>
            </div>
            @if($user->employee_id)
            <div class="credential-line">
                <span class="credential-label">Employee ID:</span> 
                <span class="credential-value">{{ $user->employee_id }}</span>
            </div>
            @endif
        </div>
        
        <div class="instruction">
            Please change your password after your first login.
        </div>
        
        <div style="text-align: center;">
            <a href="{{ route('login') }}" class="login-button">Login to System</a>
        </div>
        
        <div class="signature">Thanks,</div>
        <div class="signature">ELMSP</div>
        
        <div class="footer">
            © {{ date('Y') }} ELMSP. All rights reserved.
        </div>
    </div>
</body>
</html>