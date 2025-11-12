<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostNummer>
 */
class PostNummerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_nummer' => $this->faker->unique()->numberBetween(10000, 99999),
            'post_ort' => $this->faker->city(),
            'total_count' => $this->faker->numberBetween(0, 1000),
            'count' => $this->faker->numberBetween(0, 100),
            'phone' => $this->faker->numberBetween(0, 100),
            'house' => $this->faker->numberBetween(0, 100),
            'bolag' => $this->faker->numberBetween(0, 50),
            'foretag' => $this->faker->numberBetween(0, 200),
            'personer' => $this->faker->numberBetween(0, 500),
            'platser' => $this->faker->numberBetween(0, 100),
            'status' => $this->faker->randomElement(['pending', 'running', 'complete']),
            'progress' => $this->faker->numberBetween(0, 100),
            'is_pending' => $this->faker->boolean(),
            'is_complete' => $this->faker->boolean(),
            'is_active' => $this->faker->boolean(),
            'last_processed_page' => $this->faker->numberBetween(0, 10),
            'processed_count' => $this->faker->numberBetween(0, 100),
            'merinfo_personer' => $this->faker->numberBetween(0, 500),
            'merinfo_foretag' => $this->faker->numberBetween(0, 200),
        ];
    }
}
