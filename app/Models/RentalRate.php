<?php
// app/Models/RentalRate.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'hourly_rate',
        'daily_rate',
        'weekly_rate', 
        'monthly_rate',
        'late_fee_per_hour'
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'weekly_rate' => 'decimal:2',
        'monthly_rate' => 'decimal:2',
        'late_fee_per_hour' => 'decimal:2'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function calculateRate($days)
    {
        if ($days >= 30 && $this->monthly_rate) {
            return ($days / 30) * $this->monthly_rate;
        } elseif ($days >= 7 && $this->weekly_rate) {
            return ($days / 7) * $this->weekly_rate;
        } else {
            return $days * $this->daily_rate;
        }
    }
}
