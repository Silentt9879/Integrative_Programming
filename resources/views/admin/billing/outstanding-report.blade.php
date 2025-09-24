@extends('admin')

@section('title', 'Outstanding Charges Report - Admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0"><i class="fas fa-chart-line me-2"></i>Outstanding Charges Report</h1>
            <p class="text-muted">Comprehensive report of all outstanding customer charges and payments</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group">
                <button onclick="window.print()" class="btn btn-outline-primary">
                    <i class="fas fa-print me-2"></i>Print Report
                </button>
                <a href="{{ route('admin.billing.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Billing
                </a>
            </div>
        </div>
    </div>

    <!-- Report Filters -->
    <div class="card mb-4 d-print-none">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.billing.outstanding-report') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $date_from }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $date_to }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Export Format</label>
                        <select name="format" class="form-select">
                            <option value="view">View Report</option>
                            <option value="pdf">Download PDF</option>
                            <option value="excel">Download Excel</option>
                            <option value="csv">Download CSV</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-filter me-2"></i>Generate Report
                        </button>
                        <a href="{{ route('admin.billing.outstanding-report') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-refresh me-2"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Summary -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <h2 class="text-danger">${{ number_format($totals['damage_charges'], 2) }}</h2>
                    <p class="text-muted mb-0">Total Damage Charges</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <h2 class="text-warning">${{ number_format($totals['late_fees'], 2) }}</h2>
                    <p class="text-muted mb-0">Total Late Fees</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-info">
                <div class="card-body">
                    <h2 class="text-info">${{ number_format($totals['total_outstanding'], 2) }}</h2>
                    <p class="text-muted mb-0">Total Outstanding</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-primary">
                <div class="card-body">
                    <h2 class="text-primary">{{ $totals['count'] }}</h2>
                    <p class="text-muted mb-0">Outstanding Bills</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Details -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Outstanding Charges Details</h5>
            <small class="text-muted">
                Generated: {{ $generated_at->format('M d, Y h:i A') }} by {{ $generated_by }}
            </small>
        </div>
        <div class="card-body p-0">
            @if($bookings->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Booking #</th>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Rental Period</th>
                            <th>Base Amount</th>
                            <th>Damage</th>
                            <th>Late Fees</th>
                            <th>Total Outstanding</th>
                            <th>Payment Status</th>
                            <th>Days Overdue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $booking)
                        @php
                            $additionalCharges = ($booking->damage_charges ?? 0) + ($booking->late_fees ?? 0);
                            $totalOutstanding = $additionalCharges;
                            
                            // Add remaining balance if payment is partial or pending
                            if ($booking->payment_status === 'partial' || $booking->payment_status === 'pending') {
                                $remaining = $booking->total_amount - ($booking->deposit_amount ?? 0);
                                if ($remaining > 0) {
                                    $totalOutstanding += $remaining;
                                }
                            }
                            
                            $daysOverdue = max(0, now()->diffInDays($booking->return_datetime));
                            $isOverdue = $booking->return_datetime < now() && $booking->status !== 'completed';
                        @endphp
                        <tr class="{{ $isOverdue ? 'table-warning' : '' }}">
                            <td>
                                <strong>{{ $booking->booking_number }}</strong><br>
                                <small class="text-muted">{{ $booking->created_at->format('M d, Y') }}</small>
                            </td>
                            <td>
                                <strong>{{ $booking->user->name ?? $booking->customer_name }}</strong><br>
                                <small class="text-muted">{{ $booking->user->email ?? $booking->customer_email }}</small><br>
                                <small class="text-muted">{{ $booking->customer_phone }}</small>
                            </td>
                            <td>
                                <strong>{{ $booking->vehicle->make }} {{ $booking->vehicle->model }}</strong><br>
                                <small class="text-muted">{{ $booking->vehicle->year }} {{ $booking->vehicle->type }}</small><br>
                                <small class="text-muted">{{ $booking->vehicle->license_plate }}</small>
                            </td>
                            <td>
                                <strong>From:</strong> {{ $booking->pickup_datetime->format('M d, Y') }}<br>
                                <strong>To:</strong> {{ $booking->return_datetime->format('M d, Y') }}<br>
                                <small class="text-muted">
                                    {{ $booking->pickup_datetime->diffInDays($booking->return_datetime) ?: 1 }} day(s)
                                </small>
                            </td>
                            <td>
                                <strong>${{ number_format($booking->total_amount, 2) }}</strong>
                                @if($booking->deposit_amount > 0)
                                <br><small class="text-success">Deposit: ${{ number_format($booking->deposit_amount, 2) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($booking->damage_charges > 0)
                                <strong class="text-danger">${{ number_format($booking->damage_charges, 2) }}</strong>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($booking->late_fees > 0)
                                <strong class="text-warning">${{ number_format($booking->late_fees, 2) }}</strong>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-danger">${{ number_format($totalOutstanding, 2) }}</strong>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'partial' => 'info',
                                        'paid' => 'success',
                                        'refunded' => 'secondary'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$booking->payment_status] ?? 'secondary' }}">
                                    {{ ucfirst($booking->payment_status) }}
                                </span><br>
                                <small class="text-muted">{{ ucfirst($booking->status) }}</small>
                            </td>
                            <td>
                                @if($isOverdue)
                                <span class="text-danger">{{ $daysOverdue }} day(s)</span>
                                @else
                                <span class="text-success">On time</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <th colspan="5" class="text-end">TOTALS:</th>
                            <th>${{ number_format($totals['damage_charges'], 2) }}</th>
                            <th>${{ number_format($totals['late_fees'], 2) }}</th>
                            <th>${{ number_format($totals['total_outstanding'], 2) }}</th>
                            <th>{{ $totals['count'] }} bills</th>
                            <th>-</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                <h4>No Outstanding Charges</h4>
                <p class="text-muted">All customer payments are up to date for the selected period!</p>
                @if($date_from || $date_to)
                <p class="text-muted">
                    <strong>Period:</strong> 
                    {{ $date_from ? \Carbon\Carbon::parse($date_from)->format('M d, Y') : 'Beginning' }} - 
                    {{ $date_to ? \Carbon\Carbon::parse($date_to)->format('M d, Y') : 'Today' }}
                </p>
                @endif
            </div>
            @endif
        </div>
    </div>

    @if($bookings->count() > 0)
    <!-- Report Analysis -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Charge Breakdown</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-danger">${{ number_format($totals['damage_charges'], 2) }}</h4>
                                <p class="text-muted">Damage Charges</p>
                                <small>{{ number_format(($totals['damage_charges'] / $totals['total_outstanding']) * 100, 1) }}% of total</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-warning">${{ number_format($totals['late_fees'], 2) }}</h4>
                                <p class="text-muted">Late Fees</p>
                                <small>{{ number_format(($totals['late_fees'] / $totals['total_outstanding']) * 100, 1) }}% of total</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Collection Metrics</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-info">${{ number_format($totals['total_outstanding'] / $totals['count'], 2) }}</h4>
                                <p class="text-muted">Avg per Bill</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                @php
                                    $overdueCount = $bookings->filter(function($booking) {
                                        return $booking->return_datetime < now() && $booking->status !== 'completed';
                                    })->count();
                                @endphp
                                <h4 class="text-danger">{{ $overdueCount }}</h4>
                                <p class="text-muted">Overdue Bills</p>
                                <small>{{ number_format(($overdueCount / $totals['count']) * 100, 1) }}% of total</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommendations -->
    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            <h6 class="card-title mb-0"><i class="fas fa-lightbulb me-2"></i>Recommendations</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>High Priority Actions:</h6>
                    <ul>
                        @if($totals['total_outstanding'] > 5000)
                        <li>Consider implementing automated payment reminders</li>
                        @endif
                        @if($bookings->where('damage_charges', '>', 500)->count() > 0)
                        <li>Review vehicle inspection procedures</li>
                        @endif
                        @php
                            $overdueCount = $bookings->filter(function($booking) {
                                return $booking->return_datetime < now()->subDays(30);
                            })->count();
                        @endphp
                        @if($overdueCount > 0)
                        <li>Follow up on bills overdue by 30+ days ({{ $overdueCount }} bills)</li>
                        @endif
                        <li>Contact customers with outstanding balances over $200</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Process Improvements:</h6>
                    <ul>
                        <li>Implement deposit increase for repeat damage claims</li>
                        <li>Consider payment plan options for high amounts</li>
                        <li>Review late fee structure effectiveness</li>
                        <li>Automate charge notifications to customers</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Report Footer -->
    <div class="text-center mt-4 mb-4 text-muted">
        <hr>
        <p>
            <strong>RentWheels Billing Management System</strong><br>
            This report contains confidential information. Handle according to company data policies.
        </p>
    </div>
</div>

@endsection

@section('styles')
<style>
@media print {
    .d-print-none {
        display: none !important;
    }
    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
    body {
        font-size: 12px !important;
    }
    .table {
        font-size: 11px !important;
    }
    .btn {
        display: none !important;
    }
}
</style>
@endsection