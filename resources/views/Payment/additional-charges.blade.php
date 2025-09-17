@extends('app')

@section('title', 'Additional Charges - RentWheels')

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
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <h4>Additional Charges Required</h4>
                <p class="mb-0">Your rental has incurred additional charges that require payment.</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>Pay Additional Charges
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Charges Breakdown -->
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 mb-3">Charge Details</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    @if($additionalPayment->payment_details['late_fees'] > 0)
                                    <tr>
                                        <td><i class="fas fa-clock text-danger me-2"></i>Late Return Fees</td>
                                        <td class="text-end"><strong>RM{{ number_format($additionalPayment->payment_details['late_fees'], 2) }}</strong></td>
                                    </tr>
                                    @endif
                                    @if($additionalPayment->payment_details['damage_charges'] > 0)
                                    <tr>
                                        <td><i class="fas fa-exclamation-triangle text-warning me-2"></i>Damage Charges</td>
                                        <td class="text-end"><strong>RM{{ number_format($additionalPayment->payment_details['damage_charges'], 2) }}</strong></td>
                                    </tr>
                                    @endif
                                    <tr class="border-top table-warning">
                                        <td><strong>Total Additional Charges</strong></td>
                                        <td class="text-end"><strong class="text-danger">RM{{ number_format($additionalPayment->amount, 2) }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <form id="additionalPaymentForm" method="POST" action="{{ route('payment.additional-charges.process', $booking->id) }}">
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
                                           placeholder="1234 5678 9012 3456" maxlength="19">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Card Holder Name <span class="text-danger">*</span></label>
                                    <input type="text" name="card_holder" class="form-control" 
                                           placeholder="John Doe" value="{{ auth()->user()->name }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Expiry Month <span class="text-danger">*</span></label>
                                    <select name="expiry_month" class="form-select">
                                        <option value="">Month</option>
                                        @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}">{{ sprintf('%02d', $i) }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Expiry Year <span class="text-danger">*</span></label>
                                    <select name="expiry_year" class="form-select">
                                        <option value="">Year</option>
                                        @for($i = 2025; $i <= 2035; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">CVV <span class="text-danger">*</span></label>
                                    <input type="password" name="cvv" class="form-control" placeholder="123" maxlength="4">
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

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-danger btn-lg w-100">
                                    <i class="fas fa-lock me-2"></i>
                                    Pay RM{{ number_format($additionalPayment->amount, 2) }} Now
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Booking Summary</h5>
                </div>
                <div class="card-body">
                    <h6>{{ $booking->vehicle->make }} {{ $booking->vehicle->model }}</h6>
                    <p class="text-muted">{{ $booking->booking_number }}</p>
                    
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Original Amount:</span>
                            <span>RM{{ number_format($booking->total_amount, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-danger">
                            <span>Additional Charges:</span>
                            <span>RM{{ number_format($additionalPayment->amount, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2">
                            <strong>Total Paid/Due:</strong>
                            <strong>RM{{ number_format($booking->total_amount + $additionalPayment->amount, 2) }}</strong>
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
});
</script>
@endsection