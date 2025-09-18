<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle }} - RentWheels Admin</title>
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
            background: linear-gradient(135deg, #dc3545, #6f42c1);
            color: white;
            padding: 25px;
            text-align: center;
            margin-bottom: 25px;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .report-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .report-info table {
            width: 100%;
        }
        
        .report-info td {
            padding: 5px 0;
        }
        
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .stats-row {
            display: table-row;
        }
        
        .stats-item {
            display: table-cell;
            text-align: center;
            background: #e3f2fd;
            padding: 20px 15px;
            border: 1px solid #bbdefb;
            width: 50%;
        }
        
        .stats-number {
            font-size: 20px;
            font-weight: bold;
            color: #1976d2;
            display: block;
            margin-bottom: 5px;
        }
        
        .stats-label {
            font-size: 12px;
            color: #666;
        }
        
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .section-title {
            background: #dc3545;
            color: white;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .table th {
            background: linear-gradient(135deg, #343a40, #495057);
            color: white;
            padding: 12px 10px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        
        .table td {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
            font-size: 11px;
        }
        
        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            color: white;
        }
        
        .badge-primary { background-color: #007bff; }
        .badge-success { background-color: #28a745; }
        .badge-warning { background-color: #ffc107; color: #000; }
        .badge-info { background-color: #17a2b8; }
        .badge-danger { background-color: #dc3545; }
        .badge-secondary { background-color: #6c757d; }
        
        .revenue-section {
            background: #f0fff0;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .revenue-total {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
            text-align: center;
            margin-bottom: 10px;
        }
        
        .revenue-subtitle {
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        
        .chart-placeholder {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            padding: 40px;
            text-align: center;
            color: #666;
            margin: 15px 0;
        }
        
        .summary-box {
            background: #fff3cd;
            border-left: 5px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 25px;
            border-top: 2px solid #dc3545;
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
            .section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>📊 {{ $reportTitle }}</h1>
        <p>Comprehensive Business Analytics & Performance Report</p>
        <p>Generated on {{ $generatedAt->format('F j, Y \a\t g:i A') }}</p>
    </div>

    <!-- Report Information -->
    <div class="report-info">
        <table>
            <tr>
                <td><strong>Report Period:</strong></td>
                <td>{{ $dateFrom->format('F j, Y') }} to {{ $dateTo->format('F j, Y') }}</td>
                <td><strong>Report Type:</strong></td>
                <td>{{ ucfirst($reportType) }}</td>
            </tr>
            <tr>
                <td><strong>Generated By:</strong></td>
                <td>RentWheels Admin System</td>
                <td><strong>Total Days:</strong></td>
                <td>{{ $dateFrom->diffInDays($dateTo) + 1 }} days</td>
            </tr>
        </table>
    </div>

    <!-- Key Statistics -->
    <div class="stats-grid clearfix">
        <div class="stats-row">
            <div class="stats-item">
                <span class="stats-number">RM{{ number_format($stats['totalRevenue'], 2) }}</span>
                <div class="stats-label">Total Revenue</div>
            </div>
            <div class="stats-item">
                <span class="stats-number">{{ $stats['totalVehicles'] }}</span>
                <div class="stats-label">Total Vehicles in Fleet</div>
            </div>
        </div>
    </div>

    <!-- Revenue Overview -->
    <div class="revenue-section">
        <div class="revenue-total">RM{{ number_format($stats['totalRevenue'], 2) }}</div>
        <div class="revenue-subtitle">Total Revenue for Report Period</div>
    </div>

    @if($reportType === 'overview' || $reportType === 'users')
    <!-- Top Users Section -->
    <div class="section">
        <div class="section-title">🏆 Top Performing Users</div>
        
        @if(count($tableData['topUsers']) > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Total Bookings</th>
                        <th>Total Spent</th>
                        <th>Last Activity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tableData['topUsers'] as $user)
                    <tr>
                        <td><strong>{{ $user['name'] }}</strong></td>
                        <td>{{ $user['email'] }}</td>
                        <td><span class="badge badge-primary">{{ $user['total_bookings'] ?? 0 }}</span></td>
                        <td><strong style="color: #28a745;">RM{{ number_format($user['total_spent'] ?? 0, 2) }}</strong></td>
                        <td>{{ \Carbon\Carbon::parse($user['updated_at'])->format('M j, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="summary-box">
                <strong>No user data available for the selected period.</strong>
            </div>
        @endif
    </div>
    @endif

    @if($reportType === 'overview' || $reportType === 'vehicles')
    <!-- Vehicle Performance Section -->
    <div class="section">
        <div class="section-title">🚗 Vehicle Performance Analytics</div>
        
        @if(count($tableData['vehiclePerformance']) > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Vehicle</th>
                        <th>Type</th>
                        <th>Bookings</th>
                        <th>Revenue</th>
                        <th>Utilization</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tableData['vehiclePerformance'] as $vehicle)
                    <tr>
                        <td><strong>{{ $vehicle['make'] }} {{ $vehicle['model'] }}</strong></td>
                        <td><span class="badge badge-secondary">{{ $vehicle['type'] }}</span></td>
                        <td>{{ $vehicle['total_bookings'] ?? 0 }}</td>
                        <td><strong style="color: #28a745;">RM{{ number_format($vehicle['revenue_generated'] ?? 0, 2) }}</strong></td>
                        <td>{{ number_format($vehicle['utilization_rate'] ?? 0, 1) }}%</td>
                        <td>
                            <span class="badge badge-{{ $vehicle['status'] === 'available' ? 'success' : ($vehicle['status'] === 'rented' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($vehicle['status']) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="summary-box">
                <strong>No vehicle performance data available for the selected period.</strong>
            </div>
        @endif
    </div>
    @endif

    @if($reportType === 'overview' || $reportType === 'bookings')
    <!-- Recent Bookings Section -->
    <div class="section">
        <div class="section-title">📅 Recent Booking Analytics</div>
        
        @if(count($tableData['recentBookings']) > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Duration</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tableData['recentBookings'] as $booking)
                    <tr>
                        <td><code>BK{{ str_pad($booking['id'], 3, '0', STR_PAD_LEFT) }}</code></td>
                        <td>{{ $booking['user']['name'] ?? 'Unknown' }}</td>
                        <td>{{ ($booking['vehicle']['make'] ?? '') }} {{ ($booking['vehicle']['model'] ?? 'N/A') }}</td>
                        <td>
                            @php
                                $days = 'N/A';
                                if(isset($booking['start_date']) && isset($booking['end_date'])) {
                                    $start = \Carbon\Carbon::parse($booking['start_date']);
                                    $end = \Carbon\Carbon::parse($booking['end_date']);
                                    $days = $start->diffInDays($end) . ' ' . \Illuminate\Support\Str::plural('day', $start->diffInDays($end));
                                }
                            @endphp
                            {{ $days }}
                        </td>
                        <td><strong style="color: #28a745;">RM{{ number_format($booking['total_amount'] ?? 0, 2) }}</strong></td>
                        <td>
                            <span class="badge badge-{{ 
                                $booking['status'] === 'completed' ? 'success' : 
                                ($booking['status'] === 'active' ? 'primary' : 
                                ($booking['status'] === 'confirmed' ? 'info' : 
                                ($booking['status'] === 'cancelled' ? 'danger' : 'warning'))) 
                            }}">
                                {{ ucfirst($booking['status']) }}
                            </span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($booking['created_at'])->format('M j, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="summary-box">
                <strong>No recent bookings data available for the selected period.</strong>
            </div>
        @endif
    </div>
    @endif



    <!-- Business Insights -->
    <div class="section">
        <div class="section-title">💡 Business Insights & Recommendations</div>
        
        <div class="summary-box">
            <strong>Key Performance Indicators:</strong><br>
            • Total Revenue: RM{{ number_format($stats['totalRevenue'], 2) }} over {{ $dateFrom->diffInDays($dateTo) + 1 }} days<br>
            • Fleet Size: {{ $stats['totalVehicles'] }} vehicles in total<br>
            • Average Daily Revenue: RM{{ number_format($stats['totalRevenue'] / max($dateFrom->diffInDays($dateTo) + 1, 1), 2) }}<br>
            • Top Performing Users: {{ $tableData['topUsers']->count() }} customers tracked<br>
            • Active Vehicles: {{ $tableData['vehiclePerformance']->filter(function($v) { return $v['status'] === 'available' || $v['status'] === 'rented'; })->count() }} vehicles
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>RentWheels Admin Dashboard - Business Analytics Report</strong></p>
        <p>This comprehensive report provides insights into business performance, customer analytics, and operational efficiency.</p>
        <p>For detailed analysis or support, contact the admin team at: admin@rentwheels.com | +60 12-345 6789</p>
        <p style="margin-top: 15px;"><em>This report was automatically generated on {{ $generatedAt->format('F j, Y \a\t g:i A') }}</em></p>
        <p style="margin-top: 5px; font-size: 10px;">Report ID: RPT-{{ $generatedAt->format('Ymd-His') }} | Data Range: {{ $dateFrom->format('Y-m-d') }} to {{ $dateTo->format('Y-m-d') }}</p>
    </div>
</body>
</html>