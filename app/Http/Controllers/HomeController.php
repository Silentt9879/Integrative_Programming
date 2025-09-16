<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Vehicle;

class HomeController extends Controller
{
    public function index()
    {
        
        $vehicles = Vehicle::with('rentalRate')
            ->where('status', 'available')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();
            
        return view('home', compact('vehicles'));
    }

    public function about()
    {
        return view('about');
    }

    public function contact()
    {
        return view('contact');
    }

    public function dashboard()
    {
        
        $user = Auth::user();
        
        
        $availableVehicles = Vehicle::where('status', 'available')->count();
        
        
        $totalBookings = 0;
        $activeBookings = 0;
        
        // Determine member status based on bookings
        $memberStatus = 'New';
        if ($totalBookings > 10) {
            $memberStatus = 'VIP';
        } elseif ($totalBookings > 5) {
            $memberStatus = 'Regular';
        } elseif ($totalBookings > 0) {
            $memberStatus = 'Member';
        }
        
        return view('user.dashboard', compact(
            'totalBookings', 
            'activeBookings', 
            'availableVehicles', 
            'memberStatus'
        ));
    }
}