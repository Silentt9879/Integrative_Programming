<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * VehicleCollection - API Resource Collection for vehicle listings
 *
 * Transforms paginated vehicle collections with additional metadata
 * Provides consistent collection structure for API responses
 */
class VehicleCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'vehicles' => $this->collection->transform(function ($vehicle) {
                return new VehicleResource($vehicle);
            }),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => '1.0',
                'generated_at' => now()->toISOString(),
                'total_count' => $this->collection->count(),
            ],
            'links' => [
                'self' => $request->fullUrl(),
                'create' => route('api.vehicles.store'),
            ],
        ];
    }
}

// app/Http/Resources/VehicleTypeResource.php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * VehicleTypeResource - Resource for vehicle type information with defaults
 *
 * Used for Factory Method Pattern type information endpoints
 */
class VehicleTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => $this->resource['type'],
            'seating_capacity' => $this->resource['seating_capacity'],
            'description' => $this->resource['description'],
            'fuel_type' => $this->resource['fuel_type'],
            'late_fee_per_hour' => (float) $this->resource['late_fee_per_hour'],
            'created_using' => 'Factory Method Pattern',
        ];
    }
}
