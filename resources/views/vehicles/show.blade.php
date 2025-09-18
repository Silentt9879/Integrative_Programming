@extends('app')

@section('title', $vehicle->make . ' ' . $vehicle->model . ' - RentWheels')

@section('content')
<div class="container py-5">
    <!-- Back Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('vehicles.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Vehicles
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Vehicle Image -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    @if($vehicle->image_url)
                    <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->make }} {{ $vehicle->model }}"
                         class="img-fluid rounded w-100" style="height: 400px; object-fit: cover;">
                    @else
                    <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 400px;">
                        <i class="fas fa-car text-muted" style="font-size: 6rem;"></i>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Vehicle Details -->
        <div class="col-lg-6">
            <div class="vehicle-info">
                <!-- Title and Status -->
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h1 class="display-6 fw-bold">{{ $vehicle->make }} {{ $vehicle->model }}</h1>
                    @if($vehicle->status == 'available')
                    <span class="badge bg-success fs-6">Available</span>
                    @elseif($vehicle->status == 'rented')
                    <span class="badge bg-warning fs-6">Rented</span>
                    @else
                    <span class="badge bg-secondary fs-6">Maintenance</span>
                    @endif
                </div>

                <div class="price-section mb-4">
                    <h2 class="text-primary mb-0">RM{{ number_format($vehicle->rentalRate->daily_rate ?? 0, 2) }}</h2>
                    <p class="text-muted">per day</p>
                </div>

                <!-- Vehicle Specifications -->
                <div class="card border-0 bg-light mb-4">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Vehicle Specifications</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="spec-item">
                                    <i class="fas fa-calendar text-primary me-2"></i>
                                    <strong>Year:</strong> {{ $vehicle->year }}
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="spec-item">
                                    <i class="fas fa-palette text-primary me-2"></i>
                                    <strong>Color:</strong> {{ $vehicle->color }}
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="spec-item">
                                    <i class="fas fa-car text-primary me-2"></i>
                                    <strong>Type:</strong> {{ $vehicle->type }}
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="spec-item">
                                    <i class="fas fa-users text-primary me-2"></i>
                                    <strong>Seats:</strong> {{ $vehicle->seating_capacity }}
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="spec-item">
                                    <i class="fas fa-gas-pump text-primary me-2"></i>
                                    <strong>Fuel:</strong> {{ $vehicle->fuel_type }}
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="spec-item">
                                    <i class="fas fa-id-card text-primary me-2"></i>
                                    <strong>Plate:</strong> {{ $vehicle->license_plate }}
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="spec-item">
                                    <i class="fas fa-tachometer-alt text-primary me-2"></i>
                                    <strong>Mileage:</strong> {{ number_format($vehicle->current_mileage) }} km
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                @if($vehicle->description)
                <div class="card border-0 bg-light mb-4">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Description</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $vehicle->description }}</p>
                    </div>
                </div>
                @endif

                <!-- Features -->
                <div class="card border-0 bg-light mb-4">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0"><i class="fas fa-star me-2"></i>Features</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Air Conditioning</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Bluetooth</li>
                                </ul>
                            </div>
                            <div class="col-6">
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>GPS Navigation</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Backup Camera</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="card border-0 bg-primary text-white mb-4">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-phone me-2"></i>Contact Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">Interested in this vehicle? Contact us for more information or to inquire about availability.</p>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Phone:</strong><br>
                                <a href="tel:+60123456789" class="text-white">+60 12-345 6789</a>
                            </div>
                            <div class="col-md-6">
                                <strong>Email:</strong><br>
                                <a href="mailto:info@rentwheels.com" class="text-white">info@rentwheels.com</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons mb-4">
                    <div class="d-grid gap-2">
                        @if($vehicle->status == 'available')
                        @auth
                        <a href="{{ route('booking.create', $vehicle->id) }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-calendar-plus me-2"></i>Book This Vehicle
                        </a>
                        @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Login to Book
                        </a>
                        @endauth
                        @else
                        <button class="btn btn-secondary btn-lg" disabled>
                            <i class="fas fa-ban me-2"></i>Vehicle Not Available
                        </button>
                        @endif
                        <a href="mailto:info@rentwheels.com?subject=Inquiry about {{ $vehicle->make }} {{ $vehicle->model }}&body=Hi, I'm interested in learning more about the {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->license_plate }}). Please contact me with more information." class="btn btn-outline-primary">
                            <i class="fas fa-envelope me-2"></i>Send Email Inquiry
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Similar Vehicles Section -->
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="mb-4">Similar Vehicles</h3>
            <div class="row">
                <!-- This would be populated by controller with similar vehicles -->
                <div class="col-12">
                    <p class="text-muted text-center py-4">
                        <a href="{{ route('vehicles.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-eye me-2"></i>View All Vehicles
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS -->
<style>
    .spec-item {
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .price-section h2 {
        font-size: 2.5rem;
        font-weight: 700;
    }

    .vehicle-info .card {
        margin-bottom: 1.5rem;
    }

    .action-buttons .btn {
        border-radius: 8px;
        font-weight: 500;
    }
</style>
@endsection
