@extends('admin')

@section('title', 'Billing Management - Admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Billing Management</h1>
            <p class="text-muted">Manage customer billing, additional charges, and outstanding payments</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.billing.outstanding-report') }}" class="btn btn-primary">
                <i class="fas fa-chart-line me-2"></i>Outstanding Report
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title">${{ number_format($totalOutstanding, 2) }}</h4>
                            <p class="card-text">Total Outstanding</p>
                        </div>
                        <div class="text-end">
                            <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title">{{ $pendingCharges }}</h4>
                            <p class="card-text">Pending Charges</p>
                        </div>
                        <div class="text-end">
                            <i class="fas fa-clock fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title">{{ $outstandingBookings->total() }}</h4>
                            <p class="card-text">Outstanding Bills</p>
                        </div>
                        <div class="text-end">
                            <i class="fas fa-file-invoice fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.billing.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Customer name, email, or booking number" 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Payment Status</label>
                        <select name="payment_status" class="form-select">
                            <option value="">All Payments</option>
                            <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.billing.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Outstanding Bookings Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Outstanding Bills & Charges</h5>
        </div>
        <div class="card-body p-0">
            @if($outstandingBookings->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Booking</th>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Rental Period</th>
                            <th>Base Amount</th>
                            <th>Additional Charges</th>
                            <th>Payment Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($outstandingBookings as $booking)
                        @php
                            $additionalCharges = ($booking->damage_charges ?? 0) + ($booking->late_fees ?? 0);
                            $totalOutstanding = $additionalCharges;
                            
                            // Add remaining balance if payment is partial or pending
                            if ($booking->payment_status === 'partial' || $booking->payment_status === 'pending') {
                                $remaining = $booking->total_amount - ($booking->deposit_amount ?? 0);
                                if ($remaining > 0) {
                                    $totalOutstanding += $remaining;
                                }
                            }
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $booking->booking_number }}</strong><br>
                                <small class="text-muted">{{ $booking->created_at->format('M d, Y') }}</small>
                            </td>
                            <td>
                                <strong>{{ $booking->user->name ?? $booking->customer_name }}</strong><br>
                                <small class="text-muted">{{ $booking->user->email ?? $booking->customer_email }}</small>
                            </td>
                            <td>
                                <strong>{{ $booking->vehicle->make }} {{ $booking->vehicle->model }}</strong><br>
                                <small class="text-muted">{{ $booking->vehicle->year }} {{ $booking->vehicle->type }}</small>
                            </td>
                            <td>
                                <small>
                                    {{ $booking->pickup_datetime->format('M d') }} - 
                                    {{ $booking->return_datetime->format('M d, Y') }}
                                </small>
                            </td>
                            <td>
                                <strong>${{ number_format($booking->total_amount, 2) }}</strong>
                                @if($booking->payment_status === 'partial')
                                <br><small class="text-success">Paid: ${{ number_format($booking->deposit_amount, 2) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($additionalCharges > 0)
                                <span class="text-danger">
                                    <strong>${{ number_format($additionalCharges, 2) }}</strong>
                                </span>
                                @if($booking->damage_charges > 0)
                                <br><small class="text-muted">Damage: ${{ number_format($booking->damage_charges, 2) }}</small>
                                @endif
                                @if($booking->late_fees > 0)
                                <br><small class="text-muted">Late: ${{ number_format($booking->late_fees, 2) }}</small>
                                @endif
                                @else
                                <span class="text-warning">Not Set</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'partial' => 'info',
                                        'paid' => 'success',
                                        'refunded' => 'secondary'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$booking->payment_status] ?? 'secondary' }}">
                                    {{ ucfirst($booking->payment_status) }}
                                </span>
                                <br>
                                <small class="text-muted">{{ ucfirst($booking->status) }}</small>
                            </td>
                            <td>
                                <div class="btn-group-vertical btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary btn-sm" 
                                            onclick="setCharges({{ $booking->id }})">
                                        <i class="fas fa-dollar-sign"></i> Set Charges
                                    </button>
                                    @if($additionalCharges > 0)
                                    <button type="button" class="btn btn-outline-warning btn-sm" 
                                            onclick="waiveCharges({{ $booking->id }})">
                                        <i class="fas fa-hand-holding-usd"></i> Waive
                                    </button>
                                    @endif
                                    <a href="{{ route('admin.bookings.show', $booking->id) }}" 
                                       class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer">
                {{ $outstandingBookings->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                <h4>No Outstanding Bills</h4>
                <p class="text-muted">All customer payments are up to date!</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Set Charges Modal -->
<div class="modal fade" id="setChargesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Set Additional Charges</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="setChargesForm">
                @csrf
                <div class="modal-body">
                    <div id="bookingInfo" class="alert alert-info mb-4">
                        <!-- Booking details will be loaded here -->
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Damage Charges ($)</label>
                            <input type="number" name="damage_charges" class="form-control" 
                                   min="0" max="10000" step="0.01" placeholder="0.00">
                            <small class="form-text text-muted">Charges for vehicle damage</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Late Return Fees ($)</label>
                            <input type="number" name="late_fees" class="form-control" 
                                   min="0" max="5000" step="0.01" placeholder="0.00">
                            <small class="form-text text-muted">Fees for late return</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason for Charges <span class="text-danger">*</span></label>
                        <textarea name="charge_reason" class="form-control" rows="3" required
                                  placeholder="Describe the reason for applying these charges..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Additional Notes</label>
                        <textarea name="charge_notes" class="form-control" rows="2"
                                  placeholder="Any additional notes or details..."></textarea>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Note:</strong> Additional charges will be added to the customer's bill and they will be notified.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Charges</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Waive Charges Modal -->
<div class="modal fade" id="waiveChargesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Waive Charges</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="waiveChargesForm">
                @csrf
                <div class="modal-body">
                    <div id="waiveBookingInfo" class="alert alert-info mb-4">
                        <!-- Booking details will be loaded here -->
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Select charges to waive:</label>
                        <div class="form-check">
                            <input type="checkbox" name="waive_damage" id="waiveDamage" class="form-check-input">
                            <label class="form-check-label" for="waiveDamage">
                                Waive damage charges (<span id="damageAmount">$0.00</span>)
                            </label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="waive_late_fees" id="waiveLateFees" class="form-check-input">
                            <label class="form-check-label" for="waiveLateFees">
                                Waive late return fees (<span id="lateFeesAmount">$0.00</span>)
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason for Waiving <span class="text-danger">*</span></label>
                        <textarea name="waive_reason" class="form-control" rows="3" required
                                  placeholder="Explain why these charges are being waived..."></textarea>
                    </div>

                    <div class="alert alert-success">
                        <i class="fas fa-info-circle me-2"></i>
                        Waived charges will be removed from the customer's bill permanently.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Waive Selected Charges</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
let currentBookingId = null;

function setCharges(bookingId) {
    currentBookingId = bookingId;
    
    // Load booking details via AJAX
    fetch(`/admin/bookings/${bookingId}`)
        .then(response => response.json())
        .then(data => {
            // Populate booking info
            document.getElementById('bookingInfo').innerHTML = `
                <strong>Booking:</strong> ${data.booking_number || bookingId}<br>
                <strong>Customer:</strong> ${data.customer_name || 'N/A'}<br>
                <strong>Vehicle:</strong> ${data.vehicle_make || ''} ${data.vehicle_model || ''}<br>
                <strong>Current Damage Charges:</strong> $${data.damage_charges || '0.00'}<br>
                <strong>Current Late Fees:</strong> $${data.late_fees || '0.00'}
            `;
            
            // Pre-fill current charges
            document.querySelector('input[name="damage_charges"]').value = data.damage_charges || '';
            document.querySelector('input[name="late_fees"]').value = data.late_fees || '';
            
            // Show modal
            new bootstrap.Modal(document.getElementById('setChargesModal')).show();
        })
        .catch(error => {
            console.error('Error loading booking details:', error);
            alert('Error loading booking details. Please try again.');
        });
}

function waiveCharges(bookingId) {
    currentBookingId = bookingId;
    
    // Load booking details via AJAX
    fetch(`/admin/bookings/${bookingId}`)
        .then(response => response.json())
        .then(data => {
            // Populate booking info
            document.getElementById('waiveBookingInfo').innerHTML = `
                <strong>Booking:</strong> ${data.booking_number || bookingId}<br>
                <strong>Customer:</strong> ${data.customer_name || 'N/A'}<br>
                <strong>Vehicle:</strong> ${data.vehicle_make || ''} ${data.vehicle_model || ''}
            `;
            
            // Update amounts
            document.getElementById('damageAmount').textContent = `$${data.damage_charges || '0.00'}`;
            document.getElementById('lateFeesAmount').textContent = `$${data.late_fees || '0.00'}`;
            
            // Show/hide checkboxes based on existing charges
            document.getElementById('waiveDamage').parentElement.style.display = 
                (data.damage_charges && data.damage_charges > 0) ? 'block' : 'none';
            document.getElementById('waiveLateFees').parentElement.style.display = 
                (data.late_fees && data.late_fees > 0) ? 'block' : 'none';
            
            // Show modal
            new bootstrap.Modal(document.getElementById('waiveChargesModal')).show();
        })
        .catch(error => {
            console.error('Error loading booking details:', error);
            alert('Error loading booking details. Please try again.');
        });
}

// Handle set charges form submission
document.getElementById('setChargesForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(`/admin/billing/set-charges/${currentBookingId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Error setting charges');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error setting charges. Please try again.');
    });
});

// Handle waive charges form submission
document.getElementById('waiveChargesForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(`/admin/billing/waive-charges/${currentBookingId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Error waiving charges');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error waiving charges. Please try again.');
    });
});
</script>
@endsection