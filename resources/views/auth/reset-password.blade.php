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

    .reset-container {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 3rem 0;
        position: relative;
        z-index: 10;
    }

    .reset-card {
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

    .reset-header {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 50%, #004085 100%);
        color: white;
        padding: 2.5rem 2rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .reset-header h2 {
        margin: 0;
        font-size: 2rem;
        font-weight: 700;
        position: relative;
        z-index: 1;
        text-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }

    .reset-header p {
        margin: 0.7rem 0 0 0;
        opacity: 0.95;
        font-weight: 400;
        font-size: 1.1rem;
        position: relative;
        z-index: 1;
    }

    .reset-header i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        opacity: 0.9;
        position: relative;
        z-index: 1;
    }

    .reset-body {
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

    .form-control.with-icon:focus {
        background: #ffffff;
        border-color: #007bff;
        box-shadow: 
            0 0 0 4px rgba(0, 123, 255, 0.2),
            0 10px 30px rgba(0, 0, 0, 0.1);
        color: #333333;
        outline: none;
    }

    .btn-reset {
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

    .btn-reset:hover {
        box-shadow: 
            0 15px 35px rgba(0, 123, 255, 0.4),
            0 8px 20px rgba(0, 0, 0, 0.3);
        transform: translateY(-2px);
    }

    .alert {
        background: rgba(248, 249, 250, 0.95);
        border: 1px solid #dee2e6;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        color: #495057;
        padding: 1rem;
    }

    .alert-danger {
        background: rgba(248, 215, 218, 0.8);
        border-color: #f5c6cb;
        color: #721c24;
    }

    .reset-links {
        text-align: center;
        margin-top: 2rem;
    }

    .reset-links a {
        color: #007bff;
        text-decoration: none;
        font-weight: 500;
    }
</style>

<div class="reset-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="reset-card">
                    <div class="reset-header">
                        <i class="fas fa-lock"></i>
                        <h2>Reset Password</h2>
                        <p>Enter your new password</p>
                    </div>
                    
                    <div class="reset-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0 list-unstyled">
                                    @foreach ($errors->all() as $error)
                                        <li><i class="fas fa-exclamation-circle me-2"></i>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.store') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $request->route('token') }}">

                            <div class="form-group">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <input type="email" 
                                           class="form-control with-icon" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', $request->email) }}" 
                                           required readonly>
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="password" class="form-label">New Password</label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control with-icon with-toggle" 
                                           id="password" 
                                           name="password" 
                                           required>
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <button type="button" class="password-toggle" onclick="togglePassword('password', 'passwordToggleIcon')">
                                        <i class="fas fa-eye-slash" id="passwordToggleIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control with-icon with-toggle" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           required>
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation', 'passwordConfirmToggleIcon')">
                                        <i class="fas fa-eye-slash" id="passwordConfirmToggleIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-reset">
                                <i class="fas fa-key me-2"></i>Reset Password
                            </button>
                        </form>

                        <div class="reset-links">
                            <p><a href="{{ route('login') }}"><i class="fas fa-arrow-left me-1"></i>Back to Sign In</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePassword(fieldId, iconId) {
        const passwordInput = document.getElementById(fieldId);
        const toggleIcon = document.getElementById(iconId);
        
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
</script>
@endsection