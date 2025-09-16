@extends('app')

@section('title', 'Payment - RentWheels')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('booking.show', $booking->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Booking Details
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="display-6 fw-bold text-primary">Complete Your Payment</h2>
            <p class="text-muted">Secure and easy payment for your vehicle rental</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>Payment Details
                    </h4>
                </div>
                <div class="card-body">
                    <form id="paymentForm" method="POST" action="{{ route('payment.process', $booking->id) }}">
                        @csrf
                        
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">Select Payment Method</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="form-check payment-method-card">
                                        <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="credit_card" checked>
                                        <label class="form-check-label w-100" for="credit_card">
                                            <div class="card h-100 text-center p-3">
                                                <i class="fas fa-credit-card text-primary mb-2" style="font-size: 2rem;"></i>
                                                <strong>Credit Card</strong>
                                                <small class="text-muted d-block">Visa, MasterCard, AMEX</small>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-check payment-method-card">
                                        <input class="form-check-input" type="radio" name="payment_method" id="debit_card" value="debit_card">
                                        <label class="form-check-label w-100" for="debit_card">
                                            <div class="card h-100 text-center p-3">
                                                <i class="fas fa-university text-success mb-2" style="font-size: 2rem;"></i>
                                                <strong>Debit Card</strong>
                                                <small class="text-muted d-block">Bank Debit Cards</small>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-check payment-method-card">
                                        <input class="form-check-input" type="radio" name="payment_method" id="online_banking" value="online_banking">
                                        <label class="form-check-label w-100" for="online_banking">
                                            <div class="card h-100 text-center p-3">
                                                <i class="fas fa-laptop text-warning mb-2" style="font-size: 2rem;"></i>
                                                <strong>Online Banking</strong>
                                                <small class="text-muted d-block">FPX, Internet Banking</small>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="cardDetails" class="payment-details">
                            <h5 class="border-bottom pb-2 mb-3">Card Information</h5>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">Card Number <span class="text-danger">*</span></label>
                                    <input type="text" name="card_number" class="form-control" 
                                           placeholder="1234 5678 9012 3456" maxlength="19" 
                                           pattern="[0-9\s]{13,19}" autocomplete="cc-number">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Card Holder Name <span class="text-danger">*</span></label>
                                    <input type="text" name="card_holder" class="form-control" 
                                           placeholder="John Doe" value="{{ auth()->user()->name }}" autocomplete="cc-name">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Expiry Month <span class="text-danger">*</span></label>
                                    <select name="expiry_month" class="form-select" autocomplete="cc-exp-month">
                                        <option value="">Month</option>
                                        @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}">{{ sprintf('%02d', $i) }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Expiry Year <span class="text-danger">*</span></label>
                                    <select name="expiry_year" class="form-select" autocomplete="cc-exp-year">
                                        <option value="">Year</option>
                                        @for($i = 2025; $i <= 2035; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">CVV <span class="text-danger">*</span></label>
                                    <input type="password" name="cvv" class="form-control" 
                                           placeholder="123" maxlength="4" autocomplete="cc-csc">
                                </div>
                            </div>
                        </div>

                        <div id="bankingDetails" class="payment-details d-none">
                            <h5 class="border-bottom pb-2 mb-3">Select Your Bank</h5>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <select name="bank_name" class="form-select">
                                        <option value="">Select Bank</option>
                                        <option value="maybank">Maybank</option>
                                        <option value="cimb">CIMB Bank</option>
                                        <option value="public_bank">Public Bank</option>
                                        <option value="rhb">RHB Bank</option>
                                        <option value="hong_leong">Hong Leong Bank</option>
                                        <option value="ambank">AmBank</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="payment_amount" value="{{ $booking->total_amount }}">

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-success btn-lg w-100" id="submitPayment">
                                    <i class="fas fa-lock me-2"></i>
                                    Pay RM{{ number_format($booking->total_amount, 2) }} Securely
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card bg-light border-0 mt-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <i class="fas fa-shield-alt text-success" style="font-size: 2rem;"></i>
                        </div>
                        <div class="col-md-10">
                            <h6 class="mb-1">Secure Payment</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm sticky-top" style="top: 2rem;">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        @if($booking->vehicle->image_url)
                        <img src="{{ $booking->vehicle->image_url }}" alt="{{ $booking->vehicle->make }}" 
                             class="rounded me-3" style="width: 60px; height: 50px; object-fit: cover;">
                        @else
                        <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                             style="width: 60px; height: 50px;">
                            <i class="fas fa-car text-muted"></i>
                        </div>
                        @endif
                        <div>
                            <h6 class="mb-0">{{ $booking->vehicle->make }} {{ $booking->vehicle->model }}</h6>
                            <small class="text-muted">{{ $booking->booking_number }}</small>
                        </div>
                    </div>

                    <div class="border-top pt-3 mb-3">
                        <div class="row text-sm">
                            <div class="col-6">
                                <small class="text-muted">Pickup Date:</small><br>
                                <strong>{{ $booking->pickup_datetime->format('M d, Y') }}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Return Date:</small><br>
                                <strong>{{ $booking->return_datetime->format('M d, Y') }}</strong>
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">Duration:</small><br>
                            <strong>{{ $booking->rental_days }} {{ Str::plural('day', $booking->rental_days) }}</strong>
                        </div>
                    </div>

                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Daily Rate:</span>
                            <span>RM{{ number_format($booking->vehicle->rentalRate->daily_rate, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Duration:</span>
                            <span>{{ $booking->rental_days }} {{ Str::plural('day', $booking->rental_days) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 border-top pt-2">
                            <strong>Total Amount:</strong>
                            <strong class="text-success">RM{{ number_format($booking->total_amount, 2) }}</strong>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.payment-method-card {
    cursor: pointer;
}

.payment-method-card input[type="radio"] {
    display: none;
}

.payment-method-card input[type="radio"]:checked + label .card {
    border-color: #007bff;
    background-color: #f8f9fa;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.payment-method-card:hover .card {
    border-color: #007bff;
}

.payment-details {
    transition: all 0.3s ease;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const cardDetails = document.getElementById('cardDetails');
    const bankingDetails = document.getElementById('bankingDetails');

    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            if (this.value === 'online_banking') {
                cardDetails.classList.add('d-none');
                bankingDetails.classList.remove('d-none');
            } else {
                cardDetails.classList.remove('d-none');
                bankingDetails.classList.add('d-none');
            }
        });
    });

    const cardNumberInput = document.querySelector('input[name="card_number"]');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
            let formattedInputValue = value.match(/.{1,4}/g)?.join(' ') || value;
            if (value.length <= 16) {
                this.value = formattedInputValue;
            }
        });
    }
});
</script>
@endsection