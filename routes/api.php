<?php


// routes/api.php - Vehicle Rental System API Routes
// BMIT3173 Assignment - Vehicle Module API - Tan Xing Ye (Factory Pattern)
// BMIT3173 Assignment - Booking Module API - Chong Zheng Yao (State Pattern)
// BMIT3173 Assignment - Observer Module API - Jayvian Lazarus Jerome (Observer Pattern)

use App\Http\Controllers\Api\VehicleApiController;// Vehicle API with Factory Pattern - Tan Xing Ye
use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\Api\ReportsApiController;
use App\Http\Controllers\Api\BookingApiController; // Booking API with State Pattern - Chong Zheng Yao
use App\Http\Controllers\Api\ObserverApiController; // Observer API with Observer Pattern - Jayvian Lazarus Jerome
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/simple-test', function () {
    return response()->json(['message' => 'Basic API routing works!']);
});

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ============================================================================
// *VEHICLE MODULE API ROUTES - Tan Xing Ye*
// ============================================================================

Route::prefix('v1')->group(function () {

    // ========================================================================
    // *PUBLIC VEHICLE API ENDPOINTS*
    // ========================================================================
    // These endpoints are accessible without authentication

    // Get vehicles with filtering and pagination
    Route::get('/vehicles', [VehicleApiController::class, 'index'])
        ->name('api.vehicles.index');

    // Get available vehicles only
    Route::get('/vehicles/available', [VehicleApiController::class, 'available'])
        ->name('api.vehicles.available');

    // Get vehicle by ID
    Route::get('/vehicles/{id}', [VehicleApiController::class, 'show'])
        ->name('api.vehicles.show');

    // Get vehicle type defaults using Factory Method Pattern
    Route::get('/vehicles/types/{type}/defaults', [VehicleApiController::class, 'getTypeDefaults'])
        ->name('api.vehicles.type-defaults');

    // Get all supported vehicle types
    Route::get('/vehicles/types', [VehicleApiController::class, 'getVehicleTypes'])
        ->name('api.vehicles.types');

    // Check vehicle availability for specific period
    Route::post('/vehicles/{id}/check-availability', [VehicleApiController::class, 'checkAvailability'])
        ->name('api.vehicles.check-availability');

    // Get vehicle statistics (public stats)
    Route::get('/vehicles/statistics', [VehicleApiController::class, 'statistics'])
        ->name('api.vehicles.statistics');

    // ========================================================================
    // *PUBLIC BOOKING API ENDPOINTS (No Authentication Required)*
    // ========================================================================
    // Public booking availability checking (for guests browsing)
    Route::post('/bookings/check-availability', [BookingApiController::class, 'checkAvailability'])
        ->middleware('throttle:60,1') // Rate limit for public use
        ->name('api.bookings.check-availability');

    // ========================================================================
    // *PUBLIC OBSERVER PATTERN API ENDPOINTS - Jayvian Lazarus Jerome*
    // ========================================================================
    // Public observer statistics and health check (no sensitive data)
    Route::get('/observers/statistics', [ObserverApiController::class, 'statistics'])
        ->middleware('throttle:30,1') // Rate limit for public use
        ->name('api.observers.public-statistics');

    Route::get('/observers/health', [ObserverApiController::class, 'healthCheck'])
        ->middleware('throttle:20,1')
        ->name('api.observers.health');

    // ========================================================================
    // *ADMIN-ONLY API ENDPOINTS*
    // ========================================================================
    // These endpoints require admin authentication

    Route::middleware(\App\Http\Middleware\AdminMiddleware::class)->group(function () {

        // Vehicle CRUD operations with rate limiting
        Route::post('/vehicles', [VehicleApiController::class, 'store'])
            ->middleware('throttle:vehicle-creation')
            ->name('api.vehicles.store');

        Route::put('/vehicles/{id}', [VehicleApiController::class, 'update'])
            ->middleware('throttle:vehicle-updates')
            ->name('api.vehicles.update');

        Route::delete('/vehicles/{id}', [VehicleApiController::class, 'destroy'])
            ->middleware('throttle:vehicle-deletion')
            ->name('api.vehicles.destroy');

        // Toggle vehicle status
        Route::patch('/vehicles/{id}/toggle-status', [VehicleApiController::class, 'toggleStatus'])
            ->name('api.vehicles.toggle-status');

        // ====================================================================
        // *ADMIN BOOKING API ENDPOINTS (State Pattern Management)*
        // ====================================================================
        // Admin can manage all bookings regardless of user ownership

        Route::prefix('admin/bookings')->name('api.admin.bookings.')->group(function () {
            // Get all bookings (admin view)
            Route::get('/', function (Request $request) {
                // This would be handled by an AdminBookingApiController
                // For now, return placeholder
                return response()->json([
                    'message' => 'Admin booking management coming soon',
                    'available_endpoints' => [
                        'GET /api/v1/admin/bookings' => 'Get all bookings',
                        'PATCH /api/v1/admin/bookings/{id}/status' => 'Update booking status'
                    ]
                ]);
            })->name('index');
        });

        // ====================================================================
        // *ADMIN OBSERVER PATTERN API ENDPOINTS - Jayvian Lazarus Jerome*
        // ====================================================================
        // Full observer pattern management for administrators only

        Route::prefix('observers')->name('api.observers.')->group(function () {

            // ================================================================
            // *CORE OBSERVER MANAGEMENT - Jayvian Lazarus Jerome*
            // ================================================================

            // Get all available observers
            Route::get('/', [ObserverApiController::class, 'index'])
                ->middleware('throttle:60,1')
                ->name('index');

            // Get specific observer details
            Route::get('/{observerName}', [ObserverApiController::class, 'show'])
                ->middleware('throttle:120,1')
                ->name('show');

            // Attach observer to subject
            Route::post('/{observerName}/attach', [ObserverApiController::class, 'attach'])
                ->middleware('throttle:observer-management')
                ->name('attach');

            // Detach observer from subject
            Route::delete('/{observerName}/detach', [ObserverApiController::class, 'detach'])
                ->middleware('throttle:observer-management')
                ->name('detach');

            // Bulk attach multiple observers
            Route::post('/bulk/attach', [ObserverApiController::class, 'bulkAttach'])
                ->middleware('throttle:observer-bulk-operations')
                ->name('bulk-attach');

            // ================================================================
            // *SUBJECT MANAGEMENT - Observer Pattern*
            // ================================================================

            // Get all subjects with their observers
            Route::get('/subjects', [ObserverApiController::class, 'getSubjects'])
                ->middleware('throttle:60,1')
                ->name('subjects.index');

            // Get specific subject information
            Route::get('/subjects/{subjectName}', [ObserverApiController::class, 'getSubject'])
                ->middleware('throttle:120,1')
                ->name('subjects.show');

            // ================================================================
            // *EVENT MANAGEMENT & TESTING - Observer Pattern*
            // ================================================================

            // Manually trigger events for testing
            Route::post('/events/trigger', [ObserverApiController::class, 'triggerEvent'])
                ->middleware('throttle:observer-event-trigger')
                ->name('events.trigger');

            // Get event history with pagination
            Route::get('/events', [ObserverApiController::class, 'getEvents'])
                ->middleware('throttle:observer-events-access')
                ->name('events.index');
        });
    });
});

