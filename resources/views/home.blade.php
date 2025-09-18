@extends('app')

@section('title', 'Home - RentWheels')

@section('content')

<!-- Hero Section -->
<section class="hero-section d-flex align-items-center">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Find Your Perfect Rental Vehicle</h1>
                <p class="lead mb-4">Choose from our wide selection of cars, SUVs, and luxury vehicles.</p>
                <div class="mt-4">
                    <a href="{{ route('vehicles.index') }}" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-car me-2"></i>Browse Vehicles
                    </a>
                    @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    @else
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Vehicles Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Available Vehicles</h2>

        @if($vehicles->count() > 0)
        <div class="row">
            @foreach($vehicles as $vehicle)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card vehicle-card border-0">
                    <!-- Vehicle image from database -->
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                        @if($vehicle->image_url)
                        <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->make }} {{ $vehicle->model }}"
                             class="img-fluid" style="max-height: 100%; max-width: 100%; object-fit: cover;">
                        @else
                        <i class="fas fa-car text-muted" style="font-size: 4rem;"></i>
                        @endif
                    </div>

                    <div class="card-body">
                        <h5 class="card-title">{{ $vehicle->make }} {{ $vehicle->model }}</h5>
                        <p class="text-muted mb-2">
                            <i class="fas fa-calendar me-2"></i>{{ $vehicle->year }}
                        </p>
                        <div class="row align-items-center mb-3">
                            <div class="col-6">
                                <h4 class="text-primary mb-0">RM{{ number_format($vehicle->rentalRate->daily_rate ?? 0, 2) }}</h4>
                                <small class="text-muted">per day</small>
                            </div>
                            <div class="col-6 text-end">
                                <span class="badge bg-primary">{{ $vehicle->type }}</span>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="{{ route('vehicles.show', $vehicle->id) }}" class="btn btn-primary">
                                <i class="fas fa-eye me-2"></i>View Details
                            </a>
                            @if($vehicle->status == 'available')
                            @auth
                            <a href="{{ route('booking.create', $vehicle->id) }}" class="btn btn-success">
                                <i class="fas fa-calendar-plus me-2"></i>Book Now
                            </a>
                            @else
                            <a href="{{ route('login') }}" class="btn btn-success">
                                <i class="fas fa-sign-in-alt me-2"></i>Book Now
                            </a>
                            @endauth
                            @else
                            <button class="btn btn-secondary" disabled>
                                <i class="fas fa-ban me-2"></i>Not Available
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Call to action to view more vehicles -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="{{ route('vehicles.index') }}" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-eye me-2"></i>View All Available Vehicles
                </a>
            </div>
        </div>
        @else
        <div class="row">
            <div class="col-12 text-center">
                <div class="py-5">
                    <i class="fas fa-car text-muted mb-3" style="font-size: 4rem;"></i>
                    <h3 class="text-muted">No vehicles available at the moment</h3>
                    <p class="text-muted mb-4">Add some vehicles to get started with your rental system.</p>
                    <div class="d-flex gap-2 justify-content-center">
                        @auth
                        @if(Auth::user()->is_admin)
                        <a href="{{ route('admin.vehicles.create') }}" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Add Vehicles
                        </a>
                        <a href="{{ route('admin.vehicles') }}" class="btn btn-primary">
                            <i class="fas fa-car me-2"></i>Manage Vehicles
                        </a>
                        @else
                        <p class="text-muted">Please check back later for available vehicles.</p>
                        @endif
                        @else
                        <div class="d-flex flex-column align-items-center">
                            <p class="text-muted mb-3">Please check back later for available vehicles.</p>
                            <a href="{{ route('login') }}" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Login to Get Started
                            </a>
                        </div>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>

@endsection
