<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Factory\VehicleFactoryRegistry;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

/**
 * UpdateVehicleRequest - Form Request for vehicle update validation
 *
 * Similar to StoreVehicleRequest but handles update-specific validation
 * Includes unique validation exceptions for current record
 */
class UpdateVehicleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only authenticated admin users can update vehicles
        return Auth::check() && Auth::user()->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $vehicleId = $this->route('id'); // Get vehicle ID from route parameter

        return [
            // Vehicle Basic Information
            'license_plate' => [
                'required',
                'string',
                'max:20',
                Rule::unique('vehicles', 'license_plate')->ignore($vehicleId),
                'regex:/^[A-Z0-9\-\s]+$/i',
            ],
            'make' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z\s\-]+$/',
            ],
            'model' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9\s\-]+$/',
            ],
            'year' => [
                'required',
                'integer',
                'min:1900',
                'max:' . (date('Y') + 2),
            ],
            'color' => [
                'required',
                'string',
                'max:30',
                'regex:/^[a-zA-Z\s]+$/',
            ],

            // Vehicle Type - Must be supported by Factory Pattern
            'type' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!VehicleFactoryRegistry::isSupported($value)) {
                        $supportedTypes = implode(', ', VehicleFactoryRegistry::getSupportedTypes());
                        $fail("The {$attribute} must be one of: {$supportedTypes}");
                    }
                },
            ],

            // Vehicle Specifications
            'seating_capacity' => [
                'required',
                'integer',
                'min:1',
                'max:15',
            ],
            'fuel_type' => [
                'required',
                'string',
                'in:Petrol,Diesel,Electric,Hybrid',
            ],
            'current_mileage' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ],
            'status' => [
                'required',
                'string',
                'in:available,rented,maintenance',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            // Image Handling - Local Upload Only
            'image' => [
                'nullable', // Keep for updates
                'file',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:5120',
                'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
            ],

            // Rental Rate Information
            'daily_rate' => [
                'required',
                'numeric',
                'min:1',
                'max:9999.99',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
            'weekly_rate' => [
                'nullable',
                'numeric',
                'min:1',
                'max:99999.99',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
            'monthly_rate' => [
                'nullable',
                'numeric',
                'min:1',
                'max:999999.99',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'license_plate.regex' => 'License plate can only contain letters, numbers, hyphens, and spaces.',
            'make.regex' => 'Make can only contain letters, spaces, and hyphens.',
            'model.regex' => 'Model can only contain letters, numbers, spaces, and hyphens.',
            'color.regex' => 'Color can only contain letters and spaces.',
            'daily_rate.regex' => 'Daily rate must be in valid currency format (e.g., 99.99).',
            'weekly_rate.regex' => 'Weekly rate must be in valid currency format (e.g., 99.99).',
            'monthly_rate.regex' => 'Monthly rate must be in valid currency format (e.g., 99.99).',
            'image.dimensions' => 'Image must be between 100x100 and 2000x2000 pixels.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'license_plate' => 'license plate',
            'seating_capacity' => 'seating capacity',
            'current_mileage' => 'current mileage',
            'fuel_type' => 'fuel type',
            'daily_rate' => 'daily rate',
            'weekly_rate' => 'weekly rate',
            'monthly_rate' => 'monthly rate',
            'image_url' => 'image URL',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'license_plate' => strtoupper(trim($this->license_plate)),
            'make' => ucwords(strtolower(trim($this->make))),
            'model' => ucwords(strtolower(trim($this->model))),
            'color' => ucwords(strtolower(trim($this->color))),
            'type' => ucwords(strtolower(trim($this->type))),
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Business logic validation same as store request
            if ($this->hasFile('image') && $this->filled('image_url')) {
                $validator->errors()->add('image', 'Please provide either an image file or URL, not both.');
            }

            if ($this->filled('daily_rate') && $this->filled('weekly_rate')) {
                if ($this->weekly_rate < ($this->daily_rate * 6)) {
                    $validator->errors()->add('weekly_rate', 'Weekly rate should be at least 6 times the daily rate.');
                }
            }

            if ($this->filled('weekly_rate') && $this->filled('monthly_rate')) {
                if ($this->monthly_rate < ($this->weekly_rate * 3.5)) {
                    $validator->errors()->add('monthly_rate', 'Monthly rate should be at least 3.5 times the weekly rate.');
                }
            }
        });
    }
}
