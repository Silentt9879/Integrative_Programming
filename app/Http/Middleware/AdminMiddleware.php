<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login')->with('error', 'Please log in as admin to access this area.');
        }

        $user = Auth::user();

        if (!$user->is_admin) {
            return redirect()->route('dashboard')
                ->with('error', 'Access denied. This area is for administrators only. You have been redirected to your user dashboard.');
        }

        if ($user->status !== 'active') {
            Auth::logout();
            return redirect()->route('admin.login')->with('error', 'Your admin account has been suspended.');
        }

        return $next($request);
    }
}