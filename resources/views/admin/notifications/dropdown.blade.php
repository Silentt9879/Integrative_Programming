@if($notifications->count() > 0)
    <!-- Dropdown Header -->
    <h6 class="dropdown-header">
        <i class="fas fa-bell"></i>
        Notifications Center
    </h6>

    <!-- Notification Items -->
    @foreach($notifications as $notification)
        <a class="dropdown-item d-flex align-items-center notification-dropdown-item" 
           href="{{ route('admin.notifications.show', $notification->id) }}">
            <div class="mr-3">
                <!-- Notification Type Icon -->
                @switch($notification->type)
                    @case('new_customer')
                        <div class="icon-circle bg-success">
                            <i class="fas fa-user-plus text-white"></i>
                        </div>
                        @break
                    @case('booking_status_change')
                        <div class="icon-circle bg-info">
                            <i class="fas fa-calendar-alt text-white"></i>
                        </div>
                        @break
                    @case('security_alert')
                        <div class="icon-circle bg-danger">
                            <i class="fas fa-shield-alt text-white"></i>
                        </div>
                        @break
                    @default
                        <div class="icon-circle bg-secondary">
                            <i class="fas fa-bell text-white"></i>
                        </div>
                @endswitch
            </div>
            <div class="flex-grow-1">
                <div class="small text-gray-500">
                    {{ \Carbon\Carbon::parse($notification->created_at)->format('M d, Y') }}
                    @if($notification->priority === 'high')
                        <span class="badge badge-danger badge-sm ml-1">High</span>
                    @endif
                    @if($notification->action_required)
                        <span class="badge badge-warning badge-sm ml-1">
                            <i class="fas fa-exclamation-triangle"></i>
                        </span>
                    @endif
                </div>
                <div class="font-weight-bold">{{ $notification->title }}</div>
                <div class="small text-truncate" style="max-width: 250px;">
                    {{ $notification->message }}
                </div>
            </div>
        </a>
    @endforeach

    <!-- Dropdown Footer -->
    <div class="dropdown-divider"></div>
    <a class="dropdown-item text-center small text-gray-500" 
       href="{{ route('admin.notifications.index') }}">
        <i class="fas fa-eye"></i> View All Notifications
    </a>
@else
    <!-- No Notifications -->
    <div class="dropdown-item-text text-center py-4">
        <div class="text-muted">
            <i class="fas fa-bell-slash fa-2x mb-2"></i>
            <br>
            <small>No new notifications</small>
        </div>
    </div>
    <div class="dropdown-divider"></div>
    <a class="dropdown-item text-center small text-gray-500" 
       href="{{ route('admin.notifications.index') }}">
        <i class="fas fa-bell"></i> View All Notifications
    </a>
@endif

<style>
.notification-dropdown-item {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #e3e6f0;
}

.notification-dropdown-item:last-of-type {
    border-bottom: none;
}

.notification-dropdown-item:hover {
    background-color: #f8f9fc;
}

.icon-circle {
    height: 2.5rem;
    width: 2.5rem;
    border-radius: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.dropdown-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
    font-weight: 800;
    padding: 0.75rem 1rem;
    color: #5a5c69;
}

.badge-sm {
    font-size: 0.65em;
    padding: 0.2em 0.4em;
}

/* Custom scrollbar for dropdown */
.dropdown-menu {
    max-height: 400px;
    overflow-y: auto;
}

.dropdown-menu::-webkit-scrollbar {
    width: 6px;
}

.dropdown-menu::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.dropdown-menu::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.dropdown-menu::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}
</style>