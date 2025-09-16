@extends('app')

@section('title', 'My Bookings - RentWheels')

@section('content')
<div class="container py-5">
    <!-- Page Header -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="display-6 fw-bold mb-2">My Bookings</h1>
                    <p class="text-muted mb-0">Manage your vehicle rental bookings</p>
                </div>
                <div>
                    <div class="col-md-4 text-end">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('booking.search-form') }}" class="btn btn-success">
                                <i class="fas fa-plus me-2"></i>New Booking
                            </a>
                            <a href="{{ route('user.reports.booking-report') }}" class="btn btn-outline-primary">
                                <i class="fas fa-file-pdf me-2"></i>Export Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($bookings->count() > 0)
    <!-- Booking Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-check mb-2" style="font-size: 2rem;"></i>
                    <h4>{{ $bookings->where('status', '!=', 'cancelled')->count() }}</h4>
                    <p class="mb-0 small">Total Bookings</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-clock mb-2" style="font-size: 2rem;"></i>
                    <h4>{{ $bookings->where('status', 'pending')->count() }}</h4>
                    <p class="mb-0 small">Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-car mb-2" style="font-size: 2rem;"></i>
                    <h4>{{ $bookings->whereIn('status', ['confirmed', 'active'])->count() }}</h4>
                    <p class="mb-0 small">Active</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle mb-2" style="font-size: 2rem;"></i>
                    <h4>{{ $bookings->where('status', 'completed')->count() }}</h4>
                    <p class="mb-0 small">Completed</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="row mb-4">
        <div class="col-12">
            <ul class="nav nav-tabs" id="bookingTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                        <i class="fas fa-list me-1"></i>All Bookings
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                        <i class="fas fa-clock me-1"></i>Pending
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab">
                        <i class="fas fa-car me-1"></i>Active
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
                        <i class="fas fa-check me-1"></i>Completed
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Bookings List -->
    <div class="tab-content" id="bookingTabsContent">
        <!-- All Bookings Tab -->
        <div class="tab-pane fade show active" id="all" role="tabpanel">
            <div class="row">
                @foreach($bookings as $booking)
                <div class="col-12 mb-4">
                    <div class="card booking-card shadow-sm border-0">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <!-- Vehicle Image -->
                                <div class="col-md-2">
                                    @if($booking->vehicle->image_url)
                                    <img src="{{ $booking->vehicle->image_url }}" 
                                         alt="{{ $booking->vehicle->make }}" 
                                         class="img-fluid rounded booking-vehicle-img">
                                    @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center booking-vehicle-placeholder">
                                        <i class="fas fa-car text-muted"></i>
                                    </div>
                                    @endif
                                </div>

                                <!-- Booking Details -->
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="mb-0 fw-bold">{{ $booking->vehicle->make }} {{ $booking->vehicle->model }}</h5>
                                        <span class="badge bg-{{ $booking->status_badge_color }} ms-2">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </div>
                                    <p class="text-primary fw-semibold mb-2">{{ $booking->booking_number }}</p>
                                    <div class="row g-2 text-sm">
                                        <div class="col-6">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar-plus text-success me-1"></i>
                                                Pickup: {{ $booking->pickup_datetime->format('M d, Y') }}
                                            </small>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar-minus text-danger me-1"></i>
                                                Return: {{ $booking->return_datetime->format('M d, Y') }}
                                            </small>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                Duration: {{ $booking->rental_days }} {{ Str::plural('day', $booking->rental_days) }}
                                            </small>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                {{ $booking->pickup_location }}
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pricing & Payment -->
                                <div class="col-md-2 text-center">
                                    <h4 class="text-success mb-1">RM{{ number_format($booking->total_amount, 2) }}</h4>
                                    <small class="text-muted d-block mb-2">Total Amount</small>
                                    <span class="badge bg-{{ $booking->payment_badge_color }} small">
                                        {{ ucfirst($booking->payment_status) }}
                                    </span>
                                </div>

                                <!-- Actions -->
                                <div class="col-md-2">
                                    <div class="d-grid gap-1">
                                        <a href="{{ route('booking.show', $booking->id) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>View Details
                                        </a>

                                        @if(in_array($booking->status, ['pending', 'confirmed']))
                                        <form method="POST" action="{{ route('booking.cancel', $booking->id) }}" 
                                              class="d-inline" 
                                              onsubmit="return confirm('Are you sure you want to cancel this booking?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                                <i class="fas fa-times me-1"></i>Cancel
                                            </button>
                                        </form>
                                        @endif

                                        @if($booking->status === 'pending')
                                        <form method="POST" action="{{ route('booking.confirm', $booking->id) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success btn-sm w-100">
                                                <i class="fas fa-check me-1"></i>Confirm
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status-specific information bar -->
                        @if($booking->status === 'pending')
                        <div class="card-footer bg-warning bg-opacity-10 border-0">
                            <small class="text-warning">
                                <i class="fas fa-info-circle me-1"></i>
                                Booking is pending confirmation. Please confirm to secure your reservation.
                            </small>
                        </div>
                        @elseif($booking->status === 'confirmed')
                        <div class="card-footer bg-info bg-opacity-10 border-0">
                            <small class="text-info">
                                <i class="fas fa-check-circle me-1"></i>
                                Booking confirmed! Vehicle will be ready for pickup on {{ $booking->pickup_datetime->format('M d, Y') }}.
                            </small>
                        </div>
                        @elseif($booking->status === 'active')
                        <div class="card-footer bg-primary bg-opacity-10 border-0">
                            <small class="text-primary">
                                <i class="fas fa-car me-1"></i>
                                Vehicle is currently rented. Return by {{ $booking->return_datetime->format('M d, Y h:i A') }}.
                            </small>
                        </div>
                        @elseif($booking->status === 'completed')
                        <div class="card-footer bg-success bg-opacity-10 border-0">
                            <small class="text-success">
                                <i class="fas fa-thumbs-up me-1"></i>
                                Booking completed successfully. Thank you for choosing RentWheels!
                            </small>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Pending Bookings Tab -->
        <div class="tab-pane fade" id="pending" role="tabpanel">
            @php $pendingBookings = $bookings->where('status', 'pending') @endphp
            @if($pendingBookings->count() > 0)
            <div class="row">
                @foreach($pendingBookings as $booking)
                <div class="col-12 mb-4">
                    <!-- Same booking card structure as above -->
                    <div class="card booking-card shadow-sm border-0 border-start border-warning border-3">
                        <!-- Booking card content... (same as above) -->
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    @if($booking->vehicle->image_url)
                                    <img src="{{ $booking->vehicle->image_url }}" alt="{{ $booking->vehicle->make }}" class="img-fluid rounded booking-vehicle-img">
                                    @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center booking-vehicle-placeholder">
                                        <i class="fas fa-car text-muted"></i>
                                    </div>
                                    @endif
                                </div>
                                <div class="col-md-8">
                                    <h5 class="fw-bold">{{ $booking->vehicle->make }} {{ $booking->vehicle->model }}</h5>
                                    <p class="text-primary mb-2">{{ $booking->booking_number }} • RM{{ number_format($booking->total_amount, 2) }}</p>
                                    <p class="text-muted mb-0">{{ $booking->pickup_datetime->format('M d, Y') }} - {{ $booking->return_datetime->format('M d, Y') }}</p>
                                </div>
                                <div class="col-md-2">
                                    <form method="POST" action="{{ route('booking.confirm', $booking->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success w-100">Confirm Now</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-calendar-check text-muted mb-3" style="font-size: 3rem;"></i>
                <h4 class="text-muted">No Pending Bookings</h4>
                <p class="text-muted">All your bookings have been processed.</p>
            </div>
            @endif
        </div>

        <!-- Active Bookings Tab -->
        <div class="tab-pane fade" id="active" role="tabpanel">
            @php $activeBookings = $bookings->whereIn('status', ['confirmed', 'active']) @endphp
            @if($activeBookings->count() > 0)
            <div class="row">
                @foreach($activeBookings as $booking)
                <div class="col-lg-6 mb-4">
                    <div class="card booking-card shadow-sm border-0 border-start border-info border-3">
                        <div class="card-body">
                            <h5 class="fw-bold">{{ $booking->vehicle->make }} {{ $booking->vehicle->model }}</h5>
                            <p class="text-info fw-semibold">{{ $booking->booking_number }}</p>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">Pickup</small>
                                    <strong>{{ $booking->pickup_datetime->format('M d, Y') }}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Return</small>
                                    <strong>{{ $booking->return_datetime->format('M d, Y') }}</strong>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-{{ $booking->status_badge_color }}">{{ ucfirst($booking->status) }}</span>
                                <a href="{{ route('booking.show', $booking->id) }}" class="btn btn-outline-primary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-car text-muted mb-3" style="font-size: 3rem;"></i>
                <h4 class="text-muted">No Active Bookings</h4>
                <p class="text-muted">You don't have any confirmed or active rentals.</p>
            </div>
            @endif
        </div>

        <!-- Completed Bookings Tab -->
        <div class="tab-pane fade" id="completed" role="tabpanel">
            @php $completedBookings = $bookings->where('status', 'completed') @endphp
            @if($completedBookings->count() > 0)
            <div class="row">
                @foreach($completedBookings as $booking)
                <div class="col-lg-6 mb-4">
                    <div class="card booking-card shadow-sm border-0 border-start border-success border-3">
                        <div class="card-body">
                            <h5 class="fw-bold">{{ $booking->vehicle->make }} {{ $booking->vehicle->model }}</h5>
                            <p class="text-success fw-semibold">{{ $booking->booking_number }} • Completed</p>
                            <p class="text-muted mb-3">{{ $booking->pickup_datetime->format('M d, Y') }} - {{ $booking->return_datetime->format('M d, Y') }}</p>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-success">RM{{ number_format($booking->total_amount, 2) }}</span>
                                <a href="{{ route('booking.show', $booking->id) }}" class="btn btn-outline-success btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-history text-muted mb-3" style="font-size: 3rem;"></i>
                <h4 class="text-muted">No Completed Bookings</h4>
                <p class="text-muted">Your rental history will appear here.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Pagination -->
    @if($bookings->hasPages())
    <div class="row mt-4">
        <div class="col-12 d-flex justify-content-center">
            {{ $bookings->links() }}
        </div>
    </div>
    @endif

    @else
    <!-- No Bookings -->
    <div class="row">
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-calendar-times text-muted mb-4" style="font-size: 5rem;"></i>
                <h2 class="text-muted mb-3">No Bookings Yet</h2>
                <p class="text-muted mb-4">You haven't made any vehicle reservations. Start by browsing our available vehicles.</p>
                <div class="d-flex gap-3 justify-content-center">
                    <a href="{{ route('booking.search-form') }}" class="btn btn-success btn-lg">
                        <i class="fas fa-search me-2"></i>Search Vehicles
                    </a>
                    <a href="{{ route('vehicles.index') }}" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-car me-2"></i>Browse All Vehicles
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Custom CSS -->
<style>
    .booking-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border-radius: 12px;
    }

    .booking-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
    }

    .booking-vehicle-img {
        width: 100%;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
    }

    .booking-vehicle-placeholder {
        width: 100%;
        height: 60px;
        font-size: 1.5rem;
        border-radius: 8px;
    }

    .nav-tabs .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 500;
    }

    .nav-tabs .nav-link:hover {
        border-color: transparent;
        color: #007bff;
    }

    .nav-tabs .nav-link.active {
        background-color: transparent;
        border-color: transparent;
        color: #007bff;
        border-bottom: 2px solid #007bff;
    }

    .card .card-footer {
        font-size: 0.875rem;
        padding: 0.75rem 1rem;
    }

    @media (max-width: 768px) {
        .booking-card .row > div {
            margin-bottom: 1rem;
        }

        .booking-card .row > div:last-child {
            margin-bottom: 0;
        }
    }
</style>
@endsection

