<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_email', 
        'customer_phone',
        'booking_number',
        'user_id',
        'vehicle_id',
        'pickup_datetime',
        'return_datetime',
        'actual_return_datetime',
        'pickup_location',
        'return_location',
        'total_amount',
        'deposit_amount',
        'final_amount',
        'damage_charges',
        'late_fees',
        'status',
        'payment_status',
        'special_requests',
        'notes',
        'cancellation_reason',
        'pickup_inspection',
        'return_inspection'
    ];

    protected $casts = [
        'pickup_datetime' => 'datetime',
        'return_datetime' => 'datetime', 
        'actual_return_datetime' => 'datetime',
        'total_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'damage_charges' => 'decimal:2',
        'late_fees' => 'decimal:2',
        'pickup_inspection' => 'array',
        'return_inspection' => 'array'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // Generate unique booking number
    public static function generateBookingNumber()
    {
        do {
            $number = 'RW' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('booking_number', $number)->exists());
        
        return $number;
    }

    // Calculate rental days
    public function getRentalDaysAttribute()
    {
        return $this->pickup_datetime->diffInDays($this->return_datetime) ?: 1;
    }

    // Calculate total cost
    public function calculateTotalCost()
    {
        if (!$this->vehicle || !$this->vehicle->rentalRate) {
            return 0;
        }

        $days = $this->rental_days;
        return $this->vehicle->rentalRate->calculateRate($days);
    }

    // Status check methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isConfirmed()
    {
        return $this->status === 'confirmed';
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    // Scopes for easy querying
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['confirmed', 'active']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForVehicle($query, $vehicleId)
    {
        return $query->where('vehicle_id', $vehicleId);
    }

    // Check if booking overlaps with given dates
    public function overlapsWithDates($pickupDate, $returnDate)
    {
        return $this->pickup_datetime <= $returnDate && 
               $this->return_datetime >= $pickupDate;
    }

    // Get status badge color
    public function getStatusBadgeColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'confirmed' => 'info', 
            'active' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
            'no_show' => 'secondary',
            default => 'secondary'
        };
    }

    // Get payment status badge color  
    public function getPaymentBadgeColorAttribute()
    {
        return match($this->payment_status) {
            'pending' => 'warning',
            'partial' => 'info',
            'paid' => 'success', 
            'refunded' => 'secondary',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }
}