@extends('app')

@section('title', 'Processing Additional Payment - RentWheels')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-lg border-0">
                <div class="card-body text-center p-5">
                    <div id="processingSection">
                        <div class="processing-icon mb-4">
                            <div class="spinner-border text-warning" role="status" style="width: 4rem; height: 4rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <h3 class="mb-3">Processing Additional Charges</h3>
                        <p class="text-muted mb-4">Please wait while we process your additional charges payment...</p>
                        
                        <div class="progress mb-4" style="height: 10px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 0%" id="progressBar"></div>
                        </div>
                        
                        <div class="card bg-light border-0 mt-4">
                            <div class="card-body">
                                <div class="row text-sm">
                                    <div class="col-6">
                                        <small class="text-muted">Booking:</small><br>
                                        <strong>{{ $booking->booking_number }}</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Amount:</small><br>
                                        <strong class="text-danger">RM{{ number_format($additionalPayment->amount, 2) }}</strong>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <small class="text-muted">Type:</small><br>
                                        <strong>Additional Charges</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="successSection" class="d-none">
                        <div class="success-icon mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h3 class="text-success mb-3">Payment Successful!</h3>
                        <p class="text-muted mb-4">Your additional charges have been paid successfully.</p>
                        <div class="d-grid gap-2 mt-4">
                            <a href="{{ route('payment.additional-success', $booking->id) }}" class="btn btn-success btn-lg">
                                <i class="fas fa-receipt me-2"></i>View Receipt
                            </a>
                        </div>
                    </div>

                    <div id="failureSection" class="d-none">
                        <div class="failure-icon mb-4">
                            <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                        </div>
                        <h3 class="text-danger mb-3">Payment Failed</h3>
                        <p class="text-muted mb-4">We couldn't process your payment. Please try again.</p>
                        <div class="d-grid gap-2 mt-4">
                            <a href="{{ route('payment.additional-charges', $booking->id) }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-redo me-2"></i>Try Again
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const progressBar = document.getElementById('progressBar');
    const processingSection = document.getElementById('processingSection');
    const successSection = document.getElementById('successSection');
    const failureSection = document.getElementById('failureSection');
    
    let progress = 0;
    
    const progressInterval = setInterval(() => {
        progress += 10;
        progressBar.style.width = progress + '%';
        
        if (progress >= 100) {
            clearInterval(progressInterval);
            processPayment();
        }
    }, 200);
    
    function processPayment() {
        fetch(`{{ route('payment.additional-charges.complete', $booking->id) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                payment_method: '{{ $payment_data['payment_method'] ?? 'credit_card' }}'
            })
        })
        .then(response => response.json())
        .then(data => {
            setTimeout(() => {
                if (data.success) {
                    processingSection.classList.add('d-none');
                    successSection.classList.remove('d-none');
                    
                    setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 3000);
                } else {
                    processingSection.classList.add('d-none');
                    failureSection.classList.remove('d-none');
                }
            }, 1000);
        })
        .catch(error => {
            console.error('Payment error:', error);
            setTimeout(() => {
                processingSection.classList.add('d-none');
                failureSection.classList.remove('d-none');
            }, 1000);
        });
    }
});
</script>
@endsection