@extends('app')

@section('title', 'Billing Details - ' . $booking->booking_number)

@section('content')
<style>
    .billing-detail {
        padding: 2rem 0;
        background: #f8f9fa;
        min-height: 100vh;
    }
    
    .detail-header {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    
    .invoice-container {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    
    .invoice-header {
        border-bottom: 3px solid #007bff;
        padding-bottom: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .invoice-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }
    
    .detail-group {
        margin-bottom: 1.5rem;
    }
    
    .detail-group h6 {
        color: #6c757d;
        font-size: 0.875rem;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }
    
    .detail-group p {
        margin: 0.25rem 0;
        color: #333;
    }
    
    .charges-table {
        margin-top: 2rem;
        border-top: 2px solid #dee2e6;
        padding-top: 1rem;
    }
    
    .charges-row {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .charges-row.total {
        border-top: 2px solid #dee2e6;
        border-bottom: none;
        margin-top: 1rem;
        padding-top: 1rem;
        font-weight: bold;
        font-size: 1.1rem;
    }
    
    .charges-row.grand-total {
        background: #007bff;
        color: white;
        padding: 1rem;
        border-radius: 8px;
        margin-top: 1rem;
        font-size: 1.2rem;
    }
    
    .payment-history {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        margin-top: 2rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .status-completed {
        background: #d4edda;
        color: #155724;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }
    
    .outstanding-alert {
        background: #fff3cd;
        border: 1px solid #ffc107;
        color: #856404;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }
    
    @media print {
        .no-print {
            display: none;
        }
        
        .invoice-container {
            box-shadow: none;
            border: 1px solid #dee2e6;
        }
    }
</style>

<div class="billing-detail">
    <div class="container">
        <!-- Back Button -->
        <div class="mb-3 no-print">
            <a href="{{ route('billing.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to Billing
            </a>
        </div>
        
        <!-- Header -->
        <div class="detail-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h2>Billing Details</h2>
                    <p class="text-muted mb-0">Booking Reference: <strong>{{ $booking->booking_number }}</strong></p>
                    <p class="text-muted">Created: {{ $booking->created_at->format('d F Y') }}</p>
                </div>
                <div class="text-end">
                    <span class="status-badge status-{{ str_replace('_', '-', $booking->payment_status) }}">
                        Payment: {{ ucfirst(str_replace('_', ' ', $booking->payment_status)) }}
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Outstanding Alert -->
        @if($breakdown['outstanding'] > 0)
        <div class="outstanding-alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Outstanding Balance:</strong> RM {{ number_format($breakdown['outstanding'], 2) }}
            @if($booking->payment_status == 'additional_charges_pending')
                <a href="{{ route('payment.additional-charges', $booking->id) }}" class="btn btn-sm btn-warning ms-3">
                    Pay Now
                </a>
            @elseif($booking->payment_status == 'pending')
                <a href="{{ route('payment.form', $booking->id) }}" class="btn btn-sm btn-warning ms-3">
                    Pay Now
                </a>
            @endif
        </div>
        @endif
        
        <!-- Invoice -->
        <div class="invoice-container">
            <div class="invoice-header">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3>RentWheels Invoice</h3>
                        <p class="text-muted mb-0">Invoice #: {{ $invoice ? $invoice->invoice_number : 'Pending' }}</p>
                    </div>
                    <div class="text-end">
                        <img src="{{ asset('images/logo.png') }}" alt="RentWheels" style="height: 50px;">
                    </div>
                </div>
            </div>
            
            <div class="invoice-details">
                <div>
                    <div class="detail-group">
                        <h6>Bill To:</h6>
                        <p><strong>{{ $booking->customer_name }}</strong></p>
                        <p>{{ $booking->customer_email }}</p>
                        <p>{{ $booking->customer_phone }}</p>
                    </div>
                    
                    <div class="detail-group">
                        <h6>Vehicle Details:</h6>
                        <p><strong>{{ $booking->vehicle->make }} {{ $booking->vehicle->model }}</strong></p>
                        <p>License Plate: {{ $booking->vehicle->license_plate }}</p>
                        <p>Type: {{ $booking->vehicle->type }}</p>
                    </div>
                </div>
                
                <div>
                    <div class="detail-group">
                        <h6>Rental Period:</h6>
                        <p>Pickup: {{ $booking->pickup_datetime->format('d M Y, h:i A') }}</p>
                        <p>Return: {{ $booking->return_datetime->format('d M Y, h:i A') }}</p>
                        <p>Duration: {{ $booking->rental_days }} day(s)</p>
                    </div>
                    
                    <div class="detail-group">
                        <h6>Booking Status:</h6>
                        <p><strong>{{ ucfirst($booking->status) }}</strong></p>
                        @if($booking->actual_return_datetime)
                        <p>Actual Return: {{ $booking->actual_return_datetime->format('d M Y, h:i A') }}</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Charges Breakdown -->
            <div class="charges-table">
                <h5 class="mb-3">Charges Breakdown</h5>
                
                <div class="charges-row">
                    <span>Base Rental ({{ $booking->rental_days }} day(s) × RM {{ number_format($booking->vehicle->rentalRate->daily_rate, 2) }})</span>
                    <span>RM {{ number_format($booking->total_amount, 2) }}</span>
                </div>
                
                @if($booking->deposit_amount > 0)
                <div class="charges-row">
                    <span>Security Deposit</span>
                    <span>RM {{ number_format($booking->deposit_amount, 2) }}</span>
                </div>
                @endif
                
                @if($booking->damage_charges > 0)
                <div class="charges-row text-danger">
                    <span>Damage Charges</span>
                    <span>RM {{ number_format($booking->damage_charges, 2) }}</span>
                </div>
                @endif
                
                @if($booking->late_fees > 0)
                <div class="charges-row text-danger">
                    <span>Late Return Fees</span>
                    <span>RM {{ number_format($booking->late_fees, 2) }}</span>
                </div>
                @endif
                
                <div class="charges-row">
                    <span>Subtotal</span>
                    <span>RM {{ number_format($breakdown['subtotal'], 2) }}</span>
                </div>
                
                <div class="charges-row">
                    <span>SST (6%)</span>
                    <span>RM {{ number_format($breakdown['tax'], 2) }}</span>
                </div>
                
                <div class="charges-row grand-total">
                    <span>Grand Total</span>
                    <span>RM {{ number_format($breakdown['grand_total'], 2) }}</span>
                </div>
                
                @if($breakdown['paid_amount'] > 0)
                <div class="charges-row text-success">
                    <span>Amount Paid</span>
                    <span>- RM {{ number_format($breakdown['paid_amount'], 2) }}</span>
                </div>
                @endif
                
                @if($breakdown['outstanding'] > 0)
                <div class="charges-row total text-danger">
                    <span>Outstanding Balance</span>
                    <span>RM {{ number_format($breakdown['outstanding'], 2) }}</span>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Payment History -->
        @if($payments->count() > 0)
        <div class="payment-history">
            <h5 class="mb-3"><i class="fas fa-history me-2"></i>Payment History</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_reference }}</td>
                            <td><strong>RM {{ number_format($payment->amount, 2) }}</strong></td>
                            <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A')) }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}</td>
                            <td>{{ $payment->created_at->format('d M Y, h:i A') }}</td>
                            <td>
                                <span class="status-badge status-{{ $payment->status }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        
        <!-- Action Buttons -->
        <div class="action-buttons no-print">
            <button onclick="window.print()" class="btn btn-outline-primary">
                <i class="fas fa-print me-2"></i> Print Invoice
            </button>
            
            <a href="{{ route('booking.show', $booking->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-car me-2"></i> View Booking
            </a>
            
            @if($breakdown['outstanding'] > 0)
                @if($booking->payment_status == 'additional_charges_pending')
                    <a href="{{ route('payment.additional-charges', $booking->id) }}" class="btn btn-warning">
                        <i class="fas fa-credit-card me-2"></i> Pay Additional Charges
                    </a>
                @elseif($booking->payment_status == 'pending')
                    <a href="{{ route('payment.form', $booking->id) }}" class="btn btn-warning">
                        <i class="fas fa-credit-card me-2"></i> Pay Now
                    </a>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection