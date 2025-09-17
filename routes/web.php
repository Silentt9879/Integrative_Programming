<?php

// routes/web.php - RentWheels Vehicle Rental System Routes
// BMIT3173 Assignment Team: Chiew Chun Sheng, Jayvian Lazarus Jerome, Chong Zheng Yao, Tan Xing Ye

use App\Http\Controllers\HomeController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserReportsController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use Illuminate\Support\Facades\Route;

// ============================================================================
// PUBLIC PAGES & STATIC CONTENT
// ============================================================================
// Home and Static Pages (General Team Contribution)
Route::get('/', [HomeController::class, 'index'])->name('app');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');

// AJAX validation routes
Route::post('/check-email', [AuthController::class, 'checkEmail'])->name('check.email');

// ============================================================================
// **BILLING, PAYMENTS & USER REPORTING MODULE - Chiew Chun Sheng**
// ============================================================================

// Authenticated User Payment Routes
Route::middleware(\App\Http\Middleware\UserOnly::class)->group(function () {
    // ========================================================================
    // **PAYMENT ROUTES - Authenticated Users Only**
    // ========================================================================
    // Payment Management
    Route::prefix('payment')->name('payment.')->group(function () {
        Route::get('/{booking}', [PaymentController::class, 'showPayment'])->name('form');
        Route::post('/{booking}/process', [PaymentController::class, 'processPayment'])->name('process');
        Route::post('/{booking}/complete', [PaymentController::class, 'completePayment'])->name('complete');
        Route::get('/{booking}/success', [PaymentController::class, 'paymentSuccess'])->name('success');
        Route::get('/{booking}/failed', [PaymentController::class, 'paymentFailed'])->name('failed');
        Route::get('/{booking}/additional-charges', [PaymentController::class, 'showAdditionalCharges'])->name('additional-charges');
        Route::post('/{booking}/additional-charges/process', [PaymentController::class, 'processAdditionalCharges'])->name('additional-charges.process');
        Route::post('/{booking}/additional-charges/complete', [PaymentController::class, 'completeAdditionalCharges'])->name('additional-charges.complete');
        Route::get('/{booking}/additional-success', [PaymentController::class, 'additionalChargesSuccess'])->name('additional-success');
    });

    // ========================================================================
    // **USER REPORTS MODULE**
    // ========================================================================
    // User Report Generation Routes
    Route::prefix('reports')->name('user.reports.')->group(function () {
        // Show report options/filters page
        Route::get('/options', [UserReportsController::class, 'showReportOptions'])->name('options');

        // Generate complete booking report (all bookings)
        Route::get('/booking-report', [UserReportsController::class, 'generateBookingReport'])->name('booking-report');

        // Generate detailed/filtered report
        Route::get('/detailed-report', [UserReportsController::class, 'generateDetailedReport'])->name('detailed-report');
    });
});

// Admin Payment and Billing Management
Route::prefix('admin')->name('admin.')->middleware(\App\Http\Middleware\AdminMiddleware::class)->group(function () {
    // ========================================================================
    // **Billing, Payments & Reporting Module - Admin**
    // ========================================================================
    // Payment Management
    Route::get('/payments', [AdminController::class, 'payments'])->name('payments');

    // ========================================================================
    // **REPORTING & ANALYTICS**
    // ========================================================================
    // Main Reports Page
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');

    // AJAX Filter Reports
    Route::post('/reports/filter', [AdminController::class, 'filterReports'])->name('reports.filter');

    // PDF Export Routes - Fixed to use dedicated ReportsController
    Route::get('/reports/export', [\App\Http\Controllers\ReportsController::class, 'exportPDF'])->name('reports.export');
});

// ============================================================================
// **CUSTOMER & USER MANAGEMENT MODULE & ADMIN REPORTING - Jayvian Lazarus Jerome**
// ============================================================================

// Observer Pattern Enhanced Controllers - Jayvian
use App\Http\Controllers\Observer\Enhanced\AuthController as ObserverAuthController;
use App\Http\Controllers\Observer\Enhanced\AdminController as ObserverAdminController;
use App\Http\Controllers\Observer\Enhanced\ReportsController as ObserverReportsController;

