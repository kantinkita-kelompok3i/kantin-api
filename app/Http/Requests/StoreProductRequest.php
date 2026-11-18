<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // ponytail: kontrak memakai product_name, tabel memakai name — dipetakan di controller
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'product_name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_available' => ['sometimes', 'boolean'],
        ];
    }
}
