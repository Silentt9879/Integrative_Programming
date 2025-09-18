<?php
// routes/api.php - Vehicle Rental System API Routes
// BMIT3173 Assignment - Vehicle Module API - Tan Xing Ye
// BMIT3173 Assignment - Booking Module API - [Your Name] (State Pattern Implementation)

use App\Http\Controllers\Api\VehicleApiController;
use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\Api\ReportsApiController;
use App\Http\Controllers\Api\BookingApiController; // NEW: Booking API with State Pattern
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
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
    // *PAYMENTS API ROUTES*
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
    // *REPORTS API ROUTES*
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

// booking-creation: 5 requests per minute per user
// booking-status-update: 10 requests per minute per user
// booking-state-change: 15 requests per minute per user
// booking-cancellation: 3 requests per minute per user

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
            'GET /api/v1/bookings/statistics' => 'Get booking statistics (Auth Required)'
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
