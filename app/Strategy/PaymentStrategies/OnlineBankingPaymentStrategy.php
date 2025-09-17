<?php
// app/Strategy/PaymentStrategies/OnlineBankingPaymentStrategy.php

namespace App\Strategy\PaymentStrategies;

use App\Models\Booking;
use Illuminate\Support\Facades\Log;

class OnlineBankingPaymentStrategy implements PaymentStrategyInterface
{
    private $supportedBanks = [
        'maybank' => 'Maybank',
        'cimb' => 'CIMB Bank',
        'public_bank' => 'Public Bank',
        'rhb' => 'RHB Bank',
        'hong_leong' => 'Hong Leong Bank',
        'ambank' => 'AmBank'
    ];

    /**
     * Process online banking payment
     *
     * @param Booking $booking
     * @param array $paymentData
     * @return array
     */
    public function processPayment(Booking $booking, array $paymentData): array
    {
        $bankName = $this->supportedBanks[$paymentData['bank_name']] ?? 'Unknown Bank';

        Log::info('Processing online banking payment', [
            'booking_id' => $booking->id,
            'payment_method' => 'online_banking',
            'bank_name' => $bankName,
            'amount' => $booking->total_amount
        ]);

        // Online banking typically has higher success rates
        $success = rand(1, 100) <= 98;
        
        if ($success) {
            return [
                'success' => true,
                'transaction_id' => 'FPX_' . strtoupper($paymentData['bank_name']) . '_' . time() . rand(1000, 9999),
                'payment_method' => 'online_banking',
                'message' => "Payment processed successfully via {$bankName}",
                'gateway_response' => 'SUCCESS',
                'bank_name' => $bankName,
                'fpx_reference' => 'FPX' . time() . rand(100, 999)
            ];
        }

        return [
            'success' => false,
            'error_code' => 'FPX_ERROR',
            'message' => "Online banking payment failed. Please try again or use a different payment method.",
            'gateway_response' => 'FAILED',
            'bank_name' => $bankName
        ];
    }

    /**
     * Validate online banking payment data
     *
     * @param array $paymentData
     * @return array
     */
    public function validatePaymentData(array $paymentData): array
    {
        $rules = [
            'bank_name' => 'required|in:' . implode(',', array_keys($this->supportedBanks))
        ];

        return $rules;
    }

    /**
     * Get payment method name
     *
     * @return string
     */
    public function getPaymentMethodName(): string
    {
        return 'Online Banking';
    }

    /**
     * Check if payment method is available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        // Could check FPX gateway status
        return true;
    }

    /**
     * Get supported banks
     *
     * @return array
     */
    public function getSupportedBanks(): array
    {
        return $this->supportedBanks;
    }
}