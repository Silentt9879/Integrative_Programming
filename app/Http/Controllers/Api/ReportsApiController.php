<?php
// app/Http/Controllers/Api/ReportsApiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Strategy\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ReportsApiController extends Controller
{
    private ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
        $this->middleware('auth:sanctum');
        $this->middleware('throttle:reports,10,1'); // Rate limiting: 10 reports per minute
    }

    /**
     * Get available report formats
     * 
     * @return JsonResponse
     */
    public function getAvailableFormats(): JsonResponse
    {
        try {
            $formats = $this->reportService->getAvailableFormats();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'available_formats' => $formats,
                    'total_formats' => count($formats)
                ],
                'message' => 'Available report formats retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('API: Failed to get report formats', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve report formats',
                'error_code' => 'FORMATS_ERROR'
            ], 500);
        }
    }

    /**
     * Generate booking report via API
     * 
     * @param Request $request
     * @return mixed
     */
    public function generateBookingReport(Request $request)
    {
        // Enhanced validation for API
        $validator = Validator::make($request->all(), [
            'format' => 'required|in:pdf,excel,csv',
            'title' => 'nullable|string|max:255',
            'date_from' => 'nullable|date|before_or_equal:today',
            'date_to' => 'nullable|date|after_or_equal:date_from|before_or_equal:today',
            'status' => 'nullable|array',
            'status.*' => 'in:pending,confirmed,active,ongoing,completed,cancelled',
            'payment_status' => 'nullable|array',
            'payment_status.*' => 'in:pending,paid,failed,refunded,additional_charges_pending,paid_with_additional',
            'include_summary' => 'boolean',
            'delivery_method' => 'in:download,email', // Future feature
            'async' => 'boolean' // For large reports
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'error_code' => 'VALIDATION_ERROR'
            ], 422);
        }

        $user = Auth::user();
        $format = strip_tags($request->format);

        try {
            // Check if format is supported
            if (!$this->reportService->isFormatSupported($format)) {
                return response()->json([
                    'success' => false,
                    'message' => "Report format '{$format}' is not supported or available",
                    'error_code' => 'UNSUPPORTED_FORMAT'
                ], 400);
            }

            // Set report generation strategy
            $this->reportService->setReportStrategy($format);

            // Prepare sanitized options
            $options = [
                'title' => strip_tags($request->title ?? 'API Generated Booking Report'),
                'filename_prefix' => 'api-booking-report',
                'include_summary' => $request->boolean('include_summary', true),
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'status' => $request->status,
                'payment_status' => $request->payment_status,
                'api_generated' => true,
                'generated_by' => $user->name,
                'generated_at' => now()
            ];

            // Log API report generation
            \Log::info('API: Generating booking report', [
                'user_id' => $user->id,
                'format' => $format,
                'options' => $options
            ]);

            // Generate report using strategy pattern
            return $this->reportService->generateUserBookingReport($user, $options);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid report format: ' . $format,
                'error_code' => 'INVALID_FORMAT'
            ], 400);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Report format not available: ' . $e->getMessage(),
                'error_code' => 'FORMAT_UNAVAILABLE'
            ], 503);
        } catch (\Exception $e) {
            \Log::error('API: Report generation failed', [
                'user_id' => $user->id,
                'format' => $format,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Report generation failed',
                'error_code' => 'GENERATION_ERROR'
            ], 500);
        }
    }

    /**
     * Get booking summary statistics
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getBookingSummary(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date_from' => 'nullable|date|before_or_equal:today',
            'date_to' => 'nullable|date|after_or_equal:date_from|before_or_equal:today',
            'status_filter' => 'nullable|array',
            'status_filter.*' => 'in:pending,confirmed,active,ongoing,completed,cancelled',
            'payment_filter' => 'nullable|array',
            'payment_filter.*' => 'in:pending,paid,failed,refunded'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'error_code' => 'VALIDATION_ERROR'
            ], 422);
        }

        try {
            $user = Auth::user();
            
            // Get filtered bookings
            $query = \App\Models\Booking::with(['vehicle'])
                ->where('user_id', $user->id);

            // Apply filters
            if ($request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->status_filter) {
                $query->whereIn('status', $request->status_filter);
            }

            if ($request->payment_filter) {
                $query->whereIn('payment_status', $request->payment_filter);
            }

            $bookings = $query->get();
            $summary = $this->reportService->generateBookingSummary($bookings);

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'filters_applied' => [
                        'date_from' => $request->date_from,
                        'date_to' => $request->date_to,
                        'status_filter' => $request->status_filter,
                        'payment_filter' => $request->payment_filter
                    ],
                    'generated_at' => now()
                ],
                'message' => 'Booking summary retrieved successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('API: Failed to generate booking summary', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate booking summary',
                'error_code' => 'SUMMARY_ERROR'
            ], 500);
        }
    }

    /**
     * Check report generation status (for async reports)
     * 
     * @param Request $request
     * @param string $reportId
     * @return JsonResponse
     */
    public function getReportStatus(Request $request, string $reportId): JsonResponse
    {
        try {
            // This would typically check a job queue or database for report status
            // For now, we'll simulate the functionality
            
            $reportExists = Storage::disk('temp')->exists("reports/{$reportId}.json");
            
            if ($reportExists) {
                $reportData = json_decode(Storage::disk('temp')->get("reports/{$reportId}.json"), true);
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'report_id' => $reportId,
                        'status' => $reportData['status'] ?? 'completed',
                        'progress' => $reportData['progress'] ?? 100,
                        'download_url' => $reportData['download_url'] ?? null,
                        'expires_at' => $reportData['expires_at'] ?? null
                    ],
                    'message' => 'Report status retrieved successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Report not found',
                'error_code' => 'REPORT_NOT_FOUND'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check report status',
                'error_code' => 'STATUS_CHECK_ERROR'
            ], 500);
        }
    }

    /**
     * Validate report request parameters
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function validateReportRequest(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'format' => 'required|in:pdf,excel,csv',
                'date_from' => 'nullable|date|before_or_equal:today',
                'date_to' => 'nullable|date|after_or_equal:date_from|before_or_equal:today',
                'status' => 'nullable|array',
                'payment_status' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'error_code' => 'VALIDATION_ERROR'
                ], 422);
            }

            // Check format availability
            $format = $request->format;
            $isSupported = $this->reportService->isFormatSupported($format);
            $formatDetails = $this->reportService->getFormatDetails($format);

            return response()->json([
                'success' => true,
                'data' => [
                    'validation_passed' => true,
                    'format_supported' => $isSupported,
                    'format_details' => $formatDetails,
                    'estimated_records' => $this->estimateRecordCount($request),
                    'estimated_file_size' => $this->estimateFileSize($request)
                ],
                'message' => 'Report request validation completed'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation check failed',
                'error_code' => 'VALIDATION_CHECK_ERROR'
            ], 500);
        }
    }

    /**
     * Get report generation history
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getReportHistory(Request $request): JsonResponse
    {
        try {
            $perPage = min($request->input('per_page', 10), 50);
            
            // This would typically come from a reports table
            // For now, we'll return a simulated response
            $history = [
                [
                    'id' => 'RPT001',
                    'title' => 'Booking Report - PDF',
                    'format' => 'pdf',
                    'status' => 'completed',
                    'created_at' => now()->subHours(2),
                    'file_size' => '2.3 MB',
                    'download_count' => 1
                ],
                [
                    'id' => 'RPT002',
                    'title' => 'Custom Report - Excel',
                    'format' => 'excel',
                    'status' => 'completed',
                    'created_at' => now()->subDays(1),
                    'file_size' => '1.8 MB',
                    'download_count' => 3
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'reports' => $history,
                    'pagination' => [
                        'current_page' => 1,
                        'per_page' => $perPage,
                        'total' => count($history)
                    ]
                ],
                'message' => 'Report history retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve report history',
                'error_code' => 'HISTORY_ERROR'
            ], 500);
        }
    }

    /**
     * Estimate record count for report (helper method)
     */
    private function estimateRecordCount(Request $request): int
    {
        $query = \App\Models\Booking::where('user_id', Auth::id());
        
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query->count();
    }

    /**
     * Estimate file size for report (helper method)
     */
    private function estimateFileSize(Request $request): string
    {
        $recordCount = $this->estimateRecordCount($request);
        $format = $request->format;
        
        // Rough estimates based on format and record count
        $sizeMultipliers = [
            'pdf' => 0.5,  // KB per record
            'excel' => 0.3,
            'csv' => 0.1
        ];
        
        $estimatedSizeKB = $recordCount * ($sizeMultipliers[$format] ?? 0.3);
        
        if ($estimatedSizeKB > 1024) {
            return round($estimatedSizeKB / 1024, 1) . ' MB';
        }
        
        return round($estimatedSizeKB, 1) . ' KB';
    }
}