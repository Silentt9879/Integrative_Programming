<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Vehicle;
use App\State\StateFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingController extends Controller {

    //display booking
    public function index() {
        $user = Auth::user();

        $bookings = Booking::with(['vehicle', 'vehicle.rentalRate'])
                ->forUser($user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

        return view('booking.index', compact('bookings'));
    }

    //show booking list
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
            'payment_status' => 'pending',
            'special_requests' => $validated['special_requests']
        ]);

        // Reserve the vehicle temporarily (set to 'rented' status to prevent double booking)
        $vehicle->update(['status' => 'rented']);

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

        // Get available actions using State Pattern
        $availableActions = $booking->getAvailableActions();
        $stateMessage = $booking->getStateMessage();

        return view('booking.show', compact('booking', 'availableActions', 'stateMessage'));
    }

    /**
     * Cancel a booking - UPDATED to use State Pattern
     */
    public function cancel($bookingId) {
        $booking = Booking::where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

        // Use State Pattern to check if cancellation is allowed
        if (!$booking->canPerformAction('cancel')) {
            return back()->with('error', 'This booking cannot be cancelled in its current state.');
        }

        // Use State Pattern method to cancel
        $cancelled = $booking->cancel('Cancelled by customer');

        if (!$cancelled) {
            return back()->with('error', 'Unable to cancel booking. It may be too close to pickup time.');
        }

        return redirect()->route('booking.index')
                        ->with('success', 'Booking cancelled successfully.');
    }

    /**
     * Confirm booking - UPDATED to use State Pattern
     */
    public function confirm($bookingId) {
        $booking = Booking::where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

        // Check if confirmation action is available
        if (!$booking->canPerformAction('confirm')) {
            if ($booking->requiresPayment()) {
                return redirect()->route('payment.form', $booking->id)
                                ->with('info', 'Please complete payment to confirm your booking.');
            }
            return back()->with('error', 'This booking cannot be confirmed in its current state.');
        }

        // Use State Pattern to confirm booking
        $confirmed = $booking->confirm();

        if (!$confirmed) {
            return back()->with('error', 'Unable to confirm booking. Please ensure payment is completed.');
        }

        return redirect()->route('booking.show', $booking->id)
                        ->with('success', 'Booking confirmed successfully!');
    }

    /**
     * Activate booking (mark as picked up) - NEW METHOD using State Pattern
     */
    public function activate($bookingId) {
        $booking = Booking::where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

        // Check if activation is allowed
        if (!$booking->canPerformAction('activate')) {
            return back()->with('error', 'Cannot activate booking. The pickup time may not have arrived yet.');
        }

        // Use State Pattern to activate
        $activated = $booking->activate();

        if (!$activated) {
            return back()->with('error', 'Unable to activate booking.');
        }

        return redirect()->route('booking.show', $booking->id)
                        ->with('success', 'Vehicle pickup confirmed. Rental is now active!');
    }

    /**
     * Complete booking (mark as returned) - NEW METHOD using State Pattern
     */
    public function complete(Request $request, $bookingId) {
        $booking = Booking::where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

        // Check if completion is allowed
        if (!$booking->canPerformAction('complete')) {
            return back()->with('error', 'Cannot complete booking. The rental may still be active.');
        }

        $data = $request->validate([
            'damage_charges' => 'nullable|numeric|min:0',
            'return_notes' => 'nullable|string|max:500'
        ]);

        // Use State Pattern to complete booking
        $completed = $booking->complete($data);

        if (!$completed) {
            return back()->with('error', 'Unable to complete booking.');
        }

        return redirect()->route('booking.show', $booking->id)
                        ->with('success', 'Booking completed successfully!');
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
                ->whereIn('status', ['confirmed', 'active'])
                ->whereIn('payment_status', ['paid', 'partial'])
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
}
