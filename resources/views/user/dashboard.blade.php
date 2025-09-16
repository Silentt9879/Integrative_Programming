<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dashboard - RentWheels</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            body {
                font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
                min-height: 100vh;
            }

            .dashboard-container {
                padding: 2rem 0;
            }

            .welcome-card {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(20px);
                border-radius: 24px;
                box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
                border: 1px solid rgba(255, 255, 255, 0.2);
                padding: 2rem;
                margin-bottom: 2rem;
            }

            .stats-card {
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(10px);
                border-radius: 16px;
                padding: 1.5rem;
                text-align: center;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                border: 1px solid rgba(255, 255, 255, 0.3);
                transition: transform 0.3s ease;
            }

            .stats-card:hover {
                transform: translateY(-5px);
            }

            .stats-icon {
                font-size: 2.5rem;
                margin-bottom: 1rem;
            }

            .navbar-brand {
                font-weight: bold;
                font-size: 1.5rem;
            }

            .member-status {
                padding: 0.3rem 0.8rem;
                border-radius: 20px;
                font-weight: 600;
                font-size: 0.9rem;
            }

            .status-new {
                background: linear-gradient(45deg, #28a745, #20c997);
                color: white;
            }
            .status-member {
                background: linear-gradient(45deg, #007bff, #6610f2);
                color: white;
            }
            .status-regular {
                background: linear-gradient(45deg, #fd7e14, #e83e8c);
                color: white;
            }
            .status-vip {
                background: linear-gradient(45deg, #ffc107, #dc3545);
                color: white;
            }
        </style>
    </head>
    <body>
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand" href="{{ route('app') }}">
                    <i class="fas fa-car me-2"></i>RentWheels
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('app') }}">
                                <i class="fas fa-home me-1"></i>Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('vehicles.index') }}">
                                <i class="fas fa-car me-1"></i>Vehicles
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('booking.index') }}">
                                <i class="fas fa-calendar-check me-1"></i>Booking
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>{{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('dashboard') }}">
                                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                    </a></li>
                                <li><a class="dropdown-item" href="{{ route('booking.index') }}">
                                        <i class="fas fa-list me-2"></i>My Bookings
                                    </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                        @csrf
                                        <button class="dropdown-item" type="submit">
                                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Flash Messages -->
        @if(session('success'))
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        @endif

        <!-- Dashboard Content -->
        <div class="dashboard-container">
            <div class="container">
                <!-- Welcome Section -->
                <div class="welcome-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h2 mb-3">
                                <i class="fas fa-tachometer-alt text-primary me-3"></i>
                                Welcome back, {{ Auth::user()->name }}!
                            </h1>
                            <p class="lead text-muted mb-3">
                                Ready to find your perfect ride? Browse our available vehicles or manage your bookings.
                            </p>
                            <div class="d-flex gap-3">
                                <a href="{{ route('vehicles.index') }}" class="btn btn-primary btn-lg">
                                    <i class="fas fa-car me-2"></i>Browse Vehicles
                                </a>
                                <a href="{{ route('booking.search-form') }}" class="btn btn-success btn-lg">
                                    <i class="fas fa-plus me-2"></i>New Booking
                                </a>
                                <a href="{{ route('booking.index') }}" class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-list me-2"></i>View Bookings
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="display-1 text-primary">
                                <i class="fas fa-user-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-icon text-primary">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <h4>{{ $activeBookings ?? 0 }}</h4>
                            <p class="text-muted">Active Bookings</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-icon text-success">
                                <i class="fas fa-car"></i>
                            </div>
                            <h4>{{ $availableVehicles ?? 0 }}</h4>
                            <p class="text-muted">Available Vehicles</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-icon text-info">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h4>{{ $totalBookings ?? 0 }}</h4>
                            <p class="text-muted">Total Bookings</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-icon text-warning">
                                <i class="fas fa-star"></i>
                            </div>
                            <h4>
                                <span class="member-status status-{{ strtolower($memberStatus ?? 'new') }}">
                                    {{ $memberStatus ?? 'New' }}
                                </span>
                            </h4>
                            <p class="text-muted">Member Status</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-search text-primary me-2"></i>
                                    Find Your Perfect Vehicle
                                </h5>
                                <p class="card-text">
                                    Browse through our extensive fleet of well-maintained vehicles.
                                    <strong>{{ $availableVehicles ?? 0 }} vehicles currently available!</strong>
                                </p>
                                <a href="{{ route('vehicles.index') }}" class="btn btn-primary">
                                    <i class="fas fa-arrow-right me-1"></i>View All Vehicles
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-history text-success me-2"></i>
                                    Your Booking History
                                </h5>
                                <p class="card-text">
                                    View and manage all your past and current bookings.
                                    @if(($totalBookings ?? 0) > 0)
                                    <strong>You have {{ $totalBookings }} total bookings.</strong>
                                    @else
                                    <strong>Start your first booking today!</strong>
                                    @endif
                                </p>
                                <a href="{{ route('booking.index') }}" class="btn btn-success">
                                    <i class="fas fa-arrow-right me-1"></i>View Bookings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-dark text-light py-4 mt-5">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-car me-2"></i>RentWheels</h5>
                        <p>Your trusted partner for vehicle rental services.</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p>&copy; 2025 RentWheels. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>