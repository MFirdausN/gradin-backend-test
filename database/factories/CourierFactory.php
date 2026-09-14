<?php

namespace Database\Factories;

use App\Enums\CourierLevel;
use App\Models\Courier;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Courier> */
class CourierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->unique()->numerify('08##########'),
            'email' => fake()->unique()->safeEmail(),
            'level' => fake()->randomElement(CourierLevel::cases()),
            'is_active' => true,
        ];
    }
}
