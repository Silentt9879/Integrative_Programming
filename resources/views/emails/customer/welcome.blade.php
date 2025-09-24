<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to RentWheels</title>
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
            background: linear-gradient(135deg, #dc3545, #6f42c1);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 2rem;
        }
        .content {
            padding: 2rem;
        }
        .welcome-message {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1rem 0;
            border-left: 4px solid #dc3545;
        }
        .next-steps {
            background: #e3f2fd;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
        .next-steps h3 {
            color: #1976d2;
            margin-top: 0;
        }
        .next-steps ul {
            margin: 0;
            padding-left: 1.5rem;
        }
        .next-steps li {
            margin-bottom: 0.5rem;
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
        .footer {
            background: #343a40;
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        .profile-incomplete {
            background: #fff3cd;
            padding: 1rem;
            border-radius: 5px;
            border: 1px solid #ffeaa7;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🚗 Welcome to RentWheels!</h1>
            <p>Your journey starts here</p>
        </div>

        <!-- Content -->
        <div class="content">
            <h2>Hello {{ $customer_name }}! 👋</h2>
            
            <div class="welcome-message">
                <p>Welcome to the RentWheels family! We're thrilled to have you join our community of satisfied customers.</p>
                <p>Your account has been successfully created and is ready to use. You can now browse our extensive fleet of vehicles and make bookings with ease.</p>
            </div>

            @if(!$has_complete_profile)
            <div class="profile-incomplete">
                <h3>🔧 Complete Your Profile</h3>
                <p>To get the most out of RentWheels and ensure smooth bookings, please complete your profile by adding:</p>
                <ul>
                    <li>Phone number (for booking confirmations)</li>
                    <li>Date of birth (required for insurance)</li>
                    <li>Address (for delivery options)</li>
                </ul>
            </div>
            @endif

            <div class="next-steps">
                <h3>🚀 Get Started</h3>
                <p>Here's what you can do next:</p>
                <ul>
                    @foreach($next_steps as $step)
                    <li>{{ $step }}</li>
                    @endforeach
                </ul>
            </div>

            <div style="text-align: center; margin: 2rem 0;">
                <a href="{{ $login_url }}" class="cta-button">
                    Login to Your Account
                </a>
            </div>

            <hr>

            <h3>📞 Need Help?</h3>
            <p>If you have any questions or need assistance, don't hesitate to contact our support team:</p>
            <ul>
                <li>📧 Email: <a href="mailto:{{ $support_email }}">{{ $support_email }}</a></li>
                <li>📱 Phone: +60-123-456-789</li>
                <li>💬 Live Chat: Available 24/7 on our website</li>
            </ul>

            <p><strong>Registration Details:</strong></p>
            <ul>
                <li>Account Email: {{ $customer_email }}</li>
                <li>Registration Date: {{ $registration_date }}</li>
            </ul>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>RentWheels</strong> - Your Trusted Vehicle Rental Partner</p>
            <p>© {{ date('Y') }} RentWheels. All rights reserved.</p>
            <p style="font-size: 0.9em; opacity: 0.8;">
                This email was sent because you recently registered for a RentWheels account. 
                If you didn't register, please ignore this email.
            </p>
        </div>
    </div>
</body>
</html>