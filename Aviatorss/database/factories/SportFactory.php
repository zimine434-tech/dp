<?php

namespace Database\Factories;

use App\Models\Sport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sport>
 */
class SportFactory extends Factory
{
    protected $model = Sport::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'description' => fake()->optional()->paragraph(),
            'created_by' => User::factory(),
        ];
    }
}

