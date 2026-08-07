<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    public function rules(): array
    {
        return [
            'title'                             => ['required', 'string', 'max:255'],
            'product_code'                      => [
                'required',
                'string',
                'max:100',
                'unique:products,product_code,' . (is_object($this->route('product')) ? $this->route('product')->id : $this->route('product'))
            ],
            'related_product_codes'             => ['nullable', 'string', 'max:5000'],
            'bundle_product_codes'              => ['nullable', 'string', 'max:5000'],
            'related_product_ids'               => ['nullable', 'array'],
            'related_product_ids.*'             => ['integer', 'exists:products,id'],
            'category_id'                       => ['required', 'exists:categories,id'],
            'base_price'                        => ['required', 'numeric', 'min:0'],
            'description'                       => ['nullable', 'string', 'max:5000'],
            'material'                          => ['nullable', 'string', 'max:255'],
            'status'                            => ['required', 'in:draft,published,archived'],
            'is_new'                            => ['boolean'],
            'is_presale'                        => ['boolean'],
            'presale_available_at'              => ['nullable', 'date'],
            'presale_note'                      => ['nullable', 'string', 'max:1000'],
            'images'                            => ['nullable', 'array', 'max:10'],
            'images.*.id'                       => ['nullable'],
            'images.*.url'                      => ['nullable', 'string'],
            'images.*.color_value'              => ['nullable', 'string', 'max:100'],
            'images.*.is_primary'               => ['nullable', 'boolean'],
            'variants'                          => ['nullable', 'array'],
            'variants.*.id'                     => ['nullable'],
            'variants.*.sku'                    => ['required', 'string', 'max:100'],
            'variants.*.stock_quantity'         => ['required', 'integer', 'min:0'],
            'variants.*.price_override'         => ['nullable', 'numeric', 'min:0'],
            'variants.*.attribute_value_ids'    => ['nullable', 'array'],
            'variants.*.attribute_value_ids.*'  => ['nullable'],
        ];
    }
}
