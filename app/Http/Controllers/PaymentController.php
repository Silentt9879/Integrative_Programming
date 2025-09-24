<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{

    /**
     * Show payment form for a booking
     */
    public function showPayment($bookingId)
    {
        $booking = Booking::with(['vehicle', 'vehicle.rentalRate'])
            ->where('id', $bookingId)
            ->where('user_id', Auth::id())
            ->where('payment_status', 'pending')
            ->firstOrFail();

        return view('payment.form', compact('booking'));
    }

    public function processPayment(Request $request, $bookingId)
    {
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
            ->firstOrFail();

        // Check if this is additional payment
        $isAdditional = session('is_additional_payment', false);

        if (!$isAdditional && $booking->payment_status != 'pending') {
            return redirect()->route('booking.show', $bookingId)
                ->with('error', 'This booking has already been paid.');
        }

        // Simulate payment processing delay
        return view('payment.processing', [
            'booking' => $booking,
            'payment_data' => $validated
        ]);
    }

    public function completePayment(Request $request, $bookingId)
    {
        $booking = Booking::with(['vehicle'])
            ->where('id', $bookingId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Check if this is additional payment
        $isAdditional = session('is_additional_payment', false);

        $success = rand(1, 100) <= 95;

        if ($success) {
            if ($isAdditional) {
                // Clear additional charges
                $booking->update([
                    'damage_charges' => 0,
                    'late_fees' => 0
                ]);

                // Clear session
                session()->forget('is_additional_payment');
                session()->forget('original_booking_amount');

                // Restore original amount if needed
                if ($originalAmount = session('original_booking_amount')) {
                    $booking->total_amount = $originalAmount;
                }

                $message = 'Additional charges paid successfully!';
            } else {
                // Regular payment
                $booking->update([
                    'payment_status' => 'paid',
                    'final_amount' => $request->input('amount', $booking->total_amount)
                ]);

                // Use state pattern to confirm booking
                $confirmed = $booking->confirm();

                if (!$confirmed) {
                    Log::error("Failed to confirm booking {$booking->id} after payment");
                    $booking->update(['payment_status' => 'pending']);
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment successful but booking confirmation failed. Please contact support.',
                        'error_code' => 'CONFIRMATION_FAILED'
                    ], 400);
                }

                $message = 'Payment processed successfully!';
            }

            Log::info('Payment completed successfully', [
                'booking_id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'is_additional' => $isAdditional,
                'amount' => $booking->total_amount
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'transaction_id' => 'TXN' . time() . rand(1000, 9999),
                'redirect_url' => route('payment.success', $booking->id)
            ]);
        } else {
            Log::warning('Payment failed', [
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
    public function paymentSuccess($bookingId)
    {
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
    public function paymentFailed($bookingId)
    {
        $booking = Booking::with(['vehicle', 'vehicle.rentalRate'])
            ->where('id', $bookingId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('payment.failed', compact('booking'));
    }

    /**
     * Handle payment retry
     */
    public function retryPayment($bookingId)
    {
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
    public function checkPaymentStatus($bookingId)
    {
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

    // Add this method to PaymentController.php if it doesn't exist or update it:

    /**
     * Show additional charges payment form
     */
    public function showAdditionalCharges($bookingId)
    {
        $booking = Booking::with(['vehicle', 'vehicle.rentalRate'])
            ->where('id', $bookingId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Check for unpaid additional charges
        $additionalCharges = $booking->damage_charges + $booking->late_fees;

        if ($additionalCharges <= 0) {
            return redirect()->route('booking.show', $booking->id)
                ->with('info', 'No additional charges found for this booking.');
        }

        session(['is_additional_payment' => true]);

        $originalAmount = $booking->total_amount;
        $booking->total_amount = $additionalCharges;
        session(['original_booking_amount' => $originalAmount]);

        return view('payment.form', compact('booking'));
    }

    /**
     * Process additional charges (reuse existing processPayment)
     */
    public function processAdditionalCharges(Request $request, $bookingId)
    {
        // Simply redirect to the existing processPayment method
        return $this->processPayment($request, $bookingId);
    }

    /**
     * Show additional charges payment success
     */
    public function additionalChargesSuccess($bookingId)
    {
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
