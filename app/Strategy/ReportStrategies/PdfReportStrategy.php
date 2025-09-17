<?php
// app/Strategy/ReportStrategies/PdfReportStrategy.php

namespace App\Strategy\ReportStrategies;

use App\Models\User;
use Illuminate\Support\Collection;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PdfReportStrategy implements ReportGenerationStrategyInterface
{
    /**
     * Generate PDF report
     *
     * @param User $user
     * @param Collection $bookings
     * @param array $options
     * @return \Illuminate\Http\Response
     */
    public function generateReport(User $user, Collection $bookings, array $options = [])
    {
        $stats = $this->calculateStats($bookings);
        $groupedBookings = $this->groupBookings($bookings);

        $data = [
            'user' => $user,
            'bookings' => $bookings,
            'groupedBookings' => $groupedBookings,
            'stats' => $stats,
            'generatedAt' => Carbon::now(),
            'reportTitle' => $options['title'] ?? 'My Booking History Report',
            'reportType' => 'PDF'
        ];

        $pdf = Pdf::loadView('reports.user-booking-report', $data);
        $pdf->setPaper('A4', 'portrait');
        
        $fileName = $this->generateFileName($user, $options);
        
        return $pdf->download($fileName);
    }

    /**
     * Get report format name
     *
     * @return string
     */
    public function getFormatName(): string
    {
        return 'PDF';
    }

    /**
     * Get file extension
     *
     * @return string
     */
    public function getFileExtension(): string
    {
        return 'pdf';
    }

    /**
     * Get MIME type
     *
     * @return string
     */
    public function getMimeType(): string
    {
        return 'application/pdf';
    }

    /**
     * Check if format is available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return class_exists('\Barryvdh\DomPDF\Facade\Pdf');
    }

    /**
     * Calculate booking statistics
     *
     * @param Collection $bookings
     * @return array
     */
    private function calculateStats(Collection $bookings): array
    {
        return [
            'totalBookings' => $bookings->count(),
            'activeBookings' => $bookings->whereIn('status', ['confirmed', 'active', 'ongoing'])->count(),
            'completedBookings' => $bookings->where('status', 'completed')->count(),
            'cancelledBookings' => $bookings->where('status', 'cancelled')->count(),
            'totalAmountPaid' => $bookings->where('payment_status', 'paid')->sum('total_amount'),
            'pendingPayments' => $bookings->where('payment_status', 'pending')->sum('total_amount'),
            'averageBookingValue' => $bookings->where('payment_status', 'paid')->avg('total_amount') ?: 0,
            'totalRentalDays' => $bookings->sum('rental_days')
        ];
    }

    /**
     * Group bookings by status
     *
     * @param Collection $bookings
     * @return array
     */
    private function groupBookings(Collection $bookings): array
    {
        return [
            'Active' => $bookings->whereIn('status', ['confirmed', 'active', 'ongoing']),
            'Completed' => $bookings->where('status', 'completed'),
            'Cancelled' => $bookings->where('status', 'cancelled'),
            'Pending' => $bookings->where('status', 'pending'),
        ];
    }

    /**
     * Generate filename for the report
     *
     * @param User $user
     * @param array $options
     * @return string
     */
    private function generateFileName(User $user, array $options): string
    {
        $prefix = $options['filename_prefix'] ?? 'booking-report';
        $date = date('Y-m-d');
        
        return "{$prefix}-{$user->id}-{$date}.{$this->getFileExtension()}";
    }
}