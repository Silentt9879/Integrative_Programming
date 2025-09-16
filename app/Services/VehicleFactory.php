<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\RentalRate;

//Centralizes vehicle creation logic with type-specific defaults
class VehicleFactory
{
    //create vehicles based on type
    public static function createVehicle(array $data): Vehicle
    {
        // Apply type-specific defaults before creation
        $data = self::applyTypeDefaults($data);

        // Create the vehicle
        $vehicle = Vehicle::create($data);

        // Create associated rental rate
        self::createRentalRate($vehicle, $data);

        return $vehicle;
    }

    //update vehicles with type-specific logic
    public static function updateVehicle(Vehicle $vehicle, array $data): Vehicle
    {
        // Apply type-specific defaults for updates
        $data = self::applyTypeDefaults($data);

        // Update the vehicle
        $vehicle->update($data);

        // Update rental rate
        self::updateRentalRate($vehicle, $data);

        return $vehicle;
    }

    //Factory Method Pattern by customizing creation per type
    private static function applyTypeDefaults(array $data): array
    {
        $type = $data['type'] ?? 'Economy';

        switch ($type) {
            case 'Sedan':

                if (!isset($data['seating_capacity'])) {
                    $data['seating_capacity'] = 5;
                }
                if (!isset($data['description']) || empty($data['description'])) {
                    $data['description'] = 'Comfortable and fuel-efficient sedan perfect for city driving and daily commutes.';
                }
                break;

            case 'SUV':
                if (!isset($data['seating_capacity'])) {
                    $data['seating_capacity'] = 7;
                }
                if (!isset($data['description']) || empty($data['description'])) {
                    $data['description'] = 'Spacious and versatile SUV perfect for family trips and outdoor adventures.';
                }
                break;

            case 'Luxury':

                if (!isset($data['seating_capacity'])) {
                    $data['seating_capacity'] = 4;
                }
                if (!isset($data['description']) || empty($data['description'])) {
                    $data['description'] = 'Premium luxury vehicle with high-end features and exceptional comfort for special occasions.';
                }
                break;

            case 'Economy':

                if (!isset($data['seating_capacity'])) {
                    $data['seating_capacity'] = 4;
                }
                if (!isset($data['description']) || empty($data['description'])) {
                    $data['description'] = 'Budget-friendly economy vehicle perfect for short trips and everyday transportation needs.';
                }
                break;

            case 'Truck':

                if (!isset($data['seating_capacity'])) {
                    $data['seating_capacity'] = 3;
                }
                if (!isset($data['fuel_type'])) {
                    $data['fuel_type'] = 'Diesel';
                }
                if (!isset($data['description']) || empty($data['description'])) {
                    $data['description'] = 'Heavy-duty truck perfect for moving, construction, and cargo transportation needs.';
                }
                break;

            case 'Van':

                if (!isset($data['seating_capacity'])) {
                    $data['seating_capacity'] = 8;
                }
                if (!isset($data['fuel_type'])) {
                    $data['fuel_type'] = 'Diesel';
                }
                if (!isset($data['description']) || empty($data['description'])) {
                    $data['description'] = 'Spacious van perfect for group travel, family trips, and transportation of multiple passengers.';
                }
                break;

            default:

                if (!isset($data['seating_capacity'])) {
                    $data['seating_capacity'] = 5;
                }
                break;
        }

        return $data;
    }

   //Create rental rate with type-specific late fees
    private static function createRentalRate(Vehicle $vehicle, array $data): void
    {
        $lateFee = self::getLateFeeByType($vehicle->type);

        RentalRate::create([
            'vehicle_id' => $vehicle->id,
            'daily_rate' => $data['daily_rate'],
            'weekly_rate' => $data['weekly_rate'] ?? null,
            'monthly_rate' => $data['monthly_rate'] ?? null,
            'hourly_rate' => $data['daily_rate'] / 8,
            'late_fee_per_hour' => $lateFee
        ]);
    }

    //Update rental rate with type-specific late fees
    private static function updateRentalRate(Vehicle $vehicle, array $data): void
    {
        $lateFee = self::getLateFeeByType($vehicle->type);

        $rentalRate = $vehicle->rentalRate;
        if ($rentalRate) {
            $rentalRate->update([
                'daily_rate' => $data['daily_rate'],
                'weekly_rate' => $data['weekly_rate'] ?? null,
                'monthly_rate' => $data['monthly_rate'] ?? null,
                'hourly_rate' => $data['daily_rate'] / 8,
                'late_fee_per_hour' => $lateFee
            ]);
        } else {
            // Create rental rate if it doesn't exist
            self::createRentalRate($vehicle, $data);
        }
    }

   //Get late fee per hour based on vehicle type
    private static function getLateFeeByType(string $type): float
    {
        switch ($type) {
            case 'Economy':
                return 5.00;

            case 'Sedan':
                return 8.00;

            case 'Van':
                return 12.00;

            case 'SUV':
                return 15.00;

            case 'Truck':
                return 20.00;

            case 'Luxury':
                return 25.00;

            default:
                return 10.00;
        }
    }

    //Get vehicle type defaults for frontend use
    public static function getTypeDefaults(string $type): array
    {
        $defaults = ['type' => $type];
        $defaults = self::applyTypeDefaults($defaults);
        $defaults['late_fee_per_hour'] = self::getLateFeeByType($type);

        return $defaults;
    }

    // supported vehicle types
    public static function getSupportedTypes(): array
    {
        return ['Sedan', 'SUV', 'Luxury', 'Economy', 'Truck', 'Van'];
    }
}
