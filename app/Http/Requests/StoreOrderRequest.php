<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'table_id' => [
                'nullable',
                'integer',
                'exists:tables,id',
            ],

            'order_type' => [
                'required',
                'in:dine-in,takeaway',
            ],

            'customer_name' => [
                'required',
                'string',
                'max:100',
            ],

            'payment_method' => [
                'required',
                'in:cash,qris',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.menu_item_id' => [
                'required',
                'integer',
                'exists:menu_items,id',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'items.*.notes' => [
                'nullable',
                'string',
                'max:255',
            ],

            'items.*.addon_ids' => [
                'nullable',
                'array',
            ],

            'items.*.addon_ids.*' => [
                'integer',
                'exists:addons,id',
            ],
        ];
    }
}
