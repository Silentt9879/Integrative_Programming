<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Booking;
use App\State\StateFactory; // STATE PATTERN - Chong Zheng Yao
use App\Http\Controllers\ReportsController;
use App\Factory\VehicleFactoryRegistry; // Factory Method Pattern - Tan Xing Ye
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{

    /**
     * Show admin login form
     */
    public function showLogin()
    {
        // Additional check: If user is already logged in
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->is_admin) {
                return redirect()->route('admin.dashboard');
            } else {
                // Regular user trying to access admin login - redirect to user dashboard
                return redirect()->route('dashboard')
                    ->with('error', 'Access denied. You are logged in as a regular user.');
            }
        }

        return view('admin.login');
    }

    /**
     * Handle admin login submission
     */
    public function login(Request $request)
    {
        // Additional check: If user is already logged in
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->is_admin) {
                return redirect()->route('admin.dashboard');
            } else {
                Auth::logout(); // Logout regular user before admin login attempt
            }
        }

        // Validate the form data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'Admin email is required.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Admin password is required.',
            'password.min' => 'Password must be at least 6 characters long.'
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }

        // Get credentials
        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        // Attempt to authenticate
        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            // CRITICAL: Check if user is admin
            if (!$user->is_admin) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Access denied. Admin privileges required. This incident has been logged.'
                ])->withInput($request->only('email'));
            }

            // Check if admin account is active
            if ($user->status !== 'active') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your admin account has been suspended.'
                ])->withInput($request->only('email'));
            }

            $request->session()->regenerate();

            return redirect()->route('admin.dashboard')
                ->with('success', 'Welcome back, Admin ' . $user->name . '!');
        }

        // Authentication failed
        return back()->withErrors([
            'email' => 'The provided credentials do not match our admin records.',
        ])->withInput($request->only('email'));
    }

    /**
     * Show specific vehicle for admin
     */
    public function showVehicle(Vehicle $vehicle)
    {
        $this->ensureAdminAccess();

        // Load the rental rate relationship
        $vehicle->load('rentalRate');

        return view('vehicles.show', compact('vehicle'));
    }

    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        $this->ensureAdminAccess();

        // Calculate total revenue from paid bookings
        $totalRevenue = Booking::where('payment_status', 'paid')->sum('total_amount');

        // Calculate monthly growth
        $thisMonth = Booking::where('payment_status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        $lastMonth = Booking::where('payment_status', 'paid')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('total_amount');

        $revenueGrowth = $lastMonth > 0 ? (($thisMonth - $lastMonth) / $lastMonth) * 100 : 0;

        // Calculate new users this week
        $newUsersThisWeek = User::where('is_admin', false)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        // Get dashboard statistics
        $stats = [
            'totalUsers' => User::where('is_admin', false)->count(),
            'totalVehicles' => Vehicle::count(),
            'totalBookings' => Booking::count(),
            'activeBookings' => Booking::whereIn('status', ['active', 'confirmed', 'ongoing'])->count(),
            'availableVehicles' => Vehicle::where('status', 'available')->count(),
            'recentUsers' => User::where('is_admin', false)->latest()->take(5)->get(),
            'recentBookings' => Booking::with(['user', 'vehicle'])->latest()->take(5)->get(),
            // Add the new revenue calculations
            'totalRevenue' => $totalRevenue,
            'revenueGrowth' => round($revenueGrowth, 1),
            'newUsersThisWeek' => $newUsersThisWeek,
        ];

        return view('admin.dashboard', $stats);
    }

    /**
     * Admin logout
     */
    public function logout(Request $request)
    {
        // Ensure user is admin before logout
        if (!Auth::check() || !Auth::user()->is_admin) {
            return redirect()->route('admin.login')
                ->with('error', 'Invalid logout attempt.');
        }

        $adminName = Auth::user()->name ?? 'Admin';

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'Goodbye ' . $adminName . '! You have been logged out successfully.');
    }

    /**
     * User Management (General users list - keeping original functionality)
     */
    public function users()
    {
        $this->ensureAdminAccess();
        $users = User::where('is_admin', false)->paginate(15);
        return view('admin.users', compact('users'));
    }

    /**
     * Customer Management - Comprehensive customer management with filtering and search
     */
    public function customers(Request $request)
    {
        $this->ensureAdminAccess();

        // Start with base query
        $query = User::where('is_admin', false);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply sorting
        switch ($request->sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'email':
                $query->orderBy('email', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Get the filtered results (paginate for better performance)
        $users = $query->paginate(10)->appends($request->query());

        // Get statistics
        $totalUsers = User::where('is_admin', false)->count();
        $activeUsers = User::where('is_admin', false)->where('status', 'active')->count();
        $inactiveUsers = User::where('is_admin', false)->where('status', 'inactive')->count();
        $suspendedUsers = User::where('is_admin', false)->where('status', 'suspended')->count();

        return view('admin.customers', compact(
            'users',
            'totalUsers',
            'activeUsers',
            'inactiveUsers',
            'suspendedUsers'
        ));
    }

    /**
     * Store new customer
     */
    public function storeCustomer(Request $request)
    {
        $this->ensureAdminAccess();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'status' => 'required|in:active,suspended'
        ]);
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => $request->status,
                'is_admin' => false
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully!',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating customer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update customer
     */
    public function updateCustomer(Request $request, User $user)
    {
        $this->ensureAdminAccess();
        if ($user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit admin users'
            ], 403);
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'status' => 'required|in:active,suspended'
        ]);
        try {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'status' => $request->status
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Customer updated successfully!',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating customer: ' . $e->getMessage()
            ], 500);
        }
    }

    //Delete customer
    public function deleteCustomer(User $user)
    {
        $this->ensureAdminAccess();
        if ($user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete admin users'
            ], 403);
        }
        $activeBookings = Booking::where('user_id', $user->id)
            ->whereIn('status', ['active', 'confirmed', 'ongoing'])
            ->count();
        if ($activeBookings > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete customer with active bookings'
            ], 400);
        }
        try {
            $user->delete();
            return response()->json([
                'success' => true,
                'message' => 'Customer deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting customer: ' . $e->getMessage()
            ], 500);
        }
    }

    //Vehicle Management -  filtering & search - Tan Xing Ye
    public function vehicles(Request $request)
    {
        $this->ensureAdminAccess();

        $query = Vehicle::with('rentalRate');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('make', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('license_plate', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        // Apply sorting
        switch ($request->sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'price_low':
                $query->join('rental_rates', 'vehicles.id', '=', 'rental_rates.vehicle_id')
                    ->orderBy('rental_rates.daily_rate', 'asc')
                    ->select('vehicles.*');
                break;
            case 'price_high':
                $query->join('rental_rates', 'vehicles.id', '=', 'rental_rates.vehicle_id')
                    ->orderBy('rental_rates.daily_rate', 'desc')
                    ->select('vehicles.*');
                break;
            case 'mileage':
                $query->orderBy('current_mileage', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $vehicles = $query->paginate(12)->appends($request->query());

        // Cal statistics
        $totalVehicles = Vehicle::count();
        $availableVehicles = Vehicle::where('status', 'available')->count();
        $rentedVehicles = Vehicle::where('status', 'rented')->count();
        $maintenanceVehicles = Vehicle::where('status', 'maintenance')->count();

        return view('admin.vehicles', compact(
            'vehicles',
            'totalVehicles',
            'availableVehicles',
            'rentedVehicles',
            'maintenanceVehicles'
        ));
    }

    // vehicle status
    public function toggleStatus(Vehicle $vehicle)
    {
        $this->ensureAdminAccess();

        $statusOrder = ['available', 'rented', 'maintenance'];
        $currentIndex = array_search($vehicle->status, $statusOrder);
        $nextIndex = ($currentIndex + 1) % count($statusOrder);

        $vehicle->status = $statusOrder[$nextIndex];
        $vehicle->save();

        return back()->with('success', 'Vehicle status updated successfully!');
    }

    //Show create vehicle list
    public function createVehicle()
    {
        $this->ensureAdminAccess();
        return view('admin.create');
    }

    //Store new vehicle  - Factory Method Pattern - Tan Xing Ye
    public function storeVehicle(Request $request)
    {
        $this->ensureAdminAccess();

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

        // image url
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('vehicles', 'public');
            $validated['image_url'] = '/storage/' . $imagePath;
        } elseif ($request->filled('image_url')) {
            $validated['image_url'] = $request->image_url;
        }

        try {
            $creator = VehicleFactoryRegistry::getCreator($validated['type']);
            $vehicle = $creator->processVehicle($validated);

            return redirect()->route('admin.vehicles')
                ->with('success', 'Vehicle added successfully using Factory Method Pattern!');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withErrors(['type' => 'Unsupported vehicle type: ' . $validated['type']])
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to create vehicle: ' . $e->getMessage()])
                ->withInput();
        }
    }

    //show edit vehicle
    public function editVehicle(Vehicle $vehicle)
    {
        $this->ensureAdminAccess();

        $vehicle->load('rentalRate');

        return view('admin.edit', compact('vehicle'));
    }

    //Update/edit vehicle - Factory Method Pattern -Tan Xing Ye
    public function updateVehicle(Request $request, Vehicle $vehicle)
    {
        $this->ensureAdminAccess();

        $validated = $request->validate([
            'license_plate' => 'required|string|max:20|unique:vehicles,license_plate,' . $vehicle->id,
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

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($vehicle->image_url && Storage::disk('public')->exists(str_replace('/storage/', '', $vehicle->image_url))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $vehicle->image_url));
            }

            $imagePath = $request->file('image')->store('vehicles', 'public');
            $validated['image_url'] = '/storage/' . $imagePath;
        } elseif ($request->filled('image_url')) {
            $validated['image_url'] = $request->image_url;
        }

        //updates
        try {
            $creator = VehicleFactoryRegistry::getCreator($validated['type']);
            $vehicle = $creator->updateVehicle($vehicle, $validated);

            return redirect()->route('admin.vehicles')
                ->with('success', 'Vehicle updated successfully using Factory Method Pattern!');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withErrors(['type' => 'Unsupported vehicle type: ' . $validated['type']])
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update vehicle: ' . $e->getMessage()])
                ->withInput();
        }
    }

    //Delete vehicle
    public function deleteVehicle(Vehicle $vehicle)
    {
        $this->ensureAdminAccess();

        // Check if vehicle has active bookings
        $activeBookings = Booking::where('vehicle_id', $vehicle->id)
            ->whereIn('status', ['active', 'confirmed', 'ongoing'])
            ->count();

        if ($activeBookings > 0) {
            return back()->with('error', 'Cannot delete vehicle with active bookings.');
        }

        // Delete image file if exists
        if ($vehicle->image_url && Storage::disk('public')->exists(str_replace('/storage/', '', $vehicle->image_url))) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $vehicle->image_url));
        }

        // Delete related rental rate
        if ($vehicle->rentalRate) {
            $vehicle->rentalRate->delete();
        }

        $vehicle->delete();

        return back()->with('success', 'Vehicle deleted successfully!');
    }

    //error Get type defaults using Factory Method Pattern - Tan Xing Ye
    public function getTypeDefaults(Request $request)
    {
        $this->ensureAdminAccess();

        $type = $request->get('type');

        if (!VehicleFactoryRegistry::isSupported($type)) {
            return response()->json(['error' => 'Unsupported vehicle type'], 400);
        }

        try {
            $defaults = VehicleFactoryRegistry::getTypeDefaults($type);
            return response()->json(['defaults' => $defaults]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get type defaults: ' . $e->getMessage()], 500);
        }
    }

    //Booking Management - State Pattern - Chong Zheng Yao

    public function bookings(Request $request)
    {
        $this->ensureAdminAccess();

        $query = Booking::with(['user', 'vehicle']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('vehicle', function ($q) use ($search) {
                $q->where('make', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
            });
        }

        // date range filter
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month);
                    break;
                case 'year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }

        // Apply sorting
        switch ($request->sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'amount_high':
                $query->orderBy('total_amount', 'desc');
                break;
            case 'amount_low':
                $query->orderBy('total_amount', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $bookings = $query->paginate(15)->appends($request->query());

        // state information to each booking - State Pattern
        $bookings->each(function ($booking) {
            $booking->availableActions = $booking->getAvailableActions();
            $booking->stateMessage = $booking->getStateMessage();
            $booking->nextState = $booking->getNextState();
            $booking->canTransition = [
                'confirmed' => $booking->canTransitionTo('confirmed'),
                'active' => $booking->canTransitionTo('active'),
                'completed' => $booking->canTransitionTo('completed'),
                'cancelled' => $booking->canTransitionTo('cancelled')
            ];
        });

        // Cal real statistics
        $totalBookings = Booking::count();
        $pendingBookings = Booking::where('status', 'pending')->count();
        $confirmedBookings = Booking::where('status', 'confirmed')->count();
        $activeBookings = Booking::where('status', 'active')->count();
        $completedBookings = Booking::where('status', 'completed')->count();
        $cancelledBookings = Booking::where('status', 'cancelled')->count();
        $totalRevenue = Booking::where('payment_status', 'paid')->sum('total_amount');

        // Get possible states from State Factory
        $allStates = StateFactory::getAllStates();
        $stateWorkflow = StateFactory::getStateWorkflow();

        return view('admin.bookings', compact(
            'bookings',
            'totalBookings',
            'pendingBookings',
            'confirmedBookings',
            'activeBookings',
            'completedBookings',
            'cancelledBookings',
            'totalRevenue',
            'allStates',
            'stateWorkflow'
        ));
    }

    //Show booking details (AJAX) - State Pattern - Chong Zheng Yao
    public function showBooking(Booking $booking)
    {
        $this->ensureAdminAccess();

        $booking->load(['user', 'vehicle']);

        $booking->availableActions = $booking->getAvailableActions();
        $booking->stateMessage = $booking->getStateMessage();
        $booking->nextState = $booking->getNextState();
        $booking->statusDescription = $booking->getStatusDescription();
        $booking->isTerminal = $booking->isInTerminalState();
        $booking->requiresPayment = $booking->requiresPayment();

        // Get available transitions
        $booking->canTransition = [
            'confirmed' => $booking->canTransitionTo('confirmed'),
            'active' => $booking->canTransitionTo('active'),
            'completed' => $booking->canTransitionTo('completed'),
            'cancelled' => $booking->canTransitionTo('cancelled')
        ];

        $html = view('admin.partials.booking-details', compact('booking'))->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'stateInfo' => [
                'currentState' => $booking->status,
                'availableActions' => $booking->availableActions,
                'nextState' => $booking->nextState,
                'stateMessage' => $booking->stateMessage,
                'canTransition' => $booking->canTransition,
                'requiresPayment' => $booking->requiresPayment
            ]
        ]);
    }

    //Update booking status - Chong Zheng Yao
    public function updateBookingStatus(Request $request, Booking $booking)
    {
        $this->ensureAdminAccess();

        $request->validate([
            'status' => 'required|in:pending,confirmed,active,completed,cancelled',
            'reason' => 'nullable|string|max:500',
            'damage_charges' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000'
        ]);

        $newStatus = $request->status;
        $currentStatus = $booking->status;

        // Use to validate & perform transition (State Pattern )
        if (!$booking->canTransitionTo($newStatus)) {
            return response()->json([
                'success' => false,
                'message' => "Cannot transition from '{$currentStatus}' to '{$newStatus}'. " .
                    "Allowed transitions: " . implode(', ', $booking->getAvailableActions())
            ], 400);
        }

        // Perform the state transition
        $transitioned = false;
        $message = '';

        try {
            switch ($newStatus) {
                case 'confirmed':
                    // Check payment status be4 confirming
                    if ($booking->payment_status !== 'paid' && $currentStatus === 'pending') {
                        return response()->json([
                            'success' => false,
                            'message' => 'Cannot confirm booking without payment. Payment status is: ' . $booking->payment_status
                        ], 400);
                    }
                    $transitioned = $booking->confirm();
                    $message = 'Booking confirmed successfully';
                    break;

                case 'active':
                    // pickup time has arrived ?
                    if ($booking->pickup_datetime > now()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Cannot activate booking before pickup time: ' .
                                $booking->pickup_datetime->format('M d, Y h:i A')
                        ], 400);
                    }
                    $transitioned = $booking->activate();
                    $message = 'Booking activated (vehicle picked up)';
                    break;

                case 'completed':
                    $completionData = [];
                    if ($request->filled('damage_charges')) {
                        $completionData['damage_charges'] = $request->damage_charges;
                    }
                    if ($request->filled('notes')) {
                        $completionData['notes'] = $request->notes;
                    }
                    $transitioned = $booking->complete($completionData);
                    $message = 'Booking completed successfully';
                    break;

                case 'cancelled':
                    $reason = $request->reason ?? 'Cancelled by administrator';
                    $transitioned = $booking->cancel($reason);
                    $message = 'Booking cancelled successfully';
                    break;

                default:
                    // For any other status, try direct update
                    $booking->status = $newStatus;
                    $booking->save();
                    $transitioned = true;
                    $message = 'Booking status updated to ' . $newStatus;
            }

            if (!$transitioned) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to transition booking status. Please check booking constraints.'
                ], 400);
            }

            // updated state information
            $booking->refresh();
            $stateInfo = [
                'currentState' => $booking->status,
                'availableActions' => $booking->getAvailableActions(),
                'nextState' => $booking->getNextState(),
                'stateMessage' => $booking->getStateMessage(),
                'statusBadgeColor' => $booking->status_badge_color
            ];

            // retuen JSON for AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'stateInfo' => $stateInfo
                ]);
            }

            // Redirect for regular requests
            return back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Booking status update failed', [
                'booking_id' => $booking->id,
                'attempted_status' => $newStatus,
                'error' => $e->getMessage()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating booking status: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error updating booking status: ' . $e->getMessage());
        }
    }

    // Confirm a booking - Chong Zheng Yao
    public function confirmBooking(Request $request, Booking $booking)
    {
        $this->ensureAdminAccess();

        if (!$booking->canPerformAction('confirm')) {
            return response()->json([
                'success' => false,
                'message' => 'This booking cannot be confirmed in its current state.'
            ], 400);
        }

        if ($booking->confirm()) {
            return response()->json([
                'success' => true,
                'message' => 'Booking confirmed successfully',
                'newStatus' => $booking->status,
                'nextActions' => $booking->getAvailableActions()
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to confirm booking'
        ], 400);
    }

    //Activate a booking from admin panel
    public function activateBooking(Request $request, Booking $booking)
    {
        $this->ensureAdminAccess();

        if (!$booking->canPerformAction('activate')) {
            return response()->json([
                'success' => false,
                'message' => 'This booking cannot be activated. Pickup time may not have arrived.'
            ], 400);
        }

        if ($booking->activate()) {
            return response()->json([
                'success' => true,
                'message' => 'Booking activated successfully',
                'newStatus' => $booking->status,
                'nextActions' => $booking->getAvailableActions()
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to activate booking'
        ], 400);
    }

    //Complete booking
    public function completeBooking(Request $request, Booking $booking)
    {
        $this->ensureAdminAccess();

        $validated = $request->validate([
            'damage_charges' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        if (!$booking->canPerformAction('complete')) {
            return response()->json([
                'success' => false,
                'message' => 'This booking cannot be completed in its current state.'
            ], 400);
        }

        if ($booking->complete($validated)) {
            return response()->json([
                'success' => true,
                'message' => 'Booking completed successfully',
                'newStatus' => $booking->status,
                'finalAmount' => $booking->final_amount
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to complete booking'
        ], 400);
    }

    //Cancel a booking
    public function cancelBooking(Request $request, Booking $booking)
    {
        $this->ensureAdminAccess();

        $validated = $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        if (!$booking->canPerformAction('cancel')) {
            return response()->json([
                'success' => false,
                'message' => 'This booking cannot be cancelled in its current state.'
            ], 400);
        }

        if ($booking->cancel($validated['reason'])) {
            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully',
                'newStatus' => $booking->status
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to cancel booking'
        ], 400);
    }

    //Get booking state information
    /**
     * Confirm vehicle pickup - activate booking
     */
    public function pickupVehicle(Request $request, Booking $booking)
    {
        $this->ensureAdminAccess();

        $booking->load('vehicle'); // Load the vehicle relationship

        // Validate that booking can be activated
        if (!$booking->canPerformAction('activate')) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot activate this booking. Current status: ' . $booking->status
                ], 400);
            }
            return back()->with('error', 'Cannot activate this booking. Current status: ' . $booking->status);
        }

        try {
            // Activate the booking using state pattern
            $activated = $booking->activate();

            if (!$activated) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unable to activate booking. Please check booking status.'
                    ], 400);
                }
                return back()->with('error', 'Unable to activate booking. Please check booking status.');
            }

            Log::info("Admin confirmed pickup for booking {$booking->id} - vehicle {$booking->vehicle->id}");

            // Refresh booking to get updated state information
            $booking->refresh();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Vehicle pickup confirmed! Booking is now active.',
                    'newStatus' => $booking->status,
                    'availableActions' => $booking->getAvailableActions(),
                    'stateMessage' => $booking->getStateMessage(),
                    'statusBadgeColor' => $booking->getStatusBadgeColorAttribute()
                ]);
            }

            return back()->with('success', 'Vehicle pickup confirmed! Booking is now active.');
        } catch (\Exception $e) {
            Log::error("Failed to confirm pickup for booking {$booking->id}: " . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to confirm vehicle pickup. Please try again.'
                ], 500);
            }

            return back()->with('error', 'Failed to confirm vehicle pickup. Please try again.');
        }
    }
    //Return vehicle - calculate late fees if applicable
    public function returnVehicle(Request $request, Booking $booking)
    {
        $booking->load('vehicle');

        // Validate that booking can be completed
        if (!$booking->canPerformAction('complete')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot complete this booking. Current status: ' . $booking->status
            ], 400);
        }

        $data = $request->validate([
            'damage_charges' => 'nullable|numeric|min:0',
            'return_notes' => 'nullable|string|max:500',
            'actual_return_datetime' => 'nullable|date'
        ]);

        // Set actual return time if not provided
        if (!isset($data['actual_return_datetime'])) {
            $data['actual_return_datetime'] = now();
        }

        try {
            // Complete the booking using state pattern
            $completed = $booking->complete($data);

            if (!$completed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to complete booking. Please check booking status.'
                ], 400);
            }

            Log::info("Admin completed booking {$booking->id} and returned vehicle {$booking->vehicle->id}");

            return response()->json([
                'success' => true,
                'message' => 'Vehicle returned successfully! Booking completed and vehicle is now available.'
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to return vehicle for booking {$booking->id}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to complete vehicle return. Please try again.'
            ], 500);
        }
    }

    /**
     * Create additional charges payment for late fees and damages
     */
    private function createAdditionalChargesPayment(Booking $booking, $totalCharges, $lateFees, $damageCharges)
    {
        // Create a payment record for the additional charges
        $payment = \App\Models\Payment::create([
            'booking_id' => $booking->id,
            'amount' => $totalCharges,
            'payment_method' => 'pending',
            'payment_type' => 'additional_charges',
            'status' => 'pending',
            'payment_details' => [
                'late_fees' => $lateFees,
                'damage_charges' => $damageCharges,
                'description' => 'Additional charges for late return and/or vehicle damage'
            ],
            'notes' => 'Payment required for additional charges incurred during rental period'
        ]);

        // Update booking to reflect pending additional payment
        $booking->update([
            'payment_status' => 'additional_charges_pending'
        ]);

        return $payment;
    }

    /**
     * Payment Management - Chiew Chun Sheng will manage payments here
     */
    public function payments()
    {
        $this->ensureAdminAccess();
        // Chiew Chun Sheng will manage payments here
        return view('admin.payments');
    }

    /**
     * Display the reports page
     */
    public function reports(Request $request)
    {
        $this->ensureAdminAccess();

        // Changed default date range to show all data instead of just current month
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : Carbon::now()->subYear();
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : Carbon::now()->endOfDay();

        // Get basic statistics
        $stats = $this->getReportsStats($dateFrom, $dateTo);

        // Get chart data
        $chartData = $this->getReportsChartData($dateFrom, $dateTo);

        // Get table data
        $tableData = $this->getReportsTableData($dateFrom, $dateTo);

        return view('admin.reports', compact('stats', 'chartData', 'tableData', 'dateFrom', 'dateTo'));
    }

    /**
     * Filter reports via AJAX
     */
    public function filterReports(Request $request)
    {
        $this->ensureAdminAccess();

        try {
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : Carbon::now()->subYear();
            $dateTo = $request->date_to ? Carbon::parse($request->date_to) : Carbon::now()->endOfDay();

            $stats = $this->getReportsStats($dateFrom, $dateTo);
            $chartData = $this->getReportsChartData($dateFrom, $dateTo);
            $tableData = $this->getReportsTableData($dateFrom, $dateTo);

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'chartData' => $chartData,
                'tableData' => $tableData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error filtering reports: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Export reports - redirect to dedicated ReportsController
     */
    public function exportReports(Request $request)
    {
        $reportsController = new ReportsController();
        return $reportsController->exportPDF($request);
    }

    /**
     * Get basic statistics for reports
     */
    private function getReportsStats($dateFrom, $dateTo)
    {
        $totalRevenue = Booking::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereIn('status', ['completed', 'active', 'confirmed'])
            ->sum('total_amount');

        // Changed from activeRentals to totalVehicles
        $totalVehicles = Vehicle::count();

        $totalBookings = Booking::whereBetween('created_at', [$dateFrom, $dateTo])->count();

        $totalUsers = User::whereBetween('created_at', [$dateFrom, $dateTo])->count();

        // Calculate utilization rate based on active rentals
        $activeRentals = Booking::where('status', 'active')
            ->whereBetween('pickup_datetime', [$dateFrom, $dateTo])
            ->count();

        $utilizationRate = $totalVehicles > 0 ?
            round(($activeRentals / $totalVehicles) * 100, 2) : 0;

        return [
            'totalRevenue' => $totalRevenue,
            'totalVehicles' => $totalVehicles, // Changed from activeRentals
            'totalBookings' => $totalBookings,
            'totalUsers' => $totalUsers,
            'utilizationRate' => $utilizationRate
        ];
    }

    /**
     * Get chart data for reports
     */
    private function getReportsChartData($dateFrom, $dateTo)
    {
        // Revenue data by day for the selected period
        $revenueData = Booking::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereIn('status', ['completed', 'active', 'confirmed'])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $revenueLabels = [];
        $revenueValues = [];

        foreach ($revenueData as $data) {
            $revenueLabels[] = Carbon::parse($data->date)->format('M d');
            $revenueValues[] = (float) $data->revenue;
        }

        // Booking status distribution
        $bookingStatuses = Booking::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        $statusLabels = [];
        $statusValues = [];

        foreach ($bookingStatuses as $status) {
            $statusLabels[] = ucfirst($status->status);
            $statusValues[] = $status->count;
        }

        return [
            'revenueData' => [
                'labels' => $revenueLabels,
                'data' => $revenueValues
            ],
            'bookingStatusData' => [
                'labels' => $statusLabels,
                'data' => $statusValues
            ]
        ];
    }

    /**
     * Get table data for reports
     */
    private function getReportsTableData($dateFrom, $dateTo)
    {
        // Top users by bookings
        $topUsers = User::withCount(['bookings' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        }])
            ->withSum(['bookings as total_spent' => function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->whereIn('status', ['completed', 'active', 'confirmed']);
            }], 'total_amount')
            ->having('bookings_count', '>', 0)
            ->orderByDesc('bookings_count')
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'name' => $user->name,
                    'email' => $user->email,
                    'total_bookings' => $user->bookings_count,
                    'total_spent' => $user->total_spent ?? 0,
                    'updated_at' => $user->updated_at
                ];
            });

        // Vehicle performance
        $vehiclePerformance = Vehicle::withCount(['bookings' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        }])
            ->withSum(['bookings as revenue_generated' => function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->whereIn('status', ['completed', 'active', 'confirmed']);
            }], 'total_amount')
            ->get()
            ->map(function ($vehicle) {
                // Calculate utilization rate for each vehicle
                $totalDaysInPeriod = 30;
                $bookingDays = $vehicle->bookings_count;
                $utilizationRate = $totalDaysInPeriod > 0 ?
                    round(($bookingDays / $totalDaysInPeriod) * 100, 2) : 0;

                return [
                    'make' => $vehicle->make,
                    'model' => $vehicle->model,
                    'type' => $vehicle->type,
                    'status' => $vehicle->status,
                    'total_bookings' => $vehicle->bookings_count,
                    'revenue_generated' => $vehicle->revenue_generated ?? 0,
                    'utilization_rate' => $utilizationRate
                ];
            });

        // Recent bookings
        $recentBookings = Booking::with(['user', 'vehicle'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'user' => $booking->user ? [
                        'name' => $booking->user->name
                    ] : null,
                    'vehicle' => $booking->vehicle ? [
                        'make' => $booking->vehicle->make,
                        'model' => $booking->vehicle->model
                    ] : null,
                    'start_date' => $booking->pickup_datetime,
                    'end_date' => $booking->return_datetime,
                    'total_amount' => $booking->total_amount,
                    'status' => $booking->status
                ];
            });

        return [
            'topUsers' => $topUsers,
            'vehiclePerformance' => $vehiclePerformance,
            'recentBookings' => $recentBookings
        ];
    }

    private function ensureAdminAccess()
    {
        if (!Auth::check()) {
            abort(401, 'Authentication required');
        }

        $user = Auth::user();

        if (!$user->is_admin) {
            abort(403, 'Admin access required');
        }

        if ($user->status !== 'active') {
            Auth::logout();
            abort(403, 'Account suspended');
        }
    }
}
