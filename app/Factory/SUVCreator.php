<?php

namespace App\Factory;

use App\Models\Vehicle;

/**
 * Concrete Creator for SUV vehicles
 */
class SUVCreator extends AbstractVehicleCreator
{
    /**
     * Factory method to create an SUV vehicle
     *
     * @param array $data Vehicle data
     * @return Vehicle
     */
    public function createVehicle(array $data): Vehicle
    {
        // Ensure the type is set to SUV
        $data['type'] = 'SUV';

        return Vehicle::create($data);
    }

    /**
     * Get default values for SUV vehicles
     *
     * @return array
     */
    public function getDefaults(): array
    {
        return [
            'seating_capacity' => 7,
            'description' => 'Spacious and versatile SUV perfect for family trips and outdoor adventures.',
            'fuel_type' => 'Petrol'
        ];
    }

    /**
     * Get late fee for SUV vehicles
     *
     * @return float
     */
    public function getLateFee(): float
    {
        return 15.00;
    }
}
