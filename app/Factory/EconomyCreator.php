<?php

namespace App\Factory;

use App\Models\Vehicle;

/**
 * Concrete Creator for Economy vehicles
 */
class EconomyCreator extends AbstractVehicleCreator
{
    /**
     * Factory method to create an Economy vehicle
     *
     * @param array $data Vehicle data
     * @return Vehicle
     */
    public function createVehicle(array $data): Vehicle
    {
        // Ensure the type is set to Economy
        $data['type'] = 'Economy';

        return Vehicle::create($data);
    }

    /**
     * Get default values for Economy vehicles
     *
     * @return array
     */
    public function getDefaults(): array
    {
        return [
            'seating_capacity' => 4,
            'description' => 'Budget-friendly economy vehicle perfect for short trips and everyday transportation needs.',
            'fuel_type' => 'Petrol'
        ];
    }

    /**
     * Get late fee for Economy vehicles
     *
     * @return float
     */
    public function getLateFee(): float
    {
        return 5.00;
    }
}
