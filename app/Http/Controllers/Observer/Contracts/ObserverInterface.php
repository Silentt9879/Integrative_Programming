<?php
namespace App\Http\Controllers\Observer\Contracts;

interface ObserverInterface
{
    public function update($eventData): void;
    public function getName(): string;
}