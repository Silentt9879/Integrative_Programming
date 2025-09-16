<?php

namespace App\Factory;

use InvalidArgumentException;

//create class
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

    //create vehicle type
    public static function getCreator(string $type): AbstractVehicleCreator
    {
        if (!isset(self::$creators[$type])) {
            throw new InvalidArgumentException("Unsupported vehicle type: {$type}");
        }

        $creatorClass = self::$creators[$type];
        return new $creatorClass();
    }

    //supported vehicle types
    public static function getSupportedTypes(): array
    {
        return array_keys(self::$creators);
    }

    // vehicle type is supported
    public static function isSupported(string $type): bool
    {
        return isset(self::$creators[$type]);
    }

    // type defaults vehicle type

    public static function getTypeDefaults(string $type): array
    {
        $creator = self::getCreator($type);
        $defaults = $creator->getDefaults();
        $defaults['type'] = $type;
        $defaults['late_fee_per_hour'] = $creator->getLateFee();

        return $defaults;
    }
}
