<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'product_name' => ['sometimes', 'string', 'max:150'],
            'description' => ['sometimes', 'nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'image_url' => ['sometimes', 'nullable', 'string', 'max:500'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'is_available' => ['sometimes', 'boolean'],
        ];
    }
}