// Guest Authentication Routes (Login, Register, Password Reset)
Route::middleware('guest')->group(function () {
    // Regular User Authentication
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');

    // Password Reset Functionality
    Route::get('/forgot-password', function () {
        return view('auth.forgotpw');
    })->name('password.request');

    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('/reset-password/{token}', function ($token) {
        return view('auth.reset-password', ['token' => $token, 'request' => request()]);
    })->name('password.reset');

    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');

    // Admin Authentication (Separate Login System)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/login', [AdminController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminController::class, 'login'])->name('login.post');
    });
});

// Authenticated User Routes (Regular Users Only)
Route::middleware(\App\Http\Middleware\UserOnly::class)->group(function () {
    // User Dashboard
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    // User Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // User Profile Management
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
});

// Admin User & Customer Management
Route::prefix('admin')->name('admin.')->middleware(\App\Http\Middleware\AdminMiddleware::class)->group(function () {
    // ========================================================================
    // Admin Dashboard & Core Management
    // ========================================================================
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');

    // ========================================================================
    // **Customer and User Management Module**
    // ========================================================================
    // User & Customer Administration
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/customers', [AdminController::class, 'customers'])->name('customers');

    // Customer CRUD operations
    Route::post('/customers', [AdminController::class, 'storeCustomer'])->name('customers.store');
    Route::put('/customers/{user}', [AdminController::class, 'updateCustomer'])->name('customers.update');
    Route::delete('/customers/{user}', [AdminController::class, 'deleteCustomer'])->name('customers.delete');
});

// ========================================================================
// **OBSERVER PATTERN IMPLEMENTATION - Jayvian Lazarus Jerome**
// ========================================================================
// Enhanced controllers with Observer Pattern for event-driven notifications
// Handles user registration, booking changes, and report generation events

