<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>RentWheels Reports</title>
    <style>
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            margin: 0; 
            padding: 20px; 
            font-size: 12px;
            line-height: 1.4;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            border-bottom: 3px solid #dc3545; 
            padding-bottom: 20px; 
        }
        .header h1 { 
            color: #dc3545; 
            margin: 0; 
            font-size: 28px;
        }
        .header p { 
            color: #666; 
            margin: 8px 0; 
            font-size: 14px;
        }
        .stats-container {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            flex-wrap: wrap;
        }
        .stat-box { 
            text-align: center; 
            padding: 15px; 
            border: 2px solid #dc3545; 
            border-radius: 8px; 
            width: 22%;
            margin-bottom: 15px;
        }
        .stat-value { 
            font-size: 20px; 
            font-weight: bold; 
            color: #dc3545; 
            margin-bottom: 5px;
        }
        .stat-label { 
            font-size: 11px; 
            color: #666; 
            font-weight: bold;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0; 
            font-size: 11px;
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
            vertical-align: top;
        }
        th { 
            background-color: #dc3545; 
            color: white;
            font-weight: bold; 
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .section-title { 
            font-size: 16px; 
            font-weight: bold; 
            color: #dc3545; 
            margin: 30px 0 15px 0; 
            border-bottom: 2px solid #dc3545; 
            padding-bottom: 8px; 
        }
        .footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .page-break {
            page-break-before: always;
        }
        .no-data {
            text-align: center;
            color: #999;
            font-style: italic;
            padding: 20px;
        }
        .currency {
            color: #28a745;
            font-weight: bold;
        }
        .status {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .status-active { background-color: #007bff; color: white; }
        .status-completed { background-color: #28a745; color: white; }
        .status-confirmed { background-color: #17a2b8; color: white; }
        .status-cancelled { background-color: #dc3545; color: white; }
        .status-pending { background-color: #ffc107; color: #212529; }
        .status-available { background-color: #28a745; color: white; }
        .status-rented { background-color: #ffc107; color: #212529; }
        .status-maintenance { background-color: #6c757d; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚗 RentWheels</h1>
        <h2>Reports & Analytics</h2>
        <p><strong>Period:</strong> {{ $dateFrom->format('F d, Y') }} - {{ $dateTo->format('F d, Y') }}</p>
        <p><strong>Report Type:</strong> {{ ucfirst($reportType) }}</p>
        <p><strong>Generated:</strong> {{ now()->format('F d, Y \a\t H:i:s') }}</p>
    </div>

    <!-- Statistics Overview -->
    <div class="section-title">📊 Key Performance Indicators</div>
    <div class="stats-container">
        <div class="stat-box">
            <div class="stat-value">RM{{ number_format($data['stats']['totalRevenue'], 2) }}</div>
            <div class="stat-label">Total Revenue</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $data['stats']['totalVehicles'] }}</div>
            <div class="stat-label">Total Vehicles</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $data['stats']['totalBookings'] }}</div>
            <div class="stat-label">Total Bookings</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $data['stats']['utilizationRate'] }}%</div>
            <div class="stat-label">Fleet Utilization</div>
        </div>
    </div>

    @if($reportType === 'overview' || $reportType === 'users')
        @if(isset($data['topUsers']) && $data['topUsers']->count() > 0)
        <div class="section-title">👥 Top Users by Bookings</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 30%;">User</th>
                    <th style="width: 35%;">Email</th>
                    <th style="width: 15%;">Total Bookings</th>
                    <th style="width: 20%;">Total Spent</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['topUsers'] as $user)
                <tr>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td style="text-align: center;"><strong>{{ $user->bookings_count }}</strong></td>
                    <td class="currency">RM{{ number_format($user->total_spent ?? 0, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="section-title">👥 Top Users by Bookings</div>
        <div class="no-data">
            <p>No user data available for the selected date range.</p>
        </div>
        @endif
    @endif

    @if($reportType === 'overview' || $reportType === 'vehicles')
        @if(isset($data['vehiclePerformance']) && $data['vehiclePerformance']->count() > 0)
        @if($reportType === 'overview' && isset($data['topUsers']))
        <div class="page-break"></div>
        @endif
        <div class="section-title">🚙 Vehicle Performance Analytics</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 25%;">Vehicle</th>
                    <th style="width: 15%;">Type</th>
                    <th style="width: 15%;">Total Bookings</th>
                    <th style="width: 20%;">Revenue Generated</th>
                    <th style="width: 25%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['vehiclePerformance'] as $vehicle)
                <tr>
                    <td><strong>{{ $vehicle->make }} {{ $vehicle->model }}</strong></td>
                    <td>{{ ucfirst($vehicle->type) }}</td>
                    <td style="text-align: center;"><strong>{{ $vehicle->bookings_count }}</strong></td>
                    <td class="currency">RM{{ number_format($vehicle->revenue_generated ?? 0, 2) }}</td>
                    <td>
                        <span class="status status-{{ $vehicle->status }}">
                            {{ ucfirst($vehicle->status) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="section-title">🚙 Vehicle Performance Analytics</div>
        <div class="no-data">
            <p>No vehicle data available for the selected date range.</p>
        </div>
        @endif
    @endif

    @if($reportType === 'overview' || $reportType === 'bookings')
        @if(isset($data['recentBookings']) && $data['recentBookings']->count() > 0)
        <div class="page-break"></div>
        <div class="section-title">📅 Recent Booking Analytics</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Booking ID</th>
                    <th style="width: 20%;">User</th>
                    <th style="width: 25%;">Vehicle</th>
                    <th style="width: 15%;">Duration</th>
                    <th style="width: 15%;">Amount</th>
                    <th style="width: 13%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['recentBookings'] as $booking)
                <tr>
                    <td><strong>BK{{ str_pad($booking->id, 3, '0', STR_PAD_LEFT) }}</strong></td>
                    <td>{{ $booking->user ? $booking->user->name : 'Unknown' }}</td>
                    <td>{{ $booking->vehicle ? $booking->vehicle->make . ' ' . $booking->vehicle->model : 'N/A' }}</td>
                    <td>
                        @php
                            if($booking->pickup_datetime && $booking->return_datetime) {
                                $start = \Carbon\Carbon::parse($booking->pickup_datetime);
                                $end = \Carbon\Carbon::parse($booking->return_datetime);
                                $days = $start->diffInDays($end);
                                echo $days . ' day' . ($days != 1 ? 's' : '');
                            } else {
                                echo 'N/A';
                            }
                        @endphp
                    </td>
                    <td class="currency">RM{{ number_format($booking->total_amount ?? 0, 2) }}</td>
                    <td>
                        <span class="status status-{{ $booking->status }}">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="section-title">📅 Recent Booking Analytics</div>
        <div class="no-data">
            <p>No booking data available for the selected date range.</p>
        </div>
        @endif
    @endif

    @if((!isset($data['topUsers']) || $data['topUsers']->count() == 0) && 
        (!isset($data['vehiclePerformance']) || $data['vehiclePerformance']->count() == 0) && 
        (!isset($data['recentBookings']) || $data['recentBookings']->count() == 0))
    <div class="no-data">
        <p>No data available for the selected date range and report type.</p>
    </div>
    @endif

    <div class="footer">
        <p>© {{ date('Y') }} RentWheels - Car Rental Management System | Generated on {{ now()->format('F d, Y \a\t H:i:s') }}</p>
    </div>
</body>
</html>