<?php

namespace App\State;

use App\Models\Booking;
use Carbon\Carbon;

class ConfirmedState extends BookingState
{
    public function getAvailableActions(): array
{
    $actions = ['view'];
    $actions[] = 'cancel';
    $actions[] = 'activate';

    return $actions;
}

    public function getStateMessage(): string
    {
        return 'Booking confirmed! Vehicle will be ready for pickup on ' .
            $this->booking->pickup_datetime->format('M d, Y') . '.';
    }

    public function getBadgeColor(): string
    {
        return 'info';
    }

    public function requiresPayment(): bool
    {
        return false; // Payment completed
    }

    public function getNextState(): ?string
    {
        return 'active';
    }

    protected function getAllowedTransitions(): array
    {
        return ['active', 'cancelled'];
    }

    public function activate(): bool
    {
        if (!$this->canTransitionTo('active')) {
            return false;
        }

        // Admin can activate anytime (no time restriction)
        $this->updateBookingStatus('active', [
            'pickup_confirmed_at' => now()
        ]);

        // Ensure vehicle status is properly set
        $this->updateVehicleStatus('rented');

        return true;
    }

    public function cancel(string $reason = 'Cancelled by customer'): bool
    {
        if (!$this->canTransitionTo('cancelled')) {
            return false;
        }

        $this->updateBookingStatus('cancelled', [
            'payment_status' => 'cancelled',
            'cancellation_reason' => $reason
        ]);

        $this->updateVehicleStatus('available');

        return true;
    }
}
