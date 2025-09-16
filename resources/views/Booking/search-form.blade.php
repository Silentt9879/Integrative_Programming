@extends('app')

@section('title', 'Book a Vehicle - RentWheels')

@section('content')
<div class="container py-5">
    <!-- Page Header -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="display-5 fw-bold mb-4">Book Your Vehicle</h1>
            <p class="lead text-muted">Search for available vehicles and make your reservation</p>
        </div>
    </div>

    <!-- Search Form -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('booking.search') }}">
                        @csrf
                        <div class="row g-4">
                            <!-- Pickup Date -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-calendar-plus text-success me-2"></i>Pickup Date
                                </label>
                                <input type="date" 
                                       name="pickup_date" 
                                       class="form-control form-control-lg @error('pickup_date') is-invalid @enderror" 
                                       value="{{ old('pickup_date', date('Y-m-d')) }}"
                                       min="{{ date('Y-m-d') }}"
                                       required>
                                @error('pickup_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Return Date -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-calendar-minus text-danger me-2"></i>Return Date
                                </label>
                                <input type="date" 
                                       name="return_date" 
                                       class="form-control form-control-lg @error('return_date') is-invalid @enderror" 
                                       value="{{ old('return_date', date('Y-m-d', strtotime('+1 day'))) }}"
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                       required>
                                @error('return_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Vehicle Type -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-car text-primary me-2"></i>Vehicle Type
                                </label>
                                <select name="vehicle_type" class="form-select form-select-lg">
                                    <option value="">All Vehicle Types</option>
                                    <option value="Economy" {{ old('vehicle_type') == 'Economy' ? 'selected' : '' }}>Economy</option>
                                    <option value="Sedan" {{ old('vehicle_type') == 'Sedan' ? 'selected' : '' }}>Sedan</option>
                                    <option value="SUV" {{ old('vehicle_type') == 'SUV' ? 'selected' : '' }}>SUV</option>
                                    <option value="Luxury" {{ old('vehicle_type') == 'Luxury' ? 'selected' : '' }}>Luxury</option>
                                    <option value="Truck" {{ old('vehicle_type') == 'Truck' ? 'selected' : '' }}>Truck</option>
                                    <option value="Van" {{ old('vehicle_type') == 'Van' ? 'selected' : '' }}>Van</option>
                                </select>
                            </div>

                            <!-- Location -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-map-marker-alt text-warning me-2"></i>Pickup Location
                                </label>
                                <select name="location" class="form-select form-select-lg">
                                    <option value="">All Locations</option>
                                    <option value="Main Office" {{ old('location') == 'Main Office' ? 'selected' : '' }}>Main Office</option>
                                    <option value="Airport" {{ old('location') == 'Airport' ? 'selected' : '' }}>Airport</option>
                                    <option value="Downtown" {{ old('location') == 'Downtown' ? 'selected' : '' }}>Downtown</option>
                                    <option value="Mall Central" {{ old('location') == 'Mall Central' ? 'selected' : '' }}>Mall Central</option>
                                </select>
                            </div>

                            <!-- Search Button -->
                            <div class="col-12">
                                <button type="submit" class="btn btn-success btn-lg w-100 py-3">
                                    <i class="fas fa-search me-2"></i>Search Available Vehicles
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Information Section -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-calendar-check text-success mb-3" style="font-size: 2.5rem;"></i>
                            <h5 class="fw-bold">Easy Booking</h5>
                            <p class="text-muted mb-0">Simple and quick vehicle reservation process</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-shield-alt text-primary mb-3" style="font-size: 2.5rem;"></i>
                            <h5 class="fw-bold">Secure & Safe</h5>
                            <p class="text-muted mb-0">All vehicles are regularly maintained and insured</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-headset text-warning mb-3" style="font-size: 2.5rem;"></i>
                            <h5 class="fw-bold">24/7 Support</h5>
                            <p class="text-muted mb-0">Round-the-clock customer support for your needs</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @guest
    <!-- Login Prompt for Guests -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="alert alert-info text-center">
                <h5><i class="fas fa-info-circle me-2"></i>Ready to book?</h5>
                <p class="mb-3">Please login or register to complete your vehicle reservation.</p>
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
</div>

@section('scripts')
<script>
    document.querySelector('input[name="pickup_date"]').addEventListener('change', function() {
        const pickupDate = new Date(this.value);
        const returnDate = new Date(pickupDate);
        returnDate.setDate(returnDate.getDate() + 1);
        
        const returnInput = document.querySelector('input[name="return_date"]');
        returnInput.min = returnDate.toISOString().split('T')[0];
        
        if (new Date(returnInput.value) <= pickupDate) {
            returnInput.value = returnDate.toISOString().split('T')[0];
        }
    });
</script>
@endsection
@endsection
