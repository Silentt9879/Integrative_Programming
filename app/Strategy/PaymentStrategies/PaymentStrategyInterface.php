<?php
// app/Strategy/PaymentStrategies/PaymentStrategyInterface.php

namespace App\Strategy\PaymentStrategies;

use App\Models\Booking;

interface PaymentStrategyInterface
{
    /**
     * Process payment using the specific payment method
     *
     * @param Booking $booking
     * @param array $paymentData
     * @return array
     */
    public function processPayment(Booking $booking, array $paymentData): array;

    /**
     * Validate payment data specific to this payment method
     *
     * @param array $paymentData
     * @return array
     */
    public function validatePaymentData(array $paymentData): array;

    /**
     * Get payment method name
     *
     * @return string
     */
    public function getPaymentMethodName(): string;

    /**
     * Check if payment method is available
     *
     * @return bool
     */
    public function isAvailable(): bool;
}