<?php
// app/Strategy/ReportStrategies/ExcelReportStrategy.php

namespace App\Strategy\ReportStrategies;

use App\Models\User;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class ExcelReportStrategy implements ReportGenerationStrategyInterface
{
    /**
     * Generate Excel report
     *
     * @param User $user
     * @param Collection $bookings
     * @param array $options
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function generateReport(User $user, Collection $bookings, array $options = [])
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set report title
        $sheet->setTitle('Booking Report');
        
        // Create header section
        $this->createHeaderSection($sheet, $user, $options);
        
        // Create summary section
        $this->createSummarySection($sheet, $bookings);
        
        // Create bookings data table
        $this->createBookingsTable($sheet, $bookings);
        
        // Style the spreadsheet
        $this->styleSpreadsheet($sheet);
        
        // Generate filename and save
        $fileName = $this->generateFileName($user, $options);
        $filePath = storage_path('app/temp/' . $fileName);
        
        // Ensure temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
        
        return response()->download($filePath, $fileName)->deleteFileAfterSend();
    }

    /**
     * Create header section
     */
    private function createHeaderSection($sheet, User $user, array $options): void
    {
        $sheet->setCellValue('A1', $options['title'] ?? 'Vehicle Rental Booking Report');
        $sheet->setCellValue('A2', 'Customer: ' . $user->name);
        $sheet->setCellValue('A3', 'Email: ' . $user->email);
        $sheet->setCellValue('A4', 'Generated: ' . Carbon::now()->format('Y-m-d H:i:s'));
        $sheet->setCellValue('A5', '');
    }

    /**
     * Create summary section
     */
    private function createSummarySection($sheet, Collection $bookings): void
    {
        $stats = $this->calculateStats($bookings);
        
        $row = 6;
        $sheet->setCellValue("A{$row}", 'SUMMARY STATISTICS');
        $row++;
        
        $summaryData = [
            ['Total Bookings', $stats['totalBookings']],
            ['Active Bookings', $stats['activeBookings']],
            ['Completed Bookings', $stats['completedBookings']],
            ['Cancelled Bookings', $stats['cancelledBookings']],
            ['Total Amount Paid', 'RM ' . number_format($stats['totalAmountPaid'], 2)],
            ['Pending Payments', 'RM ' . number_format($stats['pendingPayments'], 2)],
            ['Average Booking Value', 'RM ' . number_format($stats['averageBookingValue'], 2)],
            ['Total Rental Days', $stats['totalRentalDays']],
        ];
        
        foreach ($summaryData as $data) {
            $sheet->setCellValue("A{$row}", $data[0]);
            $sheet->setCellValue("B{$row}", $data[1]);
            $row++;
        }
        
        $sheet->setCellValue("A" . ($row + 1), '');
    }

    /**
     * Create bookings data table
     */
    private function createBookingsTable($sheet, Collection $bookings): void
    {
        $startRow = 16;
        
        // Table headers
        $headers = [
            'A' => 'Booking Number',
            'B' => 'Vehicle',
            'C' => 'Pickup Date',
            'D' => 'Return Date',
            'E' => 'Days',
            'F' => 'Status',
            'G' => 'Payment Status',
            'H' => 'Total Amount (RM)'
        ];
        
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $startRow, $header);
        }
        
        // Table data
        $row = $startRow + 1;
        foreach ($bookings as $booking) {
            $sheet->setCellValue("A{$row}", $booking->booking_number);
            $sheet->setCellValue("B{$row}", $booking->vehicle->make . ' ' . $booking->vehicle->model);
            $sheet->setCellValue("C{$row}", $booking->pickup_datetime->format('Y-m-d'));
            $sheet->setCellValue("D{$row}", $booking->return_datetime->format('Y-m-d'));
            $sheet->setCellValue("E{$row}", $booking->rental_days);
            $sheet->setCellValue("F{$row}", ucfirst($booking->status));
            $sheet->setCellValue("G{$row}", ucfirst(str_replace('_', ' ', $booking->payment_status)));
            $sheet->setCellValue("H{$row}", number_format($booking->total_amount, 2));
            $row++;
        }
    }

    /**
     * Apply styling to spreadsheet
     */
    private function styleSpreadsheet($sheet): void
    {
        // Auto-size columns
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Style title
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        
        // Style summary section
        $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A6:B13')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E8F4FD');
        
        // Style table headers
        $sheet->getStyle('A16:H16')->getFont()->setBold(true);
        $sheet->getStyle('A16:H16')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D1E7DD');
        
        // Center align headers
        $sheet->getStyle('A16:H16')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    /**
     * Calculate statistics
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

    public function getFormatName(): string
    {
        return 'Excel';
    }

    public function getFileExtension(): string
    {
        return 'xlsx';
    }

    public function getMimeType(): string
    {
        return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    }

    public function isAvailable(): bool
    {
        return class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet');
    }

    private function generateFileName(User $user, array $options): string
    {
        $prefix = $options['filename_prefix'] ?? 'booking-report';
        $date = date('Y-m-d');
        return "{$prefix}-{$user->id}-{$date}.{$this->getFileExtension()}";
    }
}