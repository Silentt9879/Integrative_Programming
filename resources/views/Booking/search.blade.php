@extends('app')

@section('title', 'Available Vehicles - RentWheels')

@section('content')
<div class="container py-5">
    <!-- Search Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-0">
                                <i class="fas fa-search me-2"></i>
                                Search Results: {{ $availableVehicles->count() }} Available Vehicles
                            </h4>
                            <p class="mb-0 mt-2 opacity-75">
                                <i class="fas fa-calendar me-2"></i>
                                {{ $pickupDate->format('M d, Y') }} - {{ $returnDate->format('M d, Y') }} 
                                ({{ $pickupDate->diffInDays($returnDate) }} {{ Str::plural('day', $pickupDate->diffInDays($returnDate)) }})
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <a href="{{ route('booking.search-form') }}" class="btn btn-light">
                                <i class="fas fa-edit me-2"></i>Modify Search
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($availableVehicles->count() > 0)
        <!-- Available Vehicles -->
        <div class="row">
            @foreach($availableVehicles as $vehicle)
            <div class="col-lg-6 col-xl-4 mb-4">
                <div class="card vehicle-card h-100 shadow-sm border-0 position-relative">
                    <!-- Availability Badge -->
                    <div class="position-absolute top-0 start-0 m-3" style="z-index: 10;">
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle me-1"></i>Available
                        </span>
                    </div>

                    <!-- Vehicle Image -->
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                        @if($vehicle->image_url)
                        <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->make }} {{ $vehicle->model }}" 
                             class="img-fluid rounded-top" style="max-height: 100%; max-width: 100%; object-fit: cover;">
                        @else
                        <i class="fas fa-car text-muted" style="font-size: 4rem;"></i>
                        @endif
                    </div>

                    <div class="card-body d-flex flex-column">
                        <!-- Vehicle Title -->
                        <h5 class="card-title fw-bold">{{ $vehicle->make }} {{ $vehicle->model }}</h5>

                        <!-- Vehicle Details -->
                        <div class="vehicle-details mb-3">
                            <div class="row text-muted small mb-2">
                                <div class="col-6">
                                    <i class="fas fa-calendar me-1"></i>{{ $vehicle->year }}
                                </div>
                                <div class="col-6">
                                    <i class="fas fa-palette me-1"></i>{{ $vehicle->color }}
                                </div>
                            </div>
                            <div class="row text-muted small mb-2">
                                <div class="col-6">
                                    <i class="fas fa-users me-1"></i>{{ $vehicle->seating_capacity }} Seats
                                </div>
                                <div class="col-6">
                                    <i class="fas fa-gas-pump me-1"></i>{{ $vehicle->fuel_type }}
                                </div>
                            </div>
                            <div class="row text-muted small">
                                <div class="col-12">
                                    <i class="fas fa-id-card me-1"></i>{{ $vehicle->license_plate }}
                                </div>
                            </div>
                        </div>

                        <!-- Price Information -->
                        <div class="price-info mb-3">
                            <div class="row align-items-center">
                                <div class="col-12">
                                    <h4 class="text-primary mb-1">
                                        RM{{ number_format($vehicle->rentalRate->daily_rate ?? 0, 2) }}
                                        <small class="text-muted fs-6">per day</small>
                                    </h4>
                                    @php
                                        $totalDays = $pickupDate->diffInDays($returnDate);
                                        $totalCost = $vehicle->rentalRate ? $vehicle->rentalRate->calculateRate($totalDays) : 0;
                                    @endphp
                                    <p class="text-success fw-bold mb-0">
                                        Total: RM{{ number_format($totalCost, 2) }}
                                        <small class="text-muted">for {{ $totalDays }} {{ Str::plural('day', $totalDays) }}</small>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Vehicle Type Badge -->
                        <div class="mb-3">
                            <span class="badge bg-primary">{{ $vehicle->type }}</span>
                            @if($vehicle->current_mileage)
                            <span class="badge bg-light text-dark">{{ number_format($vehicle->current_mileage) }}km</span>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-auto">
                            <div class="d-grid gap-2">
                                <a href="{{ route('vehicles.show', $vehicle->id) }}" class="btn btn-outline-info">
                                    <i class="fas fa-eye me-2"></i>View Details
                                </a>
                                @auth
                                <a href="{{ route('booking.create', $vehicle->id) }}?pickup_date={{ $pickupDate->format('Y-m-d') }}&return_date={{ $returnDate->format('Y-m-d') }}" 
                                   class="btn btn-success">
                                    <i class="fas fa-calendar-plus me-2"></i>Book This Vehicle
                                </a>
                                @else
                                <a href="{{ route('login') }}" class="btn btn-success">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login to Book
                                </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @guest
        <!-- Login Reminder for Guests -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <h5><i class="fas fa-info-circle me-2"></i>Ready to book your vehicle?</h5>
                    <p class="mb-3">Please login or register to complete your reservation.</p>
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary">
                            <i class="fas fa-user-plus me-2"></i>Register
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endguest

    @else
        <!-- No Results -->
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times text-muted mb-3" style="font-size: 4rem;"></i>
                    <h3 class="text-muted">No Available Vehicles</h3>
                    <p class="text-muted mb-4">
                        Sorry, no vehicles are available for your selected dates and criteria. 
                        Try adjusting your search parameters.
                    </p>
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="{{ route('booking.search-form') }}" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Try Different Dates
                        </a>
                        <a href="{{ route('vehicles.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-car me-2"></i>Browse All Vehicles
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Additional Information -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card bg-light border-0">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-info-circle text-primary me-2"></i>Booking Information</h5>
                            <ul class="list-unstyled mb-0">
                                <li><i class="fas fa-check text-success me-2"></i>Free cancellation up to 24 hours before pickup</li>
                                <li><i class="fas fa-check text-success me-2"></i>No hidden fees - price shown is final</li>
                                <li><i class="fas fa-check text-success me-2"></i>30% deposit required to confirm booking</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-shield-alt text-success me-2"></i>What's Included</h5>
                            <ul class="list-unstyled mb-0">
                                <li><i class="fas fa-check text-success me-2"></i>Full insurance coverage</li>
                                <li><i class="fas fa-check text-success me-2"></i>24/7 roadside assistance</li>
                                <li><i class="fas fa-check text-success me-2"></i>Free vehicle inspection</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS -->
<style>
    .vehicle-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .vehicle-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    .vehicle-details {
        font-size: 0.9rem;
    }

    .price-info {
        background: rgba(248, 249, 250, 0.5);
        padding: 1rem;
        border-radius: 8px;
        border-left: 4px solid var(--bs-primary);
    }

    .badge {
        font-size: 0.75rem;
    }
</style>
@endsection

