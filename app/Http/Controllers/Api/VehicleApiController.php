<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VehicleService;
use App\Http\Resources\VehicleResource;
use App\Http\Resources\VehicleCollection;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * VehicleApiController - RESTful API Controller for Vehicle Management
 *
 * Provides comprehensive API endpoints for vehicle operations including:
 * - CRUD operations with proper HTTP status codes
 * - Resource transformation for consistent API responses
 * - Integration with VehicleService for business logic separation
 * - Factory Method Pattern implementation through service layer
 * - Error handling and validation
 *
 * Implements RESTful API best practices and Web Services requirements
 * as specified in the assignment rubrics.
 */
class VehicleApiController extends Controller
{
    /**
     * Vehicle service instance
     */
    protected VehicleService $vehicleService;

    /**
     * Constructor - Inject VehicleService dependency
     */
    public function __construct(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    /**
     * Display a listing of vehicles with filtering and pagination
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/api/vehicles",
     *     summary="Get all vehicles with optional filtering",
     *     tags={"Vehicles"},
     *     @OA\Parameter(name="search", in="query", description="Search by make, model, license plate"),
     *     @OA\Parameter(name="type", in="query", description="Filter by vehicle type"),
     *     @OA\Parameter(name="status", in="query", description="Filter by availability status"),
     *     @OA\Parameter(name="page", in="query", description="Page number for pagination"),
     *     @OA\Parameter(name="per_page", in="query", description="Items per page (max 50)"),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=400, description="Invalid request parameters")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Validate pagination parameters
            $perPage = min($request->get('per_page', 15), 50); // Max 50 items per page

            $vehicles = $this->vehicleService->getVehicles($request, $perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Vehicles retrieved successfully',
                'data' => new VehicleCollection($vehicles),
                'meta' => [
                    'current_page' => $vehicles->currentPage(),
                    'from' => $vehicles->firstItem(),
                    'last_page' => $vehicles->lastPage(),
                    'per_page' => $vehicles->perPage(),
                    'to' => $vehicles->lastItem(),
                    'total' => $vehicles->total(),
                    'filters_applied' => $this->getAppliedFilters($request)
                ]
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve vehicles',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get available vehicles only
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/api/vehicles/available",
     *     summary="Get available vehicles only",
     *     tags={"Vehicles"},
     *     @OA\Parameter(name="limit", in="query", description="Limit number of results"),
     *     @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function available(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit');
            $vehicles = $this->vehicleService->getAvailableVehicles($limit);

            return response()->json([
                'status' => 'success',
                'message' => 'Available vehicles retrieved successfully',
                'data' => VehicleResource::collection($vehicles),
                'count' => $vehicles->count()
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve available vehicles',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created vehicle using Factory Method Pattern
     *
     * @param StoreVehicleRequest $request
     * @return JsonResponse
     *
     * @OA\Post(
     *     path="/api/vehicles",
     *     summary="Create a new vehicle",
     *     tags={"Vehicles"},
     *     @OA\RequestBody(required=true, description="Vehicle data"),
     *     @OA\Response(response=201, description="Vehicle created successfully"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function store(StoreVehicleRequest $request): JsonResponse
    {
        try {
            $vehicle = $this->vehicleService->createVehicle($request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Vehicle created successfully using Factory Method Pattern',
                'data' => new VehicleResource($vehicle)
            ], Response::HTTP_CREATED);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid vehicle type',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create vehicle',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified vehicle
     *
     * @param int $id
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/api/vehicles/{id}",
     *     summary="Get vehicle by ID",
     *     tags={"Vehicles"},
     *     @OA\Parameter(name="id", in="path", required=true, description="Vehicle ID"),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="Vehicle not found")
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $vehicle = $this->vehicleService->findVehicle($id);
            $similarVehicles = $this->vehicleService->getSimilarVehicles($vehicle);

            return response()->json([
                'status' => 'success',
                'message' => 'Vehicle retrieved successfully',
                'data' => new VehicleResource($vehicle),
                'similar_vehicles' => VehicleResource::collection($similarVehicles)
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vehicle not found',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified vehicle using Factory Method Pattern
     *
     * @param UpdateVehicleRequest $request
     * @param int $id
     * @return JsonResponse
     *
     * @OA\Put(
     *     path="/api/vehicles/{id}",
     *     summary="Update vehicle by ID",
     *     tags={"Vehicles"},
     *     @OA\Parameter(name="id", in="path", required=true, description="Vehicle ID"),
     *     @OA\RequestBody(required=true, description="Updated vehicle data"),
     *     @OA\Response(response=200, description="Vehicle updated successfully"),
     *     @OA\Response(response=404, description="Vehicle not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UpdateVehicleRequest $request, int $id): JsonResponse
    {
        try {
            $vehicle = $this->vehicleService->updateVehicle($id, $request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Vehicle updated successfully using Factory Method Pattern',
                'data' => new VehicleResource($vehicle)
            ], Response::HTTP_OK);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid vehicle type',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update vehicle',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified vehicle from storage
     *
     * @param int $id
     * @return JsonResponse
     *
     * @OA\Delete(
     *     path="/api/vehicles/{id}",
     *     summary="Delete vehicle by ID",
     *     tags={"Vehicles"},
     *     @OA\Parameter(name="id", in="path", required=true, description="Vehicle ID"),
     *     @OA\Response(response=200, description="Vehicle deleted successfully"),
     *     @OA\Response(response=404, description="Vehicle not found"),
     *     @OA\Response(response=409, description="Vehicle has active bookings")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->vehicleService->deleteVehicle($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Vehicle deleted successfully'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            $statusCode = str_contains($e->getMessage(), 'active bookings')
                ? Response::HTTP_CONFLICT
                : Response::HTTP_NOT_FOUND;

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete vehicle',
                'error' => $e->getMessage()
            ], $statusCode);
        }
    }

    /**
     * Toggle vehicle status (available/maintenance)
     *
     * @param int $id
     * @return JsonResponse
     *
     * @OA\Patch(
     *     path="/api/vehicles/{id}/toggle-status",
     *     summary="Toggle vehicle availability status",
     *     tags={"Vehicles"},
     *     @OA\Parameter(name="id", in="path", required=true, description="Vehicle ID"),
     *     @OA\Response(response=200, description="Status updated successfully"),
     *     @OA\Response(response=404, description="Vehicle not found")
     * )
     */
    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $vehicle = $this->vehicleService->toggleVehicleStatus($id);

            return response()->json([
                'status' => 'success',
                'message' => "Vehicle status updated to {$vehicle->status}",
                'data' => new VehicleResource($vehicle)
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update vehicle status',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Get vehicle type defaults using Factory Method Pattern
     *
     * @param string $type
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/api/vehicles/types/{type}/defaults",
     *     summary="Get default values for specific vehicle type",
     *     tags={"Vehicles"},
     *     @OA\Parameter(name="type", in="path", required=true, description="Vehicle type"),
     *     @OA\Response(response=200, description="Defaults retrieved successfully"),
     *     @OA\Response(response=400, description="Unsupported vehicle type")
     * )
     */
    public function getTypeDefaults(string $type): JsonResponse
    {
        try {
            $defaults = $this->vehicleService->getVehicleTypeDefaults($type);

            return response()->json([
                'status' => 'success',
                'message' => "Default values for {$type} type retrieved successfully",
                'data' => $defaults
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get vehicle type defaults',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get supported vehicle types
     *
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/api/vehicles/types",
     *     summary="Get all supported vehicle types",
     *     tags={"Vehicles"},
     *     @OA\Response(response=200, description="Vehicle types retrieved successfully")
     * )
     */
    public function getVehicleTypes(): JsonResponse
    {
        $types = $this->vehicleService->getSupportedVehicleTypes();

        return response()->json([
            'status' => 'success',
            'message' => 'Supported vehicle types retrieved successfully',
            'data' => $types,
            'count' => count($types)
        ], Response::HTTP_OK);
    }

    /**
     * Check vehicle availability for specific period
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     *
     * @OA\Post(
     *     path="/api/vehicles/{id}/check-availability",
     *     summary="Check if vehicle is available for booking period",
     *     tags={"Vehicles"},
     *     @OA\Parameter(name="id", in="path", required=true, description="Vehicle ID"),
     *     @OA\RequestBody(required=true, description="Date range to check"),
     *     @OA\Response(response=200, description="Availability checked successfully")
     * )
     */
    public function checkAvailability(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'pickup_date' => 'required|date|after:today',
            'return_date' => 'required|date|after:pickup_date'
        ]);

        try {
            $isAvailable = $this->vehicleService->isVehicleAvailable(
                $id,
                $request->pickup_date,
                $request->return_date
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Availability checked successfully',
                'data' => [
                    'vehicle_id' => $id,
                    'pickup_date' => $request->pickup_date,
                    'return_date' => $request->return_date,
                    'available' => $isAvailable
                ]
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to check availability',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get vehicle statistics
     *
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/api/vehicles/statistics",
     *     summary="Get vehicle statistics and analytics",
     *     tags={"Vehicles"},
     *     @OA\Response(response=200, description="Statistics retrieved successfully")
     * )
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_vehicles' => \App\Models\Vehicle::count(),
                'available_vehicles' => \App\Models\Vehicle::where('status', 'available')->count(),
                'rented_vehicles' => \App\Models\Vehicle::where('status', 'rented')->count(),
                'maintenance_vehicles' => \App\Models\Vehicle::where('status', 'maintenance')->count(),
                'vehicle_types' => \App\Models\Vehicle::select('type', \DB::raw('count(*) as count'))
                    ->groupBy('type')
                    ->get(),
                'fuel_types' => \App\Models\Vehicle::select('fuel_type', \DB::raw('count(*) as count'))
                    ->groupBy('fuel_type')
                    ->get(),
                'average_daily_rate' => \App\Models\RentalRate::avg('daily_rate'),
                'total_fleet_value' => \App\Models\RentalRate::sum('daily_rate') * 365 // Estimated annual value
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Vehicle statistics retrieved successfully',
                'data' => $stats
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get applied filters from request
     *
     * @param Request $request
     * @return array
     */
    private function getAppliedFilters(Request $request): array
    {
        $filters = [];

        if ($request->filled('search')) {
            $filters['search'] = $request->search;
        }

        if ($request->filled('type')) {
            $filters['type'] = $request->type;
        }

        if ($request->filled('status')) {
            $filters['status'] = $request->status;
        }

        if ($request->filled('year')) {
            $filters['year'] = $request->year;
        }

        if ($request->filled('max_rate')) {
            $filters['max_rate'] = $request->max_rate;
        }

        if ($request->filled('min_rate')) {
            $filters['min_rate'] = $request->min_rate;
        }

        return $filters;
    }
}
