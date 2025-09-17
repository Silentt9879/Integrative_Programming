<?php
namespace App\Http\Controllers\Observer\Subjects;

use App\Http\Controllers\Observer\Contracts\ObserverInterface;
use App\Http\Controllers\Observer\Contracts\SubjectInterface;
use Illuminate\Support\Facades\Log;

abstract class BaseSubject implements SubjectInterface
{
    protected array $observers = [];
    protected string $subjectName;

    public function __construct(string $subjectName = '')
    {
        $this->subjectName = $subjectName ?: static::class;
    }

    public function attach(ObserverInterface $observer): void
    {
        $observerName = $observer->getName();
        
        foreach ($this->observers as $existingObserver) {
            if ($existingObserver->getName() === $observerName) {
                return;
            }
        }

        $this->observers[] = $observer;
        Log::info("Observer '{$observerName}' attached to subject '{$this->subjectName}'");
    }

    public function detach(ObserverInterface $observer): void
    {
        $observerName = $observer->getName();
        
        foreach ($this->observers as $key => $existingObserver) {
            if ($existingObserver->getName() === $observerName) {
                unset($this->observers[$key]);
                $this->observers = array_values($this->observers);
                Log::info("Observer '{$observerName}' detached from subject '{$this->subjectName}'");
                return;
            }
        }
    }

    public function notify($eventData): void
    {
        Log::info("Subject '{$this->subjectName}' notifying " . count($this->observers) . " observers");
        
        foreach ($this->observers as $observer) {
            try {
                $observer->update($eventData);
            } catch (\Exception $e) {
                Log::error("Observer '{$observer->getName()}' failed to process event: {$e->getMessage()}");
            }
        }
    }

    public function getObservers(): array
    {
        return $this->observers;
    }

    public function getSubjectName(): string
    {
        return $this->subjectName;
    }
}