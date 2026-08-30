<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Kalau frontend mengirim addon_ids sebagai JSON string,
        // ubah menjadi array sebelum validation.
        if ($this->has('addon_ids') && is_string($this->addon_ids)) {
            $decoded = json_decode($this->addon_ids, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge([
                    'addon_ids' => $decoded,
                ]);
            }
        }
    }

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

            'addon_ids'   => 'nullable|array',
            'addon_ids.*' => 'integer|exists:addons,id',
        ];
    }
}