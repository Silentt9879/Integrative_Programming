@extends('app')

@section('title', 'Booking ' . $booking->booking_number . ' - RentWheels')

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

    <!-- Booking Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient bg-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-0">
                                <i class="fas fa-calendar-check me-2"></i>
                                Booking {{ $booking->booking_number }}
                            </h2>
                            <p class="mb-0 mt-2 opacity-75">
                                {{ $booking->vehicle->make }} {{ $booking->vehicle->model }} • 
                                {{ $booking->pickup_datetime->format('M d, Y') }} - {{ $booking->return_datetime->format('M d, Y') }}
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <span class="badge bg-{{ $booking->status_badge_color }} fs-6 me-2">
                                {{ ucfirst($booking->status) }}
                            </span>
                            <span class="badge bg-{{ $booking->payment_badge_color }} fs-6">
                                {{ ucfirst($booking->payment_status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Required Alert -->
    @if($booking->payment_status === 'pending')
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">
                    <i class="fas fa-exclamation-triangle me-2"></i>Payment Required
                </h5>
                <p class="mb-3">Your booking is pending payment confirmation. Please complete payment to secure your vehicle reservation.</p>
                <div class="d-grid d-md-flex gap-2">
                    <a href="{{ route('payment.form', $booking->id) }}" class="btn btn-warning">
                        <i class="fas fa-credit-card me-2"></i>Complete Payment Now
                    </a>
                    <small class="text-muted align-self-center">
                        Booking will be held for 24 hours without payment
                    </small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Main Booking Details -->
        <div class="col-lg-8 mb-4">
            <!-- Vehicle Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-car text-primary me-2"></i>
                        Vehicle Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            @if($booking->vehicle->image_url)
                            <img src="{{ $booking->vehicle->image_url }}" 
                                 alt="{{ $booking->vehicle->make }} {{ $booking->vehicle->model }}" 
                                 class="img-fluid rounded">
                            @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                <i class="fas fa-car text-muted" style="font-size: 3rem;"></i>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <h4 class="fw-bold">{{ $booking->vehicle->make }} {{ $booking->vehicle->model }}</h4>
                            <div class="row g-3 mt-2">
                                <div class="col-6">
                                    <small class="text-muted d-block">Year</small>
                                    <span class="fw-semibold">{{ $booking->vehicle->year }}</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Color</small>
                                    <span class="fw-semibold">{{ $booking->vehicle->color }}</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Seating</small>
                                    <span class="fw-semibold">{{ $booking->vehicle->seating_capacity }} seats</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Fuel Type</small>
                                    <span class="fw-semibold">{{ $booking->vehicle->fuel_type }}</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">License Plate</small>
                                    <span class="fw-semibold">{{ $booking->vehicle->license_plate }}</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Vehicle Type</small>
                                    <span class="badge bg-primary">{{ $booking->vehicle->type }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rental Details -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                        Rental Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100" style="border-left: 4px solid #28a745 !important;">
                                <h6 class="text-success mb-2">
                                    <i class="fas fa-play-circle me-1"></i>Pickup
                                </h6>
                                <p class="mb-1">
                                    <i class="fas fa-calendar me-2"></i>
                                    <strong>{{ $booking->pickup_datetime->format('l, F j, Y') }}</strong>
                                </p>
                                <p class="mb-1">
                                    <i class="fas fa-clock me-2"></i>
                                    {{ $booking->pickup_datetime->format('h:i A') }}
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    {{ $booking->pickup_location }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 mt-3 mt-md-0">
                            <div class="border rounded p-3 h-100" style="border-left: 4px solid #dc3545 !important;">
                                <h6 class="text-danger mb-2">
                                    <i class="fas fa-stop-circle me-1"></i>Return
                                </h6>
                                <p class="mb-1">
                                    <i class="fas fa-calendar me-2"></i>
                                    <strong>{{ $booking->return_datetime->format('l, F j, Y') }}</strong>
                                </p>
                                <p class="mb-1">
                                    <i class="fas fa-clock me-2"></i>
                                    {{ $booking->return_datetime->format('h:i A') }}
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    {{ $booking->return_location }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="bg-light p-3 rounded text-center">
                                <strong>Total Rental Duration: {{ $booking->rental_days }} {{ Str::plural('day', $booking->rental_days) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user text-primary me-2"></i>
                        Customer Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Full Name</small>
                            <span class="fw-semibold">{{ $booking->customer_name }}</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Email Address</small>
                            <span class="fw-semibold">{{ $booking->customer_email }}</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Phone Number</small>
                            <span class="fw-semibold">{{ $booking->customer_phone }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($booking->special_requests)
            <!-- Special Requests -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-list text-primary me-2"></i>
                        Special Requests
                    </h5>
                </div>
                <div class="card-body">
                    <div class="bg-light p-3 rounded">
                        {{ $booking->special_requests }}
                    </div>
                </div>
            </div>
            @endif

            <!-- Booking Timeline -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history text-primary me-2"></i>
                        Booking Timeline
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6>Booking Created</h6>
                                <p class="text-muted mb-0">{{ $booking->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                        @if($booking->payment_status === 'paid')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6>Payment Completed</h6>
                                <p class="text-muted mb-0">{{ $booking->updated_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                        @endif
                        @if($booking->status !== 'pending')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6>Booking {{ ucfirst($booking->status) }}</h6>
                                <p class="text-muted mb-0">{{ $booking->updated_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Payment Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header {{ $booking->payment_status === 'paid' ? 'bg-success' : 'bg-warning' }} text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        Payment Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Daily Rate:</span>
                        <span>RM{{ number_format($booking->vehicle->rentalRate->daily_rate, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Duration:</span>
                        <span>{{ $booking->rental_days }} {{ Str::plural('day', $booking->rental_days) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 border-top pt-2">
                        <strong>Total Amount:</strong>
                        <strong class="text-success">RM{{ number_format($booking->total_amount, 2) }}</strong>
                    </div>
                    @if($booking->deposit_amount > 0)
                    <div class="d-flex justify-content-between mb-2 bg-light p-2 rounded">
                        <span>Deposit Required:</span>
                        <span class="fw-bold text-warning">RM{{ number_format($booking->deposit_amount, 2) }}</span>
                    </div>
                    @endif
                    @if($booking->final_amount && $booking->final_amount != $booking->total_amount)
                    <div class="d-flex justify-content-between mb-2">
                        <span>Final Amount Paid:</span>
                        <span class="fw-bold text-success">RM{{ number_format($booking->final_amount, 2) }}</span>
                    </div>
                    @endif
                    @if($booking->damage_charges > 0)
                    <div class="d-flex justify-content-between mb-2 text-danger">
                        <span>Damage Charges:</span>
                        <span>RM{{ number_format($booking->damage_charges, 2) }}</span>
                    </div>
                    @endif
                    @if($booking->late_fees > 0)
                    <div class="d-flex justify-content-between mb-2 text-danger">
                        <span>Late Fees:</span>
                        <span>RM{{ number_format($booking->late_fees, 2) }}</span>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between border-top pt-2">
                        <span>Payment Status:</span>
                        <span class="badge bg-{{ $booking->payment_badge_color }}">
                            {{ ucfirst($booking->payment_status) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <!-- Payment Button for Pending Payments -->
                        @if($booking->payment_status === 'pending')
                        <a href="{{ route('payment.form', $booking->id) }}" class="btn btn-warning btn-lg">
                            <i class="fas fa-credit-card me-2"></i>Complete Payment
                        </a>
                        @endif

                        <!-- Cancel Button -->
                        @if(in_array($booking->status, ['pending', 'confirmed']) && $booking->payment_status !== 'paid')
                        <form method="POST" action="{{ route('booking.cancel', $booking->id) }}" 
                              onsubmit="return confirm('Are you sure you want to cancel this booking?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-times me-2"></i>Cancel Booking
                            </button>
                        </form>
                        @endif
                        
                        <!-- Legacy Confirm Button (for bookings without payment) -->
                        @if($booking->status === 'pending' && $booking->payment_status === 'paid')
                        <form method="POST" action="{{ route('booking.confirm', $booking->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check me-2"></i>Confirm Booking
                            </button>
                        </form>
                        @endif

                        <!-- Support Buttons -->
                        <a href="tel:+60123456789" class="btn btn-outline-success">
                            <i class="fas fa-phone me-2"></i>Call Support
                        </a>
                        <a href="mailto:support@rentwheels.com?subject=Booking {{ $booking->booking_number }}" 
                           class="btn btn-outline-primary">
                            <i class="fas fa-envelope me-2"></i>Email Support
                        </a>
                    </div>
                </div>
            </div>

            <!-- Payment Success Info -->
            @if($booking->payment_status === 'paid')
            <div class="card shadow-sm mb-4 border-success">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle text-success mb-2" style="font-size: 2rem;"></i>
                    <h6 class="text-success">Payment Completed!</h6>
                    <p class="text-muted small mb-0">Your booking is confirmed and ready for pickup</p>
                </div>
            </div>
            @endif

            <!-- Contact Support -->
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h6>Need Help?</h6>
                    <p class="text-muted small mb-3">Our support team is available 24/7</p>
                    <div class="row g-2 text-center">
                        <div class="col-6">
                            <small class="text-muted d-block">Phone</small>
                            <strong>+60 12-345 6789</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Email</small>
                            <strong>support@rentwheels.com</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS -->
<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-marker {
        position: absolute;
        left: -37px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #dee2e6;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: -31px;
        top: 12px;
        bottom: 12px;
        width: 2px;
        background: #dee2e6;
    }

    .card {
        transition: transform 0.2s ease;
    }

    .card:hover {
        transform: translateY(-2px);
    }

    .bg-gradient {
        background: linear-gradient(135deg, #007bff, #0056b3);
    }
</style>
@endsection