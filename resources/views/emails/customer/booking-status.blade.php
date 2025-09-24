<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Update - {{ $booking_number }}</title>
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
            font-size: 1.8rem;
        }
        .content {
            padding: 2rem;
        }
        .status-update {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1rem 0;
            border-left: 4px solid #dc3545;
        }
        .status-confirmed {
            background: #d4edda;
            border-left-color: #28a745;
        }
        .status-active {
            background: #cce5ff;
            border-left-color: #007bff;
        }
        .status-completed {
            background: #e2e6ea;
            border-left-color: #6c757d;
        }
        .status-cancelled {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        .booking-details {
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
        }
        .next-actions {
            background: #e8f4fd;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
        .next-actions h3 {
            color: #0c5460;
            margin-top: 0;
        }
        .next-actions ul {
            margin: 0;
            padding-left: 1.5rem;
        }
        .next-actions li {
            margin-bottom: 0.5rem;
        }
        .support-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
            border: 1px solid #dee2e6;
        }
        .footer {
            background: #343a40;
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        .important-note {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 1rem;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Booking Update</h1>
            <p>{{ $booking_number }}</p>
        </div>

        <!-- Content -->
        <div class="content">
            <h2>Hello {{ $customer_name }}!</h2>
            
            <div class="status-update status-{{ $new_status }}">
                <h3>Status Update</h3>
                <p>Your booking status has been updated from <strong>{{ ucfirst($old_status) }}</strong> to <strong>{{ ucfirst($new_status) }}</strong> on {{ $change_date }}.</p>
                
                @if($new_status == 'confirmed')
                    <p><strong>Great news!</strong> Your booking has been confirmed. Please prepare for your upcoming rental.</p>
                @elseif($new_status == 'active')
                    <p><strong>Your rental is now active!</strong> Enjoy your vehicle and have a safe journey.</p>
                @elseif($new_status == 'completed')
                    <p><strong>Thank you!</strong> Your rental has been completed. We hope you had a great experience with RentWheels.</p>
                @elseif($new_status == 'cancelled')
                    <p><strong>Booking Cancelled:</strong> Your booking has been cancelled. Any applicable refunds will be processed according to our policy.</p>
                @endif
            </div>

            <!-- Booking Details -->
            <div class="booking-details">
                <h3>Booking Details</h3>
                <div class="detail-row">
                    <span class="detail-label">Vehicle:</span>
                    <span class="detail-value">{{ $vehicle_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">License Plate:</span>
                    <span class="detail-value">{{ $vehicle_plate }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Pickup Date:</span>
                    <span class="detail-value">{{ $booking_details['pickup_date'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Return Date:</span>
                    <span class="detail-value">{{ $booking_details['return_date'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Pickup Location:</span>
                    <span class="detail-value">{{ $booking_details['pickup_location'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Return Location:</span>
                    <span class="detail-value">{{ $booking_details['return_location'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Amount:</span>
                    <span class="detail-value"><strong>RM{{ number_format($booking_details['total_amount'], 2) }}</strong></span>
                </div>
            </div>

            @if(!empty($next_actions))
            <div class="next-actions">
                <h3>What's Next?</h3>
                <ul>
                    @foreach($next_actions as $action)
                    <li>{{ $action }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if($new_status == 'confirmed')
            <div class="important-note">
                <h4>Important Reminders:</h4>
                <ul>
                    <li>Bring a valid driver's license and credit card</li>
                    <li>Arrive 15 minutes before your scheduled pickup time</li>
                    <li>Contact us immediately if your plans change</li>
                </ul>
            </div>
            @endif

            @if($new_status == 'active')
            <div class="important-note">
                <h4>During Your Rental:</h4>
                <ul>
                    <li>Keep our contact information handy for emergencies</li>
                    <li>Report any issues immediately</li>
                    <li>Return the vehicle on time and with the same fuel level</li>
                </ul>
            </div>
            @endif

            <!-- Support Information -->
            <div class="support-info">
                <h3>Need Assistance?</h3>
                <p>Our customer support team is here to help:</p>
                <ul>
                    <li><strong>Phone:</strong> {{ $support_phone }}</li>
                    <li><strong>Email:</strong> <a href="mailto:{{ $support_email }}">{{ $support_email }}</a></li>
                    <li><strong>Emergency Hotline:</strong> Available 24/7</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>RentWheels</strong> - Your Trusted Vehicle Rental Partner</p>
            <p>© {{ date('Y') }} RentWheels. All rights reserved.</p>
            <p style="font-size: 0.9em; opacity: 0.8;">
                This email was sent regarding your booking {{ $booking_number }}.
            </p>
        </div>
    </div>
</body>
</html>