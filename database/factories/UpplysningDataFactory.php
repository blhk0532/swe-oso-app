<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UpplysningData>
 */
class UpplysningDataFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'personnamn' => fake()->name(),
            'alder' => fake()->numberBetween(18, 100),
            'kon' => fake()->randomElement(['Man', 'Kvinna', 'Annan']),
            'gatuadress' => fake()->streetAddress(),
            'postnummer' => fake()->postcode(),
            'postort' => fake()->city(),
            'telefon' => fake()->phoneNumber(),
            'karta' => fake()->url(),
            'link' => fake()->url(),
            'bostadstyp' => fake()->randomElement(['Villa', 'Lägenhet', 'Radhus', 'Bostadsrätt']),
            'bostadspris' => fake()->numberBetween(1000000, 10000000),
            'is_active' => true,
            'is_telefon' => fake()->boolean(70),
            'is_ratsit' => fake()->boolean(50),
        ];
    }
}
