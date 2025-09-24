<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BookingService;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BookingApiController extends Controller
{
    private BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }


    //Get user's bookings
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'payment_status', 'from_date', 'to_date']);

            // Convert comma-separated status to array
            if (isset($filters['status']) && is_string($filters['status'])) {
                $filters['status'] = explode(',', $filters['status']);
            }

            $bookings = $this->bookingService->getUserBookings(Auth::id(), $filters);

            return $this->successResponse($bookings, 'Bookings retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Booking API index error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse('Failed to retrieve bookings', 500);
        }
    }

    //Show specific booking with State Pattern information
    public function show(int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->getBookingWithState($id, Auth::id());

            return $this->successResponse($booking, 'Booking retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Booking not found', 404);
        } catch (\Exception $e) {
            Log::error('Booking API show error', [
                'user_id' => Auth::id(),
                'booking_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse('Failed to retrieve booking', 500);
        }
    }


    //Create new booking
    public function store(StoreBookingRequest $request): JsonResponse
    {
        // Rate limiting check for  booking activity
        if (RateLimiter::tooManyAttempts('booking-global-activity:' . Auth::id(), 50)) {
            Log::warning('Booking API global activity abuse detected', [
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return $this->errorResponse('Too many booking requests. Please try again later.', 429);
        }

        // Check for suspicious booking patterns for same vehicle
        $suspiciousKey = 'booking-vehicle-attempts:' . Auth::id() . ':' . $request->vehicle_id;
        if (RateLimiter::tooManyAttempts($suspiciousKey, 5)) {
            Log::warning('Suspicious booking pattern detected - same vehicle', [
                'user_id' => Auth::id(),
                'vehicle_id' => $request->vehicle_id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return $this->errorResponse(
                'Multiple booking attempts detected for this vehicle. Please wait before trying again.',
                429
            );
        }

        try {
            // Data is already validated and sanitized by StoreBookingRequest
            $validatedData = $request->validated();

            // Additional sanitization layer for critical fields (Defense in Depth)
            $sanitizedData = [
                'vehicle_id' => (int) $validatedData['vehicle_id'],
                'pickup_date' => $validatedData['pickup_date'],
                'pickup_time' => $validatedData['pickup_time'],
                'return_date' => $validatedData['return_date'],
                'return_time' => $validatedData['return_time'],
                'pickup_location' => strip_tags(trim($validatedData['pickup_location'])),
                'return_location' => strip_tags(trim($validatedData['return_location'])),
                'special_requests' => !empty($validatedData['special_requests']) ?
                                    htmlspecialchars(strip_tags(trim($validatedData['special_requests'])), ENT_QUOTES, 'UTF-8') : null,
                'customer_phone' => preg_replace('/[^0-9+\s\-\(\)]/', '', $validatedData['customer_phone'])
            ];

            $booking = $this->bookingService->createBooking($sanitizedData);

            // Hit rate limiters after successful operation
            RateLimiter::hit('booking-global-activity:' . Auth::id(), 60);
            RateLimiter::hit($suspiciousKey, 3600); // 1 hour

            return $this->successResponse([
                'booking' => $booking,
                'available_actions' => $booking->getAvailableActions(),
                'state_message' => $booking->getStateMessage(),
                'requires_payment' => $booking->requiresPayment()
            ], 'Booking created successfully', 201);

        } catch (\Exception $e) {
            // Enhanced security logging
            Log::error('Booking API store error - Security Event', [
                'user_id' => Auth::id(),
                'vehicle_id' => $request->vehicle_id ?? 'unknown',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'error' => $e->getMessage(),
                'sanitized_data' => isset($sanitizedData) ? array_keys($sanitizedData) : 'not_set'
            ]);

            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    //Update booking status 
    public function updateStatus(UpdateBookingRequest $request, int $id): JsonResponse
    {
        // Rate limiting check for status update abuse
        if (RateLimiter::tooManyAttempts('booking-status-abuse:' . Auth::id(), 20)) {
            Log::warning('Booking status update abuse detected', [
                'user_id' => Auth::id(),
                'booking_id' => $id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return $this->errorResponse('Too many status update requests. Please try again later.', 429);
        }

        try {
            $booking = Booking::where('id', $id)
                             ->where('user_id', Auth::id())
                             ->firstOrFail();

            $validatedData = $request->validated();
            $newStatus = $validatedData['status'];
            unset($validatedData['status']);

            $this->bookingService->updateBookingStatus($booking, $newStatus, $validatedData);

            $updatedBooking = $this->bookingService->getBookingWithState($id, Auth::id());

            // Hit rate limiter after successful operation
            RateLimiter::hit('booking-status-abuse:' . Auth::id(), 300); // 5 minutes

            return $this->successResponse([
                'booking' => $updatedBooking,
                'previous_status' => $booking->getOriginal('status'),
                'current_status' => $updatedBooking->status
            ], "Booking status updated to {$newStatus} successfully");

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Booking not found', 404);
        } catch (\Exception $e) {
            Log::error('Booking API status update error - Security Event', [
                'user_id' => Auth::id(),
                'booking_id' => $id,
                'requested_status' => $request->status ?? 'unknown',
                'ip_address' => $request->ip(),
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse($e->getMessage(), 400);
        }
    }


    //Cancel booking 
    public function destroy(int $id, Request $request): JsonResponse
    {
        // Rate limiting for cancellation abuse
        if (RateLimiter::tooManyAttempts('booking-cancel-abuse:' . Auth::id(), 10)) {
            Log::warning('Booking cancellation abuse detected', [
                'user_id' => Auth::id(),
                'booking_id' => $id,
                'ip_address' => $request->ip()
            ]);

            return $this->errorResponse('Too many cancellation requests. Please try again later.', 429);
        }

        try {
            $validator = Validator::make($request->all(), [
                'reason' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[a-zA-Z0-9\s\.\,\!\?\-\(\)]*$/' // Prevent injection
                ]
            ], [
                'reason.regex' => 'Cancellation reason contains invalid characters.'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', 422, $validator->errors());
            }

            $booking = Booking::where('id', $id)
                             ->where('user_id', Auth::id())
                             ->firstOrFail();

            // Sanitize reason
            $sanitizedReason = htmlspecialchars(strip_tags(trim($request->reason)), ENT_QUOTES, 'UTF-8');

            $this->bookingService->cancelBooking($booking, $sanitizedReason);

            // Hit rate limiter after successful operation
            RateLimiter::hit('booking-cancel-abuse:' . Auth::id(), 3600); // 1 hour

            return $this->successResponse([
                'booking_id' => $id,
                'status' => 'cancelled',
                'reason' => $sanitizedReason
            ], 'Booking cancelled successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Booking not found', 404);
        } catch (\Exception $e) {
            Log::error('Booking API cancel error - Security Event', [
                'user_id' => Auth::id(),
                'booking_id' => $id,
                'ip_address' => $request->ip(),
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse($e->getMessage(), 400);
        }
    }


    //Confirm booking 
    public function confirm(int $id): JsonResponse
    {
        // Rate limiting for state change abuse
        if (RateLimiter::tooManyAttempts('booking-state-abuse:' . Auth::id(), 15)) {
            Log::warning('Booking state change abuse detected - confirm', [
                'user_id' => Auth::id(),
                'booking_id' => $id,
                'action' => 'confirm'
            ]);

            return $this->errorResponse('Too many state change requests.
            Please try again later.', 429);
        }

        try {
            $booking = Booking::where('id', $id)
                             ->where('user_id', Auth::id())
                             ->firstOrFail();

            $this->bookingService->confirmBooking($booking);

            $updatedBooking = $this->bookingService->getBookingWithState($id, Auth::id());

            // Hit rate limiter after successful operation
            RateLimiter::hit('booking-state-abuse:' . Auth::id(), 600); // 10 minutes

            return $this->successResponse($updatedBooking, 'Booking confirmed successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Booking not found', 404);
        } catch (\Exception $e) {
            Log::error('Booking API confirm error - Security Event', [
                'user_id' => Auth::id(),
                'booking_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse($e->getMessage(), 400);
        }
    }


    //Activate booking (mark as picked up)
    public function activate(int $id): JsonResponse
    {
        // Rate limiting for state change abuse
        if (RateLimiter::tooManyAttempts('booking-state-abuse:' . Auth::id(), 15)) {
            Log::warning('Booking state change abuse detected - activate', [
                'user_id' => Auth::id(),
                'booking_id' => $id,
                'action' => 'activate'
            ]);

            return $this->errorResponse('Too many state change requests. Please try again later.', 429);
        }

        try {
            $booking = Booking::where('id', $id)
                             ->where('user_id', Auth::id())
                             ->firstOrFail();

            $this->bookingService->activateBooking($booking);

            $updatedBooking = $this->bookingService->getBookingWithState($id, Auth::id());

            // Hit rate limiter after successful operation
            RateLimiter::hit('booking-state-abuse:' . Auth::id(), 600); // 10 minutes

            return $this->successResponse($updatedBooking, 'Booking activated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Booking not found', 404);
        } catch (\Exception $e) {
            Log::error('Booking API activate error - Security Event', [
                'user_id' => Auth::id(),
                'booking_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse($e->getMessage(), 400);
        }
    }

  
    //Complete booking
    public function complete(Request $request, int $id): JsonResponse
    {
        // Rate limiting for state change abuse
        if (RateLimiter::tooManyAttempts('booking-state-abuse:' . Auth::id(), 15)) {
            Log::warning('Booking state change abuse detected - complete', [
                'user_id' => Auth::id(),
                'booking_id' => $id,
                'action' => 'complete'
            ]);

            return $this->errorResponse('Too many state change requests. Please try again later.', 429);
        }

        try {
            $validator = Validator::make($request->all(), [
                'damage_charges' => 'nullable|numeric|min:0|max:99999.99',
                'return_notes' => [
                    'nullable',
                    'string',
                    'max:500',
                    'regex:/^[a-zA-Z0-9\s\.\,\!\?\-\(\)]*$/'
                ]
            ], [
                'return_notes.regex' => 'Return notes contain invalid characters.'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', 422, $validator->errors());
            }

            $booking = Booking::where('id', $id)
                             ->where('user_id', Auth::id())
                             ->firstOrFail();

            // Sanitize return notes
            $validatedData = $validator->validated();
            if (!empty($validatedData['return_notes'])) {
                $validatedData['return_notes'] = htmlspecialchars(
                    strip_tags(trim($validatedData['return_notes'])),
                    ENT_QUOTES,
                    'UTF-8'
                );
            }

            $this->bookingService->completeBooking($booking, $validatedData);

            $updatedBooking = $this->bookingService->getBookingWithState($id, Auth::id());

            // Hit rate limiter after successful operation
            RateLimiter::hit('booking-state-abuse:' . Auth::id(), 600); // 10 minutes

            return $this->successResponse($updatedBooking, 'Booking completed successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Booking not found', 404);
        } catch (\Exception $e) {
            Log::error('Booking API complete error - Security Event', [
                'user_id' => Auth::id(),
                'booking_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    //Check vehicle availability
    public function checkAvailability(Request $request): JsonResponse
    {
        // Rate limiting for availability check abuse
        $identifier = Auth::id() ?? $request->ip();
        if (RateLimiter::tooManyAttempts('booking-availability-check:' . $identifier, 100)) {
            Log::warning('Booking availability check abuse detected', [
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return $this->errorResponse('Too many availability check requests. Please try again later.', 429);
        }

        try {
            $validator = Validator::make($request->all(), [
                'vehicle_id' => 'required|exists:vehicles,id',
                'pickup_date' => 'required|date|after_or_equal:today|before:' . now()->addMonths(12)->format('Y-m-d'),
                'pickup_time' => 'required|date_format:H:i',
                'return_date' => 'required|date|after:pickup_date',
                'return_time' => 'required|date_format:H:i'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', 422, $validator->errors());
            }

            $data = $validator->validated();

            $pickupDateTime = \Carbon\Carbon::createFromFormat(
                'Y-m-d H:i',
                $data['pickup_date'] . ' ' . $data['pickup_time']
            );

            $returnDateTime = \Carbon\Carbon::createFromFormat(
                'Y-m-d H:i',
                $data['return_date'] . ' ' . $data['return_time']
            );

            $isAvailable = $this->bookingService->isVehicleAvailable(
                $data['vehicle_id'],
                $pickupDateTime,
                $returnDateTime
            );

            $vehicle = Vehicle::with('rentalRate')->find($data['vehicle_id']);
            $days = $pickupDateTime->diffInDays($returnDateTime) ?: 1;
            $totalCost = $vehicle->rentalRate ? $vehicle->rentalRate->calculateRate($days) : 0;

            // Hit rate limiter after successful operation
            RateLimiter::hit('booking-availability-check:' . $identifier, 60);

            return $this->successResponse([
                'available' => $isAvailable,
                'vehicle' => $vehicle,
                'rental_days' => $days,
                'total_cost' => $totalCost,
                'deposit_amount' => $totalCost * 0.3
            ], $isAvailable ? 'Vehicle is available' : 'Vehicle is not available for selected dates');

        } catch (\Exception $e) {
            Log::error('Booking API availability check error - Security Event', [
                'user_id' => Auth::id() ?? 'guest',
                'ip_address' => $request->ip(),
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse('Failed to check availability', 500);
        }
    }

    /**
     * Get booking statistics using State Pattern
     * GET /api/v1/bookings/statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->bookingService->getBookingStatistics(Auth::id());

            return $this->successResponse($stats, 'Booking statistics retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Booking API statistics error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse('Failed to retrieve statistics', 500);
        }
    }

    /**
     * Get available actions for a booking (State Pattern feature)
     * GET /api/v1/bookings/{id}/actions
     */
    public function getAvailableActions(int $id): JsonResponse
    {
        try {
            $booking = Booking::where('id', $id)
                             ->where('user_id', Auth::id())
                             ->firstOrFail();

            $availableActions = $booking->getAvailableActions();
            $stateMessage = $booking->getStateMessage();

            return $this->successResponse([
                'booking_id' => $id,
                'current_status' => $booking->status,
                'available_actions' => $availableActions,
                'state_message' => $stateMessage,
                'requires_payment' => $booking->requiresPayment(),
                'next_state' => $booking->getNextState()
            ], 'Available actions retrieved successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Booking not found', 404);
        } catch (\Exception $e) {
            Log::error('Booking API actions error', [
                'user_id' => Auth::id(),
                'booking_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse('Failed to retrieve available actions', 500);
        }
    }

    /**
     * Success response helper
     */
    private function successResponse($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => [
                'timestamp' => now()->toISOString(),
                'version' => 'v1'
            ]
        ], $code);
    }

    /**
     * Error response helper
     */
    private function errorResponse(string $message, int $code = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'meta' => [
                'timestamp' => now()->toISOString(),
                'version' => 'v1'
            ]
        ], $code);
    }
}
