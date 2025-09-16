<?php

namespace App\Factory;

use App\Models\Vehicle;

//create a Truck vehicle
class TruckCreator extends AbstractVehicleCreator
{
    public function createVehicle(array $data): Vehicle
    {
        //  type set to Truck
        $data['type'] = 'Truck';

        return Vehicle::create($data);
    }

    //default values
    public function getDefaults(): array
    {
        return [
            'seating_capacity' => 3,
            'description' => 'Heavy-duty truck perfect for moving, construction, and cargo transportation needs.',
            'fuel_type' => 'Diesel'
        ];
    }

    //  late fee Truck
    public function getLateFee(): float
    {
        return 20.00;
    }
}
