<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreOutfitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:draft,published'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'image_file' => ['nullable', 'file', 'image', 'max:10240'],
            'image_url' => ['nullable', 'string'],
            'images_files' => ['nullable', 'array'],
            'images_files.*' => ['file', 'image', 'max:10240'],
            'images_urls' => ['nullable', 'array'],
            'images_urls.*' => ['string'],
        ];
    }
}
