<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle }} - {{ $user->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .user-info table {
            width: 100%;
        }
        
        .user-info td {
            padding: 5px 0;
        }
        
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        
        .stats-item {
            display: table-cell;
            text-align: center;
            background: #e3f2fd;
            padding: 15px;
            border: 1px solid #bbdefb;
            width: 16.66%;
        }
        
        .stats-number {
            font-size: 18px;
            font-weight: bold;
            color: #1976d2;
            display: block;
        }
        
        .stats-label {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            background: #007bff;
            color: white;
            padding: 10px 15px;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .booking-item {
            border: 1px solid #ddd;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        
        .booking-header {
            background: #f8f9fa;
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .booking-header .booking-id {
            font-weight: bold;
            font-size: 14px;
            color: #007bff;
        }
        
        .booking-header .booking-status {
            float: right;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            color: white;
        }
        
        .status-confirmed { background-color: #28a745; }
        .status-pending { background-color: #ffc107; color: #000; }
        .status-active { background-color: #17a2b8; }
        .status-completed { background-color: #6f42c1; }
        .status-cancelled { background-color: #dc3545; }
        
        .booking-details {
            padding: 15px;
        }
        
        .detail-grid {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .detail-row {
            display: table-row;
        }
        
        .detail-label {
            display: table-cell;
            font-weight: bold;
            width: 25%;
            padding: 5px 10px 5px 0;
            color: #666;
        }
        
        .detail-value {
            display: table-cell;
            padding: 5px 0;
        }
        
        .vehicle-info {
            background: #f1f8ff;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        
        .price-info {
            background: #f0fff0;
            padding: 10px;
            border-radius: 5px;
            text-align: right;
            margin-top: 10px;
        }
        
        .price-total {
            font-size: 16px;
            font-weight: bold;
            color: #28a745;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #007bff;
            text-align: center;
            color: #666;
            font-size: 11px;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        
        @media print {
            .booking-item {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>🚗 {{ $reportTitle }}</h1>
        <p>Generated on {{ $generatedAt->format('F j, Y \a\t g:i A') }}</p>
    </div>

    <!-- User Information -->
    <div class="user-info">
        <table>
            <tr>
                <td><strong>Customer Name:</strong></td>
                <td>{{ $user->name }}</td>
                <td><strong>Email:</strong></td>
                <td>{{ $user->email }}</td>
            </tr>
            <tr>
                <td><strong>Phone:</strong></td>
                <td>{{ $user->phone ?? 'Not provided' }}</td>
                <td><strong>Member Since:</strong></td>
                <td>{{ $user->created_at->format('F j, Y') }}</td>
            </tr>
        </table>
    </div>

    <!-- Statistics Overview -->
    <div class="stats-grid clearfix">
        <div class="stats-item">
            <span class="stats-number">{{ $stats['totalBookings'] }}</span>
            <div class="stats-label">Total Bookings</div>
        </div>
        <div class="stats-item">
            <span class="stats-number">{{ $stats['activeBookings'] }}</span>
            <div class="stats-label">Active Bookings</div>
        </div>
        <div class="stats-item">
            <span class="stats-number">{{ $stats['completedBookings'] }}</span>
            <div class="stats-label">Completed</div>
        </div>
        <div class="stats-item">
            <span class="stats-number">{{ $stats['cancelledBookings'] }}</span>
            <div class="stats-label">Cancelled</div>
        </div>
        <div class="stats-item">
            <span class="stats-number">RM{{ number_format($stats['totalAmountPaid'], 2) }}</span>
            <div class="stats-label">Total Paid</div>
        </div>
        <div class="stats-item">
            <span class="stats-number">RM{{ number_format($stats['pendingPayments'], 2) }}</span>
            <div class="stats-label">Pending Payments</div>
        </div>
    </div>

    @if($bookings->count() > 0)
        @foreach($groupedBookings as $statusGroup => $groupBookings)
            @if($groupBookings->count() > 0)
            <div class="section">
                <div class="section-title">{{ $statusGroup }} Bookings ({{ $groupBookings->count() }})</div>
                
                @foreach($groupBookings as $booking)
                <div class="booking-item">
                    <div class="booking-header clearfix">
                        <span class="booking-id">{{ $booking->booking_number }}</span>
                        <span class="booking-status status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
                    </div>
                    
                    <div class="booking-details">
                        <!-- Vehicle Information -->
                        <div class="vehicle-info">
                            <strong>🚗 {{ $booking->vehicle->make ?? 'Unknown' }} {{ $booking->vehicle->model ?? 'Unknown' }}</strong>
                            <br>
                            <small>
                                {{ $booking->vehicle->year ?? 'N/A' }} • {{ $booking->vehicle->color ?? 'N/A' }} • 
                                {{ $booking->vehicle->type ?? 'N/A' }} • 
                                License: {{ $booking->vehicle->license_plate ?? 'N/A' }}
                            </small>
                        </div>

                        <!-- Booking Details -->
                        <div class="detail-grid">
                            <div class="detail-row">
                                <div class="detail-label">Pickup Date:</div>
                                <div class="detail-value">{{ $booking->pickup_datetime ? $booking->pickup_datetime->format('M j, Y \a\t g:i A') : 'N/A' }}</div>
                                <div class="detail-label">Return Date:</div>
                                <div class="detail-value">{{ $booking->return_datetime ? $booking->return_datetime->format('M j, Y \a\t g:i A') : 'N/A' }}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Pickup Location:</div>
                                <div class="detail-value">{{ $booking->pickup_location ?? 'N/A' }}</div>
                                <div class="detail-label">Return Location:</div>
                                <div class="detail-value">{{ $booking->return_location ?? 'N/A' }}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Duration:</div>
                                <div class="detail-value">{{ $booking->rental_days ?? 0 }} {{ \Illuminate\Support\Str::plural('day', $booking->rental_days ?? 0) }}</div>
                                <div class="detail-label">Payment Status:</div>
                                <div class="detail-value">
                                    <span style="color: {{ $booking->payment_status === 'paid' ? '#28a745' : '#ffc107' }};">
                                        {{ ucfirst($booking->payment_status ?? 'pending') }}
                                    </span>
                                </div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Booked On:</div>
                                <div class="detail-value">{{ $booking->created_at ? $booking->created_at->format('M j, Y \a\t g:i A') : 'N/A' }}</div>
                                <div class="detail-label">Daily Rate:</div>
                                <div class="detail-value">RM{{ number_format($booking->vehicle->rentalRate->daily_rate ?? 0, 2) }}</div>
                            </div>
                        </div>

                        @if($booking->special_requests)
                        <div style="margin: 10px 0; padding: 8px; background: #fff3cd; border-radius: 3px;">
                            <strong>Special Requests:</strong><br>
                            {{ $booking->special_requests }}
                        </div>
                        @endif

                        <!-- Price Information -->
                        <div class="price-info">
                            @if($booking->deposit_amount && $booking->deposit_amount > 0)
                            <div>Deposit: RM{{ number_format($booking->deposit_amount, 2) }}</div>
                            @endif
                            @if($booking->damage_charges && $booking->damage_charges > 0)
                            <div style="color: #dc3545;">Damage Charges: RM{{ number_format($booking->damage_charges, 2) }}</div>
                            @endif
                            @if($booking->late_fees && $booking->late_fees > 0)
                            <div style="color: #dc3545;">Late Fees: RM{{ number_format($booking->late_fees, 2) }}</div>
                            @endif
                            <div class="price-total">Total: RM{{ number_format($booking->total_amount ?? 0, 2) }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        @endforeach
    @else
        <div class="section">
            <div style="text-align: center; padding: 40px; color: #666;">
                <h3>No Bookings Found</h3>
                <p>You haven't made any bookings yet. Start exploring our vehicles today!</p>
            </div>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p><strong>RentWheels Vehicle Rental System</strong></p>
        <p>Thank you for choosing RentWheels for your transportation needs!</p>
        <p>For support, contact us at: support@rentwheels.com | +60 12-345 6789</p>
        <p style="margin-top: 10px;">This report was generated automatically on {{ $generatedAt->format('F j, Y \a\t g:i A') }}</p>
    </div>
</body>
</html>