@extends('admin')

@section('title', 'Edit Vehicle - ' . $vehicle->make . ' ' . $vehicle->model . ' - RentWheels Admin')

@section('content')

    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #dc3545 0%, #6f42c1 50%, #fd7e14 100%);
            min-height: 100vh;
        }

        .dashboard-container {
            padding: 2rem 0;
        }

        .admin-welcome-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .admin-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: transform 0.3s ease;
        }

        .admin-card:hover {
            transform: translateY(-3px);
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

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        .btn-primary,
        .btn-warning {
            background: linear-gradient(135deg, #dc3545, #6f42c1);
            border: none;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-primary:hover,
        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
            color: white;
        }

        .btn-outline-secondary,
        .btn-outline-info {
            border-color: rgba(108, 117, 125, 0.3);
            transition: all 0.3s ease;
        }

        .btn-outline-secondary:hover,
        .btn-outline-info:hover {
            transform: translateY(-2px);
            background: rgba(108, 117, 125, 0.1);
        }

        .btn-outline-danger {
            border-color: rgba(220, 53, 69, 0.5);
            color: #dc3545;
            transition: all 0.3s ease;
        }

        .btn-outline-danger:hover {
            transform: translateY(-2px);
            background: rgba(220, 53, 69, 0.1);
            border-color: #dc3545;
            color: #dc3545;
        }

        .card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #dc3545, #6f42c1) !important;
            border: none;
            color: white !important;
        }

        .text-primary,
        .text-warning {
            color: #dc3545 !important;
        }

        .border-bottom {
            border-color: rgba(220, 53, 69, 0.3) !important;
        }
    </style>

    <div class="dashboard-container">
        <div class="container">
            <!-- Page Header -->
            <div class="admin-welcome-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="h2 mb-3">
                            <i class="fas fa-edit text-danger me-3"></i>
                            Edit Vehicle
                        </h1>
                        <p class="lead text-muted mb-3">
                            Update {{ $vehicle->make }} {{ $vehicle->model }} details and specifications.
                        </p>
                        <div class="d-flex gap-3 flex-wrap">
                            <a href="{{ route('admin.vehicles.show', $vehicle->id) }}" class="btn btn-outline-info">
                                <i class="fas fa-eye me-2"></i>View Details
                            </a>
                            <a href="{{ route('admin.vehicles') }}" class="btn quick-action-btn">
                                <i class="fas fa-arrow-left me-2"></i>Back to Vehicles
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Vehicle Form -->
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="admin-card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Update Vehicle Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.vehicles.update', $vehicle) }}" enctype="multipart/form-data" novalidate>
                                @csrf
                                @method('PUT')

                                <!-- Basic Information -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">
                                            <i class="fas fa-info-circle me-2"></i>Basic Information
                                        </h6>
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="make" class="form-label">Make <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('make') is-invalid @enderror"
                                            id="make" name="make" value="{{ old('make', $vehicle->make) }}"
                                            required>
                                        @error('make')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="model" class="form-label">Model <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('model') is-invalid @enderror"
                                            id="model" name="model" value="{{ old('model', $vehicle->model) }}"
                                            required>
                                        @error('model')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label for="year" class="form-label">Year <span
                                                class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('year') is-invalid @enderror"
                                            id="year" name="year" value="{{ old('year', $vehicle->year) }}"
                                            min="1900" max="{{ date('Y') + 1 }}" required>
                                        @error('year')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="color" class="form-label">Color <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('color') is-invalid @enderror"
                                            id="color" name="color" value="{{ old('color', $vehicle->color) }}"
                                            required>
                                        @error('color')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="license_plate" class="form-label">License Plate <span
                                                class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control @error('license_plate') is-invalid @enderror"
                                            id="license_plate" name="license_plate"
                                            value="{{ old('license_plate', $vehicle->license_plate) }}" required>
                                        @error('license_plate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Vehicle Specifications -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">
                                            <i class="fas fa-cogs me-2"></i>Specifications
                                        </h6>
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label for="type" class="form-label">Vehicle Type <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('type') is-invalid @enderror" id="type"
                                            name="type" required>
                                            <option value="">Choose Type...</option>
                                            <option value="Sedan"
                                                {{ old('type', $vehicle->type) == 'Sedan' ? 'selected' : '' }}>Sedan
                                            </option>
                                            <option value="SUV"
                                                {{ old('type', $vehicle->type) == 'SUV' ? 'selected' : '' }}>SUV</option>
                                            <option value="Luxury"
                                                {{ old('type', $vehicle->type) == 'Luxury' ? 'selected' : '' }}>Luxury
                                            </option>
                                            <option value="Economy"
                                                {{ old('type', $vehicle->type) == 'Economy' ? 'selected' : '' }}>Economy
                                            </option>
                                            <option value="Truck"
                                                {{ old('type', $vehicle->type) == 'Truck' ? 'selected' : '' }}>Truck
                                            </option>
                                            <option value="Van"
                                                {{ old('type', $vehicle->type) == 'Van' ? 'selected' : '' }}>Van</option>
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="fuel_type" class="form-label">Fuel Type <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('fuel_type') is-invalid @enderror"
                                            id="fuel_type" name="fuel_type" required>
                                            <option value="">Choose Fuel...</option>
                                            <option value="Petrol"
                                                {{ old('fuel_type', $vehicle->fuel_type) == 'Petrol' ? 'selected' : '' }}>
                                                Petrol</option>
                                            <option value="Diesel"
                                                {{ old('fuel_type', $vehicle->fuel_type) == 'Diesel' ? 'selected' : '' }}>
                                                Diesel</option>
                                            <option value="Electric"
                                                {{ old('fuel_type', $vehicle->fuel_type) == 'Electric' ? 'selected' : '' }}>
                                                Electric</option>
                                            <option value="Hybrid"
                                                {{ old('fuel_type', $vehicle->fuel_type) == 'Hybrid' ? 'selected' : '' }}>
                                                Hybrid</option>
                                        </select>
                                        @error('fuel_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="seating_capacity" class="form-label">Seating Capacity <span
                                                class="text-danger">*</span></label>
                                        <input type="number"
                                            class="form-control @error('seating_capacity') is-invalid @enderror"
                                            id="seating_capacity" name="seating_capacity"
                                            value="{{ old('seating_capacity', $vehicle->seating_capacity) }}"
                                            min="1" max="15" required>
                                        @error('seating_capacity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="current_mileage" class="form-label">Current Mileage (km) <span
                                                class="text-danger">*</span></label>
                                        <input type="number" step="0.01"
                                            class="form-control @error('current_mileage') is-invalid @enderror"
                                            id="current_mileage" name="current_mileage"
                                            value="{{ old('current_mileage', $vehicle->current_mileage) }}"
                                            min="0" required>
                                        @error('current_mileage')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="status" class="form-label">Status <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('status') is-invalid @enderror" id="status"
                                            name="status" required>
                                            <option value="available"
                                                {{ old('status', $vehicle->status) == 'available' ? 'selected' : '' }}>
                                                Available</option>
                                            <option value="maintenance"
                                                {{ old('status', $vehicle->status) == 'maintenance' ? 'selected' : '' }}>
                                                Maintenance</option>
                                            <option value="rented"
                                                {{ old('status', $vehicle->status) == 'rented' ? 'selected' : '' }}>Rented
                                            </option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Pricing Information -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">
                                            <i class="fas fa-dollar-sign me-2"></i>Pricing Information
                                        </h6>
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label for="daily_rate" class="form-label">Daily Rate (RM) <span
                                                class="text-danger">*</span></label>
                                        <input type="number" step="0.01"
                                            class="form-control @error('daily_rate') is-invalid @enderror"
                                            id="daily_rate" name="daily_rate"
                                            value="{{ old('daily_rate', $vehicle->rentalRate->daily_rate ?? 0) }}"
                                            min="0" required>
                                        @error('daily_rate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="weekly_rate" class="form-label">Weekly Rate (RM)</label>
                                        <input type="number" step="0.01"
                                            class="form-control @error('weekly_rate') is-invalid @enderror"
                                            id="weekly_rate" name="weekly_rate"
                                            value="{{ old('weekly_rate', $vehicle->rentalRate->weekly_rate ?? '') }}"
                                            min="0">
                                        @error('weekly_rate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="monthly_rate" class="form-label">Monthly Rate (RM)</label>
                                        <input type="number" step="0.01"
                                            class="form-control @error('monthly_rate') is-invalid @enderror"
                                            id="monthly_rate" name="monthly_rate"
                                            value="{{ old('monthly_rate', $vehicle->rentalRate->monthly_rate ?? '') }}"
                                            min="0">
                                        @error('monthly_rate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Additional Information -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">
                                            <i class="fas fa-plus me-2"></i>Additional Information
                                        </h6>
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-12">
                                        <label for="image" class="form-label">Vehicle Image</label>

                                        @if ($vehicle->image_url)
                                            <div class="mb-2">
                                                <img src="{{ $vehicle->image_url }}" alt="Current image"
                                                    class="img-thumbnail" style="max-height: 100px;">
                                                <small class="text-muted d-block">Current image (Stored securely on local
                                                    server)</small>
                                            </div>
                                        @endif

                                        <input type="file" class="form-control @error('image') is-invalid @enderror"
                                            id="image" name="image" accept="image/jpeg,image/png,image/gif">
                                        <div class="form-text">Upload new image to replace current one (JPG, PNG, GIF only,
                                            max 5MB) - Secured with malicious content detection</div>
                                        @error('image')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-12">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                            rows="3" maxlength="500">{{ old('description', $vehicle->description) }}</textarea>
                                        <div class="form-text">Optional vehicle description (max 500 characters)</div>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <a href="{{ route('admin.vehicles') }}" class="btn btn-outline-secondary">
                                                <i class="fas fa-times me-2"></i>Cancel
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                                                data-bs-target="#deleteModal">
                                                <i class="fas fa-trash me-2"></i>Delete Vehicle
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Update Vehicle
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Delete Vehicle
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong>{{ $vehicle->make }} {{ $vehicle->model }}</strong>?</p>
                    <p class="text-muted small">This action cannot be undone. The vehicle and all its associated data will
                        be permanently removed.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('admin.vehicles.destroy', $vehicle->id) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Delete Vehicle
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('daily_rate').addEventListener('input', function() {
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
