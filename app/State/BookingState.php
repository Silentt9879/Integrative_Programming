<?php

namespace App\State;

use App\Models\Booking;

abstract class BookingState
{
    protected Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Get available actions for current state
     */
    abstract public function getAvailableActions(): array;

    /**
     * Get state message for user
     */
    abstract public function getStateMessage(): string;

    /**
     * Get badge color for UI
     */
    abstract public function getBadgeColor(): string;

    /**
     * Check if payment is required
     */
    abstract public function requiresPayment(): bool;

    /**
     * Get next logical state
     */
    abstract public function getNextState(): ?string;

    /**
     * Handle transition to confirmed state
     */
    public function confirm(): bool
    {
        return false;
    }

    /**
     * Handle transition to active state
     */
    public function activate(): bool
    {
        return false;
    }

    /**
     * Handle transition to completed state
     */
    public function complete(array $data = []): bool
    {
        return false;
    }

    /**
     * Handle transition to cancelled state
     */
    public function cancel(string $reason = 'Cancelled by customer'): bool
    {
        return false;
    }

    /**
     * Check if transition to specific state is allowed
     */
    public function canTransitionTo(string $state): bool
    {
        $allowedTransitions = $this->getAllowedTransitions();
        return in_array($state, $allowedTransitions);
    }

    /**
     * Get allowed transitions from current state
     */
    abstract protected function getAllowedTransitions(): array;

    /**
     * Update booking status in database
     */
    protected function updateBookingStatus(string $status, array $additionalData = []): bool
    {
        $data = array_merge(['status' => $status], $additionalData);
        return $this->booking->update($data);
    }

    /**
     * Update vehicle status
     */
    protected function updateVehicleStatus(string $status): void
    {
        if ($this->booking->vehicle) {
            $this->booking->vehicle->update(['status' => $status]);
        }
    }
}
