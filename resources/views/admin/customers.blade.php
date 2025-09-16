@extends('admin')

@section('title', 'Customer Management - RentWheels Admin')

@section('content')
<style>
    body {
        font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #dc3545 0%, #6f42c1 50%, #fd7e14 100%);
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

    .status-active {
        background: linear-gradient(45deg, #28a745, #20c997);
        color: white;
    }
    .status-suspended {
        background: linear-gradient(45deg, #dc3545, #c82333);
        color: white;
    }

    .action-btn {
        padding: 0.4rem 0.8rem;
        border-radius: 8px;
        border: none;
        margin: 0 0.2rem;
        transition: all 0.3s ease;
    }

    .btn-edit {
        background: #007bff;
        color: white;
    }
    .btn-edit:hover {
        background: #0056b3;
        transform: translateY(-1px);
    }

    .btn-delete {
        background: #dc3545;
        color: white;
    }
    .btn-delete:hover {
        background: #c82333;
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
</style>

<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin"></i>
    </div>
</div>

<!-- Flash Messages -->
@if(session('success'))
<div class="container mt-3">
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
@endif

@if(session('error'))
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
                            <i class="fas fa-users me-3"></i>
                            Customer Management
                        </h1>
                        <p class="lead mb-0">
                            Manage all registered customers, view details, and control access permissions.
                        </p>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="display-1">
                            <i class="fas fa-user-cog"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Statistics -->
            <div class="stats-row">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon text-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <h4 id="totalCustomers">{{ $totalUsers }}</h4>
                            <p class="text-muted mb-0">Total Customers</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon text-success">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <h4 id="activeCustomers">{{ $activeUsers }}</h4>
                            <p class="text-muted mb-0">Active Customers</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon text-danger">
                                <i class="fas fa-user-times"></i>
                            </div>
                            <h4 id="suspendedCustomers">{{ $suspendedUsers }}</h4>
                            <p class="text-muted mb-0">Suspended</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="search-filter-section">
                <form method="GET" action="{{ route('admin.customers') }}" id="filterForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search Customers</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Search by name or email..." 
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="sort" class="form-label">Sort By</label>
                            <select class="form-select" id="sort" name="sort">
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                                <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name A-Z</option>
                                <option value="email" {{ request('sort') == 'email' ? 'selected' : '' }}>Email A-Z</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i>Filter
                                </button>
                                <a href="{{ route('admin.customers') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Clear
                                </a>
                                <button type="button" class="btn btn-success ms-auto" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                                    <i class="fas fa-user-plus me-1"></i>Add Customer
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Customers Table -->
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Join Date</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr id="customer-row-{{ $user->id }}">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="customer-avatar">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong id="customer-name-{{ $user->id }}">{{ $user->name }}</strong>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span id="customer-email-{{ $user->id }}">{{ $user->email }}</span>
                            </td>
                            <td>
                                <span class="status-badge status-{{ strtolower($user->status ?? 'active') }}" id="status-{{ $user->id }}">
                                    {{ ucfirst($user->status ?? 'Active') }}
                                </span>
                            </td>
                            <td>
                                <span>{{ $user->created_at->format('M d, Y') }}</span>
                                <br>
                                <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                <span>{{ $user->updated_at->format('M d, Y') }}</span>
                                <br>
                                <small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <button class="action-btn btn-edit" onclick="editCustomer({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->email }}', '{{ $user->status ?? 'active' }}')" title="Edit Customer Status">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn btn-delete" onclick="deleteCustomer({{ $user->id }}, '{{ addslashes($user->name) }}')" title="Delete Customer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No customers found matching your criteria</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Add pagination -->
            <div class="p-3 d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} results
                </small>
                {{ $users->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-edit me-2"></i>Edit Customer Status
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editCustomerForm">
                    <input type="hidden" id="editCustomerId">
                    <input type="hidden" id="editCustomerName">
                    <input type="hidden" id="editCustomerEmail">
                    <div class="mb-3">
                        <label for="editCustomerStatus" class="form-label">Status</label>
                        <select class="form-select" id="editCustomerStatus">
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveCustomerChanges()">
                    <i class="fas fa-save me-1"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>Add New Customer
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addCustomerForm">
                    <div class="mb-3">
                        <label for="newCustomerName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="newCustomerName" required>
                    </div>
                    <div class="mb-3">
                        <label for="newCustomerEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="newCustomerEmail" required>
                    </div>
                    <div class="mb-3">
                        <label for="newCustomerPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="newCustomerPassword" required>
                    </div>
                    <div class="mb-3">
                        <label for="newCustomerStatus" class="form-label">Status</label>
                        <select class="form-select" id="newCustomerStatus">
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="addNewCustomer()">
                    <i class="fas fa-user-plus me-1"></i>Create Customer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                     '{{ csrf_token() }}';

    function showLoading() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }

    function editCustomer(id, name, email, status) {
        document.getElementById('editCustomerId').value = id;
        document.getElementById('editCustomerName').value = name;
        document.getElementById('editCustomerEmail').value = email;
        document.getElementById('editCustomerStatus').value = status;
        const modal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
        modal.show();
    }

    function saveCustomerChanges() {
        const id = document.getElementById('editCustomerId').value;
        const name = document.getElementById('editCustomerName').value;
        const email = document.getElementById('editCustomerEmail').value;
        const status = document.getElementById('editCustomerStatus').value;

        showLoading();

        fetch(`/admin/customers/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                name: name,
                email: email,
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('editCustomerModal'));
                modal.hide();
                
                const statusBadge = document.getElementById(`status-${id}`);
                statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                statusBadge.className = `status-badge status-${status}`;
                
                showAlert('Customer status updated successfully!', 'success');
            } else {
                showAlert(data.message || 'Error updating customer', 'danger');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showAlert('Error updating customer', 'danger');
        });
    }

    function deleteCustomer(id, name) {
        if (confirm(`Are you sure you want to delete customer "${name}"? This action cannot be undone.`)) {
            showLoading();

            fetch(`/admin/customers/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    document.getElementById(`customer-row-${id}`).remove();
                    
                    updateStats();
                    
                    showAlert('Customer deleted successfully!', 'success');
                } else {
                    showAlert(data.message || 'Error deleting customer', 'danger');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                showAlert('Error deleting customer', 'danger');
            });
        }
    }

    function addNewCustomer() {
        const name = document.getElementById('newCustomerName').value;
        const email = document.getElementById('newCustomerEmail').value;
        const password = document.getElementById('newCustomerPassword').value;
        const status = document.getElementById('newCustomerStatus').value;
        
        if (!name || !email || !password) {
            alert('Please fill in all required fields');
            return;
        }

        showLoading();

        fetch('/admin/customers', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                name: name,
                email: email,
                password: password,
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('addCustomerModal'));
                modal.hide();
                
                document.getElementById('addCustomerForm').reset();
                
                showAlert('Customer created successfully!', 'success');
                
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showAlert(data.message || 'Error creating customer', 'danger');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showAlert('Error creating customer', 'danger');
        });
    }

    function updateStats() {
        const totalCustomers = document.getElementById('totalCustomers');
        if (totalCustomers) {
            totalCustomers.textContent = parseInt(totalCustomers.textContent) - 1;
        }
    }

    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>${message}
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
    document.getElementById('sort').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
</script>
@endsection