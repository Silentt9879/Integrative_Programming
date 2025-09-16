<?php

namespace App\State;

use App\Models\Booking;

class CancelledState extends BookingState
{
    public function getAvailableActions(): array
    {
        return ['view']; // if cancel only can view
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

    //if cancel no need to pay
    public function requiresPayment(): bool
    {
        return false;
    }

    public function getNextState(): ?string
    {
        return null;
    }

    protected function getAllowedTransitions(): array
    {
        return [];
    }

}
