<?php
namespace App\Http\Controllers\Observer\Observers;

use App\Http\Controllers\Observer\Contracts\ObserverInterface;
use App\Http\Controllers\Observer\Events\UserRegisteredEvent;
use App\Http\Controllers\Observer\Events\BookingStatusChangedEvent;
use App\Http\Controllers\Observer\Events\UserLoginEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class EmailNotificationObserver implements ObserverInterface
{
    public function getName(): string
    {
        return 'EmailNotificationObserver';
    }

    public function update($eventData): void
    {
        Log::info("EmailNotificationObserver processing event: " . get_class($eventData));

        try {
            switch (true) {
                case $eventData instanceof UserRegisteredEvent:
                    $this->handleCustomerWelcomeEmail($eventData);
                    break;
                    
                case $eventData instanceof UserLoginEvent:
                    $this->handleCustomerLoginNotifications($eventData);
                    break;
                    
                case $eventData instanceof BookingStatusChangedEvent:
                    $this->handleBookingStatusEmails($eventData);
                    break;
                    
                default:
                    Log::info("EmailNotificationObserver: Unhandled event type " . get_class($eventData));
            }
        } catch (\Exception $e) {
            Log::error("EmailNotificationObserver failed: " . $e->getMessage());
        }
    }

    /**
     * Send welcome email to new customers
     */
    private function handleCustomerWelcomeEmail(UserRegisteredEvent $event): void
    {
        $user = $event->getUser();
        $registrationData = $event->getRegistrationData();
        
        try {
            // Queue welcome email
            $emailData = [
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'registration_date' => $event->getTimestamp()->format('M d, Y'),
                'has_complete_profile' => $this->hasCompleteProfile($user),
                'next_steps' => $this->getCustomerNextSteps($user)
            ];

            // Send welcome email
            $this->sendCustomerEmail($user->email, 'welcome', $emailData);
            
            // Send admin notification email if customer needs approval
            if ($this->requiresApproval($user)) {
                $this->sendAdminNewCustomerEmail($user, $registrationData);
            }

            // Log email activity (only if user has a valid ID)
            if ($user->id) {
                $this->logEmailActivity($user->id, 'welcome_email_sent', $emailData);
            }
            
            Log::info("Welcome email sent to new customer: {$user->email}");
            
        } catch (\Exception $e) {
            Log::error("Failed to send welcome email to {$user->email}: " . $e->getMessage());
        }
    }

    /**
     * Handle login-related email notifications
     */
    private function handleCustomerLoginNotifications(UserLoginEvent $event): void
    {
        $user = $event->getUser();
        $loginData = $event->getLoginData();
        
        try {
            // Check if it's been a while since last login
            if ($this->isReturningCustomer($user)) {
                $this->sendWelcomeBackEmail($user, $loginData);
            }

            // Check for security concerns
            if ($this->shouldSendSecurityAlert($user, $event->getIpAddress())) {
                $this->sendSecurityAlertEmail($user, $event->getIpAddress(), $loginData);
            }

        } catch (\Exception $e) {
            Log::error("Failed to handle login notifications for {$user->email}: " . $e->getMessage());
        }
    }

    /**
     * Handle booking status change emails
     */
    private function handleBookingStatusEmails(BookingStatusChangedEvent $event): void
    {
        $booking = $event->getBooking();
        $booking->load(['user', 'vehicle']);
        
        try {
            $emailData = [
                'customer_name' => $booking->user->name,
                'booking_id' => $booking->id,
                'vehicle_name' => $booking->vehicle->name ?? 'Vehicle',
                'old_status' => $event->getOldStatus(),
                'new_status' => $event->getNewStatus(),
                'change_date' => $event->getTimestamp()->format('M d, Y H:i'),
                'booking_details' => $this->getBookingDetails($booking),
                'next_actions' => $this->getCustomerNextActions($event->getNewStatus())
            ];

            // Send customer notification
            $this->sendCustomerEmail(
                $booking->user->email, 
                'booking_status_changed', 
                $emailData
            );

            // Send admin email for important changes
            if ($this->shouldNotifyAdmins($event->getNewStatus())) {
                $this->sendAdminBookingUpdateEmail($booking, $event, $emailData);
            }

            // Log email activity (only if user has a valid ID)
            if ($booking->user_id) {
                $this->logEmailActivity($booking->user_id, 'booking_status_email_sent', $emailData);
            }
            
            Log::info("Booking status email sent to {$booking->user->email} for booking #{$booking->id}");
            
        } catch (\Exception $e) {
            Log::error("Failed to send booking status email: " . $e->getMessage());
        }
    }

    /**
     * Send customer email using template system
     */
    private function sendCustomerEmail(string $email, string $template, array $data): void
    {
        try {
            // In a real implementation, you would use Laravel's Mail facade with proper email templates
            // For now, we'll simulate email sending and log the details
            
            $emailContent = $this->buildEmailContent($template, $data);
            
            // Store email in database for tracking
            DB::table('email_logs')->insert([
                'recipient_email' => $email,
                'template' => $template,
                'subject' => $emailContent['subject'],
                'content' => $emailContent['content'],
                'data' => json_encode($data),
                'status' => 'sent',
                'sent_at' => now(),
                'created_at' => now()
            ]);

            Log::info("Email sent: {$template} to {$email}", ['subject' => $emailContent['subject']]);
            
        } catch (\Exception $e) {
            // Log failed email
            DB::table('email_logs')->insert([
                'recipient_email' => $email,
                'template' => $template,
                'data' => json_encode($data),
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'created_at' => now()
            ]);
            
            throw $e;
        }
    }

    /**
     * Build email content based on template
     */
    private function buildEmailContent(string $template, array $data): array
    {
        return match($template) {
            'welcome' => [
                'subject' => 'Welcome to RentWheels, ' . $data['customer_name'] . '!',
                'content' => $this->buildWelcomeEmailContent($data)
            ],
            'welcome_back' => [
                'subject' => 'Welcome back to RentWheels!',
                'content' => $this->buildWelcomeBackEmailContent($data)
            ],
            'booking_status_changed' => [
                'subject' => 'Booking Update - #' . $data['booking_id'],
                'content' => $this->buildBookingStatusEmailContent($data)
            ],
            'security_alert' => [
                'subject' => 'Security Alert - New login detected',
                'content' => $this->buildSecurityAlertEmailContent($data)
            ],
            default => [
                'subject' => 'RentWheels Notification',
                'content' => 'Thank you for using RentWheels.'
            ]
        };
    }

    /**
     * Build welcome email content
     */
    private function buildWelcomeEmailContent(array $data): string
    {
        $content = "Dear {$data['customer_name']},\n\n";
        $content .= "Welcome to RentWheels! We're excited to have you as a customer.\n\n";
        
        if (!$data['has_complete_profile']) {
            $content .= "To get started, please complete your profile by adding:\n";
            $content .= "- Phone number\n";
            $content .= "- Date of birth\n";
            $content .= "- Address\n\n";
        }
        
        $content .= "Next steps:\n";
        foreach ($data['next_steps'] as $step) {
            $content .= "- {$step}\n";
        }
        
        $content .= "\nBest regards,\nThe RentWheels Team";
        
        return $content;
    }

    /**
     * Build welcome back email content
     */
    private function buildWelcomeBackEmailContent(array $data): string
    {
        return "Dear {$data['customer_name']},\n\n" .
               "Welcome back to RentWheels! We're glad to see you again.\n\n" .
               "Check out our latest vehicles and special offers.\n\n" .
               "Best regards,\nThe RentWheels Team";
    }

    /**
     * Build booking status email content
     */
    private function buildBookingStatusEmailContent(array $data): string
    {
        $content = "Dear {$data['customer_name']},\n\n";
        $content .= "Your booking #{$data['booking_id']} for {$data['vehicle_name']} has been updated.\n\n";
        $content .= "Status changed from '{$data['old_status']}' to '{$data['new_status']}' on {$data['change_date']}.\n\n";
        
        if (!empty($data['next_actions'])) {
            $content .= "Next actions:\n";
            foreach ($data['next_actions'] as $action) {
                $content .= "- {$action}\n";
            }
            $content .= "\n";
        }
        
        $content .= "Best regards,\nThe RentWheels Team";
        
        return $content;
    }

    /**
     * Build security alert email content
     */
    private function buildSecurityAlertEmailContent(array $data): string
    {
        return "Dear {$data['customer_name']},\n\n" .
               "We detected a new login to your RentWheels account from IP: {$data['ip_address']}\n\n" .
               "If this was you, no action is needed. If you didn't login, please contact support immediately.\n\n" .
               "Best regards,\nThe RentWheels Security Team";
    }

    /**
     * Send admin notification email for new customer
     */
    private function sendAdminNewCustomerEmail(User $customer, array $registrationData): void
    {
        $adminEmails = $this->getAdminEmails();
        
        foreach ($adminEmails as $adminEmail) {
            $emailData = [
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'registration_date' => now()->format('M d, Y H:i'),
                'requires_approval' => true,
                'profile_complete' => $this->hasCompleteProfile($customer)
            ];
            
            $this->sendAdminEmail($adminEmail, 'new_customer_approval_needed', $emailData);
        }
    }

    /**
     * Send admin email for booking updates
     */
    private function sendAdminBookingUpdateEmail($booking, $event, array $emailData): void
    {
        $adminEmails = $this->getAdminEmails();
        
        foreach ($adminEmails as $adminEmail) {
            $adminEmailData = array_merge($emailData, [
                'requires_action' => in_array($event->getNewStatus(), ['cancelled', 'active'])
            ]);
            
            $this->sendAdminEmail($adminEmail, 'booking_status_admin_notification', $adminEmailData);
        }
    }

    /**
     * Send admin email
     */
    private function sendAdminEmail(string $email, string $template, array $data): void
    {
        // Similar to customer email but with admin-specific templates
        $this->sendCustomerEmail($email, $template, $data);
    }

    /**
     * Log email activity
     */
    private function logEmailActivity(?int $userId, string $activity, array $data): void
    {
        // Skip logging if no valid user ID
        if (!$userId) {
            return;
        }
        
        try {
            DB::table('customer_email_activities')->insert([
                'user_id' => $userId,
                'activity_type' => $activity,
                'email_template' => $data['template'] ?? null,
                'metadata' => json_encode($data),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to log email activity: " . $e->getMessage());
        }
    }

    /**
     * Check if customer has complete profile
     */
    private function hasCompleteProfile(User $user): bool
    {
        return !empty($user->phone) && !empty($user->date_of_birth) && !empty($user->address);
    }

    /**
     * Check if customer requires approval
     */
    private function requiresApproval(User $user): bool
    {
        return !$this->hasCompleteProfile($user);
    }

    /**
     * Get customer next steps
     */
    private function getCustomerNextSteps(User $user): array
    {
        $steps = [];
        
        if (!$this->hasCompleteProfile($user)) {
            $steps[] = 'Complete your profile information';
        }
        
        $steps[] = 'Browse our vehicle catalog';
        $steps[] = 'Make your first booking';
        
        return $steps;
    }

    /**
     * Check if customer is returning after long absence
     */
    private function isReturningCustomer(User $user): bool
    {
        return $user->last_login_at && 
               $user->last_login_at->diffInDays(now()) > 30;
    }

    /**
     * Check if security alert should be sent
     */
    private function shouldSendSecurityAlert(User $user, string $ipAddress): bool
    {
        // Check if login from new IP address
        $recentLogins = DB::table('customer_activities')
            ->where('user_id', $user->id)
            ->where('activity_type', 'login')
            ->where('created_at', '>=', now()->subDays(30))
            ->pluck('ip_address')
            ->unique();
            
        return !$recentLogins->contains($ipAddress);
    }

    /**
     * Send welcome back email
     */
    private function sendWelcomeBackEmail(User $user, array $loginData): void
    {
        $emailData = [
            'customer_name' => $user->name,
            'last_login' => $user->last_login_at->format('M d, Y')
        ];
        
        $this->sendCustomerEmail($user->email, 'welcome_back', $emailData);
    }

    /**
     * Send security alert email
     */
    private function sendSecurityAlertEmail(User $user, string $ipAddress, array $loginData): void
    {
        $emailData = [
            'customer_name' => $user->name,
            'ip_address' => $ipAddress,
            'login_time' => now()->format('M d, Y H:i')
        ];
        
        $this->sendCustomerEmail($user->email, 'security_alert', $emailData);
    }

    /**
     * Get booking details
     */
    private function getBookingDetails($booking): array
    {
        return [
            'vehicle_name' => $booking->vehicle->name ?? 'Vehicle',
            'start_date' => $booking->start_date ?? 'TBD',
            'end_date' => $booking->end_date ?? 'TBD',
            'total_amount' => $booking->total_amount ?? 0
        ];
    }

    /**
     * Get customer next actions based on booking status
     */
    private function getCustomerNextActions(string $status): array
    {
        return match($status) {
            'confirmed' => ['Prepare required documents', 'Arrive at pickup location on time'],
            'active' => ['Enjoy your rental', 'Contact us if you need assistance'],
            'completed' => ['Rate your experience', 'Book again anytime'],
            'cancelled' => ['Refund will be processed', 'Feel free to book again'],
            default => []
        };
    }

    /**
     * Check if admins should be notified
     */
    private function shouldNotifyAdmins(string $status): bool
    {
        return in_array($status, ['cancelled', 'active']);
    }

    /**
     * Get admin emails
     */
    private function getAdminEmails(): array
    {
        try {
            return User::where('is_admin', true)
                ->where('status', 'active')
                ->pluck('email')
                ->toArray();
        } catch (\Exception $e) {
            Log::error("Failed to get admin emails: " . $e->getMessage());
            return [];
        }
    }
}