<?php
namespace App\Http\Controllers\Observer\Events;

use App\Models\User;

class UserRegisteredEvent extends BaseEvent
{
    private User $user;
    private array $registrationData;

    public function __construct(User $user, array $registrationData = [])
    {
        $this->user = $user;
        $this->registrationData = $registrationData;
        
        parent::__construct([
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_name' => $user->name,
            'registration_data' => $registrationData
        ]);
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getRegistrationData(): array
    {
        return $this->registrationData;
    }
}