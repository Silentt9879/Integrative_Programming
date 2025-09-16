<?php

namespace App\Factory;

use App\Models\Vehicle;
use App\Models\RentalRate;

/**
 * Factory Method Pattern - Abstract Creator Class
 * Provides common functionality for all vehicle creators
 */
abstract class AbstractVehicleCreator implements VehicleCreatorInterface
{
    /**
     * Template method that handles the complete vehicle creation process
     * This is the same for all vehicle types
     *
     * @param array $data Vehicle data
     * @return Vehicle
     */
    public function processVehicle(array $data): Vehicle
    {
        // Apply type-specific defaults
        $data = $this->applyDefaults($data);

        // Create the vehicle using the factory method
        $vehicle = $this->createVehicle($data);

        // Create rental rate
        $this->createRentalRate($vehicle, $data);

        return $vehicle;
    }

    /**
     * Update existing vehicle with type-specific logic
     *
     * @param Vehicle $vehicle Existing vehicle
     * @param array $data Updated data
     * @return Vehicle
     */
    public function updateVehicle(Vehicle $vehicle, array $data): Vehicle
    {
        // Apply type-specific defaults
        $data = $this->applyDefaults($data);

        // Update vehicle
        $vehicle->update($data);

        // Update rental rate
        $this->updateRentalRate($vehicle, $data);

        return $vehicle;
    }

    /**
     * Apply type-specific defaults to data
     *
     * @param array $data Original data
     * @return array Modified data with defaults
     */
    protected function applyDefaults(array $data): array
    {
        $defaults = $this->getDefaults();

        foreach ($defaults as $key => $value) {
            if (!isset($data[$key]) || empty($data[$key])) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * Create rental rate for the vehicle
     *
     * @param Vehicle $vehicle The created vehicle
     * @param array $data Original data
     */
    protected function createRentalRate(Vehicle $vehicle, array $data): void
    {
        RentalRate::create([
            'vehicle_id' => $vehicle->id,
            'daily_rate' => $data['daily_rate'],
            'weekly_rate' => $data['weekly_rate'] ?? null,
            'monthly_rate' => $data['monthly_rate'] ?? null,
            'hourly_rate' => $data['daily_rate'] / 8,
            'late_fee_per_hour' => $this->getLateFee()
        ]);
    }

    /**
     * Update rental rate for the vehicle
     *
     * @param Vehicle $vehicle The vehicle
     * @param array $data Updated data
     */
    protected function updateRentalRate(Vehicle $vehicle, array $data): void
    {
        $rentalRate = $vehicle->rentalRate;

        if ($rentalRate) {
            $rentalRate->update([
                'daily_rate' => $data['daily_rate'],
                'weekly_rate' => $data['weekly_rate'] ?? null,
                'monthly_rate' => $data['monthly_rate'] ?? null,
                'hourly_rate' => $data['daily_rate'] / 8,
                'late_fee_per_hour' => $this->getLateFee()
            ]);
        } else {
            $this->createRentalRate($vehicle, $data);
        }
    }

    /**
     * Get supported vehicle types
     *
     * @return array
     */
    public static function getSupportedTypes(): array
    {
        return ['Sedan', 'SUV', 'Luxury', 'Economy', 'Truck', 'Van'];
    }

    /**
     * Factory method - must be implemented by concrete creators
     *
     * @param array $data Vehicle data
     * @return Vehicle
     */
    abstract public function createVehicle(array $data): Vehicle;

    /**
     * Get type-specific defaults - must be implemented by concrete creators
     *
     * @return array
     */
    abstract public function getDefaults(): array;

    /**
     * Get type-specific late fee - must be implemented by concrete creators
     *
     * @return float
     */
    abstract public function getLateFee(): float;
}
