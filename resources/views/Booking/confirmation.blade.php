@extends('app')

@section('title', 'Booking Confirmation - RentWheels')

@section('content')
<div class="container py-5">
    <!-- Success Header -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <div class="success-icon mb-3">
                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
            </div>
            <h1 class="display-5 fw-bold text-success">Booking Created Successfully!</h1>
            <p class="lead text-muted">Your vehicle reservation has been submitted and is pending confirmation.</p>
        </div>
    </div>

    <div class="row">
        <!-- Booking Details -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-calendar-check me-2"></i>
                        Booking Details
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Booking Reference -->
                    <div class="alert alert-info">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <strong>Booking Reference:</strong>
                                <h4 class="mb-0 text-primary">{{ $booking->booking_number }}</h4>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <span class="badge bg-warning fs-6">{{ ucfirst($booking->status) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-car text-primary me-2"></i>
                                Vehicle Information
                            </h5>
                        </div>
                        <div class="col-md-4">
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
                            <h5 class="fw-bold">{{ $booking->vehicle->make }} {{ $booking->vehicle->model }}</h5>
                            <div class="row g-2">
                                <div class="col-6">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <strong>Year:</strong> {{ $booking->vehicle->year }}
                                    </small>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">
                                        <i class="fas fa-palette me-1"></i>
                                        <strong>Color:</strong> {{ $booking->vehicle->color }}
                                    </small>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">
                                        <i class="fas fa-users me-1"></i>
                                        <strong>Seats:</strong> {{ $booking->vehicle->seating_capacity }}
                                    </small>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">
                                        <i class="fas fa-gas-pump me-1"></i>
                                        <strong>Fuel:</strong> {{ $booking->vehicle->fuel_type }}
                                    </small>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">
                                        <i class="fas fa-id-card me-1"></i>
                                        <strong>License Plate:</strong> {{ $booking->vehicle->license_plate }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rental Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-calendar-alt text-primary me-2"></i>
                                Rental Information
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Pickup:</strong><br>
                                <i class="fas fa-calendar me-1 text-success"></i>
                                {{ $booking->pickup_datetime->format('M d, Y') }}<br>
                                <i class="fas fa-clock me-1 text-success"></i>
                                {{ $booking->pickup_datetime->format('h:i A') }}<br>
                                <i class="fas fa-map-marker-alt me-1 text-success"></i>
                                {{ $booking->pickup_location }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Return:</strong><br>
                                <i class="fas fa-calendar me-1 text-danger"></i>
                                {{ $booking->return_datetime->format('M d, Y') }}<br>
                                <i class="fas fa-clock me-1 text-danger"></i>
                                {{ $booking->return_datetime->format('h:i A') }}<br>
                                <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                {{ $booking->return_location }}
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="bg-light p-3 rounded">
                                <strong>Rental Duration:</strong> {{ $booking->rental_days }} {{ Str::plural('day', $booking->rental_days) }}
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-user text-primary me-2"></i>
                                Customer Information
                            </h5>
                        </div>
                        <div class="col-md-4">
                            <strong>Name:</strong><br>
                            {{ $booking->customer_name }}
                        </div>
                        <div class="col-md-4">
                            <strong>Email:</strong><br>
                            {{ $booking->customer_email }}
                        </div>
                        <div class="col-md-4">
                            <strong>Phone:</strong><br>
                            {{ $booking->customer_phone }}
                        </div>
                    </div>

                    @if($booking->special_requests)
                    <!-- Special Requests -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-clipboard-list text-primary me-2"></i>
                                Special Requests
                            </h5>
                            <div class="bg-light p-3 rounded">
                                {{ $booking->special_requests }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Payment Summary & Next Steps -->
        <div class="col-lg-4">
            <!-- Payment Summary -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        Payment Summary
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
                    <div class="d-flex justify-content-between mb-3 bg-light p-2 rounded">
                        <span>Deposit Required (30%):</span>
                        <span class="fw-bold text-warning">RM{{ number_format($booking->deposit_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Payment Status:</span>
                        <span class="badge bg-warning">{{ ucfirst($booking->payment_status) }}</span>
                    </div>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list-ol me-2"></i>
                        Next Steps
                    </h5>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li class="mb-2">
                            <strong>Confirmation:</strong> We will review and confirm your booking within 24 hours.
                        </li>
                        <li class="mb-2">
                            <strong>Payment:</strong> You'll receive payment instructions once confirmed.
                        </li>
                        <li class="mb-2">
                            <strong>Pickup:</strong> Bring your ID and driver's license for vehicle pickup.
                        </li>
                    </ol>
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('booking.show', $booking->id) }}" class="btn btn-primary">
                            <i class="fas fa-eye me-2"></i>View Booking Details
                        </a>
                        <a href="{{ route('booking.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-list me-2"></i>View All My Bookings
                        </a>
                        @if($booking->status === 'pending')
                        <form method="POST" action="{{ route('booking.confirm', $booking->id) }}" class="mt-2">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check me-2"></i>Confirm Booking
                            </button>
                            <small class="text-muted d-block mt-1">
                                *In a real system, this would process payment first
                            </small>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Important Information -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="alert alert-warning">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>Important Information</h5>
                <ul class="mb-0">
                    <li>Please save your booking reference number: <strong>{{ $booking->booking_number }}</strong></li>
                    <li>You can cancel your booking free of charge up to 24 hours before pickup</li>
                    <li>A valid driver's license and ID are required for vehicle pickup</li>
                    <li>Late returns may incur additional charges</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Contact Support -->
    <div class="row mt-4">
        <div class="col-12 text-center">
            <h5>Need Help?</h5>
            <p class="text-muted mb-3">Our customer support team is here to assist you.</p>
            <div class="d-flex gap-3 justify-content-center">
                <a href="tel:+60123456789" class="btn btn-outline-success">
                    <i class="fas fa-phone me-2"></i>Call Support
                </a>
                <a href="mailto:support@rentwheels.com?subject=Booking {{ $booking->booking_number }}" class="btn btn-outline-primary">
                    <i class="fas fa-envelope me-2"></i>Email Support
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS -->
<style>
    .success-icon {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
        100% {
            transform: scale(1);
        }
    }

    .card {
        transition: transform 0.2s ease;
    }

    .card:hover {
        transform: translateY(-2px);
    }
</style>
@endsection

