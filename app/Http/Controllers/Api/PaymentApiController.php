<?php
// app/Http/Controllers/Api/PaymentApiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Strategy\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;

class PaymentApiController extends Controller
{
    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
        $this->middleware('auth:sanctum');
        $this->middleware('throttle:payment,30,1'); // Rate limiting: 30 requests per minute
    }

    /**
     * Get available payment methods
     * 
     * @return JsonResponse
     */
    public function getPaymentMethods(): JsonResponse
    {
        try {
            $paymentMethods = $this->paymentService->getAvailablePaymentMethods();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'payment_methods' => $paymentMethods
                ],
                'message' => 'Payment methods retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('API: Failed to get payment methods', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment methods',
                'error_code' => 'PAYMENT_METHODS_ERROR'
            ], 500);
        }
    }

    /**
     * Initialize payment process
     * 
     * @param Request $request
     * @param int $bookingId
     * @return JsonResponse
     */
    public function initializePayment(Request $request, int $bookingId): JsonResponse
    {
        // Rate limiting check
        $key = 'payment-init:' . Auth::id() . ':' . $bookingId;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many payment attempts. Please try again later.',
                'error_code' => 'RATE_LIMIT_EXCEEDED'
            ], 429);
        }

        RateLimiter::hit($key, 60); // 5 attempts per minute

        try {
            $booking = Booking::with(['vehicle', 'user'])
                ->where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->where('payment_status', 'pending')
                ->firstOrFail();

            $paymentMethods = $this->paymentService->getAvailablePaymentMethods();

            return response()->json([
                'success' => true,
                'data' => [
                    'booking' => [
                        'id' => $booking->id,
                        'booking_number' => $booking->booking_number,
                        'total_amount' => $booking->total_amount,
                        'currency' => 'MYR',
                        'vehicle' => [
                            'make' => $booking->vehicle->make,
                            'model' => $booking->vehicle->model
                        ]
                    ],
                    'payment_methods' => $paymentMethods,
                    'payment_token' => encrypt([
                        'booking_id' => $booking->id,
                        'user_id' => Auth::id(),
                        'timestamp' => time()
                    ])
                ],
                'message' => 'Payment initialized successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found or not available for payment',
                'error_code' => 'BOOKING_NOT_FOUND'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('API: Payment initialization failed', [
                'booking_id' => $bookingId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment initialization failed',
                'error_code' => 'INITIALIZATION_ERROR'
            ], 500);
        }
    }

    /**
     * Process payment via API
     * 
     * @param Request $request
     * @param int $bookingId
     * @return JsonResponse
     */
    public function processPayment(Request $request, int $bookingId): JsonResponse
    {
        // Enhanced input validation for API
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:credit_card,debit_card,online_banking',
            'payment_token' => 'required|string',
            'card_number' => 'nullable|required_if:payment_method,credit_card,debit_card|string|min:16|max:19',
            'card_holder' => 'nullable|required_if:payment_method,credit_card,debit_card|string|max:255',
            'expiry_month' => 'nullable|required_if:payment_method,credit_card,debit_card|integer|between:1,12',
            'expiry_year' => 'nullable|required_if:payment_method,credit_card,debit_card|integer|min:2025',
            'cvv' => 'nullable|required_if:payment_method,credit_card,debit_card|string|min:3|max:4',
            'bank_name' => 'nullable|required_if:payment_method,online_banking|string',
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
            // Verify payment token (Security measure)
            $tokenData = decrypt($request->payment_token);
            if ($tokenData['booking_id'] != $bookingId || $tokenData['user_id'] != Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payment token',
                    'error_code' => 'INVALID_TOKEN'
                ], 403);
            }

            // Check token expiry (5 minutes)
            if (time() - $tokenData['timestamp'] > 300) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment token expired',
                    'error_code' => 'TOKEN_EXPIRED'
                ], 403);
            }

            $booking = Booking::with(['vehicle'])
                ->where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->where('payment_status', 'pending')
                ->firstOrFail();

            // Set payment strategy
            $paymentMethod = $request->payment_method;
            $this->paymentService->setPaymentStrategy($paymentMethod);

            // Prepare sanitized payment data
            $paymentData = [
                'payment_method' => strip_tags($paymentMethod),
                'payment_amount' => $booking->total_amount
            ];

            // Add method-specific data with sanitization
            if (in_array($paymentMethod, ['credit_card', 'debit_card'])) {
                $paymentData = array_merge($paymentData, [
                    'card_number' => preg_replace('/\s/', '', $request->card_number),
                    'card_holder' => strip_tags($request->card_holder),
                    'expiry_month' => intval($request->expiry_month),
                    'expiry_year' => intval($request->expiry_year),
                    'cvv' => strip_tags($request->cvv)
                ]);
            } elseif ($paymentMethod === 'online_banking') {
                $paymentData['bank_name'] = strip_tags($request->bank_name);
            }

            // Process payment using strategy pattern
            $result = $this->paymentService->processPayment($booking, $paymentData);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'transaction_id' => $result['transaction_id'],
                        'payment_method' => $result['payment_method'],
                        'amount' => $booking->total_amount,
                        'currency' => 'MYR',
                        'booking_status' => 'confirmed'
                    ],
                    'message' => $result['message']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'error_code' => $result['error_code']
                ], 400);
            }

        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid payment token',
                'error_code' => 'INVALID_TOKEN'
            ], 403);
        } catch (\Exception $e) {
            \Log::error('API: Payment processing failed', [
                'booking_id' => $bookingId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed',
                'error_code' => 'PROCESSING_ERROR'
            ], 500);
        }
    }

    /**
     * Get payment status
     * 
     * @param int $bookingId
     * @return JsonResponse
     */
    public function getPaymentStatus(int $bookingId): JsonResponse
    {
        try {
            $booking = Booking::with(['vehicle'])
                ->where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $payment = Payment::where('booking_id', $booking->id)
                ->where('payment_type', 'booking_payment')
                ->latest()
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'booking_id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'payment_status' => $booking->payment_status,
                    'booking_status' => $booking->status,
                    'total_amount' => $booking->total_amount,
                    'currency' => 'MYR',
                    'payment_details' => $payment ? [
                        'payment_id' => $payment->id,
                        'payment_reference' => $payment->payment_reference,
                        'payment_method' => $payment->payment_method,
                        'payment_date' => $payment->payment_date,
                        'status' => $payment->status
                    ] : null
                ],
                'message' => 'Payment status retrieved successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
                'error_code' => 'BOOKING_NOT_FOUND'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment status',
                'error_code' => 'STATUS_ERROR'
            ], 500);
        }
    }

    /**
     * Get payment history for user
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getPaymentHistory(Request $request): JsonResponse
    {
        try {
            $perPage = min($request->input('per_page', 15), 50); // Max 50 items per page
            
            $payments = Payment::with(['booking.vehicle'])
                ->whereHas('booking', function($query) {
                    $query->where('user_id', Auth::id());
                })
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $paymentData = $payments->map(function($payment) {
                return [
                    'payment_id' => $payment->id,
                    'payment_reference' => $payment->payment_reference,
                    'booking_number' => $payment->booking->booking_number,
                    'vehicle' => $payment->booking->vehicle->make . ' ' . $payment->booking->vehicle->model,
                    'amount' => $payment->amount,
                    'currency' => 'MYR',
                    'payment_method' => $payment->payment_method,
                    'payment_type' => $payment->payment_type,
                    'status' => $payment->status,
                    'payment_date' => $payment->payment_date,
                    'created_at' => $payment->created_at
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'payments' => $paymentData,
                    'pagination' => [
                        'current_page' => $payments->currentPage(),
                        'last_page' => $payments->lastPage(),
                        'per_page' => $payments->perPage(),
                        'total' => $payments->total()
                    ]
                ],
                'message' => 'Payment history retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment history',
                'error_code' => 'HISTORY_ERROR'
            ], 500);
        }
    }

    /**
     * Validate payment data
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function validatePaymentData(Request $request): JsonResponse
    {
        try {
            $paymentMethod = $request->input('payment_method');
            
            if (!$paymentMethod) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment method is required',
                    'error_code' => 'MISSING_METHOD'
                ], 400);
            }

            $validationRules = $this->paymentService->getValidationRules($paymentMethod);
            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'error_code' => 'VALIDATION_ERROR'
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment data is valid',
                'data' => [
                    'payment_method' => $paymentMethod,
                    'validation_passed' => true
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation check failed',
                'error_code' => 'VALIDATION_CHECK_ERROR'
            ], 500);
        }
    }
}