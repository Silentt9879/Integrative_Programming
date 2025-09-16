<?php

namespace App\Factory;

use App\Models\Vehicle;

//create SUV vehicles
class SUVCreator extends AbstractVehicleCreator
{
    public function createVehicle(array $data): Vehicle
    {
        // Ensure type is SUV
        $data['type'] = 'SUV';

        return Vehicle::create($data);
    }

    // default SUV
    public function getDefaults(): array
    {
        return [
            'seating_capacity' => 7,
            'description' => 'Spacious and versatile SUV perfect for family trips and outdoor adventures.',
            'fuel_type' => 'Petrol'
        ];
    }

    //get fees
    public function getLateFee(): float
    {
        return 15.00;
    }
}
