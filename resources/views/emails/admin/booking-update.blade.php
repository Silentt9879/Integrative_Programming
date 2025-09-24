<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Update Requires Attention</title>
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
        .booking-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        .urgent {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
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
            <h1>📋 Booking Update Alert</h1>
            <p>{{ $booking_number }}</p>
        </div>

        <div class="content">
            <h2>Booking Status Changed</h2>
            
            @if($requires_action)
            <div class="urgent">
                <h4>🚨 Urgent Action Required</h4>
                <p>This booking status change requires immediate administrative attention.</p>
            </div>
            @endif

            <div class="booking-info">
                <h3>Booking Details</h3>
                <div class="detail-row">
                    <strong>Customer:</strong>
                    <span>{{ $customer_name }}</span>
                </div>
                <div class="detail-row">
                    <strong>Vehicle:</strong>
                    <span>{{ $vehicle_name }}</span>
                </div>
                <div class="detail-row">
                    <strong>Status Change:</strong>
                    <span>{{ ucfirst($old_status) }} → {{ ucfirst($new_status) }}</span>
                </div>
                <div class="detail-row">
                    <strong>Change Date:</strong>
                    <span>{{ $change_date }}</span>
                </div>
                <div class="detail-row">
                    <strong>Total Amount:</strong>
                    <span>RM{{ number_format($booking_details['total_amount'], 2) }}</span>
                </div>
            </div>

            <div style="text-align: center; margin: 2rem 0;">
                <a href="{{ $admin_panel_url }}" class="cta-button">
                    View Booking in Admin Panel
                </a>
            </div>

            <p><strong>Status-Specific Actions:</strong></p>
            @if($new_status == 'cancelled')
            <ul>
                <li>Process any applicable refunds</li>
                <li>Update vehicle availability</li>
                <li>Review cancellation reason</li>
            </ul>
            @elseif($new_status == 'active')
            <ul>
                <li>Confirm vehicle has been picked up</li>
                <li>Monitor rental period</li>
                <li>Prepare for return process</li>
            </ul>
            @elseif($new_status == 'confirmed')
            <ul>
                <li>Ensure vehicle is prepared</li>
                <li>Confirm pickup arrangements</li>
                <li>Verify customer documentation</li>
            </ul>
            @endif
        </div>

        <div class="footer">
            <p><strong>RentWheels Admin System</strong></p>
            <p>© {{ date('Y') }} RentWheels. All rights reserved.</p>
        </div>
    </div>
</body>
</html>