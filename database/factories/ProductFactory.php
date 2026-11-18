<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(10),
            'price' => fake()->numberBetween(8000, 35000),
            'image_url' => '/images/'.fake()->slug(2).'.jpg',
            'stock' => fake()->numberBetween(5, 40),
            'is_available' => true,
        ];
    }
}
