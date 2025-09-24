<!-- Admin Notification Bell for Header -->
<li class="nav-item dropdown no-arrow mx-1">
    <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" 
       data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-bell fa-fw"></i>
        <!-- Counter - Unread Notifications -->
        <span class="badge badge-danger badge-counter" id="notification-count" style="display: none;">
            0
        </span>
    </a>
    
    <!-- Dropdown - Notifications -->
    <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" 
         aria-labelledby="alertsDropdown" id="notifications-dropdown" style="min-width: 350px;">
        
        <!-- Loading State -->
        <div id="notifications-loading" class="text-center py-4">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <div class="mt-2 text-muted small">Loading notifications...</div>
        </div>
        
        <!-- Notifications Content (loaded via AJAX) -->
        <div id="notifications-content"></div>
    </div>
</li>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load notifications when page loads
    loadNotifications();
    
    // Auto-refresh notifications every 30 seconds
    setInterval(loadNotifications, 30000);
    
    // Load notifications when dropdown is opened
    document.getElementById('alertsDropdown').addEventListener('click', function(e) {
        e.preventDefault();
        loadNotifications();
    });
});

function loadNotifications() {
    fetch('/admin/notifications/header', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update notification content
            document.getElementById('notifications-content').innerHTML = data.html;
            
            // Update notification counter
            const counter = document.getElementById('notification-count');
            if (data.unread_count > 0) {
                counter.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                counter.style.display = 'block';
                
                // Add animation class for new notifications
                counter.classList.add('notification-pulse');
                setTimeout(() => {
                    counter.classList.remove('notification-pulse');
                }, 1000);
            } else {
                counter.style.display = 'none';
            }
            
            // Hide loading state
            document.getElementById('notifications-loading').style.display = 'none';
        } else {
            console.error('Failed to load notifications:', data.message);
            handleNotificationError();
        }
    })
    .catch(error => {
        console.error('Error loading notifications:', error);
        handleNotificationError();
    });
}

function handleNotificationError() {
    document.getElementById('notifications-loading').innerHTML = `
        <div class="text-center py-4 text-muted">
            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
            <div class="small">Failed to load notifications</div>
            <button class="btn btn-sm btn-outline-primary mt-2" onclick="loadNotifications()">
                <i class="fas fa-refresh"></i> Retry
            </button>
        </div>
    `;
}

// Function to mark notification as read from dropdown
function markNotificationAsRead(notificationId) {
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
            // Reload notifications to update the dropdown
            loadNotifications();
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}
</script>

<style>
/* Notification Bell Styles */
.badge-counter {
    position: absolute;
    top: -2px;
    right: -6px;
    font-size: 0.7rem;
    border: 2px solid #fff;
    min-width: 18px;
    height: 18px;
    border-radius: 10px;
    line-height: 14px;
    text-align: center;
}

/* Notification Pulse Animation */
@keyframes notificationPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

.notification-pulse {
    animation: notificationPulse 0.6s ease-in-out;
}

/* Dropdown Styling */
.dropdown-list {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    border-radius: 0.35rem;
}

/* Notification Hover Effects */
.nav-link:hover .fa-bell {
    animation: swing 1s ease-in-out;
}

@keyframes swing {
    15% { transform: translateX(5px); }
    30% { transform: translateX(-5px); }
    50% { transform: translateX(3px); }
    65% { transform: translateX(-3px); }
    80% { transform: translateX(2px); }
    100% { transform: translateX(0); }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .dropdown-list {
        min-width: 300px !important;
        right: -100px !important;
    }
}

/* Custom scrollbar for notification dropdown */
.dropdown-list {
    max-height: 400px;
    overflow-y: auto;
}

.dropdown-list::-webkit-scrollbar {
    width: 6px;
}

.dropdown-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.dropdown-list::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.dropdown-list::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}
</style>