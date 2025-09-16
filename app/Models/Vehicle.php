<?php
// app/Models/Vehicle.php
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
        ->where('status', '!=', 'cancelled')
        ->where(function($query) use ($pickupDate, $returnDate) {
            // Check both pickup_datetime/return_datetime AND pickup_time/return_time
            $query->where(function($q) use ($pickupDate, $returnDate) {
                $q->whereBetween('pickup_datetime', [$pickupDate, $returnDate])
                  ->orWhereBetween('return_datetime', [$pickupDate, $returnDate])
                  ->orWhere(function($sub) use ($pickupDate, $returnDate) {
                      $sub->where('pickup_datetime', '<=', $pickupDate)
                          ->where('return_datetime', '>=', $returnDate);
                  });
            })->orWhere(function($q) use ($pickupDate, $returnDate) {
                $q->whereBetween('pickup_time', [$pickupDate, $returnDate])
                  ->orWhereBetween('return_time', [$pickupDate, $returnDate])
                  ->orWhere(function($sub) use ($pickupDate, $returnDate) {
                      $sub->where('pickup_time', '<=', $pickupDate)
                          ->where('return_time', '>=', $returnDate);
                  });
            });
        })->exists();
}
}