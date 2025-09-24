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
                        <a class="nav-link {{ request()->routeIs('admin.notifications*') ? 'active' : '' }}" 
                           href="{{ route('admin.notifications.index') }}" id="navbar-notifications-link">
                            <i class="fas fa-bell me-1"></i>Notifications
                            <span class="badge bg-danger ms-1" id="navbar-notification-badge" style="display: none;">0</span>
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

    <script>
        // Load notification count on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadNotificationCount();
            
            // Auto-refresh notification count every 30 seconds
            setInterval(loadNotificationCount, 30000);
        });

        function loadNotificationCount() {
            fetch('/admin/notifications/stats', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('navbar-notification-badge');
                if (data.unread_total > 0) {
                    badge.textContent = data.unread_total > 99 ? '99+' : data.unread_total;
                    badge.style.display = 'inline';
                    
                    // Add pulse animation for new notifications
                    badge.classList.add('notification-pulse');
                    setTimeout(() => {
                        badge.classList.remove('notification-pulse');
                    }, 1000);
                } else {
                    badge.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error loading notification count:', error);
            });
        }
    </script>

    <style>
        /* Notification badge styling */
        #navbar-notification-badge {
            font-size: 0.7rem;
            padding: 0.2em 0.5em;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }

        /* Pulse animation for new notifications */
        @keyframes notificationPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        .notification-pulse {
            animation: notificationPulse 0.6s ease-in-out;
        }

        /* Bell icon animation on hover */
        .admin-nav-item .nav-link:hover .fa-bell {
            animation: bellSwing 0.8s ease-in-out;
        }

        @keyframes bellSwing {
            15% { transform: rotate(10deg); }
            30% { transform: rotate(-10deg); }
            50% { transform: rotate(6deg); }
            65% { transform: rotate(-6deg); }
            80% { transform: rotate(3deg); }
            100% { transform: rotate(0deg); }
        }
    </style>
</body>
</html>