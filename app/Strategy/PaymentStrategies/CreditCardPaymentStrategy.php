<?php
// app/Strategy/PaymentStrategies/CreditCardPaymentStrategy.php

namespace App\Strategy\PaymentStrategies;

use App\Models\Booking;
use Illuminate\Support\Facades\Log;

class CreditCardPaymentStrategy implements PaymentStrategyInterface
{
    /**
     * Process credit card payment
     *
     * @param Booking $booking
     * @param array $paymentData
     * @return array
     */
    public function processPayment(Booking $booking, array $paymentData): array
    {
        // Simulate credit card processing with enhanced validation
        $cardNumber = $this->maskCardNumber($paymentData['card_number']);
        
        Log::info('Processing credit card payment', [
            'booking_id' => $booking->id,
            'payment_method' => 'credit_card',
            'amount' => $booking->total_amount,
            'card_last_four' => substr($paymentData['card_number'], -4)
        ]);

        // Simulate payment gateway processing (95% success rate for demo)
        $success = rand(1, 100) <= 95;
        
        if ($success) {
            return [
                'success' => true,
                'transaction_id' => 'CC_TXN_' . time() . rand(1000, 9999),
                'payment_method' => 'credit_card',
                'message' => 'Credit card payment processed successfully',
                'gateway_response' => 'APPROVED',
                'card_info' => $cardNumber
            ];
        }

        return [
            'success' => false,
            'error_code' => 'CC_DECLINED',
            'message' => 'Credit card payment declined. Please check your card details.',
            'gateway_response' => 'DECLINED'
        ];
    }

    /**
     * Validate credit card payment data
     *
     * @param array $paymentData
     * @return array
     */
    public function validatePaymentData(array $paymentData): array
    {
        $rules = [
            'card_number' => 'required|string|min:16|max:19',
            'card_holder' => 'required|string|max:255',
            'expiry_month' => 'required|integer|between:1,12',
            'expiry_year' => 'required|integer|min:2025',
            'cvv' => 'required|string|min:3|max:4',
        ];

        // Additional credit card specific validation
        $customValidation = [];
        
        if (isset($paymentData['card_number'])) {
            $cardNumber = preg_replace('/\s/', '', $paymentData['card_number']);
            if (!$this->isValidCardNumber($cardNumber)) {
                $customValidation['card_number'] = ['Invalid credit card number'];
            }
        }

        return array_merge($rules, $customValidation);
    }

    /**
     * Get payment method name
     *
     * @return string
     */
    public function getPaymentMethodName(): string
    {
        return 'Credit Card';
    }

    /**
     * Check if payment method is available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        // Could check with payment gateway availability
        return true;
    }

    /**
     * Validate credit card number using Luhn algorithm (basic validation)
     *
     * @param string $cardNumber
     * @return bool
     */
    private function isValidCardNumber(string $cardNumber): bool
    {
        $cardNumber = preg_replace('/\D/', '', $cardNumber);
        
        if (strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
            return false;
        }

        // Basic Luhn algorithm check
        $sum = 0;
        $length = strlen($cardNumber);
        
        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = (int) $cardNumber[$i];
            
            if (($length - $i) % 2 === 0) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit = ($digit % 10) + 1;
                }
            }
            
            $sum += $digit;
        }
        
        return ($sum % 10) === 0;
    }

    /**
     * Mask card number for security
     *
     * @param string $cardNumber
     * @return string
     */
    private function maskCardNumber(string $cardNumber): string
    {
        $cleanNumber = preg_replace('/\s/', '', $cardNumber);
        $lastFour = substr($cleanNumber, -4);
        $maskedPart = str_repeat('*', strlen($cleanNumber) - 4);
        
        return $maskedPart . $lastFour;
    }
}