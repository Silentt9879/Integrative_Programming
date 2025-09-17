<?php
namespace App\Http\Controllers\Observer\Enhanced;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

// Observer Pattern Imports - Jayvian
use App\Http\Controllers\Observer\Subjects\ReportSubject;
use App\Http\Controllers\Observer\Observers\LoggingObserver;
use App\Http\Controllers\Observer\Observers\AdminNotificationObserver;

class ReportsController extends \App\Http\Controllers\Controller
{
    private ReportSubject $reportSubject;

    public function __construct()
    {
        // Initialize Observer Pattern
        $this->reportSubject = new ReportSubject();
        
        // Attach observers
        $this->reportSubject->attach(new LoggingObserver());
        $this->reportSubject->attach(new AdminNotificationObserver());
    }

    /**
     * Display the reports dashboard
     */
    public function index(Request $request)
    {
        if (!Auth::user() || !Auth::user()->is_admin) {
            abort(403);
        }

        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : Carbon::now()->subMonth();
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : Carbon::now()->endOfDay();

        // Get basic statistics
        $stats = [
            'totalRevenue' => Booking::whereBetween('created_at', [$dateFrom, $dateTo])
                ->whereIn('status', ['completed', 'active', 'confirmed'])
                ->sum('total_amount'),
            'totalBookings' => Booking::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'totalUsers' => User::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'totalVehicles' => Vehicle::count(),
        ];

        return view('admin.reports', compact('stats', 'dateFrom', 'dateTo'));
    }

    /**
     * Enhanced export PDF with Observer Pattern
     */
    public function exportPDF(Request $request)
    {
        if (!Auth::user() || !Auth::user()->is_admin) {
            abort(403);
        }

        $reportType = $request->report_type ?? 'overview';
        
        // OBSERVER PATTERN: Notify observers about report generation start
        $reportGenerationData = [
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'generation_start' => now(),
            'request_data' => $request->all()
        ];

        try {
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : Carbon::now()->subYears(2);
            $dateTo = $request->date_to ? Carbon::parse($request->date_to) : Carbon::now()->endOfDay();

            // Get report data
            $data = $this->getReportData($dateFrom, $dateTo, $reportType);

            $filename = 'rentwheels_report_' . $dateFrom->format('Y-m-d') . '_to_' . $dateTo->format('Y-m-d');

            // Generate PDF (simplified version - you can enhance this)
            $pdfContent = $this->generateSimplePDF($data, $reportType, $dateFrom, $dateTo);
            
            // OBSERVER PATTERN: Notify observers about successful report generation
            $this->reportSubject->notifyReportGenerated(
                $reportType, 
                Auth::user(), 
                ['status' => 'success'], 
                array_merge($reportGenerationData, [
                    'generation_completed' => now(),
                    'file_generated' => true
                ])
            );

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '.pdf"');
            
        } catch (\Exception $e) {
            // OBSERVER PATTERN: Notify observers about failed report generation
            $errorData = array_merge($reportGenerationData, [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'generation_failed' => now()
            ]);

            $this->reportSubject->notifyReportGenerated(
                $reportType . '_error', 
                Auth::user(), 
                ['status' => 'failed'], 
                $errorData
            );

            return redirect()->back()->with('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }

    /**
     * Get report data
     */
    private function getReportData($dateFrom, $dateTo, $reportType)
    {
        return [
            'stats' => [
                'totalRevenue' => Booking::whereBetween('created_at', [$dateFrom, $dateTo])
                    ->whereIn('status', ['completed', 'active', 'confirmed'])
                    ->sum('total_amount'),
                'totalBookings' => Booking::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'totalUsers' => User::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            ],
            'bookings' => Booking::with(['user', 'vehicle'])
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->latest()
                ->limit(20)
                ->get()
        ];
    }

    /**
     * Generate simple PDF content
     */
    private function generateSimplePDF($data, $reportType, $dateFrom, $dateTo)
    {
        // Simple PDF content generation
        $content = "RentWheels Report\n";
        $content .= "Report Type: {$reportType}\n";
        $content .= "Date Range: {$dateFrom->format('Y-m-d')} to {$dateTo->format('Y-m-d')}\n";
        $content .= "Generated: " . now()->format('Y-m-d H:i:s') . "\n\n";
        $content .= "Statistics:\n";
        $content .= "Total Revenue: " . ($data['stats']['totalRevenue'] ?? 0) . "\n";
        $content .= "Total Bookings: " . ($data['stats']['totalBookings'] ?? 0) . "\n";
        $content .= "Total Users: " . ($data['stats']['totalUsers'] ?? 0) . "\n";
        
        return $content;
    }

    /**
     * Observer Pattern: Get observer information for debugging
     */
    public function getObserverInfo()
    {
        if (!Auth::user() || !Auth::user()->is_admin) {
            abort(403);
        }

        $observers = [];
        foreach ($this->reportSubject->getObservers() as $observer) {
            $observers[] = [
                'name' => $observer->getName(),
                'class' => get_class($observer)
            ];
        }

        return response()->json([
            'subject' => $this->reportSubject->getSubjectName(),
            'observer_count' => count($observers),
            'observers' => $observers
        ]);
    }
}