<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RatsitQueue>
 */
class RatsitQueueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $postcode = fake()->numberBetween(100, 999) . ' ' . fake()->numberBetween(10, 99);

        return [
            'post_nummer' => $postcode,
            'post_ort' => fake()->city(),
            'post_lan' => fake()->randomElement(['Stockholm', 'Göteborg', 'Malmö', 'Uppsala', 'Västra Götaland', 'Skåne', 'Östergötland']),
            'foretag_total' => fake()->numberBetween(10, 500),
            'personer_total' => fake()->numberBetween(100, 5000),
            'personer_house' => fake()->numberBetween(50, 500),
            'foretag_phone' => fake()->numberBetween(5, 400),
            'personer_phone' => fake()->numberBetween(50, 4000),
            'foretag_saved' => fake()->numberBetween(0, 100),
            'personer_saved' => fake()->numberBetween(0, 1000),
            'personer_pages' => fake()->numberBetween(1, 50),
            'personer_page' => fake()->numberBetween(0, 50),
            // Allowed enum values per migration: pending, running, complete, empty, resume, idle, failed
            'personer_status' => fake()->randomElement(['pending', 'running', 'complete', 'empty', 'resume', 'idle', 'failed']),
            'foretag_status' => fake()->randomElement(['pending', 'running', 'complete', 'empty', 'resume', 'idle', 'failed']),
            'foretag_queued' => fake()->boolean(50),
            'personer_queued' => fake()->boolean(50),
            'foretag_scraped' => fake()->boolean(30),
            'personer_scraped' => fake()->boolean(30),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the record is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the record is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
