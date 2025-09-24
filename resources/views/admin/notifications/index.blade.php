@extends('admin')

@section('title', 'Admin Notifications')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-bell"></i> Admin Notifications
        </h1>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary" onclick="markAllAsRead()">
                <i class="fas fa-check-double"></i> Mark All Read
            </button>
            <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-refresh"></i> Refresh
            </a>
        </div>
    </div>

    <!-- Notification Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Notifications</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $counts['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bell fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Unread</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $counts['unread'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">High Priority</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $counts['high_priority'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Action Required</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $counts['action_required'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Filter Notifications</h6>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#filterCollapse">
                <i class="fas fa-filter"></i> Filters
            </button>
        </div>
        <div class="collapse{{ request()->hasAny(['type', 'priority', 'status']) ? ' show' : '' }}" id="filterCollapse">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.notifications.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">All Types</option>
                            <option value="new_customer" {{ request('type') === 'new_customer' ? 'selected' : '' }}>New Customer</option>
                            <option value="booking_status_change" {{ request('type') === 'booking_status_change' ? 'selected' : '' }}>Booking Update</option>
                            <option value="security_alert" {{ request('type') === 'security_alert' ? 'selected' : '' }}>Security Alert</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="priority" class="form-label">Priority</label>
                        <select class="form-select" id="priority" name="priority">
                            <option value="">All Priorities</option>
                            <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>Unread</option>
                            <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Read</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Notifications</h6>
        </div>
        <div class="card-body p-0">
            @if($notifications->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($notifications as $notification)
                        <div class="list-group-item notification-item {{ !$notification->is_read ? 'notification-unread' : '' }}" 
                             data-notification-id="{{ $notification->id }}">
                            <div class="d-flex w-100 justify-content-between">
                                <div class="d-flex align-items-start">
                                    <!-- Notification Icon -->
                                    <div class="notification-icon me-3">
                                        @switch($notification->type)
                                            @case('new_customer')
                                                <i class="fas fa-user-plus text-success fa-lg"></i>
                                                @break
                                            @case('booking_status_change')
                                                <i class="fas fa-calendar-alt text-info fa-lg"></i>
                                                @break
                                            @case('security_alert')
                                                <i class="fas fa-shield-alt text-danger fa-lg"></i>
                                                @break
                                            @default
                                                <i class="fas fa-bell text-secondary fa-lg"></i>
                                        @endswitch
                                    </div>

                                    <!-- Notification Content -->
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center">
                                            <h6 class="mb-1">{{ $notification->title }}</h6>
                                            
                                            <!-- Priority Badge -->
                                            <span class="badge ms-2
                                                @switch($notification->priority)
                                                    @case('high') badge-danger @break
                                                    @case('medium') badge-warning @break
                                                    @case('low') badge-secondary @break
                                                    @default badge-primary
                                                @endswitch
                                            ">
                                                {{ ucfirst($notification->priority) }} Priority
                                            </span>

                                            @if($notification->action_required)
                                                <span class="badge badge-info ms-1">
                                                    <i class="fas fa-tasks"></i> Action Required
                                                </span>
                                            @endif

                                            @if(!$notification->is_read)
                                                <span class="badge badge-primary ms-1">New</span>
                                            @endif
                                        </div>
                                        <p class="mb-2 text-muted">{{ $notification->message }}</p>
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> 
                                            {{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="notification-actions">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.notifications.show', $notification->id) }}" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(!$notification->is_read)
                                            <button type="button" class="btn btn-outline-success btn-sm" 
                                                    onclick="markAsRead({{ $notification->id }})">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                                onclick="deleteNotification({{ $notification->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="card-footer">
                    {{ $notifications->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No notifications found</h5>
                    <p class="text-muted">There are no notifications matching your current filters.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.notification-unread {
    background-color: #f8f9fc;
    border-left: 4px solid #4e73df;
}

.notification-item:hover {
    background-color: #f1f3f6;
}

.notification-icon {
    width: 40px;
    text-align: center;
}

.notification-actions {
    min-width: 120px;
}

@media (max-width: 768px) {
    .notification-actions {
        min-width: auto;
    }
    
    .notification-item .d-flex {
        flex-direction: column;
    }
    
    .notification-actions {
        margin-top: 10px;
        align-self: flex-end;
    }
}
</style>

<script>
function markAsRead(notificationId) {
    fetch(`/admin/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove unread styling
            const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
            notificationElement.classList.remove('notification-unread');
            
            // Remove "New" badge and mark as read button
            const newBadge = notificationElement.querySelector('.badge-primary');
            if (newBadge && newBadge.textContent.trim() === 'New') {
                newBadge.remove();
            }
            
            const markReadBtn = notificationElement.querySelector('.btn-outline-success');
            if (markReadBtn) {
                markReadBtn.remove();
            }

            // Show success message
            showAlert('success', 'Notification marked as read');
        } else {
            showAlert('error', data.message || 'Failed to mark notification as read');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred while marking notification as read');
    });
}

function markAllAsRead() {
    if (!confirm('Are you sure you want to mark all notifications as read?')) {
        return;
    }

    fetch('/admin/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the page to show updated status
            window.location.reload();
        } else {
            showAlert('error', data.message || 'Failed to mark all notifications as read');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred while marking notifications as read');
    });
}

function deleteNotification(notificationId) {
    if (!confirm('Are you sure you want to delete this notification?')) {
        return;
    }

    fetch(`/admin/notifications/${notificationId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove notification from DOM
            const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
            notificationElement.remove();
            
            showAlert('success', 'Notification deleted successfully');
        } else {
            showAlert('error', data.message || 'Failed to delete notification');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred while deleting notification');
    });
}

function showAlert(type, message) {
    // Create Bootstrap alert
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show`;
    alert.innerHTML = `
        <strong>${type === 'success' ? 'Success!' : 'Error!'}</strong> ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    `;
    
    // Insert at top of container
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alert, container.firstChild);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        alert.remove();
    }, 5000);
}
</script>
@endsection