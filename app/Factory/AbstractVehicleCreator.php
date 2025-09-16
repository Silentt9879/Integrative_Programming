<?php

namespace App\Factory;

use App\Models\Vehicle;
use App\Models\RentalRate;

//creste vehicle
abstract class AbstractVehicleCreator implements VehicleCreatorInterface
{

    public function processVehicle(array $data): Vehicle
    {
        // Apply type-specific defaults
        $data = $this->applyDefaults($data);
        $vehicle = $this->createVehicle($data);

        // Create rental rate
        $this->createRentalRate($vehicle, $data);

        return $vehicle;
    }

   //Update current vehicle
    public function updateVehicle(Vehicle $vehicle, array $data): Vehicle
    {
        // Apply type-specific defaults
        $data = $this->applyDefaults($data);
        $vehicle->update($data);

        // Update rental rate
        $this->updateRentalRate($vehicle, $data);

        return $vehicle;
    }

    //Modified data with defaults
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

    //Create rental rate /price
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

    //Update rental rate /price

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

   // vehicle types

    public static function getSupportedTypes(): array
    {
        return ['Sedan', 'SUV', 'Luxury', 'Economy', 'Truck', 'Van'];
    }

    abstract public function createVehicle(array $data): Vehicle;

    abstract public function getDefaults(): array;

    abstract public function getLateFee(): float;
}
