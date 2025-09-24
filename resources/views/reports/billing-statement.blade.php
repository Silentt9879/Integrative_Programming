<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Billing Statement - {{ $user->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #007bff;
        }
        
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 28px;
        }
        
        .info-section {
            margin-bottom: 30px;
        }
        
        .info-group {
            margin-bottom: 15px;
        }
        
        .info-group h3 {
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            margin: 0 0 5px 0;
        }
        
        .summary-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        th {
            background: #007bff;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 12px;
        }
        
        td {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
            font-size: 12px;
        }
        
        tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #dee2e6;
            text-align: center;
            font-size: 11px;
            color: #666;
        }
        
        .text-right {
            text-align: right;
        }
        
        .font-bold {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ $reportTitle }}</h1>
        <p>RentWheels Vehicle Rental System</p>
        <p>Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
    </div>
    
    <!-- Customer Information -->
    <div class="info-section">
        <div class="info-group">
            <h3>Customer Information</h3>
            <p><strong>{{ $user->name }}</strong></p>
            <p>{{ $user->email }}</p>
            <p>{{ $user->phone ?? 'N/A' }}</p>
            <p>Customer ID: #{{ str_pad($user->id, 6, '0', STR_PAD_LEFT) }}</p>
        </div>
    </div>
    
    <!-- Summary -->
    <div class="summary-box">
        <h3>Summary</h3>
        <p>Total Rental Charges: <strong>RM {{ number_format($totals['rental_charges'], 2) }}</strong></p>
        <p>Additional Charges: <strong>RM {{ number_format($totals['additional_charges'], 2) }}</strong></p>
        <p>Total Paid: <strong>RM {{ number_format($totals['total_paid'], 2) }}</strong></p>
        <p>Pending Amount: <strong>RM {{ number_format($totals['pending'], 2) }}</strong></p>
    </div>
    
    <!-- Bookings Table -->
    <h3>Booking History</h3>
    <table>
        <thead>
            <tr>
                <th>Booking #</th>
                <th>Date</th>
                <th>Vehicle</th>
                <th>Period</th>
                <th>Amount</th>
                <th>Additional</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $booking)
            <tr>
                <td>{{ $booking->booking_number }}</td>
                <td>{{ $booking->created_at->format('d/m/Y') }}</td>
                <td>{{ $booking->vehicle->make }} {{ $booking->vehicle->model }}</td>
                <td>{{ $booking->pickup_datetime->format('d/m') }} - {{ $booking->return_datetime->format('d/m') }}</td>
                <td>RM {{ number_format($booking->total_amount, 2) }}</td>
                <td>
                    @if($booking->damage_charges > 0 || $booking->late_fees > 0)
                        RM {{ number_format($booking->damage_charges + $booking->late_fees, 2) }}
                    @else
                        -
                    @endif
                </td>
                <td>{{ ucfirst($booking->payment_status) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right font-bold">Total:</td>
                <td colspan="3" class="font-bold">
                    RM {{ number_format($bookings->sum(function($b) { 
                        return $b->total_amount + $b->damage_charges + $b->late_fees; 
                    }), 2) }}
                </td>
            </tr>
        </tfoot>
    </table>
    
    <!-- Footer -->
    <div class="footer">
        <p><strong>RentWheels Vehicle Rental System</strong></p>
        <p>This is a computer-generated statement and does not require a signature.</p>
        <p>Generated on {{ $generatedAt->format('d F Y, h:i A') }}</p>
    </div>
</body>
</html>