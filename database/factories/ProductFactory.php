<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $isOneOfAKind = fake()->boolean(30); // 30% chance of being one-of-a-kind
        $title = fake()->words(3, true);

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numerify('####'),
            'description' => fake()->paragraph(),
            'story' => fake()->paragraphs(2, true),
            'price' => fake()->randomFloat(2, 250, 2500), // Luxury fashion pricing
            'sku' => 'IVH-' . fake()->unique()->numerify('####'),
            'quantity' => $isOneOfAKind ? 1 : fake()->numberBetween(1, 10),
            'is_one_of_a_kind' => $isOneOfAKind,
            'visible' => fake()->boolean(85), // 85% chance of being visible
        ];
    }

    /**
     * Indicate that the product is one-of-a-kind
     */
    public function oneOfAKind(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_one_of_a_kind' => true,
            'quantity' => 1,
        ]);
    }

    /**
     * Indicate that the product has multiple sizes
     */
    public function multiSize(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_one_of_a_kind' => false,
            'quantity' => fake()->numberBetween(5, 15),
        ]);
    }
}
