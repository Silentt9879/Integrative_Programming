<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller {

    /**
     * Show payment form for a booking
     */
    public function showPayment($bookingId) {
        $booking = Booking::with(['vehicle', 'vehicle.rentalRate'])
                ->where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->where('payment_status', 'pending')
                ->firstOrFail();

        return view('payment.form', compact('booking'));
    }

    public function processPayment(Request $request, $bookingId) {
        $validated = $request->validate([
            'payment_method' => 'required|in:credit_card,debit_card,online_banking',
            'card_number' => 'nullable|required_if:payment_method,credit_card,debit_card|string|min:16|max:19',
            'card_holder' => 'nullable|required_if:payment_method,credit_card,debit_card|string|max:255',
            'expiry_month' => 'nullable|required_if:payment_method,credit_card,debit_card|integer|between:1,12',
            'expiry_year' => 'nullable|required_if:payment_method,credit_card,debit_card|integer|min:2025',
            'cvv' => 'nullable|required_if:payment_method,credit_card,debit_card|string|min:3|max:4',
            'bank_name' => 'nullable|required_if:payment_method,online_banking|string',
            'payment_amount' => 'required|numeric|min:0'
        ]);
        $booking = Booking::with(['vehicle'])
                ->where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->where('payment_status', 'pending')
                ->firstOrFail();

        // Simulate payment processing delay
        return view('payment.processing', [
            'booking' => $booking,
            'payment_data' => $validated
        ]);
    }

    public function completePayment(Request $request, $bookingId) {
        $booking = Booking::with(['vehicle'])
                ->where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->where('payment_status', 'pending')
                ->firstOrFail();

        $success = rand(1, 100) <= 95;

        if ($success) {
    // Update payment status first
    $booking->update([
        'payment_status' => 'paid',
        'final_amount' => $request->input('amount', $booking->total_amount)
    ]);

    // Use state pattern to confirm booking (this will update vehicle status to 'rented')
    $confirmed = $booking->confirm();

    if (!$confirmed) {
        \Illuminate\Support\Facades\Log::error("Failed to confirm booking {$booking->id} after payment");
        // Rollback payment status if confirmation failed
        $booking->update(['payment_status' => 'pending']);
        return response()->json([
            'success' => false,
            'message' => 'Payment successful but booking confirmation failed. Please contact support.',
            'error_code' => 'CONFIRMATION_FAILED'
        ], 400);
    }

            \Illuminate\Support\Facades\Log::info('Payment completed successfully', [
                'booking_id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'vehicle_id' => $booking->vehicle->id,
                'amount' => $booking->total_amount
            ]);

            return response()->json([
                        'success' => true,
                        'message' => 'Payment processed successfully!',
                        'transaction_id' => 'TXN' . time() . rand(1000, 9999),
                        'redirect_url' => route('payment.success', $booking->id)
            ]);
        } else {
            \Illuminate\Support\Facades\Log::warning('Payment failed', [
                'booking_id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'reason' => 'Random failure for demo purposes'
            ]);

            return response()->json([
                        'success' => false,
                        'message' => 'Payment failed. Please try again.',
                        'error_code' => 'PAYMENT_DECLINED'
                            ], 400);
        }
    }

    /**
     * Show payment success page
     */
    public function paymentSuccess($bookingId) {
        $booking = Booking::with(['vehicle', 'vehicle.rentalRate'])
                ->where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->where('payment_status', 'paid')
                ->firstOrFail();

        return view('payment.success', compact('booking'));
    }

    /**
     * Show payment failure page
     */
    public function paymentFailed($bookingId) {
        $booking = Booking::with(['vehicle', 'vehicle.rentalRate'])
                ->where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

        return view('payment.failed', compact('booking'));
    }

    /**
     * Handle payment retry
     */
    public function retryPayment($bookingId) {
        $booking = Booking::with(['vehicle', 'vehicle.rentalRate'])
                ->where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->where('payment_status', 'pending')
                ->firstOrFail();

        return redirect()->route('payment.form', $booking->id)
                        ->with('info', 'Please complete your payment to confirm the booking.');
    }

   /**
     * Check payment status (AJAX endpoint)
     */
    public function checkPaymentStatus($bookingId) {
        $booking = Booking::where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

        return response()->json([
                    'booking_id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'payment_status' => $booking->payment_status,
                    'status' => $booking->status,
                    'total_amount' => $booking->total_amount,
                    'is_paid' => $booking->payment_status === 'paid'
        ]);
    }

    /**
     * Show additional charges payment form
     */
    public function showAdditionalCharges($bookingId) {
        $booking = Booking::with(['vehicle', 'vehicle.rentalRate'])
                ->where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->where('payment_status', 'additional_charges_pending')
                ->firstOrFail();

        // Get the pending additional charges payment
        $additionalPayment = \App\Models\Payment::where('booking_id', $booking->id)
                ->where('payment_type', 'additional_charges')
                ->where('status', 'pending')
                ->first();

        if (!$additionalPayment) {
            return redirect()->route('booking.show', $booking->id)
                            ->with('error', 'No additional charges found for this booking.');
        }

        return view('payment.additional-charges', compact('booking', 'additionalPayment'));
    }

    /**
     * Process additional charges payment
     */
    public function processAdditionalCharges(Request $request, $bookingId) {
        $validated = $request->validate([
            'payment_method' => 'required|in:credit_card,debit_card,online_banking',
            'card_number' => 'nullable|required_if:payment_method,credit_card,debit_card|string|min:16|max:19',
            'card_holder' => 'nullable|required_if:payment_method,credit_card,debit_card|string|max:255',
            'expiry_month' => 'nullable|required_if:payment_method,credit_card,debit_card|integer|between:1,12',
            'expiry_year' => 'nullable|required_if:payment_method,credit_card,debit_card|integer|min:2025',
            'cvv' => 'nullable|required_if:payment_method,credit_card,debit_card|string|min:3|max:4',
            'bank_name' => 'nullable|required_if:payment_method,online_banking|string',
        ]);

        $booking = Booking::with(['vehicle'])
                ->where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->where('payment_status', 'additional_charges_pending')
                ->firstOrFail();

        $additionalPayment = \App\Models\Payment::where('booking_id', $booking->id)
                ->where('payment_type', 'additional_charges')
                ->where('status', 'pending')
                ->firstOrFail();

        return view('payment.processing-additional', [
            'booking' => $booking,
            'additionalPayment' => $additionalPayment,
            'payment_data' => $validated
        ]);
    }

    /**
     * Complete additional charges payment
     */
    public function completeAdditionalCharges(Request $request, $bookingId) {
        $booking = Booking::with(['vehicle'])
                ->where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->where('payment_status', 'additional_charges_pending')
                ->firstOrFail();

        $additionalPayment = \App\Models\Payment::where('booking_id', $booking->id)
                ->where('payment_type', 'additional_charges')
                ->where('status', 'pending')
                ->firstOrFail();

        // Simulate payment processing (95% success rate)
        $success = rand(1, 100) <= 95;

        if ($success) {
            // Update payment status
            $additionalPayment->update([
                'status' => 'completed',
                'payment_date' => now(),
                'payment_method' => $request->input('payment_method', 'credit_card')
            ]);

            // Update booking payment status
            $booking->update([
                'payment_status' => 'paid_with_additional'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Additional charges payment processed successfully!',
                'transaction_id' => 'TXN' . time() . rand(1000, 9999),
                'redirect_url' => route('payment.additional-success', $booking->id)
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Payment failed. Please try again.',
                'error_code' => 'PAYMENT_DECLINED'
            ], 400);
        }
    }

    /**
     * Show additional charges payment success
     */
    public function additionalChargesSuccess($bookingId) {
        $booking = Booking::with(['vehicle', 'vehicle.rentalRate'])
                ->where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->where('payment_status', 'paid_with_additional')
                ->firstOrFail();

        $additionalPayment = \App\Models\Payment::where('booking_id', $booking->id)
                ->where('payment_type', 'additional_charges')
                ->where('status', 'completed')
                ->first();

        return view('payment.additional-success', compact('booking', 'additionalPayment'));
    }
}
