<?php
namespace App\Http\Controllers\Observer\Events;

use App\Models\Booking;

class BookingStatusChangedEvent extends BaseEvent
{
    private Booking $booking;
    private string $oldStatus;
    private string $newStatus;
    private array $changeData;

    public function __construct(Booking $booking, string $oldStatus, string $newStatus, array $changeData = [])
    {
        $this->booking = $booking;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->changeData = $changeData;
        
        parent::__construct([
            'booking_id' => $booking->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'change_data' => $changeData
        ]);
    }

    public function getBooking(): Booking
    {
        return $this->booking;
    }

    public function getOldStatus(): string
    {
        return $this->oldStatus;
    }

    public function getNewStatus(): string
    {
        return $this->newStatus;
    }

    public function getChangeData(): array
    {
        return $this->changeData;
    }
}