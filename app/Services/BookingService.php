<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Vehicle;
use App\State\StateFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingService
{
    /**
     * Create a new booking using State Pattern
     */
    public function createBooking(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            // Validate vehicle availability
            $vehicle = Vehicle::with('rentalRate')->findOrFail($data['vehicle_id']);

            if ($vehicle->status !== 'available') {
                throw new \Exception('Vehicle is not available for booking');
            }

            // Combine date and time
            $pickupDateTime = Carbon::createFromFormat(
                'Y-m-d H:i',
                $data['pickup_date'] . ' ' . $data['pickup_time']
            );

            $returnDateTime = Carbon::createFromFormat(
                'Y-m-d H:i',
                $data['return_date'] . ' ' . $data['return_time']
            );

            // Validate booking time
            $this->validateBookingTime($pickupDateTime, $returnDateTime);

            // Check availability for dates
            if (!$this->isVehicleAvailable($vehicle->id, $pickupDateTime, $returnDateTime)) {
                throw new \Exception('Vehicle is not available for the selected dates');
            }

            // Calculate costs
            $days = $pickupDateTime->diffInDays($returnDateTime) ?: 1;
            $totalAmount = $vehicle->rentalRate->calculateRate($days);
            $depositAmount = $totalAmount * 0.3;

            // Create booking with initial pending state
            $booking = Booking::create([
                'customer_name' => $data['customer_name'] ?? Auth::user()->name,
                'customer_email' => $data['customer_email'] ?? Auth::user()->email,
                'customer_phone' => $data['customer_phone'],
                'booking_number' => Booking::generateBookingNumber(),
                'user_id' => Auth::id(),
                'vehicle_id' => $vehicle->id,
                'pickup_datetime' => $pickupDateTime,
                'return_datetime' => $returnDateTime,
                'pickup_location' => $data['pickup_location'],
                'return_location' => $data['return_location'],
                'total_amount' => $totalAmount,
                'deposit_amount' => $depositAmount,
                'status' => 'pending',
                'payment_status' => 'pending',
                'special_requests' => $data['special_requests'] ?? null
            ]);

            // Reserve vehicle temporarily
            $vehicle->update(['status' => 'rented']);

            // Clear vehicle cache
            $this->clearVehicleCache();

            // Log booking creation
            Log::info('Booking created via State Pattern', [
                'booking_id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'user_id' => Auth::id(),
                'vehicle_id' => $vehicle->id,
                'state' => $booking->status
            ]);

            return $booking;
        });
    }

    /**
     * Update booking status using State Pattern
     */
    public function updateBookingStatus(Booking $booking, string $newStatus, array $data = []): bool
    {
        $currentState = $booking->getState();

        if (!$currentState->canTransitionTo($newStatus)) {
            throw new \Exception("Cannot transition from {$booking->status} to {$newStatus}");
        }

        $oldStatus = $booking->status;
        $result = $booking->transitionTo($newStatus, $data);

        if ($result) {
            Log::info('Booking status updated via State Pattern', [
                'booking_id' => $booking->id,
                'from_state' => $oldStatus,
                'to_state' => $newStatus,
                'user_id' => Auth::id(),
                'additional_data' => $data
            ]);

            // Clear cache if status affects availability
            if (in_array($newStatus, ['cancelled', 'completed'])) {
                $this->clearVehicleCache();
            }
        }

        return $result;
    }

    /**
     * Cancel booking using State Pattern
     */
    public function cancelBooking(Booking $booking, string $reason = 'Cancelled by customer'): bool
    {
        if (!$booking->canPerformAction('cancel')) {
            throw new \Exception('This booking cannot be cancelled in its current state');
        }

        $result = $booking->cancel($reason);

        if ($result) {
            $this->clearVehicleCache();
        }

        return $result;
    }

    /**
     * Confirm booking using State Pattern
     */
    public function confirmBooking(Booking $booking): bool
    {
        if (!$booking->canPerformAction('confirm')) {
            throw new \Exception('This booking cannot be confirmed in its current state');
        }

        if ($booking->requiresPayment()) {
            throw new \Exception('Payment must be completed before confirmation');
        }

        return $booking->confirm();
    }

    /**
     * Activate booking (mark as picked up) using State Pattern
     */
    public function activateBooking(Booking $booking): bool
    {
        if (!$booking->canPerformAction('activate')) {
            throw new \Exception('This booking cannot be activated in its current state');
        }

        return $booking->activate();
    }

    /**
     * Complete booking using State Pattern
     */
    public function completeBooking(Booking $booking, array $data = []): bool
    {
        if (!$booking->canPerformAction('complete')) {
            throw new \Exception('This booking cannot be completed in its current state');
        }

        $result = $booking->complete($data);

        if ($result) {
            $this->clearVehicleCache();
        }

        return $result;
    }

    /**
     * Get booking with state information
     */
    public function getBookingWithState(int $bookingId, ?int $userId = null): Booking
    {
        $query = Booking::with(['vehicle', 'vehicle.rentalRate'])
                       ->where('id', $bookingId);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $booking = $query->firstOrFail();

        // Eager load state information
        $booking->available_actions = $booking->getAvailableActions();
        $booking->state_message = $booking->getStateMessage();
        $booking->requires_payment = $booking->requiresPayment();
        $booking->next_state = $booking->getNextState();

        return $booking;
    }

    /**
     * Get user bookings with filtering and state information
     */
    public function getUserBookings(int $userId, array $filters = [])
    {
        $query = Booking::with(['vehicle', 'vehicle.rentalRate'])
                       ->where('user_id', $userId);

        // Apply filters
        if (isset($filters['status'])) {
            if (is_array($filters['status'])) {
                $query->whereIn('status', $filters['status']);
            } else {
                $query->where('status', $filters['status']);
            }
        }

        if (isset($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (isset($filters['from_date'])) {
            $query->where('pickup_datetime', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->where('pickup_datetime', '<=', $filters['to_date']);
        }

        $bookings = $query->orderBy('created_at', 'desc')->get();

        // Add state information to each booking
        $bookings->each(function ($booking) {
            $booking->available_actions = $booking->getAvailableActions();
            $booking->state_message = $booking->getStateMessage();
        });

        return $bookings;
    }

    /**
     * Check if vehicle is available for booking
     */
    public function isVehicleAvailable(int $vehicleId, Carbon $pickupDate, Carbon $returnDate): bool
    {
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

    /**
     * Get booking statistics using State Pattern
     */
    public function getBookingStatistics(?int $userId = null): array
    {
        $query = Booking::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return [
            'total' => $query->count(),
            'by_status' => [
                'pending' => $query->clone()->where('status', 'pending')->count(),
                'confirmed' => $query->clone()->where('status', 'confirmed')->count(),
                'active' => $query->clone()->where('status', 'active')->count(),
                'completed' => $query->clone()->where('status', 'completed')->count(),
                'cancelled' => $query->clone()->where('status', 'cancelled')->count(),
            ],
            'by_payment_status' => [
                'pending' => $query->clone()->where('payment_status', 'pending')->count(),
                'paid' => $query->clone()->where('payment_status', 'paid')->count(),
                'partial' => $query->clone()->where('payment_status', 'partial')->count(),
                'cancelled' => $query->clone()->where('payment_status', 'cancelled')->count(),
            ],
            'revenue' => [
                'total' => $query->clone()->where('status', 'completed')->sum('final_amount'),
                'pending' => $query->clone()->where('payment_status', 'pending')->sum('total_amount'),
            ]
        ];
    }

    /**
     * Validate booking time constraints
     */
    private function validateBookingTime(Carbon $pickupDateTime, Carbon $returnDateTime): void
    {
        if ($pickupDateTime <= now()) {
            throw new \Exception('Pickup time cannot be in the past');
        }

        if ($pickupDateTime <= now()->addHour()) {
            throw new \Exception('Booking must be made at least 1 hour in advance');
        }

        if ($returnDateTime <= $pickupDateTime) {
            throw new \Exception('Return time must be after pickup time');
        }
    }

    /**
     * Clear vehicle cache when booking status affects availability
     */
    private function clearVehicleCache(): void
    {
        $commonFilters = ['', 'type=Economy', 'type=Luxury', 'type=Sedan', 'type=SUV', 'type=Van', 'type=Truck'];

        foreach ($commonFilters as $filter) {
            $filterArray = [];
            if (!empty($filter)) {
                parse_str($filter, $filterArray);
            }
            $key = 'vehicles_' . md5(serialize($filterArray));
            Cache::forget($key);
        }
    }
}
