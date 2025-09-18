<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Vehicle;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BookingController extends Controller
{
    private BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Display user's bookings
     */
    public function index()
    {
        try {
            $user = Auth::user();

            $bookings = Booking::with(['vehicle', 'vehicle.rentalRate'])
                ->forUser($user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return view('booking.index', compact('bookings'));
        } catch (\Exception $e) {
            Log::error('Booking index error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->route('dashboard')
                ->with('error', 'Unable to load your bookings. Please try again.');
        }
    }

    /**
     * Show booking creation form
     */
    public function create($vehicleId)
    {
        try {
            $vehicle = Vehicle::with('rentalRate')->findOrFail($vehicleId);

            // Check if vehicle is available
            if ($vehicle->status !== 'available') {
                return redirect()->route('vehicles.show', $vehicleId)
                    ->with('error', 'This vehicle is currently not available for booking.');
            }

            return view('booking.create', compact('vehicle'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('vehicles.index')
                ->with('error', 'Vehicle not found.');
        } catch (\Exception $e) {
            Log::error('Booking create form error', [
                'user_id' => Auth::id(),
                'vehicle_id' => $vehicleId,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('vehicles.index')
                ->with('error', 'Unable to load booking form. Please try again.');
        }
    }

    /**
     * Store a new booking using BookingService
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'pickup_date' => [
                'required',
                'date',
                'after_or_equal:today',
                'before:' . now()->addMonths(12)->format('Y-m-d')
            ],
            'pickup_time' => 'required|date_format:H:i',
            'return_date' => 'required|date|after:pickup_date',
            'return_time' => 'required|date_format:H:i',
            'pickup_location' => 'required|string|max:255',
            'return_location' => 'required|string|max:255',
            'special_requests' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9\s\.\,\!\?\-\(\)]*$/'
            ],
            'customer_phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^[+]?[0-9\s\-\(\)]{8,20}$/'
            ]
        ], [
            'pickup_date.before' => 'Pickup date cannot be more than 12 months in advance.',
            'customer_phone.regex' => 'Please enter a valid phone number.',
            'special_requests.regex' => 'Special requests contain invalid characters.'
        ]);

        try {
            $booking = $this->bookingService->createBooking($validated);

            return redirect()->route('payment.form', $booking->id)
                ->with('success', 'Booking created! Please complete payment to confirm your reservation.');

        } catch (\Exception $e) {
            Log::error('Booking creation error', [
                'user_id' => Auth::id(),
                'vehicle_id' => $validated['vehicle_id'],
                'error' => $e->getMessage()
            ]);

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show booking confirmation page
     */
    public function confirmation($bookingId)
    {
        try {
            $booking = Booking::with(['vehicle', 'vehicle.rentalRate'])
                ->where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            return view('booking.confirmation', compact('booking'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('booking.index')
                ->with('error', 'Booking not found.');
        } catch (\Exception $e) {
            Log::error('Booking confirmation error', [
                'user_id' => Auth::id(),
                'booking_id' => $bookingId,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('booking.index')
                ->with('error', 'Unable to load booking confirmation.');
        }
    }

    /**
     * Show specific booking details with State Pattern information
     */
    public function show($bookingId)
    {
        try {
            $booking = $this->bookingService->getBookingWithState($bookingId, Auth::id());

            // Extract state information for view
            $availableActions = $booking->available_actions;
            $stateMessage = $booking->state_message;

            return view('booking.show', compact('booking', 'availableActions', 'stateMessage'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('booking.index')
                ->with('error', 'Booking not found.');
        } catch (\Exception $e) {
            Log::error('Booking show error', [
                'user_id' => Auth::id(),
                'booking_id' => $bookingId,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('booking.index')
                ->with('error', 'Unable to load booking details.');
        }
    }

    /**
     * Cancel a booking using BookingService and State Pattern
     */
    public function cancel($bookingId)
    {
        try {
            $booking = Booking::where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $this->bookingService->cancelBooking($booking, 'Cancelled by customer');

            return redirect()->route('booking.index')
                ->with('success', 'Booking cancelled successfully.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('booking.index')
                ->with('error', 'Booking not found.');
        } catch (\Exception $e) {
            Log::error('Booking cancellation error', [
                'user_id' => Auth::id(),
                'booking_id' => $bookingId,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Confirm booking using BookingService and State Pattern
     */
    public function confirm($bookingId)
    {
        try {
            $booking = Booking::where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            if ($booking->requiresPayment()) {
                return redirect()->route('payment.form', $booking->id)
                    ->with('info', 'Please complete payment to confirm your booking.');
            }

            $this->bookingService->confirmBooking($booking);

            return redirect()->route('booking.show', $booking->id)
                ->with('success', 'Booking confirmed successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('booking.index')
                ->with('error', 'Booking not found.');
        } catch (\Exception $e) {
            Log::error('Booking confirmation error', [
                'user_id' => Auth::id(),
                'booking_id' => $bookingId,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Activate booking (mark as picked up) using BookingService and State Pattern
     */
    public function activate($bookingId)
    {
        try {
            $booking = Booking::where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $this->bookingService->activateBooking($booking);

            return redirect()->route('booking.show', $booking->id)
                ->with('success', 'Vehicle pickup confirmed. Rental is now active!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('booking.index')
                ->with('error', 'Booking not found.');
        } catch (\Exception $e) {
            Log::error('Booking activation error', [
                'user_id' => Auth::id(),
                'booking_id' => $bookingId,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Complete booking (mark as returned) using BookingService and State Pattern
     */
    public function complete(Request $request, $bookingId)
    {
        $validated = $request->validate([
            'damage_charges' => 'nullable|numeric|min:0|max:999999.99',
            'return_notes' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9\s\.\,\!\?\-\(\)]*$/'
            ]
        ], [
            'return_notes.regex' => 'Return notes contain invalid characters.',
            'damage_charges.max' => 'Damage charges amount is too high.'
        ]);

        try {
            $booking = Booking::where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $this->bookingService->completeBooking($booking, $validated);

            return redirect()->route('booking.show', $booking->id)
                ->with('success', 'Booking completed successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('booking.index')
                ->with('error', 'Booking not found.');
        } catch (\Exception $e) {
            Log::error('Booking completion error', [
                'user_id' => Auth::id(),
                'booking_id' => $bookingId,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Check vehicle availability (AJAX endpoint using BookingService)
     */
    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'pickup_date' => 'required|date|after_or_equal:today',
            'pickup_time' => 'required|date_format:H:i',
            'return_date' => 'required|date|after:pickup_date',
            'return_time' => 'required|date_format:H:i'
        ]);

        try {
            $pickupDateTime = Carbon::createFromFormat(
                'Y-m-d H:i',
                $validated['pickup_date'] . ' ' . $validated['pickup_time']
            );

            $returnDateTime = Carbon::createFromFormat(
                'Y-m-d H:i',
                $validated['return_date'] . ' ' . $validated['return_time']
            );

            $isAvailable = $this->bookingService->isVehicleAvailable(
                $validated['vehicle_id'],
                $pickupDateTime,
                $returnDateTime
            );

            return response()->json([
                'available' => $isAvailable,
                'message' => $isAvailable ? 'Vehicle is available' : 'Vehicle is not available for selected dates'
            ]);

        } catch (\Exception $e) {
            Log::error('Availability check error', [
                'user_id' => Auth::id() ?? 'guest',
                'vehicle_id' => $validated['vehicle_id'] ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'available' => false,
                'message' => 'Unable to check availability. Please try again.'
            ], 500);
        }
    }

    /**
     * Vehicle availability search
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'pickup_date' => [
                'required',
                'date',
                'after_or_equal:today',
                'before:' . now()->addMonths(12)->format('Y-m-d')
            ],
            'return_date' => 'required|date|after:pickup_date',
            'vehicle_type' => 'nullable|string|in:Economy,Sedan,SUV,Luxury,Truck,Van',
            'location' => 'nullable|string|max:255'
        ], [
            'pickup_date.before' => 'Pickup date cannot be more than 12 months in advance.',
            'vehicle_type.in' => 'Invalid vehicle type selected.'
        ]);

        try {
            $pickupDate = Carbon::parse($validated['pickup_date']);
            $returnDate = Carbon::parse($validated['return_date']);

            // Get available vehicles
            $query = Vehicle::with('rentalRate')
                ->where('status', 'available');

            if ($request->filled('vehicle_type')) {
                $query->where('type', $validated['vehicle_type']);
            }

            $allVehicles = $query->get();

            // Filter by availability using service
            $availableVehicles = $allVehicles->filter(function ($vehicle) use ($pickupDate, $returnDate) {
                return $this->bookingService->isVehicleAvailable($vehicle->id, $pickupDate, $returnDate);
            });

            return view('booking.search', compact('availableVehicles', 'pickupDate', 'returnDate', 'validated'));

        } catch (\Exception $e) {
            Log::error('Vehicle search error', [
                'user_id' => Auth::id() ?? 'guest',
                'search_params' => $validated ?? [],
                'error' => $e->getMessage()
            ]);

            return redirect()->route('booking.search-form')
                ->with('error', 'Unable to search vehicles. Please try again.');
        }
    }

    /**
     * Show search form
     */
    public function searchForm()
    {
        return view('booking.search-form');
    }

    /**
     * Export bookings to PDF (for admin or user reports)
     */
    public function exportPDF(Request $request)
    {
        try {
            // This would integrate with your existing PDF export functionality
            // For now, return a placeholder response
            return response()->json([
                'message' => 'PDF export functionality will be integrated with existing reports module',
                'available_endpoints' => [
                    'user_reports' => route('user.reports.booking-report'),
                    'admin_reports' => route('admin.reports.export')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Booking PDF export error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Export failed'], 500);
        }
    }
}
