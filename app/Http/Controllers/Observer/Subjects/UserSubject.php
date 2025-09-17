<?php
namespace App\Http\Controllers\Observer\Subjects;

use App\Http\Controllers\Observer\Events\UserRegisteredEvent;
use App\Http\Controllers\Observer\Events\UserLoginEvent;
use App\Models\User;

class UserSubject extends BaseSubject
{
    public function __construct()
    {
        parent::__construct('UserSubject');
    }

    public function notifyUserRegistered(User $user, array $additionalData = []): void
    {
        $event = new UserRegisteredEvent($user, $additionalData);
        $this->notify($event);
    }

    public function notifyUserLogin(User $user, string $ipAddress = '', array $additionalData = []): void
    {
        $event = new UserLoginEvent($user, $ipAddress, $additionalData);
        $this->notify($event);
    }
}