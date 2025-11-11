<?php

namespace Database\Factories;

use App\Models\RatsitData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RatsitData>
 */
class RatsitDataFactory extends Factory
{
    protected $model = RatsitData::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();

        return [
            'gatuadress' => $this->faker->streetAddress(),
            'postnummer' => $this->faker->postcode(),
            'postort' => $this->faker->city(),
            'forsamling' => $this->faker->optional()->word(),
            'kommun' => $this->faker->optional()->city(),
            'lan' => $this->faker->optional()->state(),
            'fodelsedag' => $this->faker->optional()->date(),
            'personnummer' => $this->faker->optional()->numerify('##########'),
            'alder' => $this->faker->optional()->numberBetween(18, 100),
            'kon' => $this->faker->optional()->randomElement(['M', 'F', 'O']),
            'civilstand' => $this->faker->optional()->randomElement(['single', 'married', 'divorced', 'widowed']),
            'fornamn' => $firstName,
            'efternamn' => $lastName,
            'personnamn' => "{$firstName} {$lastName}",
            'telefon' => $this->faker->boolean(70) ? [$this->faker->phoneNumber()] : [],
            'epost_adress' => $this->faker->boolean(70) ? [$this->faker->email()] : [],
            'bolagsengagemang' => $this->faker->boolean(30) ? [['company' => $this->faker->company(), 'role' => $this->faker->jobTitle()]] : [],
            'agandeform' => $this->faker->optional()->randomElement(['Owned', 'Rented', 'Leased']),
            'bostadstyp' => $this->faker->optional()->randomElement(['Apartment', 'House', 'Condo']),
            'boarea' => $this->faker->optional()->numberBetween(30, 300),
            'byggar' => $this->faker->optional()->year(),
            'fastighet' => $this->faker->optional()->bothify('??? ###'),
            'personer' => $this->faker->boolean(50) ? [$this->faker->name()] : [],
            'foretag' => [],
            'grannar' => $this->faker->boolean(30) ? [$this->faker->name()] : [],
            'fordon' => [],
            'hundar' => $this->faker->boolean(20) ? [$this->faker->name()] : [],
            'longitude' => $this->faker->optional()->longitude(),
            'latitud' => $this->faker->optional()->latitude(),
            'is_active' => $this->faker->boolean(90),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
