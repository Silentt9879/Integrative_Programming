@extends('app')

@section('title', 'Login - RentWheels')

@section('content')
<style>
    body {
        font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        min-height: 100vh;
        background: #ffffff;
        position: relative;
        overflow-x: hidden;
    }

    .login-container {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 3rem 0;
        position: relative;
        z-index: 10;
    }

    /* LOGIN CARD */
    .login-card {
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
        max-width: 420px;
        position: relative;
        transform-style: preserve-3d;
    }

    /* LOGIN HEADER */
    .login-header {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 50%, #004085 100%);
        color: white;
        padding: 2.5rem 2rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .login-header h2 {
        margin: 0;
        font-size: 2rem;
        font-weight: 700;
        position: relative;
        z-index: 1;
        text-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }

    .login-header p {
        margin: 0.7rem 0 0 0;
        opacity: 0.95;
        font-weight: 400;
        font-size: 1.1rem;
        position: relative;
        z-index: 1;
    }

    .login-header i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        opacity: 0.9;
        position: relative;
        z-index: 1;
    }

    /* FORM STYLING */
    .login-body {
        padding: 2.5rem;
        position: relative;
    }

    .form-group {
        margin-bottom: 2rem;
        position: relative;
    }

    .form-label {
        font-weight: 600;
        color: #333333;
        margin-bottom: 0.8rem;
        font-size: 0.95rem;
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
        font-size: 1.05rem;
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
        border-color: #007bff;
        box-shadow: 
            0 0 0 4px rgba(0, 123, 255, 0.2),
            0 10px 30px rgba(0, 0, 0, 0.1);
        color: #333333;
        outline: none;
    }

    /* CHECKBOX - USING ADMIN STYLE */
    .form-check {
        margin: 1.8rem 0;
        display: flex;
        align-items: center;
    }

    .form-check-input {
        width: 24px;
        height: 24px;
        margin-right: 0.8rem;
        background: #fff;
        border: 2px solid #007bff;
        border-radius: 6px;
        appearance: none;
        -webkit-appearance: none;
        cursor: pointer;
        position: relative;
        transition: all 0.2s ease;
    }
    
    /* Hover effect */
    .form-check-input:hover {
        border-color: #0056b3; 
    }

    .form-check-input:checked {
        background: #007bff;
        border-color: #007bff;
    }
    
    /* Tick inside */
    .form-check-input:checked::after {
        content: "✓";
        color: #fff;
        font-size: 16px;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -55%);
        font-weight: bold;
    }

    .form-check-label {
        color: #495057;
        font-weight: 500;
        font-size: 1rem;
        cursor: pointer;
        user-select: none;
    }

    /* BUTTON */
    .btn-login {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 50%, #004085 100%);
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

    .btn-login:hover {
        box-shadow: 
            0 15px 35px rgba(0, 123, 255, 0.4),
            0 8px 20px rgba(0, 0, 0, 0.3);
        transform: translateY(-2px);
    }

    /* LINKS */
    .login-links {
        text-align: center;
        margin-top: 2rem;
    }

    .login-links p {
        margin-bottom: 0.8rem;
        color: #495057;
    }

    .login-links a {
        color: #007bff;
        text-decoration: none;
        font-weight: 500;
        position: relative;
        padding: 0.3rem 0;
        transition: all 0.3s ease;
    }

    .login-links a::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        width: 0;
        height: 2px;
        background: linear-gradient(90deg, #007bff, #0056b3);
        transform: translateX(-50%);
        transition: width 0.3s ease;
    }

    .login-links a:hover::after {
        width: 100%;
    }

    .login-links a:hover {
        color: #0056b3;
    }

    .divider {
        text-align: center;
        margin: 2.5rem 0;
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
        .login-container {
            padding: 2rem 1rem;
        }
        
        .login-body {
            padding: 2rem 1.5rem;
        }
        
        .login-header {
            padding: 2rem 1.5rem;
        }

        .login-header h2 {
            font-size: 1.7rem;
        }

        .login-card {
            max-width: 100%;
        }
    }

    /* Loading state */
    .btn-login.loading {
        pointer-events: none;
        opacity: 0.7;
    }

    .btn-login.loading::after {
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

<!-- Login Section -->
<div class="login-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="login-card">
                    
                    <div class="login-header">
                        <i class="fas fa-car"></i>
                        <h2>Welcome Back</h2>
                        <p>Access your RentWheels account</p>
                    </div>
                    
                    <div class="login-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0 list-unstyled">
                                    @foreach ($errors->all() as $error)
                                        <li><i class="fas fa-exclamation-circle me-2"></i>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" id="loginForm">
                            @csrf
                            
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
                            </div>

                            <div class="form-group">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control with-icon with-toggle @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Enter your password"
                                           required>
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <button type="button" class="password-toggle" onclick="togglePassword()">
                                        <i class="fas fa-eye-slash" id="passwordToggleIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-login" id="loginBtn">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                <span id="btnText">Sign In</span>
                            </button>
                        </form>

                        <div class="divider">
                            <span>or</span>
                        </div>

                        <div class="login-links">
                            <p><a href="{{ route('password.request') }}"><i class="fas fa-key me-1"></i>Forgot your password?</a></p>
                            <p>Don't have an account? <a href="{{ route('register') }}"><i class="fas fa-user-plus me-1"></i>Create one here</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('passwordToggleIcon');
        
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

    document.addEventListener('DOMContentLoaded', function() {
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                const btn = document.getElementById('loginBtn');
                const btnText = document.getElementById('btnText');
                
                if (btn && btnText) {
                    btn.classList.add('loading');
                    btnText.textContent = 'Signing In...';
                    
                    setTimeout(() => {
                        btn.classList.remove('loading');
                        btnText.textContent = 'Sign In';
                    }, 5000);
                }
            });
        }

        // Remember me functionality
        const rememberCheckbox = document.getElementById('remember');
        if (rememberCheckbox) {
            rememberCheckbox.addEventListener('change', function() {
                if (this.checkaed) {
                    console.log('Remember me enabled');
                } else {
                    console.log('Remember me disabled');
                }
            });
        }
    });
</script>
@endsection