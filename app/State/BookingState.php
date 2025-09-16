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

   //check current state
    abstract public function getAvailableActions(): array;

    //state message for user
    abstract public function getStateMessage(): string;

   //state color
    abstract public function getBadgeColor(): string;

    // Check payment is required
    abstract public function requiresPayment(): bool;

    //Get next logical state
    abstract public function getNextState(): ?string;

    // transition confirmed
    public function confirm(): bool
    {
        return false;
    }

    // transition active
    public function activate(): bool
    {
        return false;
    }

    //  transition completed
    public function complete(array $data = []): bool
    {
        return false;
    }

    // transition cancelled
    public function cancel(string $reason = 'Cancelled by customer'): bool
    {
        return false;
    }

    //Check transition to specific state is allowed
    public function canTransitionTo(string $state): bool
    {
        $allowedTransitions = $this->getAllowedTransitions();
        return in_array($state, $allowedTransitions);
    }

   //transitions from current state
    abstract protected function getAllowedTransitions(): array;

    //Update booking status in database
    protected function updateBookingStatus(string $status, array $additionalData = []): bool
    {
        $data = array_merge(['status' => $status], $additionalData);
        return $this->booking->update($data);
    }

    //update vehicle state
    protected function updateVehicleStatus(string $status): void
    {
        if ($this->booking->vehicle) {
            $this->booking->vehicle->update(['status' => $status]);
        }
    }
}
