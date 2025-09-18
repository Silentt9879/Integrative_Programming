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
            // API request - return JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            
            // Web request - redirect
            return redirect()->route('admin.login')->with('error', 'Please log in as admin to access this area.');
        }

        $user = Auth::user();

        if (!$user->is_admin) {
            // API request - return JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin access required'
                ], 403);
            }
            
            // Web request - redirect
            return redirect()->route('dashboard')
                ->with('error', 'Access denied. This area is for administrators only. You have been redirected to your user dashboard.');
        }

        if ($user->status !== 'active') {
            Auth::logout();
            
            // API request - return JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your admin account has been suspended'
                ], 403);
            }
            
            // Web request - redirect
            return redirect()->route('admin.login')->with('error', 'Your admin account has been suspended.');
        }

        return $next($request);
    }
}