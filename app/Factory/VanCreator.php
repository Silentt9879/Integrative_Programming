<?php

namespace App\Factory;

use App\Models\Vehicle;

//create a Van vehicle
class VanCreator extends AbstractVehicleCreator
{
    public function createVehicle(array $data): Vehicle
    {
        // Ensure the type is set to Van
        $data['type'] = 'Van';

        return Vehicle::create($data);
    }

    //Get default values Van vehicles
    public function getDefaults(): array
    {
        return [
            'seating_capacity' => 8,
            'description' => 'Spacious van perfect for group travel, family trips, and transportation of multiple passengers.',
            'fuel_type' => 'Diesel'
        ];
    }


    public function getLateFee(): float
    {
        return 12.00;
    }
}
