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
    }

    public function boot(): void
    {
        // Configure rate limiters for vehicle operations
        RateLimiter::for('vehicle-creation', function (Request $request) {
            return $request->user()
                ? Limit::perHour(5)->by($request->user()->id)
                : Limit::perHour(2)->by($request->ip());
        });

        RateLimiter::for('vehicle-updates', function (Request $request) {
            return $request->user()
                ? Limit::perHour(10)->by($request->user()->id)
                : Limit::perHour(5)->by($request->ip());
        });

        RateLimiter::for('vehicle-deletion', function (Request $request) {
            return $request->user()
                ? Limit::perHour(3)->by($request->user()->id)
                : Limit::perHour(1)->by($request->ip());
        });
    }
}
