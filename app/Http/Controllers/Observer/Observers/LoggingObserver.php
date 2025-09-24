<?php
namespace App\Http\Controllers\Observer\Observers;

use App\Http\Controllers\Observer\Contracts\ObserverInterface;
use App\Http\Controllers\Observer\Events\UserRegisteredEvent;
use App\Http\Controllers\Observer\Events\UserLoginEvent;
use App\Http\Controllers\Observer\Events\BookingStatusChangedEvent;
use App\Http\Controllers\Observer\Events\ReportGeneratedEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LoggingObserver implements ObserverInterface
{
    public function getName(): string
    {
        return 'LoggingObserver';
    }

    public function update($eventData): void
    {
        try {
            // Create comprehensive audit log entry first
            $this->createAuditLog($eventData);
            
            // Handle specific event types for detailed logging
            switch (true) {
                case $eventData instanceof UserRegisteredEvent:
                    $this->logUserRegistration($eventData);
                    break;
                    
                case $eventData instanceof UserLoginEvent:
                    $this->logUserLogin($eventData);
                    break;
                    
                case $eventData instanceof BookingStatusChangedEvent:
                    $this->logBookingStatusChange($eventData);
                    break;
                    
                case $eventData instanceof ReportGeneratedEvent:
                    $this->logReportGeneration($eventData);
                    break;
            }
            
            // Log to Laravel's standard log as well
            $this->logToFile($eventData);
            
        } catch (\Exception $e) {
            Log::error("LoggingObserver failed to log event: " . $e->getMessage(), [
                'event_type' => get_class($eventData),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Create comprehensive audit log for compliance and monitoring
     */
    private function createAuditLog($event): void
    {
        try {
            $auditData = [
                'event_id' => $this->generateEventId(),
                'event_type' => class_basename(get_class($event)),
                'event_category' => $this->getEventCategory($event),
                'timestamp' => now()->toISOString(),
                'user_id' => $this->extractUserId($event),
                'admin_id' => $this->getCurrentAdminId(),
                'ip_address' => request()->ip() ?? 'unknown',
                'user_agent' => request()->userAgent() ?? 'unknown',
                'session_id' => session()->getId() ?? null,
                'metadata' => json_encode($event->getMetadata() ?? []),
                'event_data' => json_encode($event->toArray() ?? []),
                'severity' => $this->getEventSeverity($event),
                'compliance_flag' => $this->requiresComplianceReview($event),
                'created_at' => now(),
                'updated_at' => now()
            ];

            DB::table('audit_logs')->insert($auditData);
            
            Log::channel('audit')->info("AUDIT_LOG_CREATED", $auditData);
            
        } catch (\Exception $e) {
            Log::error("Failed to create audit log: " . $e->getMessage());
        }
    }

    /**
     * Log user registration events
     */
    private function logUserRegistration(UserRegisteredEvent $event): void
    {
        $user = $event->getUser();
        $registrationData = $event->getRegistrationData();
        
        try {
            // Create customer management log
            $logData = [
                'log_type' => 'customer_registration',
                'customer_id' => $user->id,
                'customer_email' => $user->email,
                'customer_name' => $user->name,
                'registration_source' => $registrationData['source'] ?? 'web',
                'registration_ip' => $registrationData['registration_ip'] ?? request()->ip(),
                'profile_completeness' => $this->calculateProfileCompleteness($user),
                'requires_approval' => $this->requiresApproval($user),
                'data_protection_consent' => true,
                'marketing_consent' => $registrationData['marketing_consent'] ?? false,
                'metadata' => json_encode([
                    'has_phone' => !empty($user->phone),
                    'has_address' => !empty($user->address),
                    'has_date_of_birth' => !empty($user->date_of_birth),
                    'registration_time' => $event->getTimestamp()->toISOString()
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ];

            DB::table('customer_management_logs')->insert($logData);
            
            // Track customer activity
            $this->trackCustomerActivity($user->id, 'registration', [
                'ip_address' => $registrationData['registration_ip'] ?? request()->ip(),
                'user_agent' => $registrationData['user_agent'] ?? request()->userAgent(),
                'registration_data' => $registrationData
            ]);
            
            Log::info("CUSTOMER_REGISTRATION_LOGGED", [
                'customer_id' => $user->id,
                'customer_email' => $user->email,
                'registration_source' => $registrationData['source'] ?? 'web'
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to log user registration: " . $e->getMessage());
        }
    }

    /**
     * Log user login events with security tracking
     */
    private function logUserLogin(UserLoginEvent $event): void
    {
        $user = $event->getUser();
        $loginData = $event->getLoginData();
        
        try {
            // Create security log
            $securityLogData = [
                'log_type' => 'customer_login',
                'customer_id' => $user->id,
                'ip_address' => $event->getIpAddress(),
                'user_agent' => $loginData['user_agent'] ?? request()->userAgent(),
                'login_method' => 'email_password',
                'session_id' => session()->getId(),
                'remember_me' => $loginData['remember_me'] ?? false,
                'is_suspicious' => $this->detectSuspiciousActivity($user, $event->getIpAddress()),
                'location_estimate' => 'Unknown', // Could integrate with geolocation service
                'device_fingerprint' => $this->generateDeviceFingerprint($loginData),
                'previous_login' => $user->last_login_at?->toISOString(),
                'login_timestamp' => $event->getTimestamp()->toISOString(),
                'metadata' => json_encode($loginData),
                'created_at' => now(),
                'updated_at' => now()
            ];

            DB::table('security_logs')->insert($securityLogData);
            
            // Track customer activity
            $this->trackCustomerActivity($user->id, 'login', [
                'ip_address' => $event->getIpAddress(),
                'session_start' => $event->getTimestamp()->toISOString(),
                'login_data' => $loginData
            ]);
            
            Log::info("USER_LOGIN_LOGGED", [
                'customer_id' => $user->id,
                'ip_address' => $event->getIpAddress(),
                'is_suspicious' => $securityLogData['is_suspicious']
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to log user login: " . $e->getMessage());
        }
    }

    /**
     * Log booking status changes
     */
    private function logBookingStatusChange(BookingStatusChangedEvent $event): void
    {
        $booking = $event->getBooking();
        $changeData = $event->getChangeData();
        
        try {
            // Create business operation log
            $businessLogData = [
                'log_type' => 'booking_status_change',
                'booking_id' => $booking->id,
                'customer_id' => $booking->user_id,
                'vehicle_id' => $booking->vehicle_id ?? null,
                'old_status' => $event->getOldStatus(),
                'new_status' => $event->getNewStatus(),
                'changed_by_type' => Auth::check() && Auth::user()->is_admin ? 'admin' : 'system',
                'changed_by_id' => Auth::id(),
                'change_reason' => $changeData['change_reason'] ?? null,
                'admin_notes' => $changeData['admin_notes'] ?? null,
                'financial_impact' => $this->calculateFinancialImpact($booking, $event->getOldStatus(), $event->getNewStatus()),
                'customer_impact' => $this->assessCustomerImpact($event->getNewStatus()),
                'requires_followup' => $this->requiresFollowup($event->getNewStatus()),
                'change_timestamp' => $event->getTimestamp()->toISOString(),
                'metadata' => json_encode($changeData),
                'created_at' => now(),
                'updated_at' => now()
            ];

            DB::table('business_operation_logs')->insert($businessLogData);
            
            Log::info("BOOKING_STATUS_CHANGE_LOGGED", [
                'booking_id' => $booking->id,
                'status_change' => "{$event->getOldStatus()}_to_{$event->getNewStatus()}",
                'changed_by' => $businessLogData['changed_by_type'],
                'financial_impact' => $businessLogData['financial_impact']
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to log booking status change: " . $e->getMessage());
        }
    }

    /**
     * Log report generation events
     */
    private function logReportGeneration(ReportGeneratedEvent $event): void
    {
        $admin = $event->getGeneratedBy();
        $generationData = $event->getGenerationData();
        
        try {
            // Create admin activity log
            $adminLogData = [
                'log_type' => 'report_generation',
                'admin_id' => $admin->id,
                'admin_name' => $admin->name,
                'admin_email' => $admin->email,
                'report_type' => $event->getReportType(),
                'report_parameters' => json_encode($event->getReportData()),
                'generation_status' => $generationData['status'] ?? 'unknown',
                'execution_time' => $this->calculateExecutionTime($generationData),
                'data_accessed' => json_encode($this->getDataAccessedTypes($event->getReportType())),
                'export_format' => $generationData['format'] ?? 'pdf',
                'file_generated' => $generationData['file_generated'] ?? false,
                'ip_address' => request()->ip(),
                'session_id' => session()->getId(),
                'generation_timestamp' => $event->getTimestamp()->toISOString(),
                'metadata' => json_encode($generationData),
                'created_at' => now(),
                'updated_at' => now()
            ];

            DB::table('admin_activity_logs')->insert($adminLogData);
            
            Log::info("REPORT_GENERATION_LOGGED", [
                'admin_id' => $admin->id,
                'report_type' => $event->getReportType(),
                'status' => $generationData['status'] ?? 'unknown'
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to log report generation: " . $e->getMessage());
        }
    }

    /**
     * Log to Laravel's standard logging system
     */
    private function logToFile($event): void
    {
        $logData = [
            'event_class' => get_class($event),
            'event_type' => class_basename(get_class($event)),
            'timestamp' => $event->getTimestamp()->toISOString(),
            'metadata' => $event->getMetadata() ?? [],
            'processing_time' => microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true))
        ];

        $message = "Observer Event: " . class_basename(get_class($event));
        Log::info($message, $logData);
    }

    /**
     * Track customer activity in dedicated table
     */
    private function trackCustomerActivity(int $customerId, string $activity, array $data): void
    {
        try {
            DB::table('customer_activities')->insert([
                'user_id' => $customerId,
                'activity_type' => $activity,
                'ip_address' => $data['ip_address'] ?? request()->ip(),
                'user_agent' => $data['user_agent'] ?? request()->userAgent(),
                'metadata' => json_encode($data),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to track customer activity: " . $e->getMessage());
        }
    }

    /**
     * Helper methods for log analysis and classification
     */
    private function generateEventId(): string
    {
        return 'evt_' . uniqid() . '_' . time();
    }

    private function getEventCategory($event): string
    {
        return match(true) {
            $event instanceof UserRegisteredEvent => 'customer_management',
            $event instanceof UserLoginEvent => 'security',
            $event instanceof BookingStatusChangedEvent => 'business_operations',
            $event instanceof ReportGeneratedEvent => 'admin_activity',
            default => 'general'
        };
    }

    private function extractUserId($event): ?int
    {
        $metadata = $event->getMetadata() ?? [];
        return $metadata['user_id'] ?? null;
    }

    private function getCurrentAdminId(): ?int
    {
        return Auth::check() && Auth::user()->is_admin ? Auth::id() : null;
    }

    private function getEventSeverity($event): string
    {
        return match(true) {
            $event instanceof BookingStatusChangedEvent => 'warning',
            default => 'info'
        };
    }

    private function requiresComplianceReview($event): bool
    {
        return $event instanceof UserRegisteredEvent || 
               $event instanceof ReportGeneratedEvent;
    }

    private function calculateProfileCompleteness($user): string
    {
        $score = 0;
        $fields = ['name', 'email', 'phone', 'date_of_birth', 'address'];
        
        foreach ($fields as $field) {
            if (!empty($user->$field)) {
                $score += 20;
            }
        }
        
        return match(true) {
            $score >= 80 => 'complete',
            $score >= 60 => 'mostly_complete',
            $score >= 40 => 'partial',
            default => 'minimal'
        };
    }

    private function requiresApproval($user): bool
    {
        return $this->calculateProfileCompleteness($user) !== 'complete';
    }

    private function detectSuspiciousActivity($user, string $ipAddress): bool
    {
        try {
            $recentLogins = DB::table('security_logs')
                ->where('customer_id', $user->id)
                ->where('created_at', '>=', now()->subHours(6))
                ->count();
                
            return $recentLogins > 5;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function generateDeviceFingerprint(array $loginData): string
    {
        $userAgent = $loginData['user_agent'] ?? 'unknown';
        return md5($userAgent . request()->ip());
    }

    private function calculateFinancialImpact($booking, string $oldStatus, string $newStatus): float
    {
        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            return $booking->total_amount ?? 0;
        } elseif ($newStatus === 'cancelled' && in_array($oldStatus, ['confirmed', 'active'])) {
            return -($booking->total_amount ?? 0);
        }
        return 0;
    }

    private function assessCustomerImpact(string $status): string
    {
        return match($status) {
            'confirmed', 'active', 'completed' => 'positive',
            'cancelled' => 'negative',
            default => 'neutral'
        };
    }

    private function requiresFollowup(string $status): bool
    {
        return in_array($status, ['cancelled', 'active']);
    }

    private function calculateExecutionTime(array $generationData): ?float
    {
        if (isset($generationData['generation_start']) && isset($generationData['generation_completed'])) {
            $start = \Carbon\Carbon::parse($generationData['generation_start']);
            $end = \Carbon\Carbon::parse($generationData['generation_completed']);
            return $start->diffInSeconds($end);
        }
        return null;
    }

    private function getDataAccessedTypes(string $reportType): array
    {
        return match($reportType) {
            'overview' => ['bookings', 'users', 'revenue'],
            'customer_report' => ['users', 'bookings'],
            'financial_report' => ['bookings', 'revenue', 'vehicles'],
            default => ['general']
        };
    }
}