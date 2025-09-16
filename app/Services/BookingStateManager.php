<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Vehicle;

/**
 * Simple Booking State Manager implementing State Pattern
 * Manages booking state transitions and state-specific behaviors
 */
class BookingStateManager
{
    /**
     * Get available actions for a booking based on its current state
     * This is the core State Pattern implementation
     */
    public static function getAvailableActions(Booking $booking): array
    {
        switch ($booking->status) {
            case 'pending':
                return self::getPendingActions($booking);

            case 'confirmed':
                return self::getConfirmedActions($booking);

            case 'active':
                return self::getActiveActions($booking);

            case 'completed':
                return self::getCompletedActions($booking);

            case 'cancelled':
                return self::getCancelledActions($booking);

            default:
                return [];
        }
    }

    /**
     * Check if booking can transition to a specific state
     */
    public static function canTransitionTo(Booking $booking, string $newState): bool
    {
        $currentState = $booking->status;

        switch ($newState) {
            case 'confirmed':
                return in_array($currentState, ['pending']);

            case 'active':
                return in_array($currentState, ['confirmed']);

            case 'completed':
                return in_array($currentState, ['active']);

            case 'cancelled':
                return in_array($currentState, ['pending', 'confirmed']);

            default:
                return false;
        }
    }

    /**
     * Transition booking to new state with state-specific logic
     */
    public static function transitionTo(Booking $booking, string $newState, array $data = []): bool
    {
        if (!self::canTransitionTo($booking, $newState)) {
            return false;
        }

        // Execute state-specific transition logic
        switch ($newState) {
            case 'confirmed':
                return self::transitionToConfirmed($booking, $data);

            case 'active':
                return self::transitionToActive($booking, $data);

            case 'completed':
                return self::transitionToCompleted($booking, $data);

            case 'cancelled':
                return self::transitionToCancelled($booking, $data);

            default:
                return false;
        }
    }

    /**
     * Get state-specific badge color
     */
    public static function getStatusBadgeColor(string $status): string
    {
        switch ($status) {
            case 'pending':
                return 'warning';
            case 'confirmed':
                return 'info';
            case 'active':
                return 'primary';
            case 'completed':
                return 'success';
            case 'cancelled':
                return 'danger';
            case 'no_show':
                return 'secondary';
            default:
                return 'secondary';
        }
    }

    /**
     * Get payment status badge color
     */
    public static function getPaymentBadgeColor(string $paymentStatus): string
    {
        switch ($paymentStatus) {
            case 'pending':
                return 'warning';
            case 'partial':
                return 'info';
            case 'paid':
                return 'success';
            case 'refunded':
                return 'secondary';
            case 'cancelled':
                return 'danger';
            default:
                return 'secondary';
        }
    }

    /**
     * Get state-specific message for user
     */
    public static function getStateMessage(Booking $booking): string
    {
        switch ($booking->status) {
            case 'pending':
                return 'Booking is pending confirmation. Please confirm to secure your reservation.';

            case 'confirmed':
                return 'Booking confirmed! Vehicle will be ready for pickup on ' . $booking->pickup_datetime->format('M d, Y') . '.';

            case 'active':
                return 'Vehicle is currently rented. Return by ' . $booking->return_datetime->format('M d, Y h:i A') . '.';

            case 'completed':
                return 'Booking completed successfully. Thank you for choosing RentWheels!';

            case 'cancelled':
                return 'Booking has been cancelled.';

            default:
                return '';
        }
    }

    /**
     * Check if booking requires payment
     */
    public static function requiresPayment(Booking $booking): bool
    {
        return in_array($booking->status, ['pending', 'confirmed']) &&
               $booking->payment_status === 'pending';
    }

    /**
     * Get next logical state for booking
     */
    public static function getNextState(Booking $booking): ?string
    {
        switch ($booking->status) {
            case 'pending':
                return 'confirmed';
            case 'confirmed':
                return 'active';
            case 'active':
                return 'completed';
            default:
                return null;
        }
    }

    // PRIVATE METHODS - State-specific logic

    /**
     * Get available actions for pending bookings
     */
    private static function getPendingActions(Booking $booking): array
    {
        $actions = ['view', 'cancel'];

        if ($booking->payment_status === 'paid') {
            $actions[] = 'confirm';
        } else {
            $actions[] = 'pay';
        }

        return $actions;
    }