// ============================================================================
// *AUTHENTICATED USER API ROUTES*
// ============================================================================
// These routes require user authentication via Sanctum

Route::middleware('auth:sanctum')->group(function () {

    // ========================================================================
    // *BOOKING MODULE API ROUTES - State Pattern Implementation* - Chong Zheng Yao
    // ========================================================================
    Route::prefix('v1/bookings')->name('api.bookings.')->group(function () {

        // ====================================================================
        // *CORE BOOKING CRUD OPERATIONS - Chong Zheng Yao
        // ====================================================================

        // Get user's bookings with state information
        Route::get('/', [BookingApiController::class, 'index'])
            ->middleware('throttle:60,1')
            ->name('index');

       // Create new booking (with State Pattern validation)
        Route::post('/', [BookingApiController::class, 'store'])
            ->middleware('throttle:booking-creation')
            ->name('store');

        // Get specific booking with state information
        Route::get('/{id}', [BookingApiController::class, 'show'])
            ->middleware('throttle:120,1')
            ->name('show');

        // Update booking status using State Pattern
        Route::patch('/{id}/status', [BookingApiController::class, 'updateStatus'])
            ->middleware('throttle:booking-status-update')
            ->name('update-status');

        // Cancel booking (DELETE method)
        Route::delete('/{id}', [BookingApiController::class, 'destroy'])
            ->middleware('throttle:booking-cancellation')
            ->name('cancel');

        // Get available actions for a booking (State Pattern feature)
        Route::get('/{id}/actions', [BookingApiController::class, 'getAvailableActions'])
            ->middleware('throttle:120,1')
            ->name('actions');

        // State transition endpoints
        Route::post('/{id}/confirm', [BookingApiController::class, 'confirm'])
            ->middleware('throttle:booking-state-change')
            ->name('confirm');

        Route::post('/{id}/activate', [BookingApiController::class, 'activate'])
            ->middleware('throttle:booking-state-change')
            ->name('activate');

        Route::post('/{id}/complete', [BookingApiController::class, 'complete'])
            ->middleware('throttle:booking-state-change')
            ->name('complete');

        // ====================================================================
        // *STATE PATTERN SPECIFIC ENDPOINTS*
        // ====================================================================

        // Get available actions for a booking (State Pattern feature)
        Route::get('/{id}/actions', [BookingApiController::class, 'getAvailableActions'])
            ->middleware('throttle:120,1')
            ->name('actions');

        // State transition endpoints
        Route::post('/{id}/confirm', [BookingApiController::class, 'confirm'])
            ->middleware('throttle:booking-state-change')
            ->name('confirm');

        Route::post('/{id}/activate', [BookingApiController::class, 'activate'])
            ->middleware('throttle:booking-state-change')
            ->name('activate');

        Route::post('/{id}/complete', [BookingApiController::class, 'complete'])
            ->middleware('throttle:booking-state-change')
            ->name('complete');

        // ====================================================================
        // *BOOKING STATISTICS & REPORTING*
        // ====================================================================

        // Get user's booking statistics (State Pattern powered)
        Route::get('/statistics', [BookingApiController::class, 'statistics'])
            ->middleware('throttle:30,1')
            ->name('statistics');
    });

    // ========================================================================
    // *OBSERVER MODULE API ROUTES - Observer Pattern Implementation* - Jayvian Lazarus Jerome
    // ========================================================================
    Route::prefix('v1/observers')->name('api.user.observers.')->group(function () {

        // ====================================================================
        // *USER-LEVEL OBSERVER PATTERN ACCESS - Jayvian Lazarus Jerome*
        // ====================================================================
        // Limited observer pattern access for authenticated users (read-only)

        // Get observer system status (user-friendly version)
        Route::get('/status', function () {
            try {
                $observerService = app(\App\Services\ObserverService::class);
                $subjects = $observerService->getAllSubjects();

                return response()->json([
                    'success' => true,
                    'message' => 'Observer system status retrieved successfully',
                    'data' => [
                        'system_active' => true,
                        'total_subjects' => count($subjects),
                        'active_subjects' => count(array_filter($subjects, fn($s) => $s['observer_count'] > 0)),
                        'last_check' => now()->toISOString()
                    ],
                    'meta' => [
                        'timestamp' => now()->toISOString(),
                        'version' => 'v1',
                        'pattern' => 'Observer Pattern API'
                    ]
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to retrieve observer system status',
                    'meta' => [
                        'timestamp' => now()->toISOString(),
                        'version' => 'v1'
                    ]
                ], 500);
            }
        })
        ->middleware('throttle:30,1')
        ->name('status');

        // Get public observer information (no sensitive data)
        Route::get('/info', function () {
            return response()->json([
                'success' => true,
                'message' => 'Observer pattern information retrieved successfully',
                'data' => [
                    'pattern_name' => 'Observer Pattern',
                    'implementation_by' => 'Jayvian Lazarus Jerome',
                    'description' => 'Handles system-wide event notifications and logging',
                    'available_subjects' => ['UserSubject', 'BookingSubject', 'ReportSubject'],
                    'available_observers' => ['EmailNotificationObserver', 'LoggingObserver', 'AnalyticsObserver', 'AdminNotificationObserver'],
                    'features' => [
                        'Real-time event notifications',
                        'Comprehensive audit logging',
                        'Analytics and metrics tracking',
                        'Admin notification management'
                    ]
                ],
                'meta' => [
                    'timestamp' => now()->toISOString(),
                    'version' => 'v1',
                    'pattern' => 'Observer Pattern API'
                ]
            ]);
        })
        ->middleware('throttle:20,1')
        ->name('info');
    });

    // ========================================================================
    // *PAYMENTS API ROUTES*        *CHIEW CHUN SHENG*
    // ========================================================================
    Route::prefix('payments')->group(function () {
        Route::get('methods', [PaymentApiController::class, 'getPaymentMethods']);
        Route::post('bookings/{bookingId}/initialize', [PaymentApiController::class, 'initializePayment']);
        Route::post('bookings/{bookingId}/process', [PaymentApiController::class, 'processPayment']);
        Route::get('bookings/{bookingId}/status', [PaymentApiController::class, 'getPaymentStatus']);
        Route::get('history', [PaymentApiController::class, 'getPaymentHistory']);
        Route::post('validate', [PaymentApiController::class, 'validatePaymentData']);
    });

    // ========================================================================
    // *REPORTS API ROUTES*        *CHIEW CHUN SHENG*
    // ========================================================================
    Route::prefix('reports')->group(function () {
        Route::get('formats', [ReportsApiController::class, 'getAvailableFormats']);
        Route::post('booking', [ReportsApiController::class, 'generateBookingReport']);
        Route::get('summary', [ReportsApiController::class, 'getBookingSummary']);
        Route::get('history', [ReportsApiController::class, 'getReportHistory']);
        Route::post('validate', [ReportsApiController::class, 'validateReportRequest']);
        Route::get('{reportId}/status', [ReportsApiController::class, 'getReportStatus']);
    });
});

