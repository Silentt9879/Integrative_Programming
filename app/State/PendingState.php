<?php

namespace App\State;

use App\Models\Booking;

class PendingState extends BookingState
{
    public function getAvailableActions(): array
    {
        $actions = ['view', 'cancel'];

        if ($this->booking->payment_status === 'paid') {
            $actions[] = 'confirm';
        } else {
            $actions[] = 'pay';
        }

        return $actions;
    }

    public function getStateMessage(): string
    {
        return 'Booking is pending confirmation. Please confirm to secure your reservation.';
    }

    public function getBadgeColor(): string
    {
        return 'warning';
    }

    public function requiresPayment(): bool
    {
        return $this->booking->payment_status === 'pending';
    }

    public function getNextState(): ?string
    {
        return 'confirmed';
    }

    protected function getAllowedTransitions(): array
    {
        return ['confirmed', 'cancelled'];
    }

    public function confirm(): bool
    {
        if (!$this->canTransitionTo('confirmed')) {
            return false;
        }

        // confirm when payment is completed
        if ($this->booking->payment_status !== 'paid') {
            return false;
        }

        $this->updateBookingStatus('confirmed');
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
