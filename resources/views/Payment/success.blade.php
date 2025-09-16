@extends('app')

@section('title', 'Payment Successful - RentWheels')

@section('content')
<div class="container py-5">
    <!-- Success Header -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <div class="success-icon mb-3">
                <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
            </div>
            <h1 class="display-5 fw-bold text-success">Payment Successful!</h1>
            <p class="lead text-muted">Your booking has been confirmed and payment processed successfully.</p>
        </div>
    </div>

    <div class="row">
        <!-- Payment Receipt -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-receipt me-2"></i>Payment Receipt
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Transaction Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Transaction ID</h6>
                            <p class="fw-bold">TXN{{ time() }}{{ rand(1000, 9999) }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Payment Date</h6>
                            <p class="fw-bold">{{ now()->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>

                    <!-- Booking Information -->
                    <div class="border-bottom pb-3 mb-3">
                        <h5 class="mb-3">
                            <i class="fas fa-calendar-check text-primary me-2"></i>
                            Booking Information
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">Booking Reference</h6>
                                <p class="fw-bold">{{ $booking->booking_number }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Status</h6>
                                <span class="badge bg-success">Confirmed & Paid</span>
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle Details -->
                    <div class="border-bottom pb-3 mb-3">
                        <h5 class="mb-3">
                            <i class="fas fa-car text-primary me-2"></i>
                            Vehicle Details
                        </h5>
                        <div class="row">
                            <div class="col-md-4">
                                @if($booking->vehicle->image_url)
                                <img src="{{ $booking->vehicle->image_url }}" 
                                     alt="{{ $booking->vehicle->make }} {{ $booking->vehicle->model }}" 
                                     class="img-fluid rounded">
                                @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 120px;">
                                    <i class="fas fa-car text-muted" style="font-size: 2.5rem;"></i>
                                </div>
                                @endif
                            </div>
                            <div class="col-md-8">
                                <h6 class="fw-bold">{{ $booking->vehicle->make }} {{ $booking->vehicle->model }}</h6>
                                <div class="row g-2 mt-2">
                                    <div class="col-6">
                                        <small class="text-muted">Year:</small><br>
                                        <span class="fw-semibold">{{ $booking->vehicle->year }}</span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Color:</small><br>
                                        <span class="fw-semibold">{{ $booking->vehicle->color }}</span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">License Plate:</small><br>
                                        <span class="fw-semibold">{{ $booking->vehicle->license_plate }}</span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Type:</small><br>
                                        <span class="badge bg-primary">{{ $booking->vehicle->type }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rental Details -->
                    <div class="border-bottom pb-3 mb-3">
                        <h5 class="mb-3">
                            <i class="fas fa-calendar-alt text-primary me-2"></i>
                            Rental Period
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <h6 class="text-success">Pickup</h6>
                                        <p class="mb-1"><strong>{{ $booking->pickup_datetime->format('M d, Y') }}</strong></p>
                                        <p class="mb-1">{{ $booking->pickup_datetime->format('h:i A') }}</p>
                                        <small class="text-muted">{{ $booking->pickup_location }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-danger">
                                    <div class="card-body text-center">
                                        <h6 class="text-danger">Return</h6>
                                        <p class="mb-1"><strong>{{ $booking->return_datetime->format('M d, Y') }}</strong></p>
                                        <p class="mb-1">{{ $booking->return_datetime->format('h:i A') }}</p>
                                        <small class="text-muted">{{ $booking->return_location }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <div class="bg-light p-2 rounded">
                                <strong>Total Duration: {{ $booking->rental_days }} {{ Str::plural('day', $booking->rental_days) }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Breakdown -->
                    <div class="mb-3">
                        <h5 class="mb-3">
                            <i class="fas fa-credit-card text-primary me-2"></i>
                            Payment Details
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <td>Daily Rate</td>
                                        <td class="text-end">RM{{ number_format($booking->vehicle->rentalRate->daily_rate, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Number of Days</td>
                                        <td class="text-end">{{ $booking->rental_days }}</td>
                                    </tr>
                                    <tr class="border-top">
                                        <td><strong>Total Paid</strong></td>
                                        <td class="text-end"><strong class="text-success">RM{{ number_format($booking->total_amount, 2) }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="bg-light p-3 rounded">
                        <h6 class="mb-2">Customer Information</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted">Name:</small><br>
                                <strong>{{ $booking->customer_name }}</strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Email:</small><br>
                                <strong>{{ $booking->customer_email }}</strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Phone:</small><br>
                                <strong>{{ $booking->customer_phone }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions and Next Steps -->
        <div class="col-lg-4">
            <!-- Next Steps -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list-ol me-2"></i>Next Steps
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item completed">
                            <div class="timeline-marker bg-success">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Payment Completed</h6>
                                <small class="text-muted">{{ now()->format('M d, Y h:i A') }}</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary">
                                <i class="fas fa-car"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Vehicle Pickup</h6>
                                <small class="text-muted">{{ $booking->pickup_datetime->format('M d, Y h:i A') }}</small>
                                <p class="mb-0 small">Bring your ID and driver's license</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning">
                                <i class="fas fa-undo"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Vehicle Return</h6>
                                <small class="text-muted">{{ $booking->return_datetime->format('M d, Y h:i A') }}</small>
                                <p class="mb-0 small">Return on time to avoid late fees</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('booking.show', $booking->id) }}" class="btn btn-primary">
                            <i class="fas fa-eye me-2"></i>View Booking Details
                        </a>
                        <a href="{{ route('booking.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-list me-2"></i>My Bookings
                        </a>
                        <button class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Print Receipt
                        </button>
                        <a href="{{ route('app') }}" class="btn btn-outline-success">
                            <i class="fas fa-home me-2"></i>Back to Home
                        </a>
                    </div>
                </div>
            </div>

            <!-- Support Information -->
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h6>Need Help?</h6>
                    <p class="text-muted small mb-3">Contact our support team if you have any questions</p>
                    <div class="row g-2 text-center">
                        <div class="col-12">
                            <small class="text-muted d-block">Phone</small>
                            <strong>+60 12-345 6789</strong>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Email</small>
                            <strong>support@rentwheels.com</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Important Notice -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-info">
                <h5><i class="fas fa-info-circle me-2"></i>Important Reminders</h5>
                <ul class="mb-0">
                    <li>Please arrive on time for vehicle pickup with valid ID and driver's license</li>
                    <li>Vehicle inspection will be conducted before and after rental period</li>
                    <li>Any damages will be charged according to our damage assessment policy</li>
                    <li>Late returns may incur additional charges</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.success-icon {
    animation: bounceIn 1s ease-out;
}

@keyframes bounceIn {
    0% { transform: scale(0); opacity: 0; }
    50% { transform: scale(1.1); opacity: 1; }
    100% { transform: scale(1); }
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 10px;
    bottom: 10px;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 25px;
}

.timeline-marker {
    position: absolute;
    left: -37px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    color: white;
    border: 3px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.timeline-content h6 {
    margin-bottom: 4px;
    font-size: 0.9rem;
}

.timeline-content small {
    color: #6c757d;
}

@media print {
    .btn, .alert, .card-header {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
@endsection
