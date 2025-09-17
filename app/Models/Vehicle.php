<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_plate',
        'make',
        'model',
        'year',
        'color',
        'type',
        'seating_capacity',
        'fuel_type',
        'current_mileage',
        'status',
        'description',
        'image_url'
    ];

    protected $casts = [
        'year' => 'integer',
        'seating_capacity' => 'integer',
        'current_mileage' => 'decimal:2'
    ];

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function setImageUrlAttribute($value)
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid image URL format');
        }

        if ($value && parse_url($value, PHP_URL_SCHEME) !== 'https') {
            throw new \InvalidArgumentException('Only HTTPS image URLs are allowed');
        }

        $this->attributes['image_url'] = $value;
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function rentalRate()
    {
        return $this->hasOne(RentalRate::class);
    }


    public function isAvailable()
    {
        return $this->status === 'available';
    }

    public function getDailyRateAttribute()
    {
        return $this->rentalRate ? $this->rentalRate->daily_rate : 0;
    }


    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeAvailableForPeriod($query, $pickupDate, $returnDate)
    {
        return $query->where('status', 'available')
            ->whereDoesntHave('bookings', function ($q) use ($pickupDate, $returnDate) {
                $q->whereNotIn('status', ['cancelled', 'completed'])
                    ->where('pickup_datetime', '<=', $returnDate)
                    ->where('return_datetime', '>=', $pickupDate);
            });
    }

    public static function validationRules()
    {
        return [
            'license_plate' => 'required|string|max:20|unique:vehicles,license_plate',
            'make' => 'required|string|max:50',
            'model' => 'required|string|max:50',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'required|string|max:30',
            'type' => 'required|in:Sedan,SUV,Luxury,Economy,Truck,Van',
            'seating_capacity' => 'required|integer|min:1|max:15',
            'fuel_type' => 'required|in:Petrol,Diesel,Electric,Hybrid',
            'current_mileage' => 'required|numeric|min:0',
            'status' => 'required|in:available,rented,maintenance',
            'description' => 'nullable|string|max:500',
            'image_url' => 'nullable|url'
        ];
    }


    public function isAvailableForPeriod($pickupDate, $returnDate)
    {
        return !$this->bookings()
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where(function ($query) use ($pickupDate, $returnDate) {
                $query->where(function ($q) use ($pickupDate, $returnDate) {
                    // Overlapping bookings check
                    $q->where('pickup_datetime', '<=', $returnDate)
                        ->where('return_datetime', '>=', $pickupDate);
                });
            })->exists();
    }
}
