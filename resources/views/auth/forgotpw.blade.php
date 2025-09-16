@extends('app')

@section('title', 'Reset Password - RentWheels')

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

    .forgot-container {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 3rem 0;
        position: relative;
        z-index: 10;
    }

    .forgot-card {
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

    .forgot-header {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 50%, #004085 100%);
        color: white;
        padding: 2.5rem 2rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .forgot-header h2 {
        margin: 0;
        font-size: 2rem;
        font-weight: 700;
        position: relative;
        z-index: 1;
        text-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }

    .forgot-header p {
        margin: 0.7rem 0 0 0;
        opacity: 0.95;
        font-weight: 400;
        font-size: 1.1rem;
        position: relative;
        z-index: 1;
    }

    .forgot-header i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        opacity: 0.9;
        position: relative;
        z-index: 1;
    }

    .forgot-body {
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

    .form-control.with-icon {
        background: rgba(248, 249, 250, 0.8);
        border: 2px solid #e9ecef;
        border-radius: 15px;
        padding: 1rem 1rem 1rem 3.5rem;
        font-size: 1.05rem;
        color: #333333;
        transition: all 0.3s ease;
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

    .btn-forgot {
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

    .btn-forgot:hover {
        box-shadow: 
            0 15px 35px rgba(0, 123, 255, 0.4),
            0 8px 20px rgba(0, 0, 0, 0.3);
        transform: translateY(-2px);
    }

    .forgot-links {
        text-align: center;
        margin-top: 2rem;
    }

    .forgot-links p {
        margin-bottom: 0.8rem;
        color: #495057;
    }

    .forgot-links a {
        color: #007bff;
        text-decoration: none;
        font-weight: 500;
        position: relative;
        padding: 0.3rem 0;
        transition: all 0.3s ease;
    }

    .forgot-links a::after {
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

    .forgot-links a:hover::after {
        width: 100%;
    }

    .forgot-links a:hover {
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

    .alert-info {
        background: rgba(209, 236, 241, 0.8);
        border-color: #b6d4da;
        color: #0c5460;
    }

    .info-box {
        background: rgba(209, 236, 241, 0.3);
        border: 1px solid rgba(91, 192, 222, 0.3);
        border-radius: 12px;
        padding: 1.2rem;
        margin-bottom: 2rem;
        text-align: center;
    }

    .info-box i {
        color: #0c5460;
        font-size: 1.2rem;
        margin-bottom: 0.5rem;
    }

    .info-box p {
        color: #0c5460;
        margin: 0;
        font-size: 0.95rem;
        line-height: 1.5;
    }

    @media (max-width: 768px) {
        .forgot-container {
            padding: 2rem 1rem;
        }
        
        .forgot-body {
            padding: 2rem 1.5rem;
        }
        
        .forgot-header {
            padding: 2rem 1.5rem;
        }

        .forgot-header h2 {
            font-size: 1.7rem;
        }

        .forgot-card {
            max-width: 100%;
        }
    }

    .btn-forgot.loading {
        pointer-events: none;
        opacity: 0.7;
    }

    .btn-forgot.loading::after {
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

<!-- Forgot Password Section -->
<div class="forgot-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="forgot-card">
                    
                    <div class="forgot-header">
                        <i class="fas fa-key"></i>
                        <h2>Reset Password</h2>
                        <p>We'll send you a reset link</p>
                    </div>
                    
                    <div class="forgot-body">
                        @if (session('status'))
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0 list-unstyled">
                                    @foreach ($errors->all() as $error)
                                        <li><i class="fas fa-exclamation-circle me-2"></i>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="info-box">
                            <i class="fas fa-info-circle"></i>
                            <p>Enter your email address and we'll send you a link to reset your password. The link will be valid for 60 minutes.</p>
                        </div>

                        <form method="POST" action="{{ route('password.email') }}" id="forgotForm">
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
                                @error('email')
                                    <div class="text-danger mt-2">
                                        <small><i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}</small>
                                    </div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary btn-forgot" id="forgotBtn">
                                <i class="fas fa-paper-plane me-2"></i>
                                <span id="btnText">Send Reset Link</span>
                            </button>
                        </form>

                        <div class="divider">
                            <span>or</span>
                        </div>

                        <div class="forgot-links">
                            <p>Remember your password? <a href="{{ route('login') }}"><i class="fas fa-arrow-left me-1"></i>Back to Sign In</a></p>
                            <p>Don't have an account? <a href="{{ route('register') }}"><i class="fas fa-user-plus me-1"></i>Create one here</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const forgotForm = document.getElementById('forgotForm');
        if (forgotForm) {
            forgotForm.addEventListener('submit', function(e) {
                const btn = document.getElementById('forgotBtn');
                const btnText = document.getElementById('btnText');
                
                if (btn && btnText) {
                    btn.classList.add('loading');
                    btnText.textContent = 'Sending...';
                    
                    setTimeout(() => {
                        btn.classList.remove('loading');
                        btnText.textContent = 'Send Reset Link';
                    }, 10000);
                }
            });
        }

        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.opacity = '0';
                setTimeout(() => {
                    successAlert.remove();
                }, 300);
            }, 10000);
        }
    });
</script>
@endsection