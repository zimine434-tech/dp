<?php

namespace Database\Factories;

use App\Models\LocationTraining;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LocationTraining>
 */
class LocationTrainingFactory extends Factory
{
    protected $model = LocationTraining::class;

    public function definition(): array
    {
        return [
            'location' => fake()->address(),
        ];
    }
}

