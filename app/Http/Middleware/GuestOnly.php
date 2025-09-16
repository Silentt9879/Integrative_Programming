<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuestOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            if ($user->is_admin) {
                return redirect()->route('admin.dashboard')
                    ->with('info', 'You are already logged in as an administrator.');
            } else {
                return redirect()->route('dashboard')
                    ->with('info', 'You are already logged in.');
            }
        }

        return $next($request);
    }
}