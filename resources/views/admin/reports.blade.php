@extends('admin')

@section('title', 'Reports & Analytics - RentWheels Admin')

@section('content')
<style>
    body {
        font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #ffffff;
        min-height: 100vh;
    }

    .admin-container {
        padding: 2rem 0;
        background: #f8f9fa;
    }

    .admin-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        border: 3px solid #dee2e6;
        margin-bottom: 2rem;
    }

    .admin-header {
        background: linear-gradient(135deg, #dc3545, #6f42c1);
        color: white;
        padding: 2rem;
        border-radius: 17px 17px 0 0;
        margin: -3px -3px 0 -3px;
    }

    .stats-row {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 2px solid #e9ecef;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        border: 2px solid #e9ecef;
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        border-color: #dc3545;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.18);
    }

    .stat-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .chart-container {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        border: 3px solid #e9ecef;
        position: relative;
        height: 400px;
    }

    .chart-container:hover {
        border-color: #dc3545;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.18);
    }

    .chart-title {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: #343a40;
        border-bottom: 2px solid #dc3545;
        padding-bottom: 0.5rem;
        display: inline-block;
    }

    .filter-section {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        border: 3px solid #e9ecef;
    }

    .report-tabs {
        background: white;
        border-radius: 15px;
        padding: 1rem;
        margin-bottom: 2rem;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        border: 3px solid #e9ecef;
    }

    .nav-tabs .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 500;
        border-radius: 8px;
        margin-right: 0.5rem;
    }

    .nav-tabs .nav-link.active {
        background: linear-gradient(135deg, #dc3545, #6f42c1);
        color: white;
    }

    .table-responsive {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        border: 2px solid #f8f9fa;
    }

    .table thead th {
        background: linear-gradient(135deg, #343a40, #495057);
        color: white;
        border: none;
        padding: 1rem;
        font-weight: 600;
    }

    .table tbody tr {
        background: #ffffff;
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

    .form-control:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    .metric-card {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        border: 2px solid #dee2e6;
    }

    .metric-value {
        font-size: 2rem;
        font-weight: bold;
    }

    .metric-change {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .export-btn {
        background: linear-gradient(135deg, #28a745, #20c997);
        border: none;
        color: white;
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .export-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        color: white;
    }

    .alert {
        border: 2px solid;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .container {
        max-width: 1200px;
    }

    .tab-content .admin-card {
        background: #ffffff;
        border: 3px solid #e9ecef;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .card-body {
        padding: 1.5rem;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

<!-- Admin Content -->
<div class="admin-container">
    <div class="container">
        <!-- Header Card -->
        <div class="admin-card">
            <div class="admin-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="h2 mb-3">
                            <i class="fas fa-chart-bar me-3"></i>
                            Reports & Analytics
                        </h1>
                        <p class="lead mb-0">
                            Comprehensive insights into your rental business performance, user analytics, and vehicle utilization.
                        </p>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="display-1">
                            <i class="fas fa-analytics"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="stats-row">
                <div class="row g-4 justify-content-center">
                    <div class="col-lg-6 col-md-6">
                        <div class="stat-card">
                            <div class="stat-icon text-primary">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h4 id="totalRevenue">RM{{ number_format($stats['totalRevenue'], 2) }}</h4>
                            <p class="text-muted mb-0">Total Revenue</p>
                            <small class="text-success">+15% vs last month</small>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="stat-card">
                            <div class="stat-icon text-success">
                                <i class="fas fa-car-alt"></i>
                            </div>
                            <h4 id="totalVehicles">{{ $stats['totalVehicles'] }}</h4>
                            <p class="text-muted mb-0">Total Vehicles</p>
                            <small class="text-info">Available in fleet</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <form id="reportFilters" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="dateFrom" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="dateFrom" name="date_from" 
                               value="{{ $dateFrom->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="dateTo" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="dateTo" name="date_to" 
                               value="{{ $dateTo->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="reportType" class="form-label">Report Type</label>
                        <select class="form-select" id="reportType" name="report_type">
                            <option value="overview">Overview</option>
                            <option value="users">Users Only</option>
                            <option value="vehicles">Vehicles Only</option>
                            <option value="bookings">Bookings Only</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary" onclick="updateReports()">
                                <i class="fas fa-sync me-1"></i>Update Reports
                            </button>
                            <button type="button" class="btn export-btn" onclick="exportPDF()">
                                <i class="fas fa-file-pdf me-1"></i>Export PDF
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Report Tabs -->
            <div class="report-tabs">
                <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button">
                            <i class="fas fa-chart-pie me-2"></i>Overview
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button">
                            <i class="fas fa-users me-2"></i>Users
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="vehicles-tab" data-bs-toggle="tab" data-bs-target="#vehicles" type="button">
                            <i class="fas fa-car me-2"></i>Vehicles
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bookings-tab" data-bs-toggle="tab" data-bs-target="#bookings" type="button">
                            <i class="fas fa-calendar me-2"></i>Bookings
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tab Content -->
            <div class="tab-content" id="reportTabContent">
                <!-- Overview Tab -->
                <div class="tab-pane fade show active" id="overview" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="chart-container">
                                <div class="chart-title">
                                    <i class="fas fa-chart-line me-2 text-primary"></i>Revenue Trend
                                </div>
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="chart-container">
                                <div class="chart-title">
                                    <i class="fas fa-chart-pie me-2 text-success"></i>Booking Status Distribution
                                </div>
                                <canvas id="bookingStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users Tab -->
                <div class="tab-pane fade" id="users" role="tabpanel">
                    <!-- Top Users Table -->
                    <div class="admin-card mt-4">
                        <div class="card-body">
                            <h5 class="chart-title mb-3">Top Users by Bookings</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Email</th>
                                            <th>Total Bookings</th>
                                            <th>Total Spent</th>
                                            <th>Last Booking</th>
                                        </tr>
                                    </thead>
                                    <tbody id="topUsersTable">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vehicles Tab -->
                <div class="tab-pane fade" id="vehicles" role="tabpanel">
                    <div class="admin-card mt-4">
                        <div class="card-body">
                            <h5 class="chart-title mb-3">Vehicle Performance Analytics</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Vehicle</th>
                                            <th>Type</th>
                                            <th>Total Bookings</th>
                                            <th>Revenue Generated</th>
                                            <th>Utilization Rate</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="vehiclePerformanceTable">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bookings Tab -->
                <div class="tab-pane fade" id="bookings" role="tabpanel">
                    <div class="admin-card mt-4">
                        <div class="card-body">
                            <h5 class="chart-title mb-3">Recent Booking Analytics</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>User</th>
                                            <th>Vehicle</th>
                                            <th>Duration</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recentBookingsTable">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
            const chartData = @json($chartData);
            const tableData = @json($tableData);

    let revenueChart, bookingStatusChart;

    document.addEventListener('DOMContentLoaded', function () {
        initializeCharts();
        loadTableData();
    });

    function initializeCharts() {
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: chartData.revenueData.labels,
                datasets: [{
                        label: 'Revenue (RM)',
                        data: chartData.revenueData.data,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return 'RM' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        const statusCtx = document.getElementById('bookingStatusChart').getContext('2d');
        bookingStatusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: chartData.bookingStatusData.labels,
                datasets: [{
                        data: chartData.bookingStatusData.data,
                        backgroundColor: ['#28a745', '#007bff', '#17a2b8', '#dc3545', '#ffc107']
                    }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    function loadTableData() {
        loadTopUsers();
        loadVehiclePerformance();
        loadRecentBookings();
    }

    function loadTopUsers() {
        const tableBody = document.getElementById('topUsersTable');
        let html = '';

        tableData.topUsers.forEach(user => {
            html += `
        <tr>
            <td><strong>${user.name}</strong></td>
            <td>${user.email}</td>
            <td><span class="badge bg-primary">${user.total_bookings || 0}</span></td>
            <td><strong class="text-success">RM${parseFloat(user.total_spent || 0).toLocaleString()}</strong></td>
            <td><small class="text-muted">${new Date(user.updated_at).toLocaleDateString()}</small></td>
        </tr>
    `;
        });

        if (html === '') {
            html = '<tr><td colspan="5" class="text-center text-muted">No data available</td></tr>';
        }

        tableBody.innerHTML = html;
    }

    function loadVehiclePerformance() {
        const tableBody = document.getElementById('vehiclePerformanceTable');
        let html = '';

        tableData.vehiclePerformance.forEach(vehicle => {
            const utilizationRate = parseFloat(vehicle.utilization_rate || 0);
            html += `
        <tr>
            <td><strong>${vehicle.make} ${vehicle.model}</strong></td>
            <td><span class="badge bg-secondary">${vehicle.type}</span></td>
            <td>${vehicle.total_bookings || 0}</td>
            <td><strong class="text-success">RM${parseFloat(vehicle.revenue_generated || 0).toLocaleString()}</strong></td>
            <td>
                <div class="progress" style="height: 20px;">
                    <div class="progress-bar" role="progressbar" style="width: ${utilizationRate}%">
                        ${utilizationRate}%
                    </div>
                </div>
            </td>
            <td>
                <span class="badge bg-${vehicle.status === 'available' ? 'success' : vehicle.status === 'rented' ? 'warning' : 'secondary'}">
                    ${vehicle.status.charAt(0).toUpperCase() + vehicle.status.slice(1)}
                </span>
            </td>
        </tr>
    `;
        });

        if (html === '') {
            html = '<tr><td colspan="6" class="text-center text-muted">No data available</td></tr>';
        }

        tableBody.innerHTML = html;
    }

    function loadRecentBookings() {
        const tableBody = document.getElementById('recentBookingsTable');
        let html = '';

        tableData.recentBookings.forEach(booking => {
            html += `
        <tr>
            <td><code>BK${String(booking.id).padStart(3, '0')}</code></td>
            <td>${booking.user ? booking.user.name : 'Unknown'}</td>
            <td>${booking.vehicle ? `${booking.vehicle.make} ${booking.vehicle.model}` : 'N/A'}</td>
            <td>${calculateDuration(booking.start_date, booking.end_date)}</td>
            <td><strong class="text-success">RM${parseFloat(booking.total_amount || 0).toLocaleString()}</strong></td>
            <td>
                <span class="badge bg-${getStatusClass(booking.status)}">
                    ${booking.status.charAt(0).toUpperCase() + booking.status.slice(1)}
                </span>
            </td>
        </tr>
    `;
        });

        if (html === '') {
            html = '<tr><td colspan="6" class="text-center text-muted">No data available</td></tr>';
        }

        tableBody.innerHTML = html;
    }

    function calculateDuration(startDate, endDate) {
        if (!startDate || !endDate)
            return 'N/A';
        const start = new Date(startDate);
        const end = new Date(endDate);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return `${diffDays} day${diffDays !== 1 ? 's' : ''}`;
    }

    function getStatusClass(status) {
        const statusClasses = {
            'active': 'primary',
            'completed': 'success',
            'confirmed': 'info',
            'cancelled': 'danger',
            'pending': 'warning'
        };
        return statusClasses[status] || 'secondary';
    }

// Update Reports Function
    function updateReports() {
        showLoadingOverlay();

        const formData = {
            date_from: document.getElementById('dateFrom').value,
            date_to: document.getElementById('dateTo').value,
            report_type: document.getElementById('reportType').value,
            _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };

        fetch('/admin/reports/filter', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': formData._token
            },
            body: JSON.stringify(formData)
        })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateStatistics(data.stats);

                        updateCharts(data.chartData);

                        updateTables(data.tableData);

                        showMessage('Reports updated successfully!', 'success');
                    } else {
                        showMessage('Error updating reports: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('Error updating reports. Please try again.', 'error');
                })
                .finally(() => {
                    hideLoadingOverlay();
                });
    }

    function updateStatistics(stats) {
        document.getElementById('totalRevenue').textContent = 'RM' + parseFloat(stats.totalRevenue).toLocaleString('en-MY', {minimumFractionDigits: 2});
        document.getElementById('totalVehicles').textContent = stats.totalVehicles;
    }

    function updateCharts(newChartData) {
        if (revenueChart) {
            revenueChart.data.labels = newChartData.revenueData.labels;
            revenueChart.data.datasets[0].data = newChartData.revenueData.data;
            revenueChart.update();
        }

        if (bookingStatusChart) {
            bookingStatusChart.data.labels = newChartData.bookingStatusData.labels;
            bookingStatusChart.data.datasets[0].data = newChartData.bookingStatusData.data;
            bookingStatusChart.update();
        }
    }

    function updateTables(newTableData) {
        const topUsersTable = document.getElementById('topUsersTable');
        if (topUsersTable && newTableData.topUsers) {
            let html = '';
            newTableData.topUsers.forEach(user => {
                html += `
                <tr>
                    <td><strong>${user.name}</strong></td>
                    <td>${user.email}</td>
                    <td><span class="badge bg-primary">${user.total_bookings || 0}</span></td>
                    <td><strong class="text-success">RM${parseFloat(user.total_spent || 0).toLocaleString()}</strong></td>
                    <td><small class="text-muted">${new Date(user.updated_at).toLocaleDateString()}</small></td>
                </tr>
            `;
            });
            topUsersTable.innerHTML = html || '<tr><td colspan="5" class="text-center text-muted">No data available</td></tr>';
        }

        const vehiclePerformanceTable = document.getElementById('vehiclePerformanceTable');
        if (vehiclePerformanceTable && newTableData.vehiclePerformance) {
            let html = '';
            newTableData.vehiclePerformance.forEach(vehicle => {
                const utilizationRate = parseFloat(vehicle.utilization_rate || 0);
                html += `
                <tr>
                    <td><strong>${vehicle.make} ${vehicle.model}</strong></td>
                    <td><span class="badge bg-secondary">${vehicle.type}</span></td>
                    <td>${vehicle.total_bookings || 0}</td>
                    <td><strong class="text-success">RM${parseFloat(vehicle.revenue_generated || 0).toLocaleString()}</strong></td>
                    <td>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar" role="progressbar" style="width: ${utilizationRate}%">
                                ${utilizationRate}%
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-${vehicle.status === 'available' ? 'success' : vehicle.status === 'rented' ? 'warning' : 'secondary'}">
                            ${vehicle.status.charAt(0).toUpperCase() + vehicle.status.slice(1)}
                        </span>
                    </td>
                </tr>
            `;
            });
            vehiclePerformanceTable.innerHTML = html || '<tr><td colspan="6" class="text-center text-muted">No data available</td></tr>';
        }

        // Update recent bookings table
        const recentBookingsTable = document.getElementById('recentBookingsTable');
        if (recentBookingsTable && newTableData.recentBookings) {
            let html = '';
            newTableData.recentBookings.forEach(booking => {
                html += `
                <tr>
                    <td><code>BK${String(booking.id).padStart(3, '0')}</code></td>
                    <td>${booking.user ? booking.user.name : 'Unknown'}</td>
                    <td>${booking.vehicle ? `${booking.vehicle.make} ${booking.vehicle.model}` : 'N/A'}</td>
                    <td>${calculateDuration(booking.start_date, booking.end_date)}</td>
                    <td><strong class="text-success">RM${parseFloat(booking.total_amount || 0).toLocaleString()}</strong></td>
                    <td>
                        <span class="badge bg-${getStatusClass(booking.status)}">
                            ${booking.status.charAt(0).toUpperCase() + booking.status.slice(1)}
                        </span>
                    </td>
                </tr>
            `;
            });
            recentBookingsTable.innerHTML = html || '<tr><td colspan="6" class="text-center text-muted">No data available</td></tr>';
        }
    }

    function showLoadingOverlay() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.style.display = 'flex';
        }
    }

    function hideLoadingOverlay() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }

    function showMessage(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 10000; min-width: 300px;';
        alertDiv.innerHTML = `
        <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : 'check-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

        document.body.appendChild(alertDiv);

        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    function exportPDF() {
        showLoadingOverlay();

        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = document.getElementById('dateTo').value;
        const reportType = document.getElementById('reportType').value;

        const params = new URLSearchParams({
            date_from: dateFrom,
            date_to: dateTo,
            report_type: reportType
        });

        const downloadUrl = `/admin/reports/export?${params.toString()}`;

        const link = document.createElement('a');
        link.href = downloadUrl;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        setTimeout(() => {
            hideLoadingOverlay();
            showMessage('PDF report is being generated and downloaded!', 'success');
        }, 1500);
    }

    function getCsrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    }
</script>
@endsection