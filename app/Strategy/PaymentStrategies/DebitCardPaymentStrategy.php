<?php
// app/Strategy/PaymentStrategies/DebitCardPaymentStrategy.php

namespace App\Strategy\PaymentStrategies;

use App\Models\Booking;
use Illuminate\Support\Facades\Log;

class DebitCardPaymentStrategy implements PaymentStrategyInterface
{
    /**
     * Process debit card payment
     *
     * @param Booking $booking
     * @param array $paymentData
     * @return array
     */
    public function processPayment(Booking $booking, array $paymentData): array
    {
        Log::info('Processing debit card payment', [
            'booking_id' => $booking->id,
            'payment_method' => 'debit_card',
            'amount' => $booking->total_amount,
            'card_last_four' => substr($paymentData['card_number'], -4)
        ]);

        // Simulate debit card processing with bank verification
        // Debit cards might have different success rates due to insufficient funds
        $success = rand(1, 100) <= 90; // 90% success rate for debit cards
        
        if ($success) {
            return [
                'success' => true,
                'transaction_id' => 'DC_TXN_' . time() . rand(1000, 9999),
                'payment_method' => 'debit_card',
                'message' => 'Debit card payment processed successfully',
                'gateway_response' => 'APPROVED',
                'bank_reference' => 'BANK_REF_' . rand(100000, 999999)
            ];
        }

        $errorMessages = [
            'Insufficient funds in account',
            'Card temporarily blocked',
            'Transaction limit exceeded',
            'Invalid PIN attempts'
        ];

        return [
            'success' => false,
            'error_code' => 'DC_DECLINED',
            'message' => $errorMessages[array_rand($errorMessages)],
            'gateway_response' => 'DECLINED'
        ];
    }

    /**
     * Validate debit card payment data
     *
     * @param array $paymentData
     * @return array
     */
    public function validatePaymentData(array $paymentData): array
    {
        return [
            'card_number' => 'required|string|min:16|max:19',
            'card_holder' => 'required|string|max:255',
            'expiry_month' => 'required|integer|between:1,12',
            'expiry_year' => 'required|integer|min:2025',
            'cvv' => 'required|string|min:3|max:4',
        ];
    }

    /**
     * Get payment method name
     *
     * @return string
     */
    public function getPaymentMethodName(): string
    {
        return 'Debit Card';
    }

    /**
     * Check if payment method is available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return true;
    }
}