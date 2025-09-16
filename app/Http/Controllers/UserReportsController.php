<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class UserReportsController extends Controller
{
    /**
     * Generate and download user booking report as PDF
     */
    public function generateBookingReport(Request $request)
    {
        $user = Auth::user();
        
        // Get all bookings for the authenticated user
        $bookings = Booking::with(['vehicle', 'vehicle.rentalRate'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate summary statistics
        $stats = [
            'totalBookings' => $bookings->count(),
            'activeBookings' => $bookings->whereIn('status', ['confirmed', 'active', 'ongoing'])->count(),
            'completedBookings' => $bookings->where('status', 'completed')->count(),
            'cancelledBookings' => $bookings->where('status', 'cancelled')->count(),
            'totalAmountPaid' => $bookings->where('payment_status', 'paid')->sum('total_amount'),
            'pendingPayments' => $bookings->where('payment_status', 'pending')->sum('total_amount'),
        ];

        // Group bookings by status for better organization
        $groupedBookings = [
            'Active' => $bookings->whereIn('status', ['confirmed', 'active', 'ongoing']),
            'Completed' => $bookings->where('status', 'completed'),
            'Cancelled' => $bookings->where('status', 'cancelled'),
            'Pending' => $bookings->where('status', 'pending'),
        ];

        $data = [
            'user' => $user,
            'bookings' => $bookings,
            'groupedBookings' => $groupedBookings,
            'stats' => $stats,
            'generatedAt' => Carbon::now(),
            'reportTitle' => 'My Booking History Report'
        ];

        // Generate PDF
        $pdf = Pdf::loadView('reports.user-booking-report', $data);
        $pdf->setPaper('A4', 'portrait');
        
        $fileName = 'booking-report-' . $user->id . '-' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($fileName);
    }
}