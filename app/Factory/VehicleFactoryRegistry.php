<?php

namespace App\Factory;

use InvalidArgumentException;

/**
 * Registry pattern to manage vehicle creators
 * This class coordinates which concrete creator to use
 */
class VehicleFactoryRegistry
{
    /**
     * Map of vehicle types to their creator classes
     *
     * @var array
     */
    private static array $creators = [
        'Sedan' => SedanCreator::class,
        'SUV' => SUVCreator::class,
        'Luxury' => LuxuryCreator::class,
        'Economy' => EconomyCreator::class,
        'Truck' => TruckCreator::class,
        'Van' => VanCreator::class,
    ];

    /**
     * Get a creator instance for the specified vehicle type
     *
     * @param string $type Vehicle type
     * @return AbstractVehicleCreator
     * @throws InvalidArgumentException
     */
    public static function getCreator(string $type): AbstractVehicleCreator
    {
        if (!isset(self::$creators[$type])) {
            throw new InvalidArgumentException("Unsupported vehicle type: {$type}");
        }

        $creatorClass = self::$creators[$type];
        return new $creatorClass();
    }

    /**
     * Get all supported vehicle types
     *
     * @return array
     */
    public static function getSupportedTypes(): array
    {
        return array_keys(self::$creators);
    }

    /**
     * Check if a vehicle type is supported
     *
     * @param string $type Vehicle type
     * @return bool
     */
    public static function isSupported(string $type): bool
    {
        return isset(self::$creators[$type]);
    }

    /**
     * Get type defaults for a specific vehicle type
     *
     * @param string $type Vehicle type
     * @return array
     */
    public static function getTypeDefaults(string $type): array
    {
        $creator = self::getCreator($type);
        $defaults = $creator->getDefaults();
        $defaults['type'] = $type;
        $defaults['late_fee_per_hour'] = $creator->getLateFee();

        return $defaults;
    }
}
