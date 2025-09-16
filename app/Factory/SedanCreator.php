<?php

namespace App\Factory;

use App\Models\Vehicle;

/**
 * Concrete Creator for Sedan vehicles
 */
class SedanCreator extends AbstractVehicleCreator
{
    /**
     * Factory method to create a Sedan vehicle
     *
     * @param array $data Vehicle data
     * @return Vehicle
     */
    public function createVehicle(array $data): Vehicle
    {
        // Ensure the type is set to Sedan
        $data['type'] = 'Sedan';

        return Vehicle::create($data);
    }

    /**
     * Get default values for Sedan vehicles
     *
     * @return array
     */
    public function getDefaults(): array
    {
        return [
            'seating_capacity' => 5,
            'description' => 'Comfortable and fuel-efficient sedan perfect for city driving and daily commutes.',
            'fuel_type' => 'Petrol'
        ];
    }

    /**
     * Get late fee for Sedan vehicles
     *
     * @return float
     */
    public function getLateFee(): float
    {
        return 8.00;
    }
}
