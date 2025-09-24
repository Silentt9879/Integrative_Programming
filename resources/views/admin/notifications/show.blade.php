@extends('admin')

@section('title', 'Notification Details')

@section('content')
<div class="container-fluid">
</br>
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-bell"></i> Notification Details
        </h1>
        <div class="btn-group">
            @if(!$notification->is_read)
                <button class="btn btn-success" onclick="markAsRead({{ $notification->id }})">
                    <i class="fas fa-check"></i> Mark as Read
                </button>
            @endif
            <button class="btn btn-danger" onclick="deleteNotification({{ $notification->id }})">
                <i class="fas fa-trash"></i> Delete
            </button>
            <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Notifications
            </a>
        </div>
    </div>

    <!-- Notification Details Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                @switch($notification->type)
                    @case('new_customer')
                        <i class="fas fa-user-plus text-success"></i> New Customer Registration
                        @break
                    @case('booking_status_change')
                        <i class="fas fa-calendar-alt text-info"></i> Booking Status Update
                        @break
                    @case('security_alert')
                        <i class="fas fa-shield-alt text-danger"></i> Security Alert
                        @break
                    @default
                        <i class="fas fa-bell text-secondary"></i> Notification
                @endswitch
            </h6>
            <div>
                <!-- Status Badges -->
                <span class="badge 
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
                    <span class="badge badge-info">
                        <i class="fas fa-tasks"></i> Action Required
                    </span>
                @endif
                
                @if($notification->is_read)
                    <span class="badge badge-success">Read</span>
                @else
                    <span class="badge badge-primary">Unread</span>
                @endif
            </div>
        </div>
        
        <div class="card-body">
            <!-- Main Notification Content -->
            <div class="row">
                <div class="col-md-8">
                    <h4 class="mb-3">{{ $notification->title }}</h4>
                    <p class="lead mb-4">{{ $notification->message }}</p>
                    
                    <!-- Notification Metadata -->
                    @if($notification->metadata && count($notification->metadata) > 0)
                        <div class="card bg-light mb-4">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Additional Details</h6>
                            </div>
                            <div class="card-body">
                                @foreach($notification->metadata as $key => $value)
                                    @if(!is_array($value) && !is_object($value))
                                        <div class="row mb-2">
                                            <div class="col-sm-4">
                                                <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                @if($key === 'ip_address')
                                                    <code>{{ $value }}</code>
                                                @elseif(Str::contains($key, 'time') && !is_null($value))
                                                    {{ \Carbon\Carbon::parse($value)->format('M d, Y h:i A') }}
                                                @else
                                                    {{ $value }}
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                
                <div class="col-md-4">
                    <!-- Notification Info Panel -->
                    <div class="card border-left-primary">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-clock"></i> Notification Info</h6>
                            <hr>
                            
                            <div class="mb-3">
                                <small class="text-muted">Created:</small><br>
                                <strong>{{ \Carbon\Carbon::parse($notification->created_at)->format('M d, Y h:i A') }}</strong><br>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}</small>
                            </div>
                            
                            @if($notification->updated_at != $notification->created_at)
                                <div class="mb-3">
                                    <small class="text-muted">Last Updated:</small><br>
                                    <strong>{{ \Carbon\Carbon::parse($notification->updated_at)->format('M d, Y h:i A') }}</strong>
                                </div>
                            @endif
                            
                            <div class="mb-3">
                                <small class="text-muted">Type:</small><br>
                                <span class="badge badge-outline-primary">{{ ucfirst(str_replace('_', ' ', $notification->type)) }}</span>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">Category:</small><br>
                                <span class="badge badge-outline-secondary">{{ ucfirst($notification->priority) }} Priority</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Related Data Section -->
            @if(isset($relatedData) && count($relatedData) > 0)
                <hr class="my-4">
                <h5><i class="fas fa-link"></i> Related Information</h5>
                
                <!-- Related User -->
                @if(isset($relatedData['user']))
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-user"></i> Customer Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <strong>Name:</strong> {{ $relatedData['user']->name }}
                                </div>
                                <div class="col-sm-6">
                                    <strong>Email:</strong> {{ $relatedData['user']->email }}
                                </div>
                                <div class="col-sm-6">
                                    <strong>Status:</strong> 
                                    <span class="badge badge-{{ $relatedData['user']->status === 'active' ? 'success' : 'warning' }}">
                                        {{ ucfirst($relatedData['user']->status) }}
                                    </span>
                                </div>
                                <div class="col-sm-6">
                                    <strong>Registered:</strong> {{ \Carbon\Carbon::parse($relatedData['user']->created_at)->format('M d, Y') }}
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('admin.customers') }}?search={{ $relatedData['user']->email }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-external-link-alt"></i> View Customer Details
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Related Booking -->
                @if(isset($relatedData['booking']))
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-calendar-alt"></i> Booking Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <strong>Booking ID:</strong> #{{ $relatedData['booking']->id }}
                                </div>
                                <div class="col-sm-6">
                                    <strong>Customer:</strong> {{ $relatedData['booking']->customer_name }}
                                </div>
                                <div class="col-sm-6">
                                    <strong>Vehicle:</strong> {{ $relatedData['booking']->make }} {{ $relatedData['booking']->model }}
                                </div>
                                <div class="col-sm-6">
                                    <strong>Status:</strong> 
                                    <span class="badge badge-primary">{{ ucfirst($relatedData['booking']->status) }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <strong>Amount:</strong> ${{ number_format($relatedData['booking']->total_amount, 2) }}
                                </div>
                                <div class="col-sm-6">
                                    <strong>Created:</strong> {{ \Carbon\Carbon::parse($relatedData['booking']->created_at)->format('M d, Y') }}
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('admin.bookings') }}?search={{ $relatedData['booking']->customer_name }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-external-link-alt"></i> View Booking Details
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
            
            <!-- Action Buttons -->
            @if($notification->action_required && !$notification->is_read)
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle"></i> Action Required</h6>
                    <p class="mb-3">This notification requires your attention. Please review the details above and take appropriate action.</p>
                    
                    @switch($notification->type)
                        @case('new_customer')
                            <a href="{{ route('admin.customers') }}" class="btn btn-warning">
                                <i class="fas fa-users"></i> Review Customer
                            </a>
                            @break
                        @case('booking_status_change')
                            <a href="{{ route('admin.bookings') }}" class="btn btn-warning">
                                <i class="fas fa-calendar-alt"></i> Review Booking
                            </a>
                            @break
                        @case('security_alert')
                            <a href="{{ route('admin.notifications.index') }}?type=security_alert" class="btn btn-warning">
                                <i class="fas fa-shield-alt"></i> Review Security Alerts
                            </a>
                            @break
                    @endswitch
                </div>
            @endif
        </div>
    </div>
</div>

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
            // Update the status badge
            const statusBadges = document.querySelectorAll('.badge-primary');
            statusBadges.forEach(badge => {
                if (badge.textContent.trim() === 'Unread') {
                    badge.textContent = 'Read';
                    badge.classList.remove('badge-primary');
                    badge.classList.add('badge-success');
                }
            });
            
            // Hide the mark as read button
            const markReadBtn = document.querySelector('.btn-success');
            if (markReadBtn && markReadBtn.textContent.includes('Mark as Read')) {
                markReadBtn.style.display = 'none';
            }
            
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
            // Redirect to notifications list
            window.location.href = '{{ route("admin.notifications.index") }}';
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