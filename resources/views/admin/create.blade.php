@extends('admin')

@section('title', 'Add New Vehicle - RentWheels Admin')

@section('content')

<style>
    body {
        font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #ffffff;
        min-height: 100vh;
    }

    .dashboard-container {
        padding: 2rem 0;
        background: #f8f9fa;
    }

    .admin-welcome-card {
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        border: 3px solid #dee2e6;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .admin-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        border: 3px solid #e9ecef;
        transition: transform 0.3s ease;
    }

    .admin-card:hover {
        transform: translateY(-3px);
        border-color: #dc3545;
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.18);
    }

    .quick-action-btn {
        background: linear-gradient(135deg, #dc3545, #6f42c1);
        border: none;
        color: white;
        transition: all 0.3s ease;
    }

    .quick-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(220, 53, 69, 0.3);
        color: white;
    }

    .form-control, .form-select {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
        background: #ffffff;
    }

    .form-control:focus, .form-select:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    .btn-primary {
        background: linear-gradient(135deg, #dc3545, #6f42c1);
        border: none;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
    }

    .btn-outline-secondary {
        border-color: #6c757d;
        border-width: 2px;
        transition: all 0.3s ease;
    }

    .btn-outline-secondary:hover {
        transform: translateY(-2px);
        background: rgba(108, 117, 125, 0.1);
        border-color: #6c757d;
    }

    .card {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background: linear-gradient(135deg, #dc3545, #6f42c1) !important;
        border: none;
        padding: 1.5rem;
    }

    .card-body {
        background: #ffffff;
        padding: 2rem;
    }

    .text-primary {
        color: #dc3545 !important;
    }

    .border-bottom {
        border-color: rgba(220, 53, 69, 0.3) !important;
        border-width: 2px !important;
    }

    .container {
        max-width: 1200px;
    }

    .invalid-feedback {
        font-weight: 500;
    }

    .form-text {
        color: #6c757d;
        font-size: 0.875rem;
    }

    .form-label {
        font-weight: 600;
        color: #343a40;
        margin-bottom: 0.75rem;
    }

    .is-invalid {
        border-color: #dc3545 !important;
        border-width: 2px !important;
    }

    h6 {
        font-size: 1.1rem;
    }
</style>

<div class="dashboard-container">
    <div class="container">
        <div class="admin-welcome-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h2 mb-3">
                        <i class="fas fa-plus-circle text-danger me-3"></i>
                        Add New Vehicle
                    </h1>
                    <p class="lead text-muted mb-3">
                        Enter vehicle details and pricing information to expand your fleet.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="{{ route('admin.vehicles') }}" class="btn quick-action-btn">
                            <i class="fas fa-arrow-left me-2"></i>Back to Vehicles
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="admin-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Vehicle Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.vehicles.store') }}" novalidate>
                            @csrf

                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">
                                        <i class="fas fa-info-circle me-2"></i>Basic Information
                                    </h6>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="make" class="form-label">Make <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('make') is-invalid @enderror" 
                                           id="make" name="make" value="{{ old('make') }}" required>
                                    @error('make')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="model" class="form-label">Model <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('model') is-invalid @enderror" 
                                           id="model" name="model" value="{{ old('model') }}" required>
                                    @error('model')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label for="year" class="form-label">Year <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('year') is-invalid @enderror" 
                                           id="year" name="year" value="{{ old('year') }}" min="1900" max="{{ date('Y')+1 }}" required>
                                    @error('year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="color" class="form-label">Color <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('color') is-invalid @enderror" 
                                           id="color" name="color" value="{{ old('color') }}" required>
                                    @error('color')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="license_plate" class="form-label">License Plate <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('license_plate') is-invalid @enderror" 
                                           id="license_plate" name="license_plate" value="{{ old('license_plate') }}" required>
                                    @error('license_plate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">
                                        <i class="fas fa-cogs me-2"></i>Specifications
                                    </h6>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label for="type" class="form-label">Vehicle Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                        <option value="">Choose Type...</option>
                                        <option value="Sedan" {{ old('type') == 'Sedan' ? 'selected' : '' }}>Sedan</option>
                                        <option value="SUV" {{ old('type') == 'SUV' ? 'selected' : '' }}>SUV</option>
                                        <option value="Luxury" {{ old('type') == 'Luxury' ? 'selected' : '' }}>Luxury</option>
                                        <option value="Economy" {{ old('type') == 'Economy' ? 'selected' : '' }}>Economy</option>
                                        <option value="Truck" {{ old('type') == 'Truck' ? 'selected' : '' }}>Truck</option>
                                        <option value="Van" {{ old('type') == 'Van' ? 'selected' : '' }}>Van</option>
                                    </select>
                                    @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="fuel_type" class="form-label">Fuel Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('fuel_type') is-invalid @enderror" id="fuel_type" name="fuel_type" required>
                                        <option value="">Choose Fuel...</option>
                                        <option value="Petrol" {{ old('fuel_type') == 'Petrol' ? 'selected' : '' }}>Petrol</option>
                                        <option value="Diesel" {{ old('fuel_type') == 'Diesel' ? 'selected' : '' }}>Diesel</option>
                                        <option value="Electric" {{ old('fuel_type') == 'Electric' ? 'selected' : '' }}>Electric</option>
                                        <option value="Hybrid" {{ old('fuel_type') == 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                                    </select>
                                    @error('fuel_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="seating_capacity" class="form-label">Seating Capacity <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('seating_capacity') is-invalid @enderror" 
                                           id="seating_capacity" name="seating_capacity" value="{{ old('seating_capacity') }}" min="1" max="15" required>
                                    @error('seating_capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="current_mileage" class="form-label">Current Mileage (km) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control @error('current_mileage') is-invalid @enderror" 
                                           id="current_mileage" name="current_mileage" value="{{ old('current_mileage') }}" min="0" required>
                                    @error('current_mileage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>Available</option>
                                        <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                        <option value="rented" {{ old('status') == 'rented' ? 'selected' : '' }}>Rented</option>
                                    </select>
                                    @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">
                                        <i class="fas fa-dollar-sign me-2"></i>Pricing Information
                                    </h6>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label for="daily_rate" class="form-label">Daily Rate (RM) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control @error('daily_rate') is-invalid @enderror" 
                                           id="daily_rate" name="daily_rate" value="{{ old('daily_rate') }}" min="0" required>
                                    @error('daily_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="weekly_rate" class="form-label">Weekly Rate (RM)</label>
                                    <input type="number" step="0.01" class="form-control @error('weekly_rate') is-invalid @enderror" 
                                           id="weekly_rate" name="weekly_rate" value="{{ old('weekly_rate') }}" min="0">
                                    @error('weekly_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="monthly_rate" class="form-label">Monthly Rate (RM)</label>
                                    <input type="number" step="0.01" class="form-control @error('monthly_rate') is-invalid @enderror" 
                                           id="monthly_rate" name="monthly_rate" value="{{ old('monthly_rate') }}" min="0">
                                    @error('monthly_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">
                                        <i class="fas fa-plus me-2"></i>Additional Information
                                    </h6>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label for="image_url" class="form-label">Image URL</label>
                                    <input type="url" class="form-control @error('image_url') is-invalid @enderror" 
                                           id="image_url" name="image_url" value="{{ old('image_url') }}" 
                                           placeholder="https://example.com/vehicle-image.jpg">
                                    @error('image_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3" maxlength="500">{{ old('description') }}</textarea>
                                    <div class="form-text">Optional vehicle description (max 500 characters)</div>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ route('admin.vehicles') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Add Vehicle
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('daily_rate').addEventListener('input', function () {
    const dailyRate = parseFloat(this.value) || 0;
    const weeklyField = document.getElementById('weekly_rate');
    const monthlyField = document.getElementById('monthly_rate');

    if (dailyRate > 0) {
        const weeklyRate = (dailyRate * 7).toFixed(2);
        const monthlyRate = (dailyRate * 30).toFixed(2);

        weeklyField.value = weeklyRate;
        monthlyField.value = monthlyRate;
    } else {
        weeklyField.value = '';
        monthlyField.value = '';
    }
});
</script>
@endsection