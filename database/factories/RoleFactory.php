<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->jobTitle();

        return [
            'name' => str()->slug($name),
            'guard_name' => 'web',
            'description' => fake()->sentence(),
        ];
    }

    /**
     * Indicate that the role is an admin role.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Admin',
            'description' => 'Administrator role with full access',
        ]);
    }

    /**
     * Indicate that the role is a user role.
     */
    public function user(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'User',
            'description' => 'Standard user role with limited access',
        ]);
    }
}
