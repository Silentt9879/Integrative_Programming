@extends('app')

@section('title', 'Book ' . $vehicle->make . ' ' . $vehicle->model . ' - RentWheels')

@section('content')
<div class="container py-5">
    <!-- Back Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('vehicles.show', $vehicle->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Vehicle Details
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Vehicle Summary -->
        <div class="col-lg-4 mb-4">
            <div class="card sticky-top" style="top: 2rem;">
                <!-- Vehicle Image -->
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                    @if($vehicle->image_url)
                    <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->make }} {{ $vehicle->model }}"
                         class="img-fluid rounded-top" style="max-height: 100%; max-width: 100%; object-fit: cover;">
                    @else
                    <i class="fas fa-car text-muted" style="font-size: 4rem;"></i>
                    @endif
                </div>

                <div class="card-body">
                    <h5 class="fw-bold">{{ $vehicle->make }} {{ $vehicle->model }}</h5>

                    <!-- Vehicle Details -->
                    <div class="row g-2 text-sm mb-3">
                        <div class="col-6">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>{{ $vehicle->year }}
                            </small>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">
                                <i class="fas fa-users me-1"></i>{{ $vehicle->seating_capacity }} seats
                            </small>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">
                                <i class="fas fa-palette me-1"></i>{{ $vehicle->color }}
                            </small>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">
                                <i class="fas fa-gas-pump me-1"></i>{{ $vehicle->fuel_type }}
                            </small>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="bg-light p-3 rounded mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Daily Rate:</span>
                            <span class="text-primary fw-bold">RM{{ number_format($vehicle->rentalRate->daily_rate, 2) }}</span>
                        </div>
                    </div>

                    <!-- Booking Summary (will be updated via JavaScript) -->
                    <div id="bookingSummary" class="border rounded p-3" style="display: none;">
                        <h6 class="fw-bold mb-2">Booking Summary</h6>
                        <div class="d-flex justify-content-between mb-1">
                            <small>Duration:</small>
                            <small id="duration">-</small>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small>Daily Rate:</small>
                            <small>RM{{ number_format($vehicle->rentalRate->daily_rate, 2) }}</small>
                        </div>
                        <div class="d-flex justify-content-between mb-2 border-top pt-2">
                            <strong>Total Cost:</strong>
                            <strong class="text-success" id="totalCost">RM0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between text-muted">
                            <small>Deposit (30%):</small>
                            <small id="depositAmount">RM0.00</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Form -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-calendar-plus text-primary me-2"></i>
                        Complete Your Booking
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('booking.store') }}" id="bookingForm">
                        @csrf
                        <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">

                        <!-- Customer Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">Customer Information</h5>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" value="{{ auth()->user()->email }}" disabled>
                            </div>
                            <div class="col-md-6 mt-3">
                                <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel"
                                       name="customer_phone"
                                       class="form-control @error('customer_phone') is-invalid @enderror"
                                       value="{{ old('customer_phone', auth()->user()->phone) }}"
                                       placeholder="+60 12-345 6789"
                                       required>
                                @error('customer_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Rental Details -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">Rental Details</h5>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pickup Date <span class="text-danger">*</span></label>
                                <input type="date"
                                       name="pickup_date"
                                       class="form-control @error('pickup_date') is-invalid @enderror"
                                       value="{{ old('pickup_date', request('pickup_date', date('Y-m-d'))) }}"
                                       min="{{ date('Y-m-d') }}"
                                       required>
                                @error('pickup_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pickup Time <span class="text-danger">*</span></label>
                                <select name="pickup_time" class="form-select @error('pickup_time') is-invalid @enderror" required>
                                    <option value="">Select Time</option>
                                    @for($i = 8; $i <= 18; $i++)
                                    <option value="{{ sprintf('%02d:00', $i) }}" {{ old('pickup_time') == sprintf('%02d:00', $i) ? 'selected' : '' }}>
                                        {{ sprintf('%02d:00', $i) }}
                                    </option>
                                    <option value="{{ sprintf('%02d:30', $i) }}" {{ old('pickup_time') == sprintf('%02d:30', $i) ? 'selected' : '' }}>
                                        {{ sprintf('%02d:30', $i) }}
                                    </option>
                                    @endfor
                                </select>
                                @error('pickup_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mt-3">
                                <label class="form-label">Return Date <span class="text-danger">*</span></label>
                                <input type="date"
                                       name="return_date"
                                       class="form-control @error('return_date') is-invalid @enderror"
                                       value="{{ old('return_date', request('return_date', date('Y-m-d', strtotime('+1 day')))) }}"
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                       required>
                                @error('return_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mt-3">
                                <label class="form-label">Return Time <span class="text-danger">*</span></label>
                                <select name="return_time" class="form-select @error('return_time') is-invalid @enderror" required>
                                    <option value="">Select Time</option>
                                    @for($i = 8; $i <= 18; $i++)
                                    <option value="{{ sprintf('%02d:00', $i) }}" {{ old('return_time') == sprintf('%02d:00', $i) ? 'selected' : '' }}>
                                        {{ sprintf('%02d:00', $i) }}
                                    </option>
                                    <option value="{{ sprintf('%02d:30', $i) }}" {{ old('return_time') == sprintf('%02d:30', $i) ? 'selected' : '' }}>
                                        {{ sprintf('%02d:30', $i) }}
                                    </option>
                                    @endfor
                                </select>
                                @error('return_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Location Details -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">Location Details</h5>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pickup Location <span class="text-danger">*</span></label>
                                <select name="pickup_location" class="form-select @error('pickup_location') is-invalid @enderror" required>
                                    <option value="">Select Pickup Location</option>
                                    <option value="Main Office" {{ old('pickup_location') == 'Main Office' ? 'selected' : '' }}>Main Office</option>
                                    <option value="Airport" {{ old('pickup_location') == 'Airport' ? 'selected' : '' }}>Airport</option>
                                    <option value="Downtown" {{ old('pickup_location') == 'Downtown' ? 'selected' : '' }}>Downtown</option>
                                    <option value="Mall Central" {{ old('pickup_location') == 'Mall Central' ? 'selected' : '' }}>Mall Central</option>
                                </select>
                                @error('pickup_location')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Return Location <span class="text-danger">*</span></label>
                                <select name="return_location" class="form-select @error('return_location') is-invalid @enderror" required>
                                    <option value="">Select Return Location</option>
                                    <option value="Main Office" {{ old('return_location') == 'Main Office' ? 'selected' : '' }}>Main Office</option>
                                    <option value="Airport" {{ old('return_location') == 'Airport' ? 'selected' : '' }}>Airport</option>
                                    <option value="Downtown" {{ old('return_location') == 'Downtown' ? 'selected' : '' }}>Downtown</option>
                                    <option value="Mall Central" {{ old('return_location') == 'Mall Central' ? 'selected' : '' }}>Mall Central</option>
                                </select>
                                @error('return_location')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Special Requests -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label">Special Requests (Optional)</label>
                                <textarea name="special_requests"
                                          class="form-control @error('special_requests') is-invalid @enderror"
                                          rows="3"
                                          placeholder="Any special requirements or requests...">{{ old('special_requests') }}</textarea>
                                @error('special_requests')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
                                        and <a href="#" data-bs-toggle="modal" data-bs-target="#policyModal">Rental Policy</a>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-success btn-lg w-100" id="submitBtn" disabled>
                                    <i class="fas fa-calendar-check me-2"></i>
                                    Complete Booking
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms Modal (simplified) -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>By booking with RentWheels, you agree to:</p>
                <ul>
                    <li>Provide valid identification and driving license</li>
                    <li>Pay the required deposit before vehicle pickup</li>
                    <li>Return the vehicle in the same condition</li>
                    <li>Follow all traffic laws and regulations</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    function updatePricing() {
        const pickupDate = document.querySelector('input[name="pickup_date"]').value;
        const returnDate = document.querySelector('input[name="return_date"]').value;
        const dailyRate = {{ $vehicle->rentalRate->daily_rate }};

        if (pickupDate && returnDate) {
            const pickup = new Date(pickupDate);
            const returnD = new Date(returnDate);
            const days = Math.ceil((returnD - pickup) / (1000 * 60 * 60 * 24));

            if (days > 0) {
                const totalCost = days * dailyRate;
                const depositAmount = totalCost * 0.3;

                document.getElementById('duration').textContent = days + ' ' + (days === 1 ? 'day' : 'days');
                document.getElementById('totalCost').textContent = 'RM' + totalCost.toFixed(2);
                document.getElementById('depositAmount').textContent = 'RM' + depositAmount.toFixed(2);
                document.getElementById('bookingSummary').style.display = 'block';
            } else {
                document.getElementById('bookingSummary').style.display = 'none';
            }
        } else {
            document.getElementById('bookingSummary').style.display = 'none';
        }
    }

    document.getElementById('terms').addEventListener('change', function() {
        document.getElementById('submitBtn').disabled = !this.checked;
    });

    document.querySelector('input[name="pickup_date"]').addEventListener('change', updatePricing);
    document.querySelector('input[name="return_date"]').addEventListener('change', updatePricing);

    // Function to validate pickup time
function validatePickupTime() {
    const pickupDate = document.querySelector('input[name="pickup_date"]').value;
    const pickupTime = document.querySelector('select[name="pickup_time"]').value;

    if (pickupDate && pickupTime) {
        const now = new Date();
        const selectedDateTime = new Date(pickupDate + ' ' + pickupTime);

        // If pickup date is today, check if time has passed
        if (pickupDate === now.toISOString().split('T')[0]) {
            if (selectedDateTime <= now) {
                alert('Cannot select a pickup time that has already passed!');
                document.querySelector('select[name="pickup_time"]').value = '';
                return false;
            }
        }
    }
    return true;
}

document.querySelector('input[name="pickup_date"]').addEventListener('change', function() {
    const pickupDate = new Date(this.value);
    const returnDate = new Date(pickupDate);
    returnDate.setDate(returnDate.getDate() + 1);

    const returnInput = document.querySelector('input[name="return_date"]');
    returnInput.min = returnDate.toISOString().split('T')[0];

    if (new Date(returnInput.value) <= pickupDate) {
        returnInput.value = returnDate.toISOString().split('T')[0];
    }

    // Clear pickup time if date changes to trigger revalidation
    document.querySelector('select[name="pickup_time"]').value = '';
});

// Add time validation when pickup time changes
document.querySelector('select[name="pickup_time"]').addEventListener('change', validatePickupTime);

// Validate on form submit
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    if (!validatePickupTime()) {
        e.preventDefault();
    }
});

    updatePricing();

    document.querySelector('select[name="pickup_location"]').addEventListener('change', function() {
        document.querySelector('select[name="return_location"]').value = this.value;
    });
</script>
@endsection
@endsection
