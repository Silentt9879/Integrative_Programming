<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\State\StateFactory;
use App\State\BookingState;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_email',
        'customer_phone',
        'booking_number',
        'user_id',
        'vehicle_id',
        'pickup_datetime',
        'return_datetime',
        'actual_return_datetime',
        'pickup_location',
        'return_location',
        'total_amount',
        'deposit_amount',
        'final_amount',
        'damage_charges',
        'late_fees',
        'status',
        'payment_status',
        'special_requests',
        'notes',
        'cancellation_reason',
        'pickup_inspection',
        'return_inspection'
    ];

    protected $casts = [
        'pickup_datetime' => 'datetime',
        'return_datetime' => 'datetime',
        'actual_return_datetime' => 'datetime',
        'total_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'damage_charges' => 'decimal:2',
        'late_fees' => 'decimal:2',
        'pickup_inspection' => 'array',
        'return_inspection' => 'array'
    ];

    /**
     * Check if booking has pending additional charges
     */
    public function hasPendingAdditionalCharges(): bool
    {
        return $this->payment_status === 'additional_charges_pending';
    }

    /**
     * Get pending additional charges amount
     */
    public function getPendingAdditionalCharges()
    {
        return \App\Models\Payment::where('booking_id', $this->id)
                ->where('payment_type', 'additional_charges')
                ->where('status', 'pending')
                ->first();
    }

    /**
     * Cache for state object
     */
    private ?BookingState $stateObject = null;

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // Generate unique booking number
    public static function generateBookingNumber()
    {
        do {
            $number = 'RW' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('booking_number', $number)->exists());

        return $number;
    }

    // Calculate rental days
    public function getRentalDaysAttribute()
    {
        return $this->pickup_datetime->diffInDays($this->return_datetime) ?: 1;
    }

    // Calculate total cost
    public function calculateTotalCost()
    {
        if (!$this->vehicle || !$this->vehicle->rentalRate) {
            return 0;
        }

        $days = $this->rental_days;
        return $this->vehicle->rentalRate->calculateRate($days);
    }

    // Status check methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isConfirmed()
    {
        return $this->status === 'confirmed';
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    /**
     * Get state object - uses State Pattern
     */
    public function getState(): BookingState
    {
        // Create new state object if status has changed or not cached
        if (!$this->stateObject || $this->isDirty('status')) {
            $this->stateObject = StateFactory::create($this);
        }

        return $this->stateObject;
    }

    /**
     * Get available actions for this booking based on current state
     */
    public function getAvailableActions(): array
    {
        return $this->getState()->getAvailableActions();
    }

    /**
     * Check if booking can transition to specific state
     */
    public function canTransitionTo(string $newState): bool
    {
        return $this->getState()->canTransitionTo($newState);
    }

    /**
     * Transition to new state using State Pattern
     */
    public function transitionTo(string $newState, array $data = []): bool
    {
        if (!$this->canTransitionTo($newState)) {
            return false;
        }

        // Clear state cache to force recreation after transition
        $this->stateObject = null;

        // Call the appropriate transition method on state object
        switch ($newState) {
            case 'confirmed':
                return $this->confirm();
            case 'active':
                return $this->activate();
            case 'completed':
                return $this->complete($data);
            case 'cancelled':
                return $this->cancel($data['reason'] ?? 'Cancelled by customer');
            default:
                return false;
        }
    }

    /**
     * Get state-specific message for user
     */
    public function getStateMessage(): string
    {
        return $this->getState()->getStateMessage();
    }

    /**
     * Check if booking requires payment based on state
     */
    public function requiresPayment(): bool
    {
        return $this->getState()->requiresPayment();
    }

    /**
     * Get next logical state for this booking
     */
    public function getNextState(): ?string
    {
        return $this->getState()->getNextState();
    }

    /**
     * Get badge colors using StateFactory
     */
    public function getStatusBadgeColorAttribute()
    {
        return StateFactory::getStatusBadgeColor($this->status);
    }

    public function getPaymentBadgeColorAttribute()
    {
        return StateFactory::getPaymentBadgeColor($this->payment_status);
    }

    /**
     * Convenience methods for common state transitions
     */

    /**
     * Confirm this booking
     */
    public function confirm(): bool
    {
        $result = $this->getState()->confirm();
        if ($result) {
            $this->refresh(); // Refresh model to get updated status
            $this->stateObject = null; // Clear cache
        }
        return $result;
    }

    /**
     * Cancel this booking
     */
    public function cancel(string $reason = 'Cancelled by customer'): bool
    {
        $result = $this->getState()->cancel($reason);
        if ($result) {
            $this->refresh();
            $this->stateObject = null;
        }
        return $result;
    }

    /**
     * Activate this booking (mark as picked up)
     */
    public function activate(): bool
    {
        $result = $this->getState()->activate();
        if ($result) {
            $this->refresh();
            $this->stateObject = null;
        }
        return $result;
    }

    /**
     * Complete this booking
     */
    public function complete(array $data = []): bool
    {
        $result = $this->getState()->complete($data);
        if ($result) {
            $this->refresh();
            $this->stateObject = null;
        }
        return $result;
    }

    // Scopes for easy querying
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['confirmed', 'active']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForVehicle($query, $vehicleId)
    {
        return $query->where('vehicle_id', $vehicleId);
    }

    // Check if booking overlaps with given dates
    public function overlapsWithDates($pickupDate, $returnDate)
    {
        return $this->pickup_datetime <= $returnDate &&
               $this->return_datetime >= $pickupDate;
    }

    /**
     * Boot method to add model events for state transitions
     */
    protected static function boot()
    {
        parent::boot();

        // Listen for status changes and validate state transitions
        static::updating(function ($booking) {
            if ($booking->isDirty('status')) {
                $oldStatus = $booking->getOriginal('status');
                $newStatus = $booking->status;

                // Create temporary booking with old status to check transition
                $tempBooking = new static(['status' => $oldStatus]);
                $tempState = StateFactory::create($tempBooking);

                if (!$tempState->canTransitionTo($newStatus)) {
                    throw new \InvalidArgumentException(
                        "Invalid state transition from {$oldStatus} to {$newStatus}"
                    );
                }
            }
        });
    }

    // State-based query methods

    /**
     * Get bookings that can be confirmed
     */
    public function scopeCanBeConfirmed($query)
    {
        return $query->where('status', 'pending')
                    ->where('payment_status', 'paid');
    }

    /**
     * Get bookings that can be cancelled
     */
    public function scopeCanBeCancelled($query)
    {
        return $query->whereIn('status', ['pending', 'confirmed']);
    }

    /**
     * Get bookings that can be activated
     */
    public function scopeCanBeActivated($query)
    {
        return $query->where('status', 'confirmed')
                    ->where('pickup_datetime', '<=', now());
    }

    /**
     * Get bookings that can be completed
     */
    public function scopeCanBeCompleted($query)
    {
        return $query->where('status', 'active')
                    ->where('return_datetime', '<=', now());
    }

    // State Pattern utility methods

    /**
     * Get all possible actions for any booking state
     */
    public static function getAllPossibleActions(): array
    {
        return ['view', 'confirm', 'cancel', 'activate', 'complete', 'pay'];
    }

    /**
     * Check if specific action is available for this booking
     */
    public function canPerformAction(string $action): bool
    {
        return in_array($action, $this->getAvailableActions());
    }

    /**
     * Get state workflow description
     */
    public static function getStateWorkflow(): array
    {
        return StateFactory::getStateWorkflow();
    }

    /**
     * Check if booking is in a terminal state (cannot transition further)
     */
    public function isInTerminalState(): bool
    {
        return in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Get human-readable status description
     */
    public function getStatusDescription(): string
    {
        $descriptions = [
            'pending' => 'Awaiting Confirmation',
            'confirmed' => 'Confirmed & Ready',
            'active' => 'In Progress',
            'completed' => 'Successfully Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'Customer No-Show'
        ];

        return $descriptions[$this->status] ?? ucfirst($this->status);
    }
}
