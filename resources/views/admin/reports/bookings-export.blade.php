<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Bookings Export Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 15px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 5px;
        }

        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .export-date {
            color: #666;
            font-size: 11px;
        }

        .stats-section {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }

        .stats-row {
            display: table;
            width: 100%;
        }

        .stat-item {
            display: table-cell;
            text-align: center;
            width: 25%;
            padding: 15px 10px;
            border: 1px solid #ddd;
            background-color: #f8f9fa;
            vertical-align: middle;
        }

        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 10px;
            color: #666;
        }

        .filters-section {
            margin-bottom: 20px;
            padding: 12px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
        }

        .filters-title {
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .filter-item {
            margin-bottom: 3px;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: #343a40;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            border: 1px solid #dee2e6;
        }

        td {
            padding: 8px;
            border: 1px solid #dee2e6;
            font-size: 10px;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .status-pending {
            color: #fd7e14;
            font-weight: bold;
            padding: 3px 8px;
            background-color: rgba(253, 126, 20, 0.1);
            border-radius: 3px;
        }

        .status-confirmed {
            color: #28a745;
            font-weight: bold;
            padding: 3px 8px;
            background-color: rgba(40, 167, 69, 0.1);
            border-radius: 3px;
        }

        .status-active {
            color: #007bff;
            font-weight: bold;
            padding: 3px 8px;
            background-color: rgba(0, 123, 255, 0.1);
            border-radius: 3px;
        }

        .status-cancelled {
            color: #dc3545;
            font-weight: bold;
            padding: 3px 8px;
            background-color: rgba(220, 53, 69, 0.1);
            border-radius: 3px;
        }

        .status-completed {
            color: #6f42c1;
            font-weight: bold;
            padding: 3px 8px;
            background-color: rgba(111, 66, 193, 0.1);
            border-radius: 3px;
        }

        .booking-id {
            font-weight: bold;
            color: #333;
        }

        .amount {
            font-weight: bold;
            color: #28a745;
            font-size: 11px;
        }

        .customer-name {
            font-weight: bold;
            color: #333;
        }

        .customer-email {
            color: #666;
            font-size: 9px;
        }

        .vehicle-name {
            font-weight: bold;
            color: #333;
        }

        .vehicle-plate {
            color: #666;
            font-size: 9px;
        }

        .period-main {
            font-weight: bold;
            color: #333;
        }

        .period-sub {
            color: #666;
            font-size: 9px;
        }

        .footer {
            position: fixed;
            bottom: 15px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }

        .no-data {
            text-align: center;
            padding: 30px;
            color: #666;
            font-style: italic;
        }

        @page {
            margin: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="logo">RentWheels</div>
        <div class="report-title">Bookings Management Report</div>
        <div class="export-date">Generated on {{ $exportDate }}</div>
    </div>

    <!-- Statistics Summary -->
    <div class="stats-section">
        <div class="stats-row">
            <div class="stat-item">
                <div class="stat-value">{{ $totalBookings }}</div>
                <div class="stat-label">Total Bookings</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $pendingCount }}</div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $confirmedCount }}</div>
                <div class="stat-label">Confirmed</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">RM{{ number_format($totalRevenue, 2) }}</div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>
    </div>

    <!-- Applied Filters -->
    @if (!empty($filters['search']) || !empty($filters['status']) || !empty($filters['date_range']))
        <div class="filters-section">
            <div class="filters-title">📊 Applied Filters:</div>
            @if (!empty($filters['search']))
                <div class="filter-item">🔍 Search: "{{ $filters['search'] }}"</div>
            @endif
            @if (!empty($filters['status']))
                <div class="filter-item">📋 Status: {{ ucfirst($filters['status']) }}</div>
            @endif
            @if (!empty($filters['date_range']))
                <div class="filter-item">📅 Date Range: {{ ucfirst(str_replace('_', ' ', $filters['date_range'])) }}
                </div>
            @endif
        </div>
    @endif

    <!-- Bookings Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 12%;">Booking ID</th>
                <th style="width: 20%;">Customer</th>
                <th style="width: 20%;">Vehicle</th>
                <th style="width: 18%;">Period</th>
                <th style="width: 12%;">Amount</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 8%;">Created</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $booking)
                <tr>
                    <td class="booking-id">#BK{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <div class="customer-name">{{ $booking->user->name ?? 'N/A' }}</div>
                        <div class="customer-email">{{ $booking->user->email ?? 'N/A' }}</div>
                    </td>
                    <td>
                        <div class="vehicle-name">{{ $booking->vehicle->make ?? 'N/A' }}
                            {{ $booking->vehicle->model ?? '' }}</div>
                        <div class="vehicle-plate">{{ $booking->vehicle->license_plate ?? 'N/A' }}</div>
                    </td>
                    <td>
                        <div class="period-main">
                            {{ \Carbon\Carbon::parse($booking->pickup_datetime)->format('M d, Y') }}</div>
                        <div class="period-sub">to
                            {{ \Carbon\Carbon::parse($booking->return_datetime)->format('M d, Y') }}</div>
                    </td>
                    <td class="amount">RM{{ number_format($booking->total_amount, 2) }}</td>
                    <td>
                        <span class="status-{{ strtolower($booking->status) }}">

                            {{ ucfirst($booking->status) }}
                        </span>
                    </td>
                    <td>{{ $booking->created_at->format('M d, Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="no-data">
                        No bookings found matching the selected criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        RentWheels Vehicle Rental System - Bookings Report
    </div>
</body>

</html>

