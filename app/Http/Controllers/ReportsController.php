<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    /**
     * Display the reports dashboard
     */
    public function index(Request $request)
    {
        // Changed default date range to show all data instead of just current month
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : Carbon::now()->subYear();
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : Carbon::now()->endOfDay();

        // Get basic statistics
        $stats = $this->getBasicStats($dateFrom, $dateTo);

        // Get chart data
        $chartData = $this->getChartData($dateFrom, $dateTo);

        // Get table data
        $tableData = $this->getTableData($dateFrom, $dateTo);

        return view('admin.reports', compact('stats', 'chartData', 'tableData', 'dateFrom', 'dateTo'));
    }

    /**
     * Filter reports via AJAX
     */
    public function filter(Request $request)
    {
        try {
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : Carbon::now()->subYear();
            $dateTo = $request->date_to ? Carbon::parse($request->date_to) : Carbon::now()->endOfDay();

            $stats = $this->getBasicStats($dateFrom, $dateTo);
            $chartData = $this->getChartData($dateFrom, $dateTo);
            $tableData = $this->getTableData($dateFrom, $dateTo);

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
     * Export PDF report
     */
    public function exportPDF(Request $request)
    {
        try {
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : Carbon::now()->subYears(2);
            $dateTo = $request->date_to ? Carbon::parse($request->date_to) : Carbon::now()->endOfDay();
            $reportType = $request->report_type ?? 'overview';

            // Get data based on report type
            $data = $this->getReportData($dateFrom, $dateTo, $reportType);

            $filename = 'rentwheels_report_' . $dateFrom->format('Y-m-d') . '_to_' . $dateTo->format('Y-m-d');

            // Generate PDF
            $pdf = Pdf::loadView('admin.reports.pdf', [
                'data' => $data,
                'reportType' => $reportType,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
            ])->setPaper('a4', 'portrait');

            return $pdf->download($filename . '.pdf');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }

    /**
     * Get basic statistics
     */
    private function getBasicStats($dateFrom, $dateTo)
    {
        $totalRevenue = Booking::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereIn('status', ['completed', 'active', 'confirmed'])
            ->sum('total_amount');

        // Changed from activeRentals to totalVehicles to match AdminController
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
     * Get chart data for dashboard
     */
    private function getChartData($dateFrom, $dateTo)
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
     * Get table data for dashboard
     */
    private function getTableData($dateFrom, $dateTo)
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
                $totalDaysInPeriod = 30; // You can make this dynamic based on date range
                $bookingDays = $vehicle->bookings_count; // Simplified calculation
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

    /**
     * Get comprehensive report data for PDF export
     */
    private function getReportData($dateFrom, $dateTo, $reportType)
    {
        $data = [];

        // Get basic stats
        $data['stats'] = [
            'totalRevenue' => Booking::whereBetween('created_at', [$dateFrom, $dateTo])
                ->whereIn('status', ['completed', 'active', 'confirmed'])
                ->sum('total_amount'),
            'totalVehicles' => Vehicle::count(), // Changed from activeRentals
            'totalBookings' => Booking::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'totalUsers' => User::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
        ];

        // Calculate utilization rate
        $activeRentals = Booking::where('status', 'active')
            ->whereBetween('pickup_datetime', [$dateFrom, $dateTo])
            ->count();
        $data['stats']['utilizationRate'] = $data['stats']['totalVehicles'] > 0 ?
            round(($activeRentals / $data['stats']['totalVehicles']) * 100, 2) : 0;

        if ($reportType === 'overview' || $reportType === 'users') {
            // Top users data
            $data['topUsers'] = User::withCount(['bookings' => function ($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('created_at', [$dateFrom, $dateTo]);
                }])
                ->withSum(['bookings as total_spent' => function ($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('created_at', [$dateFrom, $dateTo])
                        ->whereIn('status', ['completed', 'active', 'confirmed']);
                }], 'total_amount')
                ->having('bookings_count', '>', 0)
                ->orderByDesc('bookings_count')
                ->limit(10)
                ->get();
        }

        if ($reportType === 'overview' || $reportType === 'vehicles') {
            // Vehicle performance data
            $data['vehiclePerformance'] = Vehicle::withCount(['bookings' => function ($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('created_at', [$dateFrom, $dateTo]);
                }])
                ->withSum(['bookings as revenue_generated' => function ($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('created_at', [$dateFrom, $dateTo])
                        ->whereIn('status', ['completed', 'active', 'confirmed']);
                }], 'total_amount')
                ->orderByDesc('bookings_count')
                ->limit(20)
                ->get();
        }

        if ($reportType === 'overview' || $reportType === 'bookings') {
            // Recent bookings data
            $data['recentBookings'] = Booking::with(['user', 'vehicle'])
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->orderByDesc('created_at')
                ->limit(50)
                ->get();
        }

        return $data;
    }
}