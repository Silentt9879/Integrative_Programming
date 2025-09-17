<?php
namespace App\Http\Controllers\Observer\Observers;

use App\Http\Controllers\Observer\Contracts\ObserverInterface;
use App\Http\Controllers\Observer\Events\UserRegisteredEvent;
use App\Http\Controllers\Observer\Events\BookingStatusChangedEvent;
use Illuminate\Support\Facades\Log;

class EmailNotificationObserver implements ObserverInterface
{
    public function getName(): string
    {
        return 'EmailNotificationObserver';
    }

    public function update($eventData): void
    {
        Log::info("EmailNotificationObserver processing event: " . get_class($eventData));

        try {
            switch (true) {
                case $eventData instanceof UserRegisteredEvent:
                    $this->handleUserRegistered($eventData);
                    break;
                    
                case $eventData instanceof BookingStatusChangedEvent:
                    $this->handleBookingStatusChanged($eventData);
                    break;
                    
                default:
                    Log::info("EmailNotificationObserver: Unhandled event type " . get_class($eventData));
            }
        } catch (\Exception $e) {
            Log::error("EmailNotificationObserver failed: " . $e->getMessage());
        }
    }

    private function handleUserRegistered(UserRegisteredEvent $event): void
    {
        $user = $event->getUser();
        
        Log::info("Sending welcome email to {$user->email}");
        Log::info("Welcome email sent to {$user->email}", [
            'user_name' => $user->name,
            'registration_date' => $event->getTimestamp()->format('M d, Y')
        ]);
    }

    private function handleBookingStatusChanged(BookingStatusChangedEvent $event): void
    {
        $booking = $event->getBooking();
        $booking->load(['user', 'vehicle']);
        
        Log::info("Sending booking status change email to {$booking->user->email}");
        Log::info("Booking status change email sent", [
            'booking_id' => $booking->id,
            'user_name' => $booking->user->name,
            'old_status' => $event->getOldStatus(),
            'new_status' => $event->getNewStatus()
        ]);
    }
}