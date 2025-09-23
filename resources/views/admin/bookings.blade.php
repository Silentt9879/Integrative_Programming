@extends('admin')

@section('title', 'Bookings Management - RentWheels Admin')

@section('content')
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: rgba(255, 255, 255, 0.95);
            min-height: 100vh;
        }

        .admin-container {
            padding: 2rem 0;
        }

        .admin-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 2rem;
        }

        .admin-header {
            background: linear-gradient(135deg, #dc3545, #6f42c1);
            color: white;
            padding: 2rem;
            border-radius: 20px 20px 0 0;
            margin: -1px -1px 0 -1px;
        }

        .stats-row {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .customer-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 0.75rem;
        }

        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-confirmed {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
        }

        .status-active {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
        }

        .status-pending {
            background: linear-gradient(45deg, #ffc107, #fd7e14);
            color: white;
        }

        .status-cancelled {
            background: linear-gradient(45deg, #dc3545, #c82333);
            color: white;
        }

        .status-completed {
            background: linear-gradient(45deg, #6f42c1, #007bff);
            color: white;
        }

        .action-btn {
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            border: none;
            margin: 0 0.2rem;
            transition: all 0.3s ease;
        }

        .btn-view {
            background: #17a2b8;
            color: white;
        }

        .btn-return {
            background: #28a745;
            color: white;
        }

        .btn-return:hover {
            background: #218838;
            transform: translateY(-1px);
        }

        .search-filter-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .table thead th {
            background: linear-gradient(135deg, #343a40, #495057);
            color: white;
            border: none;
            padding: 1rem;
            font-weight: 600;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background: rgba(0, 123, 255, 0.05);
            transform: scale(1.01);
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-spinner {
            color: white;
            font-size: 3rem;
        }

        .modal-header {
            background: linear-gradient(135deg, #dc3545, #6f42c1);
            color: white;
        }

        .form-control:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        .vehicle-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .vehicle-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #28a745, #20c997);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
    </style>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session('success'))
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    <!-- Admin Content -->
    <div class="admin-container">
        <div class="container">
            <!-- Header Card -->
            <div class="admin-card">
                <div class="admin-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h2 mb-3">
                                <i class="fas fa-calendar-check me-3"></i>
                                Bookings Management
                            </h1>
                            <p class="lead mb-0">
                                Monitor all vehicle bookings, manage rental status, and track customer reservations.
                            </p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="display-1">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Statistics -->
                <div class="stats-row">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon text-primary">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <h4 id="totalBookings">{{ $totalBookings ?? 0 }}</h4>
                                <p class="text-muted mb-0">Total Bookings</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon text-warning">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <h4 id="pendingBookings">{{ $pendingBookings ?? 0 }}</h4>
                                <p class="text-muted mb-0">Pending</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon text-success">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h4 id="confirmedBookings">{{ $confirmedBookings ?? 0 }}</h4>
                                <p class="text-muted mb-0">Confirmed</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon text-info">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <h4 id="totalRevenue">RM{{ number_format($totalRevenue ?? 0, 2) }}</h4>
                                <p class="text-muted mb-0">Total Revenue</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search and Filter Section -->
                <div class="search-filter-section">
                    <form method="GET" action="{{ route('admin.bookings') }}" id="filterForm">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search Bookings</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="search" name="search"
                                        placeholder="Customer name, vehicle..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                    </option>
                                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>
                                        Confirmed</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                        Completed</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                        Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="date_range" class="form-label">Date Range</label>
                                <select class="form-select" id="date_range" name="date_range">
                                    <option value="">All Dates</option>
                                    <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today
                                    </option>
                                    <option value="week" {{ request('date_range') == 'week' ? 'selected' : '' }}>This
                                        Week</option>
                                    <option value="month" {{ request('date_range') == 'month' ? 'selected' : '' }}>This
                                        Month</option>
                                    <option value="year" {{ request('date_range') == 'year' ? 'selected' : '' }}>This
                                        Year</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="sort" class="form-label">Sort By</label>
                                <select class="form-select" id="sort" name="sort">
                                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest
                                        First</option>
                                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest
                                        First</option>
                                    <option value="amount_high" {{ request('sort') == 'amount_high' ? 'selected' : '' }}>
                                        Amount: High to Low</option>
                                    <option value="amount_low" {{ request('sort') == 'amount_low' ? 'selected' : '' }}>
                                        Amount: Low to High</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter me-1"></i>Filter
                                    </button>
                                    <a href="{{ route('admin.bookings') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>Clear
                                    </a>
                                    <a href="{{ route('admin.bookings.export') }}" class="btn btn-success ms-auto">
                                        <i class="fas fa-download me-1"></i>Export PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Bookings Table -->
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Booking Period</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bookings ?? [] as $booking)
                                <tr id="booking-row-{{ $booking->id ?? rand(1, 999) }}">
                                    <td>
                                        <strong>#BK{{ str_pad($booking->id ?? rand(1, 999), 4, '0', STR_PAD_LEFT) }}</strong>
                                        <br>
                                        <small
                                            class="text-muted">{{ $booking->created_at ?? now()->format('M d, Y') }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="customer-avatar">
                                                {{ strtoupper(substr($booking->user->name ?? 'Adam', 0, 1)) }}
                                            </div>
                                            <div>
                                                <strong>{{ $booking->user->name ?? 'Adam' }}</strong>
                                                <br>
                                                <small
                                                    class="text-muted">{{ $booking->user->email ?? 'adam@gmail.com' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="vehicle-info">
                                            <div class="vehicle-icon">
                                                <i class="fas fa-car"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $booking->vehicle->make ?? 'Perodua' }}
                                                    {{ $booking->vehicle->model ?? 'Myvi' }}</strong>
                                                <br>
                                                <small
                                                    class="text-muted">{{ $booking->vehicle->license_plate ?? 'ABC123' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $booking->start_date ?? now()->format('M d, Y') }}</strong>
                                        <br>
                                        <small class="text-muted">to
                                            {{ $booking->end_date ?? now()->addDays(3)->format('M d, Y') }}</small>
                                        <br>
                                    </td>
                                    <td>
                                        <h5 class="text-success mb-0">
                                            RM{{ number_format($booking->total_amount ?? 369, 2) }}</h5>
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ strtolower($booking->status ?? 'pending') }}"
                                            id="booking-status-{{ $booking->id ?? rand(1, 999) }}">
                                            {{ ucfirst($booking->status ?? 'Pending') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <button class="action-btn btn-view"
                                                onclick="viewBooking({{ $booking->id ?? rand(1, 999) }})"
                                                title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if (
                                                ($booking->status ?? 'pending') === 'confirmed' ||
                                                    !isset($booking) ||
                                                    ($booking->status ?? 'pending') === 'pending')
                                                <button class="action-btn btn-success pickup-btn"
                                                    data-booking-id="{{ $booking->id ?? rand(1, 999) }}"
                                                    onclick="pickupVehicle({{ $booking->id ?? rand(1, 999) }})"
                                                    title="Vehicle Picked Up">
                                                    <i class="fas fa-car"></i>
                                                </button>
                                            @endif

                                            @if ($booking->status === 'active')
                                                <button class="action-btn btn-return"
                                                    onclick="returnVehicle({{ $booking->id ?? rand(1, 999) }})"
                                                    title="Return Vehicle">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <!-- Sample data for demonstration -->
                                <tr id="booking-row-1">
                                    <td>
                                        <strong>#BK0001</strong>
                                        <br>
                                        <small class="text-muted">{{ now()->subHours(3)->format('M d, Y') }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="customer-avatar">A</div>
                                            <div>
                                                <strong>Adam</strong>
                                                <br>
                                                <small class="text-muted">adam@gmail.com</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="vehicle-info">
                                            <div class="vehicle-icon">
                                                <i class="fas fa-car"></i>
                                            </div>
                                            <div>
                                                <strong>Perodua Myvi</strong>
                                                <br>
                                                <small class="text-muted">ABC123</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ now()->format('M d, Y') }}</strong>
                                        <br>
                                        <small class="text-muted">to {{ now()->addDays(3)->format('M d, Y') }}</small>
                                        <br>
                                    </td>
                                    <td>
                                        <h5 class="text-success mb-0">RM369.00</h5>
                                    </td>
                                    <td>
                                        <span class="status-badge status-pending" id="booking-status-1">
                                            Pending
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <button class="action-btn btn-view" onclick="viewBooking(1)"
                                                title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-3 d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Showing {{ $bookings->firstItem() ?? 1 }} to {{ $bookings->lastItem() ?? 1 }} of
                        {{ $bookings->total() ?? 1 }} results
                    </small>
                    {{ $bookings->appends(request()->query())->links() ?? '' }}
                </div>
            </div>
        </div>
    </div>

    <!-- View Booking Details Modal -->
    <div class="modal fade" id="viewBookingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-eye me-2"></i>Booking Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="bookingDetailsContent">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }

        function viewBooking(id) {
            showLoading();

            fetch(`/admin/bookings/${id}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    hideLoading();

                    if (data.success) {
                        document.getElementById('bookingDetailsContent').innerHTML = data.html;
                        const modal = new bootstrap.Modal(document.getElementById('viewBookingModal'));
                        modal.show();
                    } else {
                        showAlert('Error loading booking details', 'error');
                    }
                })
                .catch(error => {
                    hideLoading();
                    showAlert('Error loading booking details: ' + error.message, 'error');
                });
        }

        function exportBookings() {
            showLoading();
            const search = document.getElementById('search').value;
            const status = document.getElementById('status').value;
            const dateRange = document.getElementById('date_range').value;
            const sort = document.getElementById('sort').value;
            let exportUrl = '{{ route('admin.bookings.export') }}?';
            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (status) params.append('status', status);
            if (dateRange) params.append('date_range', dateRange);
            if (sort) params.append('sort', sort);
            exportUrl += params.toString();
            const link = document.createElement('a');
            link.href = exportUrl;
            link.download = `bookings-export-${new Date().toISOString().split('T')[0]}.pdf`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            hideLoading();
            showAlert('PDF export started! Check your downloads folder.', 'success');
        }

        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;

            const icon = type === 'success' ? 'fa-check-circle' :
                type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';

            alertDiv.innerHTML = `
        <i class="fas ${icon} me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
            const container = document.querySelector('.container');
            container.insertBefore(alertDiv, container.firstChild);
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        document.getElementById('status').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
        document.getElementById('date_range').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
        document.getElementById('sort').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });

        function returnVehicle(id) {
            const modal = `
            <div class="modal fade" id="returnModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Return Vehicle</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="returnForm">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Actual Return Date & Time</label>
                                    <input type="datetime-local" class="form-control" name="actual_return_datetime" value="${new Date().toISOString().slice(0,16)}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Damage Charges (RM)</label>
                                    <input type="number" step="0.01" class="form-control" name="damage_charges" placeholder="0.00">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Vehicle Condition</label>
                                    <textarea class="form-control" name="return_condition" rows="2" placeholder="Describe vehicle condition upon return"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Additional Notes</label>
                                    <textarea class="form-control" name="notes" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">Process Return</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;

            document.body.insertAdjacentHTML('beforeend', modal);
            const returnModal = new bootstrap.Modal(document.getElementById('returnModal'));
            returnModal.show();

            document.getElementById('returnForm').onsubmit = function(e) {
                e.preventDefault();
                showLoading();

                const formData = new FormData(this);
                const data = Object.fromEntries(formData);

                fetch(`/admin/bookings/${id}/return`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            actual_return_datetime: data.actual_return_datetime,
                            damage_charges: data.damage_charges || 0,
                            return_notes: data.return_condition + ' ' + (data.notes || '')
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        hideLoading();
                        returnModal.hide();
                        document.getElementById('returnModal').remove();

                        if (data.success) {
                            showAlert('Vehicle returned successfully!', 'success');
                            // Update status badge to completed
                            const statusBadge = document.getElementById(`booking-status-${id}`);
                            if (statusBadge) {
                                statusBadge.className = 'status-badge status-completed';
                                statusBadge.textContent = 'Completed';
                            }
                            // Refresh page
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showAlert(data.message || 'Error processing return', 'error');
                        }
                    })
                    .catch(error => {
                        hideLoading();
                        returnModal.hide();
                        showAlert('Error processing return: ' + error.message, 'error');
                    });
            };
        }

        function pickupVehicle(id) {
            if (confirm('Confirm that customer has picked up the vehicle?')) {
                showLoading();

                fetch(`/admin/bookings/${id}/pickup`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        hideLoading();

                        if (data.success) {
                            showAlert('Vehicle pickup confirmed successfully!', 'success');
                            // Update status badge
                            const statusBadge = document.getElementById(`booking-status-${id}`);
                            if (statusBadge) {
                                statusBadge.className =
                                    'status-badge status-active'; // Changed from status-confirmed to status-active
                                statusBadge.textContent = 'Active';
                            }
                            // Refresh page
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showAlert(data.message || 'Error confirming pickup', 'error');
                        }
                    })
                    .catch(error => {
                        hideLoading();
                        showAlert('Error confirming pickup: ' + error.message, 'error');
                    });
            }
        }

        document.getElementById('sort').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    </script>
@endsection
