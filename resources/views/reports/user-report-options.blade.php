@extends('app')

@section('title', 'Custom Booking Report - RentWheels')

@section('content')
<div class="container py-5">
    <!-- Back Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('booking.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to My Bookings
            </a>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient bg-primary text-white">
                <div class="card-body">
                    <h2 class="mb-0">
                        <i class="fas fa-file-export me-2"></i>
                        Custom Booking Report
                    </h2>
                    <p class="mb-0 mt-2 opacity-75">
                        Generate a personalized PDF report with your preferred filters and date ranges
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Report Options Form -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter text-primary me-2"></i>
                        Report Filters
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('user.reports.detailed-report') }}" id="reportForm">
                        <!-- Date Range Selection -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">📅 Date Range</h6>
                            </div>
                            <div class="col-md-6">
                                <label for="date_from" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" 
                                       value="{{ request('date_from') }}">
                                <small class="text-muted">Leave empty to include all dates from the beginning</small>
                            </div>
                            <div class="col-md-6">
                                <label for="date_to" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" 
                                       value="{{ request('date_to') }}">
                                <small class="text-muted">Leave empty to include all dates up to today</small>
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">📋 Booking Status</h6>
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Filter by Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                        </div>

                        <!-- Vehicle Type Filter -->
                        @if($vehicleTypes && $vehicleTypes->count() > 0)
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">🚗 Vehicle Type</h6>
                            </div>
                            <div class="col-md-6">
                                <label for="vehicle_type" class="form-label">Filter by Vehicle Type</label>
                                <select class="form-select" id="vehicle_type" name="vehicle_type">
                                    <option value="">All Vehicle Types</option>
                                    @foreach($vehicleTypes as $type)
                                        <option value="{{ $type }}" {{ request('vehicle_type') == $type ? 'selected' : '' }}>
                                            {{ ucfirst($type) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif

                        <!-- Payment Status Filter -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">💳 Payment Status</h6>
                            </div>
                            <div class="col-md-6">
                                <label for="payment_status" class="form-label">Filter by Payment Status</label>
                                <select class="form-select" id="payment_status" name="payment_status">
                                    <option value="">All Payment Statuses</option>
                                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                                    <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                                </select>
                            </div>
                        </div>

                        <!-- Report Options -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">⚙️ Report Options</h6>
                            </div>
                            <div class="col-12">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="include_cancelled" name="include_cancelled" value="1"
                                           {{ request('include_cancelled') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="include_cancelled">
                                        Include cancelled bookings
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="detailed_vehicle_info" name="detailed_vehicle_info" value="1" checked>
                                    <label class="form-check-label" for="detailed_vehicle_info">
                                        Include detailed vehicle information
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="include_special_requests" name="include_special_requests" value="1" checked>
                                    <label class="form-check-label" for="include_special_requests">
                                        Include special requests and notes
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                                <i class="fas fa-eraser me-2"></i>Clear Filters
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-file-pdf me-2"></i>Generate PDF Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar with Quick Actions -->
        <div class="col-lg-4">
            <!-- Quick Export Options -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Export Options
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('user.reports.booking-report') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-download me-2"></i>Complete Report (All Bookings)
                        </a>
                        <a href="{{ route('user.reports.detailed-report', ['status' => 'completed']) }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-check-circle me-2"></i>Completed Bookings Only
                        </a>
                        <a href="{{ route('user.reports.detailed-report', ['status' => 'active']) }}" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-car me-2"></i>Active Bookings Only
                        </a>
                        <a href="{{ route('user.reports.detailed-report', ['date_from' => date('Y-01-01'), 'date_to' => date('Y-12-31')]) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-calendar-alt me-2"></i>This Year's Bookings
                        </a>
                    </div>
                </div>
            </div>

            <!-- Report Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle text-primary me-2"></i>Report Information
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            All your booking details and vehicle information
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Rental dates, durations, and locations
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Payment information and status
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Summary statistics and totals
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check text-success me-2"></i>
                            Professional PDF format ready for printing
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Support Information -->
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h6>Need Help?</h6>
                    <p class="text-muted small mb-3">Contact our support team if you need assistance with your reports</p>
                    <div class="d-grid gap-2">
                        <a href="tel:+60123456789" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-phone me-2"></i>Call Support
                        </a>
                        <a href="mailto:support@rentwheels.com" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-envelope me-2"></i>Email Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function clearFilters() {
    document.getElementById('reportForm').reset();
    // Also clear URL parameters
    window.location.href = "{{ route('user.reports.options') }}";
}

// Set max date to today for date inputs
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('date_from').setAttribute('max', today);
    document.getElementById('date_to').setAttribute('max', today);
    
    // Ensure 'to' date is not earlier than 'from' date
    document.getElementById('date_from').addEventListener('change', function() {
        const fromDate = this.value;
        const toDateInput = document.getElementById('date_to');
        if (fromDate) {
            toDateInput.setAttribute('min', fromDate);
        } else {
            toDateInput.removeAttribute('min');
        }
    });

    // Show loading when generating report
    document.getElementById('reportForm').addEventListener('submit', function() {
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating Report...';
        submitBtn.disabled = true;
    });
});
</script>
@endsection

