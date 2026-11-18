<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'category_id' => $this->id,
            'category_name' => $this->name,
            'description' => $this->description,
            'products_count' => $this->whenCounted('products'),
        ];
    }
}
