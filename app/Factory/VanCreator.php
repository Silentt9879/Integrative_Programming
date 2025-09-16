<?php

namespace App\Factory;

use App\Models\Vehicle;

/**
 * Concrete Creator for Van vehicles
 */
class VanCreator extends AbstractVehicleCreator
{
    /**
     * Factory method to create a Van vehicle
     *
     * @param array $data Vehicle data
     * @return Vehicle
     */
    public function createVehicle(array $data): Vehicle
    {
        // Ensure the type is set to Van
        $data['type'] = 'Van';

        return Vehicle::create($data);
    }

    /**
     * Get default values for Van vehicles
     *
     * @return array
     */
    public function getDefaults(): array
    {
        return [
            'seating_capacity' => 8,
            'description' => 'Spacious van perfect for group travel, family trips, and transportation of multiple passengers.',
            'fuel_type' => 'Diesel'
        ];
    }

    /**
     * Get late fee for Van vehicles
     *
     * @return float
     */
    public function getLateFee(): float
    {
        return 12.00;
    }
}
