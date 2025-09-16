<?php

namespace App\Factory;

use App\Models\Vehicle;

//create a Sedan vehicle
class SedanCreator extends AbstractVehicleCreator
{
    public function createVehicle(array $data): Vehicle
    {
        // Ensure type is Sedan
        $data['type'] = 'Sedan';

        return Vehicle::create($data);
    }

    public function getDefaults(): array
    {
        return [
            'seating_capacity' => 5,
            'description' => 'Comfortable and fuel-efficient sedan perfect for city driving and daily commutes.',
            'fuel_type' => 'Petrol'
        ];
    }

    public function getLateFee(): float
    {
        return 8.00;
    }
}
