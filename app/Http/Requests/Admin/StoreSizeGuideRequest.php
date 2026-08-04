<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSizeGuideRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'       => 'required|string|max:50',
            'us_size'    => 'nullable|string|max:20',
            'uk_size'    => 'nullable|string|max:20',
            'eu_size'    => 'nullable|string|max:20',
            'bust'       => 'nullable|string|max:50',
            'waist'      => 'nullable|string|max:50',
            'hips'       => 'nullable|string|max:50',
            'length'     => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ];
    }
}