Route::group(['prefix' => 'observer'], function () {
    // ========================================================================
    // **Observer Pattern - Enhanced Authentication Routes**
    // ========================================================================
    // Enhanced Auth routes with Observer Pattern (triggers notifications)
    Route::middleware('guest')->group(function () {
        Route::get('/register', [ObserverAuthController::class, 'showRegister'])->name('observer.register');
        Route::post('/register', [ObserverAuthController::class, 'register'])->name('observer.register.post');
        Route::get('/login', [ObserverAuthController::class, 'showLogin'])->name('observer.login');
        Route::post('/login', [ObserverAuthController::class, 'login'])->name('observer.login.post');
    });

    // Enhanced User Routes with Observer Pattern
    Route::middleware(\App\Http\Middleware\UserOnly::class)->group(function () {
        Route::post('/logout', [ObserverAuthController::class, 'logout'])->name('observer.logout');
    });

    // ========================================================================
    // **Observer Pattern - Enhanced Admin Routes**
    // ========================================================================
    // Enhanced Admin routes with Observer Pattern (triggers booking/report notifications)
    Route::prefix('admin')->name('observer.admin.')->middleware(\App\Http\Middleware\AdminMiddleware::class)->group(function () {
        // Enhanced Admin Dashboard & Management
        Route::get('/dashboard', [ObserverAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/bookings', [ObserverAdminController::class, 'bookings'])->name('bookings');
        Route::post('/booking/{booking}/status', [ObserverAdminController::class, 'updateBookingStatus'])->name('booking.status');

        // Enhanced Reports with Observer Pattern
        Route::get('/reports', [ObserverReportsController::class, 'index'])->name('reports');
        Route::get('/reports/export', [ObserverReportsController::class, 'exportPDF'])->name('reports.export');

        // ========================================================================
        // **Observer Pattern Debug & Monitoring Routes**
        // ========================================================================
        // Debug routes for Observer Pattern (development/testing)
        Route::get('/observer/booking-info', [ObserverAdminController::class, 'getObserverInfo'])->name('observer.booking.debug');
        Route::get('/observer/report-info', [ObserverReportsController::class, 'getObserverInfo'])->name('observer.report.debug');
    });
});

// ============================================================================
// **BOOKING & RENTAL PROCESS MODULE - Chong Zheng Yao**
// ============================================================================

// Public Booking Search (No Authentication Required)
Route::get('/booking/search', [BookingController::class, 'searchForm'])->name('booking.search-form');
Route::post('/booking/search', [BookingController::class, 'search'])->name('booking.search');
Route::post('/booking/check-availability', [BookingController::class, 'checkAvailability'])->name('booking.check-availability');

// Authenticated User Booking Routes
Route::middleware(\App\Http\Middleware\UserOnly::class)->group(function () {
    // ========================================================================
    // **BOOKING ROUTES - Authenticated Users Only**
    // ========================================================================
    // User Booking Management
    Route::prefix('booking')->name('booking.')->group(function () {
        Route::get('/', [BookingController::class, 'index'])->name('index');
        Route::get('/create/{vehicle}', [BookingController::class, 'create'])->name('create');
        Route::post('/', [BookingController::class, 'store'])->name('store');
        Route::get('/{booking}/confirmation', [BookingController::class, 'confirmation'])->name('confirmation');
        Route::get('/{booking}', [BookingController::class, 'show'])->name('show');
        Route::patch('/{booking}/cancel', [BookingController::class, 'cancel'])->name('cancel');
        Route::patch('/{booking}/confirm', [BookingController::class, 'confirm'])->name('confirm');
        Route::patch('/{booking}/activate', [BookingController::class, 'activate'])->name('activate');
    });
});

// Test Export Route
Route::get('/admin/bookings/export', [BookingController::class, 'exportPDF'])->name('admin.bookings.export.test');

// Admin Booking Management
Route::prefix('admin')->name('admin.')->middleware(\App\Http\Middleware\AdminMiddleware::class)->group(function () {
    // ========================================================================
    // **Booking Management - Admin**
    // ========================================================================
    Route::get('/bookings', [AdminController::class, 'bookings'])->name('bookings');
    Route::get('/bookings/{booking}', [AdminController::class, 'showBooking'])->name('bookings.show');
    Route::patch('/bookings/{booking}/status', [AdminController::class, 'updateBookingStatus'])->name('bookings.update-status');
    Route::patch('/bookings/{booking}/return', [AdminController::class, 'returnVehicle'])->name('bookings.return');

    // Booking Export Route
    Route::get('/bookings/export', [BookingController::class, 'export'])->name('bookings.export');
});

// ============================================================================
// VEHICLE MANAGEMENT MODULE - Tan Xing Ye
// ============================================================================

// Public Vehicle Browsing (GUESTS CAN VIEW)
Route::get('/vehicles', [VehicleController::class, 'index'])
    ->middleware('cache.headers:public;max_age=1800')
    ->name('vehicles.index');

Route::get('/vehicles/{id}', [VehicleController::class, 'show'])
    ->middleware('cache.headers:public;max_age=900')
    ->name('vehicles.show');

// Admin Vehicle Management
Route::prefix('admin')->name('admin.')->middleware(\App\Http\Middleware\AdminMiddleware::class)->group(function () {
    // Vehicle CRUD Operations
    Route::get('/vehicles', [AdminController::class, 'vehicles'])->name('vehicles');
    Route::get('/vehicles/create', [AdminController::class, 'createVehicle'])->name('vehicles.create');

    // Rate limited operations
    Route::post('/vehicles', [AdminController::class, 'storeVehicle'])
        ->middleware('throttle:vehicle-creation')
        ->name('vehicles.store');

    Route::get('/vehicles/{vehicle}', [AdminController::class, 'showVehicle'])->name('vehicles.show');
    Route::get('/vehicles/{id}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');

    Route::put('/vehicles/{id}', [VehicleController::class, 'update'])
        ->middleware('throttle:vehicle-updates')
        ->name('vehicles.update');

    Route::delete('/vehicles/{id}', [VehicleController::class, 'destroy'])
        ->middleware('throttle:vehicle-deletion')
        ->name('vehicles.destroy');

    Route::patch('/vehicles/{vehicle}/toggle-status', [AdminController::class, 'toggleStatus'])
        ->name('vehicles.toggle-status');
});

// ============================================================================
// API ROUTES FOR VEHICLE MODULE
// ============================================================================
Route::prefix('api/v1')->middleware(['throttle:api'])->group(function () {
    // Public API endpoints
    Route::get('/vehicles/types/{type}/defaults', [VehicleController::class, 'getTypeDefaults'])
        ->name('api.vehicles.type-defaults');

    // Admin API endpoints
    Route::middleware(\App\Http\Middleware\AdminMiddleware::class)->group(function () {
        Route::get('/vehicles/statistics', [VehicleController::class, 'getStatistics'])
            ->name('api.vehicles.statistics');
    });
});

// Cache management
Route::prefix('admin/cache')->name('admin.cache.')
    ->middleware(\App\Http\Middleware\AdminMiddleware::class)->group(function () {
    Route::post('/vehicles/clear', [VehicleController::class, 'clearCache'])
        ->name('vehicles.clear');
});
