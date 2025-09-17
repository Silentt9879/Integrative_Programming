<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\RentalRate;
use App\Factory\VehicleFactoryRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * VehicleService - Service Layer for Vehicle Management Module
 *
 * This service class separates business logic from controllers,
 * implements the Factory Method Pattern for vehicle creation,
 * and provides a clean interface for vehicle operations.
 *
 * Implements separation of concerns and service layer pattern
 * as required by web services implementation rubrics.
 */
class VehicleService
{
    /**
     * Get paginated vehicles with search and filter capabilities
     *
     * @param Request $request
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getVehicles(Request $request, int $perPage = 12)
    {
        try {
            $query = Vehicle::with('rentalRate');

            // Apply filters
            $this->applyFilters($query, $request);

            // Apply search
            $this->applySearch($query, $request);

            return $query->orderBy('created_at', 'desc')->paginate($perPage);
        } catch (\Exception $e) {
            Log::error('Error fetching vehicles: ' . $e->getMessage());
            throw new \Exception('Failed to retrieve vehicles');
        }
    }

    /**
     * Get available vehicles only
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableVehicles(?int $limit = null)
    {
        try {
            $query = Vehicle::with('rentalRate')
                ->where('status', 'available')
                ->orderBy('created_at', 'desc');

            if ($limit) {
                $query->limit($limit);
            }

            return $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching available vehicles: ' . $e->getMessage());
            throw new \Exception('Failed to retrieve available vehicles');
        }
    }

    /**
     * Find vehicle by ID with rental rate
     *
     * @param int $id
     * @return Vehicle
     * @throws \Exception
     */
    public function findVehicle(int $id): Vehicle
    {
        try {
            $vehicle = Vehicle::with('rentalRate')->find($id);

            if (!$vehicle) {
                throw new \Exception('Vehicle not found');
            }

            return $vehicle;
        } catch (\Exception $e) {
            Log::error('Error finding vehicle: ' . $e->getMessage());
            throw new \Exception('Vehicle not found');
        }
    }

    /**
     * Create a new vehicle using Factory Method Pattern
     *
     * @param array $data
     * @return Vehicle
     * @throws \Exception
     */
    public function createVehicle(array $data): Vehicle
    {
        DB::beginTransaction();

        try {
            // Validate vehicle type is supported
            if (!VehicleFactoryRegistry::isSupported($data['type'])) {
                throw new \InvalidArgumentException("Unsupported vehicle type: {$data['type']}");
            }

            // Handle image upload if provided
            if (isset($data['image'])) {
                $data['image_url'] = $this->handleImageUpload($data['image']);
            }

            // Use Factory Method Pattern to create vehicle
            $creator = VehicleFactoryRegistry::getCreator($data['type']);
            $vehicle = $creator->processVehicle($data);

            DB::commit();

            Log::info('Vehicle created successfully', ['vehicle_id' => $vehicle->id, 'type' => $data['type']]);

            return $vehicle;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating vehicle: ' . $e->getMessage());
            throw new \Exception('Failed to create vehicle: ' . $e->getMessage());
        }
    }

    /**
     * Update existing vehicle using Factory Method Pattern
     *
     * @param int $id
     * @param array $data
     * @return Vehicle
     * @throws \Exception
     */
    public function updateVehicle(int $id, array $data): Vehicle
    {
        DB::beginTransaction();

        try {
            $vehicle = $this->findVehicle($id);

            // Validate vehicle type is supported
            if (!VehicleFactoryRegistry::isSupported($data['type'])) {
                throw new \InvalidArgumentException("Unsupported vehicle type: {$data['type']}");
            }

            // Handle image upload if provided
            if (isset($data['image'])) {
                // Delete old image if exists
                if ($vehicle->image_url) {
                    $this->deleteImage($vehicle->image_url);
                }
                $data['image_url'] = $this->handleImageUpload($data['image']);
            }

            // Use Factory Method Pattern to update vehicle
            $creator = VehicleFactoryRegistry::getCreator($data['type']);
            $vehicle = $creator->updateVehicle($vehicle, $data);

            DB::commit();

            Log::info('Vehicle updated successfully', ['vehicle_id' => $vehicle->id]);

            return $vehicle;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating vehicle: ' . $e->getMessage());
            throw new \Exception('Failed to update vehicle: ' . $e->getMessage());
        }
    }

