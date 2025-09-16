@extends('app')

@section('title', 'Payment Failed - RentWheels')

@section('content')
<div class="container py-5">
    <div class="row mb-5">
        <div class="col-12 text-center">
            <div class="failure-icon mb-3">
                <i class="fas fa-times-circle text-danger" style="font-size: 5rem;"></i>
            </div>
            <h1 class="display-5 fw-bold text-danger">Payment Failed</h1>
            <p class="lead text-muted">We were unable to process your payment. Please try again.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Error Details -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Payment Error
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <h5 class="alert-heading">Transaction Declined</h5>
                        <p class="mb-0">Your payment could not be processed at this time. This may be due to various reasons such as insufficient funds, card restrictions, or network issues.</p>
                    </div>

                    <!-- Booking Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Booking Reference</h6>
                            <p class="fw-bold">{{ $booking->booking_number }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Amount</h6>
                            <p class="fw-bold text-danger">RM{{ number_format($booking->total_amount, 2) }}</p>
                        </div>
                    </div>

                    <!-- Vehicle Summary -->
                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body">
                            <h6 class="mb-3">Booking Summary</h6>
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    @if($booking->vehicle->image_url)
                                    <img src="{{ $booking->vehicle->image_url }}" 
                                         alt="{{ $booking->vehicle->make }} {{ $booking->vehicle->model }}" 
                                         class="img-fluid rounded">
                                    @else
                                    <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="height: 100px;">
                                        <i class="fas fa-car text-white" style="font-size: 2rem;"></i>
                                    </div>
                                    @endif
                                </div>
                                <div class="col-md-8">
                                    <h5 class="fw-bold">{{ $booking->vehicle->make }} {{ $booking->vehicle->model }}</h5>
                                    <p class="text-muted mb-1">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ $booking->pickup_datetime->format('M d, Y') }} - {{ $booking->return_datetime->format('M d, Y') }}
                                    </p>
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $booking->rental_days }} {{ Str::plural('day', $booking->rental_days) }} rental
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Common Reasons -->
                    <div class="mb-4">
                        <h5 class="mb-3">Common Reasons for Payment Failure</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="fas fa-times text-danger me-2"></i>
                                        Insufficient funds in account
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-times text-danger me-2"></i>
                                        Incorrect card details
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-times text-danger me-2"></i>
                                        Card expired or blocked
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="fas fa-times text-danger me-2"></i>
                                        Daily transaction limit exceeded
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-times text-danger me-2"></i>
                                        Bank server temporarily down
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-times text-danger me-2"></i>
                                        Network connectivity issues
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- What to do next -->
                    <div class="bg-info bg-opacity-10 p-3 rounded">
                        <h6 class="text-info mb-2">
                            <i class="fas fa-lightbulb me-2"></i>What you can do:
                        </h6>
                        <ol class="mb-0">
                            <li>Verify your card details and try again</li>
                            <li>Try using a different payment method</li>
                            <li>Contact your bank to ensure your card is active</li>
                            <li>Check your account balance and transaction limits</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-grid">
                                <a href="{{ route('payment.form', $booking->id) }}" class="btn btn-primary btn-lg">
                                    <i class="fas fa-redo me-2"></i>Try Payment Again
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-grid">
                                <a href="{{ route('booking.show', $booking->id) }}" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Booking
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-grid">
                                <a href="tel:+60123456789" class="btn btn-success">
                                    <i class="fas fa-phone me-2"></i>Call Support
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-grid">
                                <a href="mailto:support@rentwheels.com?subject=Payment Failed - {{ $booking->booking_number }}" 
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-envelope me-2"></i>Email Support
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alternative Payment Methods -->
            <div class="card mt-4 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>Alternative Payment Options
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-3">If you continue to experience payment issues, you can:</p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-university text-primary mb-2" style="font-size: 2rem;"></i>
                                    <h6>Bank Transfer</h6>
                                    <p class="small text-muted mb-0">Contact us for bank transfer details</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-handshake text-success mb-2" style="font-size: 2rem;"></i>
                                    <h6>Pay at Pickup</h6>
                                    <p class="small text-muted mb-0">Pay when you collect the vehicle</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Important Notice -->
            <div class="alert alert-warning mt-4">
                <h6>
                    <i class="fas fa-exclamation-triangle me-2"></i>Important Notice
                </h6>
                <p class="mb-0">
                    Your booking ({{ $booking->booking_number }}) is still reserved for <strong>24 hours</strong>. 
                    Please complete payment within this time to secure your reservation. After 24 hours, 
                    the booking may be automatically cancelled.
                </p>
            </div>

            <!-- Support Information -->
            <div class="text-center mt-4">
                <h6>Need Immediate Help?</h6>
                <p class="text-muted">Our customer support team is available 24/7</p>
                <div class="row justify-content-center">
                    <div class="col-md-3 text-center">
                        <strong>Phone</strong><br>
                        <a href="tel:+60123456789" class="text-decoration-none">+60 12-345 6789</a>
                    </div>
                    <div class="col-md-3 text-center">
                        <strong>Email</strong><br>
                        <a href="mailto:support@rentwheels.com" class="text-decoration-none">support@rentwheels.com</a>
                    </div>
                    <div class="col-md-3 text-center">
                        <strong>WhatsApp</strong><br>
                        <a href="https://wa.me/60123456789" class="text-decoration-none" target="_blank">+60 12-345 6789</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.failure-icon {
    animation: shakeX 0.8s ease-out;
}

@keyframes shakeX {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
}

.card {
    transition: transform 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
}
</style>
@endsection
