<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Back to RentWheels</title>
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
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .content {
            padding: 2rem;
        }
        .welcome-back-message {
            background: #d4edda;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1rem 0;
            border-left: 4px solid #28a745;
        }
        .special-offers {
            background: #fff3cd;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1rem 0;
            border-left: 4px solid #ffc107;
        }
        .cta-button {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 0.5rem;
            font-weight: bold;
        }
        .footer {
            background: #343a40;
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome Back, {{ $customer_name }}! 🎉</h1>
            <p>We missed you at RentWheels</p>
        </div>

        <div class="content">
            <div class="welcome-back-message">
                <h3>Great to see you again!</h3>
                <p>It's been {{ $last_login }} since your last visit. We're excited to have you back and ready to help with your vehicle rental needs.</p>
            </div>

            <div class="special-offers">
                <h3>🎁 Special Offers Just for You!</h3>
                <ul>
                    @foreach($special_offers as $offer)
                    <li>{{ $offer }}</li>
                    @endforeach
                </ul>
            </div>

            <div style="text-align: center; margin: 2rem 0;">
                <a href="{{ $browse_url }}" class="cta-button">Browse Vehicles</a>
                <a href="{{ $dashboard_url }}" class="cta-button">My Dashboard</a>
            </div>

            <p>Thank you for choosing RentWheels for your transportation needs. We're here to make your rental experience smooth and enjoyable.</p>
        </div>

        <div class="footer">
            <p><strong>RentWheels</strong> - Your Trusted Vehicle Rental Partner</p>
            <p>© {{ date('Y') }} RentWheels. All rights reserved.</p>
        </div>
    </div>
</body>
</html>