<?php
// app/Strategy/ReportStrategies/CsvReportStrategy.php

namespace App\Strategy\ReportStrategies;

use App\Models\User;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class CsvReportStrategy implements ReportGenerationStrategyInterface
{
    /**
     * Generate CSV report
     *
     * @param User $user
     * @param Collection $bookings
     * @param array $options
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function generateReport(User $user, Collection $bookings, array $options = [])
    {
        $fileName = $this->generateFileName($user, $options);
        
        $headers = [
            'Content-Type' => $this->getMimeType(),
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function() use ($user, $bookings) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel compatibility with UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            
            // Write header information
            fputcsv($file, ['Vehicle Rental Booking Report']);
            fputcsv($file, ['Customer', $user->name]);
            fputcsv($file, ['Email', $user->email]);
            fputcsv($file, ['Generated', Carbon::now()->format('Y-m-d H:i:s')]);
            fputcsv($file, []); // Empty row
            
            // Write summary statistics
            $stats = $this->calculateStats($bookings);
            fputcsv($file, ['SUMMARY STATISTICS']);
            fputcsv($file, ['Total Bookings', $stats['totalBookings']]);
            fputcsv($file, ['Active Bookings', $stats['activeBookings']]);
            fputcsv($file, ['Completed Bookings', $stats['completedBookings']]);
            fputcsv($file, ['Cancelled Bookings', $stats['cancelledBookings']]);
            fputcsv($file, ['Total Amount Paid', 'RM ' . number_format($stats['totalAmountPaid'], 2)]);
            fputcsv($file, ['Pending Payments', 'RM ' . number_format($stats['pendingPayments'], 2)]);
            fputcsv($file, ['Average Booking Value', 'RM ' . number_format($stats['averageBookingValue'], 2)]);
            fputcsv($file, ['Total Rental Days', $stats['totalRentalDays']]);
            fputcsv($file, []); // Empty row
            
            // Write bookings table header
            fputcsv($file, [
                'Booking Number',
                'Vehicle',
                'License Plate',
                'Pickup Date',
                'Return Date',
                'Rental Days',
                'Status',
                'Payment Status',
                'Total Amount (RM)',
                'Created Date'
            ]);
            
            // Write booking data
            foreach ($bookings as $booking) {
                fputcsv($file, [
                    $booking->booking_number,
                    $booking->vehicle->make . ' ' . $booking->vehicle->model,
                    $booking->vehicle->license_plate ?? 'N/A',
                    $booking->pickup_datetime->format('Y-m-d H:i'),
                    $booking->return_datetime->format('Y-m-d H:i'),
                    $booking->rental_days,
                    ucfirst($booking->status),
                    ucfirst(str_replace('_', ' ', $booking->payment_status)),
                    number_format($booking->total_amount, 2),
                    $booking->created_at->format('Y-m-d H:i')
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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

    /**
     * Get report format name
     *
     * @return string
     */
    public function getFormatName(): string
    {
        return 'CSV';
    }

    /**
     * Get file extension
     *
     * @return string
     */
    public function getFileExtension(): string
    {
        return 'csv';
    }

    /**
     * Get MIME type
     *
     * @return string
     */
    public function getMimeType(): string
    {
        return 'text/csv';
    }

    /**
     * Check if format is available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return true; // CSV is always available
    }
}