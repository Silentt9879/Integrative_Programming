<?php
namespace App\Http\Controllers\Observer\Contracts;

interface SubjectInterface
{
    public function attach(ObserverInterface $observer): void;
    public function detach(ObserverInterface $observer): void;
    public function notify($eventData): void;
    public function getObservers(): array;
}