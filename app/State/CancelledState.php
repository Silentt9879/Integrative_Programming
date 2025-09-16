<?php

namespace App\State;

use App\Models\Booking;

class CancelledState extends BookingState
{
    public function getAvailableActions(): array
    {
        return ['view']; // Only viewing is allowed for cancelled bookings
    }

    public function getStateMessage(): string
    {
        $reason = $this->booking->cancellation_reason ?? 'Booking has been cancelled';
        return 'Booking has been cancelled. Reason: ' . $reason;
    }

    public function getBadgeColor(): string
    {
        return 'danger';
    }

    public function requiresPayment(): bool
    {
        return false; // No payment needed for cancelled bookings
    }

    public function getNextState(): ?string
    {
        return null; // Terminal state
    }

    protected function getAllowedTransitions(): array
    {
        return []; // No transitions allowed from cancelled state
    }

    // No state transitions allowed from cancelled state
    // All transition methods return false by default from parent class
}
