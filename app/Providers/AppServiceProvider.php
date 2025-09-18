<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use App\Services\ObserverService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Vehicle Factory Service (if you create VehicleService)
        $this->app->singleton(\App\Services\VehicleService::class);

        // Register Booking Service
        $this->app->singleton(\App\Services\BookingService::class);

        // Register ObserverService as singleton
        $this->app->singleton(ObserverService::class, function ($app) {
            return new ObserverService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure custom rate limiters for API
        $this->configureRateLimiting();
    }

    /**
     * Configure rate limiting for API endpoints
     */
    private function configureRateLimiting(): void
    {
        // Vehicle management rate limits
        RateLimiter::for('vehicle-creation', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('vehicle-updates', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('vehicle-deletion', function (Request $request) {
            return Limit::perMinute(3)->by($request->user()?->id ?: $request->ip());
        });

        // Enhanced Booking API Rate Limiting with Abuse Detection
        RateLimiter::for('booking-creation', function (Request $request) {
            return Limit::perMinute(3)->by($request->user()?->id ?: $request->ip()); // Your more restrictive limit
        });

        RateLimiter::for('booking-status-update', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('booking-state-change', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()); // Your limit
        });

        RateLimiter::for('booking-cancellation', function (Request $request) {
            return Limit::perHour(5)->by($request->user()?->id ?: $request->ip()); // Your enhanced limit
        });

        // Global booking activity monitoring (abuse detection) - Your addition
        RateLimiter::for('booking-global-activity', function (Request $request) {
            return Limit::perMinute(50)->by($request->user()?->id ?: $request->ip());
        });

        // Vehicle-specific booking attempts - Your addition
        RateLimiter::for('booking-vehicle-attempts', function (Request $request) {
            return Limit::perHour(5)->by($request->user()?->id ?: $request->ip());
        });

        // Observer Pattern rate limits
        RateLimiter::for('observer-management', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('observer-bulk-operations', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('observer-event-trigger', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('observer-events-access', function (Request $request) {
            return Limit::perMinute(100)->by($request->user()?->id ?: $request->ip());
        });
    }
}