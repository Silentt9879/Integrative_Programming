<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ObserverService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ObserverApiController extends Controller
{
    private ObserverService $observerService;

    public function __construct(ObserverService $observerService)
    {
        $this->observerService = $observerService;
        $this->middleware('auth:sanctum')->except(['statistics', 'healthCheck']);
        $this->middleware(\App\Http\Middleware\AdminMiddleware::class)->except(['statistics', 'healthCheck']);
    }

    /**
     * Get all subjects with their observers
     * GET /api/v1/observers/subjects
     */
    public function getSubjects(): JsonResponse
    {
        try {
            $subjects = $this->observerService->getAllSubjects();

            return $this->successResponse($subjects, 'Observer subjects retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Observer API subjects error', [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse('Failed to retrieve observer subjects', 500);
        }
    }

    /**
     * Get specific subject information
     * GET /api/v1/observers/subjects/{subjectName}
     */
    public function getSubject(string $subjectName): JsonResponse
    {
        try {
            $subject = $this->observerService->getSubject($subjectName);

            return $this->successResponse($subject, "Subject '{$subjectName}' retrieved successfully");
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return $this->errorResponse($e->getMessage(), 404);
            }

            Log::error('Observer API subject error', [
                'admin_id' => Auth::id(),
                'subject_name' => $subjectName,
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse('Failed to retrieve subject information', 500);
        }
    }

    /**
     * Get all available observers
     * GET /api/v1/observers
     */
    public function index(): JsonResponse
    {
        try {
            $observers = $this->observerService->getAvailableObservers();

            return $this->successResponse($observers, 'Available observers retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Observer API index error', [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse('Failed to retrieve observers', 500);
        }
    }

    /**
     * Get specific observer details
     * GET /api/v1/observers/{observerName}
     */
    public function show(string $observerName): JsonResponse
    {
        try {
            $observer = $this->observerService->getObserver($observerName);

            return $this->successResponse($observer, "Observer '{$observerName}' retrieved successfully");
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return $this->errorResponse($e->getMessage(), 404);
            }

            Log::error('Observer API show error', [
                'admin_id' => Auth::id(),
                'observer_name' => $observerName,
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse('Failed to retrieve observer information', 500);
        }
    }

    /**
     * Attach observer to subject with Enhanced Security
     * POST /api/v1/observers/{observerName}/attach
     */
    public function attach(Request $request, string $observerName): JsonResponse
    {
        // Rate limiting for observer management abuse
        if (RateLimiter::tooManyAttempts('observer-management:' . Auth::id(), 20)) {
            Log::warning('Observer management abuse detected - attach', [
                'admin_id' => Auth::id(),
                'observer_name' => $observerName,
                'ip_address' => $request->ip(),
                'action' => 'attach'
            ]);

            return $this->errorResponse('Too many observer management requests. Please try again later.', 429);
        }

        try {
            $validator = Validator::make($request->all(), [
                'subject_name' => [
                    'required',
                    'string',
                    'regex:/^[a-zA-Z]+Subject$/',
                    'in:UserSubject,BookingSubject,ReportSubject'
                ]
            ], [
                'subject_name.regex' => 'Invalid subject name format.',
                'subject_name.in' => 'Subject must be one of: UserSubject, BookingSubject, ReportSubject'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', 422, $validator->errors());
            }

            // Sanitize input
            $subjectName = strip_tags(trim($request->subject_name));
            $observerName = strip_tags(trim($observerName));

            $result = $this->observerService->attachObserver($subjectName, $observerName);

            if ($result) {
                // Hit rate limiter after successful operation
                RateLimiter::hit('observer-management:' . Auth::id(), 300);

                // Security logging
                Log::info('Observer attached via API - Security Event', [
                    'admin_id' => Auth::id(),
                    'admin_name' => Auth::user()->name,
                    'observer_name' => $observerName,
                    'subject_name' => $subjectName,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'action' => 'observer_attached'
                ]);

                return $this->successResponse([
                    'observer' => $observerName,
                    'subject' => $subjectName,
                    'attached_at' => now()->toISOString(),
                    'attached_by' => Auth::user()->name
                ], "Observer '{$observerName}' attached to subject '{$subjectName}' successfully");
            }

            return $this->errorResponse('Failed to attach observer', 500);

        } catch (\Exception $e) {
            // Enhanced security logging for failures
            Log::error('Observer API attach error - Security Event', [
                'admin_id' => Auth::id(),
                'observer_name' => $observerName,
                'subject_name' => $request->subject_name ?? 'unknown',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'error' => $e->getMessage(),
                'action' => 'observer_attach_failed'
            ]);

            if (str_contains($e->getMessage(), 'not found')) {
                return $this->errorResponse($e->getMessage(), 404);
            }

            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Detach observer from subject with Enhanced Security
     * DELETE /api/v1/observers/{observerName}/detach
     */
    public function detach(Request $request, string $observerName): JsonResponse
    {
        // Rate limiting for observer management abuse
        if (RateLimiter::tooManyAttempts('observer-management:' . Auth::id(), 20)) {
            Log::warning('Observer management abuse detected - detach', [
                'admin_id' => Auth::id(),
                'observer_name' => $observerName,
                'ip_address' => $request->ip(),
                'action' => 'detach'
            ]);

            return $this->errorResponse('Too many observer management requests. Please try again later.', 429);
        }

        try {
            $validator = Validator::make($request->all(), [
                'subject_name' => [
                    'required',
                    'string',
                    'regex:/^[a-zA-Z]+Subject$/',
                    'in:UserSubject,BookingSubject,ReportSubject'
                ]
            ], [
                'subject_name.regex' => 'Invalid subject name format.',
                'subject_name.in' => 'Subject must be one of: UserSubject, BookingSubject, ReportSubject'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', 422, $validator->errors());
            }

            // Sanitize input
            $subjectName = strip_tags(trim($request->subject_name));
            $observerName = strip_tags(trim($observerName));

            $result = $this->observerService->detachObserver($subjectName, $observerName);

            if ($result) {
                // Hit rate limiter after successful operation
                RateLimiter::hit('observer-management:' . Auth::id(), 300);

                // Security logging
                Log::info('Observer detached via API - Security Event', [
                    'admin_id' => Auth::id(),
                    'admin_name' => Auth::user()->name,
                    'observer_name' => $observerName,
                    'subject_name' => $subjectName,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'action' => 'observer_detached'
                ]);

                return $this->successResponse([
                    'observer' => $observerName,
                    'subject' => $subjectName,
                    'detached_at' => now()->toISOString(),
                    'detached_by' => Auth::user()->name
                ], "Observer '{$observerName}' detached from subject '{$subjectName}' successfully");
            }

            return $this->errorResponse('Failed to detach observer', 500);

        } catch (\Exception $e) {
            // Enhanced security logging for failures
            Log::error('Observer API detach error - Security Event', [
                'admin_id' => Auth::id(),
                'observer_name' => $observerName,
                'subject_name' => $request->subject_name ?? 'unknown',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'error' => $e->getMessage(),
                'action' => 'observer_detach_failed'
            ]);

            if (str_contains($e->getMessage(), 'not found')) {
                return $this->errorResponse($e->getMessage(), 404);
            }

            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Manually trigger events for testing with Enhanced Security
     * POST /api/v1/observers/events/trigger
     */
    public function triggerEvent(Request $request): JsonResponse
    {
        // Rate limiting for event triggering abuse
        if (RateLimiter::tooManyAttempts('observer-event-trigger:' . Auth::id(), 10)) {
            Log::warning('Observer event trigger abuse detected', [
                'admin_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return $this->errorResponse('Too many event trigger requests. Please try again later.', 429);
        }

        try {
            $validator = Validator::make($request->all(), [
                'event_type' => [
                    'required',
                    'string',
                    'in:user_registered,user_login,booking_status_changed,report_generated'
                ],
                'event_data' => 'required|array',
                'event_data.user_id' => 'required_if:event_type,user_registered,user_login|exists:users,id',
                'event_data.booking_id' => 'required_if:event_type,booking_status_changed|exists:bookings,id',
                'event_data.admin_id' => 'required_if:event_type,report_generated|exists:users,id',
                'event_data.old_status' => 'required_if:event_type,booking_status_changed|string',
                'event_data.new_status' => 'required_if:event_type,booking_status_changed|string',
                'event_data.report_type' => 'required_if:event_type,report_generated|string'
            ], [
                'event_type.in' => 'Event type must be one of: user_registered, user_login, booking_status_changed, report_generated'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', 422, $validator->errors());
            }

            $validatedData = $validator->validated();
            
            // Sanitize event data
            $eventType = strip_tags(trim($validatedData['event_type']));
            $eventData = $this->sanitizeEventData($validatedData['event_data'], $eventType);

            $result = $this->observerService->triggerEvent($eventType, $eventData);

            // Hit rate limiter after successful operation
            RateLimiter::hit('observer-event-trigger:' . Auth::id(), 600);

            // Security logging for manual event triggering
            Log::info('Event triggered manually via API - Security Event', [
                'admin_id' => Auth::id(),
                'admin_name' => Auth::user()->name,
                'event_type' => $eventType,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'action' => 'manual_event_trigger',
                'target_entities' => $this->extractTargetEntities($eventData)
            ]);

            return $this->successResponse($result, "Event '{$eventType}' triggered successfully", 201);

        } catch (\Exception $e) {
            // Enhanced security logging for trigger failures
            Log::error('Observer API trigger event error - Security Event', [
                'admin_id' => Auth::id(),
                'event_type' => $request->event_type ?? 'unknown',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'error' => $e->getMessage(),
                'action' => 'manual_event_trigger_failed'
            ]);

            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Get event history with pagination
     * GET /api/v1/observers/events
     */
    public function getEvents(Request $request): JsonResponse
    {
        // Rate limiting for data access
        if (RateLimiter::tooManyAttempts('observer-events-access:' . Auth::id(), 100)) {
            Log::warning('Observer events access abuse detected', [
                'admin_id' => Auth::id(),
                'ip_address' => $request->ip()
            ]);

            return $this->errorResponse('Too many event access requests. Please try again later.', 429);
        }

        try {
            $validator = Validator::make($request->all(), [
                'limit' => 'integer|min:1|max:100',
                'offset' => 'integer|min:0',
                'event_type' => 'nullable|string',
                'from_date' => 'nullable|date',
                'to_date' => 'nullable|date|after_or_equal:from_date'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', 422, $validator->errors());
            }

            $limit = $request->integer('limit', 50);
            $offset = $request->integer('offset', 0);

            $events = $this->observerService->getEventHistory($limit, $offset);

            // Hit rate limiter after successful operation
            RateLimiter::hit('observer-events-access:' . Auth::id(), 60);

            return $this->successResponse($events, 'Event history retrieved successfully');

        } catch (\Exception $e) {
            Log::error('Observer API events error', [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse('Failed to retrieve event history', 500);
        }
    }

    /**
     * Get observer pattern statistics
     * GET /api/v1/observers/statistics
     */
    public function statistics(): JsonResponse
    {
        // Rate limiting for statistics access
        $identifier = Auth::id() ?? request()->ip();

if (RateLimiter::tooManyAttempts('observer-stats-access:' . $identifier, 60)) {
    Log::warning('Observer statistics access abuse detected', [
        'admin_id' => Auth::id(),
        'ip_address' => request()->ip()  // CORRECT: uses request() helper
    ]);

            return $this->errorResponse('Too many statistics requests. Please try again later.', 429);
        }

        try {
            $statistics = $this->observerService->getObserverStatistics();

            // Hit rate limiter after successful operation
            RateLimiter::hit('observer-stats-access:' . $identifier, 60);

            return $this->successResponse($statistics, 'Observer pattern statistics retrieved successfully');

        } catch (\Exception $e) {
            Log::error('Observer API statistics error', [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse('Failed to retrieve observer statistics', 500);
        }
    }

    /**
     * Health check for observer pattern system
     * GET /api/v1/observers/health
     */
    public function healthCheck(): JsonResponse
    {
        try {
            $subjects = $this->observerService->getAllSubjects();
            $observers = $this->observerService->getAvailableObservers();
            
            $health = [
                'status' => 'healthy',
                'timestamp' => now()->toISOString(),
                'subjects' => [
                    'total' => count($subjects),
                    'active' => count(array_filter($subjects, fn($s) => $s['observer_count'] > 0))
                ],
                'observers' => [
                    'total' => count($observers),
                    'attached' => count(array_filter($observers, fn($o) => $o['attachment_count'] > 0))
                ],
                'system_info' => [
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version(),
                    'memory_usage' => memory_get_usage(true),
                    'peak_memory' => memory_get_peak_usage(true)
                ]
            ];

            return $this->successResponse($health, 'Observer pattern system is healthy');

        } catch (\Exception $e) {
            Log::error('Observer API health check error', [
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse('Observer pattern system health check failed', 500);
        }
    }

    /**
     * Bulk attach multiple observers to a subject
     * POST /api/v1/observers/bulk/attach
     */
    public function bulkAttach(Request $request): JsonResponse
    {
        // Enhanced rate limiting for bulk operations
        if (RateLimiter::tooManyAttempts('observer-bulk-operations:' . Auth::id(), 5)) {
            Log::warning('Observer bulk operations abuse detected', [
                'admin_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'action' => 'bulk_attach'
            ]);

            return $this->errorResponse('Too many bulk operations. Please try again later.', 429);
        }

        try {
            $validator = Validator::make($request->all(), [
                'subject_name' => [
                    'required',
                    'string',
                    'in:UserSubject,BookingSubject,ReportSubject'
                ],
                'observers' => 'required|array|min:1|max:10',
                'observers.*' => [
                    'string',
                    'in:EmailNotificationObserver,LoggingObserver,AnalyticsObserver,AdminNotificationObserver'
                ]
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', 422, $validator->errors());
            }

            $subjectName = strip_tags(trim($request->subject_name));
            $observers = array_map(fn($o) => strip_tags(trim($o)), $request->observers);

            $results = [];
            $successful = 0;
            $failed = 0;

            foreach ($observers as $observerName) {
                try {
                    $result = $this->observerService->attachObserver($subjectName, $observerName);
                    $results[] = [
                        'observer' => $observerName,
                        'status' => 'success',
                        'message' => "Attached to {$subjectName}"
                    ];
                    $successful++;
                } catch (\Exception $e) {
                    $results[] = [
                        'observer' => $observerName,
                        'status' => 'failed',
                        'message' => $e->getMessage()
                    ];
                    $failed++;
                }
            }

            // Hit rate limiter after operation
            RateLimiter::hit('observer-bulk-operations:' . Auth::id(), 300);

            // Security logging for bulk operations
            Log::info('Bulk observer attachment via API - Security Event', [
                'admin_id' => Auth::id(),
                'admin_name' => Auth::user()->name,
                'subject_name' => $subjectName,
                'observers_count' => count($observers),
                'successful' => $successful,
                'failed' => $failed,
                'ip_address' => $request->ip(),
                'action' => 'bulk_observer_attach'
            ]);

            return $this->successResponse([
                'subject' => $subjectName,
                'total_requested' => count($observers),
                'successful' => $successful,
                'failed' => $failed,
                'results' => $results,
                'processed_at' => now()->toISOString()
            ], "Bulk attachment completed: {$successful} successful, {$failed} failed");

        } catch (\Exception $e) {
            Log::error('Observer API bulk attach error - Security Event', [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage(),
                'action' => 'bulk_observer_attach_failed'
            ]);

            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Sanitize event data based on event type
     */
    private function sanitizeEventData(array $eventData, string $eventType): array
    {
        $sanitized = [];

        foreach ($eventData as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
            } elseif (is_numeric($value)) {
                $sanitized[$key] = $value;
            } elseif (is_array($value)) {
                $sanitized[$key] = array_map(function($item) {
                    return is_string($item) ? htmlspecialchars(strip_tags(trim($item)), ENT_QUOTES, 'UTF-8') : $item;
                }, $value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Extract target entities for logging
     */
    private function extractTargetEntities(array $eventData): array
    {
        $entities = [];
        
        if (isset($eventData['user_id'])) {
            $entities['user_id'] = $eventData['user_id'];
        }
        
        if (isset($eventData['booking_id'])) {
            $entities['booking_id'] = $eventData['booking_id'];
        }
        
        if (isset($eventData['admin_id'])) {
            $entities['admin_id'] = $eventData['admin_id'];
        }

        return $entities;
    }

    /**
     * Success response helper
     */
    private function successResponse($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => [
                'timestamp' => now()->toISOString(),
                'version' => 'v1',
                'pattern' => 'Observer Pattern API'
            ]
        ], $code);
    }

    /**
     * Error response helper
     */
    private function errorResponse(string $message, int $code = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'meta' => [
                'timestamp' => now()->toISOString(),
                'version' => 'v1',
                'pattern' => 'Observer Pattern API'
            ]
        ], $code);
    }
}