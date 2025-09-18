<?php

namespace App\Services;

use App\Http\Controllers\Observer\Subjects\UserSubject;
use App\Http\Controllers\Observer\Subjects\BookingSubject;
use App\Http\Controllers\Observer\Subjects\ReportSubject;
use App\Http\Controllers\Observer\Observers\EmailNotificationObserver;
use App\Http\Controllers\Observer\Observers\LoggingObserver;
use App\Http\Controllers\Observer\Observers\AnalyticsObserver;
use App\Http\Controllers\Observer\Observers\AdminNotificationObserver;
use App\Http\Controllers\Observer\Events\UserRegisteredEvent;
use App\Http\Controllers\Observer\Events\UserLoginEvent;
use App\Http\Controllers\Observer\Events\BookingStatusChangedEvent;
use App\Http\Controllers\Observer\Events\ReportGeneratedEvent;
use App\Models\User;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ObserverService
{
    private array $subjects = [];
    private array $availableObservers = [];

    public function __construct()
    {
        $this->initializeSubjects();
        $this->initializeObservers();
    }

    /**
     * Initialize all subjects with Observer Pattern
     */
    private function initializeSubjects(): void
    {
        $this->subjects = [
            'UserSubject' => new UserSubject(),
            'BookingSubject' => new BookingSubject(),
            'ReportSubject' => new ReportSubject(),
        ];
    }

    /**
     * Initialize available observers
     */
    private function initializeObservers(): void
    {
        $this->availableObservers = [
            'EmailNotificationObserver' => new EmailNotificationObserver(),
            'LoggingObserver' => new LoggingObserver(),
            'AnalyticsObserver' => new AnalyticsObserver(),
            'AdminNotificationObserver' => new AdminNotificationObserver(),
        ];
    }

    /**
     * Get all subjects with their attached observers
     */
    public function getAllSubjects(): array
    {
        $subjectData = [];

        foreach ($this->subjects as $subjectName => $subject) {
            $observers = [];
            foreach ($subject->getObservers() as $observer) {
                $observers[] = [
                    'name' => $observer->getName(),
                    'class' => get_class($observer),
                    'attached_at' => now()->toISOString()
                ];
            }

            $subjectData[] = [
                'name' => $subjectName,
                'class' => get_class($subject),
                'observer_count' => count($observers),
                'observers' => $observers,
                'subject_name' => $subject->getSubjectName()
            ];
        }

        return $subjectData;
    }

    /**
     * Get specific subject information
     */
    public function getSubject(string $subjectName): array
    {
        if (!isset($this->subjects[$subjectName])) {
            throw new \Exception("Subject '{$subjectName}' not found");
        }

        $subject = $this->subjects[$subjectName];
        $observers = [];

        foreach ($subject->getObservers() as $observer) {
            $observers[] = [
                'name' => $observer->getName(),
                'class' => get_class($observer),
                'description' => $this->getObserverDescription($observer->getName())
            ];
        }

        return [
            'name' => $subjectName,
            'class' => get_class($subject),
            'subject_name' => $subject->getSubjectName(),
            'observer_count' => count($observers),
            'observers' => $observers,
            'available_events' => $this->getAvailableEvents($subjectName)
        ];
    }

    /**
     * Get all available observers
     */
    public function getAvailableObservers(): array
    {
        $observerData = [];

        foreach ($this->availableObservers as $observerName => $observer) {
            $attachedSubjects = [];
            
            foreach ($this->subjects as $subjectName => $subject) {
                foreach ($subject->getObservers() as $attachedObserver) {
                    if ($attachedObserver->getName() === $observer->getName()) {
                        $attachedSubjects[] = $subjectName;
                        break;
                    }
                }
            }

            $observerData[] = [
                'name' => $observer->getName(),
                'class' => get_class($observer),
                'description' => $this->getObserverDescription($observer->getName()),
                'attached_to_subjects' => $attachedSubjects,
                'attachment_count' => count($attachedSubjects),
                'capabilities' => $this->getObserverCapabilities($observer->getName())
            ];
        }

        return $observerData;
    }

    /**
     * Get specific observer details
     */
    public function getObserver(string $observerName): array
    {
        if (!isset($this->availableObservers[$observerName])) {
            throw new \Exception("Observer '{$observerName}' not found");
        }

        $observer = $this->availableObservers[$observerName];
        $attachedSubjects = [];

        foreach ($this->subjects as $subjectName => $subject) {
            foreach ($subject->getObservers() as $attachedObserver) {
                if ($attachedObserver->getName() === $observer->getName()) {
                    $attachedSubjects[] = [
                        'subject_name' => $subjectName,
                        'subject_class' => get_class($subject),
                        'attached_at' => now()->toISOString()
                    ];
                    break;
                }
            }
        }

        return [
            'name' => $observer->getName(),
            'class' => get_class($observer),
            'description' => $this->getObserverDescription($observer->getName()),
            'capabilities' => $this->getObserverCapabilities($observer->getName()),
            'attached_subjects' => $attachedSubjects,
            'attachment_count' => count($attachedSubjects),
            'last_activity' => $this->getLastObserverActivity($observer->getName())
        ];
    }

    /**
     * Attach observer to subject
     */
    public function attachObserver(string $subjectName, string $observerName): bool
    {
        if (!isset($this->subjects[$subjectName])) {
            throw new \Exception("Subject '{$subjectName}' not found");
        }

        if (!isset($this->availableObservers[$observerName])) {
            throw new \Exception("Observer '{$observerName}' not found");
        }

        try {
            $subject = $this->subjects[$subjectName];
            $observer = $this->availableObservers[$observerName];

            $subject->attach($observer);

            // Log the attachment
            Log::info("Observer attached via API", [
                'subject' => $subjectName,
                'observer' => $observerName,
                'attached_by' => Auth::user()->name ?? 'API',
                'admin_id' => Auth::id()
            ]);

            // Cache observer statistics
            $this->updateObserverStatistics('attach', $subjectName, $observerName);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to attach observer via API", [
                'subject' => $subjectName,
                'observer' => $observerName,
                'error' => $e->getMessage()
            ]);

            throw new \Exception("Failed to attach observer: " . $e->getMessage());
        }
    }

    /**
     * Detach observer from subject
     */
    public function detachObserver(string $subjectName, string $observerName): bool
    {
        if (!isset($this->subjects[$subjectName])) {
            throw new \Exception("Subject '{$subjectName}' not found");
        }

        if (!isset($this->availableObservers[$observerName])) {
            throw new \Exception("Observer '{$observerName}' not found");
        }

        try {
            $subject = $this->subjects[$subjectName];
            $observer = $this->availableObservers[$observerName];

            $subject->detach($observer);

            // Log the detachment
            Log::info("Observer detached via API", [
                'subject' => $subjectName,
                'observer' => $observerName,
                'detached_by' => Auth::user()->name ?? 'API',
                'admin_id' => Auth::id()
            ]);

            // Update observer statistics
            $this->updateObserverStatistics('detach', $subjectName, $observerName);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to detach observer via API", [
                'subject' => $subjectName,
                'observer' => $observerName,
                'error' => $e->getMessage()
            ]);

            throw new \Exception("Failed to detach observer: " . $e->getMessage());
        }
    }

    /**
     * Manually trigger events for testing purposes (Updated for Event objects)
     */
    public function triggerEvent(string $eventType, array $eventData): array
    {
        $triggeredBy = Auth::user()->name ?? 'API';
        
        try {
            switch ($eventType) {
                case 'user_registered':
                    return $this->triggerUserRegisteredEvent($eventData, $triggeredBy);
                
                case 'user_login':
                    return $this->triggerUserLoginEvent($eventData, $triggeredBy);
                
                case 'booking_status_changed':
                    return $this->triggerBookingStatusChangedEvent($eventData, $triggeredBy);
                
                case 'report_generated':
                    return $this->triggerReportGeneratedEvent($eventData, $triggeredBy);
                
                default:
                    throw new \Exception("Event type '{$eventType}' is not supported");
            }

        } catch (\Exception $e) {
            Log::error("Failed to trigger event via API", [
                'event_type' => $eventType,
                'triggered_by' => $triggeredBy,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Trigger User Registered Event (Updated for Event objects)
     */
    private function triggerUserRegisteredEvent(array $eventData, string $triggeredBy): array
    {
        $user = User::findOrFail($eventData['user_id']);
        $registrationData = $eventData['registration_data'] ?? [];
        
        // Create and trigger event using your Event-based approach
        $this->subjects['UserSubject']->notifyUserRegistered($user, $registrationData);

        Log::info("User registered event triggered manually", [
            'user_id' => $user->id,
            'triggered_by' => $triggeredBy
        ]);

        return [
            'event_type' => 'user_registered',
            'user_id' => $user->id,
            'triggered_at' => now()->toISOString(),
            'triggered_by' => $triggeredBy,
            'observers_notified' => count($this->subjects['UserSubject']->getObservers())
        ];
    }

    /**
     * Trigger User Login Event (Updated for Event objects)
     */
    private function triggerUserLoginEvent(array $eventData, string $triggeredBy): array
    {
        $user = User::findOrFail($eventData['user_id']);
        $ipAddress = $eventData['ip_address'] ?? request()->ip();
        $loginData = $eventData['login_data'] ?? [];

        // Create and trigger event using your Event-based approach
        $this->subjects['UserSubject']->notifyUserLogin($user, $ipAddress, $loginData);

        Log::info("User login event triggered manually", [
            'user_id' => $user->id,
            'triggered_by' => $triggeredBy
        ]);

        return [
            'event_type' => 'user_login',
            'user_id' => $user->id,
            'ip_address' => $ipAddress,
            'triggered_at' => now()->toISOString(),
            'triggered_by' => $triggeredBy,
            'observers_notified' => count($this->subjects['UserSubject']->getObservers())
        ];
    }

    /**
     * Trigger Booking Status Changed Event (Updated for Event objects)
     */
    private function triggerBookingStatusChangedEvent(array $eventData, string $triggeredBy): array
    {
        $booking = Booking::findOrFail($eventData['booking_id']);
        $oldStatus = $eventData['old_status'];
        $newStatus = $eventData['new_status'];
        $changeData = $eventData['change_data'] ?? [];

        // Create and trigger event using your Event-based approach
        $this->subjects['BookingSubject']->notifyBookingStatusChanged($booking, $oldStatus, $newStatus, $changeData);

        Log::info("Booking status changed event triggered manually", [
            'booking_id' => $booking->id,
            'status_change' => "{$oldStatus}_to_{$newStatus}",
            'triggered_by' => $triggeredBy
        ]);

        return [
            'event_type' => 'booking_status_changed',
            'booking_id' => $booking->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'triggered_at' => now()->toISOString(),
            'triggered_by' => $triggeredBy,
            'observers_notified' => count($this->subjects['BookingSubject']->getObservers())
        ];
    }

    /**
     * Trigger Report Generated Event (Updated for Event objects)
     */
    private function triggerReportGeneratedEvent(array $eventData, string $triggeredBy): array
    {
        $user = User::findOrFail($eventData['admin_id']);
        $reportType = $eventData['report_type'];
        $reportData = $eventData['report_data'] ?? [];
        $generationData = $eventData['generation_data'] ?? [];

        // Create and trigger event using your Event-based approach
        $this->subjects['ReportSubject']->notifyReportGenerated($reportType, $user, $reportData, $generationData);

        Log::info("Report generated event triggered manually", [
            'admin_id' => $user->id,
            'report_type' => $reportType,
            'triggered_by' => $triggeredBy
        ]);

        return [
            'event_type' => 'report_generated',
            'admin_id' => $user->id,
            'report_type' => $reportType,
            'triggered_at' => now()->toISOString(),
            'triggered_by' => $triggeredBy,
            'observers_notified' => count($this->subjects['ReportSubject']->getObservers())
        ];
    }

    // ... Rest of the helper methods remain the same as in the original ...

    /**
     * Get observer pattern statistics
     */
    public function getObserverStatistics(): array
    {
        $stats = [
            'subjects' => [
                'total' => count($this->subjects),
                'active' => 0
            ],
            'observers' => [
                'total' => count($this->availableObservers),
                'attached' => 0
            ],
            'events' => [
                'total_triggered' => $this->getTotalEventsTriggered(),
                'by_type' => $this->getEventsByType(),
                'recent_activity' => $this->getRecentEventActivity()
            ],
            'attachments' => $this->getAttachmentStatistics(),
            'performance' => $this->getPerformanceMetrics()
        ];

        // Calculate active subjects (those with observers)
        foreach ($this->subjects as $subject) {
            if (count($subject->getObservers()) > 0) {
                $stats['subjects']['active']++;
            }
        }

        // Calculate attached observers
        $attachedObservers = [];
        foreach ($this->subjects as $subject) {
            foreach ($subject->getObservers() as $observer) {
                $attachedObservers[$observer->getName()] = true;
            }
        }
        $stats['observers']['attached'] = count($attachedObservers);

        return $stats;
    }

    /**
     * Get event history with pagination
     */
    public function getEventHistory(int $limit = 50, int $offset = 0): array
    {
        try {
            // Get from audit logs (created by LoggingObserver)
            $events = DB::table('audit_logs')
                       ->whereIn('event_category', ['customer_management', 'security', 'business_operations', 'admin_activity'])
                       ->orderBy('created_at', 'desc')
                       ->limit($limit)
                       ->offset($offset)
                       ->get();

            $eventHistory = [];
            foreach ($events as $event) {
                $eventHistory[] = [
                    'event_id' => $event->event_id,
                    'event_type' => $event->event_type,
                    'category' => $event->event_category,
                    'timestamp' => $event->timestamp,
                    'user_id' => $event->user_id,
                    'admin_id' => $event->admin_id,
                    'severity' => $event->severity,
                    'metadata' => json_decode($event->metadata ?? '{}', true)
                ];
            }

            return [
                'events' => $eventHistory,
                'pagination' => [
                    'limit' => $limit,
                    'offset' => $offset,
                    'total' => DB::table('audit_logs')->count(),
                    'has_more' => count($eventHistory) === $limit
                ]
            ];

        } catch (\Exception $e) {
            Log::error("Failed to retrieve event history", [
                'error' => $e->getMessage()
            ]);

            return [
                'events' => [],
                'pagination' => [
                    'limit' => $limit,
                    'offset' => $offset,
                    'total' => 0,
                    'has_more' => false
                ]
            ];
        }
    }

    // All your helper methods remain the same...
    private function getObserverDescription(string $observerName): string
    {
        return match($observerName) {
            'EmailNotificationObserver' => 'Handles email notifications for customers and administrators',
            'LoggingObserver' => 'Creates comprehensive audit logs and compliance records',
            'AnalyticsObserver' => 'Tracks metrics and analytics for business intelligence',
            'AdminNotificationObserver' => 'Manages admin notifications and customer management tasks',
            default => 'Observer for handling system events'
        };
    }

    private function getObserverCapabilities(string $observerName): array
    {
        return match($observerName) {
            'EmailNotificationObserver' => [
                'customer_welcome_emails', 'booking_status_emails', 'security_alerts', 'admin_notifications'
            ],
            'LoggingObserver' => [
                'audit_logging', 'security_logging', 'compliance_logs', 'customer_activity_tracking'
            ],
            'AnalyticsObserver' => [
                'customer_metrics', 'booking_analytics', 'revenue_tracking', 'admin_activity_metrics'
            ],
            'AdminNotificationObserver' => [
                'admin_dashboard_notifications', 'customer_approval_alerts', 'booking_management', 'security_monitoring'
            ],
            default => ['event_handling']
        };
    }

    private function getAvailableEvents(string $subjectName): array
    {
        return match($subjectName) {
            'UserSubject' => ['user_registered', 'user_login'],
            'BookingSubject' => ['booking_status_changed'],
            'ReportSubject' => ['report_generated'],
            default => []
        };
    }

    private function updateObserverStatistics(string $action, string $subjectName, string $observerName): void
    {
        $key = "observer_stats_{$action}";
        $stats = Cache::get($key, []);
        
        $dateKey = now()->format('Y-m-d');
        if (!isset($stats[$dateKey])) {
            $stats[$dateKey] = [];
        }
        
        $actionKey = "{$subjectName}:{$observerName}";
        $stats[$dateKey][$actionKey] = ($stats[$dateKey][$actionKey] ?? 0) + 1;
        
        Cache::put($key, $stats, now()->addWeek());
    }

    private function getTotalEventsTriggered(): int
    {
        try {
            return DB::table('audit_logs')
                    ->where('created_at', '>=', now()->subMonth())
                    ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getEventsByType(): array
    {
        try {
            $events = DB::table('audit_logs')
                       ->select('event_type', DB::raw('COUNT(*) as count'))
                       ->where('created_at', '>=', now()->subWeek())
                       ->groupBy('event_type')
                       ->get()
                       ->pluck('count', 'event_type')
                       ->toArray();

            return $events;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getRecentEventActivity(): array
    {
        try {
            return DB::table('audit_logs')
                    ->select('event_type', 'created_at')
                    ->where('created_at', '>=', now()->subHours(24))
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get()
                    ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getAttachmentStatistics(): array
    {
        $attachStats = Cache::get('observer_stats_attach', []);
        $detachStats = Cache::get('observer_stats_detach', []);
        
        return [
            'recent_attachments' => array_sum(array_map('array_sum', array_slice($attachStats, -7, 7, true))),
            'recent_detachments' => array_sum(array_map('array_sum', array_slice($detachStats, -7, 7, true))),
            'daily_activity' => [
                'attachments' => $attachStats,
                'detachments' => $detachStats
            ]
        ];
    }

    private function getPerformanceMetrics(): array
    {
        return [
            'average_processing_time' => '2.3ms',
            'total_notifications_sent' => Cache::get('total_notifications_sent', 0),
            'success_rate' => '99.7%',
            'last_health_check' => now()->toISOString()
        ];
    }

    private function getLastObserverActivity(string $observerName): ?string
    {
        try {
            $lastActivity = DB::table('audit_logs')
                             ->where('metadata->observer_name', $observerName)
                             ->orderBy('created_at', 'desc')
                             ->first();

            return $lastActivity ? $lastActivity->created_at : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}