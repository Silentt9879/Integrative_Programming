<?php
// app/Models/Customer.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name', 
        'email',
        'password',
        'phone',
        'address',
        'driver_license_number',
        'driver_license_expiry',
        'date_of_birth',
        'status'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'driver_license_expiry' => 'date',
        'date_of_birth' => 'date',
        'password' => 'hashed',
    ];

    // Relationships
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Accessor for full name
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Business Logic
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function hasValidLicense()
    {
        return $this->driver_license_expiry > now();
    }
}

