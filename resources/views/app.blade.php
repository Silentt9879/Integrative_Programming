<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                background: linear-gradient(135deg, #007bff, #0056b3);
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
                            <a class="nav-link {{ request()->routeIs('app') ? 'active-nav' : '' }}" href="{{ route('app') }}">
                                <i class="fas fa-home me-1"></i>Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('vehicles.*') ? 'active-nav' : '' }}" href="{{ route('vehicles.index') }}">
                                <i class="fas fa-car me-1"></i>Vehicles
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('booking.*') ? 'active-nav' : '' }}" href="{{ route('booking.search-form') }}">
                                <i class="fas fa-calendar-check me-1"></i>Booking
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('about') ? 'active-nav' : '' }}" href="{{ route('about') }}">
                                <i class="fas fa-info-circle me-1"></i>About
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('contact') ? 'active-nav' : '' }}" href="{{ route('contact') }}">
                                <i class="fas fa-envelope me-1"></i>Contact
                            </a>
                        </li>

                        <!-- Authentication Links -->
                        @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-warning" href="{{ route('admin.login') }}">
                                <i class="fas fa-cog me-1"></i>Admin
                            </a>
                        </li>
                        @else
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>{{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('dashboard') }}">
                                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                    </a></li>
                                <li><a class="dropdown-item" href="{{ route('booking.index') }}">
                                        <i class="fas fa-calendar me-2"></i>My Bookings
                                    </a></li>
                                <li><hr class="dropdown-divider"></li>
                            </ul>
                        </li>
                        @endguest
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
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if($errors->any() && !in_array(Route::currentRouteName(), ['admin.login', 'admin.login.post', 'login', 'register']))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
                            <li><a href="{{ route('booking.search-form') }}" class="text-light text-decoration-none">Book a Vehicle</a></li>
                            <li><a href="{{ route('contact') }}" class="text-light text-decoration-none">Contact Us</a></li>
                            <li><a href="{{ route('about') }}" class="text-light text-decoration-none">About Us</a></li>
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