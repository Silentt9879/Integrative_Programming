<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\BookingStateManager; // NEW: Import the State Manager
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

    // Relationships - UNCHANGED
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // Generate unique booking number - UNCHANGED
    public static function generateBookingNumber()
    {
        do {
            $number = 'RW' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('booking_number', $number)->exists());

        return $number;
    }

    // Calculate rental days - UNCHANGED
    public function getRentalDaysAttribute()
    {
        return $this->pickup_datetime->diffInDays($this->return_datetime) ?: 1;
    }

    // Calculate total cost - UNCHANGED
    public function calculateTotalCost()
    {
        if (!$this->vehicle || !$this->vehicle->rentalRate) {
            return 0;
        }

        $days = $this->rental_days;
        return $this->vehicle->rentalRate->calculateRate($days);
    }

    // MODIFIED: Status check methods now use State Pattern
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

    // NEW: State Pattern Methods

    /**
     * Get available actions for this booking based on current state
     * Uses State Pattern via BookingStateManager
     */
    public function getAvailableActions(): array
    {
        return BookingStateManager::getAvailableActions($this);
    }

    /**
     * Check if booking can transition to specific state
     * Uses State Pattern for state validation
     */
    public function canTransitionTo(string $newState): bool
    {
        return BookingStateManager::canTransitionTo($this, $newState);
    }

    /**
     * Transition to new state using State Pattern
     * Replaces direct status updates with state-managed transitions
     */
    public function transitionTo(string $newState, array $data = []): bool
    {
        return BookingStateManager::transitionTo($this, $newState, $data);
    }

    /**
     * Get state-specific message for user
     */
    public function getStateMessage(): string
    {
        return BookingStateManager::getStateMessage($this);
    }

    /**
     * Check if booking requires payment based on state
     */
    public function requiresPayment(): bool
    {
        return BookingStateManager::requiresPayment($this);
    }

    /**
     * Get next logical state for this booking
     */
    public function getNextState(): ?string
    {
        return BookingStateManager::getNextState($this);
    }

    // MODIFIED: Badge color methods now use State Pattern
    public function getStatusBadgeColorAttribute()
    {
        return BookingStateManager::getStatusBadgeColor($this->status);
    }

    public function getPaymentBadgeColorAttribute()
    {
        return BookingStateManager::getPaymentBadgeColor($this->payment_status);
    }

    // NEW: Convenience methods for common state transitions

    /**
     * Confirm this booking using State Pattern
     */
    public function confirm(): bool
    {
        return $this->transitionTo('confirmed');
    }

    /**
     * Cancel this booking using State Pattern
     */
    public function cancel(string $reason = 'Cancelled by customer'): bool
    {
        return $this->transitionTo('cancelled', ['reason' => $reason]);
    }

    /**
     * Activate this booking (mark as picked up) using State Pattern
     */
    public function activate(): bool
    {
        return $this->transitionTo('active');
    }

    /**
     * Complete this booking using State Pattern
     */
    public function complete(array $data = []): bool
    {
        return $this->transitionTo('completed', $data);
    }

    // Scopes for easy querying - UNCHANGED but can now leverage state pattern
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

    // Check if booking overlaps with given dates - UNCHANGED
    public function overlapsWithDates($pickupDate, $returnDate)
    {
        return $this->pickup_datetime <= $returnDate &&
               $this->return_datetime >= $pickupDate;
    }

    // NEW: State Pattern Integration Events

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

                // Validate state transition using State Pattern
                if (!BookingStateManager::canTransitionTo(
                    new static(['status' => $oldStatus]),
                    $newStatus
                )) {
                    throw new \InvalidArgumentException(
                        "Invalid state transition from {$oldStatus} to {$newStatus}"
                    );
                }
            }
        });
    }

    // NEW: State-based query methods

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

    // NEW: State Pattern utility methods

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
        return BookingStateManager::getStateWorkflow();
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
