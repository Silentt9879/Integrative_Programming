<?php
namespace App\Http\Controllers\Observer\Observers;

use App\Http\Controllers\Observer\Contracts\ObserverInterface;
use App\Http\Controllers\Observer\Events\UserRegisteredEvent;
use App\Http\Controllers\Observer\Events\BookingStatusChangedEvent;
use App\Http\Controllers\Observer\Events\ReportGeneratedEvent;
use App\Http\Controllers\Observer\Events\UserLoginEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AdminNotificationObserver implements ObserverInterface
{
    public function getName(): string
    {
        return 'AdminNotificationObserver';
    }

    public function update($eventData): void
    {
        Log::info("AdminNotificationObserver processing event: " . get_class($eventData));

        try {
            switch (true) {
                case $eventData instanceof UserRegisteredEvent:
                    $this->handleNewCustomerRegistration($eventData);
                    break;
                    
                case $eventData instanceof UserLoginEvent:
                    $this->handleCustomerLogin($eventData);
                    break;
                    
                case $eventData instanceof BookingStatusChangedEvent:
                    $this->handleBookingManagement($eventData);
                    break;
                    
                case $eventData instanceof ReportGeneratedEvent:
                    $this->handleAdminActivityTracking($eventData);
                    break;
            }
        } catch (\Exception $e) {
            Log::error("AdminNotificationObserver failed: " . $e->getMessage());
        }
    }

    /**
     * Handle new customer registration - Admin Management
     */
    private function handleNewCustomerRegistration(UserRegisteredEvent $event): void
    {
        $user = $event->getUser();
        $registrationData = $event->getRegistrationData();
        
        // Create admin notification record in database
        $this->createAdminNotification([
            'type' => 'new_customer',
            'title' => 'New Customer Registration',
            'message' => "New customer {$user->name} ({$user->email}) has registered",
            'priority' => 'medium',
            'action_required' => $this->requiresApproval($user),
            'related_user_id' => $user->id,
            'metadata' => [
                'registration_source' => $registrationData['source'] ?? 'web',
                'has_complete_profile' => $this->hasCompleteProfile($user),
                'registration_ip' => $registrationData['registration_ip'] ?? null
            ]
        ]);

        // Check if customer needs approval or verification
        if ($this->requiresApproval($user)) {
            $this->flagCustomerForReview($user);
        }

        // Update customer statistics
        $this->updateCustomerStats('new_registration');
        
        Log::info("Admin notification created for new customer: {$user->email}");
    }

    /**
     * Handle customer login tracking - Customer Management
     */
    private function handleCustomerLogin(UserLoginEvent $event): void
    {
        $user = $event->getUser();
        $loginData = $event->getLoginData();
        
        // Track customer activity
        $this->trackCustomerActivity($user, 'login', [
            'ip_address' => $event->getIpAddress(),
            'login_time' => $event->getTimestamp(),
            'user_agent' => $loginData['user_agent'] ?? null,
            'remember_me' => $loginData['remember_me'] ?? false
        ]);

        // Check for suspicious login patterns
        if ($this->detectSuspiciousLogin($user, $event->getIpAddress())) {
            $this->createSecurityAlert($user, $event->getIpAddress());
        }

        // Update last login time
        $this->updateCustomerLastSeen($user);
    }

    /**
     * Handle booking management - Admin oversight
     */
    private function handleBookingManagement(BookingStatusChangedEvent $event): void
    {
        $booking = $event->getBooking();
        $changeData = $event->getChangeData();
        
        // Create admin notification for important status changes
        $importantStatuses = ['cancelled', 'completed', 'active', 'confirmed'];
        
        if (in_array($event->getNewStatus(), $importantStatuses)) {
            $this->createAdminNotification([
                'type' => 'booking_status_change',
                'title' => 'Booking Status Updated',
                'message' => "Booking #{$booking->id} status changed from {$event->getOldStatus()} to {$event->getNewStatus()}",
                'priority' => $this->getStatusChangePriority($event->getNewStatus()),
                'action_required' => $this->statusRequiresAction($event->getNewStatus()),
                'related_booking_id' => $booking->id,
                'related_user_id' => $booking->user_id,
                'metadata' => [
                    'changed_by' => $changeData['changed_by'] ?? 'System',
                    'change_reason' => $changeData['change_reason'] ?? null,
                    'old_status' => $event->getOldStatus(),
                    'new_status' => $event->getNewStatus()
                ]
            ]);
        }

        // Update booking statistics
        $this->updateBookingStats($event->getOldStatus(), $event->getNewStatus());
    }

    /**
     * Handle admin activity tracking
     */
    private function handleAdminActivityTracking(ReportGeneratedEvent $event): void
    {
        $admin = $event->getGeneratedBy();
        
        // Track admin activity
        $this->trackAdminActivity($admin, 'report_generated', [
            'report_type' => $event->getReportType(),
            'generation_time' => $event->getTimestamp(),
            'report_data_size' => count($event->getReportData())
        ]);

        Log::info("Admin activity tracked: Report generated by {$admin->name}");
    }

    /**
     * Create admin notification in database
     */
    private function createAdminNotification(array $notificationData): void
    {
        try {
            DB::table('admin_notifications')->insert([
                'type' => $notificationData['type'],
                'title' => $notificationData['title'],
                'message' => $notificationData['message'],
                'priority' => $notificationData['priority'],
                'action_required' => $notificationData['action_required'] ?? false,
                'related_user_id' => $notificationData['related_user_id'] ?? null,
                'related_booking_id' => $notificationData['related_booking_id'] ?? null,
                'metadata' => json_encode($notificationData['metadata'] ?? []),
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to create admin notification: " . $e->getMessage());
        }
    }

    /**
     * Track customer activity
     */
    private function trackCustomerActivity(User $user, string $activity, array $data): void
    {
        try {
            DB::table('customer_activities')->insert([
                'user_id' => $user->id,
                'activity_type' => $activity,
                'ip_address' => $data['ip_address'] ?? null,
                'user_agent' => $data['user_agent'] ?? null,
                'metadata' => json_encode($data),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to track customer activity: " . $e->getMessage());
        }
    }

    /**
     * Track admin activity
     */
    private function trackAdminActivity(User $admin, string $activity, array $data): void
    {
        try {
            DB::table('admin_activities')->insert([
                'admin_id' => $admin->id,
                'activity_type' => $activity,
                'metadata' => json_encode($data),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to track admin activity: " . $e->getMessage());
        }
    }

    /**
     * Check if customer requires approval
     */
    private function requiresApproval(User $user): bool
    {
        // Business logic: require approval for customers without complete profiles
        return !$this->hasCompleteProfile($user);
    }

    /**
     * Check if customer has complete profile
     */
    private function hasCompleteProfile(User $user): bool
    {
        return !empty($user->phone) && !empty($user->date_of_birth) && !empty($user->address);
    }

    /**
     * Flag customer for review
     */
    private function flagCustomerForReview(User $user): void
    {
        try {
            $user->update(['status' => 'pending_review']);
            Log::info("Customer {$user->email} flagged for admin review");
        } catch (\Exception $e) {
            Log::error("Failed to flag customer for review: " . $e->getMessage());
        }
    }

    /**
     * Detect suspicious login patterns
     */
    private function detectSuspiciousLogin(User $user, string $ipAddress): bool
    {
        try {
            // Check for multiple logins from different IPs in short time
            $recentLogins = DB::table('customer_activities')
                ->where('user_id', $user->id)
                ->where('activity_type', 'login')
                ->where('created_at', '>=', now()->subHours(2))
                ->count();

            return $recentLogins > 3;
        } catch (\Exception $e) {
            Log::error("Failed to detect suspicious login: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create security alert
     */
    private function createSecurityAlert(User $user, string $ipAddress): void
    {
        $this->createAdminNotification([
            'type' => 'security_alert',
            'title' => 'Suspicious Login Activity',
            'message' => "Multiple logins detected for customer {$user->name} ({$user->email})",
            'priority' => 'high',
            'action_required' => true,
            'related_user_id' => $user->id,
            'metadata' => [
                'ip_address' => $ipAddress,
                'alert_type' => 'multiple_logins'
            ]
        ]);
    }

    /**
     * Update customer last seen
     */
    private function updateCustomerLastSeen(User $user): void
    {
        try {
            $user->update(['last_login_at' => now()]);
        } catch (\Exception $e) {
            Log::error("Failed to update customer last seen: " . $e->getMessage());
        }
    }

    /**
     * Get priority for status changes
     */
    private function getStatusChangePriority(string $status): string
    {
        return match($status) {
            'cancelled' => 'high',
            'active', 'confirmed' => 'medium',
            'completed' => 'low',
            default => 'medium'
        };
    }

    /**
     * Check if status requires action
     */
    private function statusRequiresAction(string $status): bool
    {
        return in_array($status, ['cancelled', 'active']);
    }

    /**
     * Update customer statistics
     */
    private function updateCustomerStats(string $type): void
    {
        try {
            $today = now()->format('Y-m-d');
            
            DB::table('customer_stats')->updateOrInsert(
                ['date' => $today, 'type' => $type],
                [
                    'count' => DB::raw('count + 1'),
                    'updated_at' => now()
                ]
            );
        } catch (\Exception $e) {
            Log::error("Failed to update customer stats: " . $e->getMessage());
        }
    }

    /**
     * Update booking statistics
     */
    private function updateBookingStats(string $oldStatus, string $newStatus): void
    {
        try {
            $today = now()->format('Y-m-d');
            
            // Decrease old status count
            DB::table('booking_stats')->updateOrInsert(
                ['date' => $today, 'status' => $oldStatus],
                [
                    'count' => DB::raw('GREATEST(count - 1, 0)'),
                    'updated_at' => now()
                ]
            );
            
            // Increase new status count
            DB::table('booking_stats')->updateOrInsert(
                ['date' => $today, 'status' => $newStatus],
                [
                    'count' => DB::raw('count + 1'),
                    'updated_at' => now()
                ]
            );
        } catch (\Exception $e) {
            Log::error("Failed to update booking stats: " . $e->getMessage());
        }
    }
}