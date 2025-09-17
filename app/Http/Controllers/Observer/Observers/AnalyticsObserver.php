<?php
namespace App\Http\Controllers\Observer\Observers;

use App\Http\Controllers\Observer\Contracts\ObserverInterface;
use App\Http\Controllers\Observer\Events\UserRegisteredEvent;
use App\Http\Controllers\Observer\Events\UserLoginEvent;
use App\Http\Controllers\Observer\Events\BookingStatusChangedEvent;
use Illuminate\Support\Facades\Log;

class AnalyticsObserver implements ObserverInterface
{
    public function getName(): string
    {
        return 'AnalyticsObserver';
    }

    public function update($eventData): void
    {
        Log::info("AnalyticsObserver processing event: " . get_class($eventData));

        try {
            switch (true) {
                case $eventData instanceof UserRegisteredEvent:
                    $this->trackUserRegistration($eventData);
                    break;
                    
                case $eventData instanceof UserLoginEvent:
                    $this->trackUserLogin($eventData);
                    break;
                    
                case $eventData instanceof BookingStatusChangedEvent:
                    $this->trackBookingStatusChange($eventData);
                    break;
            }
        } catch (\Exception $e) {
            Log::error("AnalyticsObserver failed: " . $e->getMessage());
        }
    }

    private function trackUserRegistration(UserRegisteredEvent $event): void
    {
        $analyticsData = [
            'event_type' => 'user_registration',
            'user_id' => $event->getUser()->id,
            'timestamp' => $event->getTimestamp()->toISOString()
        ];

        Log::channel('single')->info('USER_REGISTRATION_TRACKED', $analyticsData);
    }

    private function trackUserLogin(UserLoginEvent $event): void
    {
        $analyticsData = [
            'event_type' => 'user_login',
            'user_id' => $event->getUser()->id,
            'timestamp' => $event->getTimestamp()->toISOString(),
            'ip_address' => $event->getIpAddress()
        ];

        Log::channel('single')->info('USER_LOGIN_TRACKED', $analyticsData);
    }

    private function trackBookingStatusChange(BookingStatusChangedEvent $event): void
    {
        $analyticsData = [
            'event_type' => 'booking_status_changed',
            'booking_id' => $event->getBooking()->id,
            'old_status' => $event->getOldStatus(),
            'new_status' => $event->getNewStatus(),
            'timestamp' => $event->getTimestamp()->toISOString()
        ];

        Log::channel('single')->info('BOOKING_STATUS_CHANGED_TRACKED', $analyticsData);
    }
}