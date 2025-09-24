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
        
        .header p {
            margin: 5px 0;
            color: #666;
        }
        
        .info-section {
            margin-bottom: 30px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
        }
        
        .info-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
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
        
        .info-group p {
            margin: 0;
            font-size: 14px;
        }
        
        .summary-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
        }
        
        .summary-item {
            display: table-cell;
            text-align: center;
            padding: 10px;
        }
        
        .summary-item h4 {
            color: #666;
            font-size: 11px;
            text-transform: uppercase;
            margin: 0 0 5px 0;
        }
        
        .summary-item .amount {
            font-size: 18px;
            font-weight: bold;
            color: #333;
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
        
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #dee2e6;
            text-align: center;
            font-size: 11px;
            color: #666;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
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
        <p>Period: {{ Carbon\Carbon::parse($startDate)->format('d M Y') }} to {{ Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
    </div>
    
    <!-- Customer Information -->
    <div class="info-section">
        <div class="info-grid">
            <div class="info-col">
                <div class="info-group">
                    <h3>Customer Information</h3>
                    <p><strong>{{ $user->name }}</strong></p>
                    <p>{{ $user->email }}</p>
                    <p>{{ $user->phone ?? 'N/A' }}</p>
                    <p>Customer ID: #{{ str_pad($user->id, 6, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>
            <div class="info-col">
                <div class="info-group">
                    <h3>Statement Details</h3>
                    <p>Generated: {{ $generatedAt->format('d M Y, h:i A') }}</p>
                    <p>Statement Period: {{ Carbon\Carbon::parse($startDate)->diffInDays(Carbon\Carbon::parse($endDate)) }} days</p>
                    <p>Total Transactions: {{ $bookings->count() + $payments->count() }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Summary -->
    <div class="summary-box">
        <div class="summary-grid">
            <div class="summary-item">
                <h4>Total Rental Charges</h4>
                <div class="amount">RM {{ number_format($totals['rental_charges'], 2) }}</div>
            </div>
            <div class="summary-item">
                <h4>Additional Charges</h4>
                <div class="amount">RM {{ number_format($totals['additional_charges'], 2) }}</div>
            </div>
            <div class="summary-item">
                <h4>Total Paid</h4>
                <div class="amount">RM {{ number_format($totals['total_paid'], 2) }}</div>
            </div>
            <div class="summary-item">
                <h4>Pending Amount</h4>
                <div class="amount">RM {{ number_format($totals['pending'], 2) }}</div>
            </div>
        </div>
    </div>
    
    <!-- Bookings Table -->
    <h3>Booking History</h3>
    <table>
        <thead>
            <tr>
                <th>Booking #</th>
                <th>Date</th>
                <th>Vehicle</th>
                <th>Rental Period</th>
                <th>Base Amount</th>
                <th>Additional</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $booking)
            <tr>
                <td>{{ $booking->booking_number }}</td>
                <td>{{ $booking->created_at->format('d/m/Y') }}</td>
                <td>
                    {{ $booking->vehicle->make }} {{ $booking->vehicle->model }}
                    <br><small>{{ $booking->vehicle->license_plate }}</small>
                </td>
                <td>
                    {{ $booking->pickup_datetime->format('d/m') }} - 
                    {{ $booking->return_datetime->format('d/m') }}
                </td>
                <td>RM {{ number_format($booking->total_amount, 2) }}</td>
                <td>
                    @if($booking->damage_charges > 0 || $booking->late_fees > 0)
                        RM {{ number_format($booking->damage_charges + $booking->late_fees, 2) }}
                    @else
                        -
                    @endif
                </td>
                <td class="font-bold">
                    RM {{ number_format($booking->total_amount + $booking->damage_charges + $booking->late_fees, 2) }}
                </td>
                <td>
                    <span class="status-badge status-{{ str_replace('_', '-', $booking->payment_status) }}">
                        {{ ucfirst($booking->payment_status) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right font-bold">Total:</td>
                <td colspan="2" class="font-bold">
                    RM {{ number_format($bookings->sum(function($b) { 
                        return $b->total_amount + $b->damage_charges + $b->late_fees; 
                    }), 2) }}
                </td>
            </tr>
        </tfoot>
    </table>
    
    <!-- Payments Table -->
    @if($payments->count() > 0)
    <h3>Payment History</h3>
    <table>
        <thead>
            <tr>
                <th>Payment Ref</th>
                <th>Date</th>
                <th>Booking</th>
                <th>Method</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
            <tr>
                <td>{{ $payment->payment_reference }}</td>
                <td>{{ $payment->created_at->format('d/m/Y h:i A') }}</td>
                <td>{{ $payment->booking->booking_number }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A')) }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}</td>
                <td class="font-bold">RM {{ number_format($payment->amount, 2) }}</td>
                <td>
                    <span class="status-badge status-{{ $payment->status == 'completed' ? 'paid' : $payment->status }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right font-bold">Total Paid:</td>
                <td colspan="2" class="font-bold">
                    RM {{ number_format($payments->where('status', 'completed')->sum('amount'), 2) }}
                </td>
            </tr>
        </tfoot>
    </table>
    @endif
    
    <!-- Outstanding Balance Summary -->
    @php
        $totalCharges = $bookings->sum(function($b) { 
            return $b->total_amount + $b->damage_charges + $b->late_fees; 
        });
        $totalPaid = $payments->where('status', 'completed')->sum('amount');
        $outstanding = $totalCharges - $totalPaid;
    @endphp
    
    @if($outstanding > 0)
    <div class="summary-box" style="background: #fff3cd; border: 1px solid #ffc107;">
        <h3 style="margin: 0 0 10px 0;">Outstanding Balance</h3>
        <p style="font-size: 24px; font-weight: bold; margin: 0;">
            RM {{ number_format($outstanding, 2) }}
        </p>
        <p style="margin: 10px 0 0 0; font-size: 12px;">
            Please make payment to avoid late charges. Contact billing@rentwheels.com for assistance.
        </p>
    </div>
    @endif
    
    <!-- Footer -->
    <div class="footer">
        <p><strong>RentWheels Vehicle Rental System</strong></p>
        <p>Address: 123 Jalan Rental, 50000 Kuala Lumpur, Malaysia</p>
        <p>Email: billing@rentwheels.com | Phone: +60 3-1234 5680</p>
        <p>This is a computer-generated statement and does not require a signature.</p>
        <p>Generated on {{ $generatedAt->format('d F Y, h:i A') }}</p>
    </div>
</body>
</html>