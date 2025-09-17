<?php
namespace App\Http\Controllers\Observer\Subjects;

use App\Http\Controllers\Observer\Events\BookingStatusChangedEvent;
use App\Models\Booking;

class BookingSubject extends BaseSubject
{
    public function __construct()
    {
        parent::__construct('BookingSubject');
    }

    public function notifyBookingStatusChanged(Booking $booking, string $oldStatus, string $newStatus, array $additionalData = []): void
    {
        $event = new BookingStatusChangedEvent($booking, $oldStatus, $newStatus, $additionalData);
        $this->notify($event);
    }
}