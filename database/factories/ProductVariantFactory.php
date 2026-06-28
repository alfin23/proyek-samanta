<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'color' => $this->faker->safeColorName(),
            'size' => $this->faker->randomElement(['S', 'M', 'L', 'XL', 'XXL']),
            'stock' => $this->faker->numberBetween(0, 100),
        ];
    }
}
