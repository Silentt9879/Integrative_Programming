@extends('app')

@section('title', 'Admin Login - RentWheels')

@section('content')
<style>
    body {
        font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        min-height: 100vh;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 50%, #e9ecef 100%);
        position: relative;
        overflow-x: hidden;
    }

    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: 
            radial-gradient(circle at 20% 80%, rgba(220, 53, 69, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(108, 117, 125, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 40% 40%, rgba(111, 66, 193, 0.1) 0%, transparent 50%);
        pointer-events: none;
        z-index: 1;
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

    .admin-login-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 24px;
        box-shadow: 
            0 25px 50px rgba(0, 0, 0, 0.15),
            0 15px 35px rgba(0, 0, 0, 0.1),
            inset 0 1px 0 rgba(255, 255, 255, 0.8);
        overflow: hidden;
        width: 100%;
        max-width: 420px;
        position: relative;
    }

    .admin-header {
        background: #dc3545;
        color: white;
        padding: 2.5rem 2rem;
        text-align: center;
        position: relative;
    }

    .admin-header h2 {
        margin: 0;
        font-size: 2rem;
        font-weight: 700;
        text-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }

    .admin-header p {
        margin: 0.7rem 0 0 0;
        opacity: 0.95;
        font-weight: 400;
        font-size: 1.1rem;
    }

    .admin-header i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        opacity: 0.9;
    }

    .admin-body {
        padding: 2.5rem;
        position: relative;
        background: white;
    }

    .form-group {
        margin-bottom: 2rem;
        position: relative;
    }

    .form-label {
        font-weight: 600;
        color: #343a40;
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
        color: rgba(52, 58, 64, 0.7);
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
        color: rgba(52, 58, 64, 0.7);
        z-index: 5;
        font-size: 1.1rem;
        cursor: pointer;
        padding: 0;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s ease;
    }

    .password-toggle:hover {
        color: #343a40;
    }

    .form-control.with-icon {
        background: rgba(248, 249, 250, 0.8);
        border: 2px solid rgba(0, 0, 0, 0.1);
        border-radius: 15px;
        padding: 1rem 1rem 1rem 3.5rem;
        font-size: 1.05rem;
        color: #343a40;
        transition: all 0.3s ease;
    }

    .form-control.with-icon.with-toggle {
        padding-right: 3.5rem;
    }

    .form-control.with-icon::placeholder {
        color: rgba(52, 58, 64, 0.6);
    }

    .form-control.with-icon:focus {
        background: rgba(255, 255, 255, 0.95);
        border-color: rgba(220, 53, 69, 0.5);
        box-shadow: 
            0 0 0 4px rgba(220, 53, 69, 0.1),
            0 10px 30px rgba(0, 0, 0, 0.1);
        color: #343a40;
        outline: none;
    }

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
        border: 2px solid #dc3545;
        border-radius: 6px;
        appearance: none;
        -webkit-appearance: none;
        cursor: pointer;
        position: relative;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }
    
    .form-check-input:hover {
        border-color: #b02a37; 
    }

    .form-check-input:checked {
        background: #dc3545;
        border-color: #dc3545;
    }
    
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
        color: #343a40;
        font-weight: 500;
        font-size: 1rem;
        cursor: pointer;
        user-select: none;
    }

    .btn-admin-login {
        background: #dc3545;
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
        cursor: pointer;
    }

    .btn-admin-login:hover {
        background: #b02a37;
        box-shadow: 
            0 15px 35px rgba(220, 53, 69, 0.4),
            0 8px 20px rgba(0, 0, 0, 0.3);
        transform: translateY(-2px);
        color: white;
    }

    .alert {
        background: rgba(248, 249, 250, 0.9);
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 12px;
        margin-bottom: 1.5rem;
        backdrop-filter: blur(10px);
        color: #343a40;
        padding: 1rem 1.2rem;
    }

    .alert-danger {
        background: rgba(220, 53, 69, 0.1);
        border-color: rgba(220, 53, 69, 0.2);
        color: #721c24;
    }

    .alert-success {
        background: rgba(25, 135, 84, 0.1);
        border-color: rgba(25, 135, 84, 0.2);
        color: #0f5132;
    }

    .btn-admin-login.loading {
        pointer-events: none;
        opacity: 0.7;
    }

    .btn-admin-login.loading .btn-content {
        opacity: 0;
    }

    .btn-admin-login.loading::after {
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

    .form-control.is-invalid {
        border-color: rgba(220, 53, 69, 0.8);
        background: rgba(220, 53, 69, 0.05);
    }

    .form-control.is-invalid:focus {
        border-color: rgba(220, 53, 69, 0.8);
        box-shadow: 
            0 0 0 4px rgba(220, 53, 69, 0.2),
            0 10px 30px rgba(220, 53, 69, 0.1);
    }

    .text-danger {
        color: #dc3545 !important;
    }

    .text-danger small {
        font-size: 0.875rem;
    }

    @media (max-width: 768px) {
        .login-container {
            padding: 2rem 1rem;
            min-height: calc(100vh - 100px);
        }
        
        .admin-body {
            padding: 2rem 1.5rem;
        }
        
        .admin-header {
            padding: 2rem 1.5rem;
        }

        .admin-header h2 {
            font-size: 1.7rem;
        }

        .admin-header i {
            font-size: 2rem;
        }

        .admin-login-card {
            max-width: 100%;
            margin: 0 1rem;
        }

        .form-control.with-icon {
            font-size: 1rem;
        }

        .btn-admin-login {
            font-size: 1rem;
            padding: 1rem 1.5rem;
        }
    }

    @media (max-width: 480px) {
        .admin-header {
            padding: 1.5rem 1rem;
        }

        .admin-body {
            padding: 1.5rem 1rem;
        }

        .admin-header h2 {
            font-size: 1.5rem;
        }

        .admin-header p {
            font-size: 1rem;
        }
    }
</style>

<!-- Admin Login Section -->
<div class="login-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="admin-login-card">
                    <div class="admin-header">
                        <i class="fas fa-shield-alt"></i>
                        <h2>Admin Access</h2>
                        <p>RentWheels Administration Panel</p>
                    </div>
                    
                    <div class="admin-body">
                        <!-- Display only ONE consolidated error message -->
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>Please fix the following errors:</strong>
                                <ul class="mb-0 mt-2 list-unstyled">
                                    @foreach ($errors->all() as $error)
                                        <li class="mt-1">• {{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.login.post') }}" id="adminLoginForm" novalidate>
                            @csrf
                            
                            <!-- Email Field -->
                            <div class="form-group">
                                <label for="email" class="form-label">Admin Email</label>
                                <div class="input-group">
                                    <input type="email" 
                                           class="form-control with-icon @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email') }}" 
                                           placeholder="Enter admin email address"
                                           required
                                           autocomplete="email">
                                    <span class="input-group-text">
                                        <i class="fas fa-user-shield"></i>
                                    </span>
                                </div>
                            </div>

                            <!-- Password Field -->
                            <div class="form-group">
                                <label for="password" class="form-label">Admin Password</label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control with-icon with-toggle @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Enter admin password"
                                           required
                                           autocomplete="current-password">
                                    <span class="input-group-text">
                                        <i class="fas fa-key"></i>
                                    </span>
                                    <button type="button" class="password-toggle" onclick="toggleAdminPassword()" title="Show/Hide Password">
                                        <i class="fas fa-eye-slash" id="adminPasswordToggleIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-admin-login" id="adminLoginBtn">
                                <span class="btn-content">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    <span id="btnText">Access Admin Panel</span>
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleAdminPassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('adminPasswordToggleIcon');
        
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
        const adminLoginForm = document.getElementById('adminLoginForm');
        const adminLoginBtn = document.getElementById('adminLoginBtn');
        const btnText = document.getElementById('btnText');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const rememberCheckbox = document.getElementById('remember');

        // Form submission handler
        if (adminLoginForm) {
            adminLoginForm.addEventListener('submit', function(e) {
                if (!emailInput.value.trim()) {
                    e.preventDefault();
                    emailInput.focus();
                    return;
                }

                if (!passwordInput.value.trim()) {
                    e.preventDefault();
                    passwordInput.focus();
                    return;
                }

                if (adminLoginBtn && btnText) {
                    adminLoginBtn.classList.add('loading');
                    btnText.textContent = 'Authenticating...';
                    
                    setTimeout(() => {
                        adminLoginBtn.classList.remove('loading');
                        btnText.textContent = 'Access Admin Panel';
                    }, 10000);
                }
            });
        }

        if (rememberCheckbox) {
            rememberCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    console.log('Admin remember me enabled for 30 days');
                } else {
                    console.log('Admin remember me disabled');
                }
            });
        }

        if (emailInput) {
            emailInput.addEventListener('blur', function() {
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (this.value && !emailPattern.test(this.value)) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });

            emailInput.addEventListener('input', function() {
                this.classList.remove('is-invalid');
            });
        }

        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                this.classList.remove('is-invalid');
            });
        }

        if (emailInput && !emailInput.value) {
            emailInput.focus();
        } else if (passwordInput && !passwordInput.value) {
            passwordInput.focus();
        }

        const passwordToggle = document.querySelector('.password-toggle');
        if (passwordToggle) {
            passwordToggle.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggleAdminPassword();
                }
            });
        }
    });

    window.addEventListener('load', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            if (alert.classList.contains('alert-success')) {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.3s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 5000);
            }
        });
    });
</script>
@endsection