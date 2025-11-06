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
            'bo_gatuadress' => $this->faker->streetAddress(),
            'bo_postnummer' => $this->faker->postcode(),
            'bo_postort' => $this->faker->city(),
            'bo_forsamling' => $this->faker->optional()->word(),
            'bo_kommun' => $this->faker->optional()->city(),
            'bo_lan' => $this->faker->optional()->state(),
            'ps_fodelsedag' => $this->faker->optional()->date(),
            'ps_personnummer' => $this->faker->optional()->numerify('##########'),
            'ps_alder' => $this->faker->optional()->numberBetween(18, 100),
            'ps_kon' => $this->faker->optional()->randomElement(['M', 'F', 'O']),
            'ps_civilstand' => $this->faker->optional()->randomElement(['single', 'married', 'divorced', 'widowed']),
            'ps_fornamn' => $firstName,
            'ps_efternamn' => $lastName,
            'ps_personnamn' => "{$firstName} {$lastName}",
            'ps_telefon' => $this->faker->optional()->randomElement([
                [$this->faker->phoneNumber()],
                [$this->faker->phoneNumber(), $this->faker->phoneNumber()],
                [],
            ]),
            'ps_epost_adress' => $this->faker->optional()->randomElement([
                [$this->faker->email()],
                [$this->faker->email(), $this->faker->email()],
                [],
            ]),
            'ps_bolagsengagemang' => $this->faker->optional()->randomElement([
                [],
                [['company' => $this->faker->company(), 'role' => $this->faker->jobTitle()]],
            ]),
            'bo_agandeform' => $this->faker->optional()->randomElement(['Owned', 'Rented', 'Leased']),
            'bo_bostadstyp' => $this->faker->optional()->randomElement(['Apartment', 'House', 'Condo']),
            'bo_boarea' => $this->faker->optional()->numberBetween(30, 300),
            'bo_byggar' => $this->faker->optional()->year(),
            'bo_fastighet' => $this->faker->optional()->bothify('??? ###'),
            'bo_personer' => $this->faker->optional()->randomElement([
                [],
                [$this->faker->name()],
                [$this->faker->name(), $this->faker->name()],
            ]),
            'bo_foretag' => $this->faker->optional()->randomElement([
                [],
                [$this->faker->company()],
            ]),
            'bo_grannar' => $this->faker->optional()->randomElement([
                [],
                [$this->faker->name()],
            ]),
            'bo_fordon' => $this->faker->optional()->randomElement([
                [],
                [['type' => 'Car', 'registration' => $this->faker->bothify('???###')]],
            ]),
            'bo_hundar' => $this->faker->optional()->randomElement([
                [],
                [$this->faker->name()],
            ]),
            'bo_longitude' => $this->faker->optional()->longitude(),
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
