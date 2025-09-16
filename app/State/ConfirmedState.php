<?php

namespace App\State;

use App\Models\Booking;
use Carbon\Carbon;

class ConfirmedState extends BookingState
{
    public function getAvailableActions(): array
    {
        $actions = ['view'];

        // Can cancel if pickup date is more than 24 hours away
        if ($this->booking->pickup_datetime->diffInHours(now()) > 24) {
            $actions[] = 'cancel';
        }

        // Can activate if pickup date has arrived
        if ($this->booking->pickup_datetime <= now()) {
            $actions[] = 'activate';
        }

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
        return false; // Payment already completed
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

        // Check if pickup time has arrived
        if ($this->booking->pickup_datetime > now()) {
            return false;
        }

        $this->updateBookingStatus('active');
        // Vehicle remains 'rented' - no status change needed

        return true;
    }

    public function cancel(string $reason = 'Cancelled by customer'): bool
    {
        if (!$this->canTransitionTo('cancelled')) {
            return false;
        }

        // Check if cancellation is still allowed (24 hours before pickup)
        if ($this->booking->pickup_datetime->diffInHours(now()) <= 24) {
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
