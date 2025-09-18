<?php
namespace App\Http\Controllers\Observer\Observers;

use App\Http\Controllers\Observer\Contracts\ObserverInterface;
use App\Http\Controllers\Observer\Events\UserRegisteredEvent;
use App\Http\Controllers\Observer\Events\UserLoginEvent;
use App\Http\Controllers\Observer\Events\BookingStatusChangedEvent;
use App\Http\Controllers\Observer\Events\ReportGeneratedEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
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
                    $this->trackCustomerRegistrationMetrics($eventData);
                    break;
                    
                case $eventData instanceof UserLoginEvent:
                    $this->trackCustomerEngagementMetrics($eventData);
                    break;
                    
                case $eventData instanceof BookingStatusChangedEvent:
                    $this->trackBookingAnalytics($eventData);
                    break;
                    
                case $eventData instanceof ReportGeneratedEvent:
                    $this->trackAdminActivityMetrics($eventData);
                    break;
            }
        } catch (\Exception $e) {
            Log::error("AnalyticsObserver failed: " . $e->getMessage());
        }
    }

    /**
     * Track customer registration metrics for admin management insights
     */
    private function trackCustomerRegistrationMetrics(UserRegisteredEvent $event): void
    {
        $user = $event->getUser();
        $registrationData = $event->getRegistrationData();
        $timestamp = $event->getTimestamp();
        
        try {
            // Track basic registration metrics
            $this->updateDailyMetrics('customer_registrations', $timestamp);
            
            // Track registration source analytics
            $source = $registrationData['source'] ?? 'web';
            $this->updateMetricsByCategory('registrations_by_source', $source, $timestamp);
            
            // Track customer profile completeness
            $profileCompleteness = $this->calculateProfileCompleteness($user);
            $this->updateMetricsByCategory('profile_completeness', $profileCompleteness, $timestamp);
            
            // Track demographic data (if available)
            if (!empty($user->date_of_birth)) {
                $ageGroup = $this->getAgeGroup($user->date_of_birth);
                $this->updateMetricsByCategory('customers_by_age_group', $ageGroup, $timestamp);
            }
            
            // Track geographic distribution (if available)
            if (!empty($registrationData['registration_ip'])) {
                $location = $this->getLocationFromIP($registrationData['registration_ip']);
                if ($location) {
                    $this->updateMetricsByCategory('customers_by_location', $location, $timestamp);
                }
            }
            
            // Update customer lifecycle metrics
            $this->updateCustomerLifecycleMetrics($user, 'registered');
            
            // Track hourly registration patterns
            $this->updateHourlyMetrics('registrations', $timestamp);
            
            // Update real-time dashboard metrics
            $this->updateRealTimeDashboard('new_customers', 1);
            
            Log::info("Customer registration analytics tracked", [
                'user_id' => $user->id,
                'source' => $source,
                'profile_completeness' => $profileCompleteness
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to track registration metrics: " . $e->getMessage());
        }
    }

    /**
     * Track customer engagement and behavior metrics
     */
    private function trackCustomerEngagementMetrics(UserLoginEvent $event): void
    {
        $user = $event->getUser();
        $loginData = $event->getLoginData();
        $timestamp = $event->getTimestamp();
        
        try {
            // Track daily active users
            $this->updateDailyMetrics('daily_active_users', $timestamp);
            
            // Track customer session data
            $this->trackCustomerSession($user, $loginData, $timestamp);
            
            // Track customer retention
            $this->updateCustomerRetentionMetrics($user, $timestamp);
            
            // Track login patterns
            $this->updateHourlyMetrics('customer_logins', $timestamp);
            
            // Track device/browser analytics
            if (isset($loginData['user_agent'])) {
                $deviceInfo = $this->parseUserAgent($loginData['user_agent']);
                $this->updateMetricsByCategory('logins_by_device', $deviceInfo['device'], $timestamp);
                $this->updateMetricsByCategory('logins_by_browser', $deviceInfo['browser'], $timestamp);
            }
            
            // Track customer behavior patterns
            $this->updateCustomerBehaviorMetrics($user, 'login', $timestamp);
            
            // Update real-time dashboard
            $this->updateRealTimeDashboard('active_sessions', 1);
            
            Log::info("Customer engagement analytics tracked", [
                'user_id' => $user->id,
                'session_type' => $this->getSessionType($user, $timestamp)
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to track engagement metrics: " . $e->getMessage());
        }
    }

    /**
     * Track booking and business analytics
     */
    private function trackBookingAnalytics(BookingStatusChangedEvent $event): void
    {
        $booking = $event->getBooking();
        $oldStatus = $event->getOldStatus();
        $newStatus = $event->getNewStatus();
        $timestamp = $event->getTimestamp();
        
        try {
            // Track booking status transitions
            $transition = "{$oldStatus}_to_{$newStatus}";
            $this->updateMetricsByCategory('booking_transitions', $transition, $timestamp);
            
            // Track booking funnel metrics
            $this->updateBookingFunnelMetrics($oldStatus, $newStatus, $timestamp);
            
            // Track revenue impact
            if ($newStatus === 'completed') {
                $this->updateRevenueMetrics($booking, $timestamp);
            } elseif ($newStatus === 'cancelled') {
                $this->updateCancellationMetrics($booking, $timestamp);
            }
            
            // Track customer lifetime value impact
            $this->updateCustomerLTVMetrics($booking->user_id, $newStatus, $booking->total_amount ?? 0);
            
            // Track admin efficiency metrics
            if (in_array($newStatus, ['confirmed', 'active', 'completed'])) {
                $this->trackAdminProcessingEfficiency($booking, $timestamp);
            }
            
            // Track vehicle utilization
            if (isset($booking->vehicle_id)) {
                $this->updateVehicleUtilizationMetrics($booking->vehicle_id, $newStatus, $timestamp);
            }
            
            // Update business intelligence metrics
            $this->updateBusinessIntelligenceMetrics($booking, $oldStatus, $newStatus);
            
            Log::info("Booking analytics tracked", [
                'booking_id' => $booking->id,
                'transition' => $transition,
                'revenue_impact' => $newStatus === 'completed' ? $booking->total_amount : 0
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to track booking analytics: " . $e->getMessage());
        }
    }

    /**
     * Track admin activity and management metrics
     */
    private function trackAdminActivityMetrics(ReportGeneratedEvent $event): void
    {
        $admin = $event->getGeneratedBy();
        $reportType = $event->getReportType();
        $timestamp = $event->getTimestamp();
        
        try {
            // Track admin productivity
            $this->updateDailyMetrics('admin_reports_generated', $timestamp);
            $this->updateMetricsByCategory('reports_by_type', $reportType, $timestamp);
            $this->updateMetricsByCategory('reports_by_admin', $admin->id, $timestamp);
            
            // Track admin engagement patterns
            $this->updateAdminEngagementMetrics($admin, 'report_generated', $timestamp);
            
            // Track system utilization
            $this->updateSystemUtilizationMetrics('report_generation', $timestamp);
            
            Log::info("Admin activity analytics tracked", [
                'admin_id' => $admin->id,
                'report_type' => $reportType
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to track admin activity metrics: " . $e->getMessage());
        }
    }

    /**
     * Update daily metrics
     */
    private function updateDailyMetrics(string $metric, Carbon $timestamp): void
    {
        $date = $timestamp->format('Y-m-d');
        
        DB::table('analytics_daily')->updateOrInsert(
            ['date' => $date, 'metric' => $metric],
            [
                'value' => DB::raw('value + 1'),
                'updated_at' => now()
            ]
        );
    }

    /**
     * Update metrics by category
     */
    private function updateMetricsByCategory(string $metric, string $category, Carbon $timestamp): void
    {
        $date = $timestamp->format('Y-m-d');
        
        DB::table('analytics_by_category')->updateOrInsert(
            ['date' => $date, 'metric' => $metric, 'category' => $category],
            [
                'value' => DB::raw('value + 1'),
                'updated_at' => now()
            ]
        );
    }

    /**
     * Update hourly metrics for pattern analysis
     */
    private function updateHourlyMetrics(string $metric, Carbon $timestamp): void
    {
        $hour = $timestamp->format('Y-m-d H:00:00');
        
        DB::table('analytics_hourly')->updateOrInsert(
            ['datetime' => $hour, 'metric' => $metric],
            [
                'value' => DB::raw('value + 1'),
                'updated_at' => now()
            ]
        );
    }

    /**
     * Calculate profile completeness score
     */
    private function calculateProfileCompleteness($user): string
    {
        $score = 0;
        $fields = ['name', 'email', 'phone', 'date_of_birth', 'address'];
        
        foreach ($fields as $field) {
            if (!empty($user->$field)) {
                $score += 20; // Each field is worth 20%
            }
        }
        
        return match(true) {
            $score >= 80 => 'complete',
            $score >= 60 => 'mostly_complete',
            $score >= 40 => 'partial',
            default => 'minimal'
        };
    }

    /**
     * Get age group from date of birth
     */
    private function getAgeGroup($dateOfBirth): string
    {
        $age = Carbon::parse($dateOfBirth)->age;
        
        return match(true) {
            $age < 25 => '18-24',
            $age < 35 => '25-34',
            $age < 45 => '35-44',
            $age < 55 => '45-54',
            $age < 65 => '55-64',
            default => '65+'
        };
    }

    /**
     * Track customer session data
     */
    private function trackCustomerSession($user, array $loginData, Carbon $timestamp): void
    {
        DB::table('customer_sessions')->insert([
            'user_id' => $user->id,
            'session_start' => $timestamp,
            'ip_address' => $loginData['ip_address'] ?? null,
            'user_agent' => $loginData['user_agent'] ?? null,
            'is_mobile' => $this->isMobileDevice($loginData['user_agent'] ?? ''),
            'created_at' => now()
        ]);
    }

    /**
     * Update customer retention metrics
     */
    private function updateCustomerRetentionMetrics($user, Carbon $timestamp): void
    {
        $daysSinceLastLogin = $user->last_login_at ? 
            $user->last_login_at->diffInDays($timestamp) : null;
        
        if ($daysSinceLastLogin !== null) {
            $retentionCategory = match(true) {
                $daysSinceLastLogin <= 1 => 'daily_returning',
                $daysSinceLastLogin <= 7 => 'weekly_returning',
                $daysSinceLastLogin <= 30 => 'monthly_returning',
                default => 'long_term_returning'
            };
            
            $this->updateMetricsByCategory('customer_retention', $retentionCategory, $timestamp);
        }
    }

    /**
     * Update booking funnel metrics
     */
    private function updateBookingFunnelMetrics(string $oldStatus, string $newStatus, Carbon $timestamp): void
    {
        // Track progression through booking funnel
        $funnelStages = ['pending', 'confirmed', 'active', 'completed'];
        
        if (in_array($newStatus, $funnelStages)) {
            $this->updateMetricsByCategory('booking_funnel', $newStatus, $timestamp);
        }
        
        // Track dropout points
        if ($newStatus === 'cancelled') {
            $this->updateMetricsByCategory('booking_dropouts', "from_{$oldStatus}", $timestamp);
        }
    }

    /**
     * Update revenue metrics
     */
    private function updateRevenueMetrics($booking, Carbon $timestamp): void
    {
        $date = $timestamp->format('Y-m-d');
        
        DB::table('analytics_revenue')->updateOrInsert(
            ['date' => $date],
            [
                'total_revenue' => DB::raw('total_revenue + ' . ($booking->total_amount ?? 0)),
                'completed_bookings' => DB::raw('completed_bookings + 1'),
                'updated_at' => now()
            ]
        );
    }

    /**
     * Update real-time dashboard metrics
     */
    private function updateRealTimeDashboard(string $metric, int $increment): void
    {
        $cacheKey = "dashboard_metric_{$metric}";
        $currentValue = Cache::get($cacheKey, 0);
        Cache::put($cacheKey, $currentValue + $increment, now()->addHours(24));
    }

    /**
     * Parse user agent for device/browser info
     */
    private function parseUserAgent(string $userAgent): array
    {
        // Simplified user agent parsing
        $isMobile = $this->isMobileDevice($userAgent);
        $browser = $this->getBrowserFromUserAgent($userAgent);
        
        return [
            'device' => $isMobile ? 'mobile' : 'desktop',
            'browser' => $browser
        ];
    }

    /**
     * Check if device is mobile
     */
    private function isMobileDevice(string $userAgent): bool
    {
        return preg_match('/Mobile|Android|iPhone|iPad/', $userAgent) > 0;
    }

    /**
     * Get browser from user agent
     */
    private function getBrowserFromUserAgent(string $userAgent): string
    {
        if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
        if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (strpos($userAgent, 'Safari') !== false) return 'Safari';
        if (strpos($userAgent, 'Edge') !== false) return 'Edge';
        return 'Other';
    }

    /**
     * Get session type
     */
    private function getSessionType($user, Carbon $timestamp): string
    {
        return $user->last_login_at && 
               $user->last_login_at->diffInHours($timestamp) < 24 ? 
               'returning_session' : 'new_session';
    }

    /**
     * Additional helper methods for comprehensive analytics
     */
    private function getLocationFromIP(string $ip): ?string
    {
        // Simplified IP to location - in production use a proper service
        return 'Unknown';
    }

    private function updateCustomerLifecycleMetrics($user, string $stage): void
    {
        // Track customer journey stages
        $this->updateMetricsByCategory('customer_lifecycle', $stage, now());
    }

    private function updateCustomerBehaviorMetrics($user, string $action, Carbon $timestamp): void
    {
        // Track customer behavior patterns
        $this->updateMetricsByCategory('customer_behavior', $action, $timestamp);
    }

    private function updateCancellationMetrics($booking, Carbon $timestamp): void
    {
        $this->updateMetricsByCategory('cancellations', 'booking_cancelled', $timestamp);
    }

    private function updateCustomerLTVMetrics(int $userId, string $status, float $amount): void
    {
        if ($status === 'completed' && $amount > 0) {
            DB::table('customer_ltv')->updateOrInsert(
                ['user_id' => $userId],
                [
                    'total_value' => DB::raw('total_value + ' . $amount),
                    'completed_bookings' => DB::raw('completed_bookings + 1'),
                    'updated_at' => now()
                ]
            );
        }
    }

    private function trackAdminProcessingEfficiency($booking, Carbon $timestamp): void
    {
        // Track how quickly admins process bookings
        if (isset($booking->created_at)) {
            $processingTime = $booking->created_at->diffInHours($timestamp);
            $this->updateMetricsByCategory('admin_efficiency', 'processing_time_hours', $timestamp);
        }
    }

    private function updateVehicleUtilizationMetrics(int $vehicleId, string $status, Carbon $timestamp): void
    {
        if ($status === 'active') {
            $this->updateMetricsByCategory('vehicle_utilization', 'active_rental', $timestamp);
        }
    }

    private function updateBusinessIntelligenceMetrics($booking, string $oldStatus, string $newStatus): void
    {
        // Track key business metrics for BI dashboard
        $this->updateMetricsByCategory('business_intelligence', 'status_change', now());
    }

    private function updateAdminEngagementMetrics($admin, string $activity, Carbon $timestamp): void
    {
        $this->updateMetricsByCategory('admin_engagement', $activity, $timestamp);
    }

    private function updateSystemUtilizationMetrics(string $feature, Carbon $timestamp): void
    {
        $this->updateMetricsByCategory('system_utilization', $feature, $timestamp);
    }
}