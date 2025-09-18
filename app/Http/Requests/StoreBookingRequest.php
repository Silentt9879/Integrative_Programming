<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => 'required|exists:vehicles,id',
            'pickup_date' => [
                'required',
                'date',
                'after_or_equal:today',
                'before:' . now()->addMonths(12)->format('Y-m-d')
            ],
            'pickup_time' => 'required|date_format:H:i',
            'return_date' => 'required|date|after:pickup_date',
            'return_time' => 'required|date_format:H:i',
            'pickup_location' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\.\,\-\(\)]+$/' // Prevent injection
            ],
            'return_location' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\.\,\-\(\)]+$/'
            ],
            'special_requests' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9\s\.\,\!\?\-\(\)]*$/' // Strict character whitelist
            ],
            'customer_phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^[+]?[0-9\s\-\(\)]{8,20}$/' // Phone number format only
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'pickup_date.before' => 'Pickup date cannot be more than 12 months in advance.',
            'customer_phone.regex' => 'Please enter a valid phone number with only numbers, spaces, hyphens, and parentheses.',
            'special_requests.regex' => 'Special requests contain invalid characters. Only letters, numbers, and basic punctuation allowed.',
            'pickup_location.regex' => 'Location contains invalid characters.',
            'return_location.regex' => 'Location contains invalid characters.'
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            // Sanitize inputs during preparation
            'pickup_location' => $this->sanitizeInput($this->pickup_location),
            'return_location' => $this->sanitizeInput($this->return_location),
            'special_requests' => $this->sanitizeInput($this->special_requests),
            'customer_phone' => $this->sanitizePhoneNumber($this->customer_phone)
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Business logic validation
            $pickupDateTime = Carbon::createFromFormat(
                'Y-m-d H:i',
                $this->pickup_date . ' ' . $this->pickup_time
            );
            $returnDateTime = Carbon::createFromFormat(
                'Y-m-d H:i',
                $this->return_date . ' ' . $this->return_time
            );

            // Minimum advance booking time
            if ($pickupDateTime <= now()->addHour()) {
                $validator->errors()->add('pickup_date', 'Booking must be made at least 1 hour in advance.');
            }

            // Maximum rental period
            if ($returnDateTime > $pickupDateTime->addDays(90)) {
                $validator->errors()->add('return_date', 'Maximum rental period is 90 days.');
            }

            // Minimum rental duration
            if ($returnDateTime <= $pickupDateTime->addHours(2)) {
                $validator->errors()->add('return_time', 'Minimum rental duration is 2 hours.');
            }

            // Business hours validation (optional)
            if ($pickupDateTime->hour < 6 || $pickupDateTime->hour > 22) {
                $validator->errors()->add('pickup_time', 'Pickup time must be between 6:00 AM and 10:00 PM.');
            }
        });
    }

    private function sanitizeInput(?string $input): ?string
    {
        if (!$input) return null;

        // Remove HTML tags, trim whitespace, convert entities
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    private function sanitizePhoneNumber(?string $phone): ?string
    {
        if (!$phone) return null;

        // Keep only allowed phone characters
        return preg_replace('/[^0-9+\s\-\(\)]/', '', trim($phone));
    }
}
