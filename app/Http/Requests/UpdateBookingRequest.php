<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:pending,confirmed,active,completed,cancelled',
            'reason' => [
                'required_if:status,cancelled',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\.\,\!\?\-\(\)]*$/' // Prevent injection
            ],
            'damage_charges' => 'nullable|numeric|min:0|max:99999.99',
            'return_notes' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9\s\.\,\!\?\-\(\)]*$/'
            ]
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'reason' => $this->sanitizeInput($this->reason),
            'return_notes' => $this->sanitizeInput($this->return_notes)
        ]);
    }

    private function sanitizeInput(?string $input): ?string
    {
        if (!$input) return null;
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
}
