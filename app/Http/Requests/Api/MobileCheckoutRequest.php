<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MobileCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => [
                'required',
                'integer',
                Rule::exists('branches', 'id'),
            ],

            'sale_type' => [
                'required',
                Rule::in(['retail', 'wholesale']),
            ],

            'customer_name' => ['nullable', 'string', 'max:120'],
            'customer_phone' => ['nullable', 'string', 'max:40'],

            'payment_method' => [
                'required',
                Rule::in(['cash', 'mobile_money', 'card', 'bank', 'credit']),
            ],

            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],

            'notes' => ['nullable', 'string'],

            'mobile_reference' => ['nullable', 'string', 'max:120'],
            'device_name' => ['nullable', 'string', 'max:120'],
            'app_version' => ['nullable', 'string', 'max:50'],
            'synced_at' => ['nullable', 'date'],
            'offline_created_at' => ['nullable', 'date'],

            'items' => ['required', 'array', 'min:1'],

            'items.*.product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id'),
            ],

            'items.*.product_unit_id' => [
                'required',
                'integer',
                Rule::exists('product_units', 'id'),
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'min:1',
            ],

            'items.*.unit_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'items.*.line_discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ];
    }
}