    /**
     * Get available actions for confirmed bookings
     */
    private static function getConfirmedActions(Booking $booking): array
    {
        $actions = ['view'];

        // Can cancel if pickup date is more than 24 hours away
        if ($booking->pickup_datetime->diffInHours(now()) > 24) {
            $actions[] = 'cancel';
        }

        // Can activate if pickup date has arrived
        if ($booking->pickup_datetime <= now()) {
            $actions[] = 'activate';
        }

        return $actions;
    }

    /**
     * Get available actions for active bookings
     */
    private static function getActiveActions(Booking $booking): array
    {
        $actions = ['view'];

        // Can complete if return date has arrived
        if ($booking->return_datetime <= now()) {
            $actions[] = 'complete';
        }

        return $actions;
    }

    /**
     * Get available actions for completed bookings
     */
    private static function getCompletedActions(Booking $booking): array
    {
        return ['view'];
    }

    /**
     * Get available actions for cancelled bookings
     */
    private static function getCancelledActions(Booking $booking): array
    {
        return ['view'];
    }

    /**
     * Transition to confirmed state
     */
    private static function transitionToConfirmed(Booking $booking, array $data = []): bool
    {
        $booking->update([
            'status' => 'confirmed',
            'payment_status' => $booking->payment_status === 'pending' ? 'paid' : $booking->payment_status
        ]);

        // Update vehicle status when booking is confirmed
        if ($booking->vehicle) {
            $booking->vehicle->update(['status' => 'rented']);
        }

        return true;
    }

    /**
     * Transition to active state
     */
    private static function transitionToActive(Booking $booking, array $data = []): bool
    {
        $booking->update([
            'status' => 'active'
        ]);

        // Vehicle is already marked as rented, no change needed

        return true;
    }

    /**
     * Transition to completed state
     */
    private static function transitionToCompleted(Booking $booking, array $data = []): bool
    {
        $updateData = [
            'status' => 'completed',
            'actual_return_datetime' => $data['actual_return_datetime'] ?? now(),
        ];

        // Add any additional charges if provided
        if (isset($data['damage_charges'])) {
            $updateData['damage_charges'] = $data['damage_charges'];
        }

        if (isset($data['late_fees'])) {
            $updateData['late_fees'] = $data['late_fees'];
        }

        if (isset($data['final_amount'])) {
            $updateData['final_amount'] = $data['final_amount'];
        }

        $booking->update($updateData);

        // Make vehicle available again
        if ($booking->vehicle) {
            $booking->vehicle->update(['status' => 'available']);
        }

        return true;
    }

    /**
     * Transition to cancelled state
     */
    private static function transitionToCancelled(Booking $booking, array $data = []): bool
    {
        $booking->update([
            'status' => 'cancelled',
            'payment_status' => 'cancelled',
            'cancellation_reason' => $data['reason'] ?? 'Cancelled by customer'
        ]);

        // Make vehicle available again when booking is cancelled
        if ($booking->vehicle) {
            $booking->vehicle->update(['status' => 'available']);
        }

        return true;
    }

    /**
     * Get all valid booking states
     */
    public static function getAllStates(): array
    {
        return ['pending', 'confirmed', 'active', 'completed', 'cancelled', 'no_show'];
    }

    /**
     * Check if state is valid
     */
    public static function isValidState(string $state): bool
    {
        return in_array($state, self::getAllStates());
    }

    /**
     * Get state workflow description
     */
    public static function getStateWorkflow(): array
    {
        return [
            'pending' => [
                'next' => ['confirmed', 'cancelled'],
                'description' => 'Booking created, waiting for confirmation'
            ],
            'confirmed' => [
                'next' => ['active', 'cancelled'],
                'description' => 'Booking confirmed, ready for pickup'
            ],
            'active' => [
                'next' => ['completed'],
                'description' => 'Vehicle picked up, rental in progress'
            ],
            'completed' => [
                'next' => [],
                'description' => 'Rental completed successfully'
            ],
            'cancelled' => [
                'next' => [],
                'description' => 'Booking cancelled'
            ]
        ];
    }
}
