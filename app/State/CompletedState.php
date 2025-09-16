<?php

namespace App\State;

use App\Models\Booking;

class CompletedState extends BookingState
{
    public function getAvailableActions(): array
    {
        return ['view']; // Only viewing is allowed for completed bookings
    }

    public function getStateMessage(): string
    {
        return 'Booking completed successfully. Thank you for choosing RentWheels!';
    }

    public function getBadgeColor(): string
    {
        return 'success';
    }

    public function requiresPayment(): bool
    {
        return false; // All payments should be settled
    }

    public function getNextState(): ?string
    {
        return null; // Terminal state
    }

    protected function getAllowedTransitions(): array
    {
        return []; // No transitions allowed from completed state
    }

    // No state transitions allowed from completed state
    // All transition methods return false by default from parent class
}
