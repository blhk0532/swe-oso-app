<?php

namespace Database\Factories;

use App\Models\DataPrivate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DataPrivate>
 */
class DataPrivateFactory extends Factory
{
    protected $model = DataPrivate::class;

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
            'telefon' => $this->faker->optional()->randomElement([
                [$this->faker->phoneNumber()],
                [$this->faker->phoneNumber(), $this->faker->phoneNumber()],
                [],
            ]),
            'epost_adress' => $this->faker->optional()->randomElement([
                [$this->faker->email()],
                [$this->faker->email(), $this->faker->email()],
                [],
            ]),
            'bolagsengagemang' => $this->faker->optional()->randomElement([
                [],
                [['company' => $this->faker->company(), 'role' => $this->faker->jobTitle()]],
            ]),
            'agandeform' => $this->faker->optional()->randomElement(['Owned', 'Rented', 'Leased']),
            'bostadstyp' => $this->faker->optional()->randomElement(['Apartment', 'House', 'Condo']),
            'boarea' => $this->faker->optional()->numberBetween(30, 300),
            'byggar' => $this->faker->optional()->year(),
            'fastighet' => $this->faker->optional()->bothify('??? ###'),
            'personer' => $this->faker->optional()->randomElement([
                [],
                [$this->faker->name()],
                [$this->faker->name(), $this->faker->name()],
            ]),
            'foretag' => $this->faker->optional()->randomElement([
                [],
                [$this->faker->company()],
            ]),
            'grannar' => $this->faker->optional()->randomElement([
                [],
                [$this->faker->name()],
            ]),
            'fordon' => $this->faker->optional()->randomElement([
                [],
                [['type' => 'Car', 'registration' => $this->faker->bothify('???###')]],
            ]),
            'hundar' => $this->faker->optional()->randomElement([
                [],
                [$this->faker->name()],
            ]),
            'longitude' => $this->faker->optional()->longitude(),
            'bo_latitud' => $this->faker->optional()->latitude(),
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
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
