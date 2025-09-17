<?php

namespace App\Factory;

use InvalidArgumentException;
// Add these missing imports:
use App\Factory\SedanCreator;
use App\Factory\SUVCreator;
use App\Factory\LuxuryCreator;
use App\Factory\EconomyCreator;
use App\Factory\TruckCreator;
use App\Factory\VanCreator;

class VehicleFactoryRegistry
{

    private static array $creators = [
        'Sedan' => SedanCreator::class,
        'SUV' => SUVCreator::class,
        'Luxury' => LuxuryCreator::class,
        'Economy' => EconomyCreator::class,
        'Truck' => TruckCreator::class,
        'Van' => VanCreator::class,
    ];
    //creator instance based on vehicle type.

    public static function getCreator(string $type): AbstractVehicleCreator
    {
        if (!isset(self::$creators[$type])) {
            throw new InvalidArgumentException("Unsupported vehicle type: {$type}");
        }

        $creatorClass = self::$creators[$type];
        return new $creatorClass();
    }

   //Returns array of all vehicle types that can be created by this factory.
    public static function getSupportedTypes(): array
    {
        return array_keys(self::$creators);
    }

    //Validates whether a given vehicle type
    public static function isSupported(string $type): bool
    {
        return isset(self::$creators[$type]);
    }

    //Get default values and configuration for specific vehicle type
    public static function getTypeDefaults(string $type): array
    {
        $creator = self::getCreator($type);
        $defaults = $creator->getDefaults();
        $defaults['type'] = $type;
        $defaults['late_fee_per_hour'] = $creator->getLateFee();

        return $defaults;
    }
}
