<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class BookingController extends Controller {

    /**
     * Display user's bookings
     */
    public function index() {
        $user = Auth::user();

        $bookings = Booking::with(['vehicle', 'vehicle.rentalRate'])
                ->forUser($user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

        return view('booking.index', compact('bookings'));
    }

    /**
     * Show booking form for a specific vehicle
     */
    public function create($vehicleId) {
        $vehicle = Vehicle::with('rentalRate')->findOrFail($vehicleId);

        // Check if vehicle is available
        if ($vehicle->status !== 'available') {
            return redirect()->route('vehicles.show', $vehicleId)
                            ->with('error', 'This vehicle is currently not available for booking.');
        }

        return view('booking.create', compact('vehicle'));
    }

    /**
     * Store a new booking
     */
    public function store(Request $request) {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'pickup_date' => 'required|date|after_or_equal:today',
            'pickup_time' => 'required|date_format:H:i',
            'return_date' => 'required|date|after:pickup_date',
            'return_time' => 'required|date_format:H:i',
            'pickup_location' => 'required|string|max:255',
            'return_location' => 'required|string|max:255',
            'special_requests' => 'nullable|string|max:500',
            'customer_phone' => 'required|string|max:20'
        ]);

        $user = Auth::user();
        $vehicle = Vehicle::with('rentalRate')->findOrFail($validated['vehicle_id']);

        // Combine date and time
        $pickupDateTime = Carbon::createFromFormat('Y-m-d H:i',
                $validated['pickup_date'] . ' ' . $validated['pickup_time']);
        $returnDateTime = Carbon::createFromFormat('Y-m-d H:i',
                $validated['return_date'] . ' ' . $validated['return_time']);

        // Check vehicle availability for these dates
        if (!$this->isVehicleAvailable($vehicle->id, $pickupDateTime, $returnDateTime)) {
            return back()->withErrors(['dates' => 'Vehicle is not available for the selected dates.'])
                            ->withInput();
        }

        // Calculate total cost
        $days = $pickupDateTime->diffInDays($returnDateTime) ?: 1;
        $totalAmount = $vehicle->rentalRate->calculateRate($days);
        $depositAmount = $totalAmount * 0.3; // 30% deposit
        
        // Create booking
        $booking = Booking::create([
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $validated['customer_phone'],
            'booking_number' => Booking::generateBookingNumber(),
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'pickup_datetime' => $pickupDateTime,
            'return_datetime' => $returnDateTime,
            'pickup_location' => $validated['pickup_location'],
            'return_location' => $validated['return_location'],
            'total_amount' => $totalAmount,
            'deposit_amount' => $depositAmount,
            'status' => 'pending',
            'payment_status' => 'pending', // Keep as pending until payment
            'special_requests' => $validated['special_requests']
        ]);

        // DON'T change vehicle status yet - wait until payment is completed
        // The vehicle status will be changed in PaymentController when payment succeeds

        // Redirect to payment instead of confirmation
        return redirect()->route('payment.form', $booking->id)
                        ->with('success', 'Booking created! Please complete payment to confirm your reservation.');
    }

    /**
     * Show booking confirmation page
     */
    public function confirmation($bookingId) {
        $booking = Booking::with(['vehicle', 'vehicle.rentalRate'])
                ->where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

        return view('booking.confirmation', compact('booking'));
    }

    /**
     * Show specific booking details
     */
    public function show($bookingId) {
        $booking = Booking::with(['vehicle', 'vehicle.rentalRate'])
                ->where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

        return view('booking.show', compact('booking'));
    }

    /**
     * Cancel a booking
     */
    public function cancel($bookingId) {
        $booking = Booking::where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

        // Only allow cancellation for pending or confirmed bookings
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return back()->with('error', 'This booking cannot be cancelled.');
        }

        $booking->update([
            'status' => 'cancelled',
            'payment_status' => 'cancelled',
            'cancellation_reason' => 'Cancelled by customer'
        ]);

        // Make vehicle available again when booking is cancelled
        $vehicle = $booking->vehicle;
        $vehicle->update(['status' => 'available']);

        return redirect()->route('booking.index')
                        ->with('success', 'Booking cancelled successfully.');
    }

    /**
     * Check vehicle availability
     */
    public function checkAvailability(Request $request) {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'pickup_date' => 'required|date',
            'return_date' => 'required|date|after:pickup_date'
        ]);

        $pickupDate = Carbon::parse($request->pickup_date);
        $returnDate = Carbon::parse($request->return_date);

        $isAvailable = $this->isVehicleAvailable($request->vehicle_id, $pickupDate, $returnDate);

        return response()->json([
                    'available' => $isAvailable,
                    'message' => $isAvailable ? 'Vehicle is available' : 'Vehicle is not available for selected dates'
        ]);
    }

    /**
     * Vehicle availability search
     */
    public function search(Request $request) {
        $validated = $request->validate([
            'pickup_date' => 'required|date|after_or_equal:today',
            'return_date' => 'required|date|after:pickup_date',
            'vehicle_type' => 'nullable|string',
            'location' => 'nullable|string'
        ]);

        $pickupDate = Carbon::parse($validated['pickup_date']);
        $returnDate = Carbon::parse($validated['return_date']);

        // Get available vehicles
        $query = Vehicle::with('rentalRate')
                ->where('status', 'available');

        if ($request->filled('vehicle_type')) {
            $query->where('type', $validated['vehicle_type']);
        }

        $allVehicles = $query->get();

        // Filter by availability
        $availableVehicles = $allVehicles->filter(function ($vehicle) use ($pickupDate, $returnDate) {
            return $this->isVehicleAvailable($vehicle->id, $pickupDate, $returnDate);
        });

        return view('booking.search', compact('availableVehicles', 'pickupDate', 'returnDate', 'validated'));
    }

    /**
     * Show search form
     */
    public function searchForm() {
        return view('booking.search-form');
    }

    /**
     * Private method to check vehicle availability
     */
    private function isVehicleAvailable($vehicleId, $pickupDate, $returnDate) {
        // Check for overlapping bookings
        $overlappingBookings = Booking::where('vehicle_id', $vehicleId)
                ->whereIn('status', ['confirmed', 'active']) // Remove 'pending' from here since pending bookings without payment don't reserve the vehicle
                ->whereIn('payment_status', ['paid', 'partial']) // Only check paid bookings
                ->where(function ($query) use ($pickupDate, $returnDate) {
                    $query->whereBetween('pickup_datetime', [$pickupDate, $returnDate])
                            ->orWhereBetween('return_datetime', [$pickupDate, $returnDate])
                            ->orWhere(function ($subQuery) use ($pickupDate, $returnDate) {
                                $subQuery->where('pickup_datetime', '<=', $pickupDate)
                                        ->where('return_datetime', '>=', $returnDate);
                            });
                })
                ->exists();

        return !$overlappingBookings;
    }

    /**
     * Confirm booking (for legacy support - now mainly handled by payment)
     */
    public function confirm($bookingId) {
        $booking = Booking::where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->where('status', 'pending')
                ->firstOrFail();

        // Check if payment is still pending
        if ($booking->payment_status === 'pending') {
            return redirect()->route('payment.form', $booking->id)
                            ->with('info', 'Please complete payment to confirm your booking.');
        }

        $booking->update([
            'status' => 'confirmed',
            'payment_status' => 'paid' // In real app, this would be set after payment processing
        ]);

        // Update vehicle status when manually confirming
        $booking->vehicle->update(['status' => 'rented']);

        return redirect()->route('booking.show', $booking->id)
                        ->with('success', 'Booking confirmed successfully!');
    }

    /**
     * Export bookings to PDF for admin
     */
    public function export(Request $request)
    {
        // Get filters from request
        $filters = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'date_range' => $request->get('date_range'),
            'sort' => $request->get('sort', 'newest')
        ];

        // Apply the same filtering logic as your main bookings method
        $query = Booking::with(['user', 'vehicle']);

        // Apply search filter
        if ($filters['search']) {
            $query->whereHas('user', function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            })->orWhereHas('vehicle', function($q) use ($filters) {
                $q->where('make', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('model', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('license_plate', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Apply status filter
        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        // Apply date range filter
        if ($filters['date_range']) {
            switch ($filters['date_range']) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                    break;
                case 'year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }

        // Apply sorting
        switch ($filters['sort']) {
            case 'oldest':
                $query->oldest();
                break;
            case 'amount_high':
                $query->orderBy('total_amount', 'desc');
                break;
            case 'amount_low':
                $query->orderBy('total_amount', 'asc');
                break;
            default:
                $query->latest();
        }

        $bookings = $query->get();

        // Calculate statistics
        $totalBookings = $bookings->count();
        $pendingCount = $bookings->where('status', 'pending')->count();
        $confirmedCount = $bookings->where('status', 'confirmed')->count();
        $totalRevenue = $bookings->where('status', '!=', 'cancelled')->sum('total_amount');

        $data = [
            'bookings' => $bookings,
            'totalBookings' => $totalBookings,
            'pendingCount' => $pendingCount,
            'confirmedCount' => $confirmedCount,
            'totalRevenue' => $totalRevenue,
            'exportDate' => \Carbon\Carbon::now('Asia/Kuala_Lumpur')->format('F d, Y \a\t g:i A'),
            'filters' => array_filter($filters)
        ];

        $pdf = Pdf::loadView('admin.bookings-export', $data);
        
        return $pdf->download('bookings-export-' . now()->format('Y-m-d') . '.pdf');
    }
}