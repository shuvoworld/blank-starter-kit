<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Permission>
 */
class PermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $modules = ['users', 'roles', 'permissions', 'posts', 'categories', 'settings'];
        $actions = ['view any', 'view', 'create', 'update', 'delete', 'restore'];

        return [
            'name' => fake()->unique()->randomElement($actions).' '.fake()->randomElement($modules),
            'guard_name' => 'web',
            'module' => fake()->randomElement($modules),
            'description' => fake()->sentence(),
        ];
    }

    /**
     * Set a specific module for the permission.
     */
    public function forModule(string $module): static
    {
        return $this->state(fn (array $attributes) => [
            'module' => $module,
        ]);
    }

    /**
     * Set a specific action for the permission.
     */
    public function forAction(string $action): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $action.' '.$attributes['module'] ?? fake()->word(),
        ]);
    }
}
