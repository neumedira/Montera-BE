<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuItemRequest extends FormRequest
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
            'category_id' => 'nullable|exists:menu_categories,id',
            'name'        => 'required|string|max:150',
            'label'       => 'nullable|string|max:50',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'photo'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active'   => 'nullable|boolean',
            'addon_ids'   => 'nullable|array',                     // Tambahan validasi addon
        'addon_ids.*' => 'required|integer|exists:addons,id', // Memastikan ID addon valid
        ];
    }
}
