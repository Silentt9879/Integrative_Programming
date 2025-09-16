<?php

namespace App\Factory;

use App\Models\Vehicle;

// create Luxury vehicles
class LuxuryCreator extends AbstractVehicleCreator
{
    public function createVehicle(array $data): Vehicle
    {
        // Ensure type is Luxury
        $data['type'] = 'Luxury';

        return Vehicle::create($data);
    }

    public function getDefaults(): array
    {
        return [
            'seating_capacity' => 4,
            'description' => 'Premium luxury vehicle with high-end features and exceptional comfort for special occasions.',
            'fuel_type' => 'Petrol'
        ];
    }

    public function getLateFee(): float
    {
        return 25.00;
    }
}
