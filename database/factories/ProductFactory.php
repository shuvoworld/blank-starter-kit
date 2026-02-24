<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['Electronics', 'Clothing', 'Food & Drink', 'Books', 'Furniture', 'Sports', 'Beauty'];

        return [
            'name' => fake()->words(3, true),
            'sku' => strtoupper(fake()->bothify('??-####')),
            'description' => fake()->optional()->paragraph(),
            'price' => fake()->randomFloat(2, 1, 999),
            'stock' => fake()->numberBetween(0, 500),
            'category' => fake()->randomElement($categories),
            'status' => 'active',
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'active']);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'inactive']);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => ['stock' => 0]);
    }
}
