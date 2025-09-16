<?php

namespace App\Factory;

use App\Models\Vehicle;

/**
 * Concrete Creator for Luxury vehicles
 */
class LuxuryCreator extends AbstractVehicleCreator
{
    /**
     * Factory method to create a Luxury vehicle
     *
     * @param array $data Vehicle data
     * @return Vehicle
     */
    public function createVehicle(array $data): Vehicle
    {
        // Ensure the type is set to Luxury
        $data['type'] = 'Luxury';

        return Vehicle::create($data);
    }

    /**
     * Get default values for Luxury vehicles
     *
     * @return array
     */
    public function getDefaults(): array
    {
        return [
            'seating_capacity' => 4,
            'description' => 'Premium luxury vehicle with high-end features and exceptional comfort for special occasions.',
            'fuel_type' => 'Petrol'
        ];
    }

    /**
     * Get late fee for Luxury vehicles
     *
     * @return float
     */
    public function getLateFee(): float
    {
        return 25.00;
    }
}
