<?php

namespace App\State;

use App\Models\Booking;
use Carbon\Carbon;

class ActiveState extends BookingState
{
    public function getAvailableActions(): array
    {
        $actions = ['view'];

        // Can complete if return date has arrived
        if ($this->booking->return_datetime <= now()) {
            $actions[] = 'complete';
        }

        return $actions;
    }

    public function getStateMessage(): string
    {
        return 'Vehicle is currently rented. Return by ' .
               $this->booking->return_datetime->format('M d, Y h:i A') . '.';
    }

    public function getBadgeColor(): string
    {
        return 'primary';
    }

    public function requiresPayment(): bool
    {
        return false;
    }

    public function getNextState(): ?string
    {
        return 'completed';
    }

    protected function getAllowedTransitions(): array
    {
        return ['completed'];
    }

    public function complete(array $data = []): bool
    {
        if (!$this->canTransitionTo('completed')) {
            return false;
        }

        $updateData = [
            'status' => 'completed',
            'actual_return_datetime' => $data['actual_return_datetime'] ?? now(),
        ];

        // Cal late fees if returned late
        $actualReturn = $data['actual_return_datetime'] ?? now();
        if ($actualReturn > $this->booking->return_datetime && $this->booking->vehicle->rentalRate) {
            $hoursLate = $this->booking->return_datetime->diffInHours($actualReturn);
            $lateFee = $hoursLate * $this->booking->vehicle->rentalRate->late_fee_per_hour;
            $updateData['late_fees'] = $lateFee;
        }

        // damage charges
        if (isset($data['damage_charges'])) {
            $updateData['damage_charges'] = $data['damage_charges'];
        }

        // Cal final amount
        $finalAmount = $this->booking->total_amount +
                      ($updateData['late_fees'] ?? 0) +
                      ($updateData['damage_charges'] ?? 0);
        $updateData['final_amount'] = $finalAmount;

        $this->booking->update($updateData);
        $this->updateVehicleStatus('available');

        return true;
    }
}
