@extends('app')

@section('title', 'Processing Payment - RentWheels')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-lg border-0">
                <div class="card-body text-center p-5">
                    <div id="processingSection">
                        <div class="processing-icon mb-4">
                            <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <h3 class="mb-3">Processing Your Payment</h3>
                        <p class="text-muted mb-4">Please wait while we process your payment securely...</p>
                        
                        <div class="progress mb-4" style="height: 10px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 0%" id="progressBar"></div>
                        </div>
                        
                        <div id="statusMessage" class="mb-3">
                            <small class="text-muted">Connecting to payment gateway...</small>
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
                                        <strong>RM{{ number_format($booking->total_amount, 2) }}</strong>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <small class="text-muted">Vehicle:</small><br>
                                        <strong>{{ $booking->vehicle->make }} {{ $booking->vehicle->model }}</strong>
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
                        <p class="text-muted mb-4">Your booking has been confirmed and payment processed successfully.</p>
                        <div class="card bg-success bg-opacity-10 border-success">
                            <div class="card-body">
                                <h6 class="text-success mb-2">Transaction Details</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">Transaction ID:</small><br>
                                        <strong id="transactionId">-</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Status:</small><br>
                                        <span class="badge bg-success">Confirmed</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-grid gap-2 mt-4">
                            <a href="{{ route('payment.success', $booking->id) }}" class="btn btn-success btn-lg">
                                <i class="fas fa-receipt me-2"></i>View Receipt
                            </a>
                            <a href="{{ route('booking.index') }}" class="btn btn-outline-primary">
                                <i class="fas fa-list me-2"></i>My Bookings
                            </a>
                        </div>
                    </div>

                    <div id="failureSection" class="d-none">
                        <div class="failure-icon mb-4">
                            <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                        </div>
                        <h3 class="text-danger mb-3">Payment Failed</h3>
                        <p class="text-muted mb-4" id="errorMessage">We couldn't process your payment. Please try again.</p>
                        <div class="card bg-danger bg-opacity-10 border-danger">
                            <div class="card-body">
                                <h6 class="text-danger mb-2">Error Details</h6>
                                <p class="mb-0" id="errorCode">Error Code: PAYMENT_DECLINED</p>
                            </div>
                        </div>
                        <div class="d-grid gap-2 mt-4">
                            <a href="{{ route('payment.form', $booking->id) }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-redo me-2"></i>Try Again
                            </a>
                            <a href="{{ route('booking.show', $booking->id) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Booking
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="navigationWarning" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>Payment in Progress
                </h5>
            </div>
            <div class="modal-body">
                <p>Your payment is currently being processed. Please don't close or refresh this page.</p>
                <p class="text-muted mb-0">Closing this page may result in payment issues.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Stay on Page</button>
            </div>
        </div>
    </div>
</div>

<style>
.processing-icon {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.success-icon {
    animation: bounceIn 0.8s ease-out;
}

@keyframes bounceIn {
    0% { transform: scale(0); opacity: 0; }
    50% { transform: scale(1.2); opacity: 1; }
    100% { transform: scale(1); }
}

.failure-icon {
    animation: shakeX 0.8s ease-out;
}

@keyframes shakeX {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const progressBar = document.getElementById('progressBar');
    const statusMessage = document.getElementById('statusMessage');
    const processingSection = document.getElementById('processingSection');
    const successSection = document.getElementById('successSection');
    const failureSection = document.getElementById('failureSection');
    
    let progress = 0;
    let stage = 0;
    
    const stages = [
        { progress: 20, message: 'Connecting to payment gateway...', duration: 1000 },
        { progress: 40, message: 'Validating payment information...', duration: 1500 },
        { progress: 60, message: 'Processing with bank...', duration: 2000 },
        { progress: 80, message: 'Confirming transaction...', duration: 1000 },
        { progress: 90, message: 'Finalizing payment...', duration: 500 },
        { progress: 100, message: 'Payment completed!', duration: 500 }
    ];
    
    function updateProgress() {
        if (stage < stages.length) {
            const currentStage = stages[stage];
            
            // Update progress bar
            const progressInterval = setInterval(() => {
                progress += 2;
                progressBar.style.width = Math.min(progress, currentStage.progress) + '%';
                
                if (progress >= currentStage.progress) {
                    clearInterval(progressInterval);
                    statusMessage.innerHTML = `<small class="text-muted">${currentStage.message}</small>`;
                    
                    setTimeout(() => {
                        stage++;
                        updateProgress();
                    }, currentStage.duration);
                }
            }, 50);
        } else {
            processPayment();
        }
    }
    
    function processPayment() {
        fetch(`{{ route('payment.complete', $booking->id) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                amount: {{ $booking->total_amount }},
                payment_method: '{{ $payment_data['payment_method'] ?? 'credit_card' }}'
            })
        })
        .then(response => response.json())
        .then(data => {
            setTimeout(() => {
                if (data.success) {
                    document.getElementById('transactionId').textContent = data.transaction_id;
                    processingSection.classList.add('d-none');
                    successSection.classList.remove('d-none');
                    
                    setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 5000);
                } else {
                    document.getElementById('errorMessage').textContent = data.message;
                    document.getElementById('errorCode').textContent = 'Error Code: ' + (data.error_code || 'UNKNOWN_ERROR');
                    processingSection.classList.add('d-none');
                    failureSection.classList.remove('d-none');
                }
            }, 1000);
        })
        .catch(error => {
            console.error('Payment error:', error);
            setTimeout(() => {
                document.getElementById('errorMessage').textContent = 'A network error occurred. Please try again.';
                processingSection.classList.add('d-none');
                failureSection.classList.remove('d-none');
            }, 1000);
        });
    }
    
    let isProcessing = true;
    
    window.addEventListener('beforeunload', function(e) {
        if (isProcessing && !successSection.classList.contains('d-none') === false) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
    
    successSection.addEventListener('DOMSubtreeModified', function() {
        isProcessing = false;
    });
    
    failureSection.addEventListener('DOMSubtreeModified', function() {
        isProcessing = false;
    });
    
    setTimeout(updateProgress, 500);
});
</script>
@endsection
