<?php
namespace App\Http\Controllers\Observer\Events;

use Carbon\Carbon;

abstract class BaseEvent
{
    protected Carbon $timestamp;
    protected string $eventType;
    protected array $metadata;

    public function __construct(array $metadata = [])
    {
        $this->timestamp = Carbon::now();
        $this->eventType = static::class;
        $this->metadata = $metadata;
    }

    public function getTimestamp(): Carbon
    {
        return $this->timestamp;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function toArray(): array
    {
        return [
            'timestamp' => $this->timestamp->toISOString(),
            'event_type' => $this->eventType,
            'metadata' => $this->metadata
        ];
    }
}