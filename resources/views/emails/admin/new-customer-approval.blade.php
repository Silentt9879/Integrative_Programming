<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Customer Requires Approval</title>
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
        .content {
            padding: 2rem;
        }
        .customer-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        .approval-needed {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 1rem;
            margin: 1rem 0;
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
            <h1>🔔 New Customer Alert</h1>
            <p>Approval Required</p>
        </div>

        <div class="content">
            <h2>New Customer Registration</h2>
            
            <div class="customer-info">
                <h3>Customer Details</h3>
                <div class="detail-row">
                    <strong>Name:</strong>
                    <span>{{ $customer_name }}</span>
                </div>
                <div class="detail-row">
                    <strong>Email:</strong>
                    <span>{{ $customer_email }}</span>
                </div>
                <div class="detail-row">
                    <strong>Registration Date:</strong>
                    <span>{{ $registration_date }}</span>
                </div>
                <div class="detail-row">
                    <strong>Registration IP:</strong>
                    <span>{{ $registration_ip }}</span>
                </div>
                <div class="detail-row">
                    <strong>Profile Complete:</strong>
                    <span>{{ $profile_complete ? 'Yes' : 'No' }}</span>
                </div>
            </div>

            @if(!$profile_complete)
            <div class="approval-needed">
                <h4>⚠️ Action Required</h4>
                <p>This customer has registered with incomplete profile information and requires admin approval before they can make bookings.</p>
                <p><strong>Missing Information:</strong></p>
                <ul>
                    <li>Phone number</li>
                    <li>Date of birth</li>
                    <li>Address</li>
                </ul>
            </div>
            @endif

            <div style="text-align: center; margin: 2rem 0;">
                <a href="{{ $admin_panel_url }}" class="cta-button">
                    Review Customer in Admin Panel
                </a>
            </div>

            <p><strong>Recommended Actions:</strong></p>
            <ol>
                <li>Review customer information in the admin panel</li>
                <li>Contact customer if additional verification is needed</li>
                <li>Approve or suspend account based on your review</li>
            </ol>
        </div>

        <div class="footer">
            <p><strong>RentWheels Admin System</strong></p>
            <p>© {{ date('Y') }} RentWheels. All rights reserved.</p>
        </div>
    </div>
</body>
</html>