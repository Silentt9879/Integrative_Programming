<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Booking;

class UserDashboardController extends Controller
{
    public function index()
    {
        
        $user = Auth::user();
        
        // Additional check: Ensure user is NOT admin
        if ($user->is_admin) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Admin users should use the admin panel.');
        }
        
        // Get user-specific dashboard data
        $stats = [
            'activeBookings' => Booking::where('user_id', $user->id)
                ->whereIn('status', ['active', 'confirmed', 'ongoing'])->count(),
            'totalBookings' => Booking::where('user_id', $user->id)->count(),
            'availableVehicles' => Vehicle::where('status', 'available')->count(),
            'memberStatus' => $this->getUserMemberStatus($user),
        ];
        
        return view('user.dashboard', $stats);
    }
    
    private function getUserMemberStatus($user)
    {
        $bookingCount = Booking::where('user_id', $user->id)->count();
        
        if ($bookingCount >= 10) return 'VIP';
        if ($bookingCount >= 5) return 'Regular';
        if ($bookingCount >= 1) return 'Member';
        return 'New';
    }
}