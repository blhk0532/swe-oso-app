<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-1 week', '+1 week');
        $end = (clone $start)->modify('+' . $this->faker->numberBetween(1, 8) . ' hours');

        return [
            'name' => $this->faker->sentence(3),
            'start' => $start,
            'end' => $end,
        ];
    }
}
