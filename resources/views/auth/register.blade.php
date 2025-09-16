<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - RentWheels</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            min-height: 100vh;
            background: #ffffff;
            position: relative;
            overflow-x: hidden;
        }

        /* App.blade.php navbar and footer styles */
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), 
                        url('https://images.unsplash.com/photo-1449824913935-59a10b8d2000?ixlib=rb-4.0.3') center/cover;
            height: 400px;
            color: white;
        }
        
        .vehicle-card, .content-card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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

        /* Navigation Button Fix */
        .navbar .btn-outline-light {
            border: 1px solid rgba(255, 255, 255, 0.5);
            color: white;
            background: transparent;
        }

        .navbar .btn-outline-light:hover {
            background-color: rgba(255, 255, 255, 0.1) !important;
            border-color: rgba(255, 255, 255, 0.8) !important;
            color: white !important;
        }

        .register-container {
            min-height: calc(100vh - 200px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 0;
            position: relative;
            z-index: 10;
        }

        /* REGISTER CARD */
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 24px;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.15),
                0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            position: relative;
            transform-style: preserve-3d;
        }

        /* REGISTER HEADER */
        .register-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 50%, #17a2b8 100%);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .register-header h2 {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .register-header p {
            margin: 0.7rem 0 0 0;
            opacity: 0.95;
            font-weight: 400;
            font-size: 1.1rem;
            position: relative;
            z-index: 1;
        }

        .register-header i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        /* FORM STYLING */
        .register-body {
            padding: 2.5rem;
            position: relative;
        }

        .form-group {
            margin-bottom: 1.8rem;
            position: relative;
        }

        .form-label {
            font-weight: 600;
            color: #333333;
            margin-bottom: 0.6rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-group {
            position: relative;
            overflow: visible;
            border-radius: 15px;
        }

        .input-group-text {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666666;
            z-index: 5;
            font-size: 1.1rem;
        }

        /* Fix for address textarea icon */
        .address-icon {
            top: 20px !important;
            transform: translateY(0) !important;
        }

        /* Password toggle button */
        .password-toggle {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666666;
            z-index: 5;
            font-size: 1.1rem;
            cursor: pointer;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .password-toggle:hover {
            color: #333333;
        }

        .form-control.with-icon {
            background: rgba(248, 249, 250, 0.8);
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 1rem 1rem 1rem 3.5rem;
            font-size: 1rem;
            color: #333333;
            transition: all 0.3s ease;
        }

        .form-control.with-icon.with-toggle {
            padding-right: 3.5rem;
        }

        .form-control.with-icon::placeholder {
            color: #6c757d;
        }

        .form-control.with-icon:focus {
            background: #ffffff;
            border-color: #28a745;
            box-shadow: 
                0 0 0 4px rgba(40, 167, 69, 0.2),
                0 10px 30px rgba(0, 0, 0, 0.1);
            color: #333333;
            outline: none;
        }

        /* Two column layout for some fields */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        /* CHECKBOX */
        .form-check {
            margin: 1.5rem 0;
            display: flex;
            align-items: flex-start;
        }

        .form-check-input {
            width: 20px;
            height: 20px;
            margin-right: 0.8rem;
            margin-top: 2px;
            background: #ffffff;
            border: 2px solid #dee2e6;
            border-radius: 6px;
        }

        .form-check-input:checked {
            background: linear-gradient(135deg, #28a745, #20c997);
            border-color: #28a745;
        }

        .form-check-label {
            color: #495057;
            font-weight: 400;
            font-size: 0.9rem;
            cursor: pointer;
            user-select: none;
            line-height: 1.4;
        }

        .form-check-label a {
            color: #007bff;
            text-decoration: underline;
        }

        /* BUTTON */
        .btn-register {
            background: linear-gradient(135deg, #28a745 0%, #20c997 50%, #17a2b8 100%);
            border: none;
            padding: 1.2rem 2rem;
            font-size: 1.15rem;
            font-weight: 600;
            border-radius: 15px;
            width: 100%;
            color: white;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-shadow: 0 1px 3px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            box-shadow: 
                0 15px 35px rgba(40, 167, 69, 0.4),
                0 8px 20px rgba(0, 0, 0, 0.3);
            transform: translateY(-2px);
        }

        /* Password strength indicator */
        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.85rem;
        }

        .strength-bar {
            height: 4px;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 2px;
            overflow: hidden;
            margin-top: 0.3rem;
        }

        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak { background: #dc3545; width: 25%; }
        .strength-fair { background: #ffc107; width: 50%; }
        .strength-good { background: #28a745; width: 75%; }
        .strength-strong { background: #17a2b8; width: 100%; }

        /* LINKS */
        .register-links {
            text-align: center;
            margin-top: 2rem;
        }

        .register-links p {
            margin-bottom: 0.8rem;
            color: #495057;
        }

        .register-links a {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
            position: relative;
            padding: 0.3rem 0;
            transition: all 0.3s ease;
        }

        .register-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #28a745, #20c997);
            transform: translateX(-50%);
            transition: width 0.3s ease;
        }

        .register-links a:hover::after {
            width: 100%;
        }

        .register-links a:hover {
            color: #0056b3;
        }

        .divider {
            text-align: center;
            margin: 2rem 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(0,0,0,0.2), transparent);
        }

        .divider span {
            background: #ffffff;
            padding: 0.5rem 1.5rem;
            color: #495057;
            font-size: 0.9rem;
            font-weight: 500;
            border-radius: 25px;
            border: 1px solid #dee2e6;
            position: relative;
            z-index: 1;
        }

        /* ALERTS */
        .alert {
            background: rgba(248, 249, 250, 0.95);
            border: 1px solid #dee2e6;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            color: #495057;
        }

        .alert-danger {
            background: rgba(248, 215, 218, 0.8);
            border-color: #f5c6cb;
            color: #721c24;
        }

        .alert-success {
            background: rgba(212, 237, 218, 0.8);
            border-color: #c3e6cb;
            color: #155724;
        }

        @media (max-width: 768px) {
            .register-container {
                padding: 1.5rem 0.5rem;
                min-height: calc(100vh - 150px);
            }
            
            .register-body {
                padding: 1.5rem 1rem;
            }
            
            .register-header {
                padding: 1.5rem 1rem;
            }

            .register-header h2 {
                font-size: 1.6rem;
            }

            .register-card {
                max-width: 100%;
                margin: 0 0.5rem;
            }

            .form-group {
                margin-bottom: 1.5rem;
            }

            .form-row {
                gap: 0.8rem;
            }

            .form-row .form-group {
                margin-bottom: 1.2rem;
            }

            .form-label {
                font-size: 0.85rem;
                margin-bottom: 0.5rem;
            }

            .form-control.with-icon {
                padding: 0.9rem 0.9rem 0.9rem 2.8rem;
                font-size: 0.95rem;
            }

            .form-control.with-icon.with-toggle {
                padding-right: 2.8rem;
            }

            .input-group-text {
                left: 12px;
                font-size: 0.9rem;
            }

            .password-toggle {
                right: 12px;
                font-size: 0.9rem;
            }

            .btn-register {
                padding: 1rem 1.5rem;
                font-size: 1rem;
            }
        }

        /* Loading state */
        .btn-register.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .btn-register.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 22px;
            height: 22px;
            border: 3px solid transparent;
            border-top: 3px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }
    </style>
</head>
<body>
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
                        <a class="nav-link {{ request()->routeIs('booking.*') ? 'active-nav' : '' }}" href="{{ route('booking.index') }}">
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
                            <a class="nav-link {{ request()->routeIs('login') ? 'active-nav' : '' }}" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-light ms-2 px-3" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-1"></i>Register
                            </a>
                        </li>
                    @else
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>Account
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('dashboard') }}">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('my-bookings') }}">
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
                    @endguest
                    
                    <!-- Admin Link -->
                    <li class="nav-item">
                        <a class="nav-link text-warning" href="{{ route('admin.login') }}">
                            <i class="fas fa-cog me-1"></i>Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

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

    <!-- Register Section -->
    <div class="register-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="register-card">
                        
                        <div class="register-header">
                            <i class="fas fa-user-plus"></i>
                            <h2>Join RentWheels</h2>
                            <p>Create your account and start your journey</p>
                        </div>
                        
                        <div class="register-body">
                            <!-- Display any error messages -->
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0 list-unstyled">
                                        @foreach ($errors->all() as $error)
                                            <li><i class="fas fa-exclamation-circle me-2"></i>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Display success message -->
                            @if (session('success'))
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('register') }}" id="registerForm">
                                @csrf
                                
                                <!-- Name Field -->
                                <div class="form-group">
                                    <label for="name" class="form-label">Full Name</label>
                                    <div class="input-group">
                                        <input type="text" 
                                               class="form-control with-icon @error('name') is-invalid @enderror" 
                                               id="name" 
                                               name="name" 
                                               value="{{ old('name') }}" 
                                               placeholder="Enter your full name"
                                               required>
                                        <span class="input-group-text">
                                            <i class="fas fa-user"></i>
                                        </span>
                                    </div>
                                    @error('name')
                                        <div class="text-danger mt-2">
                                            <small><i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}</small>
                                        </div>
                                    @enderror
                                </div>

                                <!-- Email Field -->
                                <div class="form-group">
                                    <label for="email" class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <input type="email" 
                                               class="form-control with-icon @error('email') is-invalid @enderror" 
                                               id="email" 
                                               name="email" 
                                               value="{{ old('email') }}" 
                                               placeholder="Enter your email address"
                                               required>
                                        <span class="input-group-text">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                    </div>
                                    @error('email')
                                        <div class="text-danger mt-2">
                                            <small><i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}</small>
                                        </div>
                                    @enderror
                                </div>

                                <!-- Phone and Date of Birth Row -->
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <div class="input-group">
                                            <input type="tel" 
                                                   class="form-control with-icon @error('phone') is-invalid @enderror" 
                                                   id="phone" 
                                                   name="phone" 
                                                   value="{{ old('phone') }}" 
                                                   placeholder="Your phone number">
                                            <span class="input-group-text">
                                                <i class="fas fa-phone"></i>
                                            </span>
                                        </div>
                                        @error('phone')
                                            <div class="text-danger mt-2">
                                                <small><i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}</small>
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                                        <div class="input-group">
                                            <input type="date" 
                                                   class="form-control with-icon @error('date_of_birth') is-invalid @enderror" 
                                                   id="date_of_birth" 
                                                   name="date_of_birth" 
                                                   value="{{ old('date_of_birth') }}">
                                            <span class="input-group-text">
                                                <i class="fas fa-calendar"></i>
                                            </span>
                                        </div>
                                        @error('date_of_birth')
                                            <div class="text-danger mt-2">
                                                <small><i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}</small>
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Address Field -->
                                <div class="form-group">
                                    <label for="address" class="form-label">Address</label>
                                    <div class="input-group">
                                        <textarea class="form-control with-icon @error('address') is-invalid @enderror" 
                                                  id="address" 
                                                  name="address" 
                                                  rows="2" 
                                                  placeholder="Enter your address">{{ old('address') }}</textarea>
                                        <span class="input-group-text address-icon">
                                            <i class="fas fa-home"></i>
                                        </span>
                                    </div>
                                    @error('address')
                                        <div class="text-danger mt-2">
                                            <small><i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}</small>
                                        </div>
                                    @enderror
                                </div>

                                <!-- Password Fields -->
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <input type="password" 
                                                   class="form-control with-icon with-toggle @error('password') is-invalid @enderror" 
                                                   id="password" 
                                                   name="password" 
                                                   placeholder="Create a password"
                                                   required>
                                            <span class="input-group-text">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <button type="button" class="password-toggle" onclick="togglePassword('password', 'passwordToggleIcon')">
                                                <i class="fas fa-eye-slash" id="passwordToggleIcon"></i>
                                            </button>
                                        </div>
                                        <div class="password-strength" id="passwordStrength">
                                            <div class="strength-bar">
                                                <div class="strength-fill" id="strengthFill"></div>
                                            </div>
                                            <small id="strengthText">Password strength will appear here</small>
                                        </div>
                                        @error('password')
                                            <div class="text-danger mt-2">
                                                <small><i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}</small>
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                                        <div class="input-group">
                                            <input type="password" 
                                                   class="form-control with-icon with-toggle @error('password_confirmation') is-invalid @enderror" 
                                                   id="password_confirmation" 
                                                   name="password_confirmation" 
                                                   placeholder="Confirm your password"
                                                   required>
                                            <span class="input-group-text">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation', 'passwordConfirmToggleIcon')">
                                                <i class="fas fa-eye-slash" id="passwordConfirmToggleIcon"></i>
                                            </button>
                                        </div>
                                        @error('password_confirmation')
                                            <div class="text-danger mt-2">
                                                <small><i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}</small>
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success btn-register" id="registerBtn">
                                    <i class="fas fa-user-plus me-2"></i>
                                    <span id="btnText">Create Account</span>
                                </button>
                            </form>

                            <div class="divider">
                                <span>or</span>
                            </div>

                            <div class="register-links">
                                <p>Already have an account? <a href="{{ route('login') }}"><i class="fas fa-sign-in-alt me-1"></i>Sign in here</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
    <script>
        function togglePassword(passwordFieldId, toggleIconId) {
            const passwordInput = document.getElementById(passwordFieldId);
            const toggleIcon = document.getElementById(toggleIconId);
            
            if (passwordInput && toggleIcon) {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggleIcon.classList.remove('fa-eye-slash');
                    toggleIcon.classList.add('fa-eye');
                } else {
                    passwordInput.type = 'password';
                    toggleIcon.classList.remove('fa-eye');
                    toggleIcon.classList.add('fa-eye-slash');
                }
            }
        }

        // Password strength checker
        function checkPasswordStrength(password) {
            const strengthIndicator = {
                0: {class: '', text: 'Password strength will appear here'},
                1: {class: 'strength-weak', text: 'Weak - Add more characters'},
                2: {class: 'strength-fair', text: 'Fair - Add numbers and symbols'},
                3: {class: 'strength-good', text: 'Good - Looking better!'},
                4: {class: 'strength-strong', text: 'Strong - Great password!'}
            };

            let score = 0;
            if (password.length >= 8) score++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
            if (/\d/.test(password)) score++;
            if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) score++;

            return strengthIndicator[score];
        }

        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');

            if (passwordInput && strengthFill && strengthText) {
                passwordInput.addEventListener('input', function() {
                    const strength = checkPasswordStrength(this.value);
                    strengthFill.className = 'strength-fill ' + strength.class;
                    strengthText.textContent = strength.text;
                    strengthText.style.color = this.value.length > 0 ? '#333333' : '#6c757d';
                });
            }

            const registerForm = document.getElementById('registerForm');
            if (registerForm) {
                registerForm.addEventListener('submit', function(e) {
                    const btn = document.getElementById('registerBtn');
                    const btnText = document.getElementById('btnText');
                    
                    if (btn && btnText) {
                        btn.classList.add('loading');
                        btnText.textContent = 'Creating Account...';
                        
                        setTimeout(() => {
                            btn.classList.remove('loading');
                            btnText.textContent = 'Create Account';
                        }, 8000);
                    }
                });
            }

            const passwordConfirmInput = document.getElementById('password_confirmation');
            if (passwordInput && passwordConfirmInput) {
                passwordConfirmInput.addEventListener('input', function() {
                    if (this.value && passwordInput.value && this.value !== passwordInput.value) {
                        this.style.borderColor = 'rgba(220, 53, 69, 0.6)';
                    } else if (this.value && passwordInput.value && this.value === passwordInput.value) {
                        this.style.borderColor = 'rgba(40, 167, 69, 0.6)';
                    } else {
                        this.style.borderColor = '';
                    }
                });
            }

            const phoneInput = document.getElementById('phone');
            if (phoneInput) {
                phoneInput.addEventListener('input', function() {
                    let value = this.value.replace(/(?!^\+)\D/g, '');
                    this.value = value;
                });
            }

            const dobInput = document.getElementById('date_of_birth');
            if (dobInput) {
                dobInput.addEventListener('change', function() {
                    const today = new Date();
                    const birthDate = new Date(this.value);
                    
                    let age = today.getFullYear() - birthDate.getFullYear();
                    const monthDifference = today.getMonth() - birthDate.getMonth();
                    
                    if (monthDifference < 0 || (monthDifference === 0 && today.getDate() < birthDate.getDate())) {
                        age--;
                    }
                    
                    if (age < 18) {
                        this.style.borderColor = 'rgba(220, 53, 69, 0.6)';
                    } else {
                        this.style.borderColor = 'rgba(40, 167, 69, 0.6)'; // Green for valid age
                    }
                });
            }
        });
    </script>
</body>
</html>