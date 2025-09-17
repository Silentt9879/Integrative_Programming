<?php
namespace App\Http\Controllers\Observer\Events;

use App\Models\User;

class UserLoginEvent extends BaseEvent
{
    private User $user;
    private string $ipAddress;
    private array $loginData;

    public function __construct(User $user, string $ipAddress = '', array $loginData = [])
    {
        $this->user = $user;
        $this->ipAddress = $ipAddress;
        $this->loginData = $loginData;
        
        parent::__construct([
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip_address' => $ipAddress,
            'login_data' => $loginData
        ]);
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function getLoginData(): array
    {
        return $this->loginData;
    }
}