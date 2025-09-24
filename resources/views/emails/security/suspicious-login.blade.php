<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Alert - RentWheels</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #dc3545, #721c24);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 1.8rem;
        }
        .security-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .content {
            padding: 2rem;
        }
        .alert-box {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1rem 0;
            border-left: 4px solid #dc3545;
        }
        .alert-box h3 {
            color: #721c24;
            margin-top: 0;
        }
        .login-details {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f1f1f1;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: bold;
            color: #495057;
        }
        .detail-value {
            color: #212529;
            font-family: monospace;
        }
        .action-section {
            background: #d1ecf1;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1rem 0;
            border-left: 4px solid #0c5460;
        }
        .action-section h3 {
            color: #0c5460;
            margin-top: 0;
        }
        .cta-button {
            display: inline-block;
            background: #dc3545;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 1rem 0;
            font-weight: bold;
        }
        .safe-notice {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            padding: 1rem;
            margin: 1rem 0;
            border-left: 4px solid #28a745;
        }
        .safe-notice h4 {
            color: #155724;
            margin-top: 0;
        }
        .footer {
            background: #343a40;
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        .warning-text {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="security-icon">🔒</div>
            <h1>Security Alert</h1>
            <p>New login detected on your account</p>
        </div>

        <!-- Content -->
        <div class="content">
            <h2>Hello {{ $customer_name }},</h2>
            
            <div class="alert-box">
                <h3>⚠️ Suspicious Login Activity Detected</h3>
                <p>We detected a new login to your RentWheels account from a location or device we haven't seen before. This could be a sign that someone else is trying to access your account.</p>
            </div>

            <!-- Login Details -->
            <div class="login-details">
                <h3>Login Details</h3>
                <div class="detail-row">
                    <span class="detail-label">Date & Time:</span>
                    <span class="detail-value">{{ $login_time }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">IP Address:</span>
                    <span class="detail-value">{{ $ip_address }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Location:</span>
                    <span class="detail-value">{{ $location }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Device/Browser:</span>
                    <span class="detail-value">{{ $user_agent }}</span>
                </div>
            </div>

            <div class="safe-notice">
                <h4>✅ If This Was You</h4>
                <p>If you recognize this login activity, you can safely ignore this email. Your account remains secure.</p>
            </div>

            <div class="action-section">
                <h3>🚨 If This Wasn't You</h3>
                <p><strong class="warning-text">Take immediate action:</strong></p>
                <ol>
                    <li><strong>Change your password immediately</strong> - Use a strong, unique password</li>
                    <li><strong>Review your account</strong> - Check for any unauthorized changes</li>
                    <li><strong>Contact our support team</strong> - Report the suspicious activity</li>
                    <li><strong>Enable two-factor authentication</strong> - Add an extra layer of security</li>
                </ol>
                
                <div style="text-align: center; margin: 1.5rem 0;">
                    <a href="{{ $account_url }}" class="cta-button">
                        Secure My Account Now
                    </a>
                </div>
            </div>

            <h3>🔐 Account Security Tips</h3>
            <ul>
                <li><strong>Use strong passwords:</strong> Include uppercase, lowercase, numbers, and symbols</li>
                <li><strong>Don't share your login:</strong> Never give your password to anyone</li>
                <li><strong>Log out on shared devices:</strong> Always log out when using public computers</li>
                <li><strong>Keep software updated:</strong> Update your browser and operating system regularly</li>
                <li><strong>Watch for phishing:</strong> We'll never ask for your password via email</li>
            </ul>

            <div style="background: #fff3cd; padding: 1rem; border-radius: 5px; border: 1px solid #ffeaa7; margin: 1rem 0;">
                <h4>📞 Need Help?</h4>
                <p>If you have any concerns or need assistance securing your account:</p>
                <ul>
                    <li><strong>Email:</strong> <a href="mailto:{{ $support_email }}">{{ $support_email }}</a></li>
                    <li><strong>Emergency Security Line:</strong> +60-123-456-789</li>
                    <li><strong>Available:</strong> 24/7 for security issues</li>
                </ul>
            </div>

            <p style="font-size: 0.9em; color: #6c757d; margin-top: 2rem;">
                <strong>Why did I receive this email?</strong><br>
                We sent this security alert because we detected unusual login activity on your RentWheels account. 
                We take your account security seriously and want to keep you informed of any potentially suspicious activity.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>RentWheels Security Team</strong></p>
            <p>© {{ date('Y') }} RentWheels. All rights reserved.</p>
            <p style="font-size: 0.9em; opacity: 0.8;">
                This is an automated security alert. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>