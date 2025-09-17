<?php
// routes/api.php - Vehicle Rental System API Routes
// BMIT3173 Assignment - Vehicle Module API - Tan Xing Ye

use App\Http\Controllers\Api\VehicleApiController;
use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\Api\ReportsApiController;
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
// **VEHICLE MODULE API ROUTES - Tan Xing Ye**
// ============================================================================

Route::prefix('v1')->group(function () {

    // ========================================================================
    // **PUBLIC VEHICLE API ENDPOINTS**
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
    // **ADMIN-ONLY API ENDPOINTS**
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
    });
});

// ============================================================================
// **PAYMENTS ROUTE FOR API**
// ============================================================================
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('payments')->group(function () {
        Route::get('methods', [PaymentApiController::class, 'getPaymentMethods']);
        Route::post('bookings/{bookingId}/initialize', [PaymentApiController::class, 'initializePayment']);
        Route::post('bookings/{bookingId}/process', [PaymentApiController::class, 'processPayment']);
        Route::get('bookings/{bookingId}/status', [PaymentApiController::class, 'getPaymentStatus']);
        Route::get('history', [PaymentApiController::class, 'getPaymentHistory']);
        Route::post('validate', [PaymentApiController::class, 'validatePaymentData']);
    });

// ============================================================================
// **REPORTS ROUTE FOR API**
// ============================================================================
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
// **FALLBACK ROUTE FOR API**
// ============================================================================
Route::fallback(function(){
    return response()->json([
        'status' => 'error',
        'message' => 'API endpoint not found',
        'available_endpoints' => [
            'GET /api/v1/vehicles' => 'Get all vehicles',
            'GET /api/v1/vehicles/available' => 'Get available vehicles',
            'GET /api/v1/vehicles/{id}' => 'Get specific vehicle',
            'GET /api/v1/vehicles/types/{type}/defaults' => 'Get vehicle type defaults',
            'POST /api/v1/vehicles/{id}/check-availability' => 'Check availability'
        ]
    ], 404);
});