// ============================================================================
// *RATE LIMITING CONFIGURATION*
// ============================================================================
// Define custom rate limits in AppServiceProvider
// Observer Module (Observer Pattern) - Jayvian Lazarus Jerome:
// observer-management: 20 requests per minute per admin
// observer-bulk-operations: 5 requests per minute per admin
// observer-event-trigger: 10 requests per minute per admin
// observer-events-access: 100 requests per minute per admin

// ============================================================================
// *FALLBACK ROUTE FOR API*
// ============================================================================
Route::fallback(function () {
    return response()->json([
        'status' => 'error',
        'message' => 'API endpoint not found',
        'available_endpoints' => [
            // Vehicle endpoints (existing)
            'GET /api/v1/vehicles' => 'Get all vehicles',
            'GET /api/v1/vehicles/available' => 'Get available vehicles',
            'GET /api/v1/vehicles/{id}' => 'Get specific vehicle',
            'GET /api/v1/vehicles/types/{type}/defaults' => 'Get vehicle type defaults',
            'POST /api/v1/vehicles/{id}/check-availability' => 'Check vehicle availability',

            // Booking endpoints (NEW - State Pattern)
            'GET /api/v1/bookings' => 'Get user bookings (Auth Required)',
            'POST /api/v1/bookings' => 'Create new booking (Auth Required)',
            'GET /api/v1/bookings/{id}' => 'Get specific booking (Auth Required)',
            'PATCH /api/v1/bookings/{id}/status' => 'Update booking status (Auth Required)',
            'DELETE /api/v1/bookings/{id}' => 'Cancel booking (Auth Required)',
            'GET /api/v1/bookings/{id}/actions' => 'Get available actions (Auth Required)',
            'POST /api/v1/bookings/{id}/confirm' => 'Confirm booking (Auth Required)',
            'POST /api/v1/bookings/{id}/activate' => 'Activate booking (Auth Required)',
            'POST /api/v1/bookings/{id}/complete' => 'Complete booking (Auth Required)',
            'POST /api/v1/bookings/check-availability' => 'Check booking availability (Public)',
            'GET /api/v1/bookings/statistics' => 'Get booking statistics (Auth Required)',

            // Observer endpoints (NEW - Observer Pattern - Jayvian Lazarus Jerome)
            'GET /api/v1/observers' => 'Get all observers (Admin Required)',
            'GET /api/v1/observers/{observerName}' => 'Get specific observer (Admin Required)',
            'POST /api/v1/observers/{observerName}/attach' => 'Attach observer (Admin Required)',
            'DELETE /api/v1/observers/{observerName}/detach' => 'Detach observer (Admin Required)',
            'POST /api/v1/observers/bulk/attach' => 'Bulk attach observers (Admin Required)',
            'GET /api/v1/observers/subjects' => 'Get all subjects (Admin Required)',
            'GET /api/v1/observers/subjects/{subjectName}' => 'Get specific subject (Admin Required)',
            'POST /api/v1/observers/events/trigger' => 'Trigger event manually (Admin Required)',
            'GET /api/v1/observers/events' => 'Get event history (Admin Required)',
            'GET /api/v1/observers/statistics' => 'Get observer statistics (Public)',
            'GET /api/v1/observers/health' => 'System health check (Public)',
            'GET /api/v1/observers/status' => 'Get system status (Auth Required)',
            'GET /api/v1/observers/info' => 'Get pattern information (Auth Required)'
        ],
        'authentication' => [
            'method' => 'Bearer Token (Sanctum)',
            'header' => 'Authorization: Bearer {token}'
        ],
        'meta' => [
            'version' => 'v1',
            'timestamp' => now()->toISOString()
        ]
    ], 404);
});
