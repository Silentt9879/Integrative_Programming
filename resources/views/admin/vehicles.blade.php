@extends('admin')

@section('title', 'Vehicle Management - RentWheels Admin')

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

    .admin-stats-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.3);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .admin-stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
    }

    .stats-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    .admin-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.3);
        transition: transform 0.3s ease;
        overflow: visible !important;
    }

    .admin-card:hover {
        transform: translateY(-3px);
    }

    .admin-card .card-body {
        overflow: visible !important;
        position: relative;
    }

    .vehicle-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.3);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
    }

    .vehicle-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
    }

    .status-badge {
        font-size: 0.8em;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-weight: 600;
    }

    .status-available { background: linear-gradient(45deg, #28a745, #20c997); color: white; }
    .status-rented { background: linear-gradient(45deg, #ffc107, #fd7e14); color: white; }
    .status-maintenance { background: linear-gradient(45deg, #6c757d, #495057); color: white; }

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

    .admin-section-title {
        color: #343a40;
        font-weight: 600;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 3px solid #dc3545;
        display: inline-block;
    }

    .metric-trend {
        font-size: 0.8rem;
        font-weight: 500;
    }

    .trend-up { color: #28a745; }
    .trend-down { color: #dc3545; }
    .trend-stable { color: #6c757d; }

    .filter-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.3);
        margin-bottom: 2rem;
    }

    .vehicle-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 12px 12px 0 0;
    }

    .vehicle-placeholder {
        width: 100%;
        height: 200px;
        background: linear-gradient(45deg, #f8f9fa, #e9ecef);
        border-radius: 12px 12px 0 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-size: 3rem;
    }

    .action-btn {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .action-btn:hover {
        transform: translateY(-1px);
    }

    .vehicle-details {
        font-size: 0.9rem;
    }

    .vehicle-details .row {
        margin-bottom: 0.5rem;
    }

    .dropdown-toggle::after {
        display: none;
    }

    .list-view-item {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.3);
        margin-bottom: 1rem;
        transition: transform 0.3s ease;
        position: relative !important;
        overflow: visible !important;
        z-index: 1;
    }

    .list-view-item:hover {
        transform: translateY(-2px);
        z-index: 10;
    }

    .list-view-item .dropdown {
        position: relative !important;
        z-index: 1000;
    }

    .list-view-item .dropdown-menu {
        z-index: 9999 !important;
        position: absolute !important;
        top: 100% !important;
        right: 0 !important;
        left: auto !important;
        transform: none !important;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25) !important;
        border: 1px solid rgba(0, 0, 0, 0.1) !important;
        background: white !important;
        border-radius: 8px !important;
        padding: 0.5rem 0 !important;
        min-width: 160px !important;
        margin-top: 2px !important;
    }

    .list-view-item .dropdown-item {
        padding: 0.5rem 1rem !important;
        color: #333 !important;
        text-decoration: none !important;
    }

    .list-view-item .dropdown-item:hover {
        background-color: #f8f9fa !important;
        color: #333 !important;
    }

    .list-view-item .dropdown-item.text-danger {
        color: #dc3545 !important;
    }

    .list-view-item .dropdown-item.text-danger:hover {
        background-color: #f8f9fa !important;
        color: #dc3545 !important;
    }

    .list-view-item .dropdown.show .dropdown-menu {
        display: block !important;
    }

    .vehicle-list-image {
        width: 120px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
    }

    #vehicleListView {
        overflow: visible !important;
        position: relative;
        z-index: 1;
    }

    .container {
        overflow: visible !important;
    }

    .dashboard-container {
        overflow: visible !important;
    }
</style>

<div class="dashboard-container">
    <div class="container">
        <!-- Welcome Section -->
        <div class="admin-welcome-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h2 mb-3">
                        <i class="fas fa-car text-danger me-3"></i>
                        Vehicle Management
                    </h1>
                    <p class="lead text-muted mb-3">
                        Manage your vehicle fleet, monitor availability, and track performance metrics.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="{{ route('admin.vehicles.create') }}" class="btn quick-action-btn btn-lg">
                            <i class="fas fa-plus me-2"></i>Add New Vehicle
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter and Search Section -->
        <div class="filter-card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.vehicles') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" 
                               value="{{ request('search') }}" placeholder="Make, model, plate...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Type</label>
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
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                            <option value="rented" {{ request('status') == 'rented' ? 'selected' : '' }}>Rented</option>
                            <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
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
                        <label class="form-label">Sort By</label>
                        <select name="sort" class="form-select">
                            <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                            <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="mileage" {{ request('sort') == 'mileage' ? 'selected' : '' }}>Mileage</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <div class="d-flex gap-1 w-100">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="{{ route('admin.vehicles') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Vehicle Management Section -->
        <div class="admin-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="admin-section-title">
                        <i class="fas fa-car-side text-primary me-2"></i>
                        Fleet Management
                    </h5>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="text-muted">Showing {{ $vehicles->count() }} of {{ $vehicles->total() ?? $vehicles->count() }} vehicles</span>
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
                
                @if($vehicles->count() > 0)
                    <!-- Grid View -->
                    <div id="vehicleGridView" class="row g-4">
                        @foreach($vehicles as $vehicle)
                            <div class="col-lg-4 col-md-6">
                                <div class="vehicle-card">
                                    <div class="position-relative">
                                        @if($vehicle->image_url)
                                            <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->make }} {{ $vehicle->model }}" 
                                                 class="vehicle-image">
                                        @else
                                            <div class="vehicle-placeholder">
                                                <i class="fas fa-car"></i>
                                            </div>
                                        @endif
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light rounded-circle" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.vehicles.show', $vehicle->id) }}">
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
                                                                <i class="fas fa-toggle-on me-2 text-primary"></i>Toggle Status
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
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title fw-bold">{{ $vehicle->make }} {{ $vehicle->model }}</h5>
                                        <div class="vehicle-details mb-3">
                                            <div class="row text-muted small mb-2">
                                                <div class="col-6"><i class="fas fa-calendar me-1"></i>{{ $vehicle->year }}</div>
                                                <div class="col-6"><i class="fas fa-palette me-1"></i>{{ $vehicle->color }}</div>
                                            </div>
                                            <div class="row text-muted small mb-2">
                                                <div class="col-6"><i class="fas fa-users me-1"></i>{{ $vehicle->seating_capacity }} Seats</div>
                                                <div class="col-6"><i class="fas fa-gas-pump me-1"></i>{{ $vehicle->fuel_type }}</div>
                                            </div>
                                            <div class="row text-muted small">
                                                <div class="col-12"><i class="fas fa-id-card me-1"></i>{{ $vehicle->license_plate }}</div>
                                            </div>
                                        </div>
                                        <div class="row align-items-center mb-3">
                                            <div class="col-8">
                                                <h4 class="text-primary mb-0">RM{{ number_format($vehicle->rentalRate->daily_rate ?? 0, 2) }}</h4>
                                                <small class="text-muted">per day</small>
                                            </div>
                                            <div class="col-4 text-end">
                                                <span class="status-badge status-{{ strtolower($vehicle->status) }}">
                                                    {{ ucfirst($vehicle->status) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <span class="badge bg-primary">{{ $vehicle->type }}</span>
                                            @if($vehicle->current_mileage)
                                                <span class="badge bg-light text-dark">{{ number_format($vehicle->current_mileage) }}km</span>
                                            @endif
                                        </div>
                                        <div class="d-grid gap-2">
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.vehicles.show', $vehicle->id) }}" class="btn btn-outline-primary flex-fill action-btn">
                                                    <i class="fas fa-eye me-1"></i>View
                                                </a>
                                                <a href="{{ route('admin.vehicles.edit', $vehicle->id) }}" class="btn btn-outline-warning flex-fill action-btn">
                                                    <i class="fas fa-edit me-1"></i>Edit
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- List View -->
                    <div id="vehicleListView" class="d-none">
                        @foreach($vehicles as $vehicle)
                            <div class="list-view-item">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            @if($vehicle->image_url)
                                                <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->make }}" 
                                                     class="vehicle-list-image">
                                            @else
                                                <div class="vehicle-placeholder" style="height: 80px;">
                                                    <i class="fas fa-car"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-3">
                                            <h6 class="mb-1 fw-bold">{{ $vehicle->make }} {{ $vehicle->model }}</h6>
                                            <small class="text-muted">{{ $vehicle->year }} • {{ $vehicle->color }} • {{ $vehicle->seating_capacity }} seats</small><br>
                                            <small class="text-muted"><i class="fas fa-id-card me-1"></i>{{ $vehicle->license_plate }}</small>
                                        </div>
                                        <div class="col-md-2">
                                            <span class="badge bg-primary">{{ $vehicle->type }}</span><br>
                                            <small class="text-muted">{{ $vehicle->fuel_type }}</small>
                                        </div>
                                        <div class="col-md-2">
                                            <h5 class="text-primary mb-0">RM{{ number_format($vehicle->rentalRate->daily_rate ?? 0, 2) }}</h5>
                                            <small class="text-muted">per day</small>
                                        </div>
                                        <div class="col-md-2">
                                            <span class="status-badge status-{{ strtolower($vehicle->status) }}">
                                                {{ ucfirst($vehicle->status) }}
                                            </span><br>
                                            <small class="text-muted">{{ number_format($vehicle->current_mileage) }} km</small>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="{{ route('admin.vehicles.show', $vehicle->id) }}"><i class="fas fa-eye me-2"></i>View</a></li>
                                                    <li><a class="dropdown-item" href="{{ route('admin.vehicles.edit', $vehicle->id) }}"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                                    <li>
                                                        <button class="dropdown-item text-danger" 
                                                                onclick="confirmDelete('{{ $vehicle->id }}', '{{ $vehicle->make }} {{ $vehicle->model }}')">
                                                            <i class="fas fa-trash me-2"></i>Delete
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- No Vehicles Found -->
                    <div class="text-center py-5">
                        <i class="fas fa-car text-muted mb-3" style="font-size: 4rem;"></i>
                        <h3 class="text-muted">No vehicles found</h3>
                        <p class="text-muted">Try adjusting your search criteria or add new vehicles.</p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="{{ route('admin.vehicles') }}" class="btn btn-primary">
                                <i class="fas fa-undo me-2"></i>View All Vehicles
                            </a>
                            <a href="{{ route('admin.vehicles.create') }}" class="btn btn-success">
                                <i class="fas fa-plus me-2"></i>Add New Vehicle
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Pagination -->
                @if(method_exists($vehicles, 'links'))
                    <div class="row mt-4">
                        <div class="col-12 d-flex justify-content-center">
                            {{ $vehicles->appends(request()->query())->links() }}
                        </div>
                    </div>
                @endif
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

<script>
    // View Toggle Functionality
    document.getElementById('gridView').addEventListener('change', function() {
        if (this.checked) {
            document.getElementById('vehicleGridView').classList.remove('d-none');
            document.getElementById('vehicleListView').classList.add('d-none');
        }
    });

    document.getElementById('listView').addEventListener('change', function() {
        if (this.checked) {
            document.getElementById('vehicleGridView').classList.add('d-none');
            document.getElementById('vehicleListView').classList.remove('d-none');
        }
    });

    // Delete confirmation
    function confirmDelete(vehicleId, vehicleName) {
        document.getElementById('deleteVehicleName').textContent = vehicleName;
        document.getElementById('deleteForm').action = `/admin/vehicles/${vehicleId}`;
        
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }

    function exportVehicles() {
        alert('Export functionality would be implemented here. This would generate a CSV/Excel file with vehicle data.');
    }

    function showMaintenanceReport() {
        alert('Maintenance report functionality would show vehicles due for maintenance, maintenance history, etc.');
    }
</script>
@endsection