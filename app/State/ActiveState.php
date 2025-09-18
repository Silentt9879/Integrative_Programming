<?php

namespace App\State;

use App\Models\Booking;
use Carbon\Carbon;

class ActiveState extends BookingState
{
    public function getAvailableActions(): array
{
    $actions = ['view'];

    // Allow completion anytime for demo
    $actions[] = 'complete';

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

    // Add damage charges if provided
    if (isset($data['damage_charges']) && $data['damage_charges'] > 0) {
        $updateData['damage_charges'] = $data['damage_charges'];
    }

    // Add return notes if provided
    if (isset($data['return_notes'])) {
        $updateData['notes'] = $data['return_notes'];
    }

    // Calculate final amount (simplified for demo)
    $finalAmount = $this->booking->total_amount + ($updateData['damage_charges'] ?? 0);
    $updateData['final_amount'] = $finalAmount;

    // Update booking status to completed
    $this->booking->update($updateData);

    // Make vehicle available again
    $this->updateVehicleStatus('available');

    return true;
}
}
