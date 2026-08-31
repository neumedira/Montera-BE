<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
     * Normalize payment method sebelum validation.
     */
    protected function prepareForValidation(): void
    {
        $paymentMethod = $this->input('payment_method');

        if ($paymentMethod === 'cash') {
            $this->merge([
                'payment_method' => 'tunai',
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // =====================================================
            // TABLE
            // =====================================================

            'table_id' => [
                'nullable',
                'integer',
                'exists:tables,id',
            ],

            // =====================================================
            // ORDER TYPE
            // =====================================================

            'order_type' => [
                'required',
                'in:dine-in,takeaway',
            ],

            // =====================================================
            // CUSTOMER
            // =====================================================

            'customer_name' => [
                'required',
                'string',
                'max:100',
            ],

            // =====================================================
            // PAYMENT METHOD
            // =====================================================

            'payment_method' => [
                'required',
                'string',
                Rule::exists(
                    'payment_settings',
                    'method'
                )->where(function ($query) {
                    $query->where(
                        'is_active',
                        true
                    );
                }),
            ],

            // =====================================================
            // GLOBAL ORDER NOTES
            // =====================================================

            'notes' => [
                'nullable',
                'string',
                'max:500',
            ],

            // =====================================================
            // ITEMS
            // =====================================================

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            // =====================================================
            // MENU ITEM
            // =====================================================

            'items.*.menu_item_id' => [
                'required',
                'integer',
                'exists:menu_items,id',
            ],

            // =====================================================
            // BUNDLE
            // =====================================================
            //
            // null = menu biasa
            // id   = menu merupakan bagian dari bundle
            //
            // =====================================================

            'items.*.bundle_id' => [
                'nullable',
                'integer',
                'exists:bundles,id',
            ],

            // =====================================================
            // QUANTITY
            // =====================================================

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            // =====================================================
            // ITEM NOTES
            // =====================================================

            'items.*.notes' => [
                'nullable',
                'string',
                'max:255',
            ],

            // =====================================================
            // ADDONS
            // =====================================================

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
