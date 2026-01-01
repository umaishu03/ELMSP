<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #f5576c 75%, #4facfe 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">ELMSP</h1>
    </div>
    
    <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e0e0e0;">
        <h2 style="color: #333; margin-top: 0;">Password Reset Request</h2>
        
        <p>Hello {{ $user->name }},</p>
        
        <p>We received a request to reset your password for your ELMSP account. If you made this request, please click the button below to reset your password:</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $url }}" 
               style="display: inline-block; background-color: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Reset Password
            </a>
        </div>
        
        <p>Or copy and paste this link into your browser (make sure to copy the entire link without any spaces or line breaks):</p>
        <p style="word-break: break-all; color: #667eea; background: #f0f0f0; padding: 10px; border-radius: 5px; font-family: monospace; white-space: nowrap; overflow-x: auto;">
            <a href="{{ $url }}" style="color: #667eea; text-decoration: underline;">{{ $url }}</a>
        </p>
        
        <p style="color: #666; font-size: 14px; margin-top: 30px;">
            <strong>Important:</strong> This link will expire in 60 minutes. If you did not request a password reset, please ignore this email and your password will remain unchanged.
        </p>
        
        <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;">
        
        <p style="color: #666; font-size: 12px; margin: 0;">
            This is an automated message from ELMSP. Please do not reply to this email.
        </p>
    </div>
</body>
</html>

