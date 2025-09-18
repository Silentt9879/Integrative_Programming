<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\RentalRate;
use App\Factory\VehicleFactoryRegistry;
use App\Http\Requests\StoreVehicleRequest; // Use your existing form request
use App\Http\Requests\UpdateVehicleRequest; // Use your existing form request
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class VehicleController extends Controller
{
    // ========================================================================
    // **ENHANCED INDEX WITH CACHING & ADVANCED FILTERING**
    // ========================================================================
    public function index(Request $request)
    {
        // Create cache key based on request parameters
        $cacheKey = 'vehicles_' . md5(serialize($request->all()));

        // Disabled cache for immediate updates during presentation
        $query = Vehicle::with('rentalRate');
        $this->applyFilters($query, $request);
        $vehicles = $query->orderBy('created_at', 'desc')->paginate(12);

        // Log search patterns for analytics (non-sensitive data only)
        if ($request->filled('search')) {
            Log::info('Vehicle search performed', [
                'search_term' => Str::limit($request->search, 50),
                'filters' => $request->only(['type', 'year', 'max_rate', 'status']),
                'results_count' => $vehicles->total()
            ]);
        }

        return view('vehicles.index', compact('vehicles'));
    }

    // ========================================================================
    // **SHOW INDIVIDUAL VEHICLE WITH CACHING**
    // ========================================================================
    public function show($id)
    {
        try {
            // Cache individual vehicle for 15 minutes
            $cacheKey = "vehicle_{$id}";

            // immediate status updates
            $vehicle = Vehicle::with('rentalRate')->findOrFail($id);

            // Log vehicle view for analytics
            Log::info('Vehicle viewed', [
                'vehicle_id' => $vehicle->id,
                'license_plate' => $vehicle->license_plate,
                'viewer_id' => Auth::id(),
                'ip' => request()->ip()
            ]);

            return view('vehicles.show', compact('vehicle'));
        } catch (\Exception $e) {
            Log::error('Error displaying vehicle', [
                'vehicle_id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('vehicles.index')
                ->with('error', 'Vehicle not found or unavailable.');
        }
    }

    // ========================================================================
    // **ENHANCED STORE WITH SECURITY & ERROR HANDLING

    // ========================================================================
    public function store(StoreVehicleRequest $request)
    {
        // Rate limiting for vehicle creation (security measure)
        $executed = RateLimiter::attempt(
            'create-vehicle:' . ($request->user()->id ?? $request->ip()),
            5, // 5 attempts
            function () use ($request) {
                return $this->processVehicleCreation($request->validated());
            },
            3600 // per hour
        );

        if (!$executed) {
            return redirect()->back()
                ->withErrors(['error' => 'Too many vehicle creation attempts. Please try again later.'])
                ->withInput();
        }

        return $executed;
    }

    // ========================================================================
    // **ENHANCED UPDATE WITH SECURITY**
    // ========================================================================
    public function update(UpdateVehicleRequest $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        // Security check: Prevent updating vehicles with active bookings
        if ($vehicle->bookings()->where('status', 'active')->exists()) {
            return redirect()->back()
                ->withErrors(['error' => 'Cannot modify vehicle with active bookings'])
                ->withInput();
        }

        try {
            $validated = $request->validated();

            // Secure file handling
            $this->handleImageUpdate($vehicle, $validated, $request);

            $creator = VehicleFactoryRegistry::getCreator($validated['type']);
            $vehicle = $creator->updateVehicle($vehicle, $validated);

            // Clear related caches
            $this->clearVehicleCache();

            // Log update for audit trail
            Log::info('Vehicle updated', [
                'vehicle_id' => $vehicle->id,
                'updated_by' => Auth::id(),
                'changes' => $vehicle->getChanges()
            ]);

            return redirect()->route('admin.vehicles')
                ->with('success', 'Vehicle updated successfully using Factory Method Pattern!');
        } catch (\InvalidArgumentException $e) {
            Log::warning('Invalid vehicle type attempted', [
                'type' => $validated['type'] ?? 'unknown',
                'user_id' => Auth::id()
            ]);

            return redirect()->back()
                ->withErrors(['type' => 'Unsupported vehicle type: ' . ($validated['type'] ?? 'unknown')])
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Vehicle update failed', [
                'vehicle_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to update vehicle. Please try again.'])
                ->withInput();
        }
    }

    // ========================================================================
    // **ENHANCED DESTROY WITH SECURITY CHECKS**
    // ========================================================================
    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        // Enhanced security checks
        if ($vehicle->bookings()->whereIn('status', ['active', 'confirmed'])->exists()) {
            return redirect()->route('admin.vehicles')
                ->with('error', 'Cannot delete vehicle with active or confirmed bookings!');
        }

        try {
            // Secure file deletion
            $this->secureFileDelete($vehicle->image_url);

            // Delete related data
            if ($vehicle->rentalRate) {
                $vehicle->rentalRate->delete();
            }

            // Log deletion for audit
            Log::info('Vehicle deleted', [
                'vehicle_id' => $vehicle->id,
                'license_plate' => $vehicle->license_plate,
                'deleted_by' => Auth::id()
            ]);

            $vehicle->delete();
            $this->clearVehicleCache();

            return redirect()->route('admin.vehicles')
                ->with('success', 'Vehicle deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Vehicle deletion failed', [
                'vehicle_id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('admin.vehicles')
                ->with('error', 'Failed to delete vehicle. Please try again.');
        }
    }

    // ========================================================================
    // **ENHANCED UTILITY METHODS**
    // ========================================================================

    private function processVehicleCreation(array $validated)
    {
        try {
            // Secure file handling
            $this->handleImageUpload($validated, request());

            $creator = VehicleFactoryRegistry::getCreator($validated['type']);
            $vehicle = $creator->processVehicle($validated);

            // Clear cache
            $this->clearVehicleCache();

            // Log creation
            Log::info('Vehicle created', [
                'vehicle_id' => $vehicle->id,
                'type' => $vehicle->type,
                'created_by' => Auth::id()
            ]);

            return redirect()->route('admin.vehicles')
                ->with('success', 'Vehicle added successfully using Factory Method Pattern!');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withErrors(['type' => 'Unsupported vehicle type: ' . ($validated['type'] ?? 'unknown')])
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Vehicle creation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to create vehicle: ' . $e->getMessage()])
                ->withInput();
        }
    }

    private function handleImageUpload(array &$validated, Request $request)
    {
        if ($request->hasFile('image')) {
            // Enhanced file validation
            $file = $request->file('image');

            // Additional security checks
            if (!$this->isValidImage($file)) {
                throw new \InvalidArgumentException('Invalid image file');
            }

            $imagePath = $file->store('vehicles', 'public');
            $validated['image_url'] = '/storage/' . $imagePath;
        } elseif ($request->filled('image_url')) {
            // Validate external URLs
            if (!$this->isValidImageUrl($request->image_url)) {
                throw new \InvalidArgumentException('Invalid image URL');
            }
            $validated['image_url'] = $request->image_url;
        }
    }

    //Path Traversal Protection - secure coding practices -XY
    private function handleImageUpdate(Vehicle $vehicle, array &$validated, Request $request)
    {
        if ($request->hasFile('image')) {
            // Delete old image
            $this->secureFileDelete($vehicle->image_url);

            // Upload new image
            $this->handleImageUpload($validated, $request);
        } elseif ($request->filled('image_url')) {
            $validated['image_url'] = $request->image_url;
        }
    }

    private function isValidImage($file): bool
    {
        // Additional validation beyond form request
        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        return in_array($file->getMimeType(), $allowedMimes) &&
            getimagesize($file->getPathname()) !== false;
    }

    private function isValidImageUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) &&
            parse_url($url, PHP_URL_SCHEME) === 'https';
    }

    //Path Traversal Protection - secure coding practices -XY
    private function secureFileDelete(?string $imageUrl): void
    {
        if ($imageUrl && str_starts_with($imageUrl, '/storage/')) {
            $path = str_replace('/storage/', '', $imageUrl);

            // Prevent directory traversal
            if (!str_contains($path, '..') && str_starts_with($path, 'vehicles/')) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('max_rate')) {
            $query->whereHas('rentalRate', function ($q) use ($request) {
                $q->where('daily_rate', '<=', $request->max_rate);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('make', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('license_plate', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Clear vehicle listing cache when availability changes
     */
    private function clearVehicleCache(): void
    {
        // Clear vehicle listing cache when availability changes
        $commonFilters = [
            '',
            'type=Economy',
            'type=Luxury',
            'type=Sedan',
            'type=SUV',
            'type=Van',
            'type=Truck',
            'status=available',
            'status=rented',
            'status=maintenance'
        ];

        foreach ($commonFilters as $filter) {
            $filterArray = [];
            if (!empty($filter)) {
                parse_str($filter, $filterArray);
            }
            $key = 'vehicles_' . md5(serialize($filterArray));
            Cache::forget($key);
        }

        // Clear the base cache key (no filters)
        Cache::forget('vehicles_' . md5(''));

        // Also clear some common search combinations
        $searchCombos = [
            'search=toyota',
            'search=bmw',
            'search=perodua',
            'year=2020',
            'year=2021',
            'year=2022',
            'year=2025',
            'status=available&type=Economy',
            'status=rented&type=Luxury'
        ];
        foreach ($searchCombos as $combo) {
            parse_str($combo, $comboArray);
            $key = 'vehicles_' . md5(serialize($comboArray));
            Cache::forget($key);
        }
    }

    // ========================================================================
    // **ENHANCED API ENDPOINT**
    // ========================================================================
    public function getTypeDefaults(Request $request)
    {
        $type = $request->get('type');

        if (!VehicleFactoryRegistry::isSupported($type)) {
            return response()->json([
                'success' => false,
                'error' => 'Unsupported vehicle type'
            ], 400);
        }

        try {
            $defaults = VehicleFactoryRegistry::getTypeDefaults($type);

            return response()->json([
                'success' => true,
                'defaults' => $defaults,
                'message' => "Defaults for {$type} retrieved successfully"
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get type defaults', [
                'type' => $type,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to get type defaults'
            ], 500);
        }
    }
}
