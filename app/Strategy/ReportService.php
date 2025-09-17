<?php
// app/Strategy/ReportService.php

namespace App\Strategy;

use App\Strategy\ReportStrategies\ReportGenerationStrategyInterface;
use App\Strategy\ReportStrategies\PdfReportStrategy;
use App\Strategy\ReportStrategies\ExcelReportStrategy;
use App\Strategy\ReportStrategies\CsvReportStrategy;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Support\Collection;

class ReportService
{
    private ReportGenerationStrategyInterface $reportStrategy;
    private array $strategies;

    public function __construct()
    {
        $this->strategies = [
            'pdf' => new PdfReportStrategy(),
            'excel' => new ExcelReportStrategy(),
            'csv' => new CsvReportStrategy(),
        ];
    }

    /**
     * Set report generation strategy
     *
     * @param string $format
     * @throws \InvalidArgumentException
     */
    public function setReportStrategy(string $format): void
    {
        if (!isset($this->strategies[$format])) {
            throw new \InvalidArgumentException("Unsupported report format: {$format}");
        }

        if (!$this->strategies[$format]->isAvailable()) {
            throw new \RuntimeException("Report format '{$format}' is not available on this system");
        }

        $this->reportStrategy = $this->strategies[$format];
    }

    /**
     * Generate user booking report
     *
     * @param User $user
     * @param array $options
     * @return mixed
     */
    public function generateUserBookingReport(User $user, array $options = [])
    {
        if (!isset($this->reportStrategy)) {
            throw new \LogicException('Report strategy not set');
        }

        // Get user bookings with filters if provided
        $bookings = $this->getUserBookings($user, $options);

        // Add default report options
        $reportOptions = array_merge([
            'title' => 'My Booking History Report',
            'filename_prefix' => 'booking-report',
            'include_summary' => true,
            'group_by_status' => true
        ], $options);

        return $this->reportStrategy->generateReport($user, $bookings, $reportOptions);
    }

    /**
     * Get user bookings with optional filtering
     *
     * @param User $user
     * @param array $filters
     * @return Collection
     */
    private function getUserBookings(User $user, array $filters = []): Collection
    {
        $query = Booking::with(['vehicle', 'vehicle.rentalRate'])
            ->where('user_id', $user->id);

        // Apply date filters
        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Apply status filter
        if (isset($filters['status']) && !empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $query->whereIn('status', $filters['status']);
            } else {
                $query->where('status', $filters['status']);
            }
        }

        // Apply payment status filter
        if (isset($filters['payment_status']) && !empty($filters['payment_status'])) {
            if (is_array($filters['payment_status'])) {
                $query->whereIn('payment_status', $filters['payment_status']);
            } else {
                $query->where('payment_status', $filters['payment_status']);
            }
        }

        // Apply vehicle type filter
        if (isset($filters['vehicle_type']) && !empty($filters['vehicle_type'])) {
            $query->whereHas('vehicle', function($vehicleQuery) use ($filters) {
                $vehicleQuery->where('type', $filters['vehicle_type']);
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Generate admin reports (for future expansion)
     *
     * @param array $options
     * @return mixed
     */
    public function generateAdminReport(array $options = [])
    {
        if (!isset($this->reportStrategy)) {
            throw new \LogicException('Report strategy not set');
        }

        // This can be expanded for admin-specific reports
        $bookings = Booking::with(['user', 'vehicle'])->get();
        
        // Create a dummy admin user for the report
        $adminUser = new User([
            'name' => 'System Administrator',
            'email' => 'admin@rentwheels.com'
        ]);

        $reportOptions = array_merge([
            'title' => 'Admin Booking Report',
            'filename_prefix' => 'admin-report',
            'include_summary' => true,
            'detailed_view' => true
        ], $options);

        return $this->reportStrategy->generateReport($adminUser, $bookings, $reportOptions);
    }

    /**
     * Get available report formats
     *
     * @return array
     */
    public function getAvailableFormats(): array
    {
        $availableFormats = [];
        
        foreach ($this->strategies as $key => $strategy) {
            if ($strategy->isAvailable()) {
                $availableFormats[$key] = [
                    'name' => $strategy->getFormatName(),
                    'extension' => $strategy->getFileExtension(),
                    'mime_type' => $strategy->getMimeType()
                ];
            }
        }

        return $availableFormats;
    }

    /**
     * Check if a format is supported
     *
     * @param string $format
     * @return bool
     */
    public function isFormatSupported(string $format): bool
    {
        return isset($this->strategies[$format]) && $this->strategies[$format]->isAvailable();
    }

    /**
     * Get format details
     *
     * @param string $format
     * @return array|null
     */
    public function getFormatDetails(string $format): ?array
    {
        if (!$this->isFormatSupported($format)) {
            return null;
        }

        $strategy = $this->strategies[$format];
        
        return [
            'name' => $strategy->getFormatName(),
            'extension' => $strategy->getFileExtension(),
            'mime_type' => $strategy->getMimeType(),
            'available' => $strategy->isAvailable()
        ];
    }

    /**
     * Generate booking summary statistics
     *
     * @param Collection $bookings
     * @return array
     */
    public function generateBookingSummary(Collection $bookings): array
    {
        return [
            'total_bookings' => $bookings->count(),
            'active_bookings' => $bookings->whereIn('status', ['confirmed', 'active', 'ongoing'])->count(),
            'completed_bookings' => $bookings->where('status', 'completed')->count(),
            'cancelled_bookings' => $bookings->where('status', 'cancelled')->count(),
            'pending_bookings' => $bookings->where('status', 'pending')->count(),
            'total_revenue' => $bookings->where('payment_status', 'paid')->sum('total_amount'),
            'pending_payments' => $bookings->where('payment_status', 'pending')->sum('total_amount'),
            'average_booking_value' => $bookings->where('payment_status', 'paid')->avg('total_amount') ?: 0,
            'total_rental_days' => $bookings->sum('rental_days'),
            'most_popular_vehicles' => $this->getMostPopularVehicles($bookings),
            'booking_trends' => $this->getBookingTrends($bookings)
        ];
    }

    /**
     * Get most popular vehicles from bookings
     *
     * @param Collection $bookings
     * @return array
     */
    private function getMostPopularVehicles(Collection $bookings): array
    {
        return $bookings->groupBy('vehicle.id')
            ->map(function($group) {
                $vehicle = $group->first()->vehicle;
                return [
                    'vehicle' => $vehicle->make . ' ' . $vehicle->model,
                    'bookings_count' => $group->count(),
                    'total_revenue' => $group->where('payment_status', 'paid')->sum('total_amount')
                ];
            })
            ->sortByDesc('bookings_count')
            ->take(5)
            ->values()
            ->toArray();
    }

    /**
     * Get booking trends by month
     *
     * @param Collection $bookings
     * @return array
     */
    private function getBookingTrends(Collection $bookings): array
    {
        return $bookings->groupBy(function($booking) {
                return $booking->created_at->format('Y-m');
            })
            ->map(function($group, $month) {
                return [
                    'month' => $month,
                    'bookings' => $group->count(),
                    'revenue' => $group->where('payment_status', 'paid')->sum('total_amount')
                ];
            })
            ->sortBy('month')
            ->values()
            ->toArray();
    }
}