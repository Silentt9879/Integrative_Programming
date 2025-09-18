<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register Vehicle Factory Service (if you create VehicleService)
        $this->app->singleton(\App\Services\VehicleService::class);

        // ADD: Register Booking Service
        $this->app->singleton(\App\Services\BookingService::class);
    }

    public function boot(): void
    {
        // Enhanced Booking API Rate Limiting with Abuse Detection
        RateLimiter::for('booking-creation', function (Request $request) {
            return Limit::perMinute(3)->by($request->user()?->id ?: $request->ip()); // Reduced from 5 to 3
        });

        RateLimiter::for('booking-status-update', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('booking-state-change', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('booking-cancellation', function (Request $request) {
            return Limit::perHour(5)->by($request->user()?->id ?: $request->ip()); // Changed to per hour
        });

        // Global booking activity monitoring (abuse detection)
        RateLimiter::for('booking-global-activity', function (Request $request) {
            return Limit::perMinute(50)->by($request->user()?->id ?: $request->ip());
        });

        // Vehicle-specific booking attempts
        RateLimiter::for('booking-vehicle-attempts', function (Request $request) {
            return Limit::perHour(5)->by($request->user()?->id ?: $request->ip());
        });
    }
}
