<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * VehicleResource - API Resource for individual vehicle transformation
 *
 * Transforms vehicle model data into consistent API response format
 * Implements proper data hiding and resource transformation patterns
 */
class VehicleResource extends JsonResource
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
            'id' => $this->id,
            'license_plate' => $this->license_plate,
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'color' => $this->color,
            'type' => $this->type,
            'seating_capacity' => $this->seating_capacity,
            'fuel_type' => $this->fuel_type,
            'current_mileage' => (float) $this->current_mileage,
            'status' => $this->status,
            'description' => $this->description,
            'image_url' => $this->image_url,

            // Rental Rate Information
            'rental_rate' => $this->when($this->relationLoaded('rentalRate'), function () {
                return [
                    'daily_rate' => (float) $this->rentalRate->daily_rate,
                    'weekly_rate' => $this->rentalRate->weekly_rate ? (float) $this->rentalRate->weekly_rate : null,
                    'monthly_rate' => $this->rentalRate->monthly_rate ? (float) $this->rentalRate->monthly_rate : null,
                    'hourly_rate' => (float) $this->rentalRate->hourly_rate,
                    'late_fee_per_hour' => (float) $this->rentalRate->late_fee_per_hour,
                ];
            }),

            // Computed Properties
            'full_name' => $this->make . ' ' . $this->model,
            'is_available' => $this->status === 'available',
            'display_price' => 'RM ' . number_format($this->rentalRate->daily_rate ?? 0, 2),

            // Metadata
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Links for HATEOAS
            'links' => [
                'self' => route('api.vehicles.show', $this->id),
                'book' => $this->when($this->status === 'available', route('api.bookings.create', $this->id)),
                'edit' => route('api.vehicles.update', $this->id),
                'delete' => route('api.vehicles.destroy', $this->id),
            ],
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
            ],
        ];
    }
}
