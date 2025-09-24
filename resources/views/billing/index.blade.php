@extends('app')

@section('title', 'My Billing Dashboard - RentWheels')

@section('content')
<style>
    .billing-dashboard {
        padding: 2rem 0;
        background: #f8f9fa;
        min-height: 100vh;
    }

    .dashboard-header {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        padding: 2rem;
        border-radius: 15px;
        margin-bottom: 2rem;
    }

    .summary-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .summary-card {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        border-left: 4px solid #007bff;
    }

    .summary-card h6 {
        color: #6c757d;
        font-size: 0.875rem;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }

    .summary-card .amount {
        font-size: 1.8rem;
        font-weight: bold;
        color: #333;
    }

    .summary-card.pending {
        border-left-color: #ffc107;
    }

    .summary-card.charges {
        border-left-color: #dc3545;
    }

    .summary-card.success {
        border-left-color: #28a745;
    }

    .billing-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    }

    .table-header {
        background: #f8f9fa;
        padding: 1rem 1.5rem;
        border-bottom: 2px solid #dee2e6;
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .status-paid {
        background: #d4edda;
        color: #155724;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-cancelled {
        background: #f8d7da;
        color: #721c24;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.875rem;
    }

    .alert-outstanding {
        background: #fff3cd;
        border: 1px solid #ffc107;
        color: #856404;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    
    .filter-section {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    }
</style>

<div class="billing-dashboard">
    <div class="container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h2 class="mb-0">My Billing Dashboard</h2>
            <p class="mb-0 mt-2">Manage your payments, view invoices, and track rental charges</p>
        </div>

        <!-- Outstanding Bills Alert -->
        @php
            $pendingCount = 0;
            $pendingTotal = 0;
            foreach($bookings as $booking) {
                if($booking->payment_status == 'pending' || $booking->payment_status == 'partial') {
                    $pendingCount++;
                    $pendingTotal += $booking->total_amount;
                }
                if($booking->damage_charges > 0 || $booking->late_fees > 0) {
                    $pendingCount++;
                    $pendingTotal += ($booking->damage_charges + $booking->late_fees);
                }
            }
        @endphp
        
        @if($pendingCount > 0)
        <div class="alert-outstanding">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>You have {{ $pendingCount }} outstanding bill(s)</strong> totaling
            <strong>RM {{ number_format($pendingTotal, 2) }}</strong>
        </div>
        @endif

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card success">
                <h6><i class="fas fa-check-circle me-1"></i> Total Paid</h6>
                <div class="amount">RM {{ number_format($summary['total_paid'], 2) }}</div>
            </div>

            <div class="summary-card pending">
                <h6><i class="fas fa-clock me-1"></i> Pending Payments</h6>
                <div class="amount">RM {{ number_format($summary['pending_payment'], 2) }}</div>
            </div>

            <div class="summary-card charges">
                <h6><i class="fas fa-plus-circle me-1"></i> Additional Charges</h6>
                <div class="amount">RM {{ number_format($summary['additional_charges'], 2) }}</div>
            </div>

            <div class="summary-card">
                <h6><i class="fas fa-car me-1"></i> Total Bookings</h6>
                <div class="amount">{{ $summary['total_bookings'] }}</div>
                <small class="text-muted">{{ $summary['active_bookings'] }} active</small>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Filter Billing Records</h5>
            <form method="GET" action="{{ route('billing.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status_filter" class="form-select">
                        <option value="">All Status</option>
                        <option value="paid" {{ request('status_filter') == 'paid' ? 'selected' : '' }}>Fully Paid</option>
                        <option value="pending" {{ request('status_filter') == 'pending' ? 'selected' : '' }}>Pending Payment</option>
                        <option value="has_additional" {{ request('status_filter') == 'has_additional' ? 'selected' : '' }}>Has Additional Charges</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>Apply
                        </button>
                        <a href="{{ route('billing.index') }}" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Recent Payments Section -->
        @if($recentPayments->count() > 0)
        <div class="billing-table mb-4">
            <div class="table-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Payments</h5>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Payment Ref</th>
                            <th>Booking</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentPayments as $booking)
                        <tr>
                            <td>{{ $booking->booking_number }}</td>
                            <td>{{ $booking->booking_number }}</td>
                            <td><strong>RM {{ number_format($booking->total_amount, 2) }}</strong></td>
                            <td>{{ ucfirst($booking->payment_status) }}</td>
                            <td>{{ $booking->created_at->format('d M Y') }}</td>
                            <td>
                                <span class="status-badge status-paid">Paid</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- All Bookings with Billing -->
        <div class="billing-table">
            <div class="table-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Billing History</h5>
                <div>
                    <a href="{{ route('billing.export') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-download me-1"></i> Export PDF
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Booking #</th>
                            <th>Vehicle</th>
                            <th>Period</th>
                            <th>Total Amount</th>
                            <th>Additional</th>
                            <th>Payment Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                        @php
                            $hasUnpaidAdditional = ($booking->damage_charges > 0 || $booking->late_fees > 0);
                            $additionalTotal = $booking->damage_charges + $booking->late_fees;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $booking->booking_number }}</strong>
                                <br>
                                <small class="text-muted">{{ $booking->created_at->format('d M Y') }}</small>
                            </td>
                            <td>
                                {{ $booking->vehicle->make }} {{ $booking->vehicle->model }}
                                <br>
                                <small class="text-muted">{{ $booking->vehicle->license_plate }}</small>
                            </td>
                            <td>
                                {{ $booking->pickup_datetime->format('d/m/Y') }}
                                <br>
                                to {{ $booking->return_datetime->format('d/m/Y') }}
                            </td>
                            <td>
                                <strong>RM {{ number_format($booking->total_amount, 2) }}</strong>
                            </td>
                            <td>
                                @if($hasUnpaidAdditional)
                                <span class="text-danger">
                                    <strong>RM {{ number_format($additionalTotal, 2) }}</strong>
                                </span>
                                @if($booking->damage_charges > 0)
                                    <br><small>Damage: RM {{ number_format($booking->damage_charges, 2) }}</small>
                                @endif
                                @if($booking->late_fees > 0)
                                    <br><small>Late: RM {{ number_format($booking->late_fees, 2) }}</small>
                                @endif
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($hasUnpaidAdditional)
                                    <span class="status-badge status-pending">Pending Additional</span>
                                @elseif($booking->payment_status == 'pending')
                                    <span class="status-badge status-pending">Pending</span>
                                @elseif($booking->payment_status == 'partial')
                                    <span class="status-badge status-pending">Partial</span>
                                @else
                                    <span class="status-badge status-paid">Paid</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('billing.show', $booking->id) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>

                                    @if($hasUnpaidAdditional)
                                    <form method="POST" action="{{ route('billing.pay-additional', $booking->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-credit-card"></i> Pay Charges
                                        </button>
                                    </form>
                                    @elseif($booking->payment_status == 'pending')
                                    <a href="{{ route('payment.form', $booking->id) }}"
                                        class="btn btn-sm btn-warning">
                                        <i class="fas fa-credit-card"></i> Pay
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No billing records found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($bookings->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $bookings->withQueryString()->links() }}
            </div>
            @endif
        </div>

        <!-- Help Section -->
        <div class="mt-4 text-center">
            <p class="text-muted">
                <i class="fas fa-question-circle me-1"></i>
                Need help with billing? Contact us at
                <a href="mailto:billing@rentwheels.com">billing@rentwheels.com</a>
                or call +60 3-1234 5680
            </p>
        </div>
    </div>
</div>
@endsection