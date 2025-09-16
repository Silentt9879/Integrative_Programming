<?php

namespace App\Factory;

use App\Models\Vehicle;

/**
 * Factory Method Pattern - Creator Interface
 * Defines the factory method for creating vehicles
 */
interface VehicleCreatorInterface
{
    /**
     * Factory method to create a vehicle
     *
     * @param array $data Vehicle data
     * @return Vehicle
     */
    public function createVehicle(array $data): Vehicle;

    /**
     * Get default values for this vehicle type
     *
     * @return array
     */
    public function getDefaults(): array;

    /**
     * Get late fee for this vehicle type
     *
     * @return float
     */
    public function getLateFee(): float;
}
