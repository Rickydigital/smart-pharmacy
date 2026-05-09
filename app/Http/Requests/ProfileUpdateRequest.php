<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'first_name' => trim((string) $this->first_name),
            'last_name' => filled($this->last_name) ? trim((string) $this->last_name) : null,
            'phone' => filled($this->phone) ? trim((string) $this->phone) : null,
        ]);
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'phone.max' => 'Phone number is too long.',
        ];
    }
}