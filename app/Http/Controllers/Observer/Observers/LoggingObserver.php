<?php
namespace App\Http\Controllers\Observer\Observers;

use App\Http\Controllers\Observer\Contracts\ObserverInterface;
use App\Http\Controllers\Observer\Events\BaseEvent;
use Illuminate\Support\Facades\Log;

class LoggingObserver implements ObserverInterface
{
    public function getName(): string
    {
        return 'LoggingObserver';
    }

    public function update($eventData): void
    {
        try {
            if ($eventData instanceof BaseEvent) {
                $logData = [
                    'event_type' => $eventData->getEventType(),
                    'timestamp' => $eventData->getTimestamp()->toISOString(),
                    'metadata' => $eventData->getMetadata(),
                    'event_data' => $eventData->toArray()
                ];

                $message = "Event occurred: " . class_basename($eventData->getEventType());
                Log::info($message, $logData);
                
            } else {
                Log::warning("LoggingObserver received non-event data", [
                    'data_type' => gettype($eventData),
                    'data' => $eventData
                ]);
            }
        } catch (\Exception $e) {
            Log::error("LoggingObserver failed to log event: " . $e->getMessage());
        }
    }
}