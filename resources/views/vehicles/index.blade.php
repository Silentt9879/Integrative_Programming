@extends('app')

@section('title', 'Available Vehicles - RentWheels')

@section('content')
<div class="container py-5">
    <!-- Page Header -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="display-5 fw-bold text-center mb-4">Our Vehicle Fleet</h1>
                    <p class="lead text-center text-muted">Choose from our premium collection of vehicles</p>
                </div>
                <!-- Admin Controls -->
                @auth
                @if(Auth::user()->is_admin)
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.vehicles.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>Add New Vehicle
                    </a>
                </div>
                @endif
                @endauth
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('vehicles.index') }}" class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" 
                                   value="{{ request('search') }}" placeholder="Make, model, plate...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Vehicle Type</label>
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                <option value="Sedan" {{ request('type') == 'Sedan' ? 'selected' : '' }}>Sedan</option>
                                <option value="SUV" {{ request('type') == 'SUV' ? 'selected' : '' }}>SUV</option>
                                <option value="Luxury" {{ request('type') == 'Luxury' ? 'selected' : '' }}>Luxury</option>
                                <option value="Economy" {{ request('type') == 'Economy' ? 'selected' : '' }}>Economy</option>
                                <option value="Truck" {{ request('type') == 'Truck' ? 'selected' : '' }}>Truck</option>
                                <option value="Van" {{ request('type') == 'Van' ? 'selected' : '' }}>Van</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Year Range</label>
                            <select name="year" class="form-select">
                                <option value="">Any Year</option>
                                <option value="2024" {{ request('year') == '2024' ? 'selected' : '' }}>2024</option>
                                <option value="2023" {{ request('year') == '2023' ? 'selected' : '' }}>2023</option>
                                <option value="2022" {{ request('year') == '2022' ? 'selected' : '' }}>2022</option>
                                <option value="2021" {{ request('year') == '2021' ? 'selected' : '' }}>2021</option>
                                <option value="2020" {{ request('year') == '2020' ? 'selected' : '' }}>2020</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Max Daily Rate</label>
                            <select name="max_rate" class="form-select">
                                <option value="">Any Price</option>
                                <option value="50" {{ request('max_rate') == '50' ? 'selected' : '' }}>Under $50</option>
                                <option value="100" {{ request('max_rate') == '100' ? 'selected' : '' }}>Under $100</option>
                                <option value="150" {{ request('max_rate') == '150' ? 'selected' : '' }}>Under $150</option>
                                <option value="200" {{ request('max_rate') == '200' ? 'selected' : '' }}>Under $200</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                                <option value="rented" {{ request('status') == 'rented' ? 'selected' : '' }}>Rented</option>
                                <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="d-flex gap-1 w-100">
                                <button type="submit" class="btn btn-primary flex-fill">
                                    <i class="fas fa-search"></i>
                                </button>
                                <a href="{{ route('vehicles.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Count -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <p class="text-muted mb-0">
                <i class="fas fa-car me-2"></i>
                Showing {{ $vehicles->count() }} of {{ $vehicles->total() ?? $vehicles->count() }} vehicles
            </p>

            <!-- View Toggle -->
            <div class="btn-group" role="group">
                <input type="radio" class="btn-check" name="viewType" id="gridView" checked>
                <label class="btn btn-outline-primary" for="gridView">
                    <i class="fas fa-th"></i> Grid
                </label>

                <input type="radio" class="btn-check" name="viewType" id="listView">
                <label class="btn btn-outline-primary" for="listView">
                    <i class="fas fa-list"></i> List
                </label>
            </div>
        </div>
    </div>

    <!-- Vehicle Cards Grid View -->
    @if($vehicles->count() > 0)
    <div id="gridContainer" class="row">
        @foreach($vehicles as $vehicle)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card vehicle-card h-100 shadow-sm border-0 position-relative">
                <!-- Admin Quick Actions -->
                @auth
                @if(Auth::user()->is_admin)
                <div class="position-absolute top-0 end-0 m-2" style="z-index: 10;">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light rounded-circle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('vehicles.show', $vehicle->id) }}">
                                    <i class="fas fa-eye me-2 text-info"></i>View Details
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.vehicles.edit', $vehicle->id) }}">
                                    <i class="fas fa-edit me-2 text-warning"></i>Edit Vehicle
                                </a>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('admin.vehicles.toggle-status', $vehicle->id) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-toggle-on me-2 text-primary"></i>
                                        Toggle Status
                                    </button>
                                </form>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button class="dropdown-item text-danger" 
                                        onclick="confirmDelete('{{ $vehicle->id }}', '{{ $vehicle->make }} {{ $vehicle->model }}')">
                                    <i class="fas fa-trash me-2"></i>Delete
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
                @endif
                @endauth

                <!-- Vehicle Image -->
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                    @if($vehicle->image_url)
                    <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->make }} {{ $vehicle->model }}" 
                         class="img-fluid rounded-top" style="max-height: 100%; max-width: 100%; object-fit: cover;">
                    @else
                    <i class="fas fa-car text-muted" style="font-size: 4rem;"></i>
                    @endif
                </div>

                <div class="card-body d-flex flex-column">
                    <!-- Vehicle Title -->
                    <h5 class="card-title fw-bold">{{ $vehicle->make }} {{ $vehicle->model }}</h5>

                    <!-- Vehicle Details -->
                    <div class="vehicle-details mb-3">
                        <div class="row text-muted small mb-2">
                            <div class="col-6">
                                <i class="fas fa-calendar me-1"></i>{{ $vehicle->year }}
                            </div>
                            <div class="col-6">
                                <i class="fas fa-palette me-1"></i>{{ $vehicle->color }}
                            </div>
                        </div>
                        <div class="row text-muted small mb-2">
                            <div class="col-6">
                                <i class="fas fa-users me-1"></i>{{ $vehicle->seating_capacity }} Seats
                            </div>
                            <div class="col-6">
                                <i class="fas fa-gas-pump me-1"></i>{{ $vehicle->fuel_type }}
                            </div>
                        </div>
                        <div class="row text-muted small">
                            <div class="col-12">
                                <i class="fas fa-id-card me-1"></i>{{ $vehicle->license_plate }}
                            </div>
                        </div>
                    </div>

                    <!-- Price and Status -->
                    <div class="row align-items-center mb-3">
                        <div class="col-8">
                            <h4 class="text-primary mb-0">
                                RM{{ number_format($vehicle->rentalRate->daily_rate ?? 0, 2) }}
                            </h4>
                            <small class="text-muted">per day</small>
                        </div>
                        <div class="col-4 text-end">
                            @if($vehicle->status == 'available')
                            <span class="badge bg-success">Available</span>
                            @elseif($vehicle->status == 'rented')
                            <span class="badge bg-warning">Rented</span>
                            @else
                            <span class="badge bg-secondary">Maintenance</span>
                            @endif
                        </div>
                    </div>

                    <!-- Vehicle Type Badge -->
                    <div class="mb-3">
                        <span class="badge bg-primary">{{ $vehicle->type }}</span>
                        @if($vehicle->current_mileage)
                        <span class="badge bg-light text-dark">{{ number_format($vehicle->current_mileage) }}km</span>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-auto">
                        <div class="d-grid gap-2">
                            <div class="d-flex gap-2">
                                <a href="{{ route('vehicles.show', $vehicle->id) }}" class="btn btn-primary flex-fill">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </a>
                                @auth
                                @if(Auth::user()->is_admin)
                                <a href="{{ route('admin.vehicles.edit', $vehicle->id) }}" class="btn btn-outline-warning flex-fill">
                                    <i class="fas fa-edit me-1"></i>Edit
                                </a>
                                @endif
                                @endauth
                            </div>
                            @if($vehicle->status == 'available')
                            @auth
                            <a href="{{ route('booking.create', $vehicle->id) }}" class="btn btn-success">
                                <i class="fas fa-calendar-plus me-2"></i>Book Now
                            </a>
                            @else
                            <a href="{{ route('login') }}" class="btn btn-success">
                                <i class="fas fa-sign-in-alt me-2"></i>Book Now
                            </a>
                            @endauth
                            @else
                            <button class="btn btn-secondary" disabled>
                                <i class="fas fa-ban me-2"></i>Not Available
                            </button>
                            @endif
                            <a href="tel:+60123456789" class="btn btn-outline-success">
                                <i class="fas fa-phone me-2"></i>Call for Inquiry
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- List View -->
    <div id="listContainer" class="d-none">
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Vehicle</th>
                            <th>Year</th>
                            <th>Type</th>
                            <th>License Plate</th>
                            <th>Daily Rate</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vehicles as $vehicle)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        @if($vehicle->image_url)
                                        <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->make }}" 
                                             class="rounded" style="width: 50px; height: 40px; object-fit: cover;">
                                        @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 40px;">
                                            <i class="fas fa-car text-muted"></i>
                                        </div>
                                        @endif
                                    </div>
                                    <div>
                                        <strong>{{ $vehicle->make }} {{ $vehicle->model }}</strong><br>
                                        <small class="text-muted">{{ $vehicle->color }} • {{ $vehicle->seating_capacity }} seats</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $vehicle->year }}</td>
                            <td><span class="badge bg-primary">{{ $vehicle->type }}</span></td>
                            <td><code>{{ $vehicle->license_plate }}</code></td>
                            <td class="text-primary fw-bold">RM{{ number_format($vehicle->rentalRate->daily_rate ?? 0, 2) }}</td>
                            <td>
                                @if($vehicle->status == 'available')
                                <span class="badge bg-success">Available</span>
                                @elseif($vehicle->status == 'rented')
                                <span class="badge bg-warning">Rented</span>
                                @else
                                <span class="badge bg-secondary">Maintenance</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('vehicles.show', $vehicle->id) }}" class="btn btn-sm btn-outline-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($vehicle->status == 'available')
                                    @auth
                                    <a href="{{ route('booking.create', $vehicle->id) }}" class="btn btn-sm btn-success" title="Book Now">
                                        <i class="fas fa-calendar-plus"></i>
                                    </a>
                                    @else
                                    <a href="{{ route('login') }}" class="btn btn-sm btn-success" title="Login to Book">
                                        <i class="fas fa-sign-in-alt"></i>
                                    </a>
                                    @endauth
                                    @endif
                                    <a href="tel:+60123456789" class="btn btn-sm btn-outline-success" title="Call for Inquiry">
                                        <i class="fas fa-phone"></i>
                                    </a>
                                    @auth
                                    @if(Auth::user()->is_admin)
                                    <a href="{{ route('admin.vehicles.edit', $vehicle->id) }}" class="btn btn-sm btn-outline-warning" title="Edit Vehicle">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                    @endauth
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if(method_exists($vehicles, 'links'))
    <div class="row mt-4">
        <div class="col-12 d-flex justify-content-center">
            {{ $vehicles->appends(request()->query())->links() }}
        </div>
    </div>
    @endif
    @else
    <!-- No Vehicles Found -->
    <div class="row">
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-car text-muted mb-3" style="font-size: 4rem;"></i>
                <h3 class="text-muted">No vehicles found</h3>
                <p class="text-muted">Try adjusting your search criteria or add new vehicles.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('vehicles.index') }}" class="btn btn-primary">
                        <i class="fas fa-undo me-2"></i>View All Vehicles
                    </a>
                    @auth
                    @if(Auth::user()->is_admin)
                    <a href="{{ route('admin.vehicles.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>Add New Vehicle
                    </a>
                    @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
