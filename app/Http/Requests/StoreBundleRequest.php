<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBundleRequest extends FormRequest
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
            'name'                 => 'required|string|max:150',
            'photo_url'            => 'nullable|string|max:255',
            'normal_price'         => 'required|numeric|min:0',
            'bundle_price'         => 'required|numeric|min:0',
            'is_active'            => 'nullable|boolean',
            'items'                => 'nullable|array',
            'items.*.menu_item_id' => 'required_with:items|exists:menu_items,id',
            'items.*.quantity'     => 'required_with:items|integer|min:1',
        ];
    }
}
