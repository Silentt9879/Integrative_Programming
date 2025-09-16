<?php

namespace App\State;

use App\Models\Booking;

class CompletedState extends BookingState
{
    public function getAvailableActions(): array
    {
        return ['view']; // can viewing completed bookings
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
        return false; // pay all
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
