@extends('app')

@section('title', 'Additional Payment Successful - RentWheels')

@section('content')
<div class="container py-5">
    <div class="row mb-5">
        <div class="col-12 text-center">
            <div class="success-icon mb-3">
                <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
            </div>
            <h1 class="display-5 fw-bold text-success">Additional Payment Successful!</h1>
            <p class="lead text-muted">Your additional charges have been paid and your rental is now fully settled.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-receipt me-2"></i>Additional Charges Receipt
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Transaction Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Transaction ID</h6>
                            <p class="fw-bold">{{ $additionalPayment->payment_reference ?? 'TXN'.time().rand(1000,9999) }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Payment Date</h6>
                            <p class="fw-bold">{{ now()->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>

                    <!-- Booking Information -->
                    <div class="border-bottom pb-3 mb-3">
                        <h5 class="mb-3">Booking Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">Booking Reference</h6>
                                <p class="fw-bold">{{ $booking->booking_number }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Vehicle</h6>
                                <p class="fw-bold">{{ $booking->vehicle->make }} {{ $booking->vehicle->model }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Charges Breakdown -->
                    <div class="border-bottom pb-3 mb-3">
                        <h5 class="mb-3">Additional Charges</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    @if($additionalPayment->payment_details['late_fees'] > 0)
                                    <tr>
                                        <td><i class="fas fa-clock text-danger me-2"></i>Late Return Fees</td>
                                        <td class="text-end">RM{{ number_format($additionalPayment->payment_details['late_fees'], 2) }}</td>
                                    </tr>
                                    @endif
                                    @if($additionalPayment->payment_details['damage_charges'] > 0)
                                    <tr>
                                        <td><i class="fas fa-exclamation-triangle text-warning me-2"></i>Damage Charges</td>
                                        <td class="text-end">RM{{ number_format($additionalPayment->payment_details['damage_charges'], 2) }}</td>
                                    </tr>
                                    @endif
                                    <tr class="border-top table-success">
                                        <td><strong>Total Additional Charges Paid</strong></td>
                                        <td class="text-end"><strong class="text-success">RM{{ number_format($additionalPayment->amount, 2) }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="bg-light p-3 rounded">
                        <h6 class="mb-3">Complete Payment Summary</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted">Original Booking:</small><br>
                                <strong>RM{{ number_format($booking->total_amount, 2) }}</strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Additional Charges:</small><br>
                                <strong>RM{{ number_format($additionalPayment->amount, 2) }}</strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Total Paid:</small><br>
                                <strong class="text-success">RM{{ number_format($booking->total_amount + $additionalPayment->amount, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('booking.show', $booking->id) }}" class="btn btn-primary">
                            <i class="fas fa-eye me-2"></i>View Complete Booking Details
                        </a>
                        <a href="{{ route('booking.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-list me-2"></i>My Bookings
                        </a>
                        <button class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Print Receipt
                        </button>
                    </div>
                </div>
            </div>

            <!-- Important Notice -->
            <div class="alert alert-success mt-4">
                <h5><i class="fas fa-check-circle me-2"></i>Payment Complete</h5>
                <p class="mb-0">
                    Your rental is now fully settled. All charges including additional fees have been paid.
                    Thank you for choosing RentWheels!
                </p>
            </div>
        </div>
    </div>
</div>
@endsection