<?php
namespace App\Http\Controllers\Observer\Enhanced;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;

// Observer Pattern Imports - Jayvian
use App\Http\Controllers\Observer\Subjects\BookingSubject;
use App\Http\Controllers\Observer\Observers\EmailNotificationObserver;
use App\Http\Controllers\Observer\Observers\LoggingObserver;
use App\Http\Controllers\Observer\Observers\AdminNotificationObserver;

class AdminController extends \App\Http\Controllers\Controller
{
    private BookingSubject $bookingSubject;

    public function __construct()
    {
        // Initialize Observer Pattern
        $this->bookingSubject = new BookingSubject();
        
        // Attach observers
        $this->bookingSubject->attach(new EmailNotificationObserver());
        $this->bookingSubject->attach(new LoggingObserver());
        $this->bookingSubject->attach(new AdminNotificationObserver());
    }

    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        if (!Auth::user() || !Auth::user()->is_admin) {
            abort(403);
        }

        // Your existing dashboard logic here
        $stats = [
            'totalUsers' => \App\Models\User::where('is_admin', false)->count(),
            'totalBookings' => Booking::count(),
            'activeBookings' => Booking::whereIn('status', ['active', 'confirmed', 'ongoing'])->count(),
        ];

        return view('admin.dashboard', $stats);
    }

    /**
     * Enhanced booking status update with Observer Pattern
     */
    public function updateBookingStatus(Request $request, Booking $booking)
    {
        if (!Auth::user() || !Auth::user()->is_admin) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:pending,confirmed,active,completed,cancelled',
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000'
        ]);

        // Store the old status before updating
        $oldStatus = $booking->status;
        $newStatus = $request->status;

        try {
            // Update the booking status
            $booking->status = $newStatus;
            $booking->save();

            // OBSERVER PATTERN: Notify observers about booking status change
            if ($oldStatus !== $newStatus) {
                $changeData = [
                    'changed_by' => Auth::user()->name,
                    'change_reason' => $request->reason,
                    'admin_notes' => $request->notes,
                    'change_time' => now()
                ];
                
                $this->bookingSubject->notifyBookingStatusChanged($booking, $oldStatus, $newStatus, $changeData);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Booking status updated successfully',
                    'newStatus' => $newStatus
                ]);
            }

            return back()->with('success', 'Booking status updated successfully');

        } catch (\Exception $e) {
            \Log::error('Booking status update failed', [
                'booking_id' => $booking->id,
                'attempted_status' => $newStatus,
                'error' => $e->getMessage()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating booking status: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error updating booking status: ' . $e->getMessage());
        }
    }

    /**
     * Get bookings list
     */
    public function bookings()
    {
        if (!Auth::user() || !Auth::user()->is_admin) {
            abort(403);
        }

        $bookings = Booking::with(['user', 'vehicle'])->latest()->paginate(15);
        
        return view('admin.bookings', compact('bookings'));
    }

    /**
     * Observer Pattern: Get observer information for debugging
     */
    public function getObserverInfo()
    {
        if (!Auth::user() || !Auth::user()->is_admin) {
            abort(403);
        }

        $observers = [];
        foreach ($this->bookingSubject->getObservers() as $observer) {
            $observers[] = [
                'name' => $observer->getName(),
                'class' => get_class($observer)
            ];
        }

        return response()->json([
            'subject' => $this->bookingSubject->getSubjectName(),
            'observer_count' => count($observers),
            'observers' => $observers
        ]);
    }
}