<?php

namespace App\State;

use App\Models\Booking;
use InvalidArgumentException;

class StateFactory
{
   //create booking
    public static function create(Booking $booking): BookingState
    {
        switch ($booking->status) {
            case 'pending':
                return new PendingState($booking);

            case 'confirmed':
                return new ConfirmedState($booking);

            case 'active':
                return new ActiveState($booking);

            case 'completed':
                return new CompletedState($booking);

            case 'cancelled':
                return new CancelledState($booking);

            default:
                throw new InvalidArgumentException("Unknown booking status: {$booking->status}");
        }
    }

    //Get all available states
    public static function getAllStates(): array
    {
        return [
            'pending',
            'confirmed',
            'active',
            'completed',
            'cancelled'
        ];
    }

    //state is valid?
    public static function isValidState(string $state): bool
    {
        return in_array($state, self::getAllStates());
    }

   //state workflow descriptions
    public static function getStateWorkflow(): array
    {
        return [
            'pending' => [
                'class' => PendingState::class,
                'next' => ['confirmed', 'cancelled'],
                'description' => 'Booking created, waiting for confirmation'
            ],
            'confirmed' => [
                'class' => ConfirmedState::class,
                'next' => ['active', 'cancelled'],
                'description' => 'Booking confirmed, ready for pickup'
            ],
            'active' => [
                'class' => ActiveState::class,
                'next' => ['completed'],
                'description' => 'Vehicle picked up, rental in progress'
            ],
            'completed' => [
                'class' => CompletedState::class,
                'next' => [],
                'description' => 'Rental completed successfully'
            ],
            'cancelled' => [
                'class' => CancelledState::class,
                'next' => [],
                'description' => 'Booking cancelled'
            ]
        ];
    }

    //status color
    public static function getStatusBadgeColor(string $status): string
    {
        $colors = [
            'pending' => 'warning',
            'confirmed' => 'info',
            'active' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger'
        ];

        return $colors[$status] ?? 'secondary';
    }

//color payment status
    public static function getPaymentBadgeColor(string $paymentStatus): string
    {
        $colors = [
            'pending' => 'warning',
            'partial' => 'info',
            'paid' => 'success',
            'refunded' => 'secondary',
            'cancelled' => 'danger'
        ];

        return $colors[$paymentStatus] ?? 'secondary';
    }
}