@auth
@if(Auth::user()->is_admin)
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
                <p>Are you sure you want to delete <strong id="deleteVehicleName"></strong>?</p>
                <p class="text-muted small">This action cannot be undone. The vehicle and all its associated data will be permanently removed.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" id="deleteForm" class="d-inline">
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
@endif
@endauth

<!-- Custom CSS -->
<style>
    .vehicle-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .vehicle-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }

    .vehicle-details {
        font-size: 0.9rem;
    }

    .badge {
        font-size: 0.75rem;
    }

    .table th {
        border-top: none;
        font-weight: 600;
        color: #495057;
    }
</style>

<script>
// View Toggle Functionality
    document.getElementById('gridView').addEventListener('change', function() {
    if (this.checked) {
    document.getElementById('gridContainer').classList.remove('d-none');
    document.getElementById('listContainer').classList.add('d-none');
    }
    });
    document.getElementById('listView').addEventListener('change', function() {
    if (this.checked) {
    document.getElementById('gridContainer').classList.add('d-none');
    document.getElementById('listContainer').classList.remove('d-none');
    }
    });
// Delete Confirmation
    function confirmDelete(vehicleId, vehicleName) {
    @auth
            @if(Auth::user()->is_admin)
            document.getElementById('deleteVehicleName').textContent = vehicleName;
    document.getElementById('deleteForm').action = `/admin/vehicles/${vehicleId}`;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
    @endif
            @endauth
            }
</script>
@endsection