<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostNummerQueue>
 */
class PostNummerQueueFactory extends Factory
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

            // Merinfo stats
            'merinfo_personer_saved' => fake()->numberBetween(0, 1000),
            'merinfo_foretag_saved' => fake()->numberBetween(0, 100),
            'merinfo_personer_total' => fake()->numberBetween(100, 5000),
            'merinfo_foretag_total' => fake()->numberBetween(10, 500),
            // Allowed per migration: pending, running, complete, empty, resume, idle, failed, checked, queued, scraped
            'merinfo_status' => fake()->randomElement(['pending', 'running', 'complete', 'empty', 'resume', 'idle', 'failed', 'checked', 'queued', 'scraped']),
            'merinfo_checked' => fake()->boolean(50),
            'merinfo_queued' => fake()->boolean(50),
            'merinfo_scraped' => fake()->boolean(30),
            'merinfo_complete' => fake()->boolean(20),

            // Ratsit stats
            'ratsit_personer_saved' => fake()->numberBetween(0, 1000),
            'ratsit_foretag_saved' => fake()->numberBetween(0, 100),
            'ratsit_personer_total' => fake()->numberBetween(100, 5000),
            'ratsit_foretag_total' => fake()->numberBetween(10, 500),
            'ratsit_status' => fake()->randomElement(['pending', 'running', 'complete', 'empty', 'resume', 'idle', 'failed', 'checked', 'queued', 'scraped']),
            'ratsit_checked' => fake()->boolean(50),
            'ratsit_queued' => fake()->boolean(50),
            'ratsit_scraped' => fake()->boolean(30),
            'ratsit_complete' => fake()->boolean(20),

            // Hitta stats
            'hitta_personer_saved' => fake()->numberBetween(0, 1000),
            'hitta_foretag_saved' => fake()->numberBetween(0, 100),
            'hitta_personer_total' => fake()->numberBetween(100, 5000),
            'hitta_foretag_total' => fake()->numberBetween(10, 500),
            'hitta_status' => fake()->randomElement(['pending', 'running', 'complete', 'empty', 'resume', 'idle', 'failed', 'checked', 'queued', 'scraped']),
            'hitta_checked' => fake()->boolean(50),
            'hitta_queued' => fake()->boolean(50),
            'hitta_scraped' => fake()->boolean(30),
            'hitta_complete' => fake()->boolean(20),

            // PostNummer stats
            'post_nummer_personer_saved' => fake()->numberBetween(0, 1000),
            'post_nummer_foretag_saved' => fake()->numberBetween(0, 100),
            'post_nummer_personer_total' => fake()->numberBetween(100, 5000),
            'post_nummer_foretag_total' => fake()->numberBetween(10, 500),
            'post_nummer_status' => fake()->randomElement(['pending', 'running', 'complete', 'empty', 'resume', 'idle', 'failed', 'checked', 'queued', 'scraped']),
            'post_nummer_checked' => fake()->boolean(50),
            'post_nummer_queued' => fake()->boolean(50),
            'post_nummer_scraped' => fake()->boolean(30),
            'post_nummer_complete' => fake()->boolean(20),

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
