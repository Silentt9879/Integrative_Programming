<?php
namespace App\Http\Controllers\Observer\Observers;

use App\Http\Controllers\Observer\Contracts\ObserverInterface;
use App\Http\Controllers\Observer\Events\UserRegisteredEvent;
use App\Http\Controllers\Observer\Events\UserLoginEvent;
use App\Http\Controllers\Observer\Events\BookingStatusChangedEvent;
use App\Http\Controllers\Observer\Events\ReportGeneratedEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsObserver implements ObserverInterface
{
    public function getName(): string
    {
        return 'AnalyticsObserver';
    }

    public function update($eventData): void
    {
        Log::info("AnalyticsObserver processing event: " . get_class($eventData));

        try {
            switch (true) {
                case $eventData instanceof UserRegisteredEvent:
                    $this->trackUserRegistration($eventData);
                    break;
                    
                case $eventData instanceof UserLoginEvent:
                    $this->trackUserLogin($eventData);
                    break;
                    
                case $eventData instanceof BookingStatusChangedEvent:
                    $this->trackBookingChange($eventData);
                    break;
                    
                case $eventData instanceof ReportGeneratedEvent:
                    $this->trackReportGeneration($eventData);
                    break;
            }
        } catch (\Exception $e) {
            Log::error("AnalyticsObserver failed: " . $e->getMessage());
        }
    }

    /**
     * Track user registration - simple metrics
     */
    private function trackUserRegistration(UserRegisteredEvent $event): void
    {
        $user = $event->getUser();
        $registrationData = $event->getRegistrationData();
        $today = now()->format('Y-m-d');
        
        try {
            // Daily registration count
            DB::table('analytics_daily')->updateOrInsert(
                ['date' => $today, 'metric' => 'user_registrations'],
                ['value' => DB::raw('value + 1'), 'updated_at' => now()]
            );
            
            // Registration source tracking
            $source = $registrationData['source'] ?? 'web';
            DB::table('analytics_by_category')->updateOrInsert(
                ['date' => $today, 'metric' => 'registration_source', 'category' => $source],
                ['value' => DB::raw('value + 1'), 'updated_at' => now()]
            );
            
            // Profile completeness (simple check)
            $profileComplete = $this->isProfileComplete($user) ? 'complete' : 'incomplete';
            DB::table('analytics_by_category')->updateOrInsert(
                ['date' => $today, 'metric' => 'profile_status', 'category' => $profileComplete],
                ['value' => DB::raw('value + 1'), 'updated_at' => now()]
            );

            Log::info("User registration analytics tracked", ['user_id' => $user->id]);
            
        } catch (\Exception $e) {
            Log::error("Failed to track registration analytics: " . $e->getMessage());
        }
    }

    /**
     * Track user login - simple web engagement metrics
     */
    private function trackUserLogin(UserLoginEvent $event): void
    {
        $user = $event->getUser();
        $loginData = $event->getLoginData();
        $today = now()->format('Y-m-d');
        
        try {
            // Daily active users
            DB::table('analytics_daily')->updateOrInsert(
                ['date' => $today, 'metric' => 'daily_active_users'],
                ['value' => DB::raw('value + 1'), 'updated_at' => now()]
            );
            
            // Browser tracking (optional)
            $browser = $this->getBrowser($loginData['user_agent'] ?? '');
            DB::table('analytics_by_category')->updateOrInsert(
                ['date' => $today, 'metric' => 'browser_type', 'category' => $browser],
                ['value' => DB::raw('value + 1'), 'updated_at' => now()]
            );
            
            // Simple session tracking
            DB::table('customer_sessions')->insert([
                'user_id' => $user->id,
                'session_start' => now(),
                'ip_address' => $event->getIpAddress(),
                'user_agent' => $loginData['user_agent'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            Log::info("User login analytics tracked", ['user_id' => $user->id]);
            
        } catch (\Exception $e) {
            Log::error("Failed to track login analytics: " . $e->getMessage());
        }
    }

    /**
     * Track booking changes - focus on revenue and status
     */
    private function trackBookingChange(BookingStatusChangedEvent $event): void
    {
        $booking = $event->getBooking();
        $oldStatus = $event->getOldStatus();
        $newStatus = $event->getNewStatus();
        $today = now()->format('Y-m-d');
        
        try {
            // Track status changes by type
            DB::table('analytics_by_category')->updateOrInsert(
                ['date' => $today, 'metric' => 'booking_status', 'category' => $newStatus],
                ['value' => DB::raw('value + 1'), 'updated_at' => now()]
            );
            
            // Revenue tracking when booking completed
            if ($newStatus === 'completed' && $oldStatus !== 'completed') {
                $amount = $booking->total_amount ?? 0;
                
                // Daily revenue
                DB::table('analytics_revenue')->updateOrInsert(
                    ['date' => $today],
                    [
                        'total_revenue' => DB::raw('total_revenue + ' . $amount),
                        'completed_bookings' => DB::raw('completed_bookings + 1'),
                        'updated_at' => now()
                    ]
                );
                
                // Customer LTV
                DB::table('customer_ltv')->updateOrInsert(
                    ['user_id' => $booking->user_id],
                    [
                        'total_value' => DB::raw('total_value + ' . $amount),
                        'completed_bookings' => DB::raw('completed_bookings + 1'),
                        'updated_at' => now()
                    ]
                );
            }
            
            // Track cancellations
            if ($newStatus === 'cancelled') {
                DB::table('analytics_by_category')->updateOrInsert(
                    ['date' => $today, 'metric' => 'booking_cancelled', 'category' => 'from_' . $oldStatus],
                    ['value' => DB::raw('value + 1'), 'updated_at' => now()]
                );
            }

            Log::info("Booking analytics tracked", [
                'booking_id' => $booking->id,
                'status_change' => $oldStatus . '_to_' . $newStatus
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to track booking analytics: " . $e->getMessage());
        }
    }

    /**
     * Track admin report generation
     */
    private function trackReportGeneration(ReportGeneratedEvent $event): void
    {
        $admin = $event->getGeneratedBy();
        $reportType = $event->getReportType();
        $today = now()->format('Y-m-d');
        
        try {
            // Daily report generation count
            DB::table('analytics_daily')->updateOrInsert(
                ['date' => $today, 'metric' => 'reports_generated'],
                ['value' => DB::raw('value + 1'), 'updated_at' => now()]
            );
            
            // Report types
            DB::table('analytics_by_category')->updateOrInsert(
                ['date' => $today, 'metric' => 'report_type', 'category' => $reportType],
                ['value' => DB::raw('value + 1'), 'updated_at' => now()]
            );

            Log::info("Report generation analytics tracked", [
                'admin_id' => $admin->id,
                'report_type' => $reportType
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to track report analytics: " . $e->getMessage());
        }
    }

    /**
     * Simple profile completeness check
     */
    private function isProfileComplete($user): bool
    {
        return !empty($user->name) && 
               !empty($user->email) && 
               !empty($user->phone) && 
               !empty($user->address);
    }

    /**
     * Simple browser detection for web analytics
    */
    private function getBrowser(string $userAgent): string
    {
        if (strpos($userAgent, 'Edg') !== false) return 'Edge';
        if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
        if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (strpos($userAgent, 'Safari') !== false) return 'Safari';
        return 'Other';
    }
}