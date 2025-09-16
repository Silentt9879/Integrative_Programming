<?php

namespace App\Factory;

use App\Models\Vehicle;

//create Economy vehicles
class EconomyCreator extends AbstractVehicleCreator
{

    public function createVehicle(array $data): Vehicle
    {
        // Ensure  type is Economy
        $data['type'] = 'Economy';

        return Vehicle::create($data);
    }

    public function getDefaults(): array
    {
        return [
            'seating_capacity' => 4,
            'description' => 'Budget-friendly economy vehicle perfect for short trips and everyday transportation needs.',
            'fuel_type' => 'Petrol'
        ];
    }

    public function getLateFee(): float
    {
        return 5.00;
    }
}
