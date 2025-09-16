<?php

namespace App\Factory;

use App\Models\Vehicle;

//create a vehicle
interface VehicleCreatorInterface
{

    public function createVehicle(array $data): Vehicle;

//default values for vehicle type

    public function getDefaults(): array;

    public function getLateFee(): float;
}
