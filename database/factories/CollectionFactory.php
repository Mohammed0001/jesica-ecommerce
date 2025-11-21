<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Collection>
 */
class CollectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->words(2, true) . ' Collection';

        return [
            'title' => $title,
            'description' => fake()->paragraphs(2, true),
            'release_date' => fake()->dateTimeBetween('-1 year', '+6 months'),
            'visible' => fake()->boolean(80), // 80% chance of being visible
        ];
    }
}
