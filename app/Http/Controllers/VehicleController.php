<?php


namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\RentalRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller {

   //Display a listing of vehicles with search and filter
    public function index(Request $request) {
        $query = Vehicle::with('rentalRate');

        // Apply filters
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

        $vehicles = $query->orderBy('created_at', 'desc')->paginate(12);

        return view('vehicles.index', compact('vehicles'));
    }

// Show the form for creating a new vehicle

    public function create() {
        return view('vehicles.create');
    }

 //Store a newly created vehicle in storage
    public function store(Request $request) {
        $validated = $request->validate([
            'license_plate' => 'required|string|max:20|unique:vehicles,license_plate',
            'make' => 'required|string|max:50',
            'model' => 'required|string|max:50',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'required|string|max:30',
            'type' => 'required|in:Sedan,SUV,Luxury,Economy,Truck,Van',
            'seating_capacity' => 'required|integer|min:1|max:15',
            'fuel_type' => 'required|in:Petrol,Diesel,Electric,Hybrid',
            'current_mileage' => 'required|numeric|min:0',
            'status' => 'required|in:available,rented,maintenance',
            'description' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_url' => 'nullable|url',
            'daily_rate' => 'required|numeric|min:0',
            'weekly_rate' => 'nullable|numeric|min:0',
            'monthly_rate' => 'nullable|numeric|min:0'
        ]);

        // Handle image upload OR URL - FIXED
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('vehicles', 'public');
            $validated['image_url'] = '/storage/' . $imagePath;
        } elseif ($request->filled('image_url')) {
            // If image URL is provided, use it
            $validated['image_url'] = $request->image_url;
        }

        // Create vehicle
        $vehicle = Vehicle::create($validated);

        // Create rental rate
        RentalRate::create([
            'vehicle_id' => $vehicle->id,
            'daily_rate' => $validated['daily_rate'],
            'weekly_rate' => $validated['weekly_rate'] ?? null,
            'monthly_rate' => $validated['monthly_rate'] ?? null,
            'hourly_rate' => $validated['daily_rate'] / 8,
            'late_fee_per_hour' => 10.00
        ]);

        return redirect()->route('admin.vehicles')
                        ->with('success', 'Vehicle added successfully!');
    }

//Display the specified vehicle

    public function show($id) {
        $vehicle = Vehicle::with('rentalRate')->findOrFail($id);

        // Get similar vehicles (same type, different id)
        $similarVehicles = Vehicle::with('rentalRate')
                ->where('type', $vehicle->type)
                ->where('id', '!=', $id)
                ->where('status', 'available')
                ->limit(3)
                ->get();

        return view('vehicles.show', compact('vehicle', 'similarVehicles'));
    }

    public function edit($id) {
        $vehicle = Vehicle::with('rentalRate')->findOrFail($id);
        return view('admin.edit', compact('vehicle'));
    }

    public function update(Request $request, $id) {
        $vehicle = Vehicle::findOrFail($id);

        $validated = $request->validate([
            'license_plate' => 'required|string|max:20|unique:vehicles,license_plate,' . $id,
            'make' => 'required|string|max:50',
            'model' => 'required|string|max:50',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'required|string|max:30',
            'type' => 'required|in:Sedan,SUV,Luxury,Economy,Truck,Van',
            'seating_capacity' => 'required|integer|min:1|max:15',
            'fuel_type' => 'required|in:Petrol,Diesel,Electric,Hybrid',
            'current_mileage' => 'required|numeric|min:0',
            'status' => 'required|in:available,rented,maintenance',
            'description' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_url' => 'nullable|url',
            'daily_rate' => 'required|numeric|min:0',
            'weekly_rate' => 'nullable|numeric|min:0',
            'monthly_rate' => 'nullable|numeric|min:0'
        ]);

        //image upload 
        if ($request->hasFile('image')) {
            if ($vehicle->image_url && Storage::disk('public')->exists(str_replace('/storage/', '', $vehicle->image_url))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $vehicle->image_url));
            }

            $imagePath = $request->file('image')->store('vehicles', 'public');
            $validated['image_url'] = '/storage/' . $imagePath;
        } elseif ($request->filled('image_url')) {
            $validated['image_url'] = $request->image_url;
        }

        // Update vehicle 
        $vehicle->update($validated);

        // Update rental rate
        $rentalRate = $vehicle->rentalRate;
        if ($rentalRate) {
            $rentalRate->update([
                'daily_rate' => $validated['daily_rate'],
                'weekly_rate' => $validated['weekly_rate'] ?? null,
                'monthly_rate' => $validated['monthly_rate'] ?? null,
                'hourly_rate' => $validated['daily_rate'] / 8,
            ]);
        } else {
            // Create rental rate no exits
            RentalRate::create([
                'vehicle_id' => $vehicle->id,
                'daily_rate' => $validated['daily_rate'],
                'weekly_rate' => $validated['weekly_rate'] ?? null,
                'monthly_rate' => $validated['monthly_rate'] ?? null,
                'hourly_rate' => $validated['daily_rate'] / 8,
                'late_fee_per_hour' => 10.00
            ]);
        }

        return redirect()->route('admin.vehicles')
                        ->with('success', 'Vehicle updated successfully!');
    }

 //Remove the specified vehicle from storage
    public function destroy($id) {
        $vehicle = Vehicle::findOrFail($id);

        // Check vehicle has status
        if ($vehicle->bookings()->where('status', 'active')->exists()) {
            return redirect()->route('admin.vehicles')
                            ->with('error', 'Cannot delete vehicle with active bookings!');
        }

        // Delete image file if it exists
        if ($vehicle->image_url && Storage::disk('public')->exists(str_replace('/storage/', '', $vehicle->image_url))) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $vehicle->image_url));
        }

        // Delete related rental rate
        if ($vehicle->rentalRate) {
            $vehicle->rentalRate->delete();
        }

        // Delete vehicle
        $vehicle->delete();

        return redirect()->route('admin.vehicles')
                        ->with('success', 'Vehicle deleted successfully!');
    }

 //Toggle vehicle availability status
    public function toggleStatus($id) {
        $vehicle = Vehicle::findOrFail($id);

        $newStatus = $vehicle->status === 'available' ? 'maintenance' : 'available';
        $vehicle->update(['status' => $newStatus]);

        return redirect()->back()
                        ->with('success', 'Vehicle status updated to ' . $newStatus . '!');
    }
}