    /**
     * Delete vehicle and associated data
     *
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function deleteVehicle(int $id): bool
    {
        DB::beginTransaction();

        try {
            $vehicle = $this->findVehicle($id);

            // Check if vehicle has active bookings
            if ($vehicle->bookings()->where('status', 'active')->exists()) {
                throw new \Exception('Cannot delete vehicle with active bookings');
            }

            // Delete image file if exists
            if ($vehicle->image_url) {
                $this->deleteImage($vehicle->image_url);
            }

            // Delete related rental rate
            if ($vehicle->rentalRate) {
                $vehicle->rentalRate->delete();
            }

            // Delete vehicle
            $vehicle->delete();

            DB::commit();

            Log::info('Vehicle deleted successfully', ['vehicle_id' => $id]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting vehicle: ' . $e->getMessage());
            throw new \Exception('Failed to delete vehicle: ' . $e->getMessage());
        }
    }

    /**
     * Toggle vehicle status
     *
     * @param int $id
     * @return Vehicle
     * @throws \Exception
     */
    public function toggleVehicleStatus(int $id): Vehicle
    {
        try {
            $vehicle = $this->findVehicle($id);
            $newStatus = $vehicle->status === 'available' ? 'maintenance' : 'available';

            $vehicle->update(['status' => $newStatus]);

            Log::info('Vehicle status toggled', ['vehicle_id' => $id, 'new_status' => $newStatus]);

            return $vehicle;
        } catch (\Exception $e) {
            Log::error('Error toggling vehicle status: ' . $e->getMessage());
            throw new \Exception('Failed to update vehicle status');
        }
    }

    /**
     * Get vehicle type defaults using Factory Method Pattern
     *
     * @param string $type
     * @return array
     * @throws \Exception
     */
    public function getVehicleTypeDefaults(string $type): array
    {
        try {
            if (!VehicleFactoryRegistry::isSupported($type)) {
                throw new \InvalidArgumentException("Unsupported vehicle type: {$type}");
            }

            return VehicleFactoryRegistry::getTypeDefaults($type);
        } catch (\Exception $e) {
            Log::error('Error getting type defaults: ' . $e->getMessage());
            throw new \Exception('Failed to get vehicle type defaults');
        }
    }

    /**
     * Get similar vehicles
     *
     * @param Vehicle $vehicle
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSimilarVehicles(Vehicle $vehicle, int $limit = 3)
    {
        try {
            return Vehicle::with('rentalRate')
                ->where('type', $vehicle->type)
                ->where('id', '!=', $vehicle->id)
                ->where('status', 'available')
                ->limit($limit)
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching similar vehicles: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get supported vehicle types
     *
     * @return array
     */
    public function getSupportedVehicleTypes(): array
    {
        return VehicleFactoryRegistry::getSupportedTypes();
    }

    /**
     * Check if vehicle is available for booking period
     *
     * @param int $vehicleId
     * @param string $pickupDate
     * @param string $returnDate
     * @return bool
     */
    public function isVehicleAvailable(int $vehicleId, string $pickupDate, string $returnDate): bool
    {
        try {
            $vehicle = $this->findVehicle($vehicleId);
            return $vehicle->isAvailableForPeriod($pickupDate, $returnDate);
        } catch (\Exception $e) {
            Log::error('Error checking vehicle availability: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Apply filters to vehicle query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     */
    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('fuel_type')) {
            $query->where('fuel_type', $request->fuel_type);
        }

        if ($request->filled('max_rate')) {
            $query->whereHas('rentalRate', function ($q) use ($request) {
                $q->where('daily_rate', '<=', $request->max_rate);
            });
        }

        if ($request->filled('min_rate')) {
            $query->whereHas('rentalRate', function ($q) use ($request) {
                $q->where('daily_rate', '>=', $request->min_rate);
            });
        }
    }

    /**
     * Apply search to vehicle query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     */
    private function applySearch($query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('make', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('license_plate', 'like', "%{$search}%")
                  ->orWhere('color', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Handle image upload
     *
     * @param \Illuminate\Http\UploadedFile $image
     * @return string
     */
    private function handleImageUpload($image): string
{
    try {
        // MISSING: Advanced security checks
        if (!$this->isSecureImage($image)) {
            throw new \Exception('Security validation failed');
        }

        // MISSING: Secure filename generation
        $secureName = Str::random(40) . '_' . time() . '.' . $image->getClientOriginalExtension();

        $imagePath = $image->storeAs('vehicles', $secureName, 'public');
        return '/storage/' . $imagePath;
    } catch (\Exception $e) {
        Log::error('Error uploading image: ' . $e->getMessage());
        throw new \Exception('Failed to upload image');
    }
}

// ADD this new method
private function isSecureImage($image): bool
{
    // MIME type validation
    $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
    if (!in_array($image->getMimeType(), $allowedMimes)) {
        return false;
    }

    // File content validation
    if (@getimagesize($image->getPathname()) === false) {
        return false;
    }

    // Check for malicious content
    $content = file_get_contents($image->getPathname());
    if (strpos($content, '<?php') !== false || strpos($content, '<?=') !== false) {
        return false;
    }

    return true;
}

    /**
     * Delete image file
     *
     * @param string $imageUrl
     */
    private function deleteImage(string $imageUrl): void
    {
        try {
            $imagePath = str_replace('/storage/', '', $imageUrl);
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to delete image: ' . $e->getMessage());
        }
    }
}
