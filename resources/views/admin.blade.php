<!-- resources/views/layouts/admin.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RentWheels - Vehicle Rental System')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), 
                        url('https://images.unsplash.com/photo-1449824913935-59a10b8d2000?ixlib=rb-4.0.3') center/cover;
            height: 400px;
            color: white;
        }
        
        .vehicle-card, .content-card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .vehicle-card:hover, .content-card:hover {
            transform: translateY(-5px);
        }
        
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .page-header {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            padding: 60px 0;
        }
        
        .active-nav {
            background-color: rgba(255,255,255,0.2);
            border-radius: 5px;
        }
        
        .admin-sidebar {
            background-color: #343a40;
            min-height: 100vh;
        }
        
        .admin-sidebar .nav-link {
            color: #ffffff;
            padding: 10px 20px;
        }
        
        .admin-sidebar .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .status-badge {
            font-size: 0.8em;
        }

        /* Custom red theme for navbar */
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
    <!-- Admin Navigation Header -->
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
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
                           href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item admin-nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.customers*') ? 'active' : '' }}" 
                           href="{{ route('admin.customers') }}">
                            <i class="fas fa-users me-1"></i>Customers
                        </a>
                    </li>
                    <li class="nav-item admin-nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.vehicles*') ? 'active' : '' }}" 
                           href="{{ route('admin.vehicles') }}">
                            <i class="fas fa-car me-1"></i>Vehicles
                        </a>
                    </li>
                    <li class="nav-item admin-nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}" 
                           href="{{ route('admin.reports') }}">
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

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        </div>
    @endif

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-car me-2"></i>RentWheels</h5>
                    <p>Your trusted partner for vehicle rental services.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-light"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-instagram fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-md-3">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('vehicles.index') }}" class="text-light text-decoration-none">Browse Vehicles</a></li>
                        <li><a href="{{ route('booking.index') }}" class="text-light text-decoration-none">Make Booking</a></li>
                        <li><a href="{{ route('contact') }}" class="text-light text-decoration-none">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-md-3 text-md-end">
                    <p>&copy; 2025 RentWheels. All rights reserved.</p>
                    <small>
                        <strong>BMIT3173 Assignment Team:</strong><br>
                        Chiew Chun Sheng, Jayvian Lazarus Jerome,<br>
                        Chong Zheng Yao, Tan Xing Ye
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>