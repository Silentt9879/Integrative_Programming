<?php
namespace App\Http\Controllers\Observer\Observers;

use App\Http\Controllers\Observer\Contracts\ObserverInterface;
use App\Http\Controllers\Observer\Events\BaseEvent;
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
            if ($eventData instanceof BaseEvent) {
                // Create structured audit log entry
                $this->createAuditLog($eventData);
                
                // Handle specific event types for detailed logging
                switch (true) {
                    case $eventData instanceof UserRegisteredEvent:
                        $this->logCustomerRegistration($eventData);
                        break;
                        
                    case $eventData instanceof UserLoginEvent:
                        $this->logCustomerLogin($eventData);
                        break;
                        
                    case $eventData instanceof BookingStatusChangedEvent:
                        $this->logBookingStatusChange($eventData);
                        break;
                        
                    case $eventData instanceof ReportGeneratedEvent:
                        $this->logAdminActivity($eventData);
                        break;
                }
                
                // Create application log entry
                $this->createApplicationLog($eventData);
                
            } else {
                Log::warning("LoggingObserver received non-event data", [
                    'data_type' => gettype($eventData),
                    'data' => $eventData
                ]);
            }
        } catch (\Exception $e) {
            Log::error("LoggingObserver failed to log event: " . $e->getMessage());
        }
    }

    /**
     * Create comprehensive audit log for compliance and monitoring
     */
    private function createAuditLog(BaseEvent $event): void
    {
        try {
            $auditData = [
                'event_id' => $this->generateEventId(),
                'event_type' => class_basename($event->getEventType()),
                'event_category' => $this->getEventCategory($event),
                'timestamp' => $event->getTimestamp()->toISOString(),
                'user_id' => $this->extractUserId($event),
                'admin_id' => $this->getCurrentAdminId(),
                'ip_address' => request()->ip() ?? 'unknown',
                'user_agent' => request()->userAgent() ?? 'unknown',
                'session_id' => session()->getId() ?? null,
                'metadata' => json_encode($event->getMetadata()),
                'event_data' => json_encode($event->toArray()),
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
     * Log customer registration with detailed tracking
     */
    private function logCustomerRegistration(UserRegisteredEvent $event): void
    {
        $user = $event->getUser();
        $registrationData = $event->getRegistrationData();
        
        try {
            // Create customer management log
            $customerLogData = [
                'log_type' => 'customer_registration',
                'customer_id' => $user->id,
                'customer_email' => $user->email,
                'customer_name' => $user->name,
                'registration_source' => $registrationData['source'] ?? 'web',
                'registration_ip' => $registrationData['registration_ip'] ?? request()->ip(),
                'profile_completeness' => $this->calculateProfileCompleteness($user),
                'requires_approval' => $this->requiresApproval($user),
                'data_protection_consent' => true, // Assume consent given during registration
                'marketing_consent' => $registrationData['marketing_consent'] ?? false,
                'metadata' => json_encode([
                    'has_phone' => !empty($user->phone),
                    'has_address' => !empty($user->address),
                    'has_date_of_birth' => !empty($user->date_of_birth),
                    'registration_time' => $event->getTimestamp()->toISOString()
                ]),
                'created_at' => now()
            ];

            DB::table('customer_management_logs')->insert($customerLogData);
            
            // Create compliance log for GDPR/data protection
            $this->createComplianceLog([
                'type' => 'data_collection',
                'subject_type' => 'customer',
                'subject_id' => $user->id,
                'action' => 'personal_data_collected',
                'legal_basis' => 'legitimate_interest',
                'data_categories' => $this->getCollectedDataCategories($user),
                'retention_period' => '7_years',
                'consent_status' => 'given'
            ]);
            
            Log::info("CUSTOMER_REGISTRATION_LOGGED", [
                'customer_id' => $user->id,
                'customer_email' => $user->email,
                'registration_source' => $registrationData['source'] ?? 'web',
                'profile_complete' => $this->calculateProfileCompleteness($user) === 'complete'
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to log customer registration: " . $e->getMessage());
        }
    }

    /**
     * Log customer login with security tracking
     */
    private function logCustomerLogin(UserLoginEvent $event): void
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
                'location_estimate' => $this->estimateLocation($event->getIpAddress()),
                'device_fingerprint' => $this->generateDeviceFingerprint($loginData),
                'previous_login' => $user->last_login_at?->toISOString(),
                'login_timestamp' => $event->getTimestamp()->toISOString(),
                'metadata' => json_encode($loginData),
                'created_at' => now()
            ];

            DB::table('security_logs')->insert($securityLogData);
            
            // Track customer activity
            $this->createCustomerActivityLog($user->id, 'login', [
                'ip_address' => $event->getIpAddress(),
                'session_start' => $event->getTimestamp()->toISOString(),
                'login_data' => $loginData
            ]);
            
            Log::info("CUSTOMER_LOGIN_LOGGED", [
                'customer_id' => $user->id,
                'ip_address' => $event->getIpAddress(),
                'is_suspicious' => $securityLogData['is_suspicious']
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to log customer login: " . $e->getMessage());
        }
    }

    /**
     * Log booking status changes with business impact tracking
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
                'created_at' => now()
            ];

            DB::table('business_operation_logs')->insert($businessLogData);
            
            // Create customer interaction log if status affects customer
            if ($this->affectsCustomer($event->getNewStatus())) {
                $this->createCustomerInteractionLog($booking->user_id, 'booking_status_updated', [
                    'booking_id' => $booking->id,
                    'status_change' => "{$event->getOldStatus()}_to_{$event->getNewStatus()}",
                    'notification_sent' => true
                ]);
            }
            
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
     * Log admin activities for management oversight
     */
    private function logAdminActivity(ReportGeneratedEvent $event): void
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
                'data_accessed' => $this->getDataAccessedTypes($event->getReportType()),
                'export_format' => $generationData['format'] ?? 'pdf',
                'file_generated' => $generationData['file_generated'] ?? false,
                'ip_address' => request()->ip(),
                'session_id' => session()->getId(),
                'generation_timestamp' => $event->getTimestamp()->toISOString(),
                'metadata' => json_encode($generationData),
                'created_at' => now()
            ];

            DB::table('admin_activity_logs')->insert($adminLogData);
            
            // Create compliance log for data access
            $this->createComplianceLog([
                'type' => 'data_access',
                'subject_type' => 'admin',
                'subject_id' => $admin->id,
                'action' => 'report_generated',
                'data_categories' => $this->getReportDataCategories($event->getReportType()),
                'access_purpose' => 'business_reporting',
                'legal_basis' => 'legitimate_interest'
            ]);
            
            Log::info("ADMIN_ACTIVITY_LOGGED", [
                'admin_id' => $admin->id,
                'activity' => 'report_generation',
                'report_type' => $event->getReportType(),
                'status' => $generationData['status'] ?? 'unknown'
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to log admin activity: " . $e->getMessage());
        }
    }

    /**
     * Create application log entry with structured format
     */
    private function createApplicationLog(BaseEvent $event): void
    {
        $logData = [
            'event_class' => get_class($event),
            'event_type' => class_basename($event->getEventType()),
            'timestamp' => $event->getTimestamp()->toISOString(),
            'metadata' => $event->getMetadata(),
            'event_data' => $event->toArray(),
            'processing_time' => microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true))
        ];

        $message = "Event processed: " . class_basename($event->getEventType());
        Log::info($message, $logData);
    }

    /**
     * Create compliance log for data protection regulations
     */
    private function createComplianceLog(array $complianceData): void
    {
        try {
            $complianceLogData = array_merge($complianceData, [
                'timestamp' => now()->toISOString(),
                'regulation' => 'GDPR',
                'created_at' => now()
            ]);

            DB::table('compliance_logs')->insert($complianceLogData);
            
        } catch (\Exception $e) {
            Log::error("Failed to create compliance log: " . $e->getMessage());
        }
    }

    /**
     * Create customer activity log
     */
    private function createCustomerActivityLog(int $customerId, string $activity, array $data): void
    {
        try {
            DB::table('customer_activity_logs')->insert([
                'customer_id' => $customerId,
                'activity_type' => $activity,
                'activity_data' => json_encode($data),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'session_id' => session()->getId(),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to create customer activity log: " . $e->getMessage());
        }
    }

    /**
     * Create customer interaction log
     */
    private function createCustomerInteractionLog(int $customerId, string $interaction, array $data): void
    {
        try {
            DB::table('customer_interaction_logs')->insert([
                'customer_id' => $customerId,
                'interaction_type' => $interaction,
                'interaction_data' => json_encode($data),
                'timestamp' => now()->toISOString(),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to create customer interaction log: " . $e->getMessage());
        }
    }

    /**
     * Helper methods for log analysis and classification
     */
    private function generateEventId(): string
    {
        return 'evt_' . uniqid() . '_' . time();
    }

    private function getEventCategory(BaseEvent $event): string
    {
        return match(true) {
            $event instanceof UserRegisteredEvent => 'customer_management',
            $event instanceof UserLoginEvent => 'security',
            $event instanceof BookingStatusChangedEvent => 'business_operations',
            $event instanceof ReportGeneratedEvent => 'admin_activity',
            default => 'general'
        };
    }

    private function extractUserId(BaseEvent $event): ?int
    {
        $metadata = $event->getMetadata();
        return $metadata['user_id'] ?? null;
    }

    private function getCurrentAdminId(): ?int
    {
        return Auth::check() && Auth::user()->is_admin ? Auth::id() : null;
    }

    private function getEventSeverity(BaseEvent $event): string
    {
        return match(true) {
            $event instanceof UserRegisteredEvent => 'info',
            $event instanceof UserLoginEvent => 'info',
            $event instanceof BookingStatusChangedEvent => 'warning',
            $event instanceof ReportGeneratedEvent => 'info',
            default => 'info'
        };
    }

    private function requiresComplianceReview(BaseEvent $event): bool
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

    private function getCollectedDataCategories($user): array
    {
        $categories = ['identity', 'contact'];
        if (!empty($user->date_of_birth)) $categories[] = 'demographic';
        if (!empty($user->address)) $categories[] = 'location';
        return $categories;
    }

    private function detectSuspiciousActivity($user, string $ipAddress): bool
    {
        // Simplified suspicious activity detection
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

    private function estimateLocation(string $ipAddress): string
    {
        // Simplified location estimation - use proper geolocation service in production
        return 'Unknown';
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
            'confirmed' => 'positive',
            'active' => 'positive',
            'completed' => 'positive',
            'cancelled' => 'negative',
            default => 'neutral'
        };
    }

    private function requiresFollowup(string $status): bool
    {
        return in_array($status, ['cancelled', 'active']);
    }

    private function affectsCustomer(string $status): bool
    {
        return in_array($status, ['confirmed', 'active', 'completed', 'cancelled']);
    }

    private function calculateExecutionTime(array $generationData): ?float
    {
        if (isset($generationData['generation_start']) && isset($generationData['generation_completed'])) {
            $start = Carbon::parse($generationData['generation_start']);
            $end = Carbon::parse($generationData['generation_completed']);
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

    private function getReportDataCategories(string $reportType): array
    {
        return match($reportType) {
            'overview' => ['business_data', 'financial_data'],
            'customer_report' => ['personal_data', 'business_data'],
            'financial_report' => ['financial_data', 'business_data'],
            default => ['business_data']
        };
    }
}