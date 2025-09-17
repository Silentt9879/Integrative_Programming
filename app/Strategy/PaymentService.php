<?php
// app/Strategy/PaymentService.php

namespace App\Strategy;

use App\Strategy\PaymentStrategies\PaymentStrategyInterface;
use App\Strategy\PaymentStrategies\CreditCardPaymentStrategy;
use App\Strategy\PaymentStrategies\DebitCardPaymentStrategy;
use App\Strategy\PaymentStrategies\OnlineBankingPaymentStrategy;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    private PaymentStrategyInterface $paymentStrategy;
    private array $strategies;

    public function __construct()
    {
        $this->strategies = [
            'credit_card' => new CreditCardPaymentStrategy(),
            'debit_card' => new DebitCardPaymentStrategy(),
            'online_banking' => new OnlineBankingPaymentStrategy(),
        ];
    }

    /**
     * Set payment strategy
     *
     * @param string $paymentMethod
     * @throws \InvalidArgumentException
     */
    public function setPaymentStrategy(string $paymentMethod): void
    {
        if (!isset($this->strategies[$paymentMethod])) {
            throw new \InvalidArgumentException("Unsupported payment method: {$paymentMethod}");
        }

        $this->paymentStrategy = $this->strategies[$paymentMethod];
    }

    /**
     * Process payment using selected strategy
     *
     * @param Booking $booking
     * @param array $paymentData
     * @return array
     */
    public function processPayment(Booking $booking, array $paymentData): array
    {
        if (!isset($this->paymentStrategy)) {
            throw new \LogicException('Payment strategy not set');
        }

        return DB::transaction(function () use ($booking, $paymentData) {
            // Validate payment data using strategy
            $validationRules = $this->paymentStrategy->validatePaymentData($paymentData);
            
            // Process payment using strategy
            $result = $this->paymentStrategy->processPayment($booking, $paymentData);

            if ($result['success']) {
                $this->handleSuccessfulPayment($booking, $paymentData, $result);
            } else {
                $this->logFailedPayment($booking, $result);
            }

            return $result;
        });
    }

    /**
     * Handle successful payment
     *
     * @param Booking $booking
     * @param array $paymentData
     * @param array $result
     */
    private function handleSuccessfulPayment(Booking $booking, array $paymentData, array $result): void
    {
        // Update booking status
        $booking->update([
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'final_amount' => $paymentData['payment_amount'] ?? $booking->total_amount
        ]);

        // Update vehicle status
        $booking->vehicle->update(['status' => 'rented']);

        // Create payment record
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'payment_reference' => $result['transaction_id'],
            'amount' => $booking->total_amount,
            'payment_method' => $paymentData['payment_method'],
            'payment_type' => 'booking_payment',
            'status' => 'completed',
            'payment_date' => now(),
            'payment_details' => $result
        ]);

        // Generate invoice
        $this->generateInvoice($payment, $booking);

        Log::info('Payment completed successfully', [
            'booking_id' => $booking->id,
            'payment_id' => $payment->id,
            'transaction_id' => $result['transaction_id'],
            'amount' => $booking->total_amount,
            'payment_method' => $paymentData['payment_method']
        ]);
    }

    /**
     * Generate invoice for payment
     *
     * @param Payment $payment
     * @param Booking $booking
     */
    private function generateInvoice(Payment $payment, Booking $booking): void
    {
        $invoiceData = [
            'booking_number' => $booking->booking_number,
            'vehicle' => [
                'make' => $booking->vehicle->make,
                'model' => $booking->vehicle->model,
                'license_plate' => $booking->vehicle->license_plate
            ],
            'rental_period' => [
                'pickup_date' => $booking->pickup_datetime->format('Y-m-d'),
                'return_date' => $booking->return_datetime->format('Y-m-d'),
                'days' => $booking->rental_days
            ],
            'customer' => [
                'name' => $booking->user->name,
                'email' => $booking->user->email
            ]
        ];

        // Calculate amounts
        $subtotal = $booking->total_amount;
        $taxRate = 0.06; // 6% GST
        $taxAmount = $subtotal * $taxRate;
        $totalAmount = $subtotal + $taxAmount;

        Invoice::create([
            'payment_id' => $payment->id,
            'invoice_data' => $invoiceData,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => 0.00,
            'total_amount' => $totalAmount,
            'issued_date' => now()
        ]);
    }

    /**
     * Log failed payment attempt
     *
     * @param Booking $booking
     * @param array $result
     */
    private function logFailedPayment(Booking $booking, array $result): void
    {
        Log::warning('Payment failed', [
            'booking_id' => $booking->id,
            'booking_number' => $booking->booking_number,
            'error_code' => $result['error_code'] ?? 'UNKNOWN',
            'message' => $result['message'] ?? 'Payment failed'
        ]);
    }

    /**
     * Process additional charges payment
     *
     * @param Booking $booking
     * @param array $paymentData
     * @return array
     */
    public function processAdditionalCharges(Booking $booking, array $paymentData): array
    {
        if (!isset($this->paymentStrategy)) {
            throw new \LogicException('Payment strategy not set');
        }

        $additionalPayment = Payment::where('booking_id', $booking->id)
            ->where('payment_type', 'additional_charges')
            ->where('status', 'pending')
            ->firstOrFail();

        return DB::transaction(function () use ($booking, $additionalPayment, $paymentData) {
            $result = $this->paymentStrategy->processPayment($booking, $paymentData);

            if ($result['success']) {
                $additionalPayment->update([
                    'status' => 'completed',
                    'payment_date' => now(),
                    'payment_method' => $paymentData['payment_method'],
                    'payment_details' => $result
                ]);

                $booking->update([
                    'payment_status' => 'paid_with_additional'
                ]);

                Log::info('Additional charges payment completed', [
                    'booking_id' => $booking->id,
                    'payment_id' => $additionalPayment->id,
                    'amount' => $additionalPayment->amount
                ]);
            }

            return $result;
        });
    }

    /**
     * Get available payment methods
     *
     * @return array
     */
    public function getAvailablePaymentMethods(): array
    {
        $availableMethods = [];
        
        foreach ($this->strategies as $key => $strategy) {
            if ($strategy->isAvailable()) {
                $availableMethods[$key] = $strategy->getPaymentMethodName();
            }
        }

        return $availableMethods;
    }

    /**
     * Get validation rules for payment method
     *
     * @param string $paymentMethod
     * @return array
     */
    public function getValidationRules(string $paymentMethod): array
    {
        if (!isset($this->strategies[$paymentMethod])) {
            return [];
        }

        return $this->strategies[$paymentMethod]->validatePaymentData([]);
    }
}