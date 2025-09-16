<?php

namespace App\Factory;

use App\Models\Vehicle;

/**
 * Concrete Creator for Truck vehicles
 */
class TruckCreator extends AbstractVehicleCreator
{
    /**
     * Factory method to create a Truck vehicle
     *
     * @param array $data Vehicle data
     * @return Vehicle
     */
    public function createVehicle(array $data): Vehicle
    {
        // Ensure the type is set to Truck
        $data['type'] = 'Truck';

        return Vehicle::create($data);
    }

    /**
     * Get default values for Truck vehicles
     *
     * @return array
     */
    public function getDefaults(): array
    {
        return [
            'seating_capacity' => 3,
            'description' => 'Heavy-duty truck perfect for moving, construction, and cargo transportation needs.',
            'fuel_type' => 'Diesel'
        ];
    }

    /**
     * Get late fee for Truck vehicles
     *
     * @return float
     */
    public function getLateFee(): float
    {
        return 20.00;
    }
}
