<?php
namespace App\Http\Controllers\Observer\Observers;

use App\Http\Controllers\Observer\Contracts\ObserverInterface;
use App\Http\Controllers\Observer\Events\UserRegisteredEvent;
use App\Http\Controllers\Observer\Events\BookingStatusChangedEvent;
use App\Http\Controllers\Observer\Events\UserLoginEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
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
                    $this->handleWelcomeEmail($eventData);
                    break;
                    
                case $eventData instanceof UserLoginEvent:
                    $this->handleSecurityNotifications($eventData);
                    break;
                    
                case $eventData instanceof BookingStatusChangedEvent:
                    $this->handleBookingStatusEmails($eventData);
                    break;
                    
                default:
                    Log::info("EmailNotificationObserver: Unhandled event type " . get_class($eventData));
            }
        } catch (\Exception $e) {
            Log::error("EmailNotificationObserver failed: " . $e->getMessage());
            // Store failed email attempt
            $this->logFailedEmail($eventData, $e->getMessage());
        }
    }

    /**
     * Send welcome email to new customers with actual email delivery
     */
    private function handleWelcomeEmail(UserRegisteredEvent $event): void
    {
        $user = $event->getUser();
        $registrationData = $event->getRegistrationData();
        
        try {
            // Prepare email data
            $emailData = [
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'registration_date' => $event->getTimestamp()->format('M d, Y'),
                'has_complete_profile' => $this->hasCompleteProfile($user),
                'next_steps' => $this->getCustomerNextSteps($user),
                'login_url' => route('login'),
                'support_email' => config('mail.support_address', 'support@rentwheels.com')
            ];

            // Send actual welcome email
            $this->sendWelcomeEmail($user, $emailData);
            
            // Send admin notification if customer needs approval
            if ($this->requiresApproval($user)) {
                $this->sendAdminNewCustomerEmail($user, $registrationData);
            }

            // Log successful email
            $this->logEmailActivity($user->id, 'welcome_email_sent', $emailData);
            
            Log::info("Welcome email sent successfully to: {$user->email}");
            
        } catch (\Exception $e) {
            Log::error("Failed to send welcome email to {$user->email}: " . $e->getMessage());
            $this->logFailedEmail($event, $e->getMessage());
        }
    }

    /**
     * Handle security notifications for suspicious logins
     */
    private function handleSecurityNotifications(UserLoginEvent $event): void
    {
        $user = $event->getUser();
        $loginData = $event->getLoginData();
        $ipAddress = $event->getIpAddress();
        
        try {
            // Check for security concerns
            if ($this->isSuspiciousLogin($user, $ipAddress, $loginData)) {
                $this->sendSecurityAlertEmail($user, $ipAddress, $loginData);
            }

            // Check if it's a returning customer after long absence
            if ($this->isReturningCustomer($user)) {
                $this->sendWelcomeBackEmail($user, $loginData);
            }

        } catch (\Exception $e) {
            Log::error("Failed to handle security notifications for {$user->email}: " . $e->getMessage());
        }
    }

    /**
     * Handle booking status change emails with comprehensive notifications
     */
    private function handleBookingStatusEmails(BookingStatusChangedEvent $event): void
    {
        $booking = $event->getBooking();
        $booking->load(['user', 'vehicle']);
        
        try {
            $emailData = [
                'customer_name' => $booking->user->name,
                'booking_id' => $booking->id,
                'booking_number' => '#BK' . str_pad($booking->id, 4, '0', STR_PAD_LEFT),
                'vehicle_name' => $booking->vehicle->make . ' ' . $booking->vehicle->model,
                'vehicle_plate' => $booking->vehicle->license_plate,
                'old_status' => $event->getOldStatus(),
                'new_status' => $event->getNewStatus(),
                'change_date' => $event->getTimestamp()->format('M d, Y H:i'),
                'booking_details' => $this->getBookingDetails($booking),
                'next_actions' => $this->getCustomerNextActions($event->getNewStatus()),
                'support_phone' => config('app.support_phone', '+60-123-456-789'),
                'support_email' => config('mail.support_address', 'support@rentwheels.com')
            ];

            // Send customer notification
            $this->sendBookingStatusEmail($booking->user, $emailData);

            // Send admin email for important changes
            if ($this->shouldNotifyAdmins($event->getNewStatus())) {
                $this->sendAdminBookingUpdateEmail($booking, $event, $emailData);
            }

            // Log email activity
            $this->logEmailActivity($booking->user_id, 'booking_status_email_sent', $emailData);
            
            Log::info("Booking status email sent to {$booking->user->email} for booking #{$booking->id}");
            
        } catch (\Exception $e) {
            Log::error("Failed to send booking status email: " . $e->getMessage());
            $this->logFailedEmail($event, $e->getMessage());
        }
    }

    /**
     * Actually send welcome email using Laravel Mail
     */
    private function sendWelcomeEmail(User $user, array $emailData): void
    {
        Mail::send('emails.customer.welcome', $emailData, function ($message) use ($user, $emailData) {
            $message->to($user->email, $user->name)
                   ->subject('Welcome to RentWheels - Your Account is Ready!')
                   ->from(config('mail.from.address'), config('mail.from.name'));
        });

        // Log successful send
        $this->logEmailSent($user->email, 'welcome', 'Welcome to RentWheels - Your Account is Ready!', $emailData);
    }

    /**
     * Send booking status change email
     */
    private function sendBookingStatusEmail(User $user, array $emailData): void
    {
        $subject = $this->getBookingStatusEmailSubject($emailData['new_status'], $emailData['booking_number']);
        
        Mail::send('emails.customer.booking-status', $emailData, function ($message) use ($user, $subject) {
            $message->to($user->email, $user->name)
                   ->subject($subject)
                   ->from(config('mail.from.address'), config('mail.from.name'));
        });

        // Log successful send
        $this->logEmailSent($user->email, 'booking_status_changed', $subject, $emailData);
    }

    /**
     * Send security alert email for suspicious activity
     */
    private function sendSecurityAlertEmail(User $user, string $ipAddress, array $loginData): void
    {
        $emailData = [
            'customer_name' => $user->name,
            'ip_address' => $ipAddress,
            'login_time' => now()->format('M d, Y H:i'),
            'user_agent' => $loginData['user_agent'] ?? 'Unknown',
            'location' => $this->getLocationFromIP($ipAddress),
            'account_url' => route('dashboard'),
            'support_email' => config('mail.from.address', 'rentwheelsnoreply@gmail.com')
        ];

        Mail::send('emails.security.suspicious-login', $emailData, function ($message) use ($user) {
            $message->to($user->email, $user->name)
                   ->subject('RentWheels Security Alert - New Login Detected')
                   ->from(config('mail.from.address'), config('mail.from.name'));
        });

        // Log security email
        $this->logEmailSent($user->email, 'security_alert', 'Security Alert - New Login Detected', $emailData);
    }

    /**
     * Send welcome back email for returning customers
     */
    private function sendWelcomeBackEmail(User $user, array $loginData): void
    {
        $emailData = [
            'customer_name' => $user->name,
            'last_login' => $user->last_login_at ? $user->last_login_at->format('M d, Y') : 'a while ago',
            'special_offers' => $this->getSpecialOffers(),
            'browse_url' => route('vehicles.index'),
            'dashboard_url' => route('dashboard')
        ];

        Mail::send('emails.customer.welcome-back', $emailData, function ($message) use ($user) {
            $message->to($user->email, $user->name)
                   ->subject('Welcome Back to RentWheels!')
                   ->from(config('mail.from.address'), config('mail.from.name'));
        });

        // Log welcome back email
        $this->logEmailSent($user->email, 'welcome_back', 'Welcome Back to RentWheels!', $emailData);
    }

    /**
     * Send admin notification for new customer requiring approval
     */
    private function sendAdminNewCustomerEmail(User $customer, array $registrationData): void
    {
        $adminEmails = $this->getAdminEmails();
        
        foreach ($adminEmails as $adminEmail) {
            $emailData = [
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'registration_date' => now()->format('M d, Y H:i'),
                'profile_complete' => $this->hasCompleteProfile($customer),
                'registration_ip' => $registrationData['registration_ip'] ?? 'Unknown',
                'admin_panel_url' => route('admin.customers'),
                'customer_id' => $customer->id
            ];
            
            Mail::send('emails.admin.new-customer-approval', $emailData, function ($message) use ($adminEmail, $customer) {
                $message->to($adminEmail)
                       ->subject('New Customer Requires Approval - ' . $customer->name)
                       ->from(config('mail.from.address'), config('mail.from.name'));
            });
        }

        Log::info("Admin notification sent for new customer: {$customer->email}");
    }

    /**
     * Send admin notification for booking updates
     */
    private function sendAdminBookingUpdateEmail($booking, $event, array $emailData): void
    {
        $adminEmails = $this->getAdminEmails();
        
        foreach ($adminEmails as $adminEmail) {
            $adminEmailData = array_merge($emailData, [
                'requires_action' => in_array($event->getNewStatus(), ['cancelled', 'active']),
                'admin_panel_url' => route('admin.bookings'),
                'booking_id' => $booking->id
            ]);
            
            $subject = 'Booking Update Requires Attention - ' . $emailData['booking_number'];
            
            Mail::send('emails.admin.booking-update', $adminEmailData, function ($message) use ($adminEmail, $subject) {
                $message->to($adminEmail)
                       ->subject($subject)
                       ->from(config('mail.from.address'), config('mail.from.name'));
            });
        }
    }

    /**
     * Log successful email sends to database
     */
    private function logEmailSent(string $email, string $template, string $subject, array $data): void
    {
        try {
            DB::table('email_logs')->insert([
                'recipient_email' => $email,
                'template' => $template,
                'subject' => $subject,
                'content' => 'Email sent successfully via Mail facade',
                'data' => json_encode($data),
                'status' => 'sent',
                'sent_at' => now(),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to log email send: " . $e->getMessage());
        }
    }

    /**
     * Log failed email attempts
     */
    private function logFailedEmail($event, string $error): void
    {
        try {
            DB::table('email_logs')->insert([
                'recipient_email' => 'unknown',
                'template' => 'failed_event',
                'subject' => 'Failed Email Processing',
                'content' => 'Email processing failed',
                'data' => json_encode(['event' => get_class($event), 'error' => $error]),
                'status' => 'failed',
                'error_message' => $error,
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to log failed email: " . $e->getMessage());
        }
    }

    /**
     * Helper method to determine appropriate email subject for booking status
     */
    private function getBookingStatusEmailSubject(string $status, string $bookingNumber): string
    {
        return match($status) {
            'confirmed' => "Booking Confirmed - $bookingNumber",
            'active' => "Vehicle Ready for Pickup - $bookingNumber", 
            'completed' => "Thank You for Choosing RentWheels - $bookingNumber",
            'cancelled' => "Booking Cancelled - $bookingNumber",
            default => "Booking Update - $bookingNumber"
        };
    }

    // Helper methods (existing functionality enhanced)
    private function hasCompleteProfile(User $user): bool
    {
        return !empty($user->phone) && !empty($user->date_of_birth) && !empty($user->address);
    }

    private function requiresApproval(User $user): bool
    {
        return !$this->hasCompleteProfile($user);
    }

    private function getCustomerNextSteps(User $user): array
    {
        $steps = ['Browse our vehicle catalog', 'Make your first booking'];
        
        if (!$this->hasCompleteProfile($user)) {
            array_unshift($steps, 'Complete your profile information');
        }
        
        return $steps;
    }

    private function isSuspiciousLogin(User $user, string $ipAddress, array $loginData): bool
    {
        // Check for multiple failed attempts or new location
        $recentLogins = DB::table('security_logs')
            ->where('customer_id', $user->id)
            ->where('created_at', '>=', now()->subHours(24))
            ->count();
            
        return $recentLogins > 5 || $this->isNewLocation($user, $ipAddress);
    }

    private function isNewLocation(User $user, string $ipAddress): bool
    {
        $recentIPs = DB::table('security_logs')
            ->where('customer_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->pluck('ip_address')
            ->unique();
            
        return !$recentIPs->contains($ipAddress);
    }

    private function isReturningCustomer(User $user): bool
    {
        return $user->last_login_at && $user->last_login_at->diffInDays(now()) > 30;
    }

    private function getBookingDetails($booking): array
    {
        return [
            'pickup_date' => $booking->pickup_datetime ? $booking->pickup_datetime->format('M d, Y H:i') : 'TBD',
            'return_date' => $booking->return_datetime ? $booking->return_datetime->format('M d, Y H:i') : 'TBD',
            'total_amount' => $booking->total_amount ?? 0,
            'pickup_location' => $booking->pickup_location ?? 'Main Office',
            'return_location' => $booking->return_location ?? 'Main Office'
        ];
    }

    private function getCustomerNextActions(string $status): array
    {
        return match($status) {
            'confirmed' => ['Prepare required documents', 'Arrive at pickup location on time'],
            'active' => ['Enjoy your rental', 'Contact us if you need assistance'],
            'completed' => ['Rate your experience', 'Book again anytime'],
            'cancelled' => ['Refund will be processed if applicable', 'Feel free to book again'],
            default => ['Contact support if you have questions']
        };
    }

    private function shouldNotifyAdmins(string $status): bool
    {
        return in_array($status, ['cancelled', 'active', 'confirmed']);
    }

    private function getAdminEmails(): array
    {
        try {
            return User::where('is_admin', true)
                ->where('status', 'active')
                ->pluck('email')
                ->toArray();
        } catch (\Exception $e) {
            Log::error("Failed to get admin emails: " . $e->getMessage());
            return [config('mail.admin_email', 'admin@rentwheels.com')];
        }
    }

    private function logEmailActivity(?int $userId, string $activity, array $data): void
    {
        if (!$userId) return;
        
        try {
            DB::table('customer_email_activities')->insert([
                'user_id' => $userId,
                'activity_type' => $activity,
                'email_template' => $data['template'] ?? null,
                'metadata' => json_encode($data),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to log email activity: " . $e->getMessage());
        }
    }

    private function getLocationFromIP(string $ipAddress): string
    {
        // For production, integrate with a geolocation service
        // For now, return a placeholder
        return 'Unknown Location';
    }

    private function getSpecialOffers(): array
    {
        return [
            'Welcome back discount: 10% off your next booking',
            'Extended rental deals available',
            'New luxury vehicles in our fleet'
        ];
    }
}