<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Dashboard - RentWheels</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            body {
                font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: #ffffff;
                min-height: 100vh;
            }

            .dashboard-container {
                padding: 2rem 0;
                background: #f8f9fa;
            }

            .admin-welcome-card {
                background: #ffffff;
                border-radius: 24px;
                box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
                border: 3px solid #dee2e6;
                padding: 2rem;
                margin-bottom: 2rem;
            }

            .admin-stats-card {
                background: #ffffff;
                border-radius: 16px;
                padding: 1.5rem;
                text-align: center;
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
                border: 3px solid #dee2e6;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .admin-stats-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
                border-color: #dc3545;
            }

            .stats-icon {
                font-size: 2.5rem;
                margin-bottom: 1rem;
            }

            .navbar-brand {
                font-weight: bold;
                font-size: 1.5rem;
            }

            .admin-card {
                background: #ffffff;
                border-radius: 16px;
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
                border: 3px solid #dee2e6;
                transition: transform 0.3s ease;
            }

            .admin-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 12px 35px rgba(0, 0, 0, 0.18);
                border-color: #dc3545;
            }

            .user-avatar {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: linear-gradient(135deg, #667eea, #764ba2);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: bold;
            }

            .status-badge {
                font-size: 0.8em;
                padding: 0.3rem 0.8rem;
                border-radius: 20px;
                font-weight: 600;
            }

            .status-active {
                background: linear-gradient(45deg, #28a745, #20c997);
                color: white;
            }
            .status-inactive {
                background: linear-gradient(45deg, #6c757d, #495057);
                color: white;
            }
            .status-pending {
                background: linear-gradient(45deg, #ffc107, #fd7e14);
                color: white;
            }
            .status-suspended {
                background: linear-gradient(45deg, #dc3545, #c82333);
                color: white;
            }

            .booking-status-confirmed {
                color: #28a745;
            }
            .booking-status-active {
                color: #007bff;
            }
            .booking-status-completed {
                color: #6c757d;
            }
            .booking-status-cancelled {
                color: #dc3545;
            }
            .booking-status-pending {
                color: #ffc107;
            }

            .quick-action-btn {
                background: linear-gradient(135deg, #dc3545, #6f42c1);
                border: none;
                color: white;
                transition: all 0.3s ease;
            }

            .quick-action-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 25px rgba(220, 53, 69, 0.3);
                color: white;
            }

            .table-responsive {
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                border: 2px solid #f8f9fa;
            }

            .admin-section-title {
                color: #343a40;
                font-weight: 600;
                margin-bottom: 1.5rem;
                padding-bottom: 0.5rem;
                border-bottom: 3px solid #dc3545;
                display: inline-block;
            }

            .metric-trend {
                font-size: 0.8rem;
                font-weight: 500;
            }

            .trend-up {
                color: #28a745;
            }
            .trend-down {
                color: #dc3545;
            }
            .trend-stable {
                color: #6c757d;
            }

            .container {
                max-width: 1200px;
            }

            .card-body {
                padding: 1.5rem;
            }

            .table {
                background: #ffffff;
            }

            .table-light th {
                background-color: #f8f9fa;
                border-color: #e9ecef;
                font-weight: 600;
            }

            /* Red theme navbar styles */
            .navbar-red {
                background: linear-gradient(135deg, #dc3545, #c82333) !important;
            }

            .admin-nav-item {
                margin: 0 0.25rem;
            }

            .admin-nav-item .nav-link {
                color: rgba(255, 255, 255, 0.9) !important;
                font-weight: 500;
                padding: 0.5rem 1rem;
                border-radius: 6px;
                transition: all 0.3s ease;
            }

            .admin-nav-item .nav-link:hover {
                background-color: rgba(255, 255, 255, 0.15);
                color: white !important;
                transform: translateY(-1px);
            }

            .admin-nav-item .nav-link.active {
                background-color: rgba(255, 255, 255, 0.2);
                color: white !important;
            }

            .logout-form {
                margin: 0;
            }

            .logout-btn {
                background: none !important;
                border: none !important;
                color: rgba(255, 255, 255, 0.9) !important;
                font-weight: 500;
                padding: 0.5rem 1rem;
                border-radius: 6px;
                transition: all 0.3s ease;
                cursor: pointer;
            }

            .logout-btn:hover {
                background-color: rgba(255, 255, 255, 0.15) !important;
                color: white !important;
                transform: translateY(-1px);
            }
        </style>
    </head>
    <body>
        <!-- Updated Admin Navigation Header -->
        <nav class="navbar navbar-expand-lg navbar-dark navbar-red">
            <div class="container">
                <a class="navbar-brand" href="{{ route('app') }}">
                    <i class="fas fa-car me-2"></i>RentWheels
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item admin-nav-item">
                            <a class="nav-link active" href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item admin-nav-item">
                            <a class="nav-link" href="{{ route('admin.customers') }}">
                                <i class="fas fa-users me-1"></i>Customers
                            </a>
                        </li>
                        <li class="nav-item admin-nav-item">
                            <a class="nav-link" href="{{ route('admin.vehicles') }}">
                                <i class="fas fa-car me-1"></i>Vehicles
                            </a>
                        </li>
                        <li class="nav-item admin-nav-item">
                            <a class="nav-link" href="{{ route('admin.reports') }}">
                                <i class="fas fa-chart-bar me-1"></i>Reports
                            </a>
                        </li>
                        <li class="nav-item admin-nav-item">
                            <form method="POST" action="{{ route('admin.logout') }}" class="logout-form d-inline">
                                @csrf
                                <button class="logout-btn" type="submit">
                                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Dashboard Content -->
        <div class="dashboard-container">
            <div class="container">
                <!-- Welcome Section -->
                <div class="admin-welcome-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h2 mb-3">
                                <i class="fas fa-shield-alt text-danger me-3"></i>
                                Admin Dashboard
                            </h1>
                            <p class="lead text-muted mb-3">
                                Welcome to the RentWheels administration panel. Monitor system performance, manage users, and oversee all operations.
                            </p>
                            <div class="d-flex gap-3 flex-wrap">
                                <a href="{{ route('admin.customers') }}" class="btn quick-action-btn btn-lg">
                                    <i class="fas fa-users me-2"></i>Manage Customers
                                </a>
                                <a href="{{ route('admin.vehicles') }}" class="btn quick-action-btn btn-lg">
                                    <i class="fas fa-car me-2"></i>Manage Vehicles
                                </a>
                                <a href="{{ route('admin.reports') }}" class="btn quick-action-btn btn-lg">
                                    <i class="fas fa-chart-bar me-2"></i>View Reports
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="display-1 text-danger">
                                <i class="fas fa-cogs"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Statistics -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="admin-stats-card">
                            <div class="stats-icon text-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <h4>{{ $totalUsers ?? 0 }}</h4>
                            <p class="text-muted">Total Customers</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="admin-stats-card">
                            <div class="stats-icon text-success">
                                <i class="fas fa-car"></i>
                            </div>
                            <h4>{{ $totalVehicles ?? 0 }}</h4>
                            <p class="text-muted">Total Vehicles</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="admin-stats-card">
                            <div class="stats-icon text-info">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <h4>{{ $totalBookings ?? 0 }}</h4>
                            <p class="text-muted">Total Bookings</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="admin-stats-card">
                            <div class="stats-icon text-warning">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <h4>RM{{ number_format($totalRevenue ?? 0, 2) }}</h4>
                            <p class="text-muted">Total Revenue</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Section -->
                <div class="row g-4">
                    <!-- Recent Customers -->
                    <div class="col-lg-6">
                        <div class="admin-card">
                            <div class="card-body">
                                <h5 class="admin-section-title">
                                    <i class="fas fa-user-plus text-primary me-2"></i>
                                    Recent Customers
                                </h5>

                                @if(!empty($recentUsers) && $recentUsers->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Customer</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th>Joined</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentUsers as $user)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="user-avatar me-3">
                                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                                        </div>
                                                        <div>
                                                            <strong>{{ $user->name }}</strong>
                                                            @if($user->is_admin)
                                                            <span class="badge bg-danger ms-1">Admin</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $user->email }}</small>
                                                </td>
                                                <td>
                                                    <span class="status-badge status-{{ strtolower($user->status ?? 'active') }}">
                                                        {{ ucfirst($user->status ?? 'Active') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        {{ $user->created_at ? $user->created_at->diffForHumans() : 'Unknown' }}
                                                    </small>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-users fa-3x mb-3"></i>
                                    <p>No recent users to display</p>
                                </div>
                                @endif

                                <div class="text-center mt-3">
                                    <a href="{{ route('admin.customers') }}" class="btn btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i>View All Users
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Bookings -->
                    <div class="col-lg-6">
                        <div class="admin-card">
                            <div class="card-body">
                                <h5 class="admin-section-title">
                                    <i class="fas fa-calendar-alt text-success me-2"></i>
                                    Recent Bookings
                                </h5>

                                @if(!empty($recentBookings) && $recentBookings->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>User</th>
                                                <th>Vehicle</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentBookings as $booking)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="user-avatar me-2">
                                                            {{ $booking->user ? strtoupper(substr($booking->user->name, 0, 1)) : '?' }}
                                                        </div>
                                                        <small>{{ $booking->user->name ?? 'Unknown User' }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        {{ $booking->vehicle->make ?? 'N/A' }} 
                                                        {{ $booking->vehicle->model ?? '' }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <small class="fw-bold text-success">
                                                        RM{{ number_format($booking->total_amount ?? 0, 2) }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="booking-status-{{ strtolower($booking->status ?? 'pending') }}">
                                                        <i class="fas fa-circle fa-xs me-1"></i>
                                                        {{ ucfirst($booking->status ?? 'Pending') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        {{ $booking->created_at ? $booking->created_at->diffForHumans() : 'Unknown' }}
                                                    </small>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-calendar fa-3x mb-3"></i>
                                    <p>No recent bookings to display</p>
                                </div>
                                @endif

                                <div class="text-center mt-3">
                                    <a href="{{ url('/admin/bookings') }}" class="btn btn-outline-success">
                                        <i class="fas fa-eye me-1"></i>View All Bookings
                                    </a>
                                </div>
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
                        <small class="text-muted">Administrator Panel</small>
                    </div>
                </div>
